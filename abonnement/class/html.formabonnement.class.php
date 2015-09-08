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
 * \file
 * \ingroup
 * \brief File of class with all html predefined components
 */
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

class FormAbonnement extends Form
{

	var $db;

	var $error;

	var $num;

	/**
	 *  Return a select list with types of contacts
	 *
	 *  @param  string		$selected       Default selected value
	 *  @param  string		$htmlname		HTML select name
	 *  @param  string		$source			Source ('internal' or 'external')
	 *  @param  string		$sortorder		Sort criteria
	 *  @param  int			$showempty      1=Add en empty line
	 *  @return	void
	 */
	function selectTypeContact( $htmlname = 'type_contact',$selected='', $sortorder='code', $showempty=1)
	{
		global  $db;
		$out = '';
		$lesTypes = $this->type_contact_abonnement();
		$out .= '<select class="flat" name="'.$htmlname.'" id="'.$htmlname.'">';
		if ($showempty) $out .= '<option value="0"></option>';
		foreach($lesTypes as $key=>$value)
		{
			$out .= '<option value="'.$key.'"';
			if ($key == $selected) $out .= ' selected';
			$out .= '>'.$value.'</option>';
		}
		$out .= "</select>\n";
		return $out;

	}
	function makeAbonWeb($htmlname = 'type_contact') {
		$lesTypes = $this->type_contact_abonnement(0,'ABONWEB');
		foreach($lesTypes as $key=>$value)
		{
			$out .= '<input type="hidden" name="type_contact" value="'.$key.'">';
			$out .='<b>'.$value.'</b>';

		}
		return $out;
	}
	function getArrAbonneWeb($id_contrat)
	{
		global $langs;
	
		$tab=array();
	
		$sql = "SELECT ec.rowid, ec.statut, ec.fk_socpeople as id, ec.fk_c_type_contact";    // This field contains id of llx_socpeople or id of llx_user
		$sql.=", t.fk_soc as socid";
		$sql.= ", t.civility as civility, t.lastname as lastname, t.firstname, t.email";
		$sql.= ", tc.source, tc.element, tc.code, tc.libelle";
		$sql.= " FROM ".MAIN_DB_PREFIX."c_type_contact tc";
		$sql.= ", ".MAIN_DB_PREFIX."element_contact ec";
		$sql.=" LEFT JOIN ".MAIN_DB_PREFIX."socpeople t on ec.fk_socpeople = t.rowid";
		$sql.= " WHERE ec.element_id =".$id_contrat;
		$sql.= " AND ec.fk_c_type_contact=tc.rowid";
		$sql.= " AND tc.element='contrat'";
		$sql.= " AND tc.source = 'external'";
		//$sql.= " AND tc.code = 'ABONWEB'";
		$sql.= " AND ec.fk_c_type_contact = '6000022'";
		$sql.= " AND tc.active=1";
		//if ($statut >= 0) $sql.= " AND ec.statut = '".$statut."'";
		$sql.=" ORDER BY t.lastname ASC";
	
		dol_syslog(get_class($this)."::liste_contact", LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$num=$this->db->num_rows($resql);
			$i=0;
			while ($i < $num)
			{
				$obj = $this->db->fetch_object($resql);
	
				if (! $list)
				{
					$transkey="TypeContact_".$obj->element."_".$obj->source."_".$obj->code;
					$libelle_type=($langs->trans($transkey)!=$transkey ? $langs->trans($transkey) : $obj->libelle);
					$tab[$i]=array('source'=>$obj->source,'socid'=>$obj->socid,'id'=>$obj->id,
							'nom'=>$obj->lastname,      // For backward compatibility
							'civility'=>$obj->civility, 'lastname'=>$obj->lastname, 'firstname'=>$obj->firstname, 'email'=>$obj->email,
							'rowid'=>$obj->rowid,'code'=>$obj->code,'libelle'=>$libelle_type,'status'=>$obj->statut, 'fk_c_type_contact' => $obj->fk_c_type_contact);
				}
				else
				{
					$tab[$i]=$obj->id;
				}
	
				$i++;
			}
	
			return $tab;
		}
		else
		{
			$this->error=$this->db->error();
			dol_print_error($this->db);
			return -1;
		}
	}
	function type_contact_abonnement( $activeonly=0, $code='')
	{
		global $langs,$db;
		$this->db = $db;
		if (empty($order)) $order='code';

		$tab = array();
		$sql = "SELECT DISTINCT tc.rowid, tc.code, tc.libelle";
		$sql.= " FROM ".MAIN_DB_PREFIX."c_type_contact as tc";
		$sql.= " WHERE tc.element='contrat'";
		$sql.= " AND tc.source='external'";
		$sql.= " AND tc.code like'ABON%'";
		if ($activeonly == 1) $sql.= " AND tc.active=1"; // only the active type
		if (! empty($code)) $sql.= " AND tc.code='".$code."'";


		$sql.= " ORDER by tc.".$order;

		//print "sql=".$sql;
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$num=$this->db->num_rows($resql);
			$i=0;
			while ($i < $num)
			{
				$obj = $this->db->fetch_object($resql);

				$transkey="TypeContact_".$this->element."_".$source."_".$obj->code;
				$libelle_type=($langs->trans($transkey)!=$transkey ? $langs->trans($transkey) : $obj->libelle);
				$tab[$obj->rowid]=$libelle_type;
				$i++;
			}
			return $tab;
		}
		else
		{
			$this->error=$this->db->lasterror();
			//dol_print_error($this->db);
			return null;
		}
	}

	function envoiEmailUser($userSend,$password,$objet=null,$file=null) {
		global $user,$langs,$db;
		require_once(DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php');
		include_once DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php';
		$formmail = new FormMail($db);
		$msgishtml=0;
		// Get message template
		$arraydefaultmessage=$this->getEMailTemplate($db, 'user_abonne_create', $user, $langs);
		$mesg =$arraydefaultmessage ['content'];
		$mesg = make_substitutions($mesg, $this->SubTemplateUser($userSend,$password));
		$subject  =$arraydefaultmessage ['topic'];
		$from = ($conf->notification->email_from)?$conf->notification->email_from:$user->email;
		$mailfile = new CMailFile(
				$subject,
				$userSend->email,
				$from,
				$mesg,
				array(),
				array(),
				array(),
				'',
				'',
				0,
				$msgishtml
		);
			
		if ($mailfile->sendfile())
		{
			return 1;
		}
		else
		{
			$langs->trans("errors");
			$this->error=$langs->trans("ErrorFailedToSendPassword").' '.$mailfile->error;
			return -1;
		}
	}
	/**
	 *      Return template of email
	 *      Search into table c_email_templates
	 *
	 * 		@param	DoliDB		$db				Database handler
	 * 		@param	string		$type_template	Get message for key module
	 *      @param	string		$user			Use template public or limited to this user
	 *      @param	Translate	$outputlangs	Output lang object
	 *      @return array						array('topic'=>,'content'=>,..)
	 */
	public function getEMailTemplate($db, $type_template, $user, $outputlangs)
	{
		global $db;
		$ret=array();

		$sql = "SELECT label, topic, content, lang";
		$sql.= " FROM ".MAIN_DB_PREFIX.'c_email_templates';
		$sql.= " WHERE type_template='".$db->escape($type_template)."'";
		$sql.= " AND entity IN (".getEntity("c_email_templates").")";
		$sql.= " AND (fk_user is NULL or fk_user = 0 or fk_user = ".$user->id.")";
		if (is_object($outputlangs)) $sql.= " AND (lang = '".$outputlangs->defaultlang."' OR lang IS NULL OR lang = '')";
		$sql.= $db->order("lang,label","ASC");
			

		$resql = $db->query($sql);
		if ($resql)
		{
			$obj = $db->fetch_object($resql);	// Get first found
			if ($obj)
			{
				$ret['label']=$obj->label;
				$ret['topic']=$obj->topic;
				$ret['content']=$obj->content;
				$ret['lang']=$obj->lang;
			}
			else
			{
				$defaultmessage='';
				if     ($type_template=='facture_send')	            {
					$defaultmessage=$outputlangs->transnoentities("PredefinedMailContentSendInvoice");
				}
				elseif ($type_template=='facture_relance')			{
					$defaultmessage=$outputlangs->transnoentities("PredefinedMailContentSendInvoiceReminder");
				}
				elseif ($type_template=='propal_send')				{
					$defaultmessage=$outputlangs->transnoentities("PredefinedMailContentSendProposal");
				}
				elseif ($type_template=='order_send')				{
					$defaultmessage=$outputlangs->transnoentities("PredefinedMailContentSendOrder");
				}
				elseif ($type_template=='order_supplier_send')		{
					$defaultmessage=$outputlangs->transnoentities("PredefinedMailContentSendSupplierOrder");
				}
				elseif ($type_template=='invoice_supplier_send')	{
					$defaultmessage=$outputlangs->transnoentities("PredefinedMailContentSendSupplierInvoice");
				}
				elseif ($type_template=='shipping_send')			{
					$defaultmessage=$outputlangs->transnoentities("PredefinedMailContentSendShipping");
				}
				elseif ($type_template=='fichinter_send')			{
					$defaultmessage=$outputlangs->transnoentities("PredefinedMailContentSendFichInter");
				}
				elseif ($type_template=='thirdparty')				{
					$defaultmessage=$outputlangs->transnoentities("PredefinedMailContentThirdparty");
				}
					
				$ret['label']='default';
				$ret['topic']='';
				$ret['content']=$defaultmessage;
				$ret['lang']=$outputlangs->defaultlang;
			}


			$db->free($resql);
			return $ret;
		}
		else
		{
			dol_print_error($db);
			return -1;
		}
	}
	public function SubTemplateUser($userSub,$password) {
		$arr= array();
		if(is_object($userSub))
			$arr = array(
					'__LOGIN__' => $userSub->login,
					'__EMAIL__' => $userSub->email,
					'__PASSWORD__' => $password,
					'__URL__' => $urlwithroot
			);
		return $arr;
	}

	public function genereDocument($object) {
		global $langs;
		$outputlangs = $langs;
		$newlang = Null;
		//$newlang = GETPOST('lang_id', 'alpha');
		if ($conf->global->MAIN_MULTILANGS && empty($newlang))
			$newlang = $object->thirdparty->default_lang;
		if (! empty($newlang)) {
			$outputlangs = new Translate("", $conf);
			$outputlangs->setDefaultLang($newlang);
		}
		if(is_object($object)) {

			$ret = $object->fetch($object->id); // Reload to get new records
			$object->generateDocument($object->modelpdf, $outputlangs, $hidedetails, $hidedesc, $hideref);
		}
			
	}
	function envoiEmailCommande($object,$password,$fuser=null) {
		global $user,$langs,$conf,$db;
		require_once(DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php');
		include_once DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php';
		$formmail = new FormMail($db);
		$msgishtml=0;
		$ref = dol_sanitizeFileName($object->ref);
		include_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
		$fileparams = dol_most_recent_file($conf->commande->dir_output . '/' . $ref, preg_quote($ref, '/'));
		$file = $fileparams ['fullname'];
       if(is_null($fuser)) $fuser = $user;
		// Define output language
		$outputlangs = $langs;
		$newlang = '';
		if ($conf->global->MAIN_MULTILANGS && empty($newlang) && ! empty($_REQUEST['lang_id']))
			$newlang = $_REQUEST['lang_id'];
		if ($conf->global->MAIN_MULTILANGS && empty($newlang))
			$newlang = $object->thirdparty->default_lang;

		if (!empty($newlang))
		{
			$outputlangs = new Translate('', $conf);
			$outputlangs->setDefaultLang($newlang);
			$outputlangs->load('commercial');
		}
		///
		//var_dump($file);exit;
		$mime = 'application/pdf';
		if (dol_is_file($file))
		{
			$object->fetch_thirdparty();
			$sendto = $object->thirdparty->email;
			//
			// 			$liste = array();
			// 			foreach ($object->thirdparty->thirdparty_and_contact_email_array(1) as $key => $value)
				// 				$liste [$key] = $value;

			if (dol_strlen($sendto))
			{
				$langs->load("commercial");
				$from = $fuser->getFullName($langs) . ' <' . $fuser->email .'>';
				$replyto = $from;
					
				$sendtobcc = $conf->global->MAIN_EMAIL_USECCC;
				if (empty($object->ref_client)) {
					$topic = $outputlangs->trans('SendOrderRef', '__ORDERREF__');
				} else if (! empty($object->ref_client)) {
					$topic  = $outputlangs->trans('SendOrderRef', '__ORDERREF__ (__REFCLIENT__)');
				}
				// Get message template
				$arraydefaultmessage=$this->getEMailTemplate($db, 'abon_commande', $fuser, $langs);
				$mesg =$arraydefaultmessage ['content'];
				$substit ['__ORDERREF__'] = $object->ref;
				$substit ['__SIGNATURE__'] = $fuser->signature;
				$substit ['__REFCLIENT__'] = $object->ref_client;
				$substit ['__THIRPARTY_NAME__'] = $object->thirdparty->name;
				$substit ['__CONTACTCIVNAME__'] = $object->thirdparty->name;
				$substit ['__PERSONALIZED__'] = $object->thirdparty->name;

				$mesg = make_substitutions($mesg, $substit);
				$subject  =$arraydefaultmessage ['topic'];
				$topic = make_substitutions($topic, $substit);
				// Create form object
				$attachedfiles=array('paths'=>array($file), 'names'=>array($filename), 'mimes'=>array($mime));
				$filepath = $attachedfiles['paths'];
				$filename = $attachedfiles['names'];
				$mimetype = $attachedfiles['mimes'];

				//$from = ($conf->notification->email_from)?$conf->notification->email_from:$fuser->email;
				$mailfile = new CMailFile(
						$topic,
						$sendto,
						$from,
						$mesg,
						$filepath,
						$mimetype,
						$filename,
						$sendtocc,
						$sendtobcc,
						$deliveryreceipt,
						-1);





					
				if ($mailfile->sendfile())
				{
					return 1;
				}
				else
				{
					$langs->trans("errors");
					$this->error=$langs->trans("ErrorFailedToSendPassword").' '.$mailfile->error.' send to '.$sendto.' from '.$from;
					return -1;
				}
			} else
			{
				$langs->trans("errors");
				$this->error=$langs->trans("ErrorFailedToSendEmail").'Email non envoyé ';
				return -1;
			}
		}
	}
	
	function envoiEmailFacture($object,$login,$password,$fuser=null) {
		global $user,$langs,$conf,$db;
		if(!is_object($object)) return -1;
		require_once(DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php');
		include_once DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php';
		$formmail = new FormMail($db);
		$msgishtml=0;
		$ref = dol_sanitizeFileName($object->ref);
		include_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
		$object->generateDocument($object->modelpdf);
		$fileparams = dol_most_recent_file($conf->facture->dir_output . '/' . $ref, preg_quote($ref, '/'));
		$file = $fileparams ['fullname'];
		//var_dump($conf->facture->dir_output . '/' . $ref);
		if(is_null($fuser)) $fuser = $user;
		// Define output language
		$outputlangs = $langs;
		$newlang = '';
		if ($conf->global->MAIN_MULTILANGS && empty($newlang) && ! empty($_REQUEST['lang_id']))
			$newlang = $_REQUEST['lang_id'];
		if ($conf->global->MAIN_MULTILANGS && empty($newlang))
			$newlang = $object->thirdparty->default_lang;
	
		if (!empty($newlang))
		{
			$outputlangs = new Translate('', $conf);
			$outputlangs->setDefaultLang($newlang);
			$outputlangs->load('commercial');
		}
		///
		//var_dump($file);exit;
		$mime = 'application/pdf';
		if (dol_is_file($file))
		{
			$object->fetch_thirdparty();
			$sendto = $object->thirdparty->email;
			//
			// 			$liste = array();
			// 			foreach ($object->thirdparty->thirdparty_and_contact_email_array(1) as $key => $value)
			// 				$liste [$key] = $value;
			//var_dump($sendto);exit;
			if (dol_strlen($sendto))
			{
				$langs->load("commercial");
				$from = $fuser->getFullName($langs) . ' <' . $fuser->email .'>';
				$replyto = $from;
				
				$sendtobcc = $conf->global->MAIN_EMAIL_USECCC;
			if (empty($object->ref_client)) {
			$topic = $outputlangs->transnoentities($topicmail, '__FACREF__');
		    } else if (! empty($object->ref_client)) {
			$topic = $outputlangs->transnoentities($topicmail, '__FACREF__ (__REFCLIENT__)');
		    }
				// Get message template
				$arraydefaultmessage=$this->getEMailTemplate($db, 'confirme_abon', $fuser, $langs);
				$mesg =$arraydefaultmessage ['content'];
			
				$substit['__FACREF__'] = $object->ref;
				$substit['__SIGNATURE__'] = $fuser->signature;
				$substit['__REFCLIENT__'] = $object->ref_client;
				$substit['__THIRPARTY_NAME__'] = $object->thirdparty->name;
				$substit['__PROJECT_REF__'] = (is_object($object->projet)?$object->projet->ref:'');
				$substit['__PERSONALIZED__'] = '';
				$substit['__CONTACTCIVNAME__'] = '';
				$substit['__LOGIN__'] = $login;
				$substit['__PASSWORD__'] = $password;
				$substit['__URL__'] = DOL_MAIN_URL_ROOT;
				
				
	
				$mesg = make_substitutions($mesg, $substit);
				$subject  =$arraydefaultmessage ['topic'];
				$topic = make_substitutions($subject, $substit);
				// Create form object
				$attachedfiles=array('paths'=>array($file), 'names'=>array($filename), 'mimes'=>array($mime));
				$filepath = $attachedfiles['paths'];
				$filename = $attachedfiles['names'];
				$mimetype = $attachedfiles['mimes'];
				
				//$from = ($conf->notification->email_from)?$conf->notification->email_from:$fuser->email;
				$mailfile = new CMailFile(
						$topic,
						$sendto,
						$from,
						$mesg,
						$filepath,
						$mimetype,
						$filename,
						$sendtocc,
						$sendtobcc,
						$deliveryreceipt,
						-1);
	//var_dump($sendto);
				if ($mailfile->sendfile())
				{
					return 1;
				}
				else
				{
					$langs->trans("errors");
					$this->error=$langs->trans("ErrorFailedToSendPassword").' '.$mailfile->error.' send to '.$sendto.' from '.$from;
					return -1;
				}
			} else
			{
				$langs->trans("errors");
				$this->error=$langs->trans("ErrorFailedToSendEmail").'Email non envoyé ';
				return -1;
			}
		}
	}
	/**
	 *  Return a HTML select list of bank accounts
	 *
	 *  @param	string	$selected          Id account pre-selected
	 *  @param  string	$htmlname          Name of select zone
	 *  @param  int		$statut            Status of searched accounts (0=open, 1=closed, 2=both)
	 *  @param  string	$filtre            To filter list
	 *  @param  int		$useempty          1=Add an empty value in list, 2=Add an empty value in list only if there is more than 2 entries.
	 *  @param  string	$moreattrib        To add more attribute on select
	 * 	@return	void
	 */
	function select_comptes($selected='',$htmlname='accountid',$statut=0,$filtre='',$useempty=0,$moreattrib='')
	{
		global $langs, $conf;

		$langs->load("admin");

		$sql = "SELECT rowid, label, bank, clos as status";
		$sql.= " FROM ".MAIN_DB_PREFIX."bank_account";
		$sql.= " WHERE entity IN (".getEntity('bank_account', 1).")";
		if ($statut != 2) $sql.= " AND clos = '".$statut."'";
		if ($filtre) $sql.=" AND ".$filtre;
		$sql.= " ORDER BY label";
		$out = '';
		dol_syslog(get_class($this)."::select_comptes", LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result)
		{
			$num = $this->db->num_rows($result);
			$i = 0;
			if ($num)
			{
				$out .=  '<select id="select'.$htmlname.'" class="flat selectbankaccount" name="'.$htmlname.'"'.($moreattrib?' '.$moreattrib:'').'>';
				if ($useempty == 1 || ($useempty == 2 && $num > 1))
				{
					$out .= '<option value="-1">&nbsp;</option>';
				}

				while ($i < $num)
				{
					$obj = $this->db->fetch_object($result);
					if ($selected == $obj->rowid)
					{
						$out .= '<option value="'.$obj->rowid.'" selected="selected">';
					}
					else
					{
						$out .= '<option value="'.$obj->rowid.'">';
					}
					$out .= $obj->label;
					if ($statut == 2 && $obj->status == 1) $out .= ' ('.$langs->trans("Closed").')';
					$out .= '</option>';
					$i++;
				}
				$out .= "</select>";

			}
			else
			{
				$out .= $langs->trans("NoActiveBankAccountDefined");
			}
		}
		else {
			dol_print_error($this->db);
		}
		return $out;
	}
}