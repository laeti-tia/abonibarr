
CREATE TABLE IF NOT EXISTS `llx_relance` (
  `fk_type_relance` int(11) NOT NULL,
  `envoi_email` tinyint(1) NOT NULL,
   `sujet_email` varchar(255),
  `textemail` text,
  PRIMARY KEY (`fk_type_relance`)
  
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

