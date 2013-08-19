-- Database setup for Five Card Flickr
-- see https://github.com/cogdog/5cardflickr
-- 
-- Database: `5card`
-- 

-- create database; if your server does not allow, remove this line and create database
-- via your control panel
CREATE DATABASE IF NOT EXISTS `5card` DEFAULT CHARACTER SET latin1 COLLATE latin1_general_ci;
USE `5card`;

-- --------------------------------------------------------

--
-- Table structure for table `cards`
--

CREATE TABLE IF NOT EXISTS `cards` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `fid` varchar(12) NOT NULL,
  `farm` tinyint(4) NOT NULL,
  `server` smallint(6) NOT NULL,
  `secret` varchar(10) NOT NULL,
  `nsid` varchar(20) NOT NULL,
  `username` varchar(48) NOT NULL,
  `tag` varchar(31) NOT NULL,
  `when` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fid` (`fid`),
  KEY `tag` (`tag`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `stories`
--

CREATE TABLE IF NOT EXISTS `stories` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `deck` varchar(18) NOT NULL,
  `cards` varchar(32) NOT NULL,
  `title` varchar(72) NOT NULL,
  `name` varchar(48) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comments` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;