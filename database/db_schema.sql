-- Database schema for quotation system
-- Path: database/db_schema.sql

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS quotation_system;
USE quotation_system;

-- Quotations table
CREATE TABLE IF NOT EXISTS quotations (
    quotation_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255),
    customer_phone VARCHAR(50),
    quotation_date DATE NOT NULL,
    valid_until DATE,
    status ENUM('draft', 'sent', 'accepted', 'rejected') DEFAULT 'draft',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Quotation items table
CREATE TABLE IF NOT EXISTS quotation_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    quotation_id INT NOT NULL,
    item_no INT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit VARCHAR(50),
    description TEXT NOT NULL,
    original_price DECIMAL(10,2) NOT NULL,
    markup_percentage DECIMAL(10,2) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (quotation_id) REFERENCES quotations(quotation_id) ON DELETE CASCADE
);