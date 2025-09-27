<?php
// add_user.php

// 1) Bootstrap (no output before redirects!)
session_start();
session_regenerate_id(true);
require_once 'db.php';

// 2) Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: signup.php');
    exit();
}

// 3) Get & validate input
$name               = isset($_POST['name']) ? trim($_POST['name']) : '';
$email              = isset($_POST['email']) ? trim($_POST['email']) : '';
$password           = $_POST['password'] ?? '';
$confirm_password   = $_POST['confirm_password'] ?? '';
$dietary_preference = isset($_POST['dietary_preference']) ? trim($_POST['dietary_preference']) : '';

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '' || $confirm_password === '') {
    $_SESSION['error'] = 'Please fill out all fields with valid information.';
    header('Location: signup.php');
    exit();
}

if ($password !== $confirm_password) {
    $_SESSION['error'] = 'Passwords do not match.';
    header('Location: signup.php');
    exit();
}

// 4) Check for duplicate email
$check = $conn->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
if (!$check) {
    $_SESSION['error'] = 'Server error. Please try again.';
    header('Location: signup.php');
    exit();
}
$check->bind_param('s', $email);
$check->execute();
$checkRes = $check->get_result();
if ($checkRes && $checkRes->num_rows > 0) {
    $check->close();
    $_SESSION['error'] = 'That email is already registered. Please log in.';
    header('Location: login.php');
    exit();
}
$check->close();

// 5) Create user (hashed password)
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare('INSERT INTO users (name, email, password, dietary_preference) VALUES (?, ?, ?, ?)');
if (!$stmt) {
    $_SESSION['error'] = 'Server error. Please try again.';
    header('Location: signup.php');
    exit();
}
$stmt->bind_param('ssss', $name, $email, $hash, $dietary_preference);

if ($stmt->execute()) {
    // 6) Auto-login new user and redirect to main page
    $_SESSION['user_id']  = (int)$conn->insert_id;
    $_SESSION['is_admin'] = false;
    $stmt->close();
    $conn->close();
    header('Location: mainpage.php');
    exit();
} else {
    // If table has UNIQUE(email), this will also catch duplicates
    $_SESSION['error'] = 'Could not create account. ' . ($stmt->errno === 1062 ? 'Email already in use.' : 'Please try again.');
    $stmt->close();
    $conn->close();
    header('Location: signup.php');
    exit();
}
