DROP TABLE IF EXISTS `course_application_attachment`;

CREATE TABLE `course_application_attachment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `course_application_id` int(11) NOT NULL,
  `attachment_id` int(11) NOT NULL,
  `type` text NOT NULL,

  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
