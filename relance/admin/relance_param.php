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
 * \file relance/admin/relance_param
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
$lementtype = 'relance';

if (! $user->admin)
	accessforbidden();

$relancetypeArr = $relancetype->getList();


if($action == 'add') {
	$error=0;
	foreach ($relancetypeArr as $objRelanceType ) {
		
		$opt_textemail ='textemail'.$objRelanceType->id;
		$opt_sujet_email ='sujet_email'.$objRelanceType->id;
	   $textemail = GETPOST($opt_textemail, 'alpha');
	   $sujet_email = GETPOST($opt_sujet_email, 'alpha');
	   $obj= new Relance($db);
	   $resp=$obj->fetch($objRelanceType->id);
	  
	   $obj->textemail=$textemail;
	   
	   $obj->envoi_email = 1;
	   $obj->sujet_email = $sujet_email; 
	   
	   if (! $error)
	   {
	   	if($obj->fk_type_relance) {
	   		
	   	$result=$obj->update($user);
	   	}
	   	else {
	   		$obj->fk_type_relance = $objRelanceType->id;
	   		$result=$obj->create($user);
	   	}
	   	
	   	if ($result > 0)
	   	{
	   		// Creation OK
	   		setEventMessages('create entity success', null);
	   		//$urltogo=$backtopage?$backtopage:dol_buildpath('/relance/admin/relance_param.php',1);
	   		//header("Location: ".$urltogo);
	   		//exit;
	   	}
	   	{
	   		// Creation KO
	   		if (! empty($obj->errors)) setEventMessages(null, $obj->errors, 'errors');
	   		else  setEventMessages($obj->error, null, 'errors');
	   		$action='create';
	   	}
	   } 
	}
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
dol_fiche_head($head, 'param', $langs->trans("Module600002Name"), 0, "relance@relance");


//print $langs->trans("DefineHereComplementaryAttributes", $langs->transnoentitiesnoconv("Module103111Name")) . '<br>' . "\n";
print '<br>';

// type relance
print "<table summary=\"listofattributes\" class=\"noborder\" width=\"100%\">";
foreach ($relancetypeArr as $objRelanceType ) {
	print_fiche_titre($objRelanceType->label);
	print '<br>';
	print '<br>';
	$obj= new Relance($db);
	$resp=$obj->fetch($objRelanceType->id);

	print '<form name="new_mailing" action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n";
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="add">';
    
	print '<table class="border" width="100%">';
	print '<tr><td width="25%" class="fieldrequired">'.$langs->trans("EnvoiEmail").'</td><td><input class="flat" name="envoi_email'.$objRelanceType->id.'" size="68" value="'.$obj->envoi_email.'"></td></tr>';
	print '</table>';
		
	print '<table class="border" width="100%">';
	print '<tr><td width="25%" class="fieldrequired">'.$langs->trans("OBJECTMESSAGE").'</td><td><input class="flat" name="sujet_email'.$objRelanceType->id.'" size="68" value="'.$obj->sujet_email.'"></td></tr>';
	print '</table>';

	print '<table class="border" width="100%">';
	print '<tr><td width="25%" valign="top"><span class="fieldrequired">'.$langs->trans("MESSAGEEMAIL").'</span><br>';
	print '<br><i>'.$langs->trans("CommonSubstitutions").':<br>';
	foreach($object->substitutionarray as $key => $val)
	{
		print $key.' = '.$langs->trans($val).'<br>';
	}
	print '</i></td>';
	print '<td>';
	// Editeur wysiwyg
	require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
	$doleditor=new DolEditor('textemail'.$objRelanceType->id,$obj->textemail,'',320,'dolibarr_mailings','',true,true,$conf->global->FCKEDITOR_ENABLE_MAILING,20,70);
	$doleditor->Create();
	print '</td></tr>';
	print '</table>';
	print '<br>';
	
	
}
print '<br><center><input type="submit" class="button" value="'.$langs->trans("Validate").'"></center>';

print '</form>';
print "</table>";
dol_fiche_end();
/*$var = True;
 foreach ($extrafields->attribute_type as $key => $value) {
$var = ! $var;
print "<tr " . $bc[$var] . ">";
print "<td>" . $extrafields->attribute_label[$key] . "</td>\n";
print "<td>" . $key . "</td>\n";
print "<td>" . $type2label[$extrafields->attribute_type[$key]] . "</td>\n";
print '<td align="right">' . $extrafields->attribute_size[$key] . "</td>\n";
print '<td align="center">' . yn($extrafields->attribute_unique[$key]) . "</td>\n";
print '<td align="center">' . yn($extrafields->attribute_required[$key]) . "</td>\n";
print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?action=edit&attrname=' . $key . '">' . img_edit() . '</a>';
print "&nbsp; <a href=\"" . $_SERVER["PHP_SELF"] . "?action=delete&attrname=$key\">" . img_delete() . "</a></td>\n";
print "</tr>";
}
*/




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
