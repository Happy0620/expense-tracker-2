<?php

/**
 * AJAX: Get Monthly Summary
 * Returns income, expenses, and category breakdown
 */
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    json_response(['error' => 'Unauthorized'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Invalid request method'], 405);
}

$user_id = get_user_id();
$month = (int)($_POST['month'] ?? date('n'));
$year = (int)($_POST['year'] ?? date('Y'));

// Validate inputs
if ($month < 1 || $month > 12) {
    json_response(['error' => 'Invalid month'], 400);
}

if ($year < 2000 || $year > 2100) {
    json_response(['error' => 'Invalid year'], 400);
}

try {
    // Get monthly summary
    $summary = get_monthly_summary($pdo, $user_id, $year, $month);

    // Get category-wise expenses
    $category_expenses = get_category_expenses($pdo, $user_id, $year, $month);

    // Calculate percentages
    $total_expense = $summary['expense'];
    $categories_with_percentage = [];

    foreach ($category_expenses as $cat) {
        $categories_with_percentage[] = [
            'category' => $cat['category'],
            'total' => $cat['total'],
            'percentage' => calculate_percentage($cat['total'], $total_expense)
        ];
    }

    json_response([
        'success' => true,
        'income' => $summary['income'],
        'expense' => $summary['expense'],
        'savings' => $summary['savings'],
        'categories' => $categories_with_percentage,
        'month' => get_month_name($month),
        'year' => $year
    ]);
} catch (PDOException $e) {
    json_response(['error' => 'Database error'], 500);
}
