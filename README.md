# PHP Login System

A secure login system built with PHP and MySQL.

## Features

- User registration and login
- Password hashing and verification
- CSRF protection
- Rate limiting for failed attempts
- User profile management
- Clean, responsive design

## Setup

1. Import the database schema:
   ```bash
   mysql -u root -p < database/schema.sql
   ```
2. Update database credentials in `includes/db.php`

3. Start your web server and navigate to the project

## Security Features

- Passwords are hashed using PHP's `password_hash()`
- CSRF tokens protect against cross-site attacks
- Rate limiting prevents brute force attacks
- All database queries use prepared statements

## File Structure

- `public/` - Web pages (login, register, dashboard, profile)
- `src/` - Processing scripts
- `includes/` - Shared code and database connection
- `database/` - SQL schema

---

This project demonstrates secure authentication practices and clean PHP code structure.
