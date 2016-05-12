-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 11, 2016 at 12:28 AM
-- Server version: 5.5.49-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `phantom_sushi_notify`
--

-- --------------------------------------------------------

--
-- Table structure for table `notify_scheme`
--

CREATE TABLE IF NOT EXISTS `notify_scheme` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'scheme ID',
  `notify_delay_sec` int(11) NOT NULL COMMENT 'delay time in seconds',
  `notify_action` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'action in JSON',
  `monitor_sushi` int(11) NOT NULL COMMENT 'phantom sushi ID',
  `enabled` tinyint(1) NOT NULL COMMENT 'is the scheme enabled',
  `phantom_user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phantom_user_id` (`phantom_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `notify_task`
--

CREATE TABLE IF NOT EXISTS `notify_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `notify_scheme_id` int(11) NOT NULL,
  `state` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `phantom_user`
--

CREATE TABLE IF NOT EXISTS `phantom_user` (
  `phantom_user_id` int(11) NOT NULL COMMENT 'Unique User ID on phantom',
  `name` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Phantom user name',
  `access_token` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `access_token_expire` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `refresh_token` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`phantom_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sushi_update_time`
--

CREATE TABLE IF NOT EXISTS `sushi_update_time` (
  `phantom_user_id` int(11) NOT NULL,
  `sushi_id` int(11) NOT NULL,
  `update_event_time` bigint(20) NOT NULL,
  PRIMARY KEY (`phantom_user_id`,`sushi_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
