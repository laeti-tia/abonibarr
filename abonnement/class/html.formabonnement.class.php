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

    function envoiEmailUser($userSend,$password) {
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
}