-- MySQL dump 10.13  Distrib 5.5.34, for Linux (x86_64)
--
-- Host: localhost    Database: alzughai_familytree
-- ------------------------------------------------------
-- Server version	5.5.34-cll

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `box_account`
--

DROP TABLE IF EXISTS `box_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `box_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subscriber_id` int(11) NOT NULL,
  `iban` varchar(100) NOT NULL,
  `balance` varchar(50) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=754 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `box_behavior`
--

DROP TABLE IF EXISTS `box_behavior`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `box_behavior` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `dayofmonth` int(11) NOT NULL,
  `for_id` int(11) NOT NULL,
  `details` text NOT NULL,
  `amount` varchar(50) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `box_collector`
--

DROP TABLE IF EXISTS `box_collector`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `box_collector` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `role` enum('collector','manager') NOT NULL,
  `assigned_root_id` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `box_for`
--

DROP TABLE IF EXISTS `box_for`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `box_for` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('withdraw','deposit') NOT NULL,
  `name` varchar(100) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `box_subscriber`
--

DROP TABLE IF EXISTS `box_subscriber`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `box_subscriber` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=755 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `box_transaction`
--

DROP TABLE IF EXISTS `box_transaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `box_transaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `amount` varchar(50) NOT NULL,
  `type` enum('withdraw','deposit') NOT NULL,
  `for_id` int(11) NOT NULL,
  `details` text NOT NULL,
  `status` enum('pending','accepted','rejected') NOT NULL,
  `triggered_by` enum('direct','schedule') NOT NULL,
  `created` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `executed_at` int(11) NOT NULL,
  `executed_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `comment`
--

DROP TABLE IF EXISTS `comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `author_id` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `comment_like`
--

DROP TABLE IF EXISTS `comment_like`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comment_like` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `committee`
--

DROP TABLE IF EXISTS `committee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `committee` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `tasks` text NOT NULL,
  `members_description` text NOT NULL,
  `keywords` text NOT NULL,
  `minimum_age` int(11) NOT NULL,
  `priority` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `company`
--

DROP TABLE IF EXISTS `company`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `type` int(8) NOT NULL,
  `name` varchar(250) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=278 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dean`
--

DROP TABLE IF EXISTS `dean`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dean` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `period_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `slogan` text NOT NULL,
  `platform` text NOT NULL,
  `selected` tinyint(1) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `deanship_period`
--

DROP TABLE IF EXISTS `deanship_period`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deanship_period` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_period` date NOT NULL,
  `to_period` date NOT NULL,
  `status` enum('nomination','voting','ongoing','finished') NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `event`
--

DROP TABLE IF EXISTS `event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `day` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `title` varchar(350) NOT NULL,
  `content` text NOT NULL,
  `type` enum('meeting','wedding','death','baby_born','news') NOT NULL,
  `location` varchar(350) NOT NULL,
  `latitude` varchar(50) NOT NULL,
  `longitude` varchar(50) NOT NULL,
  `author_id` int(11) NOT NULL,
  `time` varchar(20) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `event_reaction`
--

DROP TABLE IF EXISTS `event_reaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_reaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `reaction` enum('come','not_come') NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('idea','bug','praise') NOT NULL,
  `page` varchar(300) NOT NULL,
  `content` varchar(500) NOT NULL,
  `user_agent` varchar(300) NOT NULL,
  `http_referer` varchar(300) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `page` (`page`),
  KEY `content` (`content`),
  KEY `user_agent` (`user_agent`),
  KEY `http_referer` (`http_referer`)
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hobby`
--

DROP TABLE IF EXISTS `hobby`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hobby` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `rank` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `inactive_users`
--

DROP TABLE IF EXISTS `inactive_users`;
/*!50001 DROP VIEW IF EXISTS `inactive_users`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `inactive_users` (
  `user_id` tinyint NOT NULL,
  `first_login` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `job`
--

DROP TABLE IF EXISTS `job`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(350) NOT NULL,
  `en_title` varchar(350) NOT NULL,
  `description` text NOT NULL,
  `responsibilities` text NOT NULL,
  `qualifications` text NOT NULL,
  `desired_skills` text NOT NULL,
  `hired` tinyint(1) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `married`
--

DROP TABLE IF EXISTS `married`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `married` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `husband_id` int(8) NOT NULL,
  `wife_id` int(8) NOT NULL,
  `marital_status` enum('married','divorced','widow','widower') NOT NULL DEFAULT 'married',
  PRIMARY KEY (`id`),
  KEY `husband_id` (`husband_id`),
  KEY `wife_id` (`wife_id`)
) ENGINE=InnoDB AUTO_INCREMENT=803 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `media`
--

DROP TABLE IF EXISTS `media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `type` enum('photo','video') NOT NULL,
  `name` varchar(500) NOT NULL,
  `size` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `hash` varchar(40) NOT NULL,
  `title` varchar(350) NOT NULL,
  `description` text NOT NULL,
  `views` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `media_comment`
--

DROP TABLE IF EXISTS `media_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `media_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `media_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `author_id` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `media_comment_like`
--

DROP TABLE IF EXISTS `media_comment_like`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `media_comment_like` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `media_comment_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `media_reaction`
--

DROP TABLE IF EXISTS `media_reaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `media_reaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `media_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `reaction` enum('like') NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=163 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member`
--

DROP TABLE IF EXISTS `member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `tribe_id` int(11) NOT NULL,
  `mother_id` int(8) NOT NULL DEFAULT '-1',
  `father_id` int(8) NOT NULL,
  `descenders` int(8) NOT NULL,
  `alive_descenders` int(8) NOT NULL,
  `name` varchar(150) NOT NULL,
  `nickname` varchar(100) NOT NULL,
  `fullname` varchar(350) NOT NULL,
  `gender` int(4) NOT NULL DEFAULT '1',
  `blood_type` varchar(10) NOT NULL,
  `dob` date NOT NULL,
  `age` int(11) NOT NULL,
  `pob` varchar(150) NOT NULL,
  `is_alive` tinyint(1) NOT NULL DEFAULT '1',
  `dod` date NOT NULL,
  `location` varchar(150) NOT NULL,
  `living` varchar(20) NOT NULL,
  `neighborhood` varchar(100) NOT NULL,
  `education` int(4) NOT NULL,
  `major` varchar(200) NOT NULL,
  `company_id` int(8) NOT NULL DEFAULT '-1',
  `job_title` varchar(250) NOT NULL,
  `salary` int(11) NOT NULL DEFAULT '0',
  `marital_status` int(4) NOT NULL DEFAULT '0',
  `mobile` int(11) NOT NULL,
  `phone_home` int(11) NOT NULL,
  `phone_work` int(11) NOT NULL,
  `fax` int(11) NOT NULL,
  `email` varchar(250) NOT NULL,
  `website` varchar(300) NOT NULL,
  `facebook` varchar(250) NOT NULL,
  `twitter` varchar(100) NOT NULL,
  `linkedin` varchar(200) NOT NULL,
  `flickr` varchar(200) NOT NULL,
  `flag` int(4) NOT NULL,
  `photo` varchar(300) NOT NULL,
  `cv` text NOT NULL,
  `notes` text NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `privacy_mother` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'related_circle',
  `privacy_partners` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'related_circle',
  `privacy_daughters` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'related_circle',
  `privacy_mobile` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_phone_home` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_phone_work` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_fax` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_email` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_dob` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_pob` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_dod` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_age` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_education` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_major` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_company` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_job_title` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_marital_status` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_blood_type` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_location` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_living` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_neighborhood` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
  `privacy_salary` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'admins',
  `privacy_website` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'all',
  `privacy_facebook` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'all',
  `privacy_twitter` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'all',
  `privacy_linkedin` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'all',
  `privacy_flickr` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'all',
  `privacy_hobby` enum('all','members','related_circle','admins') NOT NULL DEFAULT 'all',
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `nickname` (`nickname`),
  KEY `fullname` (`fullname`),
  KEY `email` (`email`),
  KEY `job_title` (`job_title`),
  KEY `gender` (`gender`)
) ENGINE=InnoDB AUTO_INCREMENT=4518 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_committee`
--

DROP TABLE IF EXISTS `member_committee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_committee` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `committee_id` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected','resigned','nominee') NOT NULL DEFAULT 'pending',
  `member_title` enum('member','head') NOT NULL DEFAULT 'member',
  `reason` text NOT NULL,
  `joined` int(11) NOT NULL,
  `leaved` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_dean`
--

DROP TABLE IF EXISTS `member_dean`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_dean` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `dean_id` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_hobby`
--

DROP TABLE IF EXISTS `member_hobby`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_hobby` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `hobby_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  KEY `hobby_id` (`hobby_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1346 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `member_job`
--

DROP TABLE IF EXISTS `member_job`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_job` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_question`
--

DROP TABLE IF EXISTS `member_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_question` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=620 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notification`
--

DROP TABLE IF EXISTS `notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('request_receive','request_reject','request_accept','password_change','committee_join_request_receive','committee_nominee','event_add','event_react_come','event_react_not_come','comment_response','comment_like','media_comment_response','media_comment_like','media_like','media_add') NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` varchar(300) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `link` varchar(300) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `content` (`content`),
  KEY `link` (`link`)
) ENGINE=InnoDB AUTO_INCREMENT=29926 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prepared_relation`
--

DROP TABLE IF EXISTS `prepared_relation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prepared_relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(300) NOT NULL,
  `relation` text NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ramadan_question`
--

DROP TABLE IF EXISTS `ramadan_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ramadan_question` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` text NOT NULL,
  `answer1` text NOT NULL,
  `answer2` text NOT NULL,
  `answer3` text NOT NULL,
  `answer4` text NOT NULL,
  `correct_answer` int(11) NOT NULL,
  `day` int(11) NOT NULL,
  `positive_message` text NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `request`
--

DROP TABLE IF EXISTS `request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `random_key` varchar(10) NOT NULL,
  `title` varchar(250) NOT NULL,
  `description` text NOT NULL,
  `phpscript` text NOT NULL,
  `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `reason` text NOT NULL,
  `affected_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `assigned_to` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  `executed` int(11) NOT NULL,
  `executed_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `random_key` (`random_key`),
  KEY `title` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=1953 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tagmember`
--

DROP TABLE IF EXISTS `tagmember`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tagmember` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('event','media') NOT NULL,
  `content_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  `width` int(11) NOT NULL DEFAULT '100',
  `height` int(11) NOT NULL DEFAULT '100',
  `top` int(11) NOT NULL,
  `left` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tribe`
--

DROP TABLE IF EXISTS `tribe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tribe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=348 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(32) NOT NULL,
  `usergroup` enum('user','moderator','admin') NOT NULL,
  `member_id` int(11) NOT NULL,
  `twitter_userid` varchar(32) NOT NULL,
  `twitter_oauth_token` varchar(300) NOT NULL,
  `twitter_oautho_secret` varchar(300) NOT NULL,
  `assigned_root_id` int(11) NOT NULL,
  `sms_received` tinyint(1) NOT NULL,
  `first_login` tinyint(1) NOT NULL DEFAULT '1',
  `last_login_time` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`,`password`),
  KEY `username_2` (`username`),
  KEY `password` (`password`),
  KEY `usergroup` (`usergroup`)
) ENGINE=InnoDB AUTO_INCREMENT=2234 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Final view structure for view `inactive_users`
--

/*!50001 DROP TABLE IF EXISTS `inactive_users`*/;
/*!50001 DROP VIEW IF EXISTS `inactive_users`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`alzughai`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `inactive_users` AS select `user`.`id` AS `user_id`,`user`.`first_login` AS `first_login` from (`user` join `member`) where ((`user`.`member_id` = `member`.`id`) and (`member`.`location` <> '') and (`user`.`first_login` = 1)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-01-07  0:00:11
