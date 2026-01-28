<?php

/**
 * Edit Transaction Page
 */
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

require_login();

$user_id = get_user_id();
$page_title = 'Edit Transaction';
$errors = [];

// Get transaction ID
$transaction_id = (int)($_GET['id'] ?? 0);

if ($transaction_id <= 0) {
    set_flash('error', 'Invalid transaction');
    redirect('dashboard.php');
}

// Fetch transaction
try {
    $stmt = $pdo->prepare("
        SELECT * FROM transactions 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$transaction_id, $user_id]);
    $transaction = $stmt->fetch();

    if (!$transaction) {
        set_flash('error', 'Transaction not found');
        redirect('dashboard.php');
    }
} catch (PDOException $e) {
    set_flash('error', 'Database error');
    redirect('dashboard.php');
}

// Get categories
$expense_categories = get_categories($pdo, 'expense');
$income_categories = get_categories($pdo, 'income');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token';
    }

    // Sanitize inputs
    $type = sanitize_input($_POST['type'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $amount = sanitize_input($_POST['amount'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $transaction_date = sanitize_input($_POST['transaction_date'] ?? '');

    // Validation
    if (!in_array($type, ['expense', 'income'])) {
        $errors[] = 'Invalid transaction type';
    }

    if ($category_id <= 0) {
        $errors[] = 'Please select a category';
    }

    if (empty($amount) || !is_numeric($amount) || $amount <= 0) {
        $errors[] = 'Please enter a valid amount';
    }

    if (empty($transaction_date)) {
        $errors[] = 'Transaction date is required';
    }

    // Update transaction
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE transactions 
                SET category_id = ?, amount = ?, type = ?, description = ?, transaction_date = ?
                WHERE id = ? AND user_id = ?
            ");

            $stmt->execute([
                $category_id,
                $amount,
                $type,
                $description,
                $transaction_date,
                $transaction_id,
                $user_id
            ]);

            set_flash('success', 'Transaction updated successfully!');
            redirect('dashboard.php');
        } catch (PDOException $e) {
            $errors[] = 'Failed to update transaction. Please try again.';
        }
    }
} else {
    // Pre-fill form with existing data
    $_POST = $transaction;
}

require_once '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <span><i class="fas fa-edit"></i> Edit Transaction</span>
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
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
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

        <div class="form-group">
            <label for="type">Transaction Type *</label>
            <select name="type" id="type" class="form-control" required onchange="updateCategories()">
                <option value="">Select Type</option>
                <option value="expense" <?php echo $_POST['type'] === 'expense' ? 'selected' : ''; ?>>
                    Expense
                </option>
                <option value="income" <?php echo $_POST['type'] === 'income' ? 'selected' : ''; ?>>
                    Income
                </option>
            </select>
        </div>

        <div class="form-group">
            <label for="category_id">Category *</label>
            <select name="category_id" id="category_id" class="form-control" required>
                <option value="">Select Category</option>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="amount">Amount (Rs.) *</label>
                <input type="number"
                    name="amount"
                    id="amount"
                    class="form-control"
                    step="0.01"
                    min="0.01"
                    value="<?php echo escape($_POST['amount'] ?? ''); ?>"
                    required>
            </div>

            <div class="form-group">
                <label for="transaction_date">Date *</label>
                <input type="date"
                    name="transaction_date"
                    id="transaction_date"
                    class="form-control"
                    value="<?php echo escape($_POST['transaction_date'] ?? ''); ?>"
                    max="<?php echo date('Y-m-d'); ?>"
                    required>
            </div>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description"
                id="description"
                class="form-control"
                rows="3"><?php echo escape($_POST['description'] ?? ''); ?></textarea>
        </div>

        <div class="btn-group">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Update Transaction
            </button>
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<script>
    const expenseCategories = <?php echo json_encode($expense_categories); ?>;
    const incomeCategories = <?php echo json_encode($income_categories); ?>;

    function updateCategories() {
        const typeSelect = document.getElementById('type');
        const categorySelect = document.getElementById('category_id');
        const type = typeSelect.value;
        const currentCategory = '<?php echo $_POST['category_id'] ?? ''; ?>';

        categorySelect.innerHTML = '<option value="">Select Category</option>';

        const categories = type === 'expense' ? expenseCategories : incomeCategories;

        categories.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.name;
            if (cat.id == currentCategory) {
                option.selected = true;
            }
            categorySelect.appendChild(option);
        });
    }

    window.addEventListener('DOMContentLoaded', updateCategories);
</script>

<?php require_once '../includes/footer.php'; ?>