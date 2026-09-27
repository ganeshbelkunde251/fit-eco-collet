-- EcoCollect Database Schema
-- Database Name: ecocollect

CREATE DATABASE IF NOT EXISTS `ecocollect` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ecocollect`;

-- Drop existing tables to allow clean re-importing
DROP TABLE IF EXISTS `pickup_requests`;
DROP TABLE IF EXISTS `waste_categories`;
DROP TABLE IF EXISTS `users`;

-- 1. Users Table
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(120) NOT NULL UNIQUE,
  `phone` VARCHAR(20) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Waste Categories Table
CREATE TABLE `waste_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `description` TEXT NOT NULL,
  `icon` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Pickup Requests Table
CREATE TABLE `pickup_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `request_id` VARCHAR(25) NOT NULL UNIQUE,
  `user_id` INT NOT NULL,
  `waste_category` VARCHAR(50) NOT NULL,
  `description` TEXT NOT NULL,
  `quantity` VARCHAR(50) NOT NULL,
  `house_building` VARCHAR(150) NOT NULL,
  `street_area` VARCHAR(150) NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `state` VARCHAR(100) NOT NULL,
  `pincode` VARCHAR(20) NOT NULL,
  `location_notes` TEXT DEFAULT NULL,
  `pickup_date` DATE NOT NULL,
  `pickup_time` VARCHAR(20) NOT NULL,
  `status` ENUM('Pending', 'Assigned', 'Scheduled', 'Picked Up', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  INDEX `idx_request_id` (`request_id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_pickup_date` (`pickup_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
