<?php include 'header.php'; 
session_start();
include 'db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}

// Query to get all users
$sql = "SELECT * FROM users";
$result = $conn->query($sql);

echo "<h1>Users List</h1>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . htmlspecialchars($row["user_id"]) . 
             " - Name: " . htmlspecialchars($row["name"]) . 
             " - Email: " . htmlspecialchars($row["email"]) . "<br>";
    }
} else {
    echo "<h1>No users found.</h1>"; // Changed for clarity
}

// Close the database connection
$conn->close();
?>

<h2>More Options:</h2>
<!-- Link to search by ingredient -->
<button onclick="location.href='index.html'">Search By Ingredient</button>
<button onclick="location.href='mainpage.php'">Go Back Home</button>