<?php

/**
 * AJAX: Check Username Availability
 * Returns JSON response
 */
require_once '../../config/database.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Invalid request method'], 405);
}

$username = sanitize_input($_POST['username'] ?? '');

if (strlen($username) < 3) {
    json_response(['available' => false, 'message' => 'Username too short']);
}

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);

    $available = !$stmt->fetch();

    json_response(['available' => $available]);
} catch (PDOException $e) {
    json_response(['error' => 'Database error'], 500);
}
