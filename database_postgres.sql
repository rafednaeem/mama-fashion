-- database_postgres.sql
-- PostgreSQL schema and sample data for MAMA Fashion (Supabase)
-- Import this file into your Supabase database

-- 1) Users (customers)
DROP TABLE IF EXISTS users CASCADE;
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(30),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample user (password in plain text for now: "user123")
INSERT INTO users (name, email, password, phone, address) VALUES
('Sample User', 'user@mama.local', 'user123', '0300-0000000', 'Lahore, Pakistan');

-- 2) Admins
DROP TABLE IF EXISTS admin CASCADE;
CREATE TABLE admin (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample admin (password in plain text for now: "admin123")
INSERT INTO admin (name, email, password) VALUES
('Main Admin', 'admin@mama.local', 'admin123');

-- 3) Categories
DROP TABLE IF EXISTS categories CASCADE;
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO categories (name, slug) VALUES
('Women Suits', 'women-suits'),
('Unstitched Fabric', 'unstitched-fabric'),
('Stitched Dresses', 'stitched-dresses'),
('Jewelry (Artificial)', 'jewelry-artificial'),
('Accessories', 'accessories');

-- 4) Products
DROP TABLE IF EXISTS products CASCADE;
CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    category_id INTEGER NOT NULL REFERENCES categories(id) ON DELETE CASCADE,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2),
    is_on_sale SMALLINT NOT NULL DEFAULT 0,
    image VARCHAR(255),
    is_active SMALLINT NOT NULL DEFAULT 1,
    stock INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO products (category_id, name, description, price, image, stock) VALUES
((SELECT id FROM categories WHERE slug = 'women-suits'),
 'Emerald Festive 3‑Piece Suit',
 'Rich emerald green 3‑piece suit with delicate thread work, perfect for weddings and dawats.',
 6990.00,
 'emerald_festive_3pc.jpg',
 10),

((SELECT id FROM categories WHERE slug = 'unstitched-fabric'),
 'Maroon Luxury Lawn Unstitched',
 'Premium maroon lawn with gold detailing, 3‑piece unstitched fabric for your own tailored fit.',
 4990.00,
 'maroon_luxury_lawn.jpg',
 15),

((SELECT id FROM categories WHERE slug = 'stitched-dresses'),
 'Ivory Everyday Kurti',
 'Soft ivory kurti with subtle embroidery, ideal for office and university wear.',
 2890.00,
 'ivory_everyday_kurti.jpg',
 20),

((SELECT id FROM categories WHERE slug = 'jewelry-artificial'),
 'Kundan Jhumkay Set',
 'Traditional kundan jhumkay with matching tikka, inspired by old Lahore jewelry bazaars.',
 2490.00,
 'kundan_jhumkay_set.jpg',
 25),

((SELECT id FROM categories WHERE slug = 'accessories'),
 'Gold Accent Potli Bag',
 'Embellished potli bag with gold accents, perfect to pair with wedding outfits.',
 1990.00,
 'gold_accent_potli.jpg',
 30);

-- 5) Orders
DROP TABLE IF EXISTS orders CASCADE;
CREATE TABLE orders (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    customer_name VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    address TEXT NOT NULL,
    payment_method VARCHAR(50) NOT NULL, -- COD, bank_transfer, jazzcash, easypaisa, sadapay, nayapay
    payment_status VARCHAR(30) NOT NULL DEFAULT 'pending', -- pending, verified, rejected
    status VARCHAR(30) NOT NULL DEFAULT 'pending', -- pending, confirmed, delivered, cancelled
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    payment_proof VARCHAR(255), -- screenshot file name
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6) Order items
DROP TABLE IF EXISTS order_items CASCADE;
CREATE TABLE order_items (
    id SERIAL PRIMARY KEY,
    order_id INTEGER NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
    product_id INTEGER NOT NULL REFERENCES products(id) ON DELETE RESTRICT,
    quantity INTEGER NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL
);

-- 7) Payments (for manual tracking)
DROP TABLE IF EXISTS payments CASCADE;
CREATE TABLE payments (
    id SERIAL PRIMARY KEY,
    order_id INTEGER NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
    method VARCHAR(50) NOT NULL, -- COD, bank_transfer, jazzcash, easypaisa, sadapay, nayapay
    reference VARCHAR(150),
    amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'pending', -- pending, verified, failed
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 8) Optional: persistent cart table (not yet fully used; main cart is via PHP sessions)
DROP TABLE IF EXISTS cart CASCADE;
CREATE TABLE cart (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    product_id INTEGER NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    quantity INTEGER NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 9) Wishlists (per user)
DROP TABLE IF EXISTS wishlists CASCADE;
CREATE TABLE wishlists (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    product_id INTEGER NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (user_id, product_id)
);

-- 10) Product reviews
DROP TABLE IF EXISTS product_reviews CASCADE;
CREATE TABLE product_reviews (
    id SERIAL PRIMARY KEY,
    product_id INTEGER NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    rating SMALLINT NOT NULL, -- 1–5
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 11) Simple in-site notifications (e.g. wishlist item went on sale)
DROP TABLE IF EXISTS notifications CASCADE;
CREATE TABLE notifications (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    product_id INTEGER REFERENCES products(id) ON DELETE SET NULL,
    type VARCHAR(50) NOT NULL, -- e.g. sale, order
    message VARCHAR(255) NOT NULL,
    is_read SMALLINT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
