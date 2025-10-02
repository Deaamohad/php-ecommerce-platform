# PHP E‑Commerce Platform

Complete e‑commerce site built with PHP and MySQL: auth, products, cart, checkout, orders, profile, and admin.

## Tech Stack
- PHP 8+
- MySQL 8+
- CSS/HTML + Bootstrap Icons

## Key Features
- Auth: sessions, password hashing, CSRF, rate limiting
- Products: categories, image upload/URL, search and price filtering
- Cart/Checkout: quantity controls, CSRF, server‑side validation
- Orders: history, status badges, details modal with item images
- Admin: add/edit/delete products with safe image handling
- Responsive UI

## Recent Improvements
- Backend order‑status simulation (demo): orders auto‑advance over time
- Server‑side out‑of‑stock guard and stock decrement on checkout
- FK‑safe product deletion (removes related `order_items` in a transaction)
- Order success page styling isolated from cart styles
- Base‑path‑aware auth redirects (works in subdirectories / shared hosting)

## Setup
1) Database
```sql
CREATE DATABASE ecommerce_system;
USE ecommerce_system;
```
Import `database/schema.sql` and optionally `database/sample_categories.sql`.

2) Config
- Set DB creds in `includes/db.php`.
- Ensure `uploads/products/` is writable.

3) Run
- Place the project in your web root (or a subfolder).
- Open `http://localhost/project/products` (adjust path to your folder).

## Demo Accounts
- User: `user` / `user`
- Admin: `admin` / `admin`

## Notes
- Order status auto‑advance is for portfolio demos. You can disable or adjust timings in `profile.php` (Orders tab block).
- Redirects honor the current base path via `includes/auth.php`.
