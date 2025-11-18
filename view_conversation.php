<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    die("You must be logged in to view your messages.");
}

$user_id = $_SESSION['user'];

// Get the other user's ID from the query parameter
if (!isset($_GET['user_id'])) {
    die("No user selected.");
}

$other_user_id = $_GET['user_id'];

// Connect to the SQLite database
try {
    $db = new PDO('sqlite:360DB.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get the conversation between the logged-in user and the selected user
    $query = "
        SELECT sender_id, receiver_id, message, created_at
        FROM messages
        WHERE (sender_id = :user_id AND receiver_id = :other_user_id)
        OR (sender_id = :other_user_id AND receiver_id = :user_id)
        ORDER BY created_at DESC
    ";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':other_user_id', $other_user_id, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch all messages
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Display conversation
    echo "<h1>Conversation with User $other_user_id</h1>";
    if ($messages) {
        foreach ($messages as $message) {
            $sender = ($message['sender_id'] == $user_id) ? "You" : "User $message[sender_id]";
            echo "<div class='message'>";
            echo "<p><strong>$sender:</strong> " . htmlspecialchars($message['message']) . "</p>";
            echo "<p><small>" . htmlspecialchars($message['created_at']) . "</small></p>";
            echo "</div><hr>";
        }
    } else {
        echo "<p>No messages exchanged yet.</p>";
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
