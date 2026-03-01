-- =============================================
-- Maharaja Transport Company — FULL DATABASE SETUP
-- Database: login
-- =============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- =============================================
-- DROP EXISTING TABLES IN REVERSE DEPENDENCY ORDER
-- =============================================
DROP TABLE IF EXISTS `truck_locations`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `driver_leaves`;
DROP TABLE IF EXISTS `trucks`;
DROP TABLE IF EXISTS `drivers`;
DROP TABLE IF EXISTS `truck_rates`;
DROP TABLE IF EXISTS `admin_users`;
DROP TABLE IF EXISTS `users`;

-- --------------------------------------------------------
-- 1. USERS — Customer accounts (login, register, forgot password)
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `email` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `last_login` DATETIME DEFAULT NULL COMMENT 'Updated on each successful login',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 2. ADMIN_USERS — Admin panel login (bcrypt hashed passwords)
-- --------------------------------------------------------
CREATE TABLE `admin_users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL COMMENT 'Stored as bcrypt hash',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default admin: user_id = admin, password = admin123
INSERT INTO `admin_users` (`user_id`, `password`) VALUES
('admin', '$2y$10$wGeaZwv0.79Orsqa8hZ0HeC6nVIAgdQN9U0HklleyPCV2rHxsU6.k2');

-- Maintain the old user ID as well
INSERT INTO `admin_users` (`user_id`, `password`) VALUES
('762086', '$2y$10$L7a4DvEnDwEH5WPdtRSEVOpD4wX9qg0Bm1i4rRWxl5wYBVAdcHElG');

-- ↑ This is bcrypt hash of 'admin123'. Change after first login!

-- --------------------------------------------------------
-- 3. TRUCK_RATES — Truck Models Catalog with Pricing
-- --------------------------------------------------------
CREATE TABLE `truck_rates` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `truck_key` VARCHAR(50) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `image_path` VARCHAR(255) DEFAULT 'default.jpg',
  `features` TEXT COMMENT 'Comma separated features',
  `capacity_ton` DECIMAL(10,2) NOT NULL DEFAULT 1.00,
  `price_per_km` INT(11) NOT NULL,
  `price_per_ton` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Default truck rates & catalog
INSERT INTO `truck_rates` (`truck_key`, `name`, `description`, `image_path`, `features`, `capacity_ton`, `price_per_km`, `price_per_ton`) VALUES
('intra', 'Intra Truck', 'Reliable transport for small to medium loads. Perfect for city deliveries and short-haul routes.', 'intra.webp', '1.5 Ton,Short Haul', 1.50, 24, 0.00),
('yodha', 'Yodha Truck', 'Built for rugged terrain and heavy loads. The warrior of off-road cargo transport.', 'yodha.webp', '2.0 Ton,All Terrain', 2.00, 40, 0.00),
('tata-prima', 'Tata Prima', 'Advanced technology for long-haul journeys. India\'s first world-class heavy truck.', 'tata-prima.webp', '17.5 Ton,Long Haul', 17.50, 60, 0.00),
('ashok-layland', 'Ashok Leyland', 'Robust and reliable for heavy cargo. Trusted by industries across India for decades.', 'ashok-leyland.webp', '19.0 Ton,Industrial', 19.00, 55, 0.00),
('bharatbenz', 'Bharat Benz', 'High-performance and fuel-efficient. German engineering tailored for Indian roads.', 'bharatbenz.webp', '15.0 Ton,Fuel Efficient', 15.00, 70, 0.00);

-- --------------------------------------------------------
-- 4. TRUCKS — Fleet inventory (registration, model, status)
-- --------------------------------------------------------
CREATE TABLE `trucks` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `truck_number` VARCHAR(50) NOT NULL UNIQUE COMMENT 'License plate / registration number',
  `truck_model` VARCHAR(100) NOT NULL COMMENT 'e.g. Tata Prima, Ashok Leyland',
  `truck_type` VARCHAR(50) DEFAULT 'FTL' COMMENT 'FTL or PTL',
  `driver_name` VARCHAR(100) DEFAULT NULL,
  `driver_phone` VARCHAR(15) DEFAULT NULL,
  `status` ENUM('available','in_transit','maintenance') DEFAULT 'available',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 5. DRIVERS — Company drivers (for assignment to bookings)
-- --------------------------------------------------------
CREATE TABLE `drivers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `license_number` VARCHAR(50) NOT NULL UNIQUE,
  `phone` VARCHAR(20) NOT NULL,
  `salary` DECIMAL(10,2) DEFAULT 0.00,
  `status` ENUM('Available','On Trip','On Leave','Fired') DEFAULT 'Available',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample drivers
INSERT INTO `drivers` (`name`, `license_number`, `phone`, `salary`) VALUES
('Rahul Sharma', 'MH-12-2023-001', '9876543210', 25000.00),
('Suresh Patil', 'MH-14-2024-002', '8765432109', 22000.00),
('Amit Deshmukh', 'MH-09-2023-003', '7654321098', 28000.00);

-- --------------------------------------------------------
-- 6. DRIVER_LEAVES — Leave requests from drivers
-- --------------------------------------------------------
CREATE TABLE `driver_leaves` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `driver_id` INT(11) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `reason` TEXT,
  `status` ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_leave_driver` (`driver_id`),
  CONSTRAINT `fk_leave_driver` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 7. BOOKINGS — Customer truck bookings
-- --------------------------------------------------------
CREATE TABLE `bookings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `customer_name` VARCHAR(100) NOT NULL,
  `user_email` VARCHAR(100) NOT NULL DEFAULT '',
  `truck_name` VARCHAR(50) NOT NULL,
  `truck_id` INT(11) DEFAULT NULL,
  `booking_date` DATE NOT NULL,
  `address` TEXT NOT NULL,
  `pickup_location` VARCHAR(255) DEFAULT NULL,
  `delivery_location` VARCHAR(255) DEFAULT NULL,
  `distance_km` INT(11) NOT NULL,
  `price_per_km` DECIMAL(10,2) NOT NULL,
  `total_cost` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `driver_id` INT(11) DEFAULT NULL COMMENT 'Assigned driver',
  `weight_ton` DECIMAL(10,2) DEFAULT 1.00,
  `reject_reason` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_booking_truck` (`truck_id`),
  KEY `fk_booking_driver` (`driver_id`),
  CONSTRAINT `fk_booking_truck` FOREIGN KEY (`truck_id`) REFERENCES `trucks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_booking_driver` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 8. TRUCK_LOCATIONS — GPS tracking data (future feature)
-- --------------------------------------------------------
CREATE TABLE `truck_locations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `truck_id` INT(11) NOT NULL,
  `latitude` DECIMAL(10,8) NOT NULL COMMENT 'GPS latitude coordinate',
  `longitude` DECIMAL(11,8) NOT NULL COMMENT 'GPS longitude coordinate',
  `speed` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Speed in km/h',
  `heading` DECIMAL(5,2) DEFAULT NULL COMMENT 'Compass direction (0-360)',
  `altitude` DECIMAL(7,2) DEFAULT NULL COMMENT 'Altitude in meters',
  `accuracy` DECIMAL(6,2) DEFAULT NULL COMMENT 'GPS accuracy in meters',
  `timestamp` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_truck_timestamp` (`truck_id`, `timestamp`),
  CONSTRAINT `truck_locations_ibfk_1` FOREIGN KEY (`truck_id`) REFERENCES `trucks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
