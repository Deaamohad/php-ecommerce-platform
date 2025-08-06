-- PHP Login System Database Schema
-- This file contains the complete database structure for the login system

-- Create the database
CREATE DATABASE IF NOT EXISTS login_system;
USE login_system;

-- Users table - stores user account information
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Login attempts table - tracks failed login attempts for rate limiting
CREATE TABLE login_attempts (
    ip_address VARCHAR(45) PRIMARY KEY,
    attempts INT DEFAULT 0,
    last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    blocked_until TIMESTAMP NULL
);

