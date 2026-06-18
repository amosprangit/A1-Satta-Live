<?php
require_once 'config.php';

// New password - change this to whatever you want
$new_password = 'admin123';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Update the admin user
$stmt = $pdo->prepare("UPDATE admin_users SET password = ? WHERE username = 'admin'");
$stmt->execute([$hashed_password]);

if ($stmt->rowCount() > 0) {
    echo "✅ Password updated successfully!<br>";
    echo "New username: admin<br>";
    echo "New password: " . $new_password . "<br>";
    echo "<a href='admin-login.php'>Click here to login</a>";
} else {
    // If update fails, insert new admin
    $stmt2 = $pdo->prepare("INSERT INTO admin_users (username, password, email) VALUES (?, ?, ?)");
    $stmt2->execute(['admin', $hashed_password, 'admin@a1sattalive.com']);
    echo "✅ New admin created!<br>";
    echo "Username: admin<br>";
    echo "Password: " . $new_password . "<br>";
    echo "<a href='admin-login.php'>Click here to login</a>";
}
?>