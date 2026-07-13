<?php
// Only include config.php - session is already started there
require_once 'config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin-dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password!';
    } else {
        // Check in admin_users table
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $success = 'Login successful! Redirecting...';
            header('refresh:2;url=admin-dashboard.php');
        } else {
            $error = 'Invalid username or password!';
        }
    }
}

$page_title = 'Admin Login - A1 Satta Live';
require_once 'header.php';
?>
<link rel="stylesheet" href="./css/admin-login.css">
<!-- Login Page Content -->
<div class="login-container">
    <h1>A1 SATTA LIVE</h1>
    <div class="badge">Admin Portal</div>
    <div class="subtitle">Please login to access the admin dashboard</div>

    <!-- Error Message -->
    <?php if ($error): ?>
        <div class="error-msg">❌ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Success Message -->
    <?php if ($success): ?>
        <div class="success-msg">✅ <?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <!-- Login Form -->
    <form method="POST" action="">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" placeholder="Enter your username" required autofocus>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter your password" required>
        </div>

        <button type="submit" class="login-btn">Login to Dashboard</button>
    </form>

    <!-- Back to Home -->
    <a href="index.php" class="back-link">← Back to Homepage</a>

    <!-- Security Note -->
    <div class="security-note">🔐 This area is restricted to authorized administrators only.</div>
</div>
<?php
// Include footer if it exists
if (file_exists('footer.php')) {
    require_once 'footer.php';
} else {
    ?>
    <!-- Footer Section -->
    <div class="footer" style="background: #000; text-align: center; padding: 30px; border-top: 1px solid #333;">
        <a href="/privacy-policy" style="color: #ffd700; text-decoration: none; margin: 0 20px; font-size: 16px;">Privacy
            Policy</a>
        <a href="/terms-and-conditions"
            style="color: #ffd700; text-decoration: none; margin: 0 20px; font-size: 16px;">Terms & Conditions</a>
        <a href="/disclaimer" style="color: #ffd700; text-decoration: none; margin: 0 20px; font-size: 16px;">Disclaimer</a>
        <p style="color: #666; margin-top: 20px; font-size: 14px;">© 2026 A1 satta live | All Rights Reserved</p>
    </div>

    <div class="disclaimer" style="background: #111; padding: 20px; text-align: center; font-size: 12px; color: #888;">
        !! DISCLAIMER - A1 satta live is a non-commercial informational website. Please view this site at your own risk,
        All The Information Shown On Website Is Sponsored And We Warn You That satta matka Gambling/Satta May Be Banned
        Or Illegal In Your Country. We Are Not Responsible For Any Issues Or Scam..., We Respect All Country
        Rules/Laws... If You Not Agree With Our Site disclaimer Please Quit Our Site Right Now. Thank You.
    </div>
    <?php
}
?>

</body>

</html>