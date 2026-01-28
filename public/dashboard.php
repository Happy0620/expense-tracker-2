<?php

/**
 * Dashboard - Main Page
 */
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

require_login();

$user_id = get_user_id();
$page_title = 'Dashboard';

// Get current month and year
$current_month = date('n');
$current_year = date('Y');

// Get monthly summary
$summary = get_monthly_summary($pdo, $user_id, $current_year, $current_month);
$category_expenses = get_category_expenses($pdo, $user_id, $current_year, $current_month);

// Calculate percentages for category expenses
$total_expense = $summary['expense'];
foreach ($category_expenses as &$cat) {
    $cat['percentage'] = calculate_percentage($cat['total'], $total_expense);
}

// Get recent transactions
try {
    $stmt = $pdo->prepare("
        SELECT t.*, c.name as category_name
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ?
        ORDER BY t.transaction_date DESC, t.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$user_id]);
    $recent_transactions = $stmt->fetchAll();
} catch (PDOException $e) {
    $recent_transactions = [];
}

require_once '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <span><i class="fas fa-chart-line"></i> Dashboard Overview</span>
        <span style="font-size: 1rem; font-weight: normal;">
            <?php echo get_month_name($current_month) . ' ' . $current_year; ?>
        </span>
    </div>
</div>

<!-- Monthly Statistics -->
<div class="stats-grid">
    <div class="stat-card income">
        <div class="stat-icon"><i class="fas fa-arrow-up"></i></div>
        <div class="stat-label">Total Income</div>
        <div class="stat-value"><?php echo format_currency($summary['income']); ?></div>
    </div>

    <div class="stat-card expense">
        <div class="stat-icon"><i class="fas fa-arrow-down"></i></div>
        <div class="stat-label">Total Expenses</div>
        <div class="stat-value"><?php echo format_currency($summary['expense']); ?></div>
    </div>

    <div class="stat-card savings">
        <div class="stat-icon"><i class="fas fa-piggy-bank"></i></div>
        <div class="stat-label">Savings</div>
        <div class="stat-value"><?php echo format_currency($summary['savings']); ?></div>
    </div>
</div>

<!-- Month/Year Selector for AJAX -->
<div class="card">
    <h3><i class="fas fa-calendar"></i> View Different Month</h3>
    <div class="form-row">
        <div class="form-group">
            <label for="summary-month">Month</label>
            <select id="summary-month" class="form-control">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?php echo $m; ?>" <?php echo $m == $current_month ? 'selected' : ''; ?>>
                        <?php echo get_month_name($m); ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="summary-year">Year</label>
            <select id="summary-year" class="form-control">
                <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo $y == $current_year ? 'selected' : ''; ?>>
                        <?php echo $y; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
    </div>
    <div id="monthly-summary"></div>
</div>

<!-- Category-wise Expenses -->
<?php if (!empty($category_expenses)): ?>
    <div class="card">
        <h3><i class="fas fa-chart-pie"></i> Category-wise Expenses</h3>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($category_expenses as $cat): ?>
                        <tr>
                            <td><?php echo escape($cat['category']); ?></td>
                            <td><?php echo format_currency($cat['total']); ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="flex: 1; background: #e5e7eb; border-radius: 10px; height: 10px;">
                                        <div style="background: var(--primary-color); width: <?php echo $cat['percentage']; ?>%; height: 100%; border-radius: 10px;"></div>
                                    </div>
                                    <span><?php echo $cat['percentage']; ?>%</span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- Recent Transactions -->
<div class="card">
    <div class="card-header">
        <span><i class="fas fa-history"></i> Recent Transactions</span>
        <a href="add_expense.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New
        </a>
    </div>

    <?php if (!empty($recent_transactions)): ?>
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
                    <?php foreach ($recent_transactions as $trans): ?>
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
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>No Transactions Yet</h3>
            <p>Start by adding your first transaction</p>
            <a href="add_expense.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Transaction
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>