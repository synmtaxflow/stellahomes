# Hostel Management System - Setup Instructions

## Database Configuration

1. **Create Database in XAMPP:**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `hostel`
   - Or use MySQL command line:
     ```sql
     CREATE DATABASE hostel;
     ```

2. **Configure .env file:**
   - Copy `.env.example` to `.env` (if not exists)
   - Update the following database settings in `.env`:
     ```
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=hostel
     DB_USERNAME=root
     DB_PASSWORD=
     ```
   - Generate application key:
     ```bash
     php artisan key:generate
     ```

3. **Run Migrations:**
   ```bash
   php artisan migrate
   ```

4. **Seed Database (Create Test Users):**
   ```bash
   php artisan db:seed
   ```

## Default Login Credentials

After seeding, you can login with:

- **Owner:**
  - Email: `owner@hostel.com`
  - Password: `password`

- **Matron:**
  - Email: `matron@hostel.com`
  - Password: `password`

- **Patron:**
  - Email: `patron@hostel.com`
  - Password: `password`

- **Student:**
  - Email: `student@hostel.com`
  - Password: `password`

## User Roles

- **Owner:** Full system access
- **Matron/Patron:** Manage students and handle issues
- **Student:** View own information and room details

## Starting the Application

```bash
php artisan serve
```

Then visit: http://localhost:8000/login

## Features Implemented

✅ Database configuration for MySQL (XAMPP)
✅ User authentication system
✅ Login page with Bootstrap 5
✅ Role-based access control (Owner, Matron/Patron, Student)
✅ Separate dashboards for each user role
✅ Responsive design with Bootstrap
✅ Beautiful UI with modern styling

