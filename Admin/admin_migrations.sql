-- Run in phpMyAdmin (database: login) to add booking status for accept/reject

-- Add status column for accept/reject (run once; skip if column already exists)
ALTER TABLE `bookings` ADD COLUMN `status` varchar(20) NOT NULL DEFAULT 'pending';
