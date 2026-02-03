
1

Automatic Zoom
# Personal Expense Tracker 
 
**Student Name:** [Milan Sherpa]   
**Student ID:** NP03CS4A240214   
**Course:** 5CS045 - Full Stack Development 
**Assignment:** Task 2  
**Institution:** Herald College Kathmandu 
 
--- 
 
##  Live Demo 
 
**Website URL:** http://student.heraldcollege.edu.np/~NP03CS4A240214/ 
 
**Direct Login:** http://student.heraldcollege.edu.np/~NP03CS4A240214/login.php 
 
--- 
 
## Demo Account Credentials 
 
**Username:** `happy`   
**Password:** `happy1` 
 
Feel free to use this account to explore all features, or register a new account. 
 
--- 
 
##  Project Overview 
 
A comprehensive web-based Personal Expense Tracker that allows users to manage their daily 
income and expenses. The system provides complete CRUD operations, advanced search 
functionality, real-time AJAX features, and robust security measures. 
 
--- 
 
##  Features Implemented 
 
###  **Mandatory Requirements** 
 
#### **1. Full CRUD Operations** 
- ✓ **Create:** Add new income/expense transactions 
- ✓ **Read:** View all transactions on dashboard 
- ✓ **Update:** Edit existing transactions with pre-filled forms 
- ✓ **Delete:** Remove transactions with confirmation prompts 
 
#### **2. Session-Based Authentication** 
- ✓ User registration with validation 
- ✓ Secure login system 
- ✓ Password hashing using PHP `password_hash()` 
- ✓ Session management with security flags 
- ✓ Automatic redirect for unauthorized access 
 
#### **3. Advanced Search Functionality** 
- ✓ Search by description/keywords 
- ✓ Filter by transaction type (Income/Expense) 
- ✓ Filter by category 
- ✓ Filter by date range (from-to dates) 
- ✓ Filter by amount range (min-max) 
- ✓ **Multi-criteria search** (combine all filters) 
 
#### **4. Security Implementation** 
- ✓ **SQL Injection Prevention:** PDO prepared statements used throughout 
- ✓ **XSS Prevention:** `htmlspecialchars()` for all output 
- ✓ **CSRF Protection:** Token validation for all POST requests 
- ✓ **Password Security:** Bcrypt hashing with `password_hash()` 
- ✓ **Session Security:** HTTP-only cookies, secure flags 
- ✓ Input validation (client-side and server-side) 
 
#### **5. AJAX Features (3 Implementations)** 
- ✓ **Username Availability Checker:** Real-time validation during registration 
- ✓ **Category Autocomplete:** Live search suggestions 
- ✓ **Monthly Summary Loader:** Dynamic data loading without page reload 
 
###  **Additional Features** 
 
- Monthly financial summary (Income, Expenses, Savings) 
- Category-wise expense breakdown with percentages 
- Recent transactions view 
- Responsive design (mobile-friendly) 
- Flash message system 
- Modern UI with Font Awesome icons 
- Date validation and input restrictions 
 
--- 
 
## Technologies Used 
 
### **Backend** 
- PHP 7.4+ (Core PHP, no frameworks due to server limitations) 
- MySQL 5.7+ with PDO 
- Session handling 
 
### **Frontend** 
- HTML5 
- CSS3 (Custom styling with CSS variables) 
- JavaScript (Vanilla JS with Fetch API) 
- Font Awesome 6.4 (Icons) 
 
### **Security** 
- PDO Prepared Statements 
- Password Hashing (Bcrypt) 
- CSRF Tokens 
- Output Escaping 
- Session Security 
 
--- 
 
## Project Structure 
 
``` 
expense_tracker/ 
├── config/ 
│   └── database.php              # Database connection 
├── includes/ 
│   ├── header.php                # Page header template 
│   ├── footer.php                # Page footer template 
│   ├── functions.php             # Helper functions 
│   └── session.php               # Session management 
├── public/ 
│   ├── index.php                 # Landing/redirect page 
│   ├── login.php                 # User login 
│   ├── register.php              # User registration 
│   ├── logout.php                # Logout handler 
│   ├── dashboard.php             # Main dashboard 
│   ├── add_expense.php           # Add transaction 
│   ├── edit_expense.php          # Edit transaction 
│   ├── delete_expense.php        # Delete transaction 
│   ├── search.php                # Advanced search 
│   ├── ajax/ 
│   │   ├── check_username.php    # AJAX: Username checker 
│   │   ├── autocomplete_category.php  # AJAX: Autocomplete 
│   │   └── get_monthly_summary.php    # AJAX: Monthly data 
│   └── assets/ 
│       ├── css/ 
│       │   └── style.css         # Main stylesheet 
│       └── js/ 
│           └── main.js           # JavaScript/AJAX 
├── sql/ 
│   └── expense_tracker.sql       # Database schema 
└── README.md                     # This file 
``` 
 
--- 
 
##         Database Schema 
 
### **Database Name:** `NP03CS4A240214` 
 
### **Tables:** 
 
#### **1. users** 
- `id` (Primary Key) 
- `username` (Unique) 
- `email` (Unique) 
- `password` (Hashed) 
- `created_at` 
 
#### **2. categories** 
- `id` (Primary Key) 
- `name` (Category name) 
- `type` (expense/income) 
- `created_at` 
 
#### **3. transactions** 
- `id` (Primary Key) 
- `user_id` (Foreign Key → users.id) 
- `category_id` (Foreign Key → categories.id) 
- `amount` (DECIMAL) 
- `type` (expense/income) 
- `description` (TEXT) 
- `transaction_date` (DATE) 
- `created_at` 
- `updated_at` 
 
--- 
 
##  Security Features 
 
### **1. SQL Injection Prevention** 
All database queries use PDO prepared statements: 
```php 
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?"); 
$stmt->execute([$username]); 
``` 
 
### **2. XSS Prevention** 
All output is escaped: 
```php 
echo htmlspecialchars($data, ENT_QUOTES, 'UTF-8'); 
``` 
 
### **3. CSRF Protection** 
All POST forms include and verify CSRF tokens: 
```php 
<input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>"> 
``` 
 
### **4. Password Security** 
Passwords are hashed using PHP's bcrypt: 
```php 
$hashed = password_hash($password, PASSWORD_DEFAULT); 
``` 
 
### **5. Session Security** 
Sessions use secure configuration: 
```php 
session_start([ 
    'cookie_httponly' => true, 
    'use_strict_mode' => true 
]); 
``` 
 
--- 
 
##  Deployment & Setup 
 
### **Live Website** 
The application is deployed on Herald College server and accessible at: 
``` 
http://student.heraldcollege.edu.np/~NP03CS4A240214/ 
``` 
 
### **Server Configuration** 
- **Database Host:** localhost 
- **Database Name:** NP03CS4A240214 
- **Database User:** NP03CS4A240214 
- **Database Password:** RkXAAQWwym 
- **Server:** Herald College Student Server (10.80.0.250) 
 
### **Database Configuration** 
The `config/database.php` file is configured for the Herald College server: 
 
```php 
define('DB_HOST', 'localhost'); 
define('DB_NAME', 'NP03CS4A240214'); 
define('DB_USER', 'NP03CS4A240214'); 
define('DB_PASS', 'RkXAAQWwym'); 
define('DB_CHARSET', 'utf8mb4'); 
``` 
 
### **Database Setup** 
The database has been imported from `sql/expense_tracker.sql` and includes: 
- 13 default categories (8 expense, 5 income) 
- 1 demo user account (username: happy, password: happy1) 
- All necessary table structures (users, categories, transactions) 
 
### **Local Development Setup (Optional)** 
If running locally for development: 
1. Import `sql/expense_tracker.sql` to your local MySQL 
2. Update `config/database.php`: 
   ```php 
   define('DB_HOST', 'localhost'); 
   define('DB_NAME', 'expense_tracker'); 
   define('DB_USER', 'root'); 
   define('DB_PASS', '');  // Empty for XAMPP/WAMP 
   ``` 
3. Access via: `http://localhost/expense_tracker/public/` 
 
--- 
 
##  Testing Guide 
 
### **Test CRUD Operations:** 
1. Login with demo account 
2. Add a new transaction (Food expense, Rs. 500) 
3. Edit the transaction (change amount to Rs. 750) 
4. Delete the transaction 
5. Verify all operations work correctly 
 
### **Test Search Functionality:** 
1. Add multiple transactions with different: 
   - Types (income/expense) 
   - Categories 
   - Dates 
   - Amounts 
2. Test each search filter individually 
3. Test combined filters (all criteria at once) 
 
### **Test AJAX Features:** 
1. **Registration page:** Type username → See real-time availability check 
2. **Search page:** Type in search box → See autocomplete suggestions 
3. **Dashboard:** Change month/year dropdown → Data updates without reload 
 
### **Test Security:** 
1. **SQL Injection:** Try entering `' OR '1'='1` in login → Should fail safely 
2. **XSS:** Try entering `<script>alert('XSS')</script>` in description → Should display as text 
3. **Session:** Logout and try accessing dashboard URL → Should redirect to login 
4. **CSRF:** Inspect form, change token value → Should reject submission 
 
--- 
 
##  Features Demonstration Script 
 
### **For Tutor Presentation:** 
 
1. **Authentication** (2 mins) 
   - Show registration with username checker 
   - Login with demo account 
 
2. **CRUD Operations** (3 mins) 
   - Create: Add expense 
   - Read: View on dashboard 
   - Update: Edit transaction 
   - Delete: Remove transaction 
 
3. **Advanced Search** (2 mins) 
   - Single filter search 
   - Multi-criteria search 
 
4. **AJAX Features** (2 mins) 
   - Username checker (no reload) 
   - Autocomplete (no reload) 
   - Monthly summary (no reload) 
 
5. **Security** (3 mins) 
   - Show SQL injection test 
   - Show XSS prevention 
   - Show CSRF token in code 
   - Explain prepared statements 
 
--- 
 
## Assignment Requirements Checklist 
 
| Requirement | Status | Implementation | 
|-------------|--------|----------------| 
| PHP + MySQL |     | Core PHP with MySQL database | 
| Hosted on server |     | Herald College server | 
| CRUD operations |     | Full Create, Read, Update, Delete | 
| Search functionality |     | Advanced multi-criteria search | 
| SQL Injection prevention |     | PDO prepared statements | 
| XSS prevention |     | htmlspecialchars() output escaping | 
| CSRF protection |     | Token validation | 
| AJAX features |     | 3 AJAX implementations | 
| Session authentication |     | Full login system | 
 
--- 
 
##  Known Issues 
 
None currently identified. All features tested and working correctly. 
 
--- 
 
##     Contact Information 
 
**Student:** [Milan sherpa]   
**Email:** [NP03CS4A240214@heraldcollege.edu.np]   
**Student ID:** NP03CS4A240214 
 
--- 
 
##  References 
 
- PHP Documentation: https://www.php.net/manual/en/ 
- PDO Documentation: https://www.php.net/manual/en/book.pdo.php 
- OWASP Security Guidelines: https://owasp.org/ 
- Font Awesome Icons: https://fontawesome.com/ 
 
--- 
 
 
 
 
