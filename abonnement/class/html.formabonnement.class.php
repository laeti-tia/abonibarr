<?php
/*
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
 * \ingroup
 * \brief File of class with all html predefined components
 */
class FormAbonnement extends Form
{

	var $db;

	var $error;

	var $num;

	/**
	 *  Return a select list with types of contacts
	 *
	 *  @param  string		$selected       Default selected value
	 *  @param  string		$htmlname		HTML select name
	 *  @param  string		$source			Source ('internal' or 'external')
	 *  @param  string		$sortorder		Sort criteria
	 *  @param  int			$showempty      1=Add en empty line
	 *  @return	void
	 */
	function selectTypeContact( $htmlname = 'type_contact',$selected='', $sortorder='code', $showempty=1)
	{
		global  $db;
		$out = '';
		$lesTypes = $this->type_contact_abonnement();
		$out .= '<select class="flat" name="'.$htmlname.'" id="'.$htmlname.'">';
		if ($showempty) $out .= '<option value="0"></option>';
		foreach($lesTypes as $key=>$value)
		{
			$out .= '<option value="'.$key.'"';
			if ($key == $selected) $out .= ' selected';
			$out .= '>'.$value.'</option>';
		}
		$out .= "</select>\n";
		return $out;

	}
	function type_contact_abonnement( $activeonly=0, $code='')
	{
		global $langs,$db;
	    $this->db = $db;
		if (empty($order)) $order='code';
	
		$tab = array();
		$sql = "SELECT DISTINCT tc.rowid, tc.code, tc.libelle";
		$sql.= " FROM ".MAIN_DB_PREFIX."c_type_contact as tc";
		$sql.= " WHERE tc.element='contrat'";
		$sql.= " AND tc.source='external'";
		$sql.= " AND tc.code like'ABON%'";
		if ($activeonly == 1) $sql.= " AND tc.active=1"; // only the active type
		if (! empty($code)) $sql.= " AND tc.code='".$code."'";
		
		
		$sql.= " ORDER by tc.".$order;
	
		//print "sql=".$sql;
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$num=$this->db->num_rows($resql);
			$i=0;
			while ($i < $num)
			{
				$obj = $this->db->fetch_object($resql);
	
				$transkey="TypeContact_".$this->element."_".$source."_".$obj->code;
				$libelle_type=($langs->trans($transkey)!=$transkey ? $langs->trans($transkey) : $obj->libelle);
				$tab[$obj->rowid]=$libelle_type;
				$i++;
			}
			return $tab;
		}
		else
		{
			$this->error=$this->db->lasterror();
			//dol_print_error($this->db);
			return null;
		}
	}


}