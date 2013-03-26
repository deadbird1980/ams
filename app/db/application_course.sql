DROP TABLE IF EXISTS `application_course`;

CREATE TABLE `application_course` (
  `id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `school` text DEFAULT NULL,
  `subject` text DEFAULT NULL,
  `course` text DEFAULT NULL,
  `status` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
