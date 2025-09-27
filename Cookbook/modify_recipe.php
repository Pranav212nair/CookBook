<?php include 'header.php'; 
session_start(); 
include 'db.php'; 

if (!isset($_GET['recipe_id'])) {
    echo "No recipe specified.";
    exit();
}

$recipe_id = intval($_GET['recipe_id']); // Ensure it's an integer

// Fetch recipe details from database
$sql_recipe = "SELECT * FROM recipes WHERE recipe_id = ?";
$stmt_recipe = $conn->prepare($sql_recipe);
$stmt_recipe->bind_param("i", $recipe_id);
$stmt_recipe->execute();
$result_recipe = $stmt_recipe->get_result();

if ($result_recipe && $result_recipe->num_rows > 0) {
    $row_recipe = $result_recipe->fetch_assoc();
    
    echo "<h1>" . htmlspecialchars($row_recipe["title"]) . "</h1>";
    echo "<p><strong>Cuisine:</strong> " . htmlspecialchars($row_recipe["cuisine"]) . "</p>";
    echo "<p><strong>Cooking Time:</strong> " . htmlspecialchars($row_recipe["cooking_time"]) . " minutes</p>";
    echo "<p><strong>Instructions:</strong> " . nl2br(htmlspecialchars($row_recipe["instructions"])) . "</p>";

} else {
   echo "<h1>Recipe not found.</h1>";
}

// Close the recipe statement
$stmt_recipe->close();

// Fetch comments for this recipe
$sql_comments = "SELECT * FROM comments WHERE recipe_id = ?";
$stmt_comments = $conn->prepare($sql_comments);
$stmt_comments->bind_param("i", $recipe_id);
$stmt_comments->execute();
$result_comments = $stmt_comments->get_result();

echo "<h2>Comments:</h2>";
if ($result_comments && $result_comments->num_rows > 0) {
   while ($row_comment = $result_comments->fetch_assoc()) {
       echo "<p><strong>User ID:</strong> " . htmlspecialchars($row_comment["user_id"]) . "<br>" .
            htmlspecialchars($row_comment["content"]) . "</p>";
   }
} else {
   echo "<p>No comments yet.</p>";
}

// Close comments statement
$stmt_comments->close();

// Add Comment Form
if (isset($_SESSION['user_id'])) {  // Only show form if user is logged in
   echo '<h2>Add Comment</h2>';
   echo '<form action="add_comment.php" method="POST">';
   echo '<textarea name="content" required></textarea>';
   echo '<input type="hidden" name="recipe_id" value="' . htmlspecialchars($recipe_id) . '">';
   echo '<input type="submit" value="Add Comment">';
   echo '</form>';
}

// Add Favorite Button
if (isset($_SESSION['user_id'])) {  // Only show button if user is logged in
   echo '<form action="add_favorite.php" method="POST">';
   echo '<input type="hidden" name="recipe_id" value="' . htmlspecialchars($recipe_id) . '">';
   echo '<input type="submit" value="Add to Favorites">';
   echo '</form>';
}

// Create Meal Plan Form
if (isset($_SESSION['user_id'])) {  // Only show form if user is logged in
    echo '<h2>Create Meal Plan</h2>';
    echo '<form action="create_mealplan.php" method="POST">';
    echo 'Start Date: <input type="date" name="start_date" required>';
    echo 'End Date: <input type="date" name="end_date" required>';
    
    // Fetch recipes for selection (optional)
    $sql_recipes = "SELECT * FROM recipes";
    $result_recipes = $conn->query($sql_recipes);
    
    if ($result_recipes && $result_recipes->num_rows > 0) {
        echo 'Select Recipes:<br>';
        echo '<select name="recipe_ids[]" multiple required>';
        while ($row_recipe = $result_recipes->fetch_assoc()) {
            echo "<option value='" . htmlspecialchars($row_recipe['recipe_id']) . "'>" . htmlspecialchars($row_recipe['title']) . "</option>";
        }
        echo '</select><br>';
    }

    echo '<input type="submit" value="Create Meal Plan">';
    echo '</form>';
}

// More Options Section
echo '<h2>More Options:</h2>';
echo '<button onclick="location.href=\'index.html\'">Search By Ingredient</button>';
echo '<button onclick="location.href=\'mainpage.php\'">Go Back Home</button>';

$conn->close(); 
?>