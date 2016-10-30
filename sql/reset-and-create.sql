-- Adminer 4.2.5 MySQL dump

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `play`;
CREATE TABLE `play` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time_utc` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `track_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `track_id` (`track_id`),
  CONSTRAINT `play_ibfk_1` FOREIGN KEY (`track_id`) REFERENCES `track` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `track`;
CREATE TABLE `track` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `artist` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `play_count` int(11) NOT NULL DEFAULT '0',
  `vote_count` int(11) NOT NULL DEFAULT '0',
  `vote_total` int(11) DEFAULT NULL,
  `vote_average` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `key` (`key`),
  KEY `artist` (`artist`),
  KEY `title` (`title`),
  KEY `play_count` (`play_count`),
  KEY `vote_count` (`vote_count`),
  KEY `vote_total` (`vote_total`),
  KEY `vote_average` (`vote_average`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `vote`;
CREATE TABLE `vote` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time_utc` datetime NOT NULL,
  `track_id` int(11) NOT NULL,
  `value` tinyint(4) NOT NULL,
  `nick` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source` enum('irc','twitter') COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_authed` bit(1) NOT NULL,
  `deleted` bit(1) NOT NULL DEFAULT b'0',
  `comment` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time_utc` (`time_utc`),
  KEY `track_id` (`track_id`),
  KEY `nick` (`nick`),
  KEY `source` (`source`),
  KEY `deleted` (`deleted`),
  CONSTRAINT `vote_ibfk_1` FOREIGN KEY (`track_id`) REFERENCES `track` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 2016-10-19 05:32:59
