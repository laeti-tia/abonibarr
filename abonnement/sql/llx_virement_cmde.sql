Create table llx_virement_cmde  ( 
rowid integer AUTO_INCREMENT PRIMARY KEY,
num_mvt integer,
date_mvt date ,
montant float ,
devise char(5),
fk_commande integer ,
communication varchar(30),
fk_user_author   integer,   -- can be null because member can be create by a guest
fk_user_mod      integer,
fk_user_valid    integer
)ENGINE=innodb;
