-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 31, 2026 at 04:43 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `glass_login_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `created_at`) VALUES
(1, 'test@example.com', '$2y$10$wS2/jF.28RjW4O./jF.28uX/jF.28RjW4O./jF.28uX/jF.28u', '2026-01-19 06:04:10'),
(2, 'athawalevarun3@gmail.com', '$2y$10$tvjAt3AHl.Otn2RdfcADSeI6GxrMX6mxosqBiBTZ09hQbbwYfnL.q', '2026-01-19 06:11:33'),
(3, 'faizan@gmail.com', '$2y$10$7PwHq3n.oKSlmwCtRCOFX.FD2BxrMhfR/jgrtQ2Ca0P4bnorArtbq', '2026-01-19 06:12:17'),
(5, 'athawalevikram@gmail.com', '$2y$10$aEVX3orr73mGzzG.HFTAR.Ts9HIsSFMEBpZP260wvaFuD3Ki9ezmu', '2026-01-19 14:41:42'),
(6, 'amar1@gmail.com', '$2y$10$kC9kvpnuzdlFJmmfF1I/v.r.mi3OX03onBjMfitSeUkPziOFnLFJm', '2026-01-28 07:30:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
