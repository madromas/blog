-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Апр 25 2025 г., 22:10
-- Версия сервера: 5.7.21-20-beget-5.7.21-20-1-log
-- Версия PHP: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `vmordovii_ai77`
--

-- --------------------------------------------------------

--
-- Структура таблицы `achievements`
--
-- Создание: Апр 24 2025 г., 18:14
-- Последнее обновление: Апр 24 2025 г., 18:21
--

DROP TABLE IF EXISTS `achievements`;
CREATE TABLE `achievements` (
  `id` varchar(50) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `points` int(11) NOT NULL,
  `icon` varchar(50) NOT NULL,
  `target_value` int(11) DEFAULT NULL,
  `is_hidden` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `achievements`
--

INSERT INTO `achievements` (`id`, `title`, `description`, `points`, `icon`, `target_value`, `is_hidden`) VALUES
('first_comment', 'Начало положено', 'Оставить первый комментарий', 15, 'fa-comment', 1, 0),
('read_rules', 'Законопослушный гражданин', 'Прочитать правила Пикабу', 10, 'fa-book', 1, 0),
('registered', 'Дратути', 'Зарегистрироваться на Пикабу', 5, 'fa-user-plus', 1, 0),
('returned', 'С возвращением', 'Зайти на сайт спустя сутки после регистрации', 10, 'fa-redo', 1, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `comments`
--
-- Создание: Апр 24 2025 г., 15:19
-- Последнее обновление: Апр 25 2025 г., 18:28
--

DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `content`, `created_at`) VALUES
(1, 1, 2, 'cvxc', '2025-04-24 17:32:42'),
(2, 2, 2, 'ава', '2025-04-24 19:21:55'),
(3, 2, 2, 'ddd', '2025-04-24 19:21:59'),
(4, 4, 5, 'ааа', '2025-04-24 21:56:54'),
(5, 2, 5, 'ывм', '2025-04-25 08:10:54'),
(6, 2, 5, 'gdfgdf', '2025-04-25 08:14:51'),
(7, 2, 5, 'ddd', '2025-04-25 08:18:41'),
(8, 2, 5, 'sdfsdf', '2025-04-25 08:21:00'),
(9, 1, 5, 'Да конеечно', '2025-04-25 18:28:01');

-- --------------------------------------------------------

--
-- Структура таблицы `content_reports`
--
-- Создание: Апр 24 2025 г., 15:19
--

DROP TABLE IF EXISTS `content_reports`;
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
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `levels`
--
-- Создание: Апр 24 2025 г., 18:14
-- Последнее обновление: Апр 24 2025 г., 18:21
--

DROP TABLE IF EXISTS `levels`;
CREATE TABLE `levels` (
  `level_name` varchar(50) NOT NULL,
  `min_points` int(11) NOT NULL,
  `next_level_points` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `levels`
--

INSERT INTO `levels` (`level_name`, `min_points`, `next_level_points`) VALUES
('Бронза', 0, 100),
('Золото', 300, 600),
('Легенда', 1000, 0),
('Платина', 600, 1000),
('Серебро', 100, 300);

-- --------------------------------------------------------

--
-- Структура таблицы `posts`
--
-- Создание: Апр 24 2025 г., 15:19
-- Последнее обновление: Апр 25 2025 г., 19:10
--

DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `upvotes` int(11) DEFAULT '0',
  `downvotes` int(11) DEFAULT '0',
  `views` int(11) DEFAULT '0',
  `comments_count` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `title`, `content`, `image`, `tags`, `upvotes`, `downvotes`, `views`, `comments_count`, `created_at`) VALUES
(1, 1, 'Привет всем', 'Всем добрый вечееер', '680a5a6a546e7.jpg', 'Теги, теги2', 0, 1, 13, 2, '2025-04-24 15:36:10'),
(2, 2, 'доброго дня вам', 'и вам привет', 'img_680a83029685f.png', 'теги, теги2, теги3', 0, 1, 27, 6, '2025-04-24 18:29:22'),
(3, 2, 'ыва', 'ыва', NULL, '', 0, 0, 15, 0, '2025-04-24 18:51:54'),
(4, 5, 'Привет пост', 'ПНовый пост пикабу', 'img_680ab3638a5c1.png', 'ааа', 0, 0, 40, 1, '2025-04-24 21:55:47'),
(5, 5, 'авпва', 'пвапвапвап', NULL, '', 0, 0, 6, 0, '2025-04-25 08:06:02'),
(6, 5, 'Привет всем друзья', 'Друзья всем привет', 'img_680b46509eba3.jpg', '', 0, 1, 11, 0, '2025-04-25 08:22:40'),
(7, 5, '234234', '234234234', 'img_680ba2833b125.png', '', 0, 0, 10, 0, '2025-04-25 14:56:03'),
(8, 5, 'первый пост пикабу', 'Пикабуууууу', 'img_680bdda6d6034.jpg', 'пикабу, бб', 0, 0, 2, 0, '2025-04-25 19:08:22');

-- --------------------------------------------------------

--
-- Структура таблицы `private_messages`
--
-- Создание: Апр 24 2025 г., 15:19
-- Последнее обновление: Апр 25 2025 г., 18:21
--

DROP TABLE IF EXISTS `private_messages`;
CREATE TABLE `private_messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `private_messages`
--

INSERT INTO `private_messages` (`id`, `sender_id`, `receiver_id`, `content`, `is_read`, `created_at`) VALUES
(1, 5, 2, 'привет', 0, '2025-04-25 09:01:59'),
(2, 3, 5, 'Привет', 1, '2025-04-25 09:24:41'),
(3, 5, 3, 'пррррпр', 1, '2025-04-25 09:25:44'),
(4, 3, 5, 'Да', 1, '2025-04-25 09:26:10'),
(5, 5, 3, 'пвапва', 1, '2025-04-25 09:38:13'),
(6, 5, 2, 'вапвап', 0, '2025-04-25 09:38:17'),
(7, 3, 5, 'Давай', 1, '2025-04-25 10:18:32'),
(8, 5, 3, 'Привет', 1, '2025-04-25 17:47:21'),
(9, 3, 5, 'Привеет', 1, '2025-04-25 18:21:10'),
(10, 5, 3, 'gjrf', 0, '2025-04-25 18:21:56');

-- --------------------------------------------------------

--
-- Структура таблицы `sessions`
--
-- Создание: Апр 24 2025 г., 15:19
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `session_id` varchar(128) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `stories`
--
-- Создание: Апр 24 2025 г., 15:19
-- Последнее обновление: Апр 25 2025 г., 19:06
--

DROP TABLE IF EXISTS `stories`;
CREATE TABLE `stories` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `stories`
--

INSERT INTO `stories` (`id`, `user_id`, `content`, `image`, `created_at`, `expires_at`, `is_active`) VALUES
(5, 1, 'Привеет', '680a5a894c0b6.jpg', '2025-04-24 15:36:41', NULL, 1),
(6, 2, 'Привееет', '680a75d2ed5e9.jpg', '2025-04-24 17:33:06', '2025-04-25 17:33:06', 1),
(7, 5, 'Вот история !!!', 'img_680aab6a99114.png', '2025-04-24 21:21:46', '2025-04-25 21:21:46', 1),
(8, 5, 'апр', 'img_680ab3b5339a2.png', '2025-04-24 21:57:09', '2025-04-25 21:57:09', 1),
(9, 3, 'Я Елена', 'img_680ab57c9e69b.jpg', '2025-04-24 22:04:44', '2025-04-25 22:04:44', 1),
(10, 5, 'пппппв', 'img_680b469083567.webp', '2025-04-25 08:23:44', '2025-04-26 08:23:44', 1),
(12, 5, '', 'img_680b5f1567469.png', '2025-04-25 10:08:21', '2025-04-26 10:08:21', 1),
(13, 5, '', 'img_680b89022a8a3.png', '2025-04-25 13:07:14', '2025-04-26 13:07:14', 1),
(14, 5, '', 'img_680bdd3dced4d.jpg', '2025-04-25 19:06:37', '2025-04-26 19:06:37', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `subscriptions`
--
-- Создание: Апр 24 2025 г., 18:58
-- Последнее обновление: Апр 25 2025 г., 19:09
--

DROP TABLE IF EXISTS `subscriptions`;
CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `subscriber_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `subscriber_id`, `user_id`, `created_at`) VALUES
(1, 2, 1, '2025-04-24 19:02:33'),
(2, 5, 3, '2025-04-25 08:27:50'),
(3, 5, 2, '2025-04-25 18:10:42'),
(4, 8, 5, '2025-04-25 19:09:06');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--
-- Создание: Апр 24 2025 г., 19:49
-- Последнее обновление: Апр 25 2025 г., 19:09
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','moderator','admin') DEFAULT 'user',
  `avatar` varchar(255) DEFAULT 'default.png',
  `about` text,
  `rating` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` datetime DEFAULT NULL,
  `followers_count` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `avatar`, `about`, `rating`, `created_at`, `last_login`, `followers_count`) VALUES
(1, 'Serega261', 'Serega126@bk.ru', '$2y$10$hFmqyrSTC4JuEA.qC2btfOM9ShVFRpGQv5ClBMH1iSj2fEk24wL/.', 'user', '680a5a44ec78f.jpg', NULL, -1, '2025-04-24 15:34:22', NULL, 1),
(2, 'Serega262', 'Serega26222@bk.ru', '$2y$10$CtvrVauEdfAS5UYrE7dqv.ce43fFE9LpF2pV0r3K/lMVoSLpF/8n.', 'user', 'img_680a763792541.jpg', NULL, -1, '2025-04-24 17:28:44', '2025-04-24 21:39:39', 1),
(3, 'Eleba55', 'Eleba55@bk.ru', '$2y$10$Mf4UUBSYbV1IS6/pl98AE.Ff3B4kucV/KTgCIFdI2A65XeaQxvwFO', 'user', 'default.png', NULL, 5, '2025-04-24 18:47:34', '2025-04-25 21:20:58', 1),
(4, 'admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'default.png', NULL, 0, '2025-04-24 19:50:32', NULL, 0),
(5, 'Serega26', 'Serega26@bk.ru', '$2y$10$aKebjbTEFJRZUvbrWWteguLdmFhjbhfXFhPp0nRwZh5bkDrgVmWdi', 'admin', 'img_680ab4ef21e30.png', NULL, -1, '2025-04-24 19:56:30', '2025-04-25 21:26:13', 1),
(6, 'bLZbWbcyNKfob', 'rdelacruzx78@gmail.com', '$2y$10$awADjtP2qVOFs7M.rWWQuOlNE/zpkKyzJQJBudj2HJO0ZK4VpmHZS', 'user', 'default.png', NULL, 5, '2025-04-24 21:38:16', NULL, 0),
(7, 'zauHhxib', 'shelagkey@gmail.com', '$2y$10$Me6dEWXt/K91ipxrugpZ9.VRvWCmr8VUIg7brUytzHovnKduhAp6m', 'user', 'default.png', NULL, 5, '2025-04-25 16:57:50', NULL, 0),
(8, 'bereg62', 'bereg62@bk.ru', '$2y$10$dDzdM1LW1IVGK5.851IcYuslVHPzBK0.Gle9odSyZ3dnAmgs6Em9i', 'user', 'default.png', NULL, 5, '2025-04-25 19:08:54', '2025-04-25 22:09:48', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `user_achievements`
--
-- Создание: Апр 24 2025 г., 18:14
-- Последнее обновление: Апр 25 2025 г., 19:08
--

DROP TABLE IF EXISTS `user_achievements`;
CREATE TABLE `user_achievements` (
  `user_id` int(11) NOT NULL,
  `achievement_id` varchar(50) NOT NULL,
  `progress` int(11) DEFAULT '0',
  `is_completed` tinyint(1) DEFAULT '0',
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `user_achievements`
--

INSERT INTO `user_achievements` (`user_id`, `achievement_id`, `progress`, `is_completed`, `completed_at`) VALUES
(2, 'first_comment', 1, 1, '2025-04-24 19:21:55'),
(3, 'registered', 1, 1, '2025-04-24 18:47:34'),
(5, 'first_comment', 1, 1, '2025-04-24 21:56:54'),
(5, 'registered', 1, 1, '2025-04-24 19:56:30'),
(6, 'registered', 1, 1, '2025-04-24 21:38:16'),
(7, 'registered', 1, 1, '2025-04-25 16:57:50'),
(8, 'registered', 1, 1, '2025-04-25 19:08:54');

-- --------------------------------------------------------

--
-- Структура таблицы `user_reports`
--
-- Создание: Апр 24 2025 г., 15:19
--

DROP TABLE IF EXISTS `user_reports`;
CREATE TABLE `user_reports` (
  `id` int(11) NOT NULL,
  `reported_user_id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `moderator_id` int(11) DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `votes`
--
-- Создание: Апр 24 2025 г., 15:19
-- Последнее обновление: Апр 25 2025 г., 14:40
--

DROP TABLE IF EXISTS `votes`;
CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `type` enum('upvote','downvote') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `votes`
--

INSERT INTO `votes` (`id`, `user_id`, `post_id`, `type`, `created_at`) VALUES
(1, 2, 1, 'downvote', '2025-04-24 17:32:50'),
(17, 5, 2, 'downvote', '2025-04-24 21:56:42'),
(26, 5, 6, 'downvote', '2025-04-25 14:37:43');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `content_reports`
--
ALTER TABLE `content_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `comment_id` (`comment_id`),
  ADD KEY `story_id` (`story_id`),
  ADD KEY `reporter_id` (`reporter_id`),
  ADD KEY `moderator_id` (`moderator_id`);

--
-- Индексы таблицы `levels`
--
ALTER TABLE `levels`
  ADD PRIMARY KEY (`level_name`);

--
-- Индексы таблицы `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `private_messages`
--
ALTER TABLE `private_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Индексы таблицы `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `stories`
--
ALTER TABLE `stories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subscriber_id` (`subscriber_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Индексы таблицы `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD PRIMARY KEY (`user_id`,`achievement_id`),
  ADD KEY `achievement_id` (`achievement_id`);

--
-- Индексы таблицы `user_reports`
--
ALTER TABLE `user_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reported_user_id` (`reported_user_id`),
  ADD KEY `reporter_id` (`reporter_id`),
  ADD KEY `moderator_id` (`moderator_id`);

--
-- Индексы таблицы `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`post_id`),
  ADD KEY `post_id` (`post_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `content_reports`
--
ALTER TABLE `content_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `private_messages`
--
ALTER TABLE `private_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `stories`
--
ALTER TABLE `stories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT для таблицы `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `user_reports`
--
ALTER TABLE `user_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `content_reports`
--
ALTER TABLE `content_reports`
  ADD CONSTRAINT `content_reports_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_reports_ibfk_2` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_reports_ibfk_3` FOREIGN KEY (`story_id`) REFERENCES `stories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_reports_ibfk_4` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_reports_ibfk_5` FOREIGN KEY (`moderator_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `private_messages`
--
ALTER TABLE `private_messages`
  ADD CONSTRAINT `private_messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `private_messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `stories`
--
ALTER TABLE `stories`
  ADD CONSTRAINT `stories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`subscriber_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscriptions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD CONSTRAINT `user_achievements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_achievements_ibfk_2` FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_reports`
--
ALTER TABLE `user_reports`
  ADD CONSTRAINT `user_reports_ibfk_1` FOREIGN KEY (`reported_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_reports_ibfk_2` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_reports_ibfk_3` FOREIGN KEY (`moderator_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
