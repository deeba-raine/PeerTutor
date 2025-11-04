<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user'];

// Handle deletion of notifications
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notification_id'])) {
    $notification_id = $_POST['delete_notification_id'];

    try {
        $db = new PDO('sqlite:360DB.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Query to delete the notification by ID
        $deleteQuery = "DELETE FROM notifications WHERE id = :notification_id AND user_id = :user_id";
        $deleteStmt = $db->prepare($deleteQuery);
        $deleteStmt->bindValue(':notification_id', $notification_id, PDO::PARAM_INT);
        $deleteStmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $deleteStmt->execute();

        // Redirect back to the notifications page to refresh the list
        header("Location: notifications.php");
        exit;

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

try {
    // Connect to the SQLite database using PDO
    $db = new PDO('sqlite:360DB.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to get the user's notifications, ordered by creation date (most recent first)
    $query = "SELECT id, message, type, status, created_at FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

    // Execute the query and fetch results
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Display notifications
    echo "<h1>Your Notifications</h1>";
    if ($results) {
        foreach ($results as $row) {
            // Display each notification
            echo "<div class='notification'>";
            echo "<p><strong>Type:</strong> " . htmlspecialchars($row['type']) . "</p>";
            echo "<p><strong>Message:</strong> " . htmlspecialchars($row['message']) . "</p>";
            echo "<p><strong>Status:</strong> " . htmlspecialchars($row['status']) . "</p>";
            echo "<p><strong>Received at:</strong> " . htmlspecialchars($row['created_at']) . "</p>";
            
            // Form to delete the notification
            echo "<form action='notifications.php' method='POST'>";
            echo "<input type='hidden' name='delete_notification_id' value='" . $row['id'] . "'>";
            echo "<button type='submit'>Delete</button>";
            echo "</form>";
            
            echo "</div><hr>";
        }
    } else {
        echo "<p>You have no notifications at the moment.</p>";
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
</head>
<body>

    <h2>Welcome, <?= htmlspecialchars($_SESSION['user']) ?>!</h2>
    <p>You are now logged in.</p>
    <a href="usersearch.php">Search User</a>
    <a href="view_meetings.php">Meetings</a>
    <a href="notifications.php">Notifications</a>
    <a href="messages.php">Messages</a>
    <a href="settings.php">User Settings</a>
    <a href="logout.php">Logout</a>

</body>
</html>
