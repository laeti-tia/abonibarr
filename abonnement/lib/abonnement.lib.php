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
 * \file		lib/abonnement.lib.php
 * \ingroup	
 * \brief		This file is an example module library
 * Put some comments here
 */
function abonnementAdminPrepareHead()
{
	global $langs, $conf;
	
	$langs->load("abonnement@abonnement");
	$langs->load("admin");
	
	$h = 0;
	$head = array();
	
	$head[$h][0] = dol_buildpath("/abonnement/admin/admin_abonnement.php", 1);
	$head[$h][1] = $langs->trans("SettingsRelance");
	$head[$h][2] = 'settings';
	$h ++;
	
	
	complete_head_from_modules($conf, $langs, null, $head, $h, 'relance_admin');
	
	return $head;
}

