<?php
/* 
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
 * \ingroup	autre
 * \brief		This file is an example module setup page
 * Put some comments here
 */
// Dolibarr environment
$res = @include '../../main.inc.php'; // From htdocs directory
if (! $res) {
	$res = @include '../../../main.inc.php'; // From "custom" directory
}

// Libraries
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once '../lib/abonnement.lib.php';
//require_once '../class/lead.class.php';

// Translations
$langs->load("abonnement@abonnement");

// Access control
if (! $user->admin) {
	accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');
$value = GETPOST('value', 'alpha');
$label = GETPOST('label', 'alpha');
$scandir = GETPOST('scandir', 'alpha');

/*
 * Actions
 */

 if ($action == 'setvar') {
 	$error = 0 ;
	$nb_renouvel = GETPOST('NBRE_JOURS_AVANT_RENOUVELLEMENT', 'int');
	if (! empty($nb_renouvel)) {
		$res = dolibarr_set_const($db, 'NBRE_JOURS_AVANT_RENOUVELLEMENT', $nb_renouvel, 'chaine', 0, '', $conf->entity);
	}
	if (! $res > 0)
		$error ++;
	
	
	
	if (! $error) {
		setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
	} else {
		setEventMessages($langs->trans("Error"), null, 'errors');
	}
}

/*
 * View
 */
$page_name = "AbonnementSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">' . $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = abonnementAdminPrepareHead();
dol_fiche_head($head, 'settings', $langs->trans("Module600001Name"), 0, "abonnement@abonnement");


clearstatcache();

$form = new Form($db);

// Admin var of module
print_fiche_titre($langs->trans("AbonnementAdmVar"));

print '<form method="post" action="' . $_SERVER['PHP_SELF'] . '" >';
print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
print '<input type="hidden" name="action" value="setvar">';

print '<table class="noborder" width="100%">';

print '<tr class="liste_titre">';
print '<td>' . $langs->trans("Name") . '</td>';
print '<td width="400px">' . $langs->trans("Valeur") . '</td>';
print "</tr>\n";

// Nb Days
print '<tr class="pair"><td>' . $langs->trans("NBRE_JOURS_AVANT_RENOUVELLEMENT") . '</td>';
print '<td align="left">';
print '<input type="text" name="NBRE_JOURS_AVANT_RENOUVELLEMENT" value="' . $conf->global->NBRE_JOURS_AVANT_RENOUVELLEMENT . '" size="4" ></td>';
print '</tr>';

// User Group
//print '<tr class="pair"><td>' . $langs->trans("LeadUserGroupAffect") . '</td>';
//print '<td align="left">';
//print $form->select_dolgroups($conf->global->LEAD_GRP_USER_AFFECT, 'LEAD_GRP_USER_AFFECT', 1, array(), 0, '', '', $object->entity);
/*$form->select_
*/
//print '</tr>';
print '</table>';

print '<tr class="impair"><td colspan="2" align="right"><input type="submit" class="button" value="' . $langs->trans("Save") . '"></td>';
print '</tr>';

print '</table><br>';
print '</form>';

llxFooter();

$db->close();