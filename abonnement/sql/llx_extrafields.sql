
INSERT INTO `llx_extrafields` ( `name`, `entity`, `elementtype`, `tms`, `label`, `type`, `size`, `fieldunique`, `fieldrequired`, `pos`, `alwayseditable`, `param`) VALUES
( 'comm_structure', 1, 'commande', now(), 'Communication structurée', 'varchar', '255', 0, 0, 2, 0, 'a:1:{s:7:"options";a:1:{s:0:"";N;}}'),
( 'prop_renouv', 1, 'contrat', '2015-10-14 13:30:33', 'Proposition de renouvellement déjà envoyée?', 'boolean', '', 0, 0, 0, 1, 'a:1:{s:7:"options";a:1:{s:0:"";N;}}');
;

ALTER TABLE `llx_commande_extrafields` ADD `comm_structure` VARCHAR(255) NULL ;
ALTER TABLE `llx_contrat_extrafields` ADD  `prop_renouv` INT(1)  DEFAULT NULL;
