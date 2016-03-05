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
 *	    \file       htdocs/abonnement/reabonnement.php
 *      \ingroup    other
 *		\brief      liste des réabonnements
 */

require ("../main.inc.php");
require_once (DOL_DOCUMENT_ROOT."/contrat/class/contrat.class.php");
require_once (DOL_DOCUMENT_ROOT."/product/class/product.class.php");
require_once (DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");
require_once (DOL_DOCUMENT_ROOT."/abonnement/class/contrat.pdfmasse.class.php");
require_once (DOL_DOCUMENT_ROOT."/abonnement/class/html.formabonnement.class.php");
require_once (DOL_DOCUMENT_ROOT."/abonnement/class/abonnement.class.php");
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';


$langs->load("products");
$langs->load("contracts");
$langs->load("companies");
$langs->load("abonnement");


$mode = GETPOST("mode");
$sortfield = GETPOST("sortfield",'alpha');
$sortorder = GETPOST("sortorder",'alpha');
$action = GETPOST("action",'alpha');
$page = GETPOST("page",'int');
if ($page == -1) {
	$page = 0 ;
}
$limit = $conf->liste_limit;
$offset = $limit * $page ;

if (! $sortfield) $sortfield="c.rowid";
if (! $sortorder) $sortorder="ASC";

$filter=GETPOST("filter");
$search_name=GETPOST("search_name");
$search_contract=GETPOST("search_contract");
$search_service=GETPOST("search_service");
$statut=GETPOST('statut')?GETPOST('statut'):1;
$socid=GETPOST('socid','int');

$op1month=GETPOST('op1month');
$op1day=GETPOST('op1day');
$op1year=GETPOST('op1year');
$filter_op1=GETPOST('filter_op1');
$op2month=GETPOST('op2month');
$op2day=GETPOST('op2day');
$op2year=GETPOST('op2year');
$filter_op2=GETPOST('filter_op2');

// Security check
$contratid = GETPOST('id','int');
if (! empty($user->societe_id)) $socid=$user->societe_id;
$result = restrictedArea($user, 'contrat',$contratid);


$staticcontrat=new Contrat($db);
$staticcontratligne=new ContratLigne($db);
$companystatic=new Societe($db);

if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter")) // Both test are required to be compatible with all browsers
{
	$search_name="";
	$search_contract="";
	$search_service="";
	$op1month="";
	$op1day="";
	$op1year="";
	$filter_op1="";
	$op2month="";
	$op2day="";
	$op2year="";
	$filter_op2="";
	
}
//var_dump($_REQUEST);exit;
if ($action == 'confirmesendmail' && GETPOST('cancel'))
{
	$action = '';
}elseif($action == 'confirmesendmail' ) {
	if (!isset($user->email))
	{
		$error++;
		setEventMessage("NoSenderEmailDefined");
	}
	$countToSend = count($_POST['toSend']);
	if (empty($countToSend))
	{
		$error++;
		setEventMessage("AbonContratChecked","warnings");
	}
	if (! $error)
	{
		$nbsent = 0;
		$nbignored = 0;
		$pdfMasse = new ContratPDFMasse();
		$subject = GETPOST('subject');
		$message = GETPOST('message');
		$sendtocc = GETPOST('sentocc');
		
		$pdfMasse->generePDF($_POST['toSend'],$message,$subject,$sendtocc,$db );

		$nbsent = $pdfMasse->nbsent;
		$nbignored = $pdfMasse->nbignored;
		$resultmasssend=$pdfMasse->resultmasssend;

		if ($nbsent)
		{
			$action='';	
			setEventMessage($nbsent. '/'.$countToSend.' '.$langs->trans("AbonEnvoiReabonnement"));
		}
		else
		{
			setEventMessage($langs->trans("AbonAuncuneFatureEnvoiyer"), 'warnings');
		}
	}

}

/*
 * View
*/

$now=dol_now();

$form=new Form($db);

llxHeader();
?>
<script type="text/javascript">
$(document).ready(function() {
	$("#checkallsend").click(function() {
		$(".checkforsend").attr('checked', true);
	});
	$("#checknonesend").click(function() {
		$(".checkforsend").attr('checked', false);
	});
});
</script>

<?php

$sql = "SELECT c.rowid as cid, c.ref, c.statut as cstatut,";
$sql.= " s.rowid as socid, s.nom as name,";
$sql.= " cd.rowid, cd.description, cd.statut,";
$sql.= " p.rowid as pid, p.ref as pref, p.label as label, p.fk_product_type as ptype,";
if (!$user->rights->societe->client->voir && !$socid) $sql .= " sc.fk_soc, sc.fk_user,";
$sql.= " cd.date_ouverture_prevue,";
$sql.= " cd.date_ouverture,";
$sql.= " cd.date_fin_validite,";
$sql.= " cd.date_cloture,";
$sql.= " DATEDIFF( cd.date_fin_validite,now()) as nbre_jours_renouveau";
$sql.= " FROM ".MAIN_DB_PREFIX."contrat as c ";
$sql.= " LEFT JOIN  ".MAIN_DB_PREFIX."societe as s ON  c.fk_soc = s.rowid ";
//if (!$user->rights->societe->client->voir && !$socid) $sql .= " ".MAIN_DB_PREFIX."societe_commerciaux as sc,";
$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."contratdet as cd ON  c.rowid = cd.fk_contrat ";
$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."product as p ON cd.fk_product = p.rowid";
$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."contrat_extrafields as ce ON c.rowid = ce.fk_object ";

$sql.= " WHERE c.entity = ".$conf->entity;
$sql.= " AND (ce.prop_renouv=0 OR ce.prop_renouv is null) ";
// On ne prend pas les abonnements fermés.
$sql.= " AND cd.statut != 5 ";

if( $conf->global->NBRE_JOURS_AVANT_RENOUVELLEMENT) $sql.=" AND DATEDIFF( date_fin_validite,now()) <= ". $conf->global->NBRE_JOURS_AVANT_RENOUVELLEMENT;
//$limit = 10000;

//if (!$user->rights->societe->client->voir && !$socid) $sql .= " AND s.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;
//if ($mode == "0") $sql.= " AND cd.statut = 0";
//if ($mode == "4") $sql.= " AND cd.statut = 4";
//if ($mode == "5") $sql.= " AND cd.statut = 5";
if ($filter == "expired") $sql.= " AND cd.date_fin_validite < '".$db->idate($now)."'";
if ($search_name)     $sql.= " AND s.nom LIKE '%".$db->escape($search_name)."%'";
if ($search_contract) $sql.= " AND c.ref = '".$db->escape($search_contract)."'";
if ($search_service)  $sql.= " AND (p.ref LIKE '%".$db->escape($search_service)."%' OR p.description LIKE '%".$db->escape($search_service)."%' OR cd.description LIKE '%".$db->escape($search_service)."%')";
if ($socid > 0)       $sql.= " AND s.rowid = ".$socid;
$filter_date1=dol_mktime(0,0,0,$op1month,$op1day,$op1year);
$filter_date2=dol_mktime(0,0,0,$op2month,$op2day,$op2year);
if (! empty($filter_op1) && $filter_op1 != -1 && $filter_date1 != '') $sql.= " AND date_ouverture_prevue ".$filter_op1." '".$db->idate($filter_date1)."'";
if (! empty($filter_op2) && $filter_op2 != -1 && $filter_date2 != '') $sql.= " AND date_fin_validite ".$filter_op2." '".$db->idate($filter_date2)."'";
$sql .= $db->order($sortfield,$sortorder);
//$sql .= $db->plimit($limit + 1, $offset);
//var_dump($sql);exit;
dol_syslog("contrat/services.php", LOG_DEBUG);

print '<form method="POST" action="'. $_SERVER["PHP_SELF"] .'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';


	include_once DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php';
	$formmail = new FormMail($db);

	print '<br>';
	print_fiche_titre($langs->trans("sendReabonnement"),'','');
	print '<br>';

	$topicmail="MailTopicSendInvoices";
	$modelmail="reabonnement";

	// Cree l'objet formulaire mail
	include_once DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php';
	$formmail = new FormMail($db);
	$formmail->withform=-1;
	$formmail->fromtype = 'user';
	$formmail->fromid   = $user->id;
	$formmail->fromname = $user->getFullName($langs);
	$formmail->frommail = $user->email;
	$formmail->withfrom=1;
	$liste=array();
	$formmail->withto=$langs->trans("AllRecipientSelectedForReabonnement");
	$formmail->withtofree=0;
	$formmail->withtoreadonly=1;
	$formmail->withtocc=1;
	$formmail->withtoccc=$conf->global->MAIN_EMAIL_USECCC;
	//->withtopic=$langs->transnoentities($topicmail, '__FACREF__', '__REFCLIENT__');
	$formmail->withfile=$langs->trans("AbonAttacheFactureEMail");
	$formmail->withbody=1;
	$formmail->withdeliveryreceipt=1;
	$formmail->withcancel=1;
	// Tableau des substitutions
	//$formmail->substit['__FACREF__']='';
	$formmail->substit['__SIGNATURE__']=$user->signature;
	//$formmail->substit['__REFCLIENT__']='';
	$formmail->substit['__PERSONALIZED__']='';
	$formmail->substit['__CONTACTCIVNAME__']='';

	// Tableau des parametres complementaires du post
	$formmail->param['action']='confirmesendmail';;
	//$formmail->param['confirmesendmail']='confirmesendmail';
	$formmail->param['models']=$modelmail;
	//$formmail->param['facid']=$object->id;
	//$formmail->param['returnurl']=$_SERVER["PHP_SELF"].'?id='.$object->id;

	print $formmail->get_form();

	print '</form><br>'."\n";

        print '<form method="POST" action="'. $_SERVER["PHP_SELF"] .'">';
        print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';

if ($resultmasssend)
{
	print '<br><strong>'.$langs->trans("ResultOfMassSending").':</strong><br>'."\n";
	print $langs->trans("Selected").': '.$countToSend."\n<br>";
	print $langs->trans("Ignored").': '.$nbignored."\n<br>";
	print $langs->trans("Sent").': '.$nbsent."\n<br>";
	//print $resultmasssend;
	print '<br>';
}





$resql=$db->query($sql);
if ($resql)
{
	$num = $db->num_rows($resql);
	$confListeTemp = $conf->liste_limit ;
	$conf->liste_limit = $num;
	$i = 0;
	$param='';
	if ($search_contract) $param.='&amp;search_contract='.urlencode($search_contract);
	if ($search_name)      $param.='&amp;search_name='.urlencode($search_name);
	if ($search_service)  $param.='&amp;search_service='.urlencode($search_service);
	if ($mode)            $param.='&amp;mode='.$mode;
	if ($filter)          $param.='&amp;filter='.$filter;
	if (! empty($filter_op1) && $filter_op1 != -1) $param.='&amp;filter_op1='.urlencode($filter_op1);
	if (! empty($filter_op2) && $filter_op2 != -1) $param.='&amp;filter_op2='.urlencode($filter_op2);
	if ($filter_date1 != '') $param.='&amp;op1day='.$op1day.'&amp;op1month='.$op1month.'&amp;op1year='.$op1year;
	if ($filter_date2 != '') $param.='&amp;op2day='.$op2day.'&amp;op2month='.$op2month.'&amp;op2year='.$op2year;

	$title=$langs->trans("REABONNEMENTLIST");
	if ($mode == "0") $title=$langs->trans("ListOfInactiveServices");	// Must use == "0"
	if ($mode == "4" && $filter != "expired") $title=$langs->trans("ListOfRunningServices");
	if ($mode == "4" && $filter == "expired") $title=$langs->trans("ListOfExpiredServices");
	if ($mode == "5") $title=$langs->trans("ListOfClosedServices");
	print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder,'',$num);

	print '<table class="liste" width="100%">';

	print '<tr class="liste_titre">';
	print_liste_field_titre($langs->trans("Contract"),$_SERVER["PHP_SELF"], "c.rowid",$param,"","",$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("Service"),$_SERVER["PHP_SELF"], "p.description",$param,"","",$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("Company"),$_SERVER["PHP_SELF"], "s.nom",$param,"","",$sortfield,$sortorder);
	// Date debut
	if ($mode == "0") print_liste_field_titre($langs->trans("DateStartPlannedShort"),$_SERVER["PHP_SELF"], "cd.date_ouverture_prevue",$param,'',' align="center"',$sortfield,$sortorder);
	if ($mode == "" || $mode > 0) print_liste_field_titre($langs->trans("DateStartRealShort"),$_SERVER["PHP_SELF"], "cd.date_ouverture",$param,'',' align="center"',$sortfield,$sortorder);
	// Date fin
	if ($mode == "" || $mode < 5) print_liste_field_titre($langs->trans("DateEndPlannedShort"),$_SERVER["PHP_SELF"], "cd.date_fin_validite",$param,'',' align="center"',$sortfield,$sortorder);
	else print_liste_field_titre($langs->trans("DateEndRealShort"),$_SERVER["PHP_SELF"], "cd.date_cloture",$param,'',' align="center"',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("ABON_NBRE_JOURS"),$_SERVER["PHP_SELF"], "s.nom",$param,"","",$sortfield,$sortorder);

	print_liste_field_titre($langs->trans("Status"),$_SERVER["PHP_SELF"], "cd.statut,c.statut",$param,"","align=\"right\"",$sortfield,$sortorder);
	print '<td class="liste_titre" align="center">';
	if ($conf->use_javascript_ajax) print '<a href="#" id="checkallsend">'.$langs->trans("All").'</a> / <a href="#" id="checknonesend">'.$langs->trans("None").'</a>';
	print '</td>';
	print "</tr>\n";


	print '<tr class="liste_titre">';
	print '<td class="liste_titre">';
	print '<input type="hidden" name="filter" value="'.$filter.'">';
	print '<input type="hidden" name="mode" value="'.$mode.'">';
	print '<input type="text" class="flat" size="3" name="search_contract" value="'.dol_escape_htmltag($search_contract).'">';
	print '</td>';
	// Service label
	print '<td class="liste_titre">';
	print '<input type="text" class="flat" size="18" name="search_service" value="'.dol_escape_htmltag($search_service).'">';
	print '</td>';
	// Third party
	print '<td class="liste_titre">';
	print '<input type="text" class="flat" size="24" name="search_name" value="'.dol_escape_htmltag($search_name).'">';
	print '</td>';
	print '<td class="liste_titre" align="center">';
	$arrayofoperators=array('<'=>'<','>'=>'>');
	print $form->selectarray('filter_op1',$arrayofoperators,$filter_op1,1);
	print ' ';
	$filter_date1=dol_mktime(0,0,0,$op1month,$op1day,$op1year);
	print $form->select_date($filter_date1,'op1',0,0,1);
	print '</td>';
	print '<td class="liste_titre" align="center">';
	$arrayofoperators=array('<'=>'<','>'=>'>');
	print $form->selectarray('filter_op2',$arrayofoperators,$filter_op2,1);
	print ' ';
	$filter_date2=dol_mktime(0,0,0,$op2month,$op2day,$op2year);
	print $form->select_date($filter_date2,'op2',0,0,1);
	print '</td>';
	print '<td>';
	//print '<input type="text" class="flat" size="24" name="search_name" value="'.dol_escape_htmltag($search_name).'">';
	print '</td>';
	print '<td class="liste_titre" align="right"><input type="image" class="liste_titre" name="button_search" src="'.img_picto($langs->trans("Search"),'search.png','','',1).'" value="'.dol_escape_htmltag($langs->trans("Search")).'" title="'.dol_escape_htmltag($langs->trans("Search")).'">';
	print '<input type="image" class="liste_titre" name="button_removefilter" src="'.img_picto($langs->trans("Search"),'searchclear.png','','',1).'" value="'.dol_escape_htmltag($langs->trans("RemoveFilter")).'" title="'.dol_escape_htmltag($langs->trans("RemoveFilter")).'">';
	print "</td>";
	print '<td class="liste_titre" align="center">';
	print '</td>';
	print "</tr>\n";
	

	$contractstatic=new Contrat($db);
	$productstatic=new Product($db);

	$var=True;
	while ($i < $num)
	{
		$obj = $db->fetch_object($resql);
		$var=!$var;
		print "<tr ".$bc[$var].">";
		print '<td>';
		$contractstatic->id=$obj->cid;
		$contractstatic->ref=$obj->ref?$obj->ref:$obj->cid;
		print $contractstatic->getNomUrl(1,16);
		print '</td>';

		// Service
		print '<td>';
		if ($obj->pid)
		{
			$productstatic->id=$obj->pid;
			$productstatic->type=$obj->ptype;
			$productstatic->ref=$obj->pref;
			print $productstatic->getNomUrl(1,'',20);
			print $obj->label?' - '.dol_trunc($obj->label,16):'';
			if (! empty($obj->description) && ! empty($conf->global->PRODUCT_DESC_IN_LIST)) print '<br>'.dol_nl2br($obj->description);
		}
		else
		{
			if ($obj->type == 0) print img_object($obj->description,'product').dol_trunc($obj->description,20);
			if ($obj->type == 1) print img_object($obj->description,'service').dol_trunc($obj->description,20);
		}
		print '</td>';

		// Third party
		print '<td>';
		$companystatic->id=$obj->socid;
		$companystatic->name=$obj->name;
		$companystatic->client=1;
		print $companystatic->getNomUrl(1,'customer',28);
		print '</td>';

		// Start date
		if ($mode == "0") {
			print '<td align="center">';
			print ($obj->date_ouverture_prevue?dol_print_date($db->jdate($obj->date_ouverture_prevue)):'&nbsp;');
			if ($db->jdate($obj->date_ouverture_prevue) && ($db->jdate($obj->date_ouverture_prevue) < ($now - $conf->contrat->services->inactifs->warning_delay)))
				print img_picto($langs->trans("Late"),"warning");
			else print '&nbsp;&nbsp;&nbsp;&nbsp;';
			print '</td>';
		}
		if ($mode == "" || $mode > 0) print '<td align="center">'.($obj->date_ouverture?dol_print_date($db->jdate($obj->date_ouverture)):'&nbsp;').'</td>';
		// Date fin
		if ($mode == "" || $mode < 5) print '<td align="center">'.($obj->date_fin_validite?dol_print_date($db->jdate($obj->date_fin_validite)):'&nbsp;');
		else print '<td align="center">'.dol_print_date($db->jdate($obj->date_cloture));
		// Icone warning
		if ($obj->date_fin_validite && $db->jdate($obj->date_fin_validite) < ($now - $conf->contrat->services->expires->warning_delay) && $obj->statut < 5) print img_warning($langs->trans("Late"));
		else print '&nbsp;&nbsp;&nbsp;&nbsp;';
		print '</td>';
		print '<td>'.dol_getdate($db->jdate($objp->date_fin_validite),true).'</td>';
		print '<td align="right" class="nowrap"> ';
		if ($obj->cstatut == 0)	// If contract is draft, we say line is also draft
		{
			print $contractstatic->LibStatut(0,5,($obj->date_fin_validite && $db->jdate($obj->date_fin_validite) < $now));
		}
		else
		{
			print $staticcontratligne->LibStatut($obj->statut,5,($obj->date_fin_validite && $db->jdate($obj->date_fin_validite) < $now)?1:0);
		}
		print '</td>';
		//
		print '<td class="nowrap" align="center">';
		print '<input class="flat checkforsend" type="checkbox" name="toSend[]" value="'.$obj->cid.'">';
		print '</td>' ;
		print "</tr>\n";
		$i++;
	}
	$db->free($resql);

	print "</table>";
	$conf->liste_limit=$confListeTemp ;
}
else
{
	dol_print_error($db);
}


print '</form>';

$db->close();

llxFooter();
