<?php
/*
 * Copyright (C) 2015 Ares voukissi <ares.voukissi@gmail.com>
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
 * \file relance/admin/type_relance
 * \brief Page to setup
 */

// Dolibarr environment
$res = @include '../../main.inc.php'; // From htdocs directory
if (! $res) {
	$res = @include '../../../main.inc.php'; // From "custom" directory
}
require_once DOL_DOCUMENT_ROOT . '/relance/class/relance.class.php';
require_once DOL_DOCUMENT_ROOT . '/relance/class/crelancetype.class.php';
require_once '../lib/relance.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formadmin.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';

//require_once '../lib/lead.lib.php';

if (! $user->admin)
	accessforbidden();

$langs->load("admin");
$langs->load("other");
$langs->load("relance@relance");

$relance = new Relance($db);
$relancetype = new Crelancetype($db);
$form = new Form($db);


$action = GETPOST('action', 'alpha');
$rowid = GETPOST('rowid', 'alpha');

$lementtype = 'relance';

if (! $user->admin)
	accessforbidden();

$relancetypeArr = $relancetype->getList();
$arrChamps=array('Code'=>'code','Label'=>'label','NbOfDays'=>'nbre_jours');
// Actions ajout ou modification d'une entree dans un dictionnaire de donnee
if (GETPOST('actionadd') || GETPOST('actionmodify'))
{
	// Check that all fields are filled
	$ok=1;
	foreach ($arrChamps as $key => $value) {
		if(! isset($_POST[$value]) || $_POST[$value]=='' )  {
			$ok = 0;
			setEventMessage($langs->transnoentities("ErrorFieldRequired", $langs->transnoentities($key)),'errors');
		}
	}
	if (isset($_POST["code"]))
	{
		if ($_POST["code"]=='0')
		{
			$ok=0;
			setEventMessage($langs->transnoentities('ErrorCodeCantContainZero'),'errors');
		}
	}
	$relancetype->code = $_POST["code"];
	$relancetype->label = $_POST["label"];
	$relancetype->nbre_jours = $_POST["nbre_jours"];
	global $user;
	// ajout 
	if ($ok && GETPOST('actionadd'))
	{
		
		$result = $relancetype->create($user);
		//var_dump($result);
		//var_dump($relancetype->errors);exit;
		if ($result > 0 )	// Add is ok
		{
			setEventMessage($langs->transnoentities("RecordSaved"));
			//$_POST=array('id'=>$id);	// Clean $_POST array, we keep only
		}
	}
	// Si verif ok et action modify, on modifie la ligne
	if ($ok && GETPOST('actionmodify'))
	{
		$relancetype->id= $rowid;
		$result = $relancetype->update($user);
		if ($result > 0 )	// Add is ok
		{
			setEventMessage($langs->transnoentities("RecordSaved"));
		}
	}
}

if (GETPOST('actioncancel'))
{
	//$_GET["id"]=GETPOST('id', 'int');       // Force affichage dictionnaire en cours d'edition
}
//var_dump($action);
//
// Confirmation de la suppression de la ligne
if ($action == 'delete')
{
	//print $form->formconfirm($_SERVER["PHP_SELF"].'?rowid='.$rowid, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_delete','',0,1);
	//exit;
}

if ($action == 'confirm_delete'
		// && $confirm == 'yes'
		)       // delete
{
	$relancetype->id =$rowid;
	$relancetype->delete($user);
	//exit;
}
/*
 * View
*/

$textobject = $langs->transnoentitiesnoconv("Module600002Name");
$page_name = "RelanceSetup";
llxHeader('', $langs->trans($page_name));

$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">' . $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback, 'setup');
print "<br>\n";

// Configuration header
// Configuration header
$head = relanceAdminPrepareHead();
dol_fiche_head($head, 'TYPERELANCE', $langs->trans("Module600002Name"), 0, "relance@relance");

global  $langs;
print '<br>';
$url = $_SERVER['PHP_SELF'];
print '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td> '.$langs->trans("Code").'</td>';
print '<td> '.$langs->trans("Label").'</td>';
print '<td> '.$langs->trans("NbOfDays").'</td>';
print '<td> &nbsp;</td>';

print '</tr>';

print '<tr >';
print '<td> ';
print '<input type="text" size=10 class="flat"  name="code">';
print '</td>';
print '<td> ';
print '<input type="text" size=32 class="flat"  name="label">';
print '</td>';
print '<td> ';
print '<input type="text" size=10 class="flat"  name="nbre_jours">';
print '</td>';
print '<td  align="right"><input type="submit" class="button" name="actionadd" value="'.$langs->trans("Add").'"></td>';

print '</tr>';
print '<tr> <td colspan="4"></td>';print '</tr>';

print '<tr class="liste_titre">';
print '<td> '.$langs->trans("Code").'</td>';
print '<td> '.$langs->trans("Label").'</td>';
print '<td> '.$langs->trans("NbOfDays").'</td>';
print '<td> &nbsp;</td>';

print '</tr>';
$arrTypeRelance = $relancetype->getList();
foreach ($arrTypeRelance as $objRelance) {
	$url = $_SERVER["PHP_SELF"].'?'.'rowid='.$objRelance->id;
	print '<tr >';
	print '<td> '.$objRelance->code.'</td>';
	print '<td> '.$objRelance->label.'</td>';
	print '<td> '.$objRelance->nbre_jours.'</td>';
	print '<td align="center"><a href="'.$url.'&action=confirm_delete">'.img_delete().'</a></td>';
	
	print '</tr>';
}


print '</table> ';


/* ************************************************************************* */
/*                                                                            */
/* Edition d'un champ optionnel                                               */
/*                                                                            */
/* ************************************************************************** */
/*if ($action == 'edit' && ! empty($attrname)) {
 print "<br>";
print_fiche_titre($langs->trans("FieldEdition", $attrname));

require DOL_DOCUMENT_ROOT . '/core/tpl/admin_extrafields_edit.tpl.php';
}
*/
llxFooter();

$db->close();
