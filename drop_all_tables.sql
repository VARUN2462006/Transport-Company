-- =============================================
-- Maharaja Transport Company — DROP ALL TABLES
-- Run this FIRST to wipe the database clean.
-- ⚠️  This will permanently delete ALL data!
-- =============================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `truck_locations`;
DROP TABLE IF EXISTS `driver_leaves`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `trucks`;
DROP TABLE IF EXISTS `truck_rates`;
DROP TABLE IF EXISTS `drivers`;
DROP TABLE IF EXISTS `admin_users`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `admin login`;

SET FOREIGN_KEY_CHECKS = 1;
