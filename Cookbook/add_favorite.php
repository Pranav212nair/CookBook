<?php include 'header.php'; 
session_start(); 
include 'db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    // Ensure user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id']; // Get the logged-in user's ID
    $recipe_id = $_POST['recipe_id']; 

    // Prepared statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO favorites (user_id, recipe_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $recipe_id); 

    if ($stmt->execute()) { 
        // Redirect back to the recipe view page
        header("Location: view_recipe.php"); // Adjust this to the correct recipe view page
        exit();
    } else { 
        echo "Error: " . $stmt->error; 
    } 

    // Close the statement
    $stmt->close(); 
} 

$conn->close(); 
?>