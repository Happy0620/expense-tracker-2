<?php

/**
 * AJAX: Category Autocomplete
 * Returns matching category names
 */
require_once '../../config/database.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Invalid request method'], 405);
}

$query = sanitize_input($_POST['query'] ?? '');

if (strlen($query) < 2) {
    json_response(['categories' => []]);
}

try {
    $stmt = $pdo->prepare("
        SELECT DISTINCT name 
        FROM categories 
        WHERE name LIKE ? 
        ORDER BY name 
        LIMIT 10
    ");
    $stmt->execute(["%$query%"]);

    $categories = [];
    while ($row = $stmt->fetch()) {
        $categories[] = $row['name'];
    }

    json_response(['categories' => $categories]);
} catch (PDOException $e) {
    json_response(['error' => 'Database error'], 500);
}
