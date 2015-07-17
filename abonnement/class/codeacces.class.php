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
 *  \file       dev/skeletons/codeacces.class.php
 *  \ingroup    mymodule othermodule1 othermodule2
 *  \brief      This file is an example for a CRUD class file (Create/Read/Update/Delete)
 *				Initialy built by build_class_from_table on 2015-06-24 01:20
 */

// Put here all includes required by your class file
require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");
//require_once(DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");
//require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");


/**
 *	Put here description of your class
 */
class Codeacces extends CommonObject
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	var $element='codeacces';			//!< Id that identify managed objects
	var $table_element='code_acces';		//!< Name of table without prefix where object is stored

    var $id;
    
	var $entity;
	var $ref_ext;
	var $lastname;
	var $firstname;
	var $login;
	var $pass;
	var $fk_soc;
	var $note;
	var $datevalid='';
	var $datec='';
	var $tms='';
	var $fk_user_author;
	var $fk_user_mod;
	var $fk_user_valid;
	var $canvas;
	var $import_key;

    


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
        
		if (isset($this->entity)) $this->entity=trim($this->entity);
		if (isset($this->ref_ext)) $this->ref_ext=trim($this->ref_ext);
		if (isset($this->lastname)) $this->lastname=trim($this->lastname);
		if (isset($this->firstname)) $this->firstname=trim($this->firstname);
		if (isset($this->login)) $this->login=trim($this->login);
		if (isset($this->pass)) $this->pass=trim($this->pass);
		if (isset($this->fk_soc)) $this->fk_soc=trim($this->fk_soc);
		if (isset($this->note)) $this->note=trim($this->note);
		if (isset($this->fk_user_author)) $this->fk_user_author=trim($this->fk_user_author);
		if (isset($this->fk_user_mod)) $this->fk_user_mod=trim($this->fk_user_mod);
		if (isset($this->fk_user_valid)) $this->fk_user_valid=trim($this->fk_user_valid);
		if (isset($this->canvas)) $this->canvas=trim($this->canvas);
		if (isset($this->import_key)) $this->import_key=trim($this->import_key);

        

		// Check parameters
		// Put here code to add control on parameters values

        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX.$this->table_element."(";
		
		$sql.= "entity,";
		$sql.= "ref_ext,";
		$sql.= "lastname,";
		$sql.= "firstname,";
		$sql.= "login,";
		$sql.= "pass,";
		$sql.= "fk_soc,";
		$sql.= "note,";
		$sql.= "datevalid,";
		$sql.= "datec,";
		$sql.= "fk_user_author,";
		$sql.= "fk_user_mod,";
		$sql.= "fk_user_valid,";
		$sql.= "canvas,";
		$sql.= "import_key";

		
        $sql.= ") VALUES (";
        
		$sql.= " ".(! isset($this->entity)?'NULL':"'".$this->entity."'").",";
		$sql.= " ".(! isset($this->ref_ext)?'NULL':"'".$this->db->escape($this->ref_ext)."'").",";
		$sql.= " ".(! isset($this->lastname)?'NULL':"'".$this->db->escape($this->lastname)."'").",";
		$sql.= " ".(! isset($this->firstname)?'NULL':"'".$this->db->escape($this->firstname)."'").",";
		$sql.= " ".(! isset($this->login)?'NULL':"'".$this->db->escape($this->login)."'").",";
		$sql.= " ".(! isset($this->pass)?'NULL':"'".$this->db->escape($this->pass)."'").",";
		$sql.= " ".(! isset($this->fk_soc)?'NULL':"'".$this->fk_soc."'").",";
		$sql.= " ".(! isset($this->note)?'NULL':"'".$this->db->escape($this->note)."'").",";
		$sql.= " ".(! isset($this->datevalid) || dol_strlen($this->datevalid)==0?'NULL':"'".$this->db->idate($this->datevalid)."'").",";
		$sql.= " ".(! isset($this->datec) || dol_strlen($this->datec)==0?'NULL':"'".$this->db->idate($this->datec)."'").",";
		$sql.= " ".(! isset($this->fk_user_author)?'NULL':"'".$this->fk_user_author."'").",";
		$sql.= " ".(! isset($this->fk_user_mod)?'NULL':"'".$this->fk_user_mod."'").",";
		$sql.= " ".(! isset($this->fk_user_valid)?'NULL':"'".$this->fk_user_valid."'").",";
		$sql.= " ".(! isset($this->canvas)?'NULL':"'".$this->db->escape($this->canvas)."'").",";
		$sql.= " ".(! isset($this->import_key)?'NULL':"'".$this->db->escape($this->import_key)."'")."";

        
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
		
		$sql.= " t.entity,";
		$sql.= " t.ref_ext,";
		$sql.= " t.lastname,";
		$sql.= " t.firstname,";
		$sql.= " t.login,";
		$sql.= " t.pass,";
		$sql.= " t.fk_soc,";
		$sql.= " t.note,";
		$sql.= " t.datevalid,";
		$sql.= " t.datec,";
		$sql.= " t.tms,";
		$sql.= " t.fk_user_author,";
		$sql.= " t.fk_user_mod,";
		$sql.= " t.fk_user_valid,";
		$sql.= " t.canvas,";
		$sql.= " t.import_key";

		
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
                
				$this->entity = $obj->entity;
				$this->ref_ext = $obj->ref_ext;
				$this->lastname = $obj->lastname;
				$this->firstname = $obj->firstname;
				$this->login = $obj->login;
				$this->pass = $obj->pass;
				$this->fk_soc = $obj->fk_soc;
				$this->note = $obj->note;
				$this->datevalid = $this->db->jdate($obj->datevalid);
				$this->datec = $this->db->jdate($obj->datec);
				$this->tms = $this->db->jdate($obj->tms);
				$this->fk_user_author = $obj->fk_user_author;
				$this->fk_user_mod = $obj->fk_user_mod;
				$this->fk_user_valid = $obj->fk_user_valid;
				$this->canvas = $obj->canvas;
				$this->import_key = $obj->import_key;

                
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
        
		if (isset($this->entity)) $this->entity=trim($this->entity);
		if (isset($this->ref_ext)) $this->ref_ext=trim($this->ref_ext);
		if (isset($this->lastname)) $this->lastname=trim($this->lastname);
		if (isset($this->firstname)) $this->firstname=trim($this->firstname);
		if (isset($this->login)) $this->login=trim($this->login);
		if (isset($this->pass)) $this->pass=trim($this->pass);
		if (isset($this->fk_soc)) $this->fk_soc=trim($this->fk_soc);
		if (isset($this->note)) $this->note=trim($this->note);
		if (isset($this->fk_user_author)) $this->fk_user_author=trim($this->fk_user_author);
		if (isset($this->fk_user_mod)) $this->fk_user_mod=trim($this->fk_user_mod);
		if (isset($this->fk_user_valid)) $this->fk_user_valid=trim($this->fk_user_valid);
		if (isset($this->canvas)) $this->canvas=trim($this->canvas);
		if (isset($this->import_key)) $this->import_key=trim($this->import_key);

        

		// Check parameters
		// Put here code to add a control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element." SET";
        
		$sql.= " entity=".(isset($this->entity)?$this->entity:"null").",";
		$sql.= " ref_ext=".(isset($this->ref_ext)?"'".$this->db->escape($this->ref_ext)."'":"null").",";
		$sql.= " lastname=".(isset($this->lastname)?"'".$this->db->escape($this->lastname)."'":"null").",";
		$sql.= " firstname=".(isset($this->firstname)?"'".$this->db->escape($this->firstname)."'":"null").",";
		$sql.= " login=".(isset($this->login)?"'".$this->db->escape($this->login)."'":"null").",";
		$sql.= " pass=".(isset($this->pass)?"'".$this->db->escape($this->pass)."'":"null").",";
		$sql.= " fk_soc=".(isset($this->fk_soc)?$this->fk_soc:"null").",";
		$sql.= " note=".(isset($this->note)?"'".$this->db->escape($this->note)."'":"null").",";
		$sql.= " datevalid=".(dol_strlen($this->datevalid)!=0 ? "'".$this->db->idate($this->datevalid)."'" : 'null').",";
		$sql.= " datec=".(dol_strlen($this->datec)!=0 ? "'".$this->db->idate($this->datec)."'" : 'null').",";
		$sql.= " tms=".(dol_strlen($this->tms)!=0 ? "'".$this->db->idate($this->tms)."'" : 'null').",";
		$sql.= " fk_user_author=".(isset($this->fk_user_author)?$this->fk_user_author:"null").",";
		$sql.= " fk_user_mod=".(isset($this->fk_user_mod)?$this->fk_user_mod:"null").",";
		$sql.= " fk_user_valid=".(isset($this->fk_user_valid)?$this->fk_user_valid:"null").",";
		$sql.= " canvas=".(isset($this->canvas)?"'".$this->db->escape($this->canvas)."'":"null").",";
		$sql.= " import_key=".(isset($this->import_key)?"'".$this->db->escape($this->import_key)."'":"null")."";

        
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

		$object=new Codeacces($this->db);

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
		
		$this->entity='';
		$this->ref_ext='';
		$this->lastname='';
		$this->firstname='';
		$this->login='';
		$this->pass='';
		$this->fk_soc='';
		$this->note='';
		$this->datevalid='';
		$this->datec='';
		$this->tms='';
		$this->fk_user_author='';
		$this->fk_user_mod='';
		$this->fk_user_valid='';
		$this->canvas='';
		$this->import_key='';

		
	}

}
