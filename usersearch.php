<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Database connection using PDO
try {
    $db = new PDO('sqlite:360DB.db');  // Adjust path to your database file
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  // Enable error handling
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Initialize search parameters
$searchTerm = '';
$searchLocation = '';
$searchExpertise = '';

// Check if form is submitted and process search filters
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Sanitize user input
    if (isset($_GET['searchTerm'])) {
        $searchTerm = '%' . htmlspecialchars($_GET['searchTerm']) . '%'; // Add wildcards for partial match
    }
    if (isset($_GET['searchLocation'])) {
        $searchLocation = '%' . htmlspecialchars($_GET['searchLocation']) . '%'; // Add wildcards for partial match
    }
    if (isset($_GET['searchExpertise'])) {
        $searchExpertise = '%' . htmlspecialchars($_GET['searchExpertise']) . '%'; // Add wildcards for partial match
    }
}

// Build the dynamic query with filters
$query = "SELECT * FROM Users WHERE 1";

$params = [];
if ($searchTerm) {
    $query .= " AND username LIKE :searchTerm";
    $params[':searchTerm'] = $searchTerm;
}
if ($searchLocation) {
    $query .= " AND location LIKE :searchLocation";
    $params[':searchLocation'] = $searchLocation;
}
if ($searchExpertise) {
    $query .= " AND expertise LIKE :searchExpertise";
    $params[':searchExpertise'] = $searchExpertise;
}

// Prepare and execute the query
$stmt = $db->prepare($query);
$stmt->execute($params);

// Fetch all users that match the search criteria
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Search</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f9;
        }
        h2 {
            text-align: center;
        }
        .search-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .search-container input[type="text"] {
            margin: 5px;
            padding: 10px;
            width: 250px;
        }
        .search-container button {
            padding: 10px 20px;
            background-color: #5cb85c;
            color: white;
            border: none;
            cursor: pointer;
        }
        .search-container button:hover {
            background-color: #4cae4c;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .user-info {
            text-align: left;
            padding: 10px;
        }
        .user-info span {
            font-weight: bold;
        }
    </style>
</head>
<body>

<h2>User Search</h2>

<!-- Search Form -->
<div class="search-container">
    <form method="GET" action="usersearch.php">
        <input type="text" name="searchTerm" placeholder="Search by name" value="<?= htmlspecialchars($searchTerm) ?>">
        <input type="text" name="searchLocation" placeholder="Search by location" value="<?= htmlspecialchars($searchLocation) ?>">
        <input type="text" name="searchExpertise" placeholder="Search by expertise" value="<?= htmlspecialchars($searchExpertise) ?>">
        <button type="submit">Search</button>
    </form>
</div>

<!-- Display Search Results -->
<?php if (count($users) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Expertise</th>
                <th>Availability</th>
                <th>Location</th>
                <th>Tutor Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['expertise']) ?></td>
                    <td><?= htmlspecialchars($user['availability']) ?></td>
                    <td><?= htmlspecialchars($user['location']) ?></td>
                    <td><?= $user['isTutor'] == 'true' ? 'Yes' : 'No' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No users found matching your search criteria.</p>
<?php endif; ?>
<h2>Welcome, <?= htmlspecialchars($_SESSION['user']) ?>!</h2>
    <p>You are now logged in.</p>
    <a href="usersearch.php">Search User</a>
    <a href="notifications.php">Notifications</a>
    <a href="messages.php">Messages</a>
    <a href="settings.php">User Settings</a>
    <a href="logout.php">Logout</a>
</body>
</html>