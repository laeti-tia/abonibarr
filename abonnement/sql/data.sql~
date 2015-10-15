
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see <http://www.gnu.org/licenses/>.

INSERT INTO llx_c_type_contact(rowid, element, source, code, libelle, active, module) VALUES (6000021,'contrat','external','ABONPAPIER','Abonné papier','1',null);
INSERT INTO llx_c_type_contact(rowid, element, source, code, libelle, active, module) VALUES (6000022,'contrat','external','ABONWEB','Abonné web','1',null);


insert into llx_contrat_extrafields (`fk_object`) select rowid from llx_contrat where rowid not in ( SELECT c.rowid FROM llx_contrat as c, `llx_contrat_extrafields` as ce WHERE c.rowid=ce.fk_object);




