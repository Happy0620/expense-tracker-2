<?php

/**
 * Search Page - Advanced Search with Multiple Criteria
 */
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

require_login();

$user_id = get_user_id();
$page_title = 'Search Transactions';
$results = [];
$search_performed = false;

// Get all categories
$categories = get_categories($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' || !empty($_GET['search'])) {
    $search_performed = true;

    // Get search criteria
    $keyword = sanitize_input($_POST['keyword'] ?? $_GET['keyword'] ?? '');
    $type = sanitize_input($_POST['type'] ?? $_GET['type'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? $_GET['category_id'] ?? 0);
    $date_from = sanitize_input($_POST['date_from'] ?? $_GET['date_from'] ?? '');
    $date_to = sanitize_input($_POST['date_to'] ?? $_GET['date_to'] ?? '');
    $amount_min = sanitize_input($_POST['amount_min'] ?? $_GET['amount_min'] ?? '');
    $amount_max = sanitize_input($_POST['amount_max'] ?? $_GET['amount_max'] ?? '');

    // Build query
    $sql = "SELECT t.*, c.name as category_name 
            FROM transactions t 
            JOIN categories c ON t.category_id = c.id 
            WHERE t.user_id = ?";
    $params = [$user_id];

    // Add conditions based on criteria
    if (!empty($keyword)) {
        $sql .= " AND t.description LIKE ?";
        $params[] = "%$keyword%";
    }

    if (!empty($type) && in_array($type, ['expense', 'income'])) {
        $sql .= " AND t.type = ?";
        $params[] = $type;
    }

    if ($category_id > 0) {
        $sql .= " AND t.category_id = ?";
        $params[] = $category_id;
    }

    if (!empty($date_from)) {
        $sql .= " AND t.transaction_date >= ?";
        $params[] = $date_from;
    }

    if (!empty($date_to)) {
        $sql .= " AND t.transaction_date <= ?";
        $params[] = $date_to;
    }

    if (!empty($amount_min) && is_numeric($amount_min)) {
        $sql .= " AND t.amount >= ?";
        $params[] = $amount_min;
    }

    if (!empty($amount_max) && is_numeric($amount_max)) {
        $sql .= " AND t.amount <= ?";
        $params[] = $amount_max;
    }

    $sql .= " ORDER BY t.transaction_date DESC, t.created_at DESC";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = 'Search failed. Please try again.';
    }
}

require_once '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <span><i class="fas fa-search"></i> Search Transactions</span>
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <form method="POST" action="">
        <div class="form-group" style="position: relative;">
            <label for="keyword">Search by Description</label>
            <input type="text"
                name="keyword"
                id="category-search"
                class="form-control"
                value="<?php echo escape($keyword ?? ''); ?>"
                placeholder="Enter keywords...">
            <div id="category-suggestions" class="autocomplete-suggestions"></div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="type">Type</label>
                <select name="type" id="type" class="form-control">
                    <option value="">All Types</option>
                    <option value="expense" <?php echo ($type ?? '') === 'expense' ? 'selected' : ''; ?>>
                        Expense
                    </option>
                    <option value="income" <?php echo ($type ?? '') === 'income' ? 'selected' : ''; ?>>
                        Income
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label for="category_id">Category</label>
                <select name="category_id" id="category_id" class="form-control">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"
                            <?php echo ($category_id ?? 0) == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo escape($cat['name']); ?>
                            (<?php echo ucfirst($cat['type']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="date_from">Date From</label>
                <input type="date"
                    name="date_from"
                    id="date_from"
                    class="form-control"
                    value="<?php echo escape($date_from ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="date_to">Date To</label>
                <input type="date"
                    name="date_to"
                    id="date_to"
                    class="form-control"
                    value="<?php echo escape($date_to ?? ''); ?>"
                    max="<?php echo date('Y-m-d'); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="amount_min">Amount Min (Rs.)</label>
                <input type="number"
                    name="amount_min"
                    id="amount_min"
                    class="form-control"
                    step="0.01"
                    value="<?php echo escape($amount_min ?? ''); ?>"
                    placeholder="0.00">
            </div>

            <div class="form-group">
                <label for="amount_max">Amount Max (Rs.)</label>
                <input type="number"
                    name="amount_max"
                    id="amount_max"
                    class="form-control"
                    step="0.01"
                    value="<?php echo escape($amount_max ?? ''); ?>"
                    placeholder="10000.00">
            </div>
        </div>

        <div class="btn-group">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Search
            </button>
            <a href="search.php" class="btn btn-secondary">
                <i class="fas fa-redo"></i> Reset
            </a>
        </div>
    </form>
</div>

<?php if ($search_performed): ?>
    <div class="card">
        <h3><i class="fas fa-list"></i> Search Results (<?php echo count($results); ?> found)</h3>

        <?php if (!empty($results)): ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_income = 0;
                        $total_expense = 0;

                        foreach ($results as $trans):
                            if ($trans['type'] === 'income') {
                                $total_income += $trans['amount'];
                            } else {
                                $total_expense += $trans['amount'];
                            }
                        ?>
                            <tr>
                                <td><?php echo format_date($trans['transaction_date']); ?></td>
                                <td><?php echo escape($trans['description'] ?: '-'); ?></td>
                                <td><?php echo escape($trans['category_name']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $trans['type']; ?>">
                                        <?php echo ucfirst($trans['type']); ?>
                                    </span>
                                </td>
                                <td style="font-weight: bold; color: <?php echo $trans['type'] === 'income' ? 'green' : 'red'; ?>">
                                    <?php echo $trans['type'] === 'income' ? '+' : '-'; ?>
                                    <?php echo format_currency($trans['amount']); ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="edit_expense.php?id=<?php echo $trans['id']; ?>"
                                            class="btn btn-secondary"
                                            style="padding: 0.5rem 1rem;">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="confirmDelete(<?php echo $trans['id']; ?>, '<?php echo escape($trans['description']); ?>')"
                                            class="btn btn-danger"
                                            style="padding: 0.5rem 1rem;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot style="background-color: var(--light-color); font-weight: bold;">
                        <tr>
                            <td colspan="4" style="text-align: right;">Summary:</td>
                            <td colspan="2">
                                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                                    <span style="color: green;">Income: <?php echo format_currency($total_income); ?></span>
                                    <span style="color: red;">Expense: <?php echo format_currency($total_expense); ?></span>
                                    <span style="color: var(--primary-color);">Net: <?php echo format_currency($total_income - $total_expense); ?></span>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-search-minus"></i>
                <h3>No Results Found</h3>
                <p>Try adjusting your search criteria</p>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>