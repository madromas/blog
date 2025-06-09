-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 08, 2025 at 08:22 PM
-- Server version: 10.11.10-MariaDB-cll-lve
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `russian7_mad`
--

-- --------------------------------------------------------

--
-- Table structure for table `achievements`
--

CREATE TABLE `achievements` (
  `id` varchar(50) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `points` int(11) NOT NULL,
  `icon` varchar(50) NOT NULL,
  `target_value` int(11) DEFAULT NULL,
  `is_hidden` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `achievements`
--

INSERT INTO `achievements` (`id`, `title`, `description`, `points`, `icon`, `target_value`, `is_hidden`) VALUES
('first_comment', 'A start has been made', 'Left first comment', 15, 'fa-comment', 1, 0),
('read_rules', 'A law-abiding citizen', 'Read website rules', 10, 'fa-book', 1, 0),
('registered', 'Welcome', 'Registered on the website', 5, 'fa-user-plus', 1, 0),
('returned', 'Welcome back', 'Visit the website a day after registration', 10, 'fa-redo', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `auth_tokens`
--

CREATE TABLE `auth_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `selector` varchar(255) NOT NULL,
  `hashed_token` varchar(255) NOT NULL,
  `expiry` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auth_tokens`
--

INSERT INTO `auth_tokens` (`id`, `user_id`, `selector`, `hashed_token`, `expiry`) VALUES
(7, 9, '9bd854ec365303c7', 'bdda048b9691cf7110a59f104cb1b5e410be1e0591218e41a1ddd760bbedf482', '2025-07-08 19:49:49');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `content`, `created_at`, `image`) VALUES
(16, 1, 9, 'Hell yea!', '2025-06-08 00:12:23', NULL),
(30, 13, 12, 'Noice!', '2025-06-08 23:41:08', '');

-- --------------------------------------------------------

--
-- Table structure for table `content_reports`
--

CREATE TABLE `content_reports` (
  `id` int(11) NOT NULL,
  `post_id` int(11) DEFAULT NULL,
  `comment_id` int(11) DEFAULT NULL,
  `story_id` int(11) DEFAULT NULL,
  `reporter_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `moderator_id` int(11) DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `levels`
--

CREATE TABLE `levels` (
  `level_name` varchar(50) NOT NULL,
  `min_points` int(11) NOT NULL,
  `next_level_points` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `levels`
--

INSERT INTO `levels` (`level_name`, `min_points`, `next_level_points`) VALUES
('Bronze', 0, 100),
('Gold', 300, 600),
('Legend', 1000, 0),
('Platinum', 600, 1000),
('Silver', 100, 300);

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `upvotes` int(11) DEFAULT 0,
  `downvotes` int(11) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `comments_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `title`, `content`, `image`, `tags`, `upvotes`, `downvotes`, `views`, `comments_count`, `created_at`) VALUES
(1, 9, 'Shannon McMullen', 'Noice', 'img_6844d55075b27.webp', '', 1, 0, 7, 12, '2025-06-08 00:12:00'),
(3, 9, 'Helga Lovekaty', 'girl', 'img_6844e658e6f2c.webp', 'girl, sexy', 0, 0, 3, 0, '2025-06-08 01:24:40'),
(5, 9, 'kirstentoosweet', 'What an ass', 'img_68459379bb403.jpg', 'kirstentoosweet, girl, sexy, ass', 1, 0, 2, 0, '2025-06-08 13:43:21'),
(6, 9, 'Nicole Borda', '...', 'img_68459577d065d.jpg', 'sexy, latina', 0, 0, 3, 0, '2025-06-08 13:51:51'),
(9, 9, 'Jump', 'https://img-9gag-fun.9cache.com/photo/an7Dmyn_460svvp9.webm', NULL, 'girl, fit', 0, 0, 1, 0, '2025-06-08 19:19:45'),
(10, 9, 'What do girls think when they catch a man&#039;s gaze on them?', '1. I usually check right away if I&#039;m okay. Maybe there are food stains on the clothes somewhere, or food is left on the face. You can&#039;t tell if he&#039;s evaluating you or just looking at you.\r\n\r\n2. Careful peeks through a book or phone seem very pleasant to me and are not so flattering. It&#039;s just as cute when a guy turns away in embarrassment because you caught his eye. I immediately smile and blush. It&#039;s nice to know that someone is attracted to you. But if a person looks at you with obvious lust, does not look away and grins like an evil clown, it&#039;s immediately obvious.\r\n\r\n3. I&#039;m trying to figure out who he&#039;s really looking at. Probably some pretty girl. I was at an event. At some point, everyone, and I too, started dancing. Suddenly, a handsome man started making circles around me. And I just stopped and started looking around to figure out who he wanted to dance with.\r\n\r\n4. It depends on the situation. Recently, a guy came up to my car and leaned on the hood to check out my breasts. It was so disgusting. I was even upset because the guy was cute. In general, it depends on whether you know how to behave or not.\r\n\r\n5. Since this rarely happens, I always think that someone has finally seen a person in me, and even an attractive one.\r\n\r\n6. When a guy pays attention to me, it immediately seems to me that he has decided that I am strange, or something is wrong with my clothes. The idea that he might like me seems so incredible that it doesn&#039;t even occur to me.\r\n\r\n7. When you catch quick glances or playful glances, it&#039;s very flattering, and my day gets a little better. But when people stare at you without looking away, you immediately feel uneasy, and even sometimes you begin to fear for your life.\r\n\r\n8. It depends on whether I find him attractive or not. I&#039;m usually stared at by men over 30, and it&#039;s creepy because I&#039;m 18. In such a situation, I try to move to the side where I am not visible, although there have been several cases that I have been chased. If you like a guy, I can look back at him and make eyes at him.\r\n\r\n9. I answer the girls who write here that they have never been evaluated. Yes, you just didn&#039;t notice it, because most men don&#039;t stare openly, but work with a passing glance, like a ninja. It takes us less than a second to evaluate you.\r\n\r\n10. I hope he doesn&#039;t come over to get acquainted and ruin my day. I came to the hardware store to shop for repairs, not to look for new friends. I&#039;m surprised, but men in construction stare at me most of the time.\r\n\r\n11. To be honest, I don&#039;t like meeting people in inappropriate places at all. But if someone asks for it, then on the one hand you have to be polite so as not to offend the person by refusing, on the other hand, many people do not understand the word &quot;no&quot; or take it for a hidden &quot;yes&quot;.\r\n\r\n12. Listen to advice from a slightly tired lady. Never follow a girl, assessing her from behind. Don&#039;t touch her if you decide to get to know her. Don&#039;t stare openly, don&#039;t point a finger at her. Leave immediately if she has made it clear that the sympathy is not mutual. Always behave respectfully. If you want to compliment her, praise her clothes, jewelry, hairstyle, but not her figure. Always stand up for a girl if you see someone starting to behave suspiciously. In short, just be a normal person.', NULL, 'woman, think, look', 0, 0, 1, 0, '2025-06-08 21:13:12'),
(11, 9, 'Honda N 360 keicar', 'car', 'img_684604ec5aa9a.webp', 'car, honda', 0, 0, 2, 0, '2025-06-08 21:47:24'),
(12, 9, 'ashydeluca', '...', 'img_68461c6f1595a.webp', 'ashydeluca, girl', 0, 0, 1, 0, '2025-06-08 23:27:07'),
(13, 9, 'Priscilla Huggins', 'https://img-9gag-fun.9cache.com/photo/adB2GBM_460svvp9.webm', NULL, 'girl, undressing, sexy', 1, 0, 5, 1, '2025-06-08 23:33:27');

-- --------------------------------------------------------

--
-- Table structure for table `private_messages`
--

CREATE TABLE `private_messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `private_messages`
--

INSERT INTO `private_messages` (`id`, `sender_id`, `receiver_id`, `content`, `is_read`, `created_at`) VALUES
(11, 12, 9, 'Hello', 1, '2025-06-08 23:42:05'),
(12, 9, 12, 'Howdy?', 1, '2025-06-08 23:43:38');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `session_id` varchar(128) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stories`
--

CREATE TABLE `stories` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `subscriber_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `subscriber_id`, `user_id`, `created_at`) VALUES
(5, 12, 9, '2025-06-08 23:49:08');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','moderator','admin') DEFAULT 'user',
  `avatar` varchar(255) DEFAULT 'default.png',
  `about` text DEFAULT NULL,
  `rating` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `followers_count` int(11) DEFAULT 0,
  `show_email` tinyint(1) NOT NULL DEFAULT 0,
  `allow_private_messages` tinyint(1) NOT NULL DEFAULT 0,
  `show_online_status` tinyint(1) NOT NULL DEFAULT 0,
  `profile_visibility` enum('public','registered','private') NOT NULL DEFAULT 'public'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `avatar`, `about`, `rating`, `created_at`, `last_login`, `followers_count`, `show_email`, `allow_private_messages`, `show_online_status`, `profile_visibility`) VALUES
(9, 'Roman', 'madromas@yahoo.com', '$2y$10$XuBkWSCW2s/3O8izlsxSZeIPjmywyq8Vqh7FIUFMqm.6bJXrtdiii', 'admin', 'img_6840d560b4e12.jpg', 'idiot', 3, '2025-06-04 23:00:56', '2025-06-08 19:49:49', 1, 0, 0, 0, 'public'),
(12, 'punk', 'madwaynet@gmail.com', '$2y$10$As/3pNXlCFNJPNgn7POADe8cc1kmgF4mNWfs95/KXURsq0WbAKIH2', 'user', 'default.png', NULL, 20, '2025-06-08 23:40:32', '2025-06-08 19:44:08', 0, 0, 0, 0, 'public');

-- --------------------------------------------------------

--
-- Table structure for table `user_achievements`
--

CREATE TABLE `user_achievements` (
  `user_id` int(11) NOT NULL,
  `achievement_id` varchar(50) NOT NULL,
  `progress` int(11) DEFAULT 0,
  `is_completed` tinyint(1) DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `user_achievements`
--

INSERT INTO `user_achievements` (`user_id`, `achievement_id`, `progress`, `is_completed`, `completed_at`) VALUES
(9, 'first_comment', 1, 1, '2025-06-04 23:11:28'),
(9, 'registered', 1, 1, '2025-06-04 23:00:56'),
(9, 'returned', 1, 1, '2025-06-06 23:05:27'),
(12, 'first_comment', 1, 1, '2025-06-08 23:41:08'),
(12, 'registered', 1, 1, '2025-06-08 23:40:32');

-- --------------------------------------------------------

--
-- Table structure for table `user_reports`
--

CREATE TABLE `user_reports` (
  `id` int(11) NOT NULL,
  `reported_user_id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `moderator_id` int(11) DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `type` enum('upvote','downvote') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`id`, `user_id`, `post_id`, `type`, `created_at`) VALUES
(30, 9, 1, 'upvote', '2025-06-08 00:53:10'),
(33, 9, 5, 'upvote', '2025-06-08 20:58:23'),
(34, 9, 13, 'upvote', '2025-06-09 00:19:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `content_reports`
--
ALTER TABLE `content_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `comment_id` (`comment_id`),
  ADD KEY `story_id` (`story_id`),
  ADD KEY `reporter_id` (`reporter_id`),
  ADD KEY `moderator_id` (`moderator_id`);

--
-- Indexes for table `levels`
--
ALTER TABLE `levels`
  ADD PRIMARY KEY (`level_name`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `private_messages`
--
ALTER TABLE `private_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `stories`
--
ALTER TABLE `stories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subscriber_id` (`subscriber_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD PRIMARY KEY (`user_id`,`achievement_id`),
  ADD KEY `achievement_id` (`achievement_id`);

--
-- Indexes for table `user_reports`
--
ALTER TABLE `user_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reported_user_id` (`reported_user_id`),
  ADD KEY `reporter_id` (`reporter_id`),
  ADD KEY `moderator_id` (`moderator_id`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`post_id`),
  ADD KEY `post_id` (`post_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `content_reports`
--
ALTER TABLE `content_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `private_messages`
--
ALTER TABLE `private_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `stories`
--
ALTER TABLE `stories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `user_reports`
--
ALTER TABLE `user_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `content_reports`
--
ALTER TABLE `content_reports`
  ADD CONSTRAINT `content_reports_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_reports_ibfk_2` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_reports_ibfk_3` FOREIGN KEY (`story_id`) REFERENCES `stories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_reports_ibfk_4` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_reports_ibfk_5` FOREIGN KEY (`moderator_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `private_messages`
--
ALTER TABLE `private_messages`
  ADD CONSTRAINT `private_messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `private_messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stories`
--
ALTER TABLE `stories`
  ADD CONSTRAINT `stories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`subscriber_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscriptions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD CONSTRAINT `user_achievements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_achievements_ibfk_2` FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_reports`
--
ALTER TABLE `user_reports`
  ADD CONSTRAINT `user_reports_ibfk_1` FOREIGN KEY (`reported_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_reports_ibfk_2` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_reports_ibfk_3` FOREIGN KEY (`moderator_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
