/**
 * Main JavaScript File
 * Handles AJAX functionality and UI interactions
 */

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});

/**
 * AJAX: Username availability checker
 * Used in registration form
 */
function checkUsername() {
    const usernameInput = document.getElementById('username');
    const feedbackDiv = document.getElementById('username-feedback');
    
    if (!usernameInput || !feedbackDiv) return;
    
    const username = usernameInput.value.trim();
    
    if (username.length < 3) {
        feedbackDiv.innerHTML = '';
        return;
    }
    
    fetch('ajax/check_username.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'username=' + encodeURIComponent(username)
    })
    .then(response => response.json())
    .then(data => {
        if (data.available) {
            feedbackDiv.innerHTML = '<span style="color: green;"><i class="fas fa-check-circle"></i> Username available</span>';
            usernameInput.style.borderColor = 'green';
        } else {
            feedbackDiv.innerHTML = '<span style="color: red;"><i class="fas fa-times-circle"></i> Username already taken</span>';
            usernameInput.style.borderColor = 'red';
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

/**
 * AJAX: Category autocomplete
 * Used in search form
 */
function setupCategoryAutocomplete() {
    const searchInput = document.getElementById('category-search');
    const suggestionsDiv = document.getElementById('category-suggestions');
    
    if (!searchInput || !suggestionsDiv) return;
    
    let timeout = null;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(timeout);
        
        const query = this.value.trim();
        
        if (query.length < 2) {
            suggestionsDiv.innerHTML = '';
            suggestionsDiv.style.display = 'none';
            return;
        }
        
        timeout = setTimeout(() => {
            fetch('ajax/autocomplete_category.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'query=' + encodeURIComponent(query)
            })
            .then(response => response.json())
            .then(data => {
                if (data.categories && data.categories.length > 0) {
                    suggestionsDiv.innerHTML = data.categories
                        .map(cat => `<div class="autocomplete-suggestion" onclick="selectCategory('${cat}')">${cat}</div>`)
                        .join('');
                    suggestionsDiv.style.display = 'block';
                } else {
                    suggestionsDiv.innerHTML = '<div class="autocomplete-suggestion">No categories found</div>';
                    suggestionsDiv.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }, 300);
    });
    
    // Close suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
            suggestionsDiv.style.display = 'none';
        }
    });
}

function selectCategory(category) {
    const searchInput = document.getElementById('category-search');
    const suggestionsDiv = document.getElementById('category-suggestions');
    
    if (searchInput) {
        searchInput.value = category;
        suggestionsDiv.style.display = 'none';
    }
}

/**
 * AJAX: Load monthly summary without page reload
 */
function loadMonthlySummary() {
    const monthSelect = document.getElementById('summary-month');
    const yearSelect = document.getElementById('summary-year');
    const summaryDiv = document.getElementById('monthly-summary');
    
    if (!monthSelect || !yearSelect || !summaryDiv) return;
    
    const month = monthSelect.value;
    const year = yearSelect.value;
    
    summaryDiv.innerHTML = '<div class="spinner"></div>';
    
    fetch('ajax/get_monthly_summary.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `month=${month}&year=${year}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            summaryDiv.innerHTML = `
                <div class="stats-grid">
                    <div class="stat-card income">
                        <div class="stat-icon"><i class="fas fa-arrow-up"></i></div>
                        <div class="stat-label">Total Income</div>
                        <div class="stat-value">Rs. ${parseFloat(data.income).toFixed(2)}</div>
                    </div>
                    <div class="stat-card expense">
                        <div class="stat-icon"><i class="fas fa-arrow-down"></i></div>
                        <div class="stat-label">Total Expenses</div>
                        <div class="stat-value">Rs. ${parseFloat(data.expense).toFixed(2)}</div>
                    </div>
                    <div class="stat-card savings">
                        <div class="stat-icon"><i class="fas fa-piggy-bank"></i></div>
                        <div class="stat-label">Savings</div>
                        <div class="stat-value">Rs. ${parseFloat(data.savings).toFixed(2)}</div>
                    </div>
                </div>
                ${data.categories.length > 0 ? `
                    <div class="card">
                        <h3>Category-wise Expenses</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.categories.map(cat => `
                                    <tr>
                                        <td>${cat.category}</td>
                                        <td>Rs. ${parseFloat(cat.total).toFixed(2)}</td>
                                        <td>${cat.percentage}%</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                ` : '<p class="empty-state">No expenses recorded for this period.</p>'}
            `;
        } else {
            summaryDiv.innerHTML = '<p class="alert alert-error">Failed to load summary</p>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        summaryDiv.innerHTML = '<p class="alert alert-error">An error occurred</p>';
    });
}

/**
 * Confirm delete action
 */
function confirmDelete(id, description) {
    const message = description ? 
        `Are you sure you want to delete "${description}"?` : 
        'Are you sure you want to delete this transaction?';
    
    if (confirm(message)) {
        window.location.href = `delete_expense.php?id=${id}`;
    }
}

/**
 * Form validation
 */
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = 'red';
            isValid = false;
        } else {
            field.style.borderColor = '';
        }
    });
    
    return isValid;
}

/**
 * Initialize all features on page load
 */
document.addEventListener('DOMContentLoaded', function() {
    setupCategoryAutocomplete();
    
    // Setup username checker if on register page
    const usernameInput = document.getElementById('username');
    if (usernameInput) {
        usernameInput.addEventListener('input', checkUsername);
    }
    
    // Setup monthly summary loader if on dashboard
    const monthSelect = document.getElementById('summary-month');
    const yearSelect = document.getElementById('summary-year');
    if (monthSelect && yearSelect) {
        monthSelect.addEventListener('change', loadMonthlySummary);
        yearSelect.addEventListener('change', loadMonthlySummary);
    }
});