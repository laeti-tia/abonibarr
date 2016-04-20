<?php

require_once (DOL_DOCUMENT_ROOT."/compta/facture/class/facture.class.php");
require_once (DOL_DOCUMENT_ROOT."/commande/class/commande.class.php");

require_once (DOL_DOCUMENT_ROOT."/compta/paiement/class/paiement.class.php");
require_once (DOL_DOCUMENT_ROOT."/contrat/class/contrat.class.php");
require_once (DOL_DOCUMENT_ROOT."/product/class/product.class.php");
require_once (DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");
require_once (DOL_DOCUMENT_ROOT."/contact/class/contact.class.php");
require_once (DOL_DOCUMENT_ROOT."/core/lib/date.lib.php");


class Abonnement
{
	var $db ;
	var $error ;
	var $errors = array();
	var $warring = null;
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
	function updateExtrafieldsContrat($objet,$arrData) {
		global $db;
		$extrafields=new ExtraFields($db);
		$extralabels=$extrafields->fetch_name_optionals_label('contrat',true);
		//
		//$objet = new Contrat($db);
		foreach($extrafields->attribute_label as $key=>$label)
		{
			$key1='options_'.$key;
			if (isset($arrData[$key]))
			{
				$objet->array_options[$key1]=$arrData[$key];
			}
		}
		return $objet->insertExtraFields();

		//$objet->fetch_optionals($objet->id);
		//var_dump($objet->array_options);exit;

	}
	function updateExtrafieldsFacture($objet,$arrData) {
		global $db;
		$extrafields=new ExtraFields($db);
		$extralabels=$extrafields->fetch_name_optionals_label('facture',true);
		//
		//$objet = new Contrat($db);
		foreach($extrafields->attribute_label as $key=>$label)
		{
			$key1='options_'.$key;
			if (isset($arrData[$key]))
			{
				$objet->array_options[$key1]=$arrData[$key];
			}
		}
		//$objet->fetch_optionals($objet->id);
		//var_dump($objet->array_options);exit;
		return $objet->insertExtraFields();



	}
	function updateExtrafieldsCommande($objet,$arrData) {
		global $db;
		$extrafields=new ExtraFields($db);
		$extralabels=$extrafields->fetch_name_optionals_label('commande',true);
		//var_dump($extralabels);
		foreach($extrafields->attribute_label as $key=>$label)
		{
			$key1='options_'.$key;
			if (isset($arrData[$key]))
			{
				$objet->array_options[$key1]=$arrData[$key];
			}
		}
		return $objet->insertExtraFields();
	}
	function updateExtrafieldsContrat1($object,$arrData) {
		global $db;
		$extrafields=new ExtraFields($db);
		$extralabels=$extrafields->fetch_name_optionals_label('contrat',true);

		foreach($extrafields->attribute_label as $key=>$label)
		{
			$key1='options_'.$key;
			if (isset($arrData[$key]))
			{  // $object = new Contrat($db);

				$result=$object->setValueFrom($key, $arrData[$key], 'contrat_extrafields');
				//var_dump($result);exit;
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
			//$object = new commande($db);
			$object->fetchObjectLinked('','','contrat');
			$contratArr = $object->linkedObjects;
			// comm structurée
			$object->fetch_optionals($object->id);
			$attributs = $object->array_options;
			//var_dump($attributs);exit;
			$commStructuree = $attributs['options_comm_structure'];
			$id_contract_pre = isset($attributs['options_contract_pre'])?$attributs['options_contract_pre']:0;


			if(isset($contratArr['contrat'])) {
				$keyscontrat=array_keys($contratArr['contrat']);
				$firstcontrat=$keyscontrat[0];
				$contrat=$contratArr['contrat'][$firstcontrat];
			}
			else
				$contrat = $this->createContratFromCommande($object);

			if (is_object($contrat) )
			{
				$contrat->validate($user);
				$contrat->fetchObjectLinked('','','facture');
				$factureArr = $contrat->linkedObjects;


				if(isset($factureArr['facture'])){
					$keysfacture=array_keys($factureArr['facture']);
					$firstfacture=$keysfacture[0];
					$facture=$factureArr['facture'][$firstfacture];
				}
				else
					$facture = $this->createInvoiceFromContrat($contrat);


				//$factureArr = isset($contrat->linkedObjects["facture"])?$contrat->linkedObjects["facture"]:array();
				//$factureArr = $contrat->linkedObjects;

				if (is_object($facture) ) {
					$facture->validate($user);
					//$result=$facture->setValueFrom($key, $arrData[$key], 'commande_extrafields');
					$this->updateExtrafieldsFacture($facture, array('comm_structure'=>$commStructuree));
					// paiement
					$r = $this->paiementFacture($facture, $montantPaie, $numCh, $id_accound);
					if($r<0) $erro++;

					$montantpaye = 0;
					foreach ($facture->getListOfPayments() as $paie) {
						$montantpaye+=$paie ["amount"];
					}


					//var_dump($montantpaye >=$object->total_ttc,$montantpaye,$object->total_ttc);exit;
					// si paiement complet activer le service
					//var_dump($montantpaye >=$object->total_ttc);exit;
					if($montantpaye >=$object->total_ttc) {
						$t=$this->activeServiceContrat($contrat);
						
						
						if($id_contract_pre > 0) {
							$contratReab = new Contrat($db);
							$contratReab->fetch($id_contract_pre);	
							$arrAbonne = $contratReab->liste_contact(- 1);
							
							foreach ($arrAbonne as $abonne) {
								$result = $contrat->add_contact($abonne['id'], $abonne["code"], $abonne["source"]);
								//var_dump($result);
							}
							$templatemail ='confirme_reabon';
							//$templatemail ='confirme_abon';
						} else {
							// nouveau contrat  on va creer les logins
							$param = $this->createLoginAbonne($contrat);
							//var_dump($param);exit;
							//creation user par defaut
							$fuser = new User($db);
								
							$login = isset($param['login'])?$param['login']:'';
							$password = isset($param['password'])?$param['password']:'';
							$templatemail ='confirme_abon';
						}
						//exit;
					} else {
						$templatemail ='paiement_incomplet';
						$this->warring='Paiement incomplet pour Commande <a href="'.DOL_URL_ROOT.'/commande/card.php?id='.$object->id. '">'.$object->ref.'</a>';
					}
					require_once(DOL_DOCUMENT_ROOT.'/abonnement/class/html.formabonnement.class.php');
					$formabonne = new FormAbonnement($db);
					$formabonne->envoiEmailFacture($facture,$login,$password,$templatemail,null,$object->ref);

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
		global $db,$conf;
		$arrLoginParam = array();
		//$contrat = new Contrat($db);

		if(is_object($contrat)) {
			//$db->begin();
			$contrat->fetch_thirdparty();
			$soc = $contrat->thirdparty;

			$contact = $soc->contact;
			$arrContact = $soc->contact_array();
			$contrat->fetch_lines();

			$idprod = $contrat->lines[0]->fk_product;
			$prod = new Product($db);
			$prod->fetch($idprod);
			//$extrafields = new ExtraFields($db);
			//$attributsLabel = $extrafields->fetch_name_optionals_label($prod->table_element);
			$prod->fetch_optionals($idprod);
			$attributs = $prod->array_options;
			// var_dump( $attributs);exit;
			// $attributs['options_type_produit']='';
			//$num=count($contrat->lines);
			//var_dump($arrContact,'user');exit;
			//var_dump($arrContact,'login');
			if(is_array($arrContact)&& count($arrContact)>0){
				require_once DOL_DOCUMENT_ROOT."/core/lib/security2.lib.php";

				foreach ($arrContact as $contactid =>$label) {
					$contact = new Contact($db);
					$contact->fetch($contactid);
					if(isset($attributs['options_type_produit']) && ($attributs['options_type_produit']==2 || $attributs['options_type_produit']==3))
						$contrat->add_contact($contact->id, 'ABONWEB','external');

					if(isset($attributs['options_type_produit']) && ($attributs['options_type_produit']==1 || $attributs['options_type_produit']==3))
						$contrat->add_contact($contact->id, 'ABONPAPIER','external');
					$nuser = new User($db);
					$nuser->pass=getRandomPassword(false);
					$resultUser=$nuser->create_from_contact($contact,$contact->email,$nuser->pass);
					if( intval($conf->global->PROFIL_CLIENT)> 0 )
						$nuser->SetInGroup(intval($conf->global->PROFIL_CLIENT), $nuser->entity);

					//var_dump($nuser->newgroupid,'user');exit;
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
				//var_dump($lines[$i]->product_type);exit;
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
		global $langs,$user;
		if(is_object($contrat)) {
			//$commande = $this->createInvoiceFromContact($contrat);
			// creation de la commande
			$commande = $this->createCommandeFromContrat($contrat);

			//ici
			if($commande){
				//$contrat->cloture($user);
				$this->updateExtrafieldsContrat($contrat, array('prop_renouv'=>1));
				$outputlangs = $langs;
				$newlang = '';
				if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id')) $newlang = GETPOST('lang_id');
				if ($conf->global->MAIN_MULTILANGS && empty($newlang)) $newlang = $commande->thirdparty->default_lang;
				if (! empty($newlang))
				{
					$outputlangs = new Translate("", $conf);
					$outputlangs->setDefaultLang($newlang);
				}
				$commande->valid($user);
				$this->updateExtrafieldsCommande($commande,  array('contract_pre'=>$contrat->id));
				$note = $this->updateExtrafieldsCommandeCommStruture($commande);
				$commande->update_note_public('Communication structurée : '.$note);
				$result = $commande->generateDocument($commande->modelpdf, $outputlangs, $hidedetails, $hidedesc, $hideref);
				//var_dump($result,'jjj');exit;

			}
			//var_dump($result,'444jj');exit;
			return $commande;
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
			$contrat->active_line($user,$contrat->lines[$i]->id, $contrat->lines[$i]->date_start,
					$contrat->lines[$i]->date_fin_validite);


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
	 *  Load an object from an order and create a new invoice into database
	 *
	 *  @param      Object			$object         	Object source
	 *  @return     int             					<0 if KO, 0 if nothing done, 1 if OK
	 */
	function createCommandeFromContrat($object)
	{
		global $conf,$user,$langs,$hookmanager;

		$erro=0;
		$commande = new Commande($this->db);
		// Closed order
		$commande->date = dol_now();
		$commande->source = 0;
		//$object = new Contrat($this->db);
		$object->fetch_lines();
		$num=count($object->lines);
		for ($i = 0; $i < $num; $i++)
		{
			$line = new OrderLine($this->db);

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

			$commande->lines[$i] = $line;
		}

		$commande->socid                = $object->socid;
		$commande->fk_project           = $object->fk_project;
		$commande->cond_reglement_id    = $object->cond_reglement_id;
		$commande->mode_reglement_id    = $object->mode_reglement_id;
		$commande->availability_id      = $object->availability_id;
		$commande->demand_reason_id     = $object->demand_reason_id;
		$commande->date_livraison       = $object->date_livraison;
		$commande->fk_delivery_address  = $object->fk_delivery_address;
		$commande->contact_id           = $object->contactid;
		$commande->ref_client           = $object->ref_client;
		$commande->note_private         = $object->note_private;
		$commande->note_public          = $object->note_public;

		//$commande->origin				= $object->element;
		$commande->origin_id			= $object->id;

		// get extrafields from original line
		$object->fetch_optionals($object->id);
		// 		foreach($object->array_options as $options_key => $value)
		// 			$commande->array_options[$options_key] = $value;

		// Possibility to add external linked objects with hooks
		// 		$commande->linked_objects[$commande->origin] = $commande->origin_id;
		// 		if (! empty($object->other_linked_objects) && is_array($object->other_linked_objects))
		// 		{
		// 		$commande->linked_objects = array_merge($commande->linked_objects, $object->other_linked_objects);
		// 		}

		$ret = $commande->create($user);
		$commande->fetch_thirdparty();

		if ($ret > 0)
		{
			// Actions hooked (by external module)
			//$hookmanager->initHooks(array('invoicedao'));

			$parameters=array('objFrom'=>$object);
			$action='';
			$reshook=$hookmanager->executeHooks('createFrom',$parameters,$commande,$action);    // Note that $action and $object may have been modified by some hooks
			if ($reshook < 0) $erro++;

			if (! $erro)
			{
				return $commande;
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

		$erro=0;///
		$contrat = new Contrat($this->db);
		// Closed order
		$contrat->date = dol_now();
		$contrat->source = 0;
		$date_debut = dol_now();

		$object->fetch_optionals($object->id);
		$attributs = $object->array_options;
		//var_dump($attributs);exit;

		$id_contract_pre = isset($attributs['options_contract_pre'])?$attributs['options_contract_pre']:0;
		if(intval($id_contract_pre )>0) {
			$contrat_pre = new Contrat($this->db);
			$contrat_pre->fetch($id_contract_pre);
			if(count($contrat_pre->lines) > 0)  {
				$date_debut = $contrat_pre->lines[0]->date_fin_validite;
				//var_dump($date_debut);
				if(!is_null($date_debut))
					$date_debut = dol_time_plus_duree($date_debut , 1, 'd');
			}
		}

			
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

			$fin_date = dol_time_plus_duree($date_debut, $prod->duration_value, $prod->duration_unit);

			$result = $contrat->addline(
					$object->lines[$i]->desc,
					$object->lines[$i]->subprice,
					$object->lines[$i]->qty,
					$object->lines[$i]->tva_tx,
					$object->lines[$i]->localtax1_tx,
					$object->lines[$i]->localtax2_tx,
					$object->lines[$i]->fk_product,
					$object->lines[$i]->remise_percent,
					$date_debut,//$object->lines[$i]->date_start
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

	function contrat_expire($duration_value,$duration_value2){
		$sql = "SELECT DISTINCT tc.rowid, tc.code, tc.libelle";
		$sql.= " FROM ".MAIN_DB_PREFIX."c_type_contact as tc";
		$sql.= " WHERE tc.element='contrat'";
		$sql.= " AND tc.source='external'";
		$sql.= " AND tc.code like'ABON%'";

		$sql  = "SELECT c.ref, cd.date_fin_validite, cd.total_ttc, cd.description as description, p.label as plabel,";
		$sql.= " s.rowid as sid, s.nom as name, s.email, s.default_lang";
		$sql.= ", sp.rowid as cid, sp.firstname as cfirstname, sp.lastname as clastname, sp.email as cemail";
		$sql .= " FROM ".MAIN_DB_PREFIX."societe AS s";
		$sql.= ", ".MAIN_DB_PREFIX."socpeople as sp";
		$sql .= ", ".MAIN_DB_PREFIX."contrat AS c";
		$sql .= ", ".MAIN_DB_PREFIX."contratdet AS cd";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."product AS p ON p.rowid = cd.fk_product";
		$sql .= " WHERE s.rowid = c.fk_soc AND c.rowid = cd.fk_contrat AND c.statut > 0 AND cd.statut < 5";
		if (is_numeric($duration_value2)) $sql.= " AND cd.date_fin_validite >= '".$db->idate(dol_time_plus_duree($now, $duration_value2, "d"))."'";
		if (is_numeric($duration_value)) $sql.= " AND cd.date_fin_validite < '".$db->idate(dol_time_plus_duree($now, $duration_value, "d"))."'";
		$sql.= " AND s.rowid = sp.fk_soc";
		$sql.= " ORDER BY";
		$sql.= " sp.email, sp.rowid,";
		$sql.= " s.email ASC, s.rowid ASC, cd.date_fin_validite ASC";	// Order by email to allow one message per email

		//var_dump($sql);exit;
	}
	function closeContratexpire($id_contrat,$delayduration = 0){
		global $db,$user ;
		$sql  = "SELECT c.rowid as id_contrat ";
		$sql .= " FROM  ".MAIN_DB_PREFIX."contrat AS c";
		$sql .= ", ".MAIN_DB_PREFIX."contratdet AS cd";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."product AS p ON p.rowid = cd.fk_product";
		$sql .= " WHERE  c.rowid = cd.fk_contrat AND c.statut > 0 AND cd.statut < 5";
		$sql.= " AND cd.date_fin_validite < '".$db->idate(dol_time_plus_duree($now, $delayduration, "d"))."'";
		$result=$db->query($sql);
		if ($result)
		{
			$nump = $this->db->num_rows($result);
			if ($nump)
			{
				$i = 0;
				while ($i < $nump)
				{
					$obj = $this->db->fetch_object($result);
					$contrat = new Contrat($db);
					$contrat->fetch($obj->id_contrat);
					$contrat->cloture($user);
					$i++;
				}
				$this->db->free($result);
				/* Return array */
				return $elements;
			}
		}
		else
		{
			dol_print_error($this->db);
		}

	}
	public function getContratActive($login) {
		global $db;
		$sql = "SELECT ec.rowid FROM
		".MAIN_DB_PREFIX."c_type_contact tc,
		".MAIN_DB_PREFIX."element_contact ec ,
		".MAIN_DB_PREFIX."socpeople t   ,
		".MAIN_DB_PREFIX."user u ,
		".MAIN_DB_PREFIX."contrat AS c,
		".MAIN_DB_PREFIX."contratdet AS cd LEFT JOIN ".MAIN_DB_PREFIX."product AS p ON p.rowid = cd.fk_product
		WHERE  ec.element_id= c.rowid AND c.rowid = cd.fk_contrat AND ec.fk_socpeople = t.rowid AND  u.fk_socpeople=t.rowid
		AND  ec.fk_c_type_contact=tc.rowid AND tc.element='contrat'
		AND tc.source = 'external' AND tc.code = 'ABONWEB'
		AND tc.active=1 AND u.login = '$login'
		AND 	cd.statut = 4
		ORDER BY c.rowid, t.lastname ASC ";
		$elements = array();
		//var_dump($sql);
		$result=$db->query($sql);
		if ($result)
		{
			$nump = $this->db->num_rows($result);
			if ($nump)
			{
				$i = 0;
				while ($i < $nump)
				{
					$obj = $this->db->fetch_object($result);
					$elements[$i] = $obj->rowid;
					$i++;
				}
				$this->db->free($result);
				/* Return array */
				return $elements;
			}
		}
		else
		{
			dol_print_error($this->db);
		}
		return $elements;

	}

	public function getAllContratByLogin($login) {
		global $db;
		$sql = "SELECT c.rowid FROM
		".MAIN_DB_PREFIX."c_type_contact tc,
		".MAIN_DB_PREFIX."element_contact ec ,
		".MAIN_DB_PREFIX."socpeople t   ,
		".MAIN_DB_PREFIX."user u ,
		".MAIN_DB_PREFIX."contrat AS c,
		".MAIN_DB_PREFIX."contratdet AS cd LEFT JOIN ".MAIN_DB_PREFIX."product AS p ON p.rowid = cd.fk_product
		WHERE  ec.element_id= c.rowid AND c.rowid = cd.fk_contrat AND ec.fk_socpeople = t.rowid AND  u.fk_socpeople=t.rowid
		AND  ec.fk_c_type_contact=tc.rowid AND tc.element='contrat'
		AND tc.source = 'external' AND tc.code  like 'ABON%'
		AND tc.active=1 AND u.login = '$login'
		AND 	cd.statut = 4
		ORDER BY c.rowid, t.lastname ASC ";
		$elements = array();
		//var_dump($sql);
		$result=$db->query($sql);
		if ($result)
		{
			$nump = $this->db->num_rows($result);
			if ($nump)
			{
				$i = 0;
				while ($i < $nump)
				{
					$obj = $this->db->fetch_object($result);
					$elements[$i] = $obj->rowid;
					$i++;
				}
				$this->db->free($result);
				/* Return array */
				return $elements;
			}
		}
		else
		{
			dol_print_error($this->db);
		}
		return $elements;

	}
	function update_nb_exemplaire($contratid, $contactid,$nb_link)
	{

		// On recherche id type_contact
		$sql = "SELECT tc.rowid";
		$sql.= " FROM ".MAIN_DB_PREFIX."c_type_contact as tc";
		$sql.= " WHERE tc.element='contrat'";
		$sql.= " AND tc.source='external'";
		$sql.= " AND tc.code='ABONPAPIER' AND tc.active=1";
		//print $sql;
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$obj = $this->db->fetch_object($resql);
			$id_type_contact=$obj->rowid;
		}
		// Insertion dans la base
		$sql = "UPDATE ".MAIN_DB_PREFIX."element_contact set";
		$sql.= " nb_link = ".$nb_link;
		$sql.= " where element_id = ".$contratid." AND fk_socpeople = '".$contactid ."' AND fk_c_type_contact = '".intval($id_type_contact)."'";
		//var_dump($sql);exit;
		$resql=$this->db->query($sql);
		if ($resql)
		{
			return 0;
		}
		else
		{
			$this->error=$this->db->lasterror();
			return -1;
		}
	}
	/**
	 *
	 * @param object $objcontrat
	 * @param int $type  1-> papier, 2 web
	 */
	public function IsTypeProduitWebORPaPer($objcontrat,$type) {
		global $db;
		if(is_object($objcontrat)) {
			$objcontrat->fetch_lines();
			if(isset($objcontrat->lines[0]) ) {
				$idprod = $objcontrat->lines[0]->fk_product;
				$prod = new Product($db);
				$prod->fetch($idprod);
					
				//$extrafields = new ExtraFields($db);
				//$attributsLabel = $extrafields->fetch_name_optionals_label($prod->table_element);
				$prod->fetch_optionals($idprod);
				$attributs = $prod->array_options;
				if($type ==2)
					return (isset($attributs['options_type_produit']) && ($attributs['options_type_produit']==2 || $attributs['options_type_produit']==3));
				elseif($type ==1)
				return (isset($attributs['options_type_produit']) && ($attributs['options_type_produit']==1 || $attributs['options_type_produit']==3));
			}
		}
	}



}
