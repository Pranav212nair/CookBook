<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Recipes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #fafafa;
            margin: 0;
            padding: 0;
        }
        header {
            background: #ff6b81;
            color: white;
            padding: 15px;
            text-align: center;
        }
        h1 {
            margin: 0;
        }
        .options {
            text-align: center;
            margin: 20px;
        }
        .options button {
            background: #ff6b81;
            border: none;
            padding: 10px 20px;
            margin: 5px;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }
        .options button:hover {
            background: #ff4757;
        }
        #recipes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .recipe {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .recipe:hover {
            transform: scale(1.02);
        }
        .recipe h3 {
            margin-top: 0;
            color: #2f3542;
        }
        .recipe p {
            margin: 5px 0;
            color: #555;
        }
        .recipe button {
            margin-top: 10px;
            background: #1e90ff;
            border: none;
            padding: 8px 15px;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }
        .recipe button:hover {
            background: #0066cc;
        }
    </style>
</head>
<body>

<header>
    <h1>🍴 Saved Recipes</h1>
</header>

<div class="options">
    <button onclick="location.href='index.html'">🔍 Search By Ingredient</button>
    <button onclick="location.href='mainpage.php'">🏠 Go Back Home</button>
</div>

<div id="recipes">
   <?php
   $sql_recipes = "SELECT * FROM recipes";
   $result_recipes = $conn->query($sql_recipes);

   if ($result_recipes && $result_recipes->num_rows > 0) {
       while ($row_recipe = $result_recipes->fetch_assoc()) {
           echo "<div class='recipe'>";
           echo "<h3>" . htmlspecialchars($row_recipe['title']) . "</h3>";
           echo "<p><strong>Cuisine:</strong> " . htmlspecialchars($row_recipe['cuisine']) . "</p>";
           echo "<p><strong>Cooking Time:</strong> " . htmlspecialchars($row_recipe['cooking_time']) . " mins</p>";
           echo "<p><strong>Instructions:</strong> " . nl2br(htmlspecialchars($row_recipe['instructions'])) . "</p>";
           echo "<button onclick=\"location.href='modify_recipe.php?recipe_id=".$row_recipe['recipe_id']."'\">✏️ Edit Recipe</button>";
           echo "</div>";
       }
   } else {
       echo "<p style='text-align:center; color:#777;'>No recipes saved yet.</p>";
   }
   ?>
</div>

</body>
</html>
