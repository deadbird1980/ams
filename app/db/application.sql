DROP TABLE IF EXISTS `application`;

CREATE TABLE `application` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `assignee_id` int(11) DEFAULT NULL,
  `type` text NOT NULL COMMENT 'visa,university',
  `status` varchar(20) NOT NULL DEFAULT '',
  `paid` tinyint(1) NOT NULL DEFAULT '0',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `submitted` datetime DEFAULT NULL,
  `confirmed` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
