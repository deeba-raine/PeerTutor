<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    die("You must be logged in to send a message.");
}

$user_id = $_SESSION['user'];

// Get the receiver ID from the query parameter
if (!isset($_GET['receiver_id'])) {
    die("No user selected.");
}

$receiver_id = $_GET['receiver_id'];

// If the form is submitted, insert the message into the database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];

    try {
        $db = new PDO('sqlite:360DB.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Insert the message into the database
        $query = "
            INSERT INTO messages (sender_id, receiver_id, message)
            VALUES (:sender_id, :receiver_id, :message)
        ";
        
        $stmt = $db->prepare($query);
        $stmt->bindValue(':sender_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':receiver_id', $receiver_id, PDO::PARAM_INT);
        $stmt->bindValue(':message', $message, PDO::PARAM_STR);
        $stmt->execute();

        $notification_message = "New message from User $user_id";
        $query = "
            INSERT INTO notifications (user_id, message)
            VALUES (:receiver_id, :notification_message)
        ";

        $stmt = $db->prepare($query);
        $stmt->bindValue(':receiver_id', $receiver_id, PDO::PARAM_INT);  // Make sure to use the receiver's ID
        $stmt->bindValue(':notification_message', $notification_message, PDO::PARAM_STR);  // Use the correct placeholder name
        $stmt->execute();


        echo "<p>Message sent successfully!</p>";
        echo "<p><a href='view_conversation.php?user_id=$receiver_id'>Go to conversation</a></p>";

    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
}

?>

<!-- HTML form to send a message -->
<h1>Send a Message</h1>
<form action="send_message.php?receiver_id=<?php echo $receiver_id; ?>" method="POST">
    <textarea name="message" rows="4" placeholder="Type your message..."></textarea><br>
    <input type="submit" value="Send Message">
</form>
<h2>Welcome, <?= htmlspecialchars($_SESSION['user']) ?>!</h2>
    <p>You are now logged in.</p>
    <a href="usersearch.php">Search User</a>
    <a href="notifications.php">Notifications</a>
    <a href="messages.php">Messages</a>
    <a href="settings.php">User Settings</a>
    <a href="logout.php">Logout</a>