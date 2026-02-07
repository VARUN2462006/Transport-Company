-- Run in phpMyAdmin (database: login). Run each ALTER once (skip if column already exists).

-- Add status column for accept/reject
ALTER TABLE `bookings` ADD COLUMN `status` varchar(20) NOT NULL DEFAULT 'pending';

-- Add weight (tons) for rate calculation: cost = price_per_km × distance_km × weight_ton
ALTER TABLE `bookings` ADD COLUMN `weight_ton` decimal(10,2) NULL DEFAULT 1;

-- Add rate per ton so admin can set overall cost: cost = (₹/km × distance) + (₹/ton × weight)
ALTER TABLE `truck_rates` ADD COLUMN `price_per_ton` decimal(10,2) NOT NULL DEFAULT 0;
