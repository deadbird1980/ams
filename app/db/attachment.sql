DROP TABLE IF EXISTS `attachment`;

CREATE TABLE `attachment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `file_name` char(50) DEFAULT NULL,
  `file_size` char(50) DEFAULT NULL,
  `file_type` char(50) DEFAULT NULL,
  `content` longblob,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
