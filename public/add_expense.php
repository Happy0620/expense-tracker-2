<?php

/**
 * Add Transaction Page
 */
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

require_login();

$user_id = get_user_id();
$page_title = 'Add Transaction';
$errors = [];

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

    // Insert transaction
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO transactions (user_id, category_id, amount, type, description, transaction_date)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $user_id,
                $category_id,
                $amount,
                $type,
                $description,
                $transaction_date
            ]);

            set_flash('success', 'Transaction added successfully!');
            redirect('dashboard.php');
        } catch (PDOException $e) {
            $errors[] = 'Failed to add transaction. Please try again.';
        }
    }
}

require_once '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <span><i class="fas fa-plus-circle"></i> Add New Transaction</span>
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

    <form method="POST" action="" id="transaction-form">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

        <div class="form-group">
            <label for="type">Transaction Type *</label>
            <select name="type" id="type" class="form-control" required onchange="updateCategories()">
                <option value="">Select Type</option>
                <option value="expense" <?php echo ($_POST['type'] ?? '') === 'expense' ? 'selected' : ''; ?>>
                    Expense
                </option>
                <option value="income" <?php echo ($_POST['type'] ?? '') === 'income' ? 'selected' : ''; ?>>
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
                    value="<?php echo escape($_POST['transaction_date'] ?? date('Y-m-d')); ?>"
                    max="<?php echo date('Y-m-d'); ?>"
                    required>
            </div>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description"
                id="description"
                class="form-control"
                rows="3"
                placeholder="Optional notes about this transaction"><?php echo escape($_POST['description'] ?? ''); ?></textarea>
        </div>

        <div class="btn-group">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Transaction
            </button>
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<script>
    // Store categories in JavaScript
    const expenseCategories = <?php echo json_encode($expense_categories); ?>;
    const incomeCategories = <?php echo json_encode($income_categories); ?>;

    function updateCategories() {
        const typeSelect = document.getElementById('type');
        const categorySelect = document.getElementById('category_id');
        const type = typeSelect.value;

        // Clear existing options
        categorySelect.innerHTML = '<option value="">Select Category</option>';

        // Add appropriate categories
        const categories = type === 'expense' ? expenseCategories : incomeCategories;

        categories.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.name;
            categorySelect.appendChild(option);
        });
    }

    // Initialize categories on page load
    window.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        if (typeSelect.value) {
            updateCategories();
            // Restore selected category if exists
            const selectedCategory = '<?php echo $_POST['category_id'] ?? ''; ?>';
            if (selectedCategory) {
                document.getElementById('category_id').value = selectedCategory;
            }
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>