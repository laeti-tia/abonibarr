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
 *      \file       htdocs/contrat/contact.php
 *      \ingroup    contrat
 *      \brief      Onglet de gestion des contacts des contrats
 */

require ("../main.inc.php");
require_once DOL_DOCUMENT_ROOT.'/core/lib/contract.lib.php';
require_once DOL_DOCUMENT_ROOT.'/contrat/class/contrat.class.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once (DOL_DOCUMENT_ROOT."/abonnement/class/html.formabonnement.class.php");

require_once (DOL_DOCUMENT_ROOT."/abonnement/class/abonnement.class.php");

$langs->load("contracts");
$langs->load("companies");

$action=GETPOST('action','alpha');
$confirm=GETPOST('confirm','alpha');
$socid = GETPOST('socid','int');
$id = GETPOST('id','int');
$ref=GETPOST('ref','alpha');

// Security check
if ($user->societe_id) $socid=$user->societe_id;
$result=restrictedArea($user,'contrat',$id);

$object = new Contrat($db);
$formAbonnement = new FormAbonnement($db);


/*
 * Ajout d'un nouveau contact
*/

if ($action == 'addabonne' && $user->rights->contrat->creer)
{
	$result = $object->fetch($id);



	if ($result > 0 && $id > 0)
	{
		$db->begin();
		$tabAb = $formAbonnement->getArrAbonneWeb($id);
		//var_dump(count($tabAb));exit;
		$nbreMaxAbonneWeb= intval($conf->global->NBRE_MAX_ABONNE_WEB)!=0?$conf->global->NBRE_MAX_ABONNE_WEB:10000;
		//var_dump(($nbreMaxAbonneWeb));exit;
		if(count($tabAb) < $nbreMaxAbonneWeb) {
			$objContact = new Contact($db);
			$objContact->socid			= $object->socid;
			$objContact->lastname		= GETPOST("lastname");
			$objContact->firstname		= GETPOST("firstname");
			$objContact->civility_id	= GETPOST("civility_id",'alpha');
			$objContact->poste			= GETPOST("poste");
			$objContact->address		= GETPOST("address");
			$objContact->zip			= GETPOST("zipcode");
			$objContact->town			= GETPOST("town");
			$objContact->country_id		= GETPOST("country_id",'int');
			$objContact->state_id       = GETPOST("state_id",'int');
			$objContact->skype			= GETPOST("skype");
			$objContact->email			= GETPOST("email",'alpha');
			$objContact->phone_pro		= GETPOST("phone_pro");
			$objContact->phone_perso	= GETPOST("phone_perso");
			$objContact->phone_mobile	= GETPOST("phone_mobile");
			$objContact->fax			= GETPOST("fax");
			$objContact->jabberid		= GETPOST("jabberid",'alpha');
			$objContact->no_email		= GETPOST("no_email",'int');
			$objContact->priv			= GETPOST("priv",'int');
			$objContact->note_public	= GETPOST("note_public");
			$objContact->note_private	= GETPOST("note_private");
			$password = GETPOST("password");
			$objContact->statut			= 1; //Defult status to Actif
			$error=0;
			$errors=array();
			$contactid = (GETPOST('userid') ? GETPOST('userid') : GETPOST('contactid'));
			$contactid =  $objContact->create($user);

			if ($contactid > 0)
			{
				$result = $object->add_contact($contactid, $_POST["type_contact"], 'external');
				if($result > 0) {
					$nuser = new User($db);
					$resultUser=$nuser->create_from_contact($objContact,$objContact->email,$password);
					$nuser->SetInGroup(1, $nuser->entity);
					if ($resultUser < 0)
					{
						$langs->load("errors");
						//$error++; $errors=array_merge($errors,array($langs->trans($nuser->error)));
						//$action = 'create';
						setEventMessage($langs->trans($nuser->error), 'errors');
					}
					$formAbonnement->envoiEmailUser($nuser,$password);

				} else {
					if ($object->error == 'DB_ERROR_RECORD_ALREADY_EXISTS') {
						$langs->load("errors");
						$msg = $langs->trans("ErrorThisContactIsAlreadyDefinedAsThisType");
					} else {
						$mesg = $object->error;
					}
					$error++; $errors=array_merge($errors,array($mesg));
					$action = 'create';
				}

			} else

			{
				$error++; $errors=array_merge($errors,($objContact->error?array($objContact->error):$objContact->errors));
				$action = 'create';
			}
		} else {
			$mesg="Le nombre maximum d'abonné web est de $nbreMaxAbonneWeb, vous ne pouvez plus ajouter d'autre compte";
			$error++; $errors=array_merge($errors,array($mesg));
			$action = 'create';
		}

	}

	if (!$error )
	{
		setEventMessage($langs->trans(AbonAjoutSucces), 'mesgs');
		$db->commit();
		header("Location: ".$_SERVER['PHP_SELF']."?id=".$object->id);
		exit;
	} else {
		foreach ($errors as $ms) $out .=$ms;
		setEventMessage($out, 'errors');
		$db->rollback();
	}

}

//supprimer  un contact
if ($action == 'deletecontact' && $user->rights->contrat->creer)
{
	// 	$object->fetch($id);
	// 	$result = $object->delete_contact($_GET["lineid"]);
	// 	if ($result >= 0)
	// 	{
	// 		header("Location: ".$_SERVER['PHP_SELF']."?id=".$object->id);
	// 		exit;
	// 	}
}

/*
 * View
*/

llxHeader('', $langs->trans("ContractCard"), "Contrat");

$form = new Form($db);
$formcompany= new FormCompany($db);
$contactstatic=new Contact($db);
$userstatic=new User($db);
$companystatic = new Societe($db);

/* *************************************************************************** */
/*                                                                             */
/* Mode vue des abonnés                                                      */
/*                                                                             */
/* *************************************************************************** */

if ($id > 0 )
{
	if ($object->fetch($id) > 0)
	{
		$object->fetch_thirdparty();

		$head = contract_prepare_head($object);

		$hselected=2;

		dol_fiche_head($head, $hselected, $langs->trans("Contract"), 0, 'contract');

		/*
		 *   Contrat
		*/
		print '<table class="border" width="100%">';

		$linkback = '<a href="'.DOL_URL_ROOT.'/contrat/list.php'.(! empty($socid)?'?socid='.$socid:'').'">'.$langs->trans("BackToList").'</a>';

		// Reference du contrat
		print '<tr><td width="25%">'.$langs->trans("Ref").'</td><td colspan="3">';
		print $form->showrefnav($object, 'ref', $linkback, 1, 'ref', 'ref', '');
		print "</td></tr>";

		// Customer
		print "<tr><td>".$langs->trans("Customer")."</td>";
		print '<td colspan="3">'.$object->thirdparty->getNomUrl(1).'</td></tr>';

		// Ligne info remises tiers
		print '<tr><td>'.$langs->trans('Discount').'</td><td>';
		if ($object->thirdparty->remise_percent) print $langs->trans("CompanyHasRelativeDiscount",$object->thirdparty->remise_percent);
		else print $langs->trans("CompanyHasNoRelativeDiscount");
		$absolute_discount=$object->thirdparty->getAvailableDiscounts();
		print '. ';
		if ($absolute_discount) print $langs->trans("CompanyHasAbsoluteDiscount",$absolute_discount,$langs->trans("Currency".$conf->currency));
		else print $langs->trans("CompanyHasNoAbsoluteDiscount");
		print '.';
		print '</td></tr>';

		print "</table>";

		print '</div>';

		print '<br>';

		/*
		 *   formulaire abonné
		*/
		if($action=='create' )
		{
			print '<form name="formaddAbonne" action="' . $_SERVER["PHP_SELF"] . '" method="POST">';
			print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';

			print '<input type="hidden" name="id" value="'.GETPOST('id','int').'">';

			print '<input type="hidden" name="action" value="addabonne">';

			print '<table class="border" width="100%">';

			//print '<tr><td><label for="socid">'.$langs->trans("ThirdParty").'</label></td>';
			//print '<td colspan="3" class="maxwidthonsmartphone">';
			//print $objsoc->getNomUrl(1);
			//print '<input type="hidden" name="socid" id="socid" value="'.$objsoc->id.'">';
			//print '</td></tr>';

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

			print '<tr><td width="15%"><label for="type_contact">'.$langs->trans("ThirdPartyContact").'</label></td>';
			print '<td>';
			print  $formAbonnement->makeAbonWeb();
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

			print '<tr><td><span class="fieldrequired">'.$langs->trans("Email").' / '.$langs->trans("Id").'</span></td><td><input type="text" name="email" size="40" value="'.(isset($_POST["email"])?$_POST["email"]:$object->email).'"></td></tr>';

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


			print '</form> <br><br>';
		}


		$buttoncreate='';

		$addcontact =  $langs->trans("AddAbonne");
		$buttoncreate='<a class="addnewrecord" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=create">'.$addcontact;
		if (empty($conf->dol_optimize_smallscreen)) $buttoncreate.=' '.img_picto($addcontact,'filenew');
		$buttoncreate.='</a>'."\n";

		print "\n";

		$title = (! empty($conf->global->SOCIETE_ADDRESSES_MANAGEMENT) ? $langs->trans("ContactsForCompany") : $langs->trans("ContactsAddressesForCompany"));
		print_fiche_titre($title,$buttoncreate,'');


		print '<table class="noborder" width="100%">'."\n";
		print '<tr class="liste_titre">';
		print_liste_field_titre($langs->trans('Source'));
		print_liste_field_titre($langs->trans('Company'));
		print_liste_field_titre($langs->trans('Contacts'));
		print_liste_field_titre($langs->trans('ContactType'));
		print_liste_field_titre($langs->trans('Status'));
		print_liste_field_titre($langs->trans(''));
		print '</tr>';
		$tab = $object->liste_contact(- 1);
		$totalCount = count($tab);

		$i = 0;
		foreach ($tab as $contacttype)
		{
			print '<tr><td>';
			if ($contacttype['source']=='internal') echo $langs->trans("User");
			if ($contacttype['source']=='external') echo $langs->trans("ThirdPartyContact");
			print '</td>';

			print '<td>';
			if ($contacttype['socid'] > 0) {
				$companystatic->fetch($contacttype['socid']);
				echo $companystatic->getNomUrl(1);
			}
			if ($contacttype['socid'] < 0) {
				echo $conf->global->MAIN_INFO_SOCIETE_NOM;
			}
			if (! $contacttype['socid']) {
				echo '&nbsp;';
			}
			print '</td>';

			print '<td>';
			if ($contacttype['source'] == 'internal') {
				$userstatic->id = $contacttype['id'];
				$userstatic->lastname = $contacttype['lastname'];
				$userstatic->firstname = $contacttype['firstname'];
				echo $userstatic->getNomUrl(1);
			}
			if ($contacttype['source'] == 'external') {
				$contactstatic->id = $contacttype['id'];
				$contactstatic->lastname = $contacttype['lastname'];
				$contactstatic->firstname = $contacttype['firstname'];
				echo $contactstatic->getNomUrl(1);
			}
			print '</td>';

			print '<td>';
			echo $contacttype['libelle'];
			print '</td>';

			print '<td>';
			if ($contacttype['source'] == 'internal') {
				$userstatic->id = $contacttype['id'];
				$userstatic->lastname = $contacttype['lastname'];
				$userstatic->firstname = $contacttype['firstname'];
				// echo $userstatic->LibStatut($contacttype['status'],3);
			}
			if ($contacttype['source'] == 'external') {
				$contactstatic->id = $contacttype['id'];
				$contactstatic->lastname = $contacttype['lastname'];
				$contactstatic->firstname = $contacttype['firstname'];
				echo $contactstatic->LibStatut($contacttype['status'], 3);
			}
			print '</td>';

			print '<td>';
			print '&nbsp;
			<a href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=deletecontact&amp;lineid='.$tab[$i]['rowid'].'">'.img_delete().'</a>';
			print '</td></tr>';
		}

		print '</table>';




	}
	else
	{
		print "ErrorRecordNotFound";
	}
}


llxFooter();
$db->close();
