-- This file contains initial category data for the e-commerce system
-- Provides a starting set of product categories for better organization

USE ecommerce_system;

-- Insert sample product categories
-- These categories cover common e-commerce product types
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

SELECT * FROM categories ORDER BY name;
