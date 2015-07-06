<?php
/* 
 * Copyright (C) 2014 Florian HENRY <florian.henry@open-concept.pro>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file 
 * \brief File of class with all html predefined components
 */
class FormRelance extends Form
{

	var $db;

	var $error;

	var $num;

	/**
	 * Build Select List of element associable to a businesscase
	 *
	 * @param string $tablename To parse
	 * @param Lead $lead The lead
	 * @param string $htmlname Name of the component
	 *
	 * @return string HTML select list of element
	 */
	
	/**
	 * Return combo list of differents type
	 *
	 * @param string $selected Value
	 * @param string $htmlname Name of the component
	 * @param int $showempty Row
	 *
	 * @return string HTML select
	 */
	function select_relance_type($selected = '', $htmlname = 'fk_type_relance', $showempty = 1)
	{
		require_once 'relance.class.php';
		$relance = new Relance($this->db);
		
		return $this->selectarray($htmlname, $relance->fk_type_relance, $selected, $showempty);
	}
	
	
}