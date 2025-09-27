<?php
// create_mealplan.php (fixed)

// 1) Session FIRST, no output before this line
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';

// 2) Handle POST: create the plan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id    = (int)$_SESSION['user_id'];
    $start_date = $_POST['start_date']   ?? '';
    $end_date   = $_POST['end_date']     ?? '';
    $recipe_ids = isset($_POST['recipe_ids']) && is_array($_POST['recipe_ids']) ? $_POST['recipe_ids'] : [];

    // Basic validation
    if ($start_date === '' || $end_date === '') {
        $_SESSION['error'] = 'Please provide start and end dates.';
        header("Location: create_mealplan.php");
        exit();
    }
    if (strtotime($start_date) === false || strtotime($end_date) === false) {
        $_SESSION['error'] = 'Invalid date format.';
        header("Location: create_mealplan.php");
        exit();
    }
    if (strtotime($start_date) > strtotime($end_date)) {
        $_SESSION['error'] = 'Start date cannot be after end date.';
        header("Location: create_mealplan.php");
        exit();
    }

    // Transaction for atomic insert
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO mealplans (user_id, start_date, end_date) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $start_date, $end_date);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $mealplan_id = $conn->insert_id;
        $stmt->close();

        if (!empty($recipe_ids)) {
            $stmt2 = $conn->prepare("INSERT INTO mealplan_recipes (mealplan_id, recipe_id) VALUES (?, ?)");
            foreach ($recipe_ids as $rid) {
                $rid = (int)$rid;
                $stmt2->bind_param("ii", $mealplan_id, $rid);
                if (!$stmt2->execute()) {
                    throw new Exception($stmt2->error);
                }
            }
            $stmt2->close();
        }

        $conn->commit();
        header("Location: view_mealplan.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = 'Could not create meal plan: ' . htmlspecialchars($e->getMessage());
        header("Location: create_mealplan.php");
        exit();
    }
}

// 3) GET: render a small form (include header AFTER logic so headers still work)
include 'header.php';

// Fetch user’s saved recipes to pick from
$user_id = (int)$_SESSION['user_id'];
$recipes = [];
$r = $conn->prepare("SELECT recipe_id, title FROM recipes WHERE user_id = ? ORDER BY title");
$r->bind_param("i", $user_id);
$r->execute();
$res = $r->get_result();
while ($row = $res->fetch_assoc()) { $recipes[] = $row; }
$r->close();
$conn->close();

// Read & clear error
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Meal Plan</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    :root{--bg:#faf7f2;--card:#fff;--ink:#1f2937;--muted:#6b7280;--brand:#ff6b6b;--brand2:#ff8e53;--shadow:0 10px 20px rgba(0,0,0,.08);--radius:16px;}
    *{box-sizing:border-box}
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:var(--bg);color:var(--ink)}
    .container{max-width:900px;margin:22px auto;padding:0 16px}
    .card{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:18px}
    h1{margin:0 0 14px}
    .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    label{font-weight:600}
    input[type="date"]{width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:10px}
    .recipes{margin-top:14px;max-height:260px;overflow:auto;border:1px solid #eee;border-radius:10px;padding:10px;background:#fff}
    .recipe-item{display:flex;align-items:center;gap:8px;padding:6px 4px}
    .actions{display:flex;gap:10px;margin-top:16px;justify-content:flex-end}
    .btn{background:#111827;color:#fff;border:none;border-radius:10px;padding:10px 14px;cursor:pointer;box-shadow:var(--shadow)}
    .btn.primary{background:linear-gradient(90deg,var(--brand),var(--brand2))}
    .muted{color:var(--muted);font-size:14px}
    .alert{background:#fff3f3;color:#7a1f1f;border:1px solid #ffd1d1;padding:10px;border-radius:10px;margin-bottom:12px}
  </style>
</head>
<body>
  <main class="container">
    <div class="card">
      <h1>➕ Create Meal Plan</h1>
      <p class="muted">Pick a date range and optionally attach saved recipes.</p>

      <?php if ($error): ?>
        <div class="alert"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post" action="create_mealplan.php" autocomplete="off">
        <div class="row">
          <div>
            <label for="start_date">Start date</label>
            <input type="date" id="start_date" name="start_date" required>
          </div>
          <div>
            <label for="end_date">End date</label>
            <input type="date" id="end_date" name="end_date" required>
          </div>
        </div>

        <div class="recipes">
          <?php if (empty($recipes)): ?>
            <div class="muted">You have no saved recipes yet.</div>
          <?php else: ?>
            <?php foreach ($recipes as $rc): ?>
              <label class="recipe-item">
                <input type="checkbox" name="recipe_ids[]" value="<?= (int)$rc['recipe_id'] ?>">
                <span><?= htmlspecialchars($rc['title']) ?></span>
              </label>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <div class="actions">
          <button type="button" class="btn" onclick="location.href='view_mealplan.php'">Cancel</button>
          <button type="submit" class="btn primary">Create Plan</button>
        </div>
      </form>
    </div>
  </main>
</body>
</html>
