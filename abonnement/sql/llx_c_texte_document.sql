

CREATE TABLE IF NOT EXISTS `llx_c_texte_document` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL,
  `label` text  NOT NULL,
   `content` text  NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`rowid`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

ALTER TABLE `llx_c_texte_document` ADD `content` TEXT NOT NULL ;

