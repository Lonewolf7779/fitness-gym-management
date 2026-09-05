# IRONCORE | Fitness & Gym Management System

IRONCORE is a full-stack gym management application built for a college project with a production-minded MVC structure. It uses **HTML5, CSS3, Vanilla JavaScript, PHP 8+, and MySQL** with PDO.

## What is implemented

- Premium responsive public landing page
- Registration and login with role-based authentication
- Admin, Trainer, and Member portals
- Admin member management backed by MySQL
- Trainer management with create/edit/status controls
- Membership plan management with create/edit/activation controls
- Attendance check-in/check-out with duplicate protection
- Payment recording with unique transaction IDs
- Workout plan creation and exercise assignment
- Reports and operational metrics
- Live admin KPIs, recent members, expiry alerts, and revenue/attendance charts
- Live trainer client/program metrics
- Live member subscription, streak, attendance, and workout data
- Member progress logging with validation
- Member attendance history and self check-in/check-out
- Persistent admin gym settings
- PDO prepared statements, password hashing, CSRF protection, output escaping, session hardening, role guards, and server-side validation
- Optional realistic demo dataset for immediate dashboard testing

## Technology

| Layer | Technology |
|---|---|
| UI | HTML5, CSS3, Vanilla JavaScript ES6+ |
| Backend | PHP 8+ |
| Database | MySQL 5.7+/8.0 or MariaDB |
| Database access | PDO prepared statements |
| Local server | XAMPP Apache, WAMP/LAMP, or PHP built-in server |

## Project structure

```text
fitness-gym-management/
├── public/                    # Web document root
│   ├── index.php
│   ├── login.php
│   ├── register.php
│   ├── api.php
│   ├── dashboard-data.php
│   ├── dashboard-chart.php
│   ├── member-data.php
│   ├── admin/
│   ├── trainer/
│   ├── member/
│   └── assets/
├── app/
│   ├── config/
│   ├── controllers/
│   ├── models/
│   ├── services/
│   ├── middleware/
│   ├── helpers/
│   └── views/
├── database/
│   ├── schema.sql
│   ├── seed.sql
│   └── demo-data.sql
├── storage/logs/
├── .env
└── README.md
```

## XAMPP setup

1. Install XAMPP with **Apache + MySQL + phpMyAdmin**.
2. Put the project in:
   `C:\xampp\htdocs\fitness-gym-management`
3. Start **Apache** and **MySQL** in the XAMPP Control Panel.
4. Open phpMyAdmin at `http://localhost/phpmyadmin/`.
5. Import `database/schema.sql`.
6. Import `database/seed.sql`.
7. Optionally import `database/demo-data.sql` for a populated first run.
8. Copy `.env.example` to `.env` and keep the local database values:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/fitness-gym-management/public
AUTH_MODE=database

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=ironcore_gym
DB_USER=root
DB_PASS=
```

9. Open:
   `http://localhost/fitness-gym-management/public/`

### PHP built-in server

If Apache is not required, PHP can also serve the public directory directly:

```bash
php -S localhost:8000 -t public
```

Then open `http://localhost:8000`.

## Test accounts

| Role | Email | Password |
|---|---|---|
| Admin | admin@ironcore.com | Admin@123 |
| Trainer | marcus@ironcore.com | Trainer@123 |
| Member | alex@gmail.com | Member@123 |
| Suspended test | suspended@gmail.com | Member@123 |
| Inactive test | inactive@gmail.com | Member@123 |

The suspended and inactive accounts are intentionally rejected by the authentication flow.

## Recommended first-run test

After importing the three SQL files:

1. Log in as Admin and verify live member, attendance, revenue, trainer, membership, payment, workout, and report data.
2. Create a member and assign a plan.
3. Create/edit a trainer.
4. Create/edit a membership plan.
5. Check a member in and out from Attendance.
6. Record a payment and verify it appears in reports/dashboard revenue.
7. Create a workout plan and assign exercises.
8. Log in as the Trainer and confirm the assigned client appears.
9. Log in as the Member and add a progress measurement.
10. Check the member's attendance history and subscription data.
11. Open Admin Settings and save gym configuration.
12. Test suspended and inactive accounts to confirm access is blocked.

## Production note

Before deployment, use `APP_ENV=production`, disable debug output, use `AUTH_MODE=database`, set a strong database password, use HTTPS, and keep `.env` outside source control. The development credentials above are for project testing only.

## Status

The application codebase now contains the core end-to-end management workflows and live database integrations. Final acceptance still requires running the application against an actual MySQL instance, because PHP/MySQL runtime testing cannot be performed from the repository itself.

© 2026 IRONCORE Fitness
