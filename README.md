# PHP E-Commerce Platform

A complete e-commerce website I built using PHP and MySQL. Started as a simple login system but grew into a full shopping platform with cart functionality and admin features.

## What it does

- Users can register, login, and manage their profiles
- Browse products with search and price filtering
- Add items to cart and manage quantities
- Admins can add, edit, and delete products
- Secure authentication with proper password hashing
- Responsive design that works on mobile

## Tech Stack

- **PHP** - Backend logic and user authentication
- **MySQL** - Database for users, products, and cart data
- **CSS/HTML** - Frontend styling with responsive design
- **Bootstrap Icons** - For clean UI icons

## Getting Started

1. **Database Setup**
   ```sql
   CREATE DATABASE ecommerce_system;
   USE ecommerce_system;
   ```
   Then import `database/schema.sql`

2. **Configuration**
   - Update database credentials in `includes/db.php`
   - Make sure you have PHP and MySQL running

3. **Access the site**
   - Put files in your web server directory
   - Visit `localhost/project/public/`

## Features 

- **Security**: CSRF protection, prepared statements, rate limiting
- **User Experience**: Clean interface, error messages, success feedback
- **Admin Panel**: Full product management with image uploads
- **Shopping Cart**: Persistent cart that remembers items between sessions
- **Profile System**: Comprehensive user account management
PHP and MySQL running

3. **Access the site**
   - Put files in your web server directory
   - Visit `localhost/project/public/`

## Features 

- **Security**: CSRF protection, prepared statements, rate limiting
- **User Experience**: Clean interface, error messages, success feedback
- **Admin Panel**: Full product management with image uploads
- **Shopping Cart**: Persistent cart that remembers items between sessions
- **Profile System**: Comprehensive user account management
