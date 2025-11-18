<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    die("You must be logged in to schedule a meeting.");
}

$user_id = $_SESSION['user'];

// Get the receiver ID from the query parameter
if (!isset($_GET['receiver_id'])) {
    die("No user selected for meeting.");
}

$receiver_id = $_GET['receiver_id'];

// If the form is submitted, insert the meeting into the database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['meeting_date']) && isset($_POST['meeting_time'])) {
    $meeting_date = $_POST['meeting_date'];
    $meeting_time = $_POST['meeting_time'];

    try {
        $db = new PDO('sqlite:360DB.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Insert the meeting into the database
        $query = "
            INSERT INTO meetings (user_id, receiver_id, meeting_date, meeting_time)
            VALUES (:user_id, :receiver_id, :meeting_date, :meeting_time)
        ";
        
        $stmt = $db->prepare($query);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':receiver_id', $receiver_id, PDO::PARAM_INT);
        $stmt->bindValue(':meeting_date', $meeting_date, PDO::PARAM_STR);
        $stmt->bindValue(':meeting_time', $meeting_time, PDO::PARAM_STR);
        $stmt->execute();

        echo "<p>Meeting scheduled successfully!</p>";
        echo "<p><a href='view_conversation.php?user_id=$receiver_id'>Go to conversation</a></p>";
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
}

?>

<!-- HTML form to schedule a meeting -->
<h1>Schedule a Meeting</h1>
<form action="schedule_meeting.php?receiver_id=<?php echo $receiver_id; ?>" method="POST">
    <label for="meeting_date">Select a date:</label>
    <input type="date" name="meeting_date" id="meeting_date" required><br><br>

    <label for="meeting_time">Select a time:</label>
    <input type="time" name="meeting_time" id="meeting_time" required><br><br>

    <input type="submit" value="Schedule Meeting">
</form>
<h2>Welcome, <?= htmlspecialchars($_SESSION['user']) ?>!</h2>
    <p>You are now logged in.</p>
    <a href="usersearch.php">Search User</a>
    <a href="view_meetings.php">Meetings</a>
    <a href="notifications.php">Notifications</a>
    <a href="messages.php">Messages</a>
    <a href="settings.php">User Settings</a>
    <a href="logout.php">Logout</a>
