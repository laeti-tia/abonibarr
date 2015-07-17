-- ===================================================================
-- Copyright (C) 2002-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
-- Copyright (C) 2002-2003 Jean-Louis Bergamo   <jlb@j1b.org>
-- Copyright (C) 2006-2009 Laurent Destailleur  <eldy@users.sourceforge.net>
-- Copyright (C) 2009      Regis Houssin        <regis.houssin@capnetworks.com>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program. If not, see <http://www.gnu.org/licenses/>.
--
-- ===================================================================
--
-- statut
-- -1 : brouillon
--  0 : resilie
--  1 : valide

create table llx_code_acces
(
  rowid            integer AUTO_INCREMENT PRIMARY KEY,
  entity           integer DEFAULT 1 NOT NULL,	-- multi company id
  ref_ext          varchar(128),                -- reference into an external system (not used by dolibarr)

  lastname         varchar(50),
  firstname        varchar(50),
  login            varchar(50),          -- login
  pass             varchar(50),          -- password
  
  fk_soc           integer NULL,		-- Link to third party linked to member
 
  note             text,
  datevalid        datetime,  -- date de validation
  datec            datetime,  -- date de creation
  tms              timestamp, -- date de modification
  fk_user_author   integer,   -- can be null because member can be create by a guest
  fk_user_mod      integer,
  fk_user_valid    integer,
  canvas		   varchar(32),			                        -- type of canvas if used (null by default)
  import_key       varchar(14)                  -- Import key
)ENGINE=innodb;
