<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? escape($page_title) . ' - ' : ''; ?>Expense Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <i class="fas fa-wallet"></i>
                <span>Expense Tracker</span>
            </div>

            <?php if (is_logged_in()): ?>
                <ul class="nav-menu">
                    <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="add_expense.php"><i class="fas fa-plus"></i> Add Transaction</a></li>
                    <li><a href="search.php"><i class="fas fa-search"></i> Search</a></li>
                    <li class="nav-user">
                        <span><i class="fas fa-user"></i> <?php echo escape(get_username()); ?></span>
                        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </nav>

    <main class="main-content">
        <div class="container">
            <?php
            // Display flash messages
            if ($success = get_flash('success')): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo escape($success); ?>
                </div>
            <?php endif; ?>

            <?php if ($error = get_flash('error')): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo escape($error); ?>
                </div>
            <?php endif; ?>