
INSERT INTO `llx_extrafields` (`rowid`, `name`, `entity`, `elementtype`, `tms`, `label`, `type`, `size`, `fieldunique`, `fieldrequired`, `pos`, `alwayseditable`, `param`) VALUES
(1, 'comm_structure', 1, 'commande', now(), 'Communication structurée', 'varchar', '255', 0, 0, 2, 0, 'a:1:{s:7:"options";a:1:{s:0:"";N;}}');

ALTER TABLE `llx_commande_extrafields` ADD `comm_structure` VARCHAR(255) NULL ;

