# 🧩 Mini MVC Framework – Simple PHP Core

A lightweight, easy-to-understand MVC framework built with vanilla PHP. Perfect for learning MVC concepts or building small to medium web applications without any external dependencies (except PHP itself).

---

## Table of Contents

1. [Features](#features)
2. [Requirements](#requirements)
3. [Installation](#installation)
4. [Configuration](#configuration)
5. [Directory Structure](#directory-structure)
6. [How It Works](#how-it-works)
7. [Routing & URLs](#routing--urls)
8. [Controllers](#controllers)
9. [Views](#views)
10. [Models & Database](#models--database)
11. [Helper Functions](#helper-functions)
12. [Error Handling](#error-handling)
13. [Security Features](#security-features)
14. [Running the Application](#running-the-application)

---

## ✨ Features

- 🔀 **Simple URL Routing** – based on `$_GET['url']` parameter (clean URLs via .htaccess)
- 🎮 **Controllers** – handle business logic and user requests
- 🎨 **Views** – plain PHP templates with partial includes
- 🗄️ **Database** – PDO with prepared statements (MySQL)
- 🧰 **Global Helper Functions** – `dd()`, `redirect()`, `safeEcho()`, and more
- 📊 **Data Export** – Excel (.xls), Word (.doc), CSV, SQL dump
- 🔒 **CSRF Protection** – token generation and validation
- 🔐 **Secure Password Hashing** – Argon2id support
- 📁 **File Download** – secure file download helper
- 🛡️ **Basic Security Headers** – X-Frame-Options, X-XSS-Protection, etc.
- ⚡ **Lightweight** – no external Composer dependencies required

---

## ⚙️ Requirements

- PHP >= 7.4 (PHP 8+ recommended)
- MySQL / MariaDB
- Web server (Apache / Nginx) with URL rewriting

---

## 🧰 Installation

```bash
# 1. Clone or copy the project into your web root
cd your-project-folder

# 2. Make sure the following directories exist and are writable (if needed):
#    - No special permissions required for basic operation

# 3. Configure your database in app/libs/Config.php

# 4. Point your document root to the `public` folder

# 5. Create a .htaccess file in the public folder (see below)


### Sample .htaccess (Apache)

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
```

---

## ⚙️ Configuration

All configuration is done in `app/libs/Config.php`.

```php
class Config
{
    // Database
    public const DB_LOCALHOST = "localhost";
    public const DB_USER = "root";
    public const DB_PASSWORD = "";
    public const DB_NAME = "mvc";

    // Application Paths
    public const APPROOT = "../app/";
    public const PUBLICROOT = "/";
    public const URLROOT = "http://localhost/mvc/";
}
```

### Error Mode (Development / Production)

In `app/libs/bootstrap.php`, change the `$WEB` variable:

```php
$WEB = "off";  // "off" = show all errors (development)
$WEB = "on";   // "on" = hide errors (production)
```

---

## 📁 Directory Structure

```
project-root/
├── app/
│   ├── controllers/         # Application controllers
│   │   ├── Errors.php       # Error handler controller
│   │   └── Index.php        # Default controller
│   ├── libs/                # Core framework files
│   │   ├── bootstrap.php    # Auto-loader, error settings, session start
│   │   ├── Config.php       # Configuration constants
│   │   ├── Controller.php   # Base controller class
│   │   ├── Core.php         # Router / Front controller
│   │   ├── Database.php     # Database connection class
│   │   └── Helpers.php      # Global helper functions
│   ├── models/              # Database models
│   │   └── Mvc.php          # Example model
│   └── views/               # View templates
│       ├── inc.end.php      # Footer partial
│       ├── inc.start.php    # Header partial
│       └── index.php        # Home view
├── public/
│   ├── assets/              # CSS, JS, images (optional)
│   ├── index.php            # Front controller (entry point)
│   └── .htaccess            # URL rewriting rules
└── README.md
```

---

## 🚀 How It Works

1. All requests are routed through `public/index.php`.
2. The `bootstrap.php` file sets up error handling, session, headers, and the auto-loader.
3. The `Core` class parses the URL from `$_GET['url']` and determines:
   - Which controller to load
   - Which method to call
   - What parameters to pass
4. The controller method renders a view or processes data.
5. Views can include partials using `require_view()`.

---

## 🔗 Routing & URLs

The framework uses a simple routing system based on the URL segment.

### URL Structure

```
http://yourdomain.com/controller/method/param1/param2/...
```

### Examples

| URL | Controller | Method | Parameters |
|-----|------------|--------|------------|
| `/` | `Index` | `index` | `[]` |
| `/user` | `User` | `index` | `[]` |
| `/user/show/5` | `User` | `show` | `[5]` |
| `/product/edit/10/active` | `Product` | `edit` | `[10, "active"]` |

### How It Works

1. The first segment is the controller name (capitalized).
2. The second segment is the method name.
3. Remaining segments are passed as parameters to the method.

> **Note:** Controllers are stored in `app/controllers/` and must have the same name as the file (e.g., `User.php` for `User` controller).

### Default Controller

If no URL segment is provided, the `Index` controller with `index` method is loaded.

---

## 🎮 Controllers

Controllers handle the logic of your application. They extend the base `Controller` class.

### Example Controller

```php
<?php

class User extends Controller
{
    public function index()
    {
        // Show all users
        $userModel = Controller::model("User");
        $users = $userModel->getAll();
        return Controller::view("user.list", compact("users"));
    }

    public function show($id)
    {
        // Show a single user by ID
        $userModel = Controller::model("User");
        $user = $userModel->find($id);
        return Controller::view("user.profile", compact("user"));
    }

    public function create()
    {
        Controller::post(); // Ensures request method is POST
        // Process form submission
        redirect("/users");
    }
}
```

### Base Controller Methods

| Method | Description |
|--------|-------------|
| `Controller::model($name, $route = [])` | Load and instantiate a model (supports subdirectories via dot notation) |
| `Controller::view($view, $data = [])` | Render a view (dot notation for subdirectories) |
| `Controller::post()` | Validate that the request method is POST; returns 403 if not |
| `Controller::errors($code)` | Show error page (403, 404, 500) |

---

## 🎨 Views

Views are plain PHP files stored in `app/views/`. They can contain HTML and PHP.

### View Example (`app/views/user/list.php`)

```php
<?php require_view("inc.start"); ?>
<h1>User List</h1>
<ul>
    <?php foreach ($users as $user): ?>
        <li><?php safeEcho($user->name); ?></li>
    <?php endforeach; ?>
</ul>
<?php require_view("inc.end"); ?>
```

### View Helpers

| Function | Description |
|----------|-------------|
| `require_view($path)` | Include a view partial (dot notation) |
| `safeEcho($value)` | Print HTML-escaped value |
| `urlPath($path)` | Print full URL using `URLROOT` |
| `publicPath($path)` | Print relative path starting with `./` |

### Dot Notation

Views use dot notation for subdirectories:

```php
Controller::view("admin.dashboard");  // app/views/admin/dashboard.php
require_view("inc.header");           // app/views/inc/header.php
```

---

## 🗄️ Models & Database

Models represent database tables. They use the `Database` class for PDO connections.

### Database Configuration

Set credentials in `app/libs/Config.php`:

```php
public const DB_LOCALHOST = "localhost";
public const DB_USER = "root";
public const DB_PASSWORD = "";
public const DB_NAME = "mydatabase";
```

### Example Model

```php
<?php

class User
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function getAll()
    {
        $stmt = $this->db->db->prepare("SELECT * FROM users ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function find($id)
    {
        $stmt = $this->db->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function create($data)
    {
        $stmt = $this->db->db->prepare("INSERT INTO users (name, email) VALUES (:name, :email)");
        return $stmt->execute($data);
    }
}
```

### Using Models in Controllers

```php
$userModel = Controller::model("User");
$users = $userModel->getAll();
```

### Database Connection Helper

```php
$conn = getDbConnection(); // Returns PDO instance
```

---

## 🛠️ Helper Functions

All helpers are defined in `app/libs/Helpers.php`.

### General Helpers

| Function | Description |
|----------|-------------|
| `dd($content)` | Dump variable and die (beautiful styled output) |
| `redirect($path)` | Redirect to relative URL (appends `URLROOT`) |
| `pdf()` | Trigger browser print dialog |
| `periodPath($path)` | Convert dot notation to directory separators |

### Security Helpers

| Function | Description |
|----------|-------------|
| `MakeSecureHash($password)` | Hash password using Argon2id |
| `CheckSecureHashed($hash, $plain)` | Verify password against hash |
| `safeEcho($value)` | Print HTML-escaped string |
| `generateToken()` | Generate and store CSRF token in session |
| `validateToken()` | Validate CSRF token from POST request |

### File Helpers

| Function | Description |
|----------|-------------|
| `download($path, $name)` | Securely download a file (checks file is inside `PUBLICROOT`) |

### Data Export Helpers

| Function | Description |
|----------|-------------|
| `excel($tableName)` | Export entire table as Excel (.xls) |
| `word($tableName)` | Export entire table as Word (.doc) |
| `csv($tableName)` | Export entire table as CSV |
| `tableExport($tableName)` | Export table as SQL (CREATE + INSERT statements) |

### Export Example

```php
// In any controller or route
excel("users");      // Downloads users_export_2025-01-01.xls
word("products");    // Downloads products_export_2025-01-01.doc
csv("orders");       // Downloads orders_export_2025-01-01.csv
tableExport("logs"); // Downloads logs_export_2025-01-01.sql
```

---

## ⚠️ Error Handling

### Error Controller

Errors are handled by `app/controllers/Errors.php`. The `Errors::error($code)` method displays simple error messages:

| Code | Message |
|------|---------|
| 403 | `ERROR || YOU DONT HAVE PERMISSION || 403` |
| 404 | `ERROR || NOT FOUND || 404` |
| 500 | `ERROR || CONNECTION || 500` |

### Manual Error Trigger

```php
Controller::errors(403); // Forbidden
Controller::errors(404); // Not Found
Controller::errors(500); // Internal Server Error
```

### Development Mode

When `$WEB = "off"` in `bootstrap.php`:
- All PHP errors are displayed on screen
- Useful for debugging

### Production Mode

When `$WEB = "on"`:
- Display errors are turned off
- Only your custom error messages are shown

---

## 🔒 Security Features

- **Prepared Statements** – All database queries use PDO prepared statements (SQL injection protection)
- **Output Escaping** – `safeEcho()` uses `htmlspecialchars()` (XSS protection)
- **CSRF Protection** – Built-in token generation and validation
- **Secure Password Hashing** – Argon2id algorithm
- **Security Headers** – Sent automatically:
  - `X-Frame-Options: SAMEORIGIN`
  - `X-Content-Type-Options: nosniff`
  - `X-XSS-Protection: 1; mode=block`
  - `Referrer-Policy: no-referrer-when-downgrade`
- **Request Method Validation** – `Controller::post()` ensures POST requests only
- **Secure File Download** – `download()` validates file paths

### CSRF Protection Example

```php
// In your form view
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo generateToken(); ?>">
    <!-- your form fields -->
</form>

// In your controller
public function store()
{
    Controller::post();
    validateToken(); // Returns 403 or redirects back on failure
    // Process form...
}
```

---

## 🚀 Running the Application

1. **Configure your web server** – Set document root to the `public` folder.

2. **Configure database** – Update `Config.php` with your database credentials.

3. **Create a database table** (example for the `Mvc` model):
   ```sql
   CREATE TABLE `mvc` (
       `id` int(11) NOT NULL AUTO_INCREMENT,
       `title` varchar(255) DEFAULT NULL,
       PRIMARY KEY (`id`)
   );
   ```

4. **Set URL Rewriting** – Ensure `.htaccess` (Apache) or equivalent (Nginx) is configured.

5. **Access the application** – Open `http://localhost/mvc/` (or your configured URL).

The default home page will display: **"Welcome To My Mini Mvc"**

### Testing the Framework

- **Home page** – `/` or `/index/index`
- **Custom controller** – Create `User.php` in `app/controllers/`
- **Database test** – The `Mvc` model fetches from the `mvc` table

---

## 📝 Creating a New Controller

1. Create a file in `app/controllers/` (e.g., `Product.php`):

```php
<?php

class Product extends Controller
{
    public function index()
    {
        return Controller::view("product.list");
    }

    public function show($id)
    {
        echo "Showing product #" . htmlspecialchars($id);
    }
}
```

2. Access it at: `http://yourdomain.com/product/show/5`

---

## 📄 License

MIT – free for personal and commercial projects.

---

## 🙌 Credits

Built with simplicity and learning in mind. No external dependencies – just pure PHP power!
```