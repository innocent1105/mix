-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 02, 2024 at 02:16 AM
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
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_id` bigint(150) NOT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone_number` varchar(45) DEFAULT NULL,
  `whatsapp` varchar(45) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `pp` varchar(255) NOT NULL DEFAULT 'default-pp.png',
  `account_type` varchar(200) DEFAULT 'user',
  `dob` varchar(40) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `relationship_status` varchar(100) DEFAULT NULL,
  `paid` varchar(10) DEFAULT NULL,
  `subscription_type` varchar(200) DEFAULT NULL,
  `subscriprion_end_date` varchar(200) DEFAULT NULL,
  `swipes_remaining` varchar(10) DEFAULT NULL,
  `matches_remaining` varchar(10) DEFAULT NULL,
  `country` varchar(30) DEFAULT NULL,
  `city` varchar(30) DEFAULT NULL,
  `lng` varchar(100) DEFAULT NULL,
  `lat` varchar(100) DEFAULT NULL,
  `ipaddress` varchar(100) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `matches` (
  `id` int(11) NOT NULL,
  `user_id` bigint(150) NOT NULL,
  `other_user` varchar(255) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `interests` (
  `id` int(11) NOT NULL,
  `user_id` varchar (150) NOT NULL,
  `interest` varchar(255) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `followers` (
  `id` int(11) NOT NULL,
  `user_id` varchar (150) NOT NULL,
  `followed_user_id` varchar (150) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `age_range` (
  `id` int(11) NOT NULL,
  `user_id` varchar (150) NOT NULL,
  `age` varchar(255) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `hobbies` (
  `id` int(11) NOT NULL,
  `user_id` bigint(150) NOT NULL,
  `hobby` varchar(255) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `languages` (
  `id` int(11) NOT NULL,
  `user_id` bigint(150) NOT NULL,
  `language` varchar(255) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `body_features` (
  `id` int(11) NOT NULL,
  `user_id` bigint(150) NOT NULL,
  `body_size` varchar(255) DEFAULT NULL,
  `height` varchar(255) DEFAULT NULL,
  `complection` varchar(255) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `lifestyles` (
  `id` int(11) NOT NULL,
  `user_id` bigint(150) NOT NULL,
  `lifestyle` varchar(255) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `chats` (
  `id` int(11) NOT NULL,
  `sender` varchar (150) NOT NULL,
  `reciever` varchar(150) NOT NULL,
  `message` varchar(255) NOT NULL,
  `message_type` varchar(45) NOT NULL DEFAULT 'text',
  `status` varchar(45) NOT NULL DEFAULT 'sent',
  `attachment_name` varchar(255) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender` varchar (150) NOT NULL,
  `reciever` varchar(150) NOT NULL,
  `message` varchar(255) NOT NULL,
  `message_type` varchar(45) NOT NULL DEFAULT 'text',
  `status` varchar(45) NOT NULL DEFAULT 'sent',
  `attachment_name` varchar(255) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` varchar (150) NOT NULL,
  `post_text` varchar(200) NOT NULL,
  `location` varchar(45) NOT NULL DEFAULT 'text',
  `images` varchar(45) NOT NULL DEFAULT 'none',
  `attachment_name` varchar(255) DEFAULT NULL,
  `views` varchar(255) DEFAULT NULL,
  `likes` varchar(255) DEFAULT NULL,
  `comments` varchar(255) DEFAULT NULL,
  `shares` varchar(255) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `pins` (
  `id` int(11) NOT NULL,
  `user_id` varchar (150) NOT NULL,
  `name` varchar(200) NOT NULL,
  `des` varchar(200) NOT NULL,
  `tag` varchar(200) NOT NULL,
  `images` varchar(200) NOT NULL,
  `location` varchar(250) NOT NULL DEFAULT 'text',
  `lng` varchar(45) NOT NULL DEFAULT 'none',
  `lat` varchar(45) NOT NULL DEFAULT 'none',
  `rating` varchar(50) DEFAULT NULL,
  `views` varchar(255) DEFAULT NULL,
  `likes` varchar(255) DEFAULT NULL,
  `comments` varchar(255) DEFAULT NULL,
  `shares` varchar(255) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;






CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `user_id` varchar (150) NOT NULL,
  `post_id` varchar(200) NOT NULL,
  `image` varchar(45) NOT NULL DEFAULT 'none',
  `attachment_type` varchar(255) DEFAULT 'text',
  `comment` varchar(255) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;







CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `user_id` varchar (150) NOT NULL,
  `post_id` varchar(200) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `views` (
  `id` int(11) NOT NULL,
  `user_id` varchar (150) NOT NULL,
  `post_id` varchar(200) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `shares` (
  `id` int(11) NOT NULL,
  `user_id` varchar (150) NOT NULL,
  `post_id` varchar(200) NOT NULL,
  `content` varchar(200) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;







ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;


















