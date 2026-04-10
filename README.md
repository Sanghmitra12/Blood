# 🩸 BloodLink Campus — Blood Donation Management System
### University / Campus PHP Web Project

---

## 📁 Project Structure

```
blood-donation/
├── index.php                  ← Home page
├── login.php                  ← User login
├── register.php               ← Donor registration
├── logout.php                 ← Session logout
├── database.sql               ← MySQL database setup
├── .htaccess                  ← Apache security config
│
├── includes/
│   ├── db.php                 ← Database connection
│   ├── auth.php               ← Session & auth helpers
│   ├── header.php             ← Navigation header
│   └── footer.php            ← Footer template
│
├── assets/
│   ├── css/style.css          ← Full design system
│   └── js/main.js             ← Frontend JS
│
├── pages/                     ← Public pages
│   ├── donors.php             ← Search & find donors
│   ├── request.php            ← Submit blood request
│   ├── events.php             ← Campus events
│   └── inventory.php         ← Blood bank stock
│
├── donor/
│   └── profile.php           ← Donor profile & history
│
└── admin/
    ├── dashboard.php          ← Admin overview
    ├── donors.php             ← Manage all donors
    ├── requests.php           ← Manage blood requests
    ├── donations.php          ← Log & track donations
    ├── inventory.php          ← Update blood stock
    └── events.php            ← Create & manage events
```

---

## ⚙️ Setup Instructions

### Requirements
- PHP 7.4+ or 8.x
- MySQL 5.7+ or MariaDB
- Apache with mod_rewrite enabled
- XAMPP / WAMP / LAMP stack

### Step 1 — Database Setup
```sql
-- Open phpMyAdmin or MySQL CLI
source database.sql;
-- OR import the database.sql file via phpMyAdmin Import tab
```

### Step 2 — Configure Database
Edit `includes/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_username');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'blood_donation_db');
```

### Step 3 — Place Project
- Copy the `blood-donation/` folder to your server's web root:
  - XAMPP: `C:/xampp/htdocs/blood-donation/`
  - Linux: `/var/www/html/blood-donation/`

### Step 4 — Access the App
- Site: `http://localhost/blood-donation/`
- Admin: `http://localhost/blood-donation/admin/dashboard.php`

---

## 🔐 Default Login Credentials

| Role  | Email                    | Password |
|-------|--------------------------|----------|
| Admin | admin@university.edu     | password |
| Donor | rahul@student.edu        | password |
| Donor | priya@student.edu        | password |

> ⚠️ Change passwords immediately in production!

---

## ✅ Features

### Public Features
- 🏠 **Home Page** — Stats, blood inventory, recent donors, events
- 🔍 **Find Donors** — Search by blood group, department, name
- 🩸 **Request Blood** — Submit urgent/critical blood requests
- 📅 **Events** — View and register for donation drives
- 📊 **Blood Inventory** — Real-time stock levels by blood group
- 👤 **Registration** — Donor account creation with validation
- 🔒 **Login/Logout** — Secure session-based authentication

### Donor Dashboard
- Edit profile, phone, department, address
- Toggle availability status
- Track last donation date (eligibility checker)
- View personal donation history

### Admin Panel
- 📊 Dashboard with live statistics
- 👥 Manage all donors (toggle availability, delete)
- 🩸 Blood requests management (fulfill/cancel/delete)
- 📝 Log donations (auto-updates inventory + last donation date)
- 🏥 Update blood bank inventory
- 📅 Create, edit, delete campus events
- View event registrations count

---

## 🎨 Design Highlights
- Editorial / Medical aesthetic — deep crimson + ivory + charcoal
- Playfair Display (headings) + DM Sans (body)
- Fully responsive (mobile-first)
- Sticky navigation
- Flash messages with auto-dismiss
- CSS animations on stats counter
- Blood group color-coded cards
- Status and urgency badges

---

## 🔒 Security Features
- Password hashing with `password_hash()` (bcrypt)
- SQL injection prevention via `real_escape_string()` + prepared statements
- Session-based authentication
- Role-based access control (donor / admin)
- `.htaccess` protection for includes and sensitive files
- Input sanitization on all forms

---

## 📝 Blood Eligibility Note
- Minimum age: 18 years
- Minimum gap between donations: 90 days (3 months)
- The system shows eligibility status on the donor profile

---

## 👨‍💻 Tech Stack
- **Backend**: PHP (procedural)
- **Database**: MySQL with MySQLi
- **Frontend**: HTML5, CSS3, Vanilla JS
- **Icons**: Font Awesome 6
- **Fonts**: Google Fonts (Playfair Display, DM Sans)
- **Server**: Apache with .htaccess

---

*Made with ♥ for saving campus lives — University Blood Donation Management System*
