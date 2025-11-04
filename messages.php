<?php
session_start();

// Check if the user is logged in (assuming you store the user ID in the session)
if (!isset($_SESSION['user'])) {
    die("You must be logged in to view your messages.");
}

$user_id = $_SESSION['user'];

// Connect to the SQLite database
try {
    $db = new PDO('sqlite:360DB.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get all users who have sent or received messages with the logged-in user
    $query = "
        SELECT DISTINCT
            CASE 
                WHEN sender_id = :user_id THEN receiver_id
                ELSE sender_id
            END AS other_user_id
        FROM messages
        WHERE sender_id = :user_id OR receiver_id = :user_id
    ";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch all users
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Display users
    echo "<h1>Your Conversations</h1>";
    if ($users) {
        foreach ($users as $user) {
            // Get user info (for simplicity, assuming a `users` table exists)
            $other_user_id = $user['other_user_id'];
            $userQuery = "SELECT username FROM users WHERE id = :other_user_id";
            $userStmt = $db->prepare($userQuery);
            $userStmt->bindValue(':other_user_id', $other_user_id, PDO::PARAM_INT);
            $userStmt->execute();
            $userInfo = $userStmt->fetch(PDO::FETCH_ASSOC);

            // Check if user info exists before accessing the username
            if ($userInfo) {
                $username = $userInfo['username'];
                // Display each user as a link to view the conversation
                echo "<p><a href='view_conversation.php?user_id=$other_user_id'>$username</a></p>";
            } else {
                // Handle the case where user info is not found (e.g., user deleted)
                echo "<p>User not found.</p>";
            }
        }
    } else {
        echo "<p>No conversations yet.</p>";
    }

    // Button to send a new message to any user
    // Button to send a new message to any user
    echo "<hr><h3>Start a New Conversation</h3>";
    echo "<form action='send_message.php' method='GET'>";
    echo "<label for='receiver_id'>Select a user to message:</label>";
    echo "<select name='receiver_id' id='receiver_id'>";
        
    // Fetch all users to be selected for starting a new conversation
    $userQuery = "SELECT id, username FROM users WHERE id != :user_id";  // Exclude the logged-in user
    $userStmt = $db->prepare($userQuery);
    $userStmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $userStmt->execute();

    // Populate the dropdown with users
    while ($row = $userStmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['username']) . "</option>";
    }

    echo "</select><br><br>";
    echo "<input type='submit' value='Send Message'>";
    echo "</form>";

    // Add button for scheduling a meeting (next to the Send Message button)
    echo "<hr><h3>Schedule a Meeting</h3>";
    echo "<form action='schedule_meeting.php' method='GET'>";
    echo "<label for='receiver_id'>Select a user to schedule a meeting:</label>";
    echo "<select name='receiver_id' id='receiver_id'>";

    // Re-populate the dropdown with users again (you can optimize this by using the same query)
    $userStmt->execute();  // Re-execute the query to get the users again
    while ($row = $userStmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['username']) . "</option>";
    }

    echo "</select><br><br>";
    echo "<input type='submit' value='Schedule Meeting'>";
    echo "</form>";

    
    // Fetch all users to be selected for starting a new conversation
    $userQuery = "SELECT id, username FROM users WHERE id != :user_id";  // Exclude the logged-in user
    $userStmt = $db->prepare($userQuery);
    $userStmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $userStmt->execute();

    // Populate the dropdown with users
    while ($row = $userStmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['username']) . "</option>";
    }

    echo "</select><br><br>";
    echo "<input type='submit' value='Send Message'>";
    echo "</form>";

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
<h2>Welcome, <?= htmlspecialchars($_SESSION['user']) ?>!</h2>
    <p>You are now logged in.</p>
    <a href="usersearch.php">Search User</a>
    <a href="view_meetings.php">Meetings</a>
    <a href="notifications.php">Notifications</a>
    <a href="messages.php">Messages</a>
    <a href="settings.php">User Settings</a>
    <a href="logout.php">Logout</a>
