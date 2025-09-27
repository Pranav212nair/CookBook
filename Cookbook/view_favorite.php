<?php
// view_favorite.php

// 1) Session & auth
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'db.php';

$user_id = (int)$_SESSION['user_id'];

// 2) Fetch favorites with recipe details
$favorites = [];
$sql = "SELECT r.recipe_id, r.title, r.cuisine, r.cooking_time, r.instructions
        FROM favorites f
        JOIN recipes r ON r.recipe_id = f.recipe_id
        WHERE f.user_id = ?
        ORDER BY f.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $favorites[] = $row;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Your Favorite Recipes</title>
  <style>
    :root{
      --bg:#faf7f2; --card:#fff; --ink:#1f2937; --muted:#6b7280;
      --brand:#ff6b6b; --brand2:#ff8e53; --shadow:0 10px 20px rgba(0,0,0,.08);
      --radius:16px; --blue:#2563eb; --red:#ef4444; --green:#10b981;
    }
    *{box-sizing:border-box}
    body{margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif; background:var(--bg); color:var(--ink)}
    header{padding:28px 16px 8px; text-align:center}
    .brand{display:inline-flex; align-items:center; gap:12px;
      background:linear-gradient(90deg,var(--brand),var(--brand2));
      padding:10px 16px; border-radius:999px; color:#fff; box-shadow:var(--shadow)}
    .brand .emoji{font-size:22px}
    .brand h1{margin:0; font-size:20px; font-weight:800}
    .sub{margin:.6rem auto 0; color:var(--muted)}
    .container{max-width:1100px; margin:18px auto; padding:0 16px}
    .actions{display:flex; gap:10px; justify-content:flex-end; margin-bottom:14px}
    .btn{background:#111827; color:#fff; border:none; border-radius:10px; padding:10px 14px;
         cursor:pointer; box-shadow:var(--shadow); text-decoration:none; display:inline-flex; gap:6px}
    .btn:hover{filter:brightness(1.05); transform:translateY(-1px)}
    .btn.blue{background:var(--blue)} .btn.red{background:var(--red)} .btn.green{background:var(--green)}
    .grid{display:grid; gap:18px; grid-template-columns:repeat(auto-fill,minmax(280px,1fr))}
    .card{background:var(--card); border-radius:var(--radius); box-shadow:var(--shadow); overflow:hidden; display:flex; flex-direction:column}
    .thumb{height:140px; background:#f3f4f6; display:flex; align-items:center; justify-content:center; font-size:36px}
    .body{padding:14px}
    .body h3{margin:0 0 6px}
    .meta{display:flex; gap:10px; flex-wrap:wrap; color:var(--muted); font-size:13px}
    .dot{width:4px; height:4px; background:#d1d5db; border-radius:50%; align-self:center}
    .desc{margin:10px 0 12px; color:#374151; font-size:14px; line-height:1.45; max-height:84px; overflow:auto}
    .card-actions{display:flex; gap:8px; margin:0 14px 14px}
    .empty{max-width:620px; margin:40px auto; background:var(--card); border-radius:18px; box-shadow:var(--shadow); padding:28px 24px; text-align:center}
    .empty .emoji{font-size:40px}
  </style>
</head>
<body>
  <header>
    <div class="brand">
      <span class="emoji">⭐</span>
      <h1>Your Favorite Recipes</h1>
    </div>
    <p class="sub">Quick access to the dishes you love.</p>
  </header>

  <main class="container">
    <div class="actions">
      <a href="index.html" class="btn blue">🔎 Search by Ingredient</a>
      <a href="view_recipe.php" class="btn">📖 View All Saved</a>
      <a href="mainpage.php" class="btn">🏠 Home</a>
    </div>

    <?php if (empty($favorites)): ?>
      <section class="empty">
        <div class="emoji">🍰</div>
        <h3>No favorites yet</h3>
        <p class="sub">Browse recipes and tap “Add to Favorites” to see them here.</p>
        <div style="margin-top:12px">
          <a class="btn green" href="view_recipe.php">Browse Saved Recipes</a>
        </div>
      </section>
    <?php else: ?>
      <section class="grid">
        <?php foreach ($favorites as $r): ?>
          <article class="card">
            <div class="thumb">🍲</div>
            <div class="body">
              <h3><?= htmlspecialchars($r['title']) ?></h3>
              <div class="meta">
                <span><?= htmlspecialchars($r['cuisine'] ?? '—') ?></span>
                <span class="dot"></span>
                <span><?= ($r['cooking_time'] !== null && $r['cooking_time'] !== '') ? (int)$r['cooking_time'].' min' : '—' ?></span>
              </div>
              <div class="desc"><?= nl2br(htmlspecialchars(mb_strimwidth($r['instructions'] ?? '', 0, 260, '…'))) ?></div>
            </div>
            <div class="card-actions">
              <a class="btn blue" href="modify_recipe.php?recipe_id=<?= (int)$r['recipe_id'] ?>">Edit</a>
              <!-- Optional: create remove_favorite.php to handle deletion safely -->
              <!-- <a class="btn red" href="remove_favorite.php?recipe_id=<?= (int)$r['recipe_id'] ?>" onclick="return confirm('Remove from favorites?')">Remove</a> -->
            </div>
          </article>
        <?php endforeach; ?>
      </section>
    <?php endif; ?>
  </main>
</body>
</html>
