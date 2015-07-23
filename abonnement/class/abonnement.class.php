<?php

require_once (DOL_DOCUMENT_ROOT."/compta/facture/class/facture.class.php");
require_once DOL_DOCUMENT_ROOT.'/compta/paiement/class/paiement.class.php';
require_once (DOL_DOCUMENT_ROOT."/contrat/class/contrat.class.php");
require_once (DOL_DOCUMENT_ROOT."/product/class/product.class.php");
require_once (DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");
require_once (DOL_DOCUMENT_ROOT."/contact/class/contact.class.php");


class Abonnement
{
	var $db ;
	var $error ;
	var $errors = array();
	function __construct($db){
		$this->db = $db;
	}
	function updateExtrafieldsCommandeCommStruture($objet) {
		global $db;
		$extrafields=new ExtraFields($db);
		require_once (DOL_DOCUMENT_ROOT."/abonnement/class/communication_structure.class.php");
			$extralabels=$extrafields->fetch_name_optionals_label('commande',true);
		//
		$arrData['options_comm_structure']=CommStructure::generate($objet->ref);
		//var_dump(CommStructure::genera  te($objet->ref));exit;
		$result = 1; $objet->array_options['options_comm_structure']=CommStructure::generate($objet->ref);
		$objet->insertExtraFields();
		
		$objet->fetch_optionals($objet->id);
		
		return CommStructure::generate($objet->ref);
	}
	function updateExtrafieldsCommande($objet,$arrData) {
		global $db;
		$extrafields=new ExtraFields($db);
		$extralabels=$extrafields->fetch_name_optionals_label('commande',true);
		//var_dump($extralabels);
		foreach($extrafields->attribute_label as $key=>$label)
		{
			$key='options_'.$key;
			if (isset($arrData[$key]))
			{
				$result=$object->setValueFrom($key, $arrData[$key], 'commande_extrafields');
			}
		}
	}
	function createAbonne() {

	}
	function paiementFacture($facture,$montantPaie,$numCh,$id_accound=NULL) {
		global $db,$user;
		//$facture = new Facture($db);
		$paiement = new Paiement($db);
		
		$paiement->datepaye     = dol_now();
		$paiement->amounts      = array($facture->id=>$montantPaie);   // Array with all payments dispatching
		$paiement->paiementid   = dol_getIdFromCode($db,'VIR','c_paiement');
		$paiement->num_paiement = $numCh;
		$paiement->note         = 'paiement par lot';
		$res = $paiement->create($user,1);
		if(!$res) {
			$this->errors = $paiement->errors;
			return $res;
		}
		$label='(CustomerInvoicePayment)';
		$tiers = new Societe($db);
		$tiers->fetch($facture->socid);
		$result=$paiement->addPaymentToBank($user,'payment',$label,$id_accound,$tiers->nom,$numCh);
		
		if(!$result) $this->errors = $paiement->errors;
		
		return $result ;
	}

	function createInvoiceAndContratFromCommande($object,$montantPaie=0,$numCh=1,$id_accound=1) {
		global $db,$user;
		$db->begin();
		//$object = new Commande($db);
		$facture = new Facture($db);
		// = new Contrat($db);
		if(is_object($object)){
				
			$object->valid($user);
			$cn = new commande($db); 
			$contrat = $this->createContratFromCommande($object);
			if (is_object($contrat) )
			{
				$contrat->validate($user);
				//$this->activeServiceContrat($contrat);
				$facture = $this->createInvoiceFromContrat($contrat);
				if (is_object($facture) ) {
					$facture->validate($user);
					// paiement
					$r = $this->paiementFacture($facture, $montantPaie, $numCh, $id_accound);
					if($r<0) $erro++;
					$param = $this->createLoginAbonne($contrat);
					//var_dump($param);exit;
					//creation user par defaut
					$fuser = new User($db);
					require_once(DOL_DOCUMENT_ROOT.'/abonnement/class/html.formabonnement.class.php');
					$login = isset($param['login'])?$param['login']:'';	
					$password = isset($param['password'])?$param['password']:'';
					$formabonne = new FormAbonnement($db);
					$formabonne->envoiEmailFacture($facture,$login,$password);
						
				}else $erro++;

			} else {
				$erro++;
			}
			

				
		}
		if (!$erro) {
			$db->commit();
			return 1;
		} else {
			$db->rollback();
			return -1;
		}
	}
	function createLoginAbonne($contrat) {
		global $db;
		$arrLoginParam = array();
		//$contrat = new Contrat($db);
		if(is_object($contrat)) {
		//$db->begin();
		$contrat->fetch_thirdparty();
		$soc = $contrat->thirdparty;
		
		$contact = $soc->contact;
		$arrContact = $soc->contact_array();
		//var_dump($arrContact,'user');exit;
		//var_dump($arrContact);
		if(is_array($arrContact)&& count($arrContact)>0){
			foreach ($arrContact as $contactid =>$label) {
				$contact = new Contact($db);
				$contact->fetch($contactid); 
				$contrat->add_contact($contact->id, 'ABONWEB','external');
				$nuser = new User($db);
				$nuser->pass='passer';
				$resultUser=$nuser->create_from_contact($contact,$contact->email,'passer');
				//var_dump($resultUser);exit;
				$arrLoginParam = array('login'=>$nuser->login,'password'=>$nuser->pass);
				
			}
			
		 
		}
		return $arrLoginParam;
		//$db->rollback();
		//exit;
		}
	
	}
	function createContratFromcommande1($command) {
		global $user,$db;
		$command = new Commande($db);
		$erro = 0;
		if(is_object($command)) {
			$object = new Contrat($db);
			$object->socid						= $command->socid;
			$object->date_contrat				= dol_now();

			$object->commercial_suivi_id		= $user->id;
			$object->commercial_signature_id	= $user->id;

			//$object->note_private				= Null;
			//$object->note_public				= null;
			//$object->fk_project					= null;
			$object->remise_percent				= $command->remise_percent;
			//$object->ref						= GETPOST('ref','alpha');
			//$object->ref_supplier				= GETPOST('ref_supplier','alpha');
			$res = $object->create($user);
			if ($res < 0) {
				$erro++;
				$this->error=$object->error; $this->errors[]=$object->error;
			}
			$command->fetch_thirdparty();
			$lines = $command->lines;
			if (empty($lines))
			{
				$command->fetch_lines();
				$lines = $command->lines;
			}
			for ($i=0;$i<$num;$i++)
			{
				var_dump($lines[$i]->product_type);exit;
				$product_type=($lines[$i]->product_type?$lines[$i]->product_type:0);

				if ($product_type == 1 || (! empty($conf->global->CONTRACT_SUPPORT_PRODUCTS) && in_array($product_type, array(0,1)))) { 	// TODO Exclude also deee
					// service prédéfini
					if ($lines[$i]->fk_product > 0)
					{
						$product_static = new Product($db);

						// Define output language
						if (! empty($conf->global->MAIN_MULTILANGS) && ! empty($conf->global->PRODUIT_TEXTS_IN_THIRDPARTY_LANGUAGE))
						{
							$prod = new Product($db);
							$prod->id=$lines[$i]->fk_product;
							$prod->getMultiLangs();

							$outputlangs = $langs;
							$newlang='';
							if (empty($newlang) && GETPOST('lang_id')) $newlang=GETPOST('lang_id');
							if (empty($newlang)) $newlang=$srcobject->thirdparty->default_lang;
							if (! empty($newlang))
							{
								$outputlangs = new Translate("",$conf);
								$outputlangs->setDefaultLang($newlang);
							}

							$label = (! empty($prod->multilangs[$outputlangs->defaultlang]["libelle"])) ? $prod->multilangs[$outputlangs->defaultlang]["libelle"] : $lines[$i]->product_label;
						}
						else
						{
							$label = $lines[$i]->product_label;
						}

						if ($conf->global->PRODUIT_DESC_IN_FORM)
							$desc .= ($lines[$i]->desc && $lines[$i]->desc!=$lines[$i]->libelle)?dol_htmlentitiesbr($lines[$i]->desc):'';
					}
					else {
						$desc = dol_htmlentitiesbr($lines[$i]->desc);
					}

					$result = $object->addline(
							$desc,
							$lines[$i]->subprice,
							$lines[$i]->qty,
							$lines[$i]->tva_tx,
							$lines[$i]->localtax1_tx,
							$lines[$i]->localtax2_tx,
							$lines[$i]->fk_product,
							$lines[$i]->remise_percent,
							$lines[$i]->date_start,
							$lines[$i]->date_end,
							'HT',
							0,
							$lines[$i]->info_bits,
							$lines[$i]->fk_fournprice,
							$lines[$i]->pa_ht
					);

					if ($result < 0)
					{
						$erro++;
						$this->error=$object->error; $this->errors[]=$object->error;
						break;
					}

				}
			}
			if(!$erro) return 1;
			else return -1;
		}

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
	function activeServiceContrat($contrat) {
		global $user;
		if(!is_object($contrat)) {
			return -1;
		}
		
		$contrat->fetch_lines();
		$num=count($contrat->lines);
		for ($i = 0; $i < $num; $i++)
		{ 
			$contrat->active_line($user,$contrat->lines[$i]->rowid, $contrat->lines[$i]->date_start,
					$contrat->lines[$i]->date_end);
				

		}
		return 1;

	}
	/**
	 *  Load an object from an order and create a new invoice into database
	 *
	 *  @param      Object			$object         	Object source
	 *  @return     int             					<0 if KO, 0 if nothing done, 1 if OK
	 */
	function createInvoiceFromContrat($object)
	{
		global $conf,$user,$langs,$hookmanager;

		$erro=0;
		$facture = new Facture($this->db);
		// Closed order
		$facture->date = dol_now();
		$facture->source = 0;
		//$object = new Contrat($this->db);
		$object->fetch_lines();
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
			$line->marge_tx			= $marginInfos[1];
			$line->marque_tx		= $marginInfos[2];


			// get extrafields from original line
// 			$object->lines[$i]->fetch_optionals($object->lines[$i]->rowid);
// 			foreach($object->lines[$i]->array_options as $options_key => $value)
// 				$line->array_options[$options_key] = $value;

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
// 		foreach($object->array_options as $options_key => $value)
// 			$facture->array_options[$options_key] = $value;

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
			if ($reshook < 0) $erro++;

			if (! $erro)
			{
				return $facture;
			}
			else return -1;
		}
		else return -1;
	}

	/**
	 *  Load an object from an order and create a new contrat into database
	 *
	 *  @param      Object			$object         	Object source
	 *  @return     int             					<0 if KO, 0 if nothing done, 1 if OK
	 */
	function createContratFromCommande($object)
	{
		global $conf,$user,$langs,$hookmanager;

		$erro=0;
		$contrat = new Contrat($this->db);
		// Closed order
		$contrat->date = dol_now();
		$contrat->source = 0;



		$contrat->socid                   = $object->socid;
		$contrat->date_contrat            = dol_now();
		$contrat->commercial_suivi_id     = $user->id;
		$contrat->commercial_signature_id = $user->id;
		$contrat->remise_percent           = $object->remise_percent;
		$contrat->note_private            = $object->note_private;
		$contrat->note_public            = $object->note_public;
		$contrat->origin				= $object->element;
		$contrat->origin_id			= $object->id;
		$res = $contrat->create($user);

		if ($res < 0) {
			$erro++;
			$this->error=$object->error; $this->errors[]=$object->error;
		}
		$contrat->fetch_thirdparty();
			
		// get extrafields from original line
		$object->fetch_optionals($object->id);
// 		foreach($object->array_options as $options_key => $value)
// 			$contrat->array_options[$options_key] = $value;

		// Possibility to add external linked objects with hooks
		$contrat->linked_objects[$contrat->origin] = $contrat->origin_id;
		if (! empty($object->other_linked_objects) && is_array($object->other_linked_objects))
		{
			$contrat->linked_objects = array_merge($contrat->linked_objects, $object->other_linked_objects);
		}
		require_once (DOL_DOCUMENT_ROOT."/product/class/product.class.php");

		$num=count($object->lines);
		for ($i = 0; $i < $num; $i++)
		{
			$prod = new Product($this->db);
			$prod->fetch($object->lines[$i]->fk_product);
			//var_dump($prod->duration_value);var_dump($prod->duration_unit);exit;

			$fin_date = dol_time_plus_duree(dol_now(), $prod->duration_value, $prod->duration_unit);

			$result = $contrat->addline(
					$object->lines[$i]->desc,
					$object->lines[$i]->subprice,
					$object->lines[$i]->qty,
					$object->lines[$i]->tva_tx,
					$object->lines[$i]->localtax1_tx,
					$object->lines[$i]->localtax2_tx,
					$object->lines[$i]->fk_product,
					$object->lines[$i]->remise_percent,
					dol_now(),//$object->lines[$i]->date_start
					$fin_date,//$object->lines[$i]->date_end,
					'HT',
					0,
					$object->lines[$i]->info_bits,
					$object->lines[$i]->fk_fournprice,
					$object->lines[$i]->pa_ht
			);

			if ($result < 0)
			{
				$erro++;
				$this->error=$object->error; $this->errors[]=$object->error;
				break;
			}

		}

		if(!$erro) return $contrat;
		else return -1;
	}
}