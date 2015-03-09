-- phpMyAdmin SQL Dump
-- version 4.0.10.8
-- http://www.phpmyadmin.net
--
-- Host: 127.11.203.130:3306
-- Generation Time: Mar 09, 2015 at 12:53 PM
-- Server version: 5.5.41
-- PHP Version: 5.3.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `dc`
--
CREATE DATABASE IF NOT EXISTS `dc` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `dc`;

-- --------------------------------------------------------

--
-- Table structure for table `appointment`
--

DROP TABLE IF EXISTS `appointment`;
CREATE TABLE IF NOT EXISTS `appointment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `consultant_id` int(11) NOT NULL,
  `date_time` datetime NOT NULL,
  `health_issue` text NOT NULL,
  `attachment` mediumtext COMMENT 'max 16MB',
  `type` enum('follow-up','referral','normal') NOT NULL DEFAULT 'normal',
  `referrer_name` varchar(128) NOT NULL DEFAULT '',
  `referrer_clinic` varchar(128) NOT NULL DEFAULT '',
  `previous_id` int(11) DEFAULT NULL,
  `remarks` text NOT NULL,
  `status` enum('pending','accepted','rejected','finished','cancelled') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(128) NOT NULL,
  `password` varchar(128) NOT NULL,
  `full_name` varchar(128) NOT NULL,
  `address` varchar(128) NOT NULL,
  `passport_number` varchar(16) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `date_of_birth` date NOT NULL,
  `nationality` varchar(64) NOT NULL,
  `type` enum('admin','consultant','patient') NOT NULL,
  `notification` enum('local','email','sms','all') NOT NULL DEFAULT 'local',
  `password_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
