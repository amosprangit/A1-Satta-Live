<?php

require '../config.php';

// ===== CHANGE THESE =====
$username = "admin";
$password = "admin123";
$email = "admin@example.com";
// ========================

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Check if username already exists
$stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
$stmt->execute([$username]);

if ($stmt->fetch()) {
    die("Username already exists.");
}

// Insert admin
$stmt = $pdo->prepare("
INSERT INTO admin_users
(username, password, email)
VALUES (?, ?, ?)
");

$stmt->execute([
    $username,
    $hashedPassword,
    $email
]);

echo "Admin created successfully!";