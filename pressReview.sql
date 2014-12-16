--
-- Permissions
--
INSERT INTO `auth_permission` (`class`, `code`, `label`, `description`, `admin`) VALUES
('pressReview', 'can_admin', 'Amministrazione modulo', 'Amministrazione completa del modulo rassegna stampa', 1);

--
-- Table structure for table `press_review_item`
--

CREATE TABLE IF NOT EXISTS `press_review_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance` int(11) NOT NULL,
  `newspaper` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `file` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `press_review_newspaper`
--

CREATE TABLE IF NOT EXISTS `press_review_newspaper` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `press_review_opt`
--

CREATE TABLE IF NOT EXISTS `press_review_opt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance` int(11) NOT NULL,
  `newspaper_logo_width` int(5) NOT NULL,
  `last_tpl_number` int(2) NOT NULL,
  `list_tpl_ifp` int(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
