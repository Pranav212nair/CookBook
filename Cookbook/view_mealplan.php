<?php
// view_mealplan.php

// 1) Session & auth (no output before this)
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';

$user_id = (int)$_SESSION['user_id'];

// Flash messages
$flash_success = $_SESSION['success'] ?? '';
$flash_error   = $_SESSION['error']   ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// 2) Fetch all meal plans for this user
$plans = [];
$stmt = $conn->prepare("SELECT mealplan_id, start_date, end_date, created_at FROM mealplans WHERE user_id = ? ORDER BY start_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $row['recipes'] = [];
    $plans[(int)$row['mealplan_id']] = $row;
}
$stmt->close();

// 3) Fetch recipe titles for those plans in one query
if (!empty($plans)) {
    $ids = implode(',', array_map('intval', array_keys($plans)));
    $sql = "SELECT mr.mealplan_id, r.title
            FROM mealplan_recipes mr
            JOIN recipes r ON r.recipe_id = mr.recipe_id
            WHERE mr.mealplan_id IN ($ids)
            ORDER BY r.title ASC";
    if ($r = $conn->query($sql)) {
        while ($rw = $r->fetch_assoc()) {
            $plans[(int)$rw['mealplan_id']]['recipes'][] = $rw['title'];
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Your Meal Plans</title>
  <style>
    :root{
      --bg:#faf7f2; --card:#fff; --ink:#1f2937; --muted:#6b7280;
      --brand:#ff6b6b; --brand2:#ff8e53; --ring:rgba(255,107,107,.25);
      --shadow:0 10px 20px rgba(0,0,0,.08); --radius:16px;
      --green:#10b981; --blue:#2563eb; --red:#ef4444;
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
    .btn{
      background:#111827; color:#fff; border:none; border-radius:10px; padding:10px 14px;
      cursor:pointer; box-shadow:var(--shadow); text-decoration:none; display:inline-flex; gap:6px; align-items:center;
      transition:.2s transform,.2s filter;
    }
    .btn:hover{filter:brightness(1.05); transform:translateY(-1px)}
    .btn.primary{background:linear-gradient(90deg,var(--brand),var(--brand2))}
    .btn.blue{background:var(--blue)} .btn.green{background:var(--green)} .btn.red{background:var(--red)}

    .alert{border-radius:10px; padding:10px 12px; margin-bottom:12px}
    .alert.success{background:#f0fff8; color:#065f46; border:1px solid #a7f3d0}
    .alert.error{background:#fff3f3; color:#7a1f1f; border:1px solid #ffd1d1}

    .grid{display:grid; gap:18px; grid-template-columns:repeat(auto-fill,minmax(300px,1fr))}
    .card{background:var(--card); border-radius:var(--radius); box-shadow:var(--shadow); padding:18px; display:flex; flex-direction:column; gap:10px}
    .card h3{margin:2px 0 8px; font-size:18px}
    .meta{display:flex; flex-wrap:wrap; gap:10px; color:var(--muted); font-size:14px}
    .chip-row{display:flex; flex-wrap:wrap; gap:8px}
    .chip{background:#ffe8e8; color:#9b1c1c; border-radius:999px; padding:6px 10px; font-size:13px}
    .card-actions{display:flex; gap:8px; margin-top:6px; flex-wrap:wrap}
    .empty{text-align:center; background:var(--card); border-radius:18px; box-shadow:var(--shadow); padding:28px 24px; max-width:600px; margin:40px auto}
    .empty .emoji{font-size:40px}
    form.inline{display:inline}
  </style>
</head>
<body>
  <header>
    <div class="brand">
      <span class="emoji">🍽️</span>
      <h1>Your Meal Plans</h1>
    </div>
    <p class="sub">Plan your week like a pro chef. Review, tweak, and cook!</p>
  </header>

  <main class="container">
    <div class="actions">
      <a class="btn" href="index.html">🔎 Search by Ingredient</a>
      <a class="btn primary" href="create_mealplan.php">➕ Create Meal Plan</a>
      <a class="btn" href="mainpage.php">🏠 Home</a>
    </div>

    <?php if ($flash_success): ?>
      <div class="alert success"><?= htmlspecialchars($flash_success) ?></div>
    <?php endif; ?>
    <?php if ($flash_error): ?>
      <div class="alert error"><?= htmlspecialchars($flash_error) ?></div>
    <?php endif; ?>

    <?php if (empty($plans)): ?>
      <section class="empty">
        <div class="emoji">🥗</div>
        <h3>No meal plans found</h3>
        <p>Create your first meal plan to get started.</p>
        <div style="margin-top:12px">
          <a class="btn primary" href="create_mealplan.php">Create Meal Plan</a>
        </div>
      </section>
    <?php else: ?>
      <section class="grid">
        <?php foreach ($plans as $plan): ?>
          <?php $pid = (int)$plan['mealplan_id']; ?>
          <article class="card">
            <h3>Meal Plan #<?= $pid ?></h3>
            <div class="meta">
              <span><strong>Start:</strong> <?= htmlspecialchars($plan['start_date']) ?></span>
              <span><strong>End:</strong> <?= htmlspecialchars($plan['end_date']) ?></span>
            </div>

            <div>
              <strong>Recipes:</strong>
              <?php if (!empty($plan['recipes'])): ?>
                <div class="chip-row" style="margin-top:8px">
                  <?php foreach ($plan['recipes'] as $title): ?>
                    <span class="chip"><?= htmlspecialchars($title) ?></span>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div style="color:var(--muted); margin-top:6px">No recipes added yet.</div>
              <?php endif; ?>
            </div>

            <div class="card-actions">
              <a class="btn blue" href="modify_recipe.php?mealplan_id=<?= $pid ?>">Edit Recipes</a>
              <a class="btn" href="view_recipe.php">View Saved Recipes</a>

              <!-- Delete button -->
              <form class="inline" action="delete_mealplan.php" method="POST" onsubmit="return confirm('Delete this meal plan?');">
                <input type="hidden" name="mealplan_id" value="<?= $pid ?>">
                <button type="submit" class="btn red">Delete</button>
              </form>
            </div>
          </article>
        <?php endforeach; ?>
      </section>
    <?php endif; ?>
  </main>
</body>
</html>
