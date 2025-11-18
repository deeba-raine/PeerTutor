<?php
// Start the session (optional, you can remove this if you don't need sessions)
session_start();

// Database connection (SQLite)
try {
    $db = new PDO('sqlite:360DB.db');
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Basic form validation
    if (empty($username) || empty($password)) {
        $error = "Username and password cannot be empty!";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if username already exists
        $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $error = "Username already exists!";
        } else {
            // Insert the new user with the hashed password
            $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashed_password);
            if ($stmt->execute()) {
                $success = "User created successfully!";
            } else {
                $error = "Failed to create user. Please try again.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>
</head>
<body>

<h2>Create New User</h2>

<?php if (isset($error)): ?>
    <p style="color: red;"><?= $error ?></p>
<?php elseif (isset($success)): ?>
    <p style="color: green;"><?= $success ?></p>
<?php endif; ?>

<form action="createuser.php" method="POST">
    <label for="username">Username:</label>
    <input type="text" name="username" id="username" required><br>

    <label for="password">Password:</label>
    <input type="password" name="password" id="password" required><br>

    <button type="submit">Create User</button>
</form>

<a href="login.php">Login Page</a>

</body>
</html>
