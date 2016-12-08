-- phpMyAdmin SQL Dump
-- version 2.10.0.2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Feb 06, 2010 at 05:46 PM
-- Server version: 5.0.27
-- PHP Version: 5.2.0

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- --------------------------------------------------------

-- 
-- Table structure for table `lt_map`
-- 

CREATE TABLE `lt_map` (
  `id` int(11) NOT NULL auto_increment,
  `name` tinytext NOT NULL,
  `width_px` int(11) NOT NULL,
  `height_px` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

-- 
-- Dumping data for table `lt_map`
-- 

INSERT INTO `lt_map` (`id`, `name`, `width_px`, `height_px`) VALUES 
(9, 'Sample Map 1', 700, 450);

-- --------------------------------------------------------

-- 
-- Table structure for table `lt_position`
-- 

CREATE TABLE `lt_position` (
  `id` int(11) NOT NULL auto_increment,
  `tag_id` int(11) NOT NULL,
  `xpos` int(11) NOT NULL,
  `ypos` int(11) NOT NULL,
  `map_id` int(11) NOT NULL,
  `reported` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `tag_id` (`tag_id`),
  KEY `map_id` (`map_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=38 ;

-- 
-- Dumping data for table `lt_position`
-- 

INSERT INTO `lt_position` (`id`, `tag_id`, `xpos`, `ypos`, `map_id`, `reported`) VALUES 
(1, 3, 130, 100, 9, '2010-02-06 17:34:05'),
(2, 2, 130, 100, 9, '2010-02-06 17:34:05'),
(3, 4, 130, 100, 9, '2010-02-06 17:34:05'),
(4, 5, 130, 100, 9, '2010-02-06 17:34:05'),
(5, 6, 130, 100, 9, '2010-02-06 17:34:05'),
(6, 3, 130, 340, 9, '2010-02-06 17:34:07'),
(7, 3, 420, 300, 9, '2010-02-06 17:34:10'),
(8, 5, 130, 340, 9, '2010-02-06 17:34:11'),
(9, 2, 130, 340, 9, '2010-02-06 17:34:11'),
(10, 4, 130, 340, 9, '2010-02-06 17:34:11'),
(11, 3, 650, 410, 9, '2010-02-06 17:34:13'),
(12, 4, 420, 300, 9, '2010-02-06 17:34:14'),
(13, 5, 420, 300, 9, '2010-02-06 17:34:14'),
(14, 4, 310, 140, 9, '2010-02-06 17:34:16'),
(15, 5, 480, 150, 9, '2010-02-06 17:34:17'),
(16, 3, 420, 300, 9, '2010-02-06 17:34:18'),
(17, 6, 130, 340, 9, '2010-02-06 17:34:18'),
(18, 3, 640, 160, 9, '2010-02-06 17:34:20'),
(19, 2, 420, 300, 9, '2010-02-06 17:34:22'),
(20, 6, 420, 300, 9, '2010-02-06 17:34:23'),
(21, 6, 640, 260, 9, '2010-02-06 17:34:24'),
(22, 2, 650, 410, 9, '2010-02-06 17:34:25'),
(23, 4, 420, 300, 9, '2010-02-06 17:34:29'),
(24, 4, 130, 340, 9, '2010-02-06 17:34:30'),
(25, 4, 130, 100, 9, '2010-02-06 17:34:31'),
(26, 5, 420, 300, 9, '2010-02-06 17:34:32'),
(27, 3, 420, 300, 9, '2010-02-06 17:34:33'),
(28, 6, 420, 300, 9, '2010-02-06 17:34:34'),
(29, 2, 420, 300, 9, '2010-02-06 17:34:35'),
(30, 5, 130, 340, 9, '2010-02-06 17:34:36'),
(31, 3, 130, 340, 9, '2010-02-06 17:34:37'),
(32, 6, 130, 340, 9, '2010-02-06 17:34:38'),
(33, 2, 130, 340, 9, '2010-02-06 17:34:38'),
(34, 5, 130, 100, 9, '2010-02-06 17:34:39'),
(35, 3, 130, 100, 9, '2010-02-06 17:34:39'),
(36, 6, 130, 100, 9, '2010-02-06 17:34:40'),
(37, 2, 130, 100, 9, '2010-02-06 17:34:41');

-- --------------------------------------------------------

-- 
-- Table structure for table `lt_reader`
-- 

CREATE TABLE `lt_reader` (
  `id` int(11) NOT NULL auto_increment,
  `map_id` int(11) NOT NULL,
  `addr` char(6) NOT NULL,
  `name` tinytext NOT NULL,
  `xpos` int(11) NOT NULL,
  `ypos` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `alias` (`addr`),
  KEY `map_id` (`map_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;

-- 
-- Dumping data for table `lt_reader`
-- 

INSERT INTO `lt_reader` (`id`, `map_id`, `addr`, `name`, `xpos`, `ypos`) VALUES 
(10, 9, '000001', 'Stock Room', 130, 100),
(11, 9, '000002', 'Office 2', 310, 140),
(12, 9, '000003', 'Office', 480, 150),
(13, 9, '000004', 'Staff Lounge', 640, 160),
(14, 9, '000005', 'Shop Area 2', 130, 340),
(15, 9, '000006', 'Shop Area 1', 420, 300),
(16, 9, '000007', 'Kitchen', 640, 260),
(17, 9, '000008', 'Reception', 650, 410);

-- --------------------------------------------------------

-- 
-- Table structure for table `lt_tag`
-- 

CREATE TABLE `lt_tag` (
  `id` int(11) NOT NULL auto_increment,
  `addr` char(6) NOT NULL,
  `name` tinytext NOT NULL,
  `colour` char(7) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `alias` (`addr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- 
-- Dumping data for table `lt_tag`
-- 

INSERT INTO `lt_tag` (`id`, `addr`, `name`, `colour`) VALUES 
(2, '200000', 'Test Tag 2', '#88c54c'),
(3, '100000', 'Test Tag 1', '#f06363'),
(4, '300000', 'Test Tag 3', '#55aae0'),
(5, '400000', 'Test Tag 4', '#c560f4'),
(6, '500000', 'Test Tag 5', '#ffb85c');

-- --------------------------------------------------------

-- 
-- Table structure for table `lt_user`
-- 

CREATE TABLE `lt_user` (
  `id` int(11) NOT NULL auto_increment,
  `email` tinytext NOT NULL,
  `password` char(32) NOT NULL,
  `name` tinytext NOT NULL,
  `registered` datetime NOT NULL,
  `lastlogin` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `lt_user`
-- 

INSERT INTO `lt_user` (`id`, `email`, `password`, `name`, `registered`, `lastlogin`) VALUES 
(1, 'demo@ns-tech.co.uk', 'fe01ce2a7fbac8fafaed7c982a04e229', 'Demo User', '2010-01-03 16:44:58', '2010-02-06 17:42:11');

-- 
-- Constraints for dumped tables
-- 

-- 
-- Constraints for table `lt_position`
-- 
ALTER TABLE `lt_position`
  ADD CONSTRAINT `lt_position_ibfk_1` FOREIGN KEY (`tag_id`) REFERENCES `lt_tag` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lt_position_ibfk_2` FOREIGN KEY (`map_id`) REFERENCES `lt_map` (`id`) ON DELETE CASCADE;

-- 
-- Constraints for table `lt_reader`
-- 
ALTER TABLE `lt_reader`
  ADD CONSTRAINT `lt_reader_ibfk_1` FOREIGN KEY (`map_id`) REFERENCES `lt_map` (`id`) ON DELETE CASCADE;
