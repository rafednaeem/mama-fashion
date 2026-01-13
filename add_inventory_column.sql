-- Add inventory/stock column to products table
ALTER TABLE products ADD COLUMN stock INT UNSIGNED NOT NULL DEFAULT 0 AFTER image;

-- Update existing products with sample stock values
UPDATE products SET stock = 10 WHERE id = 1;
UPDATE products SET stock = 15 WHERE id = 2;
UPDATE products SET stock = 20 WHERE id = 3;
UPDATE products SET stock = 25 WHERE id = 4;
UPDATE products SET stock = 30 WHERE id = 5;
