-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 22, 2025 at 08:05 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sport_sync`
--

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `match_id`) VALUES
(4, 12, 18),
(5, 12, 20),
(6, 12, 20),
(13, 12, 20),
(14, 12, 11),
(15, 12, 17),
(16, 12, 12),
(17, 13, 18),
(18, 13, 17),
(19, 13, 19),
(20, 13, 21),
(21, 13, 20);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `name`, `email`, `subject`, `message`, `created_at`) VALUES
(1, 'Varun Joshi', 'varunpndey@outlook.com', 'testing feedback', 'this is for testing purpose', '2025-04-21 21:16:21'),
(2, 'Varun Joshi', 'varunpndey@outlook.com', 'testing feedback', 'helloo', '2025-04-21 21:55:02'),
(3, 'Varun Joshi', 'varunpndey@outlook.com', 'testing feedback', 'ggggg', '2025-04-21 21:57:31'),
(4, 'Varun Joshi', 'varunpndey@outlook.com', 'testing feedback', 'hhghfgyhfgyu', '2025-04-21 21:59:36'),
(5, 'Varun Joshi', 'varunpndey@outlook.com', 'testing feedback', 'hhghfgyhfgyu', '2025-04-21 21:59:39'),
(6, 'Varun Joshi', 'varunpndey@outlook.com', 'testing feedback', 'hhghfgyhfgyu', '2025-04-21 21:59:39'),
(7, 'Varun Joshi', 'varunpndey@outlook.com', 'testing feedback', 'hhghfgyhfgyu', '2025-04-21 21:59:39'),
(8, 'Varun Joshi', 'varunpndey@outlook.com', 'another testing', 'this for testing purpose', '2025-04-22 04:17:33'),
(9, 'abhay lal', 'abhaylal122@gmail.com', 'hdwiuq', 'weuygdhfiuw edf', '2025-04-22 05:36:48'),
(10, 'Varun Joshi', 'varunpndey@outlook.com', 'hjf', 'ugewyd iuygfhdqwe iufhqbiowefhb', '2025-04-22 05:55:57');

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `id` int(11) NOT NULL,
  `sport` varchar(50) NOT NULL,
  `team1` varchar(50) NOT NULL,
  `team2` varchar(50) NOT NULL,
  `score_team1` int(11) DEFAULT 0,
  `score_team2` int(11) DEFAULT 0,
  `status` enum('upcoming','live','completed') DEFAULT 'upcoming',
  `venue` varchar(100) DEFAULT NULL,
  `match_time` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `team1_score` int(11) DEFAULT 0,
  `team2_score` int(11) DEFAULT 0,
  `team1_country` varchar(2) DEFAULT NULL,
  `team2_country` varchar(2) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `team1_wickets` int(11) DEFAULT 0,
  `team2_wickets` int(11) DEFAULT 0,
  `team1_overs` decimal(5,1) DEFAULT 0.0,
  `team2_overs` decimal(5,1) DEFAULT 0.0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `matches`
--

INSERT INTO `matches` (`id`, `sport`, `team1`, `team2`, `score_team1`, `score_team2`, `status`, `venue`, `match_time`, `created_at`, `team1_score`, `team2_score`, `team1_country`, `team2_country`, `location`, `updated_at`, `team1_wickets`, `team2_wickets`, `team1_overs`, `team2_overs`) VALUES
(11, 'cricket', 'India', 'Australia', 0, 0, 'live', 'Melbourne Cricket Ground', '2025-04-21 18:57:39', '2025-04-21 17:57:39', 0, 0, NULL, NULL, NULL, '2025-04-21 17:57:39', 0, 0, 0.0, 0.0),
(12, 'cricket', 'England', 'Pakistan', 0, 0, 'upcoming', 'Lords Cricket Ground', '2025-04-21 19:57:39', '2025-04-21 17:57:39', 0, 0, NULL, NULL, NULL, '2025-04-21 17:57:39', 0, 0, 0.0, 0.0),
(13, 'cricket', 'South Africa', 'New Zealand', 0, 0, 'completed', 'Eden Park', '2025-04-21 16:57:39', '2025-04-21 17:57:39', 0, 0, NULL, NULL, NULL, '2025-04-21 17:57:39', 0, 0, 0.0, 0.0),
(14, 'cricket', 'India', 'England', 0, 0, 'completed', 'The Oval, London', '2025-06-18 08:30:00', '2025-04-21 17:57:39', 244, 243, NULL, NULL, NULL, '2025-04-21 22:07:12', 6, 10, 20.0, 19.3),
(15, 'cricket', 'Australia', 'Pakistan', 0, 0, 'completed', 'Edgbaston, Birmingham', '2025-06-15 10:30:00', '2025-04-21 17:57:39', 0, 0, NULL, NULL, NULL, '2025-04-21 17:57:39', 0, 0, 0.0, 0.0),
(16, 'cricket', 'New Zealand', 'South Africa', 0, 0, 'completed', 'Lord\'s, London', '2025-06-12 13:00:00', '2025-04-21 17:57:39', 0, 0, NULL, NULL, NULL, '2025-04-21 17:57:39', 0, 0, 0.0, 0.0),
(17, 'football', 'Manchester United', 'Liverpool', 0, 0, 'live', 'Old Trafford', '2025-04-21 18:27:39', '2025-04-21 17:57:39', 0, 0, NULL, NULL, NULL, '2025-04-21 17:57:39', 0, 0, 0.0, 0.0),
(18, 'football', 'Real Madrid', 'Barcelona', 0, 0, 'upcoming', 'Santiago Bernabéu', '2025-04-21 20:57:39', '2025-04-21 17:57:39', 0, 0, NULL, NULL, NULL, '2025-04-21 17:57:39', 0, 0, 0.0, 0.0),
(19, 'football', 'Bayern Munich', 'Paris Saint-Germain', 0, 0, 'completed', 'Allianz Arena', '2025-04-21 15:57:39', '2025-04-21 17:57:39', 5, 2, NULL, NULL, NULL, '2025-04-21 18:20:24', 0, 0, 0.0, 0.0),
(20, 'football', 'Argentina', 'France', 0, 0, 'completed', 'Lusail Iconic Stadium, Qatar', '2023-12-18 18:00:00', '2025-04-21 17:57:39', 0, 0, NULL, NULL, NULL, '2025-04-21 17:57:39', 0, 0, 0.0, 0.0),
(21, 'football', 'Croatia', 'Argentina', 0, 0, 'completed', 'Lusail Iconic Stadium, Qatar', '2023-12-13 22:00:00', '2025-04-21 17:57:39', 0, 0, NULL, NULL, NULL, '2025-04-21 17:57:39', 0, 0, 0.0, 0.0),
(22, 'football', 'France', 'Morocco', 0, 0, 'completed', 'Al Bayt Stadium, Qatar', '2023-12-14 22:00:00', '2025-04-21 17:57:39', 0, 0, NULL, NULL, NULL, '2025-04-21 17:57:39', 0, 0, 0.0, 0.0),
(23, 'football', 'England', 'France', 0, 0, 'completed', 'Al Bayt Stadium, Qatar', '2023-12-10 20:00:00', '2025-04-21 17:57:39', 0, 0, NULL, NULL, NULL, '2025-04-21 17:57:39', 0, 0, 0.0, 0.0);

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `category`, `title`, `content`, `created_at`) VALUES
(1, 'football', 'Champions League Quarter-Finals Draw Announced', 'The UEFA Champions League quarter-finals draw has been completed, setting up some exciting matchups between Europe\'s top teams...', '2025-04-21 16:53:58'),
(2, 'cricket', 'IPL 2024 Opening Ceremony Details Revealed', 'The Indian Premier League 2024 is set to begin with a spectacular opening ceremony featuring top performers and cricket legends...', '2025-04-21 16:53:58'),
(3, 'football', 'Premier League Title Race Heats Up', 'With just 10 games remaining in the season, three teams are separated by just four points in an intense title race...', '2025-04-21 16:53:58'),
(4, 'cricket', 'World Cup 2024 Schedule Released', 'The ICC has announced the complete schedule for the upcoming T20 World Cup, with matches spread across multiple venues...', '2025-04-21 16:53:58'),
(5, 'football', 'Transfer News: Star Striker Set for Record Move', 'One of football\'s biggest names is reportedly close to completing a record-breaking transfer to a major European club...', '2025-04-21 16:53:58');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter`
--

CREATE TABLE `newsletter` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `email`, `created_at`) VALUES
(1, 'varunpndey@outlook.com', '2025-04-21 21:57:39'),
(2, 'varun.raj.joshi@gmail.com', '2025-04-21 22:13:15'),
(3, 'satyajitrout658@gmail.com', '2025-04-22 03:39:18'),
(4, 'joshivarun266@gmail.com', '2025-04-22 04:16:50'),
(5, 'abhaylal122@gmail.com', '2025-04-22 05:33:58');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `verification_code` varchar(100) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `favorite_sport` varchar(50) DEFAULT NULL,
  `favorite_team` varchar(50) DEFAULT NULL,
  `account_type` enum('user','admin') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `verification_code`, `is_verified`, `created_at`, `favorite_sport`, `favorite_team`, `account_type`) VALUES
(1, 'varunpandey', 'varunpandey@outlook.com', '$2y$10$l6H3WUsx0DAq3ojvDExoDOVFFIE.2rzUq48yI/bNPXvaYadcT.WSW', 'admin', '6acd6d029fe434e23c485189b175843a', 0, '2025-04-19 16:49:06', NULL, NULL, 'user'),
(2, 'varunjoshi', 'varunjoshi@gmail.com', '$2y$10$6ngMMpULgWewpK/48/JLtONg7/KQCVhfgtKmySVtdsNTl72UayW6C', 'admin', '56be60a7b9b593d5afdd9fac0c625425', 0, '2025-04-19 17:23:22', NULL, NULL, 'user'),
(3, 'varun', 'varun@gmail.com', '$2y$10$UUaZzQwsWDrLXMSnvpGpoeOijpGv1M5R67HfrdnO0vIT4ZxeZZPci', 'user', 'bc0a38990fa12434edb102f0c76e4fa5', 1, '2025-04-19 17:33:59', NULL, NULL, 'user'),
(4, 'varunpndey', 'varunpndey@outlook.com', '$2y$10$RHpOEMOFSfaAS8Kllxj5geuc.38dAVZwbraJtQV5wZwECEGwnCOie', 'admin', 'a60e4370b2114b07af94f57fb7a22cda', 1, '2025-04-19 17:35:21', NULL, NULL, 'user'),
(6, 'user', 'user@gmail.com', '25f9e794323b453885f5181f1b624d0b', 'user', NULL, 0, '2025-04-19 21:35:27', NULL, NULL, 'user'),
(7, 'tester', 'tester@gmail.com', '25f9e794323b453885f5181f1b624d0b', 'user', NULL, 0, '2025-04-19 21:36:37', NULL, NULL, 'user'),
(8, 'kallu', 'kallu@gmail.com', '25f9e794323b453885f5181f1b624d0b', 'user', NULL, 0, '2025-04-19 21:37:14', NULL, NULL, 'user'),
(9, 'rowdy', 'rowdy@gmail.com', '25f9e794323b453885f5181f1b624d0b', 'user', NULL, 0, '2025-04-19 21:39:06', NULL, NULL, 'user'),
(10, 'fidaa', 'fidaa@gmail.com', '25f9e794323b453885f5181f1b624d0b', 'user', NULL, 0, '2025-04-19 21:40:51', NULL, NULL, 'user'),
(11, 'berlin', 'berlin@gmail.com', '25f9e794323b453885f5181f1b624d0b', 'user', NULL, 0, '2025-04-19 21:43:44', NULL, NULL, 'user'),
(12, 'varunpndey', 'va@gmail.com', '25f9e794323b453885f5181f1b624d0b', 'admin', NULL, 0, '2025-04-20 05:38:14', NULL, NULL, 'admin'),
(13, 'testuser', 'test@example.com', 'cc03e747a6afbbcbf8be7668acfebee5', 'user', NULL, 0, '2025-04-22 04:11:11', NULL, NULL, 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `match_id` (`match_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `newsletter`
--
ALTER TABLE `newsletter`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

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
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `matches`
--
ALTER TABLE `matches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `newsletter`
--
ALTER TABLE `newsletter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
