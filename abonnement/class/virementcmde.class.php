<?php
/* Copyright (C) 2007-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2014	   Juanjo Menent		<jmenent@2byte.es>
 * Copyright (C) ---Put here your own copyright and developer email---
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
 *  \file       dev/skeletons/virementcmde.class.php
 *  \ingroup    mymodule othermodule1 othermodule2
 *  \brief      This file is an example for a CRUD class file (Create/Read/Update/Delete)
 *				Initialy built by build_class_from_table on 2015-09-22 00:39
 */

// Put here all includes required by your class file
require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");
//require_once(DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");
//require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");


/**
 *	Put here description of your class
 */
class Virementcmde extends CommonObject
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	var $element='virementcmde';			//!< Id that identify managed objects
	var $table_element='virement_cmde';		//!< Name of table without prefix where object is stored

    var $id;
    
	var $num_mvt;
	var $date_mvt='';
	var $montant;
	var $devise;
	var $fk_commande;
	var $communication;
	var $fk_user_author;
	var $fk_user_mod;
	var $fk_user_valid;

    


    /**
     *  Constructor
     *
     *  @param	DoliDb		$db      Database handler
     */
    function __construct($db)
    {
        $this->db = $db;
        return 1;
    }


    /**
     *  Create object into database
     *
     *  @param	User	$user        User that creates
     *  @param  int		$notrigger   0=launch triggers after, 1=disable triggers
     *  @return int      		   	 <0 if KO, Id of created object if OK
     */
    function create($user, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters
        
		if (isset($this->num_mvt)) $this->num_mvt=trim($this->num_mvt);
		if (isset($this->montant)) $this->montant=trim($this->montant);
		if (isset($this->devise)) $this->devise=trim($this->devise);
		if (isset($this->fk_commande)) $this->fk_commande=trim($this->fk_commande);
		if (isset($this->communication)) $this->communication=trim($this->communication);
		if (isset($this->fk_user_author)) $this->fk_user_author=trim($this->fk_user_author);
		if (isset($this->fk_user_mod)) $this->fk_user_mod=trim($this->fk_user_mod);
		if (isset($this->fk_user_valid)) $this->fk_user_valid=trim($this->fk_user_valid);

        

		// Check parameters
		// Put here code to add control on parameters values

        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX.$this->table_element."(";
		
		$sql.= "num_mvt,";
		$sql.= "date_mvt,";
		$sql.= "montant,";
		$sql.= "devise,";
		$sql.= "fk_commande,";
		$sql.= "communication,";
		$sql.= "fk_user_author,";
		$sql.= "fk_user_mod,";
		$sql.= "fk_user_valid";

		
        $sql.= ") VALUES (";
        
		$sql.= " ".(! isset($this->num_mvt)?'NULL':"'".$this->num_mvt."'").",";
		$sql.= " ".(! isset($this->date_mvt) || dol_strlen($this->date_mvt)==0?'NULL':"'".$this->db->idate($this->date_mvt)."'").",";
		$sql.= " ".(! isset($this->montant)?'NULL':"'".$this->montant."'").",";
		$sql.= " ".(! isset($this->devise)?'NULL':"'".$this->devise."'").",";
		$sql.= " ".(! isset($this->fk_commande)?'NULL':"'".$this->fk_commande."'").",";
		$sql.= " ".(! isset($this->communication)?'NULL':"'".$this->db->escape($this->communication)."'").",";
		$sql.= " ".(! isset($this->fk_user_author)?'NULL':"'".$this->fk_user_author."'").",";
		$sql.= " ".(! isset($this->fk_user_mod)?'NULL':"'".$this->fk_user_mod."'").",";
		$sql.= " ".(! isset($this->fk_user_valid)?'NULL':"'".$this->fk_user_valid."'")."";

        
		$sql.= ")";
		$this->db->begin();

	   	dol_syslog(__METHOD__, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX.$this->table_element);

			if (! $notrigger)
			{
	            // Uncomment this and change MYOBJECT to your own tag if you
	            // want this action calls a trigger.

	            //// Call triggers
	            //$result=$this->call_trigger('MYOBJECT_CREATE',$user);
	            //if ($result < 0) { $error++; //Do also what you must do to rollback action if trigger fail}
	            //// End call triggers
			}
        }

        // Commit or rollback
        if ($error)
		{
			foreach($this->errors as $errmsg)
			{  var_dump($errmsg);
	            dol_syslog(__METHOD__." ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
            return $this->id;
		}
    }


    /**
     *  Load object in memory from the database
     *
     *  @param	int		$id    	Id object
     *  @param	string	$ref	Ref
     *  @return int          	<0 if KO, >0 if OK
     */
    function fetch($id,$ref='')
    {
    	global $langs;
        $sql = "SELECT";
		$sql.= " t.rowid,";
		
		$sql.= " t.num_mvt,";
		$sql.= " t.date_mvt,";
		$sql.= " t.montant,";
		$sql.= " t.devise,";
		$sql.= " t.fk_commande,";
		$sql.= " t.communication,";
		$sql.= " t.fk_user_author,";
		$sql.= " t.fk_user_mod,";
		$sql.= " t.fk_user_valid";

		
        $sql.= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
        if ($ref) $sql.= " WHERE t.ref = '".$ref."'";
        else $sql.= " WHERE t.rowid = ".$id;

    	dol_syslog(get_class($this)."::fetch");
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    = $obj->rowid;
                
				$this->num_mvt = $obj->num_mvt;
				$this->date_mvt = $this->db->jdate($obj->date_mvt);
				$this->montant = $obj->montant;
				$this->devise = $obj->devise;
				$this->fk_commande = $obj->fk_commande;
				$this->communication = $obj->communication;
				$this->fk_user_author = $obj->fk_user_author;
				$this->fk_user_mod = $obj->fk_user_mod;
				$this->fk_user_valid = $obj->fk_user_valid;

                
            }
            $this->db->free($resql);

            return 1;
        }
        else
        {
      	    $this->error="Error ".$this->db->lasterror();
            return -1;
        }
    }


    /**
     *  Update object into database
     *
     *  @param	User	$user        User that modifies
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
     *  @return int     		   	 <0 if KO, >0 if OK
     */
    function update($user, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters
        
		if (isset($this->num_mvt)) $this->num_mvt=trim($this->num_mvt);
		if (isset($this->montant)) $this->montant=trim($this->montant);
		if (isset($this->devise)) $this->devise=trim($this->devise);
		if (isset($this->fk_commande)) $this->fk_commande=trim($this->fk_commande);
		if (isset($this->communication)) $this->communication=trim($this->communication);
		if (isset($this->fk_user_author)) $this->fk_user_author=trim($this->fk_user_author);
		if (isset($this->fk_user_mod)) $this->fk_user_mod=trim($this->fk_user_mod);
		if (isset($this->fk_user_valid)) $this->fk_user_valid=trim($this->fk_user_valid);

        

		// Check parameters
		// Put here code to add a control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element." SET";
        
		$sql.= " num_mvt=".(isset($this->num_mvt)?$this->num_mvt:"null").",";
		$sql.= " date_mvt=".(dol_strlen($this->date_mvt)!=0 ? "'".$this->db->idate($this->date_mvt)."'" : 'null').",";
		$sql.= " montant=".(isset($this->montant)?$this->montant:"null").",";
		$sql.= " devise=".(isset($this->devise)?$this->devise:"null").",";
		$sql.= " fk_commande=".(isset($this->fk_commande)?$this->fk_commande:"null").",";
		$sql.= " communication=".(isset($this->communication)?"'".$this->db->escape($this->communication)."'":"null").",";
		$sql.= " fk_user_author=".(isset($this->fk_user_author)?$this->fk_user_author:"null").",";
		$sql.= " fk_user_mod=".(isset($this->fk_user_mod)?$this->fk_user_mod:"null").",";
		$sql.= " fk_user_valid=".(isset($this->fk_user_valid)?$this->fk_user_valid:"null")."";

        
        $sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(__METHOD__);
        $resql = $this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
		{
			if (! $notrigger)
			{
	            // Uncomment this and change MYOBJECT to your own tag if you
	            // want this action calls a trigger.

	            //// Call triggers
	            //$result=$this->call_trigger('MYOBJECT_MODIFY',$user);
	            //if ($result < 0) { $error++; //Do also what you must do to rollback action if trigger fail}
	            //// End call triggers
			 }
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(__METHOD__." ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
    }


 	/**
	 *  Delete object in database
	 *
     *	@param  User	$user        User that deletes
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
	 *  @return	int					 <0 if KO, >0 if OK
	 */
	function delete($user, $notrigger=0)
	{
		global $conf, $langs;
		$error=0;

		$this->db->begin();

		if (! $error)
		{
			if (! $notrigger)
			{
				// Uncomment this and change MYOBJECT to your own tag if you
		        // want this action calls a trigger.

	            //// Call triggers
	            //$result=$this->call_trigger('MYOBJECT_DELETE',$user);
	            //if ($result < 0) { $error++; //Do also what you must do to rollback action if trigger fail}
	            //// End call triggers
			}
		}

		if (! $error)
		{
    		$sql = "DELETE FROM ".MAIN_DB_PREFIX.$this->table_element;
    		$sql.= " WHERE rowid=".$this->id;

    		dol_syslog(__METHOD__);
    		$resql = $this->db->query($sql);
        	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(__METHOD__." ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}



	/**
	 *	Load an object from its id and create a new one in database
	 *
	 *	@param	int		$fromid     Id of object to clone
	 * 	@return	int					New id of clone
	 */
	function createFromClone($fromid)
	{
		global $user,$langs;

		$error=0;

		$object=new Virementcmde($this->db);

		$this->db->begin();

		// Load source object
		$object->fetch($fromid);
		$object->id=0;
		$object->statut=0;

		// Clear fields
		// ...

		// Create clone
		$result=$object->create($user);

		// Other options
		if ($result < 0)
		{
			$this->error=$object->error;
			$error++;
		}

		if (! $error)
		{


		}

		// End
		if (! $error)
		{
			$this->db->commit();
			return $object->id;
		}
		else
		{
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 *	Initialise object with example values
	 *	Id must be 0 if object instance is a specimen
	 *
	 *	@return	void
	 */
	function initAsSpecimen()
	{
		$this->id=0;
		
		$this->num_mvt='';
		$this->date_mvt='';
		$this->montant='';
		$this->devise='';
		$this->fk_commande='';
		$this->communication='';
		$this->fk_user_author='';
		$this->fk_user_mod='';
		$this->fk_user_valid='';

		
	}
	
	
	function isExiste($comm_structure,$num_mvt,$date_mvt_timestamp) {
		$sql =" SELECT rowid ";
		$sql.= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
		$sql.= " WHERE communication = '".$comm_structure."'";
		$sql.= "  AND num_mvt = '".$num_mvt."'";
		
		$sql.= " AND date_mvt = '".$this->db->idate($date_mvt_timestamp)."'";
		$resql=$this->db->query($sql);
		
		if ($resql)
		{
			/* if ($this->db->num_rows($resql))
			{
				
			}
			$this->db->free($resql);
			
			return 1; */
			$nbre = $this->db->num_rows($resql);
			$this->db->free($resql);
			return $nbre;
		}
		
		else
		{
			$this->error="Error ".$this->db->lasterror();
			//var_dump(($this->error));exit;
			return 1;
		}
		
		
		
		
	}

}
