<?php

/**
 * User Registration Page
 */
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $username = sanitize_input($_POST['username'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($username)) {
        $errors[] = 'Username is required';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters';
    }

    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!validate_email($email)) {
        $errors[] = 'Invalid email format';
    }

    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }

    // Check if username or email already exists
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);

            if ($stmt->fetch()) {
                $errors[] = 'Username or email already exists';
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error occurred';
        }
    }

    // Register user
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password]);

            set_flash('success', 'Registration successful! Please login.');
            redirect('login.php');
        } catch (PDOException $e) {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}

$page_title = 'Register';
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
                <h1>Create Account</h1>
                <p>Join us to start tracking your expenses</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul style="margin: 0; padding-left: 1.5rem;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo escape($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text"
                        id="username"
                        name="username"
                        class="form-control"
                        value="<?php echo escape($_POST['username'] ?? ''); ?>"
                        required>
                    <div id="username-feedback" style="margin-top: 0.5rem; font-size: 0.875rem;"></div>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        value="<?php echo escape($_POST['email'] ?? ''); ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="password">Password * (min 6 characters)</label>
                    <input type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password"
                        id="confirm_password"
                        name="confirm_password"
                        class="form-control"
                        required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-user-plus"></i> Register
                </button>
            </form>

            <div class="auth-toggle">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>

</html>