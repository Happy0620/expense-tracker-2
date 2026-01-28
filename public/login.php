<?php

/**
 * User Login Page
 */
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        try {
            // Fetch user from database
            $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Login successful
                login_user($user['id'], $user['username']);
                redirect('dashboard.php');
            } else {
                $error = 'Invalid username or password';
            }
        } catch (PDOException $e) {
            $error = 'An error occurred. Please try again.';
        }
    }
}

$page_title = 'Login';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escape($page_title); ?> - Expense Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-wallet" style="font-size: 3rem; color: var(--primary-color);"></i>
                <h1>Welcome Back</h1>
                <p>Login to manage your expenses</p>
            </div>

            <?php if ($success = get_flash('success')): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo escape($success); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo escape($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text"
                        id="username"
                        name="username"
                        class="form-control"
                        value="<?php echo escape($_POST['username'] ?? ''); ?>"
                        required
                        autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <div class="auth-toggle">
                Don't have an account? <a href="register.php">Register here</a>
            </div>

            <div style="margin-top: 1rem; padding: 1rem; background-color: #f0f9ff; border-radius: 5px; text-align: center;">
                <small><strong>Demo Account:</strong><br>
                    Username: <code>demo</code><br>
                    Password: <code>demo123</code></small>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>

</html>