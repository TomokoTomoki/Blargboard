-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Mer 25 Décembre 2013 à 23:00
-- Version du serveur: 5.5.24-log
-- Version de PHP: 5.3.13

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `blargboard`
--

-- --------------------------------------------------------

--
-- Structure de la table `badges`
--

CREATE TABLE IF NOT EXISTS `badges` (
  `owner` int(11) NOT NULL DEFAULT '0',
  `name` varchar(256) NOT NULL DEFAULT '',
  `color` int(8) NOT NULL DEFAULT '0',
  UNIQUE KEY `steenkinbadger` (`owner`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `blockedlayouts`
--

CREATE TABLE IF NOT EXISTS `blockedlayouts` (
  `user` int(11) NOT NULL DEFAULT '0',
  `blockee` int(11) NOT NULL DEFAULT '0',
  KEY `mainkey` (`blockee`,`user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL DEFAULT '',
  `corder` int(8) NOT NULL DEFAULT '0',
  `board` varchar(16) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1  ;

--
-- Contenu de la table `categories`
--

INSERT INTO `categories` (`id`, `name`, `corder`, `board`) VALUES
(1, 'Sample category', 0, '');

-- --------------------------------------------------------

--
-- Structure de la table `enabledplugins`
--

CREATE TABLE IF NOT EXISTS `enabledplugins` (
  `plugin` varchar(256) NOT NULL DEFAULT '',
  UNIQUE KEY `plugin` (`plugin`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `favorites`
--

CREATE TABLE IF NOT EXISTS `favorites` (
  `user` int(11) NOT NULL,
  `thread` int(11) NOT NULL,
  PRIMARY KEY (`user`,`thread`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `forums`
--

CREATE TABLE IF NOT EXISTS `forums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(256) NOT NULL DEFAULT '',
  `description` text,
  `catid` int(8) NOT NULL DEFAULT '0',
  `numthreads` int(11) NOT NULL DEFAULT '0',
  `numposts` int(11) NOT NULL DEFAULT '0',
  `lastpostdate` int(11) NOT NULL DEFAULT '0',
  `lastpostuser` int(11) NOT NULL DEFAULT '0',
  `lastpostid` int(11) NOT NULL DEFAULT '0',
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `forder` int(8) NOT NULL DEFAULT '0',
  `board` varchar(16) NOT NULL,
  `l` int(11) NOT NULL,
  `r` int(11) NOT NULL,
  `redirect` varchar(256) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `catid` (`catid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

--
-- Contenu de la table `forums`
--

INSERT INTO `forums` (`id`, `title`, `description`, `catid`, `numthreads`, `numposts`, `lastpostdate`, `lastpostuser`, `lastpostid`, `hidden`, `forder`, `board`, `l`, `r`, `redirect`) VALUES
(1, 'Sample forum', 'This is a sample forum. You might want to add some more, though. This is currently configured to serve as everything, from announcements to trash.', 1, 0, 0, 0, 0, 0, 0, 0, '', 1, 2, '');

-- --------------------------------------------------------

--
-- Structure de la table `guests`
--

CREATE TABLE IF NOT EXISTS `guests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(45) NOT NULL DEFAULT '',
  `date` int(11) NOT NULL DEFAULT '0',
  `lasturl` varchar(100) NOT NULL DEFAULT '',
  `lastforum` int(11) NOT NULL DEFAULT '0',
  `useragent` varchar(100) NOT NULL DEFAULT '',
  `bot` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`),
  KEY `bot` (`bot`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `ignoredforums`
--

CREATE TABLE IF NOT EXISTS `ignoredforums` (
  `uid` int(11) NOT NULL DEFAULT '0',
  `fid` int(11) NOT NULL DEFAULT '0',
  KEY `mainkey` (`uid`,`fid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `ip2c`
--

CREATE TABLE IF NOT EXISTS `ip2c` (
  `ip_from` bigint(12) NOT NULL DEFAULT '0',
  `ip_to` bigint(12) NOT NULL DEFAULT '0',
  `cc` varchar(2) DEFAULT '',
  KEY `ip_from` (`ip_from`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `ipbans`
--

CREATE TABLE IF NOT EXISTS `ipbans` (
  `ip` varchar(45) NOT NULL DEFAULT '',
  `reason` varchar(100) NOT NULL DEFAULT '',
  `date` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `ip` (`ip`),
  KEY `date` (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `misc`
--

CREATE TABLE IF NOT EXISTS `misc` (
  `version` int(11) NOT NULL DEFAULT '0',
  `views` int(11) NOT NULL DEFAULT '0',
  `hotcount` int(11) NOT NULL DEFAULT '0',
  `porabox` text,
  `poratitle` varchar(100) NOT NULL DEFAULT '',
  `maxusers` int(11) NOT NULL DEFAULT '0',
  `maxusersdate` int(11) NOT NULL DEFAULT '0',
  `maxuserstext` text,
  `maxpostsday` int(11) NOT NULL DEFAULT '0',
  `maxpostsdaydate` int(11) NOT NULL DEFAULT '0',
  `maxpostshour` int(11) NOT NULL DEFAULT '0',
  `maxpostshourdate` int(11) NOT NULL DEFAULT '0',
  `milestone` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `misc`
--

INSERT INTO `misc` (`version`, `views`, `hotcount`, `porabox`, `poratitle`, `maxusers`, `maxusersdate`, `maxuserstext`, `maxpostsday`, `maxpostsdaydate`, `maxpostshour`, `maxpostshourdate`, `milestone`) VALUES
(1337, 0, 30, 'herp', 'derp', 0, 0, '', 0, 0, 0, 0, '');

-- --------------------------------------------------------

--
-- Structure de la table `moodavatars`
--

CREATE TABLE IF NOT EXISTS `moodavatars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `mid` int(11) NOT NULL DEFAULT '0',
  `name` varchar(256) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `mainkey` (`uid`,`mid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1  ;

-- --------------------------------------------------------

--
-- Structure de la table `passmatches`
--

CREATE TABLE IF NOT EXISTS `passmatches` (
  `date` int(11) NOT NULL,
  `ip` varchar(50) NOT NULL,
  `user` int(11) NOT NULL,
  `matches` varchar(200) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `permissions`
--

CREATE TABLE IF NOT EXISTS `permissions` (
  `applyto` tinyint(4) NOT NULL,
  `id` int(11) NOT NULL,
  `perm` varchar(32) NOT NULL,
  `arg` int(11) NOT NULL DEFAULT '0',
  `value` tinyint(4) NOT NULL,
  PRIMARY KEY (`applyto`,`id`,`perm`,`arg`),
  KEY `perm` (`perm`,`arg`),
  KEY `applyto` (`applyto`,`id`),
  KEY `applyto_2` (`applyto`,`id`,`perm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `permissions`
--

INSERT INTO `permissions` (`applyto`, `id`, `perm`, `arg`, `value`) VALUES
(0, -1, 'admin.adminusercomments', 0, -1),
(0, -1, 'admin.banusers', 0, -1),
(0, -1, 'admin.editforums', 0, -1),
(0, -1, 'admin.editgroups', 0, -1),
(0, -1, 'admin.editsettings', 0, -1),
(0, -1, 'admin.editsmilies', 0, -1),
(0, -1, 'admin.editusers', 0, -1),
(0, -1, 'admin.ipsearch', 0, -1),
(0, -1, 'admin.manageipbans', 0, -1),
(0, -1, 'admin.viewadminnotices', 0, -1),
(0, -1, 'admin.viewadminpanel', 0, -1),
(0, -1, 'admin.viewallranks', 0, -1),
(0, -1, 'admin.viewips', 0, -1),
(0, -1, 'admin.viewlkb', 0, -1),
(0, -1, 'admin.viewlog', 0, -1),
(0, -1, 'admin.viewpms', 0, -1),
(0, -1, 'admin.viewstaffpms', 0, -1),
(0, -1, 'forum.postreplies', 0, -1),
(0, -1, 'forum.postthreads', 0, -1),
(0, -1, 'forum.viewforum', 0, 1),
(0, -1, 'mod.closethreads', 0, -1),
(0, -1, 'mod.deleteposts', 0, -1),
(0, -1, 'mod.deletethreads', 0, -1),
(0, -1, 'mod.editposts', 0, -1),
(0, -1, 'mod.movethreads', 0, -1),
(0, -1, 'mod.renamethreads', 0, -1),
(0, -1, 'mod.stickthreads', 0, -1),
(0, -1, 'mod.trashthreads', 0, -1),
(0, -1, 'uploader.deletefiles', 0, -1),
(0, -1, 'uploader.deleteownfiles', 0, -1),
(0, -1, 'uploader.movefiles', 0, -1),
(0, -1, 'uploader.moveownfiles', 0, -1),
(0, -1, 'uploader.uploadfiles', 0, -1),
(0, -1, 'uploader.uploadrestricted', 0, -1),
(0, -1, 'uploader.viewprivate', 0, -1),
(0, -1, 'user.deleteownposts', 0, -1),
(0, -1, 'user.deleteownusercomments', 0, -1),
(0, -1, 'user.doublepost', 0, -1),
(0, -1, 'user.editavatars', 0, -1),
(0, -1, 'user.editbio', 0, -1),
(0, -1, 'user.editdisplayname', 0, -1),
(0, -1, 'user.editownposts', 0, -1),
(0, -1, 'user.editpostlayout', 0, -1),
(0, -1, 'user.editprofile', 0, -1),
(0, -1, 'user.edittitle', 0, -1),
(0, -1, 'user.havetitle', 0, -1),
(0, -1, 'user.postusercomments', 0, -1),
(0, -1, 'user.rateusers', 0, -1),
(0, -1, 'user.renameownthreads', 0, -1),
(0, -1, 'user.reportposts', 0, -1),
(0, -1, 'user.sendpms', 0, 1),
(0, -1, 'user.viewhiddenforums', 0, -1),
(0, -1, 'user.votepolls', 0, -1),
(0, -1, 'user.voteposts', 0, -1),
(0, -1, 'wiki.deletepages', 0, -1),
(0, -1, 'wiki.editpages', 0, -1),
(0, -1, 'wiki.makepagesspecial', 0, -1),
(0, 0, 'admin.adminusercomments', 0, -1),
(0, 0, 'admin.banusers', 0, -1),
(0, 0, 'admin.editforums', 0, -1),
(0, 0, 'admin.editgroups', 0, -1),
(0, 0, 'admin.editsettings', 0, -1),
(0, 0, 'admin.editsmilies', 0, -1),
(0, 0, 'admin.editusers', 0, -1),
(0, 0, 'admin.ipsearch', 0, -1),
(0, 0, 'admin.manageipbans', 0, -1),
(0, 0, 'admin.viewadminnotices', 0, -1),
(0, 0, 'admin.viewadminpanel', 0, -1),
(0, 0, 'admin.viewallranks', 0, -1),
(0, 0, 'admin.viewips', 0, -1),
(0, 0, 'admin.viewlkb', 0, -1),
(0, 0, 'admin.viewlog', 0, -1),
(0, 0, 'admin.viewpms', 0, -1),
(0, 0, 'admin.viewstaffpms', 0, -1),
(0, 0, 'forum.postreplies', 0, 1),
(0, 0, 'forum.postthreads', 0, 1),
(0, 0, 'forum.viewforum', 0, 1),
(0, 0, 'mod.closethreads', 0, -1),
(0, 0, 'mod.deleteposts', 0, -1),
(0, 0, 'mod.deletethreads', 0, -1),
(0, 0, 'mod.editposts', 0, -1),
(0, 0, 'mod.movethreads', 0, -1),
(0, 0, 'mod.renamethreads', 0, -1),
(0, 0, 'mod.stickthreads', 0, -1),
(0, 0, 'mod.trashthreads', 0, -1),
(0, 0, 'uploader.deletefiles', 0, -1),
(0, 0, 'uploader.deleteownfiles', 0, 1),
(0, 0, 'uploader.movefiles', 0, -1),
(0, 0, 'uploader.moveownfiles', 0, 1),
(0, 0, 'uploader.uploadfiles', 0, 1),
(0, 0, 'uploader.uploadrestricted', 0, -1),
(0, 0, 'uploader.viewprivate', 0, -1),
(0, 0, 'user.deleteownposts', 0, 1),
(0, 0, 'user.deleteownusercomments', 0, 1),
(0, 0, 'user.doublepost', 0, -1),
(0, 0, 'user.editavatars', 0, 1),
(0, 0, 'user.editbio', 0, 1),
(0, 0, 'user.editdisplayname', 0, 1),
(0, 0, 'user.editownposts', 0, 1),
(0, 0, 'user.editpostlayout', 0, 1),
(0, 0, 'user.editprofile', 0, 1),
(0, 0, 'user.edittitle', 0, 1),
(0, 0, 'user.havetitle', 0, -1),
(0, 0, 'user.postusercomments', 0, 1),
(0, 0, 'user.rateusers', 0, 1),
(0, 0, 'user.renameownthreads', 0, 1),
(0, 0, 'user.reportposts', 0, 1),
(0, 0, 'user.sendpms', 0, 1),
(0, 0, 'user.viewhiddenforums', 0, -1),
(0, 0, 'user.votepolls', 0, 1),
(0, 0, 'user.voteposts', 0, 1),
(0, 0, 'wiki.deletepages', 0, -1),
(0, 0, 'wiki.editpages', 0, 1),
(0, 0, 'wiki.makepagesspecial', 0, -1),
(0, 1, 'admin.adminusercomments', 0, -1),
(0, 1, 'admin.banusers', 0, -1),
(0, 1, 'admin.editforums', 0, -1),
(0, 1, 'admin.editgroups', 0, -1),
(0, 1, 'admin.editsettings', 0, -1),
(0, 1, 'admin.editsmilies', 0, -1),
(0, 1, 'admin.editusers', 0, -1),
(0, 1, 'admin.ipsearch', 0, -1),
(0, 1, 'admin.manageipbans', 0, -1),
(0, 1, 'admin.viewadminnotices', 0, -1),
(0, 1, 'admin.viewadminpanel', 0, -1),
(0, 1, 'admin.viewallranks', 0, -1),
(0, 1, 'admin.viewips', 0, -1),
(0, 1, 'admin.viewlkb', 0, -1),
(0, 1, 'admin.viewlog', 0, -1),
(0, 1, 'admin.viewpms', 0, -1),
(0, 1, 'admin.viewstaffpms', 0, -1),
(0, 1, 'forum.postreplies', 0, 1),
(0, 1, 'forum.postthreads', 0, 1),
(0, 1, 'forum.viewforum', 0, 1),
(0, 1, 'uploader.deletefiles', 0, -1),
(0, 1, 'uploader.deleteownfiles', 0, 1),
(0, 1, 'uploader.movefiles', 0, -1),
(0, 1, 'uploader.moveownfiles', 0, 1),
(0, 1, 'uploader.uploadfiles', 0, 1),
(0, 1, 'uploader.uploadrestricted', 0, -1),
(0, 1, 'uploader.viewprivate', 0, -1),
(0, 1, 'user.deleteownposts', 0, 1),
(0, 1, 'user.deleteownusercomments', 0, 1),
(0, 1, 'user.doublepost', 0, -1),
(0, 1, 'user.editavatars', 0, 1),
(0, 1, 'user.editbio', 0, 1),
(0, 1, 'user.editdisplayname', 0, 1),
(0, 1, 'user.editownposts', 0, 1),
(0, 1, 'user.editpostlayout', 0, 1),
(0, 1, 'user.editprofile', 0, 1),
(0, 1, 'user.edittitle', 0, 1),
(0, 1, 'user.havetitle', 0, 1),
(0, 1, 'user.postusercomments', 0, 1),
(0, 1, 'user.rateusers', 0, 1),
(0, 1, 'user.renameownthreads', 0, 1),
(0, 1, 'user.reportposts', 0, 1),
(0, 1, 'user.sendpms', 0, 1),
(0, 1, 'user.viewhiddenforums', 0, -1),
(0, 1, 'user.votepolls', 0, 1),
(0, 1, 'user.voteposts', 0, 1),
(0, 1, 'wiki.deletepages', 0, -1),
(0, 1, 'wiki.editpages', 0, 1),
(0, 1, 'wiki.makepagesspecial', 0, -1),
(0, 2, 'admin.adminusercomments', 0, -1),
(0, 2, 'admin.banusers', 0, 1),
(0, 2, 'admin.editforums', 0, -1),
(0, 2, 'admin.editgroups', 0, -1),
(0, 2, 'admin.editsettings', 0, -1),
(0, 2, 'admin.editsmilies', 0, -1),
(0, 2, 'admin.editusers', 0, -1),
(0, 2, 'admin.ipsearch', 0, -1),
(0, 2, 'admin.manageipbans', 0, -1),
(0, 2, 'admin.viewadminnotices', 0, -1),
(0, 2, 'admin.viewadminpanel', 0, -1),
(0, 2, 'admin.viewallranks', 0, -1),
(0, 2, 'admin.viewips', 0, 1),
(0, 2, 'admin.viewlkb', 0, -1),
(0, 2, 'admin.viewlog', 0, -1),
(0, 2, 'admin.viewpms', 0, -1),
(0, 2, 'admin.viewstaffpms', 0, 1),
(0, 2, 'forum.postreplies', 0, 1),
(0, 2, 'forum.postthreads', 0, 1),
(0, 2, 'forum.viewforum', 0, 1),
(0, 2, 'mod.closethreads', 0, 1),
(0, 2, 'mod.deleteposts', 0, 1),
(0, 2, 'mod.deletethreads', 0, 1),
(0, 2, 'mod.editposts', 0, 1),
(0, 2, 'mod.movethreads', 0, 1),
(0, 2, 'mod.renamethreads', 0, 1),
(0, 2, 'mod.stickthreads', 0, 1),
(0, 2, 'mod.trashthreads', 0, 1),
(0, 2, 'uploader.deletefiles', 0, -1),
(0, 2, 'uploader.deleteownfiles', 0, 1),
(0, 2, 'uploader.movefiles', 0, -1),
(0, 2, 'uploader.moveownfiles', 0, 1),
(0, 2, 'uploader.uploadfiles', 0, 1),
(0, 2, 'uploader.uploadrestricted', 0, -1),
(0, 2, 'uploader.viewprivate', 0, -1),
(0, 2, 'user.deleteownposts', 0, 1),
(0, 2, 'user.deleteownusercomments', 0, 1),
(0, 2, 'user.doublepost', 0, -1),
(0, 2, 'user.editavatars', 0, 1),
(0, 2, 'user.editbio', 0, 1),
(0, 2, 'user.editdisplayname', 0, 1),
(0, 2, 'user.editownposts', 0, 1),
(0, 2, 'user.editpostlayout', 0, 1),
(0, 2, 'user.editprofile', 0, 1),
(0, 2, 'user.edittitle', 0, 1),
(0, 2, 'user.havetitle', 0, 1),
(0, 2, 'user.postusercomments', 0, 1),
(0, 2, 'user.rateusers', 0, 1),
(0, 2, 'user.renameownthreads', 0, 1),
(0, 2, 'user.reportposts', 0, 1),
(0, 2, 'user.sendpms', 0, 1),
(0, 2, 'user.viewhiddenforums', 0, 1),
(0, 2, 'user.votepolls', 0, 1),
(0, 2, 'user.voteposts', 0, 1),
(0, 2, 'wiki.deletepages', 0, 1),
(0, 2, 'wiki.editpages', 0, 1),
(0, 2, 'wiki.makepagesspecial', 0, 1),
(0, 3, 'admin.adminusercomments', 0, 1),
(0, 3, 'admin.banusers', 0, 1),
(0, 3, 'admin.editforums', 0, 1),
(0, 3, 'admin.editgroups', 0, 1),
(0, 3, 'admin.editsettings', 0, 1),
(0, 3, 'admin.editsmilies', 0, 1),
(0, 3, 'admin.editusers', 0, 1),
(0, 3, 'admin.ipsearch', 0, 1),
(0, 3, 'admin.manageipbans', 0, 1),
(0, 3, 'admin.viewadminnotices', 0, 1),
(0, 3, 'admin.viewadminpanel', 0, 1),
(0, 3, 'admin.viewallranks', 0, 1),
(0, 3, 'admin.viewips', 0, 1),
(0, 3, 'admin.viewlkb', 0, 1),
(0, 3, 'admin.viewlog', 0, 1),
(0, 3, 'admin.viewpms', 0, 1),
(0, 3, 'admin.viewstaffpms', 0, 1),
(0, 3, 'forum.postreplies', 0, 1),
(0, 3, 'forum.postthreads', 0, 1),
(0, 3, 'forum.viewforum', 0, 1),
(0, 3, 'mod.closethreads', 0, 1),
(0, 3, 'mod.deleteposts', 0, 1),
(0, 3, 'mod.deletethreads', 0, 1),
(0, 3, 'mod.editposts', 0, 1),
(0, 3, 'mod.movethreads', 0, 1),
(0, 3, 'mod.renamethreads', 0, 1),
(0, 3, 'mod.stickthreads', 0, 1),
(0, 3, 'mod.trashthreads', 0, 1),
(0, 3, 'uploader.deletefiles', 0, 1),
(0, 3, 'uploader.deleteownfiles', 0, 1),
(0, 3, 'uploader.movefiles', 0, 1),
(0, 3, 'uploader.moveownfiles', 0, 1),
(0, 3, 'uploader.uploadfiles', 0, 1),
(0, 3, 'uploader.uploadrestricted', 0, 1),
(0, 3, 'uploader.viewprivate', 0, 1),
(0, 3, 'user.deleteownposts', 0, 1),
(0, 3, 'user.deleteownusercomments', 0, 1),
(0, 3, 'user.doublepost', 0, 1),
(0, 3, 'user.editavatars', 0, 1),
(0, 3, 'user.editbio', 0, 1),
(0, 3, 'user.editdisplayname', 0, 1),
(0, 3, 'user.editnamecolor', 0, 1),
(0, 3, 'user.editownposts', 0, 1),
(0, 3, 'user.editpostlayout', 0, 1),
(0, 3, 'user.editprofile', 0, 1),
(0, 3, 'user.edittitle', 0, 1),
(0, 3, 'user.havetitle', 0, 1),
(0, 3, 'user.postusercomments', 0, 1),
(0, 3, 'user.rateusers', 0, 1),
(0, 3, 'user.renameownthreads', 0, 1),
(0, 3, 'user.reportposts', 0, 1),
(0, 3, 'user.sendpms', 0, 1),
(0, 3, 'user.viewhiddenforums', 0, 1),
(0, 3, 'user.votepolls', 0, 1),
(0, 3, 'user.voteposts', 0, 1),
(0, 3, 'wiki.deletepages', 0, 1),
(0, 3, 'wiki.editpages', 0, 1),
(0, 3, 'wiki.makepagesspecial', 0, 1),
(0, 4, 'admin.adminusercomments', 0, 1),
(0, 4, 'admin.banusers', 0, 1),
(0, 4, 'admin.editforums', 0, 1),
(0, 4, 'admin.editgroups', 0, 1),
(0, 4, 'admin.editsettings', 0, 1),
(0, 4, 'admin.editsmilies', 0, 1),
(0, 4, 'admin.editusers', 0, 1),
(0, 4, 'admin.ipsearch', 0, 1),
(0, 4, 'admin.manageipbans', 0, 1),
(0, 4, 'admin.viewadminnotices', 0, 1),
(0, 4, 'admin.viewadminpanel', 0, 1),
(0, 4, 'admin.viewallranks', 0, 1),
(0, 4, 'admin.viewips', 0, 1),
(0, 4, 'admin.viewlkb', 0, 1),
(0, 4, 'admin.viewlog', 0, 1),
(0, 4, 'admin.viewpms', 0, 1),
(0, 4, 'admin.viewstaffpms', 0, 1),
(0, 4, 'forum.postreplies', 0, 1),
(0, 4, 'forum.postthreads', 0, 1),
(0, 4, 'forum.viewforum', 0, 1),
(0, 4, 'mod.closethreads', 0, 1),
(0, 4, 'mod.deleteposts', 0, 1),
(0, 4, 'mod.deletethreads', 0, 1),
(0, 4, 'mod.editposts', 0, 1),
(0, 4, 'mod.movethreads', 0, 1),
(0, 4, 'mod.renamethreads', 0, 1),
(0, 4, 'mod.stickthreads', 0, 1),
(0, 4, 'mod.trashthreads', 0, 1),
(0, 4, 'uploader.deletefiles', 0, 1),
(0, 4, 'uploader.deleteownfiles', 0, 1),
(0, 4, 'uploader.movefiles', 0, 1),
(0, 4, 'uploader.moveownfiles', 0, 1),
(0, 4, 'uploader.uploadfiles', 0, 1),
(0, 4, 'uploader.uploadrestricted', 0, 1),
(0, 4, 'uploader.viewprivate', 0, 1),
(0, 4, 'user.deleteownposts', 0, 1),
(0, 4, 'user.deleteownusercomments', 0, 1),
(0, 4, 'user.doublepost', 0, 1),
(0, 4, 'user.editavatars', 0, 1),
(0, 4, 'user.editbio', 0, 1),
(0, 4, 'user.editdisplayname', 0, 1),
(0, 4, 'user.editnamecolor', 0, 1),
(0, 4, 'user.editownposts', 0, 1),
(0, 4, 'user.editpostlayout', 0, 1),
(0, 4, 'user.editprofile', 0, 1),
(0, 4, 'user.edittitle', 0, 1),
(0, 4, 'user.havetitle', 0, 1),
(0, 4, 'user.postusercomments', 0, 1),
(0, 4, 'user.rateusers', 0, 1),
(0, 4, 'user.renameownthreads', 0, 1),
(0, 4, 'user.reportposts', 0, 1),
(0, 4, 'user.sendpms', 0, 1),
(0, 4, 'user.viewhiddenforums', 0, 1),
(0, 4, 'user.votepolls', 0, 1),
(0, 4, 'user.voteposts', 0, 1),
(0, 4, 'wiki.deletepages', 0, 1),
(0, 4, 'wiki.editpages', 0, 1),
(0, 4, 'wiki.makepagesspecial', 0, 1);

-- --------------------------------------------------------

--
-- Structure de la table `pmsgs`
--

CREATE TABLE IF NOT EXISTS `pmsgs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userto` int(11) NOT NULL DEFAULT '0',
  `userfrom` int(11) NOT NULL DEFAULT '0',
  `date` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(45) NOT NULL DEFAULT '',
  `msgread` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `drafting` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userto` (`userto`),
  KEY `userfrom` (`userfrom`),
  KEY `msgread` (`msgread`),
  KEY `date` (`date`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1  ;

-- --------------------------------------------------------

--
-- Structure de la table `pmsgs_text`
--

CREATE TABLE IF NOT EXISTS `pmsgs_text` (
  `pid` int(11) NOT NULL DEFAULT '0',
  `title` varchar(256) NOT NULL DEFAULT '',
  `text` mediumtext,
  PRIMARY KEY (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `poll`
--

CREATE TABLE IF NOT EXISTS `poll` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(256) NOT NULL DEFAULT '',
  `briefing` text,
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  `doublevote` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1  ;

-- --------------------------------------------------------

--
-- Structure de la table `pollvotes`
--

CREATE TABLE IF NOT EXISTS `pollvotes` (
  `poll` int(11) NOT NULL DEFAULT '0',
  `choice` int(11) NOT NULL DEFAULT '0',
  `user` int(11) NOT NULL DEFAULT '0',
  `choiceid` int(11) NOT NULL DEFAULT '0',
  KEY `lol` (`user`,`choiceid`),
  KEY `poll` (`poll`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `poll_choices`
--

CREATE TABLE IF NOT EXISTS `poll_choices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `poll` int(11) NOT NULL DEFAULT '0',
  `choice` varchar(256) NOT NULL DEFAULT '',
  `color` varchar(25) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `poll` (`poll`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1  ;

-- --------------------------------------------------------

--
-- Structure de la table `postplusones`
--

CREATE TABLE IF NOT EXISTS `postplusones` (
  `user` int(11) NOT NULL,
  `post` int(11) NOT NULL,
  PRIMARY KEY (`user`,`post`),
  KEY `user` (`user`),
  KEY `post` (`post`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `posts`
--

CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `thread` int(11) NOT NULL DEFAULT '0',
  `user` int(11) NOT NULL DEFAULT '0',
  `date` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(45) NOT NULL DEFAULT '',
  `num` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `deletedby` int(11) NOT NULL DEFAULT '0',
  `reason` varchar(300) NOT NULL DEFAULT '',
  `options` tinyint(4) NOT NULL DEFAULT '0',
  `mood` int(11) NOT NULL DEFAULT '0',
  `currentrevision` int(11) NOT NULL DEFAULT '0',
  `postplusones` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `thread` (`thread`),
  KEY `date` (`date`),
  KEY `user` (`user`),
  KEY `ip` (`ip`),
  KEY `id` (`id`,`currentrevision`),
  KEY `deletedby` (`deletedby`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1  ;

-- --------------------------------------------------------

--
-- Structure de la table `posts_text`
--

CREATE TABLE IF NOT EXISTS `posts_text` (
  `pid` int(11) NOT NULL DEFAULT '0',
  `text` mediumtext,
  `revision` int(11) NOT NULL DEFAULT '0',
  `user` int(11) NOT NULL DEFAULT '0',
  `date` int(11) NOT NULL DEFAULT '0',
  KEY `user` (`user`),
  KEY `pidrevision` (`pid`,`revision`),
  FULLTEXT KEY `text` (`text`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `proxybans`
--

CREATE TABLE IF NOT EXISTS `proxybans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1  ;

-- --------------------------------------------------------

--
-- Structure de la table `queryerrors`
--

CREATE TABLE IF NOT EXISTS `queryerrors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(45) NOT NULL DEFAULT '',
  `time` int(11) NOT NULL DEFAULT '0',
  `query` text,
  `get` text,
  `post` text,
  `cookie` text,
  `error` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1  ;

-- --------------------------------------------------------

--
-- Structure de la table `referrals`
--

CREATE TABLE IF NOT EXISTS `referrals` (
  `ref_hash` varchar(32) NOT NULL,
  `referral` text NOT NULL,
  `count` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ref_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `reports`
--

CREATE TABLE IF NOT EXISTS `reports` (
  `ip` varchar(45) NOT NULL DEFAULT '',
  `user` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `text` varchar(1024) NOT NULL DEFAULT '',
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `severity` tinyint(2) NOT NULL DEFAULT '0',
  `request` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `secondarygroups`
--

CREATE TABLE IF NOT EXISTS `secondarygroups` (
  `userid` int(11) NOT NULL,
  `groupid` int(11) NOT NULL,
  PRIMARY KEY (`userid`,`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(256) NOT NULL DEFAULT '',
  `user` int(11) NOT NULL DEFAULT '0',
  `expiration` int(11) NOT NULL DEFAULT '0',
  `autoexpire` tinyint(1) NOT NULL DEFAULT '0',
  `iplock` tinyint(1) NOT NULL DEFAULT '0',
  `iplockaddr` varchar(128) NOT NULL DEFAULT '',
  `lastip` varchar(128) NOT NULL DEFAULT '',
  `lasturl` varchar(128) NOT NULL DEFAULT '',
  `lasttime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `expiration` (`expiration`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
  `plugin` varchar(128) NOT NULL DEFAULT '',
  `name` varchar(128) NOT NULL DEFAULT '',
  `value` text,
  UNIQUE KEY `mainkey` (`plugin`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `settings`
--

INSERT INTO `settings` (`plugin`, `name`, `value`) VALUES
('main', 'boardname', 'Blargboard'),
('main', 'metaDescription', 'Blargboard, the best board software ever.'),
('main', 'metaTags', 'blargboard blarg board'),
('main', 'dateformat', 'm-d-y, h:i a'),
('main', 'customTitleThreshold', '100'),
('main', 'oldThreadThreshold', '2'),
('main', 'viewcountInterval', '50000'),
('main', 'ajax', '1'),
('main', 'guestLayouts', '1'),
('main', 'registrationWord', ''),
('main', 'breadcrumbsMainName', 'Blargboard'),
('main', 'mailResetSender', ''),
('main', 'defaultTheme', 'blargboard'),
('main', 'defaultLayout', 'abxd'),
('main', 'defaultLanguage', 'en_US'),
('main', 'showPoRA', '0'),
('main', 'tagsDirection', 'Right'),
('main', 'PoRATitle', 'zdgadgaew'),
('main', 'PoRAText', 'Welcome to Blargboard. Edit this.'),
('main', 'profilePreviewText', 'blah blah sample post'),
('main', 'menuMainName', 'fdgfdg'),
('main', 'showGender', '1'),
('main', 'nofollow', '0'),
('main', 'floodProtectionInterval', '10'),
('main', 'trashForum', '5'),
('main', 'secretTrashForum', '10'),
('main', 'alwaysMinipic', '0'),
('main', 'showExtraSidebar', '1'),
('main', 'anncForum', '1'),
('main', 'newsForum', '1'),
('main', 'defaultGroup', '0'),
('main', 'rootGroup', '4'),
('main', 'bannedGroup', '-1');

-- --------------------------------------------------------

--
-- Structure de la table `smilies`
--

CREATE TABLE IF NOT EXISTS `smilies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL DEFAULT '',
  `image` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1  ;

--
-- Contenu de la table `smilies`
--

INSERT INTO `smilies` (`id`, `code`, `image`) VALUES
(1, ':)', 'smile.png'),
(2, ';)', 'wink.png'),
(3, ':D', 'biggrin.png'),
(4, 'o_o', 'blank.png'),
(5, ':awsum:', 'awsum.png'),
(6, '-_-', 'annoyed.png'),
(7, 'o_O', 'bigeyes.png'),
(8, ':LOL:', 'lol.png'),
(9, ':O', 'jawdrop.png'),
(10, ':(', 'frown.png'),
(11, ';_;', 'cry.png'),
(12, '>:', 'mad.png'),
(13, 'O_O', 'eek.png'),
(14, '8-)', 'glasses.png'),
(15, '^_^', 'cute.png'),
(16, '^^;;;', 'cute2.png'),
(17, '>_<', 'yuck.png'),
(18, '<_<', 'shiftleft.png'),
(19, '>_>', 'shiftright.png'),
(20, '@_@', 'dizzy.png'),
(21, '^~^', 'angel.png'),
(22, '>:)', 'evil.png'),
(23, 'x_x', 'sick.png'),
(24, ':P', 'tongue.png'),
(25, ':S', 'wobbly.png'),
(26, ':[', 'vamp.png'),
(27, '~:o', 'baby.png'),
(28, ':YES:', 'yes.png'),
(29, ':NO:', 'no.png'),
(30, '<3', 'heart.png'),
(31, ':3', 'colonthree.png'),
(32, ':up:', 'approve.png'),
(33, ':down:', 'deny.png'),
(34, ':durr:', 'durrr.png'),
(35, '^^;', 'embarras.png'),
(36, ':barf:', 'barf.png'),
(37, '._.', 'ashamed.png'),
(38, '''.''', 'umm.png'),
(39, '''_''', 'downcast.png'),
(40, ':big:', 'teeth.png'),
(41, ':lawl:', 'lawl.png'),
(42, ':ninja:', 'ninja.png'),
(43, ':pirate:', 'pirate.png'),
(44, 'D:', 'outrage.png'),
(45, ':sob:', 'sob.png'),
(46, ':XD:', 'xd.png'),
(47, ':nyan:', 'nyan.gif'),
(48, ':c', 'frown_improved.png'),
(49, ':yum:', 'yum.png');

-- --------------------------------------------------------

--
-- Structure de la table `spieslog`
--

CREATE TABLE IF NOT EXISTS `spieslog` (
  `userid` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `pmid` int(11) NOT NULL,
  KEY `userid` (`userid`),
  KEY `date` (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `threads`
--

CREATE TABLE IF NOT EXISTS `threads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `forum` int(11) NOT NULL DEFAULT '0',
  `user` int(11) NOT NULL DEFAULT '0',
  `date` int(11) NOT NULL,
  `firstpostid` int(11) NOT NULL,
  `views` int(11) NOT NULL DEFAULT '0',
  `title` varchar(100) NOT NULL DEFAULT '',
  `icon` varchar(200) NOT NULL DEFAULT '',
  `replies` int(11) NOT NULL DEFAULT '0',
  `lastpostdate` int(11) NOT NULL DEFAULT '0',
  `lastposter` int(11) NOT NULL DEFAULT '0',
  `lastpostid` int(11) NOT NULL DEFAULT '0',
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  `sticky` tinyint(1) NOT NULL DEFAULT '0',
  `poll` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `forum` (`forum`),
  KEY `user` (`user`),
  KEY `sticky` (`sticky`),
  KEY `lastpostdate` (`lastpostdate`),
  FULLTEXT KEY `title` (`title`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1  ;

-- --------------------------------------------------------

--
-- Structure de la table `threadsread`
--

CREATE TABLE IF NOT EXISTS `threadsread` (
  `id` int(11) NOT NULL DEFAULT '0',
  `thread` int(11) NOT NULL DEFAULT '0',
  `date` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`thread`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `uploader`
--

CREATE TABLE IF NOT EXISTS `uploader` (
  `id` varchar(16) NOT NULL DEFAULT '',
  `filename` varchar(512) DEFAULT NULL,
  `description` varchar(1024) NOT NULL DEFAULT '',
  `big_description` text NOT NULL,
  `user` int(11) NOT NULL DEFAULT '0',
  `date` int(11) NOT NULL DEFAULT '0',
  `private` tinyint(1) NOT NULL DEFAULT '0',
  `category` int(11) NOT NULL DEFAULT '0',
  `downloads` int(11) NOT NULL DEFAULT '0',
  `deldate` int(11) NOT NULL DEFAULT '0',
  `physicalname` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `uploader_categories`
--

CREATE TABLE IF NOT EXISTS `uploader_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL DEFAULT '',
  `description` text,
  `ord` int(11) NOT NULL DEFAULT '0',
  `showindownloads` tinyint(4) NOT NULL,
  `minpower` int(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1  ;

-- --------------------------------------------------------

--
-- Structure de la table `usercomments`
--

CREATE TABLE IF NOT EXISTS `usercomments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `cid` int(11) NOT NULL DEFAULT '0',
  `text` text,
  `date` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `date` (`date`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1  ;

-- --------------------------------------------------------

--
-- Structure de la table `usergroups`
--

CREATE TABLE IF NOT EXISTS `usergroups` (
  `id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(32) NOT NULL,
  `title` varchar(256) NOT NULL DEFAULT '',
  `rank` int(11) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `display` tinyint(4) NOT NULL,
  `color_male` varchar(8) NOT NULL,
  `color_female` varchar(8) NOT NULL,
  `color_unspec` varchar(8) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `usergroups`
--

INSERT INTO `usergroups` (`id`, `name`, `title`, `rank`, `type`, `display`, `color_male`, `color_female`, `color_unspec`) VALUES
(-1, 'Banned users', 'Banned', -1000, 0, 0, '#888888', '#888888', '#888888'),
(0, 'Normal users', 'Normal user', 0, 0, 0, '#97ACEF', '#F185C9', '#7C60B0'),
(1, 'Local moderators', 'Local moderator', 100, 0, 1, '#D8E8FE', '#FFB3F3', '#EEB9BA'),
(2, 'Global moderators', 'Global moderator', 200, 0, 1, '#AFFABE', '#C762F2', '#47B53C'),
(3, 'Administrators', 'Administrator', 300, 0, 1, '#FFEA95', '#C53A9E', '#F0C413'),
(4, 'Owners', 'Owner', 1000, 0, 1, '#5555FF', '#FF5588', '#FF55FF');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL DEFAULT '',
  `displayname` varchar(32) NOT NULL DEFAULT '',
  `password` varchar(256) NOT NULL DEFAULT '',
  `pss` varchar(16) NOT NULL DEFAULT '',
  `primarygroup` int(8) NOT NULL DEFAULT '0',
  `flags` smallint(6) NOT NULL DEFAULT '0',
  `posts` int(11) NOT NULL DEFAULT '0',
  `regdate` int(11) NOT NULL DEFAULT '0',
  `minipic` varchar(128) NOT NULL DEFAULT '',
  `picture` varchar(128) NOT NULL DEFAULT '',
  `title` varchar(256) NOT NULL DEFAULT '',
  `postheader` text,
  `signature` text,
  `bio` text,
  `sex` tinyint(2) NOT NULL DEFAULT '2',
  `rankset` varchar(128) NOT NULL DEFAULT '',
  `realname` varchar(60) NOT NULL DEFAULT '',
  `lastknownbrowser` text,
  `location` varchar(128) NOT NULL DEFAULT '',
  `birthday` int(11) NOT NULL DEFAULT '0',
  `email` varchar(60) NOT NULL DEFAULT '',
  `homepageurl` varchar(80) NOT NULL DEFAULT '',
  `homepagename` varchar(100) NOT NULL DEFAULT '',
  `lastposttime` int(11) NOT NULL DEFAULT '0',
  `lastactivity` int(11) NOT NULL DEFAULT '0',
  `lastip` varchar(50) NOT NULL DEFAULT '',
  `lasturl` varchar(128) NOT NULL DEFAULT '',
  `lastforum` int(11) NOT NULL DEFAULT '0',
  `postsperpage` int(8) NOT NULL DEFAULT '20',
  `threadsperpage` int(8) NOT NULL DEFAULT '50',
  `timezone` float NOT NULL DEFAULT '0',
  `theme` varchar(64) NOT NULL DEFAULT '',
  `signsep` tinyint(1) NOT NULL DEFAULT '0',
  `dateformat` varchar(20) NOT NULL DEFAULT 'm-d-y',
  `timeformat` varchar(20) NOT NULL DEFAULT 'h:i a',
  `fontsize` int(8) NOT NULL DEFAULT '80',
  `karma` int(11) NOT NULL DEFAULT '100',
  `blocklayouts` tinyint(1) NOT NULL DEFAULT '0',
  `globalblock` tinyint(1) NOT NULL DEFAULT '0',
  `usebanners` tinyint(1) NOT NULL DEFAULT '1',
  `showemail` tinyint(1) NOT NULL DEFAULT '0',
  `newcomments` tinyint(1) NOT NULL DEFAULT '0',
  `tempbantime` int(11) NOT NULL DEFAULT '0',
  `tempbanpl` int(8) NOT NULL DEFAULT '0',
  `forbiddens` varchar(1024) NOT NULL DEFAULT '',
  `pluginsettings` text,
  `lostkey` varchar(128) NOT NULL DEFAULT '',
  `lostkeytimer` int(11) NOT NULL DEFAULT '0',
  `loggedin` tinyint(1) NOT NULL DEFAULT '0',
  `color` varchar(128) NOT NULL,
  `postplusones` int(11) NOT NULL,
  `postplusonesgiven` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `posts` (`posts`),
  KEY `name` (`name`),
  KEY `lastforum` (`lastforum`),
  KEY `lastposttime` (`lastposttime`),
  KEY `lastactivity` (`lastactivity`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1  ;

-- --------------------------------------------------------

--
-- Structure de la table `uservotes`
--

CREATE TABLE IF NOT EXISTS `uservotes` (
  `uid` int(11) NOT NULL DEFAULT '0',
  `voter` int(11) NOT NULL DEFAULT '0',
  `up` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`,`voter`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `wiki_pages`
--

CREATE TABLE IF NOT EXISTS `wiki_pages` (
  `id` varchar(128) NOT NULL DEFAULT '',
  `revision` int(11) NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `wiki_pages_text`
--

CREATE TABLE IF NOT EXISTS `wiki_pages_text` (
  `id` varchar(128) NOT NULL DEFAULT '',
  `revision` int(11) NOT NULL DEFAULT '0',
  `date` int(11) NOT NULL DEFAULT '0',
  `user` int(11) NOT NULL DEFAULT '0',
  `text` text,
  KEY `wpt` (`id`,`revision`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
