<?php
// AddRecipe.php (frontend form)

// 1) Session first (no output before this)
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Optionally read + clear any flash error/success set by add_recipe.php
$flash_error   = $_SESSION['error']   ?? '';
$flash_success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Add a Recipe</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style>
    :root{
      --bg:#faf7f2; --card:#fff; --ink:#1f2937; --muted:#6b7280;
      --brand:#ff6b6b; --brand2:#ff8e53; --ring:rgba(255,107,107,.25);
      --shadow:0 10px 20px rgba(0,0,0,.08); --radius:16px;
      --blue:#2563eb; --green:#10b981; --red:#ef4444;
    }
    *{box-sizing:border-box}
    body{margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif; background:var(--bg); color:var(--ink)}
    header{
      padding:28px 16px 8px; text-align:center;
      background:
        radial-gradient(900px 420px at 100% -40%, #fff4ed 0, transparent 60%),
        radial-gradient(800px 380px at -20% 130%, #fff8f0 0, transparent 60%);
    }
    .brand{display:inline-flex; align-items:center; gap:12px;
      background:linear-gradient(90deg,var(--brand),var(--brand2));
      padding:10px 16px; border-radius:999px; color:#fff; box-shadow:var(--shadow)}
    .brand .emoji{font-size:22px}
    .brand h1{margin:0; font-size:20px; font-weight:800; letter-spacing:.2px}
    .sub{margin:.6rem auto 0; color:var(--muted)}
    .container{max-width:900px; margin:18px auto; padding:0 16px}
    .card{background:var(--card); border-radius:var(--radius); box-shadow:var(--shadow); padding:18px}
    h2{margin:0 0 10px}
    .row{display:grid; grid-template-columns:1fr 1fr; gap:12px}
    label{font-weight:600; display:block; margin-bottom:6px}
    input[type="text"], input[type="number"], textarea{
      width:100%; padding:11px 12px; border:1px solid #e5e7eb; border-radius:10px; font-size:15px; outline:none;
    }
    input:focus, textarea:focus{border-color:var(--brand); box-shadow:0 0 0 4px var(--ring)}
    textarea{min-height:140px; resize:vertical}
    .muted{color:var(--muted); font-size:14px}
    .actions{display:flex; gap:10px; justify-content:flex-end; margin-top:14px}
    .btn{background:#111827; color:#fff; border:none; border-radius:10px; padding:10px 14px; cursor:pointer; box-shadow:var(--shadow); text-decoration:none}
    .btn:hover{filter:brightness(1.05); transform:translateY(-1px)}
    .btn.primary{background:linear-gradient(90deg,var(--brand),var(--brand2))}
    .alert{border-radius:10px; padding:10px 12px; margin-bottom:12px}
    .alert.error{background:#fff3f3; color:#7a1f1f; border:1px solid #ffd1d1}
    .alert.success{background:#f0fff8; color:#065f46; border:1px solid #a7f3d0}
    .tips{margin-top:10px; font-size:13px; color:var(--muted)}
  </style>
</head>
<body>
  <header>
    <div class="brand">
      <span class="emoji">📝</span>
      <h1>Add a Recipe</h1>
    </div>
    <p class="sub">Save your dish to the cookbook. Keep it short and delicious.</p>
  </header>

  <main class="container">
    <div class="card">
      <?php if ($flash_error): ?>
        <div class="alert error"><?= htmlspecialchars($flash_error) ?></div>
      <?php endif; ?>
      <?php if ($flash_success): ?>
        <div class="alert success"><?= htmlspecialchars($flash_success) ?></div>
      <?php endif; ?>

      <form method="post" action="add_recipe.php" autocomplete="off">
        <div class="row">
          <div>
            <label for="title">Recipe Title</label>
            <input type="text" id="title" name="title" placeholder="e.g. Garlic Butter Pasta" required>
          </div>
          <div>
            <label for="cuisine">Cuisine</label>
            <input type="text" id="cuisine" name="cuisine" placeholder="e.g. Italian, Indian, Mexican">
          </div>
        </div>

        <div class="row">
          <div>
            <label for="cooking_time">Cooking Time (minutes)</label>
            <input type="number" id="cooking_time" name="cooking_time" min="0" step="1" placeholder="e.g. 20">
          </div>
          <div>
            <label for="image_url">Image URL (optional)</label>
            <input type="text" id="image_url" name="image_url" placeholder="https://example.com/photo.jpg">
          </div>
        </div>

        <div style="margin-top:10px">
          <label for="instructions">Instructions</label>
          <textarea id="instructions" name="instructions" placeholder="Brief steps for cooking..."></textarea>
          <div class="tips">Tip: shorter steps are easier to follow. You can edit later.</div>
        </div>

        <div class="actions">
          <a class="btn" href="view_recipe.php">Cancel</a>
          <button type="submit" class="btn primary">Save Recipe</button>
        </div>
      </form>
    </div>
  </main>
</body>
</html>
