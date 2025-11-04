<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

try {
    $db = new PDO('sqlite:360DB.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to get the username
    $query = "SELECT username FROM users WHERE id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch the result
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // If user is found, store the username
    $username = $user['username'];

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>

    <h2>Welcome, <?= htmlspecialchars($_SESSION['user']) ?>!</h2>
    <h2>Welcome, <?= htmlspecialchars($username) ?>!</h2>
    <p>You are now logged in.</p>
    <a href="usersearch.php">Search User</a>
    <a href="view_meetings.php">Meetings</a>
    <a href="notifications.php">Notifications</a>
    <a href="messages.php">Messages</a>
    <a href="settings.php">User Settings</a>
    <a href="logout.php">Logout</a>

</body>
</html>
