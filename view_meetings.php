<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    die("You must be logged in to view your meetings.");
}

$user_id = $_SESSION['user'];

try {
    // Connect to the SQLite database
    $db = new PDO('sqlite:360DB.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch all pending meeting requests for the logged-in user (as the recipient)
    $query = "
        SELECT m.id, m.user_id AS sender_id, m.receiver_id, m.meeting_date, m.meeting_time, m.accepted, u.username AS sender_username
        FROM meetings m
        JOIN users u ON m.user_id = u.id
        WHERE m.receiver_id = :user_id AND m.accepted = 0
    ";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $pendingMeetings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all accepted meetings (as the recipient)
    $acceptedQuery = "
        SELECT m.id, m.user_id AS sender_id, m.receiver_id, m.meeting_date, m.meeting_time, u.username AS sender_username
        FROM meetings m
        JOIN users u ON m.user_id = u.id
        WHERE m.receiver_id = :user_id AND m.accepted = 1
    ";
    $acceptedStmt = $db->prepare($acceptedQuery);
    $acceptedStmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $acceptedStmt->execute();

    $acceptedMeetings = $acceptedStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all accepted meetings for the logged-in user (as the sender)
    $senderAcceptedQuery = "
        SELECT m.id, m.user_id AS sender_id, m.receiver_id, m.meeting_date, m.meeting_time, u.username AS receiver_username
        FROM meetings m
        JOIN users u ON m.receiver_id = u.id
        WHERE m.user_id = :user_id AND m.accepted = 1
    ";
    $senderAcceptedStmt = $db->prepare($senderAcceptedQuery);
    $senderAcceptedStmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $senderAcceptedStmt->execute();

    $senderAcceptedMeetings = $senderAcceptedStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Meetings</title>
</head>
<body>
    <h1>Your Meetings</h1>
    
    <!-- Pending Meeting Requests (as a recipient) -->
    <h2>Pending Requests</h2>
    <?php if ($pendingMeetings): ?>
        <ul>
        <?php foreach ($pendingMeetings as $meeting): ?>
            <li>
                <strong>Meeting request from:</strong> <?= htmlspecialchars($meeting['sender_username']) ?> <br>
                <strong>Date:</strong> <?= htmlspecialchars($meeting['meeting_date']) ?> <br>
                <strong>Time:</strong> <?= htmlspecialchars($meeting['meeting_time']) ?> <br>
                <form action="view_meetings.php" method="POST">
                    <input type="hidden" name="meeting_id" value="<?= $meeting['id'] ?>">
                    <input type="hidden" name="action" value="accept">
                    <button type="submit">Accept</button>
                </form>
                <form action="view_meetings.php" method="POST">
                    <input type="hidden" name="meeting_id" value="<?= $meeting['id'] ?>">
                    <input type="hidden" name="action" value="decline">
                    <button type="submit">Decline</button>
                </form>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No pending meeting requests.</p>
    <?php endif; ?>

    <hr>

    <!-- Accepted Meetings (as the recipient) -->
    <h2>Accepted Meetings (as a recipient)</h2>
    <?php if ($acceptedMeetings): ?>
        <ul>
        <?php foreach ($acceptedMeetings as $meeting): ?>
            <li>
                <strong>Meeting with:</strong> <?= htmlspecialchars($meeting['sender_username']) ?> <br>
                <strong>Date:</strong> <?= htmlspecialchars($meeting['meeting_date']) ?> <br>
                <strong>Time:</strong> <?= htmlspecialchars($meeting['meeting_time']) ?> <br>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No accepted meetings as a recipient.</p>
    <?php endif; ?>

    <hr>

    <!-- Accepted Meetings (as the sender) -->
    <h2>Accepted Meetings (as a sender)</h2>
    <?php if ($senderAcceptedMeetings): ?>
        <ul>
        <?php foreach ($senderAcceptedMeetings as $meeting): ?>
            <li>
                <strong>Meeting with:</strong> <?= htmlspecialchars($meeting['receiver_username']) ?> <br>
                <strong>Date:</strong> <?= htmlspecialchars($meeting['meeting_date']) ?> <br>
                <strong>Time:</strong> <?= htmlspecialchars($meeting['meeting_time']) ?> <br>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No accepted meetings as a sender.</p>
    <?php endif; ?>

    <hr>

    <a href="messages.php">Go Back to Messages</a>
    <a href="logout.php">Logout</a>
</body>
</html>

<?php
// Handle meeting accept or decline action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $meeting_id = $_POST['meeting_id'];

    try {
        if ($action === 'accept') {
            // Update the meeting status to accepted (set accepted = 1)
            $updateQuery = "UPDATE meetings SET accepted = 1 WHERE id = :meeting_id";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bindValue(':meeting_id', $meeting_id, PDO::PARAM_INT);
            $updateStmt->execute();

            // Get the sender's ID (user_id) to send a notification
            $selectQuery = "SELECT user_id FROM meetings WHERE id = :meeting_id";
            $selectStmt = $db->prepare($selectQuery);
            $selectStmt->bindValue(':meeting_id', $meeting_id, PDO::PARAM_INT);
            $selectStmt->execute();
            $sender = $selectStmt->fetch(PDO::FETCH_ASSOC);

            // Send notification to the sender
            $notification_message = "Your meeting request has been accepted!";
            $notifyQuery = "INSERT INTO notifications (user_id, message) VALUES (:sender_id, :notification_message)";
            $notifyStmt = $db->prepare($notifyQuery);
            $notifyStmt->bindValue(':sender_id', $sender['user_id'], PDO::PARAM_INT);
            $notifyStmt->bindValue(':notification_message', $notification_message, PDO::PARAM_STR);
            $notifyStmt->execute();

            echo "<p>Meeting accepted and notification sent to the sender.</p>";

        } elseif ($action === 'decline') {
            // Delete the meeting request from the database
            $deleteQuery = "DELETE FROM meetings WHERE id = :meeting_id";
            $deleteStmt = $db->prepare($deleteQuery);
            $deleteStmt->bindValue(':meeting_id', $meeting_id, PDO::PARAM_INT);
            $deleteStmt->execute();

            echo "<p>Meeting declined and removed from the database.</p>";
        }

        // Refresh the page to show the updated list of meetings
        header("Location: view_meetings.php");
        exit;

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
