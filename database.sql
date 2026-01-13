-- database.sql
-- SQL schema and sample data for MAMA Fashion (XAMPP + MySQL/MariaDB)
-- Import this file into a database named: mama_fashion

CREATE DATABASE IF NOT EXISTS mama_fashion
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE mama_fashion;

-- 1) Users (customers)
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(30),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample user (password in plain text for now: "user123")
INSERT INTO users (name, email, password, phone, address) VALUES
('Sample User', 'user@mama.local', 'user123', '0300-0000000', 'Lahore, Pakistan');


-- 2) Admins
DROP TABLE IF EXISTS admin;
CREATE TABLE admin (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample admin (password in plain text for now: "admin123")
INSERT INTO admin (name, email, password) VALUES
('Main Admin', 'admin@mama.local', 'admin123');


-- 3) Categories
DROP TABLE IF EXISTS categories;
CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO categories (name, slug) VALUES
('Women Suits', 'women-suits'),
('Unstitched Fabric', 'unstitched-fabric'),
('Stitched Dresses', 'stitched-dresses'),
('Jewelry (Artificial)', 'jewelry-artificial'),
('Accessories', 'accessories');


-- 4) Products
DROP TABLE IF EXISTS products;
CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) NULL,
    is_on_sale TINYINT(1) NOT NULL DEFAULT 0,
    image VARCHAR(255),
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO products (category_id, name, description, price, image) VALUES
((SELECT id FROM categories WHERE slug = 'women-suits'),
 'Emerald Festive 3‑Piece Suit',
 'Rich emerald green 3‑piece suit with delicate thread work, perfect for weddings and dawats.',
 6990.00,
 'emerald_festive_3pc.jpg'),

((SELECT id FROM categories WHERE slug = 'unstitched-fabric'),
 'Maroon Luxury Lawn Unstitched',
 'Premium maroon lawn with gold detailing, 3‑piece unstitched fabric for your own tailored fit.',
 4990.00,
 'maroon_luxury_lawn.jpg'),

((SELECT id FROM categories WHERE slug = 'stitched-dresses'),
 'Ivory Everyday Kurti',
 'Soft ivory kurti with subtle embroidery, ideal for office and university wear.',
 2890.00,
 'ivory_everyday_kurti.jpg'),

((SELECT id FROM categories WHERE slug = 'jewelry-artificial'),
 'Kundan Jhumkay Set',
 'Traditional kundan jhumkay with matching tikka, inspired by old Lahore jewelry bazaars.',
 2490.00,
 'kundan_jhumkay_set.jpg'),

((SELECT id FROM categories WHERE slug = 'accessories'),
 'Gold Accent Potli Bag',
 'Embellished potli bag with gold accents, perfect to pair with wedding outfits.',
 1990.00,
 'gold_accent_potli.jpg');


-- 5) Orders
DROP TABLE IF EXISTS orders;
CREATE TABLE orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    customer_name VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    address TEXT NOT NULL,
    payment_method VARCHAR(50) NOT NULL, -- COD, bank_transfer, jazzcash, easypaisa, sadapay, nayapay
    payment_status VARCHAR(30) NOT NULL DEFAULT 'pending', -- pending, verified, rejected
    status VARCHAR(30) NOT NULL DEFAULT 'pending', -- pending, confirmed, delivered, cancelled
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    payment_proof VARCHAR(255), -- screenshot file name
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- 6) Order items
DROP TABLE IF EXISTS order_items;
CREATE TABLE order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- 7) Payments (for manual tracking)
DROP TABLE IF EXISTS payments;
CREATE TABLE payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    method VARCHAR(50) NOT NULL, -- COD, bank_transfer, jazzcash, easypaisa, sadapay, nayapay
    reference VARCHAR(150),
    amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'pending', -- pending, verified, failed
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- 8) Optional: persistent cart table (not yet fully used; main cart is via PHP sessions)
DROP TABLE IF EXISTS cart;
CREATE TABLE cart (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9) Wishlists (per user)
DROP TABLE IF EXISTS wishlists;
CREATE TABLE wishlists (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_product (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10) Product reviews
DROP TABLE IF EXISTS product_reviews;
CREATE TABLE product_reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL, -- 1–5
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11) Simple in-site notifications (e.g. wishlist item went on sale)
DROP TABLE IF EXISTS notifications;
CREATE TABLE notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NULL,
    type VARCHAR(50) NOT NULL, -- e.g. sale, order
    message VARCHAR(255) NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

