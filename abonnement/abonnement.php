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
 *	    \file       htdocs/abonnement/abonnement.php
 *      \ingroup    other
 *		\brief     Abonnement
 */

require ("../main.inc.php");
require_once (DOL_DOCUMENT_ROOT."/contrat/class/contrat.class.php");
require_once (DOL_DOCUMENT_ROOT."/product/class/product.class.php");
require_once (DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
require_once DOL_DOCUMENT_ROOT.'/abonnement/class/html.formabonnement.class.php';
require_once DOL_DOCUMENT_ROOT.'/abonnement/class/abonnement.class.php';


$action = GETPOST('action', 'alpha');


$formabonne = new FormAbonnement($db);
$abonne = new Abonnement($db);
//$formabonne->getArrAbonneWeb(112);exit;
// $login = 'ares.voukissi@gmail.com';
// $login = 'code1.code1';
// $r=$abonne->getContratActive($login);
// var_dump($r);exit;
//$abonne->contrat_expire($duration_value, $duration_value2);

//  $cmd = new Commande($db);
//  $cmd->fetch(88);
// // $re = $cmd->valid($user);
// $re = $abonne->createInvoiceAndContratFromCommande($cmd,101,'1','1');
// var_dump($abonne->errors,$re);
// exit;


// $facture = new Facture($db);
// $facture->fetch(108);
// $formabonne->envoiEmailFacture($facture, '');
// exit;
// $contrat = new Contrat($db);
// $contrat->fetch(89);
// $abonne->createLoginAbonne($contrat);

//$facture = new Facture($db);
//$facture->fetch(75);
//$re=$abonne->paiementFacture($facture, '121', '1223', '1');
//var_dump($abonne->errors,$re);
//exit;
//  $cmd = new Commande($db);
//  $cmd->fetch(10);
// // $re = $cmd->valid($user);
// $re = $abonne->createInvoiceAndContratFromCommande($cmd);
// var_dump($abonne->errors,$re);
// exit;
// $cmd = new Commande($db);
// $cmd->fetch(28);
// $cmd->update($user);
// var_dump($cmd->total_ht);
// var_dump($cmd->total_ttc);
// var_dump($cmd->total_tva);

// exit;

if($action == 'add') {
	$error = 0;
	$db->begin();
	//var_dump($_POST);exit;
	$idpd = GETPOST('idprod', 'int');
	$object = new Societe($db);
	$object->client = 1;
	$object->civility_id       = GETPOST('civility_id', 'int');
	// Add non official properties
	//$object->name_bis          = GETPOST('name','alpha')?GETPOST('name','alpha'):GETPOST('nom','alpha');
	$object->lastname = GETPOST('lastname','alpha');
	$object->firstname         = GETPOST('firstname','alpha');
	$object->name              = GETPOST('lastname','alpha');
	//var_dump($object->name );exit;
	$object->address               = GETPOST('address', 'alpha');
	$object->zip                   = GETPOST('zipcode', 'alpha');
	$object->town                  = GETPOST('town', 'alpha');
	$object->country_id            = GETPOST('country_id', 'int');
	//$object->state_id              = GETPOST('state_id', 'int');
	$object->phone                 = GETPOST('phone', 'alpha');
	$object->fax                   = GETPOST('fax','alpha');
	$object->email                 = GETPOST('email', 'custom', 0, FILTER_SANITIZE_EMAIL);
	
	$object->code_client           = GETPOST('code_client', 'alpha');
	$object->capital               = GETPOST('capital', 'alpha');
	
	
	if (! empty($object->email) && ! isValidEMail($object->email))
	{
		$langs->load("errors");
		$error++; $errors[] = $langs->trans("ErrorBadEMail",$object->email);
		//$action = ($action=='add'?'create':'edit');
	}
	
	$object->country_id=GETPOST('country_id')!=''?GETPOST('country_id'):$mysoc->country_id;
	if ($object->country_id)
	{
		$tmparray=getCountry($object->country_id,'all');
		$object->country_code=$tmparray['code'];
		$object->country=$tmparray['label'];
	}
	
	
	$result = $object->create($user);
	if ($result < 0)
	{
		$langs->load("errors");
		$error++; $errors[] = $object->error;
		//var_dump($object->errors);exit;
	}
	
	$commande = new Commande($db);
	$commande->socid = $object->id;
	$commande->date_commande = dol_now();
	$commande->statut = 0;
	$commande->fetch_thirdparty();
	
	$commande->add_product($idpd, 1);
	$result = $commande->create($user);
	if ($result < 0)
	{
		$langs->load("errors");
		$error++; $errors[] = $commande->error;
	}
	$commande->valid($user);
	$formabonne->genereDocument($commande);
	
	if(is_object($commande)) {
		$formabonne->envoiEmailCommande($commande,'');
	}
	
	if(!$error) {
		$db->commit();
		setEventMessage('Client ajouté avec succès','msg');
		$url = DOL_URL_ROOT.'/societe/soc.php?socid='.$object->id;
		header("Location: ".$url);
		exit;
		
		
	} else {
		$msgs = '';
		foreach ($errors as $msgerr) $msgs .=$msgerr;
		setEventMessage($mesgs);
		$action ='';
	}
	//echo '<pre>';
	//exit();
	//var_dump($_POST);
	$action ='';
}

/*
 * View
*/

llxHeader('', $langs->trans('Module600001Name'));

$form = new Form($db);
$formcompany= new FormCompany($db);
$now = dol_now();
// Add new proposal
if ($action == 'create' || $action == '') {
	print_fiche_titre($langs->trans("AbonnementCreate"), '', dol_buildpath('/lead/img/object_lead.png', 1), 1);
	print '<br><br>';

	print '<form name="addlead" action="' . $_SERVER["PHP_SELF"] . '" method="POST">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';


	print '<input type="hidden" name="action" value="add">';

	print '<table class="border" width="100%">';
	print '<tr>';
	print '<td class="fieldrequired"  width="20%">';
	print $langs->trans('ProductsAndServices');
	print '</td>';
	print '<td>';
	print $form->select_produits('', 'idprod' . $i, '', $conf->product->limit_size);
	print '</td>';
	print '</tr>';
	
	print '<tr>';
	print '<td class="fieldrequired"  width="20%">';
	print $langs->trans('Lastname');
	print '</td>';
	print '<td><input name="lastname" id="lastname" type="text" size="30" maxlength="80" value="'.dol_escape_htmltag(GETPOST("lastname")?GETPOST("lastname"):$object->lastname).'" autofocus="autofocus"></td>';
	print '</tr>';

	print '<tr><td><label for="firstname">'.$langs->trans("Firstname").'</label></td>';
	print '<td><input name="firstname" id="firstname" type="text" size="50" maxlength="80" value="'.dol_escape_htmltag(GETPOST("poste",'alpha')?GETPOST("poste",'alpha'):$object->poste).'"></td>';
	print '</tr>';

	// Civility
	print '<tr><td width="15%"><label for="civility_id">'.$langs->trans("UserTitle").'</label></td>';
	print '<td>';
	print $formcompany->select_civility(GETPOST("civility_id",'alpha')?GETPOST("civility_id",'alpha'):$object->civility_id);
	print '</td></tr>';
	
	// Address
	print '<tr><td valign="top">'.$langs->trans("Address").'</td><td>';
	print '<textarea name="address" wrap="soft" cols="40" rows="2">'.(GETPOST('address','alpha')?GETPOST('address','alpha'):$object->address).'</textarea>';
	print '</td></tr>';

	// Zip / Town
	print '<tr><td>'.$langs->trans("Zip").' / '.$langs->trans("Town").'</td><td>';
	print $formcompany->select_ziptown((GETPOST('zipcode','alpha')?GETPOST('zipcode','alpha'):$object->zip),'zipcode',array('town','selectcountry_id','state_id'),6);
	print ' ';
	print $formcompany->select_ziptown((GETPOST('town','alpha')?GETPOST('town','alpha'):$object->town),'town',array('zipcode','selectcountry_id','state_id'));
	print '</td></tr>';

	// Country
	$object->country_id=$object->country_id?$object->country_id:$mysoc->country_id;
	print '<tr><td width="25%">'.$langs->trans('Country').'</td><td>';
	print $form->select_country(GETPOST('country_id','alpha')?GETPOST('country_id','alpha'):$object->country_id,'country_id');
	if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
	print '</td></tr>';

	print '<tr><td><label for="phone_pro">'.$langs->trans("PhonePro").'</label></td>';
	print '<td><input name="phone_pro" id="phone_pro" type="text" size="18" maxlength="80" value="'.dol_escape_htmltag(GETPOST("phone_pro")?GETPOST("phone_pro"):$object->phone_pro).'"></td>';
	print '</tr>';
	print '<td><label for="phone_perso">'.$langs->trans("PhonePerso").'</label></td>';
	print '<td><input name="phone_perso" id="phone_perso" type="text" size="18" maxlength="80" value="'.dol_escape_htmltag(GETPOST("phone_perso")?GETPOST("phone_perso"):$object->phone_perso).'"></td></tr>';
	print '</tr>';

	print '<tr><td><span class="fieldrequired">'.$langs->trans("Email").'</span></td><td><input type="text" name="email" size="40" value="'.(isset($_POST["email"])?$_POST["email"]:$object->email).'"></td></tr>';

	require_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';
	$generated_password=getRandomPassword(false);
	print '<tr><td><span class="fieldrequired">'.$langs->trans("Password").'</span></td><td>';
	print '<input size="30" maxsize="32" type="text" name="password" value="'.$generated_password.'">';
	print '</td></tr>';
	
	
	print '</table> <br>';


	print '<div class="center">';
	print '<input type="submit" class="button" value="' . $langs->trans("Create") . '">';
	print '&nbsp;<input type="button" class="button" value="' . $langs->trans("Cancel") . '" onClick="javascript:history.go(-1)">';
	print '</div>';

	print '</form>';
}