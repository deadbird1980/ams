DROP TABLE IF EXISTS `application_attachment`;

CREATE TABLE `application_attachment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `application_file_id` int(11) NOT NULL,
  `attachment_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
