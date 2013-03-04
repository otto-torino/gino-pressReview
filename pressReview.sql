--
-- Table structure for table `press_review_grp`
--

CREATE TABLE IF NOT EXISTS `press_review_grp` (
  `id` int(2) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `no_admin` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `press_review_grp`
--

INSERT INTO `press_review_grp` (`id`, `name`, `description`, `no_admin`) VALUES
(1, 'responsabili', 'Gestiscono l''assegnazione degli utenti ai singoli gruppi.', 'no'),
(2, 'assistenti', 'Gestiscono gli articoli e testate della rassegna stampa.', 'no');

-- --------------------------------------------------------

--
-- Table structure for table `press_review_item`
--

CREATE TABLE IF NOT EXISTS `press_review_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance` int(11) NOT NULL,
  `newspaper` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `date` date NOT NULL,
  `file` varchar(200) DEFAULT NULL,
  `link` varchar(200) DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `press_review_newspaper`
--

CREATE TABLE IF NOT EXISTS `press_review_newspaper` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `logo` varchar(200) DEFAULT NULL,
  `link` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `press_review_opt`
--

CREATE TABLE IF NOT EXISTS `press_review_opt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance` int(11) NOT NULL,
  `title_last` varchar(200) NOT NULL,
  `title_list` varchar(200) NOT NULL,
  `newspaper_logo_width` int(4) NOT NULL,
  `last_tpl_code` text NOT NULL,
  `last_tpl_number` int(2) NOT NULL,
  `list_tpl_code` text NOT NULL,
  `list_tpl_ifp` int(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `press_review_usr`
--

CREATE TABLE IF NOT EXISTS `press_review_usr` (
  `instance` int(11) NOT NULL,
  `group_id` int(2) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

