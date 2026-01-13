## MAMA Fashion – PHP E‑commerce (XAMPP)

This is a simple, full‑stack e‑commerce web application for a Pakistani women’s fashion business, built with **PHP**, **MySQL**, **HTML**, **CSS**, and **JavaScript**, designed to run locally on **XAMPP**.

It focuses on:

- South Asian women’s clothing (suits, kurtis, stitched & unstitched)
- Artificial jewelry
- Fashion accessories (bags, bangles, dupattas, etc.)

The goal is to be **beginner‑friendly**, **clean**, and **easy to extend**.

### 1. Prerequisites

- XAMPP with:
  - Apache
  - PHP 8.x
  - MySQL / MariaDB
- A web browser (Chrome, Edge, Firefox, etc.)

### 2. Folder Setup on Windows (XAMPP)

1. Install XAMPP if you haven't already.
2. Go to your XAMPP `htdocs` directory, usually:
   - `C:\xampp\htdocs\`
3. Create a folder for this project, for example:
   - `C:\xampp\htdocs\mama_fashion\`
4. Copy all files and folders from this project into that folder, so you have:
   - `C:\xampp\htdocs\mama_fashion\index.php`
   - `C:\xampp\htdocs\mama_fashion\config\...`
   - `C:\xampp\htdocs\mama_fashion\admin\...`
   - `C:\xampp\htdocs\mama_fashion\assets\...`

### 3. Database Setup

1. Start **Apache** and **MySQL** from the XAMPP Control Panel.
2. Open `http://localhost/phpmyadmin/` in your browser.
3. Click **Databases** → create a new database named (for example) `mama_fashion`.
4. Click the new database, then go to the **Import** tab.
5. Choose the file `database.sql` from this project and click **Go** to import tables and sample data.
6. If you change the database name, user, or password, update the values in:
   - `config/config.php`

### 4. Running the Application

1. With Apache and MySQL running, open your browser and go to:
   - `http://localhost/mama_fashion/`
2. You will see the **public storefront**:
   - Home page (featured products and categories)
   - Product listing and detail pages
   - Register / login
   - Cart and checkout

### 5. Admin Panel

- Admin login URL:
  - `http://localhost/mama_fashion/admin/login.php`
- Default admin credentials (from sample data, change after first login):
  - **Email**: `admin@mama.local`
  - **Password**: `admin123`

From the admin panel you can:

- Add / edit / delete products
- Assign categories
- Upload product images
- View orders and payment proofs
- Mark orders as confirmed / delivered

### 6. Notes

- This project is intentionally simple and uses **plain PHP** with includes and a light structure (no heavy frameworks).
- The code is commented for learning.
- Payment integrations (JazzCash, EasyPaisa, SadaPay, NayaPay) are left as **placeholders** for future API integration.


