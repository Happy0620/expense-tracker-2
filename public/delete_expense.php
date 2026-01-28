<?php

/**
 * Delete Transaction Page
 */
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

require_login();

$user_id = get_user_id();
$transaction_id = (int)($_GET['id'] ?? 0);

if ($transaction_id <= 0) {
    set_flash('error', 'Invalid transaction');
    redirect('dashboard.php');
}

try {
    // Check if transaction belongs to user
    $stmt = $pdo->prepare("SELECT id FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->execute([$transaction_id, $user_id]);

    if (!$stmt->fetch()) {
        set_flash('error', 'Transaction not found');
        redirect('dashboard.php');
    }

    // Delete transaction
    $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->execute([$transaction_id, $user_id]);

    set_flash('success', 'Transaction deleted successfully');
} catch (PDOException $e) {
    set_flash('error', 'Failed to delete transaction');
}

redirect('dashboard.php');
