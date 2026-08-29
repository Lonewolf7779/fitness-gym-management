# IRONCORE — Fitness & Gym Management System

A commercial-grade, high-performance **Fitness & Gym Management Web Application** engineered with pure native **HTML5, CSS3, Vanilla JavaScript, PHP 8+**, and **MySQL**.

Designed with a clean MVC-inspired architecture and modern dark athletic aesthetics, **IRONCORE** unifies gym operation management—memberships, check-ins, trainers, workout programming, payments, and progress analytics—into a unified platform.

---

## 🚀 Key Features

* **Public Landing Page**: Premium tech-driven aesthetic, scroll animations, numerical tickers, interactive SVG chart preview, pricing calculator.
* **MVC PHP Architecture**: Separation of concerns between Controllers, Services, Models, Repositories, Helpers, and View Templates.
* **Role-Based Portals**: Dedicated administrative control, trainer workspace, and member dashboard entry points.
* **Security First**: Prepared PDO queries, BCRYPT password hashing, session hardening, output escaping (`e()`), and CSRF token protection.
* **Zero External JS/CSS Framework Dependencies**: Built strictly with Vanilla JavaScript and CSS Custom Properties.

---

## 🛠️ Technology Stack

| Component | Technology |
| :--- | :--- |
| **Frontend UI** | HTML5, CSS3 (Custom Tokens & Flex/Grid), Vanilla JavaScript (ES6+) |
| **Backend Core** | PHP 8.0+ (MVC Architecture) |
| **Database** | MySQL 5.7+ / 8.0 / MariaDB (PDO Prepared Statements) |
| **Server Engine** | Compatible with Apache (XAMPP/WAMP/LAMP) or PHP Built-in Server |

---

## 📁 Directory Structure

```
fitness-gym-management/
├── public/                     # Document root accessible to web server
│   ├── index.php               # Public Landing Page
│   ├── login.php               # Login Page
│   ├── register.php            # Member Registration
│   ├── admin/                  # Admin Portal
│   ├── trainer/                # Trainer Portal
│   ├── member/                 # Member Portal
│   ├── assets/                 # Stylesheets, JS, Images, & Icons
│   └── uploads/                # File uploads directory
├── app/                        # Core Application Layer (Protected)
│   ├── config/                 # App configuration & Database PDO Singleton
│   ├── controllers/            # Request Handlers & HTTP Routers
│   ├── models/                 # PDO Data Models & SQL Repositories
│   ├── services/               # Business Logic Services
│   ├── middleware/             # Role Guards & Auth Middleware
│   ├── helpers/                # Security, Validation, & Response Utilities
│   └── views/                  # UI Layouts & Component Views
├── database/
│   ├── schema.sql              # MySQL DDL Tables Creation Script
│   └── seed.sql                # Initial Seed Data
├── storage/logs/               # Server & App Log files
├── .env                        # Local Environment Config
├── .gitignore
└── README.md
```

---

## ⚙️ Local Setup Instructions

### Option 1: Quick Test via PHP Built-in Server (Recommended)

1. Open PowerShell / Command Prompt and navigate to the project directory:
   ```bash
   cd C:\side_Project\fitness-gym-management
   ```

2. Start the built-in development server pointing to the `public/` directory:
   ```bash
   php -S localhost:8000 -t public
   ```

3. Open your browser and navigate to:
   ```
   http://localhost:8000
   ```

---

### Option 2: Setup via XAMPP / WAMP / LAMP

1. Copy or link the `fitness-gym-management` folder inside your XAMPP `htdocs` directory (e.g. `C:\xampp\htdocs\fitness-gym-management`).
2. Point your virtual host or Apache document root to `C:\xampp\htdocs\fitness-gym-management\public`.
3. Open `http://localhost/fitness-gym-management/public/index.php`.

---

## 🗄️ Database Setup (MySQL)

1. Open phpMyAdmin or your MySQL client (e.g., MySQL Workbench, HeidiSQL).
2. Create the target database:
   ```sql
   CREATE DATABASE ironcore_gym;
   ```
3. Import the relational schema DDL:
   ```bash
   mysql -u root -p ironcore_gym < database/schema.sql
   ```
4. Import seed data:
   ```bash
   mysql -u root -p ironcore_gym < database/seed.sql
   ```
5. Ensure your `.env` credentials match your local database:
   ```env
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=ironcore_gym
   DB_USER=root
   DB_PASS=
   ```

---

## 🔑 Default Credentials (Seed Accounts)

* **Admin Portal**: `admin@ironcore.com` | `Admin@123`
* **Trainer Portal**: `marcus@ironcore.com` | `Trainer@123`
* **Member Portal**: `alex@gmail.com` | `Member@123`

---

## 🛣️ Development Roadmap

- [x] Phase 1: Architecture setup, MVC folder structure, design tokens system, and Landing Page implementation.
- [ ] Phase 2: Complete CRUD logic for Admin Member management & Membership plan updates.
- [ ] Phase 3: Trainer workout creator & exercise assignment interface.
- [ ] Phase 4: QR-code attendance check-in module and Razorpay/UPI gateway integration.
- [ ] Phase 5: Member progress graphs & transformation log tracking.

---

## 📄 License & Attribution

Developed for college project submission and commercial expansion.  
&copy; 2026 IRONCORE Fitness. All rights reserved.
