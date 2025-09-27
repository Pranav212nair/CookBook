<?php
// check_login.php

// 1) Bootstrap (no output before redirects!)
session_start();
session_regenerate_id(true);
require_once 'db.php';

// 2) Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

// 3) Get & validate input
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = $_POST['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    $_SESSION['error'] = 'Please enter a valid email and password.';
    header('Location: login.php');
    exit();
}

// 4) Admin login (FIXED)
// Best practice: store a fixed hash in a config/env, not generated on the fly.
// Example (generate once offline): password_hash('admin', PASSWORD_DEFAULT)
// For demo, we'll do a plain-text compare; replace with a stored hash in production.
if ($email === 'admin@cookbook.com' && $password === 'admin') {
    $_SESSION['user_id']  = 0;       // special ID for admin
    $_SESSION['is_admin'] = true;    // flag for admin
    header('Location: mainpage.php');
    exit();
}

// 5) Regular user login (prepared statement + single row)
$stmt = $conn->prepare('SELECT user_id, password FROM users WHERE email = ? LIMIT 1');
if (!$stmt) {
    // DB error
    $_SESSION['error'] = 'Server error. Please try again.';
    header('Location: login.php');
    exit();
}

$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // The `password` column should store a hash from password_hash(...)
    if (password_verify($password, $row['password'])) {
        $_SESSION['user_id']  = (int)$row['user_id'];
        $_SESSION['is_admin'] = false;
        header('Location: mainpage.php');
        $stmt->close();
        $conn->close();
        exit();
    }
}

// 6) Failed login
$stmt->close();
$conn->close();

$_SESSION['error'] = 'Invalid email or password.';
header('Location: login.php');
exit();
