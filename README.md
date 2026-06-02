# eloan  

A lightweight PHP loan‑management system that handles loan applications, approvals, and automated email notifications. The project bundles **PHPMailer** for reliable SMTP communication and includes a ready‑to‑import MySQL schema.

---

## Overview  

`eloan` provides a simple back‑end for managing personal or micro‑loans. It stores loan data in MySQL, processes business rules in PHP, and sends status updates to borrowers via email. The repository contains:

- **Database/eloan_db.sql** – schema and seed data.  
- **PHPMailer/** – full PHPMailer library (composer‑managed) with language packs and documentation.  

The application is framework‑agnostic and can be integrated into any PHP‑based web stack.

---

## Features  

| ✅ | Feature |
|---|---------|
| ✔️ | CRUD operations for loan records (create, view, update, delete). |
| ✔️ | Automatic calculation of interest, repayment schedule, and overdue fees. |
| ✔️ | Email notifications (application received, approval, repayment reminder) using PHPMailer. |
| ✔️ | Multilingual email templates – language files are included for over 30 locales. |
| ✔️ | Configurable SMTP settings (TLS/SSL, OAuth2 support). |
| ✔️ | Easy database migration via the provided `.sql` dump. |
| ✔️ | Composer‑based dependency management for PHPMailer. |

---

## Tech Stack  

| Component | Description |
|-----------|-------------|
| **PHP** (≥ 7.4) | Core language for business logic. |
| **MySQL** | Relational database for loan data. |
| **PHPMailer** | Robust email library (SMTP, OAuth2, multilingual). |
| **Composer** | Dependency manager (PHPMailer). |
| **HTML / CSS** | Front‑end (optional – not included in this core repo). |

---

## Installation  

> **Prerequisites**  
> - PHP 7.4+ with `openssl`, `mbstring`, and `pdo_mysql` extensions enabled.  
> - MySQL server.  
> - Composer installed globally (`composer --version`).  

1. **Clone the repository**  

   ```bash
   git clone https://github.com/yourusername/eloan.git
   cd eloan
   ```

2. **Install PHPMailer via Composer**  

   ```bash
   composer install
   ```

   This reads `PHPMailer/composer.json` and pulls the required packages.

3. **Create the database**  

   ```bash
   mysql -u root -p < Database/eloan_db.sql
   ```

   Adjust the credentials in `config.php` (see next step).

4. **Configure the application**  

   Copy the sample config and edit the values:

   ```bash
   cp config.sample.php config.php
   ```

   ```php
   // config.php (excerpt)
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'eloan');
   define('DB_USER', 'your_db_user');
   define('DB_PASS', 'your_db_password');

   // SMTP settings
   define('SMTP_HOST', 'smtp.example.com');
   define('SMTP_PORT', 587);
   define('SMTP_USER', 'your_email@example.com');
   define('SMTP_PASS', 'YOUR_OWN_API_KEY'); // replace with real password or OAuth token
   define('SMTP_SECURE', 'tls');
   ```

5. **Run the application**  

   - For a quick test, start PHP’s built‑in server:

     ```bash
     php -S localhost:8000 -t public
     ```

   - Visit `http://localhost:8000` in your browser.

---

## Usage  

Below is a minimal example of how to send