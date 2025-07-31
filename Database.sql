-- Copy Paste this code into phpMyAdmin's SQL to create all necessaty databases to start.

-- Create the database
CREATE DATABASE IF NOT EXISTS complain_portal;
USE complain_portal;

-- Create table `complaints`
CREATE TABLE IF NOT EXISTS `complaints` (
  `complaint_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_email` varchar(200) NOT NULL,
  `gov_email` varchar(200) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `location` varchar(255) NOT NULL,
  `votes` int(11) DEFAULT 0,
  `status` varchar(256) DEFAULT 'Unresolved',
  `image` varchar(255) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `date_reported` datetime DEFAULT current_timestamp(),
  `last_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sector` varchar(200) NOT NULL,
  PRIMARY KEY (`complaint_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create table `otp`
CREATE TABLE IF NOT EXISTS `otp` (
  `email` varchar(200) NOT NULL,
  `otp` int(10) NOT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create table `user`
CREATE TABLE IF NOT EXISTS `user` (
  `user_email` varchar(200) NOT NULL,
  `name` varchar(200) NOT NULL,
  `contact` varchar(200) NOT NULL,
  `type` int(10) NOT NULL,
  `sector` varchar(200) NOT NULL,
  PRIMARY KEY (`user_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create table `votes`
CREATE TABLE IF NOT EXISTS `votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_email` varchar(255) DEFAULT NULL,
  `complaint_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_vote` (`user_email`, `complaint_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert admin user
INSERT INTO `user` (`user_email`, `name`, `contact`, `type`, `sector`) 
VALUES ('admin@gmail.com', 'Admi Nistrator', '9841984198', 3, 'Admin');
