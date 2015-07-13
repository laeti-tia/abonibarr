<?php

require_once (DOL_DOCUMENT_ROOT."/compta/facture/class/facture.class.php");

class Abonnement
{ var $db ;
function __construct($db){
	$this->db = $db;
}
function createAbonne() {

}

function reabonnement($contrat) {
	global $langs;
	if(is_object($contrat)) {
		$facture = $this->createInvoiceFromContact($contrat);
		if($facture){
			$outputlangs = $langs;
			$newlang = '';
			if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id')) $newlang = GETPOST('lang_id');
			if ($conf->global->MAIN_MULTILANGS && empty($newlang)) $newlang = $facture->thirdparty->default_lang;
			if (! empty($newlang))
			{
				$outputlangs = new Translate("", $conf);
				$outputlangs->setDefaultLang($newlang);
			}
			$result = $facture->generateDocument($facture->modelpdf, $outputlangs, $hidedetails, $hidedesc, $hideref);
			//var_dump($result,'jjj');exit;
				
		}
		//var_dump($result,'444jj');exit;
		return $facture;
	}

}
/**
 *  Load an object from an order and create a new invoice into database
 *
 *  @param      Object			$object         	Object source
 *  @return     int             					<0 if KO, 0 if nothing done, 1 if OK
 */
function createInvoiceFromContact($object)
{
	global $conf,$user,$langs,$hookmanager;

	$error=0;
	$facture = new Facture($this->db);
	// Closed order
	$facture->date = dol_now();
	$facture->source = 0;

	$num=count($object->lines);
	for ($i = 0; $i < $num; $i++)
	{
		$line = new FactureLigne($this->db);

		$line->libelle			= $object->lines[$i]->libelle;
		$line->label			= $object->lines[$i]->label;
		$line->desc				= $object->lines[$i]->desc;
		$line->subprice			= $object->lines[$i]->subprice;
		$line->total_ht			= $object->lines[$i]->total_ht;
		$line->total_tva		= $object->lines[$i]->total_tva;
		$line->total_ttc		= $object->lines[$i]->total_ttc;
		$line->tva_tx			= $object->lines[$i]->tva_tx;
		$line->localtax1_tx		= $object->lines[$i]->localtax1_tx;
		$line->localtax2_tx		= $object->lines[$i]->localtax2_tx;
		$line->qty				= $object->lines[$i]->qty;
		$line->fk_remise_except	= $object->lines[$i]->fk_remise_except;
		$line->remise_percent	= $object->lines[$i]->remise_percent;
		$line->fk_product		= $object->lines[$i]->fk_product;
		$line->info_bits		= $object->lines[$i]->info_bits;
		$line->product_type		= 0;//$object->lines[$i]->product_type;
		$line->rang				= $object->lines[$i]->rang;
		$line->special_code		= $object->lines[$i]->special_code;
		$line->fk_parent_line	= $object->lines[$i]->fk_parent_line;

		$line->fk_fournprice	= $object->lines[$i]->fk_fournprice;
		$marginInfos			= getMarginInfos($object->lines[$i]->subprice, $object->lines[$i]->remise_percent, $object->lines[$i]->tva_tx, $object->lines[$i]->localtax1_tx, $object->lines[$i]->localtax2_tx, $object->lines[$i]->fk_fournprice, $object->lines[$i]->pa_ht);
		$line->pa_ht			= $marginInfos[0];

		// get extrafields from original line
		$object->lines[$i]->fetch_optionals($object->lines[$i]->rowid);
		foreach($object->lines[$i]->array_options as $options_key => $value)
			$line->array_options[$options_key] = $value;

		$facture->lines[$i] = $line;
	}

	$facture->socid                = $object->socid;
	$facture->fk_project           = $object->fk_project;
	$facture->cond_reglement_id    = $object->cond_reglement_id;
	$facture->mode_reglement_id    = $object->mode_reglement_id;
	$facture->availability_id      = $object->availability_id;
	$facture->demand_reason_id     = $object->demand_reason_id;
	$facture->date_livraison       = $object->date_livraison;
	$facture->fk_delivery_address  = $object->fk_delivery_address;
	$facture->contact_id           = $object->contactid;
	$facture->ref_client           = $object->ref_client;
	$facture->note_private         = $object->note_private;
	$facture->note_public          = $object->note_public;

	$facture->origin				= $object->element;
	$facture->origin_id			= $object->id;

	// get extrafields from original line
	$object->fetch_optionals($object->id);
	foreach($object->array_options as $options_key => $value)
		$facture->array_options[$options_key] = $value;

	// Possibility to add external linked objects with hooks
	$facture->linked_objects[$facture->origin] = $facture->origin_id;
	if (! empty($object->other_linked_objects) && is_array($object->other_linked_objects))
	{
		$facture->linked_objects = array_merge($facture->linked_objects, $object->other_linked_objects);
	}

	$ret = $facture->create($user);
	$facture->fetch_thirdparty();

	if ($ret > 0)
	{
		// Actions hooked (by external module)
		$hookmanager->initHooks(array('invoicedao'));

		$parameters=array('objFrom'=>$object);
		$action='';
		$reshook=$hookmanager->executeHooks('createFrom',$parameters,$facture,$action);    // Note that $action and $object may have been modified by some hooks
		if ($reshook < 0) $error++;

		if (! $error)
		{
			return $facture;
		}
		else return -1;
	}
	else return -1;
}
}