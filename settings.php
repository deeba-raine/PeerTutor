<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user'];

// Database connection using PDO
try {
    $db = new PDO('sqlite:360DB.db');  // Adjust path to your database file
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  // Enable error handling
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Get the logged-in user's ID from the session
$user_id = $_SESSION['user'];

// Fetch the current user settings using PDO
$query = "SELECT * FROM Users WHERE id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// If user is not found, log out
if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// If form is submitted, update user settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isTutor = isset($_POST['isTutor']) ? 'true' : 'false';  // Check if isTutor is selected
    $expertise = $_POST['expertise'];
    $availability = $_POST['availability'];
    $location = $_POST['location'];

    // Update the user's information using PDO
    $updateQuery = "
        UPDATE Users 
        SET isTutor = :isTutor, expertise = :expertise, availability = :availability, location = :location 
        WHERE id = :user_id
    ";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->bindValue(':isTutor', $isTutor, PDO::PARAM_STR);
    $updateStmt->bindValue(':expertise', $expertise, PDO::PARAM_STR);
    $updateStmt->bindValue(':availability', $availability, PDO::PARAM_STR);
    $updateStmt->bindValue(':location', $location, PDO::PARAM_STR);
    $updateStmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

    // Execute the update query
    if ($updateStmt->execute()) {
        $message = "Your settings have been updated successfully!";
    } else {
        $message = "Failed to update settings. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f9;
        }
        h2 {
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin: 10px 0 5px;
        }
        input[type="text"], input[type="radio"] {
            margin-bottom: 15px;
            padding: 8px;
        }
        .submit-btn {
            padding: 10px 20px;
            background-color: #5cb85c;
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 15px;
        }
        .submit-btn:hover {
            background-color: #4cae4c;
        }
        .message {
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<h2>Update Your Settings</h2>

<!-- Display success or error message -->
<?php if (isset($message)): ?>
    <div class="message"><?= $message ?></div>
<?php endif; ?>

<form action="settings.php" method="POST">
    <!-- Radio buttons for isTutor -->
    <label for="isTutor">Are you a tutor?</label>
    <input type="radio" name="isTutor" value="1" <?= $user['isTutor'] == 'true' ? 'checked' : ''; ?>> Yes
    <input type="radio" name="isTutor" value="0" <?= $user['isTutor'] == 'false' ? 'checked' : ''; ?>> No

    <!-- Textboxes for expertise, availability, and location -->
    <label for="expertise">Expertise</label>
    <input type="text" name="expertise" id="expertise" value="<?= htmlspecialchars($user['expertise']); ?>" required>

    <label for="availability">Availability</label>
    <input type="text" name="availability" id="availability" value="<?= htmlspecialchars($user['availability']); ?>" required>

    <label for="location">Location</label>
    <input type="text" name="location" id="location" value="<?= htmlspecialchars($user['location']); ?>" required>

    <!-- Submit button -->
    <button type="submit" class="submit-btn">Save Changes</button>
</form>

<h2>Welcome, <?= htmlspecialchars($_SESSION['user']) ?>!</h2>
    <p>You are now logged in.</p>
    <a href="usersearch.php">Search User</a>
    <a href="notifications.php">Notifications</a>
    <a href="messages.php">Messages</a>
    <a href="settings.php">User Settings</a>
    <a href="logout.php">Logout</a>

</body>
</html>
