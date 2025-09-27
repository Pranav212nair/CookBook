<?php
// delete_mealplan.php

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['mealplan_id'])) {
    $_SESSION['error'] = 'Invalid request.';
    header('Location: view_mealplan.php');
    exit();
}

$mealplan_id = (int)$_POST['mealplan_id'];
$user_id     = (int)$_SESSION['user_id'];

$conn->begin_transaction();
try {
    // Ensure the plan belongs to the current user
    $chk = $conn->prepare("SELECT 1 FROM mealplans WHERE mealplan_id = ? AND user_id = ?");
    $chk->bind_param("ii", $mealplan_id, $user_id);
    $chk->execute();
    $owns = $chk->get_result()->fetch_row();
    $chk->close();

    if (!$owns) {
        throw new Exception('Not authorized to delete this meal plan.');
    }

    // Delete linked rows first
    $d1 = $conn->prepare("DELETE FROM mealplan_recipes WHERE mealplan_id = ?");
    $d1->bind_param("i", $mealplan_id);
    if (!$d1->execute()) throw new Exception($d1->error);
    $d1->close();

    // Delete the plan
    $d2 = $conn->prepare("DELETE FROM mealplans WHERE mealplan_id = ? AND user_id = ?");
    $d2->bind_param("ii", $mealplan_id, $user_id);
    if (!$d2->execute()) throw new Exception($d2->error);
    $d2->close();

    $conn->commit();
    $_SESSION['success'] = 'Meal plan deleted.';
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = 'Could not delete meal plan: ' . htmlspecialchars($e->getMessage());
}

$conn->close();
header('Location: view_mealplan.php');
exit();
