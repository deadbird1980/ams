DROP TABLE IF EXISTS `visa_application`;

CREATE TABLE `visa_application` (
  `id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `type` char(10) NOT NULL DEFAULT '',
  `start_date` date NOT NULL,
  `passport_no` char(10) NOT NULL DEFAULT '',
  `passport_start_date` date NOT NULL,
  `passport_end_date` date NOT NULL,
  `birthday` date NOT NULL,
  `organization` char(50) NOT NULL,
  `passport_name` char(50) NOT NULL DEFAULT '',
  `visa_start_date` date NOT NULL,
  `visa_end_date` date NOT NULL,
  `address` char(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
