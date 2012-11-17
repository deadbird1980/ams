-- phpMyAdmin SQL Dump
-- version 3.2.0.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 23, 2009 at 06:41 PM
-- Server version: 5.1.36
-- PHP Version: 5.3.0

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `acldemo`
--

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `pwd` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `first_name` varchar(64) COLLATE utf8_unicode_ci,
  `last_name` varchar(64) COLLATE utf8_unicode_ci,
  `first_name_alphabet` varchar(64) COLLATE utf8_unicode_ci,
  `last_name_alphabet` varchar(64) COLLATE utf8_unicode_ci,
  `address` varchar(64) COLLATE utf8_unicode_ci,
  `post_code` varchar(10) COLLATE utf8_unicode_ci,
  `email` varchar(20) COLLATE utf8_unicode_ci,

  `group` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'member',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `pwd`, `group`) VALUES
(1, 'demo', '1234', 'member'),
(2, 'admin', '1234', 'admin'),
(3, 'david', '1234', 'member'),
(4, 'doophp', '1234', 'member');
