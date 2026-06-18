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

<!-- Login Page Styles - Simple & Basic -->
<style>
    /* Reset & Body */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        background: #f5f5f5;
    }

    /* Login Container */
    .login-container {
        max-width: 400px;
        margin: 80px auto;
        padding: 20px;
        background: #ffffff;
        border-radius: 10px;
        border: 1px solid #ddd;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .login-container h1 {
        text-align: center;
        color: #c49a00;
        font-size: 28px;
        margin-bottom: 5px;
    }

    .login-container .subtitle {
        text-align: center;
        color: #666;
        font-size: 14px;
        margin-bottom: 25px;
    }

    .login-container .badge {
        text-align: center;
        background: #1e1e2a;
        color: #ffd700;
        display: inline-block;
        padding: 4px 15px;
        border-radius: 20px;
        font-size: 12px;
        margin-bottom: 15px;
        width: 100%;
    }

    /* Form Elements */
    .form-group {
        margin-bottom: 18px;
    }

    .form-group label {
        display: block;
        font-weight: bold;
        font-size: 14px;
        color: #333;
        margin-bottom: 5px;
    }

    .form-group input {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 16px;
        background: #fafafa;
    }

    .form-group input:focus {
        border-color: #ffd700;
        outline: none;
        background: #fff;
    }

    /* Button */
    .login-btn {
        width: 100%;
        padding: 14px;
        background: #ffd700;
        color: #000;
        border: none;
        border-radius: 8px;
        font-size: 18px;
        font-weight: bold;
        cursor: pointer;
        margin-top: 5px;
    }

    .login-btn:hover {
        background: #e6c200;
    }

    /* Messages */
    .error-msg {
        background: #f8d7da;
        color: #721c24;
        padding: 12px 15px;
        border-radius: 8px;
        margin-bottom: 15px;
        font-size: 14px;
        border-left: 4px solid #dc3545;
    }

    .success-msg {
        background: #d4edda;
        color: #155724;
        padding: 12px 15px;
        border-radius: 8px;
        margin-bottom: 15px;
        font-size: 14px;
        border-left: 4px solid #28a745;
    }

    /* Links */
    .back-link {
        display: block;
        text-align: center;
        margin-top: 18px;
        color: #c49a00;
        text-decoration: none;
        font-size: 14px;
    }

    .back-link:hover {
        text-decoration: underline;
    }

    .security-note {
        text-align: center;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #eee;
        font-size: 12px;
        color: #999;
    }

    /* Responsive */
    @media (max-width: 480px) {
        .login-container {
            margin: 40px 15px;
            padding: 20px 15px;
        }

        .login-container h1 {
            font-size: 24px;
        }

        .form-group input {
            padding: 10px 12px;
            font-size: 14px;
        }

        .login-btn {
            padding: 12px;
            font-size: 16px;
        }
    }
</style>

<!-- Login Page Content -->
<div class="login-container">
    <h1>A1 SATTA LIVE</h1>
    <div class="badge">🔐 Admin Portal</div>
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

<!-- Disawer Section -->
<div class="live-box" style="margin-top: 0; background: #000; text-align: center; padding: 40px 20px;">
    <div id="clockLogin" class="clock" style="color: #ffd700; font-size: 22px; font-weight: bold; margin-bottom: 30px;">
    </div>
    <h2 style="font-size: 28px; margin-bottom: 40px; color: #ffd700;">हा भाई यही आती हे सबसे पहले खबर रूको और देखो</h2>
    <h1 style="font-size: 70px; margin-bottom: 20px; color: #fff;">DISAWER</h1>
    <?php
    $disawer_data = $pdo->query("SELECT today_result FROM game_results WHERE game_name = 'disawer'")->fetch(PDO::FETCH_ASSOC);
    $disawer_result = $disawer_data ? $disawer_data['today_result'] : '71';
    ?>
    <div class="result-number"
        style="font-size: 80px; font-weight: bold; background: #222; display: inline-block; padding: 20px 50px; border-radius: 20px; color: #ffd700;">
        <?php echo $disawer_result; ?></div>
</div>

<script>
    function updateClockLogin() {
        const now = new Date();
        const date = now.toLocaleDateString('en-IN', { year: 'numeric', month: 'long', day: 'numeric' });
        let hours = now.getHours();
        let minutes = now.getMinutes();
        let seconds = now.getSeconds();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        const clockElem = document.getElementById('clockLogin');
        if (clockElem) {
            clockElem.innerHTML = date + " | " + hours.toString().padStart(2, '0') + ":" + minutes.toString().padStart(2, '0') + ":" + seconds.toString().padStart(2, '0') + " " + ampm;
        }
    }
    setInterval(updateClockLogin, 1000);
    updateClockLogin();
</script>

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