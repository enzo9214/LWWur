-- phpMyAdmin SQL Dump
-- version 2.11.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 10, 2008 at 08:04 PM
-- Server version: 4.1.22
-- PHP Version: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `search_MusicSearch`
--

-- --------------------------------------------------------

--
-- Table structure for table `mp_admin_errors`
--

DROP TABLE IF EXISTS `mp_admin_errors`;
CREATE TABLE IF NOT EXISTS `mp_admin_errors` (
  `id` mediumint(9) NOT NULL auto_increment,
  `file_name` varchar(255) NOT NULL default '',
  `log_type` char(1) NOT NULL default 'd',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=164410 ;

-- --------------------------------------------------------

--
-- Table structure for table `mp_admin_summary`
--

DROP TABLE IF EXISTS `mp_admin_summary`;
CREATE TABLE IF NOT EXISTS `mp_admin_summary` (
  `id` mediumint(9) NOT NULL auto_increment,
  `total_count` mediumint(9) NOT NULL default '0',
  `tagged_count` mediumint(11) NOT NULL default '0',
  `sample_count` mediumint(9) NOT NULL default '0',
  `duplicate_count` mediumint(9) NOT NULL default '0',
  `empty_tag_count` mediumint(9) NOT NULL default '0',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `time_taken` float NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Table structure for table `mp_id3_artists`
--

DROP TABLE IF EXISTS `mp_id3_artists`;
CREATE TABLE IF NOT EXISTS `mp_id3_artists` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `top_tracks` text NOT NULL,
  `top_albums` text NOT NULL,
  `artist_genre` varchar(255) NOT NULL default '',
  `similar_artists` text NOT NULL,
  `artist_biography` longtext NOT NULL,
  `artist_image` varchar(255) NOT NULL default '',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3270 ;

-- --------------------------------------------------------

--
-- Table structure for table `mp_id3_lyrics`
--

DROP TABLE IF EXISTS `mp_id3_lyrics`;
CREATE TABLE IF NOT EXISTS `mp_id3_lyrics` (
  `id` int(11) NOT NULL auto_increment,
  `track_id` int(11) NOT NULL default '0',
  `lyrics` longtext NOT NULL,
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=46304 ;

-- --------------------------------------------------------

--
-- Table structure for table `mp_id3_tags`
--

DROP TABLE IF EXISTS `mp_id3_tags`;
CREATE TABLE IF NOT EXISTS `mp_id3_tags` (
  `ID` mediumint(8) unsigned NOT NULL auto_increment,
  `filename` text NOT NULL,
  `audio_bitrate` float NOT NULL default '0',
  `playtime_seconds` double NOT NULL default '0',
  `artist` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `album` varchar(255) NOT NULL default '',
  `genre` varchar(255) NOT NULL default '',
  `track` varchar(7) NOT NULL default '',
  `year` varchar(10) NOT NULL default '',
  `file_type` varchar(10) NOT NULL default '',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `is_corrected` tinyint(4) NOT NULL default '0',
  `is_lyrics` tinyint(4) NOT NULL default '0',
  `is_clipped` tinyint(4) NOT NULL default '0',
  `is_artist_details` tinyint(4) NOT NULL default '0',
  `is_artist_bio` tinyint(4) NOT NULL default '0',
  `album_art` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`ID`),
  KEY `artist` (`artist`),
  KEY `genre` (`genre`),
  KEY `file_type` (`file_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=47093 ;
