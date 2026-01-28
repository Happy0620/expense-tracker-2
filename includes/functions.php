<?php

/**
 * Helper Functions
 * Utility functions for the application
 */

/**
 * Sanitize output to prevent XSS
 */
function escape($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize input data
 */
function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    return $data;
}

/**
 * Validate email
 */
function validate_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Format currency
 */
function format_currency($amount)
{
    return 'Rs. ' . number_format($amount, 2);
}

/**
 * Format date
 */
function format_date($date)
{
    return date('M d, Y', strtotime($date));
}

/**
 * Get month name from number
 */
function get_month_name($month)
{
    return date('F', mktime(0, 0, 0, $month, 1));
}

/**
 * Calculate percentage
 */
function calculate_percentage($part, $whole)
{
    if ($whole == 0) return 0;
    return round(($part / $whole) * 100, 2);
}

/**
 * Get category name by ID
 */
function get_category_name($pdo, $category_id)
{
    try {
        $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
        $stmt->execute([$category_id]);
        $result = $stmt->fetch();
        return $result ? $result['name'] : 'Unknown';
    } catch (PDOException $e) {
        return 'Unknown';
    }
}

/**
 * Get all categories by type
 */
function get_categories($pdo, $type = null)
{
    try {
        if ($type) {
            $stmt = $pdo->prepare("SELECT * FROM categories WHERE type = ? ORDER BY name");
            $stmt->execute([$type]);
        } else {
            $stmt = $pdo->query("SELECT * FROM categories ORDER BY type, name");
        }
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get monthly summary for a user
 */
function get_monthly_summary($pdo, $user_id, $year, $month)
{
    try {
        $stmt = $pdo->prepare("
            SELECT 
                type,
                SUM(amount) as total
            FROM transactions 
            WHERE user_id = ? 
            AND YEAR(transaction_date) = ? 
            AND MONTH(transaction_date) = ?
            GROUP BY type
        ");
        $stmt->execute([$user_id, $year, $month]);

        $data = ['income' => 0, 'expense' => 0];
        while ($row = $stmt->fetch()) {
            $data[$row['type']] = $row['total'];
        }
        $data['savings'] = $data['income'] - $data['expense'];

        return $data;
    } catch (PDOException $e) {
        return ['income' => 0, 'expense' => 0, 'savings' => 0];
    }
}

/**
 * Get category-wise expenses
 */
function get_category_expenses($pdo, $user_id, $year, $month)
{
    try {
        $stmt = $pdo->prepare("
            SELECT 
                c.name as category,
                SUM(t.amount) as total
            FROM transactions t
            JOIN categories c ON t.category_id = c.id
            WHERE t.user_id = ? 
            AND t.type = 'expense'
            AND YEAR(t.transaction_date) = ? 
            AND MONTH(t.transaction_date) = ?
            GROUP BY c.name
            ORDER BY total DESC
        ");
        $stmt->execute([$user_id, $year, $month]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Redirect to a page
 */
function redirect($page)
{
    header("Location: $page");
    exit;
}

/**
 * Check if request is AJAX
 */
function is_ajax_request()
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Return JSON response
 */
function json_response($data, $status_code = 200)
{
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
