<?php


class ContratPDFMasse {
	public $resultmasssend;
	public $nbignored=0;
	public $nbsent=0;
	public $subject = "";
	public $message = "";
	public $sendtocc = "";
	function substitutionarray($contrat ) {
		$contratdt = $contrat->lines[0];
	 return 	$substitutionarray=array(
				'__ID__' => $contrat->id,
				'__EMAIL__' => $contrat->thirdparty->email,
				//'__CHECK_READ__' => '<img src="'.DOL_MAIN_URL_ROOT.'/public/emailing/mailing-read.php?tag='.$obj2->tag.'&securitykey='.urlencode($conf->global->MAILING_EMAIL_UNSUBSCRIBE_KEY).'" width="1" height="1" style="width:1px;height:1px" border="0"/>',
				'__LASTNAME__' => $contrat->thirdparty->lastname,
				'__FIRSTNAME__' => $contrat->thirdparty->firstname,
	            '__THIRPARTY_NAME__'=> $contrat->thirdparty->name,
				'__REF__' => $contrat->ref,
				'__REFCLIENT__' => $contrat->thirdparty->name,
				'__FACREF__'=>$contrat->ref,
	 		    '__DATE_FIN_VALIDER'=>is_object($contratdt)?$contratdt->date_fin_validite:''
		);
	}
	function generePDF($dataSend,$message,$subject,$sendtocc ,$db) {
		global $conf,$langs,$mysoc,$user;
		$countToSend = count($dataSend);
		$this->subject = $subject ; // GETPOST('subject');
		$this->message = $message ;// GETPOST('message');
		$this->sendtocc =$sendtocc ; //GETPOST('sentocc');

		for ($i = 0; $i < $countToSend; $i++)
		{
			$contrat = new Contrat($db);
			$result = $contrat->fetch($dataSend[$i]);
			//var_dump($contrat->statut);exit;
			if ($result > 0)	// Invoice was found
			{
				//if ($object->statut != 1)
				//{
				//	continue; // Payment done or started or canceled
				//}
				// création de la facture
				$abonnement = new Abonnement($db);
				$object = $abonnement->reabonnement($contrat);
				
				if(is_object($object)) {
						

					// Read document
					// TODO Use future field $object->fullpathdoc to know where is stored default file
					// TODO If not defined, use $object->modelpdf (or defaut invoice config) to know what is template to use to regenerate doc.
					$filename=dol_sanitizeFileName($object->ref).'.pdf';
					$filedir=$conf->commande->dir_output . '/' . dol_sanitizeFileName($object->ref);
					$file = $filedir . '/' . $filename;
					$mime = 'application/pdf';
					//var_dump(dol_is_file($file));exit;
					if (dol_is_file($file))
					{
						$object->fetch_thirdparty();
						$sendto = $object->thirdparty->email;
						//var_dump($sendto);exit;
						if (empty($sendto)) $this->nbignored++;
							
						if (dol_strlen($sendto))
						{
							$langs->load("commercial");
							$from = $user->getFullName($langs) . ' <' . $user->email .'>';
							$replyto = $from;

							$sendtobcc = (empty($conf->global->MAIN_MAIL_AUTOCOPY_INVOICE_TO)?'':$conf->global->MAIN_MAIL_AUTOCOPY_INVOICE_TO);

							$this->message=make_substitutions($this->message, $this->substitutionarray($object));
							$this->subject=make_substitutions($this->subject,  $this->substitutionarray($object));

							$actiontypecode='AC_FAC';
							$actionmsg=$langs->transnoentities('MailSentBy').' '.$from.' '.$langs->transnoentities('To').' '.$sendto;
							if ($this->message)
							{
								if ($sendtocc) $actionmsg = dol_concatdesc($actionmsg, $langs->transnoentities('Bcc') . ": " . $sendtocc);
								$actionmsg = dol_concatdesc($actionmsg, $langs->transnoentities('MailTopic') . ": " . $this->subject);
								$actionmsg = dol_concatdesc($actionmsg, $langs->transnoentities('TextUsedInTheMessageBody') . ":");
								$actionmsg = dol_concatdesc($actionmsg, $this->message);
							}

							// Create form object
							$attachedfiles=array('paths'=>array($file), 'names'=>array($filename), 'mimes'=>array($mime));
							$filepath = $attachedfiles['paths'];
							$filename = $attachedfiles['names'];
							$mimetype = $attachedfiles['mimes'];

							// Send mail
							require_once(DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php');
							$mailfile = new CMailFile($this->subject,$sendto,$from,$this->message,$filepath,$mimetype,$filename,$sendtocc,$sendtobcc,$deliveryreceipt,-1);
							if ($mailfile->error)
							{
								$this->resultmasssend.='<div class="error">'.$mailfile->error.'</div>';
							}
							else
							{
								$result=$mailfile->sendfile();
								if ($result)
								{
									$this->resultmasssend.=$langs->trans('MailSuccessfulySent',$mailfile->getValidAddress($from,2),$mailfile->getValidAddress($sendto,2));		// Must not contain "

									$error=0;

									// Initialisation donnees
									$object->sendtoid		= 0;
									$object->actiontypecode	= $actiontypecode;
									$object->actionmsg		= $actionmsg;  // Long text
									$object->actionmsg2		= $actionmsg2; // Short text
									$object->fk_element		= $object->id;
									$object->elementtype	= $object->element;

									// Appel des triggers
									include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
									$interface=new Interfaces($db);
									$result=$interface->run_triggers('BILL_SENTBYMAIL',$object,$user,$langs,$conf);
									if ($result < 0) {
										$error++; $this->errors=$interface->errors;
									}
									// Fin appel triggers

									if (! $error)
									{
										$this->resultmasssend.=$langs->trans("MailSent").': '.$sendto."<br>\n";
									}
									else
									{
										dol_print_error($db);
									}
									$this->nbsent++;

								}
								else
								{
									$langs->load("other");
									if ($mailfile->error)
									{
										$this->resultmasssend.=$langs->trans('ErrorFailedToSendMail',$from,$sendto);
										$this->resultmasssend.='<br><div class="error">'.$mailfile->error.'</div>';
									}
									else
									{
										$this->resultmasssend.='<div class="warning">No mail sent. Feature is disabled by option MAIN_DISABLE_ALL_MAILS</div>';
									}
								}
							}
						}
					}
					else
					{
						$this->nbignored++;
						$langs->load("other");
						$this->resultmasssend.='<div class="error">'.$langs->trans('ErrorCantReadFile',$file).'</div>';
						dol_syslog('Failed to read file: '.$file);
						break ;
					}
				}//
			}

		}
	}
}