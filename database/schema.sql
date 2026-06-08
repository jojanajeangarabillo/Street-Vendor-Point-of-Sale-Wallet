-- Street Vendor Point-of-Sale Wallet Schema
-- Target: MySQL 8.x (XAMPP)

-- Create Database
CREATE DATABASE IF NOT EXISTS `street_vendor_pos` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `street_vendor_pos`;

-- 1. Vendors Table
-- Stores vendor profile and authentication information.
CREATE TABLE `vendors` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_vendor_email (`email`)
) ENGINE=InnoDB;

-- 2. Wallets Table
-- Links Stellar public keys (Freighter or internal) to vendors.
CREATE TABLE `wallets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `vendor_id` INT NOT NULL,
    `stellar_public_key` VARCHAR(56) NOT NULL,
    `wallet_type` ENUM('freighter', 'internal') DEFAULT 'internal',
    `network` ENUM('testnet', 'public') DEFAULT 'testnet',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE,
    INDEX idx_stellar_pubkey (`stellar_public_key`)
) ENGINE=InnoDB;

-- 3. Payment Requests Table
-- Stores unique payment sessions for customers to pay via QR.
CREATE TABLE `payment_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `vendor_id` INT NOT NULL,
    `amount` DECIMAL(18, 7) NOT NULL,
    `asset_code` VARCHAR(12) DEFAULT 'XLM',
    `description` VARCHAR(255),
    `payment_reference` VARCHAR(64) NOT NULL UNIQUE,
    `qr_data` TEXT,
    `status` ENUM('pending', 'completed', 'expired', 'cancelled') DEFAULT 'pending',
    `expires_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE,
    INDEX idx_payment_ref (`payment_reference`),
    INDEX idx_payment_status (`status`)
) ENGINE=InnoDB;

-- 4. Transactions Table
-- Records confirmed Stellar blockchain transactions.
CREATE TABLE `transactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `vendor_id` INT NOT NULL,
    `payment_request_id` INT DEFAULT NULL,
    `stellar_transaction_hash` VARCHAR(64) NOT NULL UNIQUE,
    `sender_address` VARCHAR(56) NOT NULL,
    `receiver_address` VARCHAR(56) NOT NULL,
    `amount` DECIMAL(18, 7) NOT NULL,
    `asset_code` VARCHAR(12) DEFAULT 'XLM',
    `status` ENUM('confirmed', 'failed') DEFAULT 'confirmed',
    `confirmed_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`payment_request_id`) REFERENCES `payment_requests`(`id`) ON DELETE SET NULL,
    INDEX idx_stellar_hash (`stellar_transaction_hash`)
) ENGINE=InnoDB;

-- 5. Audit Logs Table
-- Tracks critical vendor actions for security and history.
CREATE TABLE `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `vendor_id` INT NOT NULL,
    `action` VARCHAR(100) NOT NULL,
    `details` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Sample Seed Data
INSERT INTO `vendors` (`full_name`, `email`, `password_hash`, `phone`) VALUES
('Test Vendor', 'vendor@example.com', '$2y$10$S6A6I0Xq1hG2E8vGjR.ZDeCjQ2QyU7m5p2LhXqf8Rz0Yv4eH5nS2O', '1234567890'); -- password: password123

INSERT INTO `wallets` (`vendor_id`, `stellar_public_key`, `wallet_type`, `network`) VALUES
(1, 'GAHH3O44T3V6B3H6G4X3X4X3X4X3X4X3X4X3X4X3X4X3X4X3X4X3X4X3', 'internal', 'testnet');
