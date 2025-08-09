-- Run this file to populate the categories table with common product categories

USE ecommerce_system;

-- Insert sample categories
INSERT INTO categories (name) VALUES 
('Electronics'),
('Clothing'),
('Books'),
('Home & Garden'),
('Sports & Outdoors'),
('Beauty & Personal Care'),
('Toys & Games'),
('Automotive'),
('Health & Wellness'),
('Food & Beverages'),
('Office Supplies'),
('Pet Supplies'),
('Music & Movies'),
('Jewelry & Accessories'),
('Art & Crafts');

-- Display inserted categories
SELECT * FROM categories ORDER BY name;
