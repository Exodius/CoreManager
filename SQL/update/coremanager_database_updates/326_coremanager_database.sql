DROP TABLE IF EXISTS `point_system_achievement_contests`;
CREATE TABLE `point_system_achievement_contests` (
  `entry` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `realm` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `achievement` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `date_issued` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `after_only` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `expiration` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `prize_bag` int(10) NOT NULL DEFAULT '0',
  `winner_guid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `announce` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `announce_msg` longtext NOT NULL,
  `enabled` int(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`entry`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
