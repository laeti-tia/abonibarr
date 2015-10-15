<?php
/* Copyright (C) 2005-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2009 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2012 Christophe Battarel  		<christophe.battarel@altairis.fr>
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
 *      \file       htdocs/imports/import.php
 *      \ingroup    import
 *      \brief      Pages of import Wizard
 */

require_once '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/imports/class/import.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/import/modules_import.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/import.lib.php';
require_once DOL_DOCUMENT_ROOT.'/abonnement/class/html.formabonnement.class.php';
require_once DOL_DOCUMENT_ROOT.'/abonnement/class/communication_structure.class.php';
require_once DOL_DOCUMENT_ROOT.'/abonnement/class/abonnement.class.php';
require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';


$langs->load("exports");
$langs->load("compta");
$langs->load("errors");


// Security check
$result=restrictedArea($user, 'import');

// $abonne = new Abonnement($db);
// $cmd = new Commande($db);
// $abonne->updateExtrafieldsCommande($cmd, array());
// exit;

$datatoimport		= GETPOST('datatoimport');
$format				= GETPOST('format');
$filetoimport		= GETPOST('filetoimport');
$action				= GETPOST('action','alpha');
$confirm			= GETPOST('confirm','alpha');
$step				= (GETPOST('step') ? GETPOST('step') : 1);
$import_name		= GETPOST('import_name');
$hexa				= GETPOST('hexa');
$importmodelid		= GETPOST('importmodelid');
$excludefirstline	= (GETPOST('excludefirstline') ? GETPOST('excludefirstline') : 0);
$separator			= (GETPOST('separator') ? GETPOST('separator') : (! empty($conf->global->IMPORT_CSV_SEPARATOR_TO_USE)?$conf->global->IMPORT_CSV_SEPARATOR_TO_USE:','));
$enclosure			= (GETPOST('enclosure') ? GETPOST('enclosure') : '"');

$objimport=new Import($db);
$objimport->load_arrays($user,$datatoimport);
//var_dump($objimport);exit;
$datatoimport='abonnement_1';
$objmodelimport=new ModeleImports();
// $t = "VirementBusiness***007/9867/01629***'BankOn-line-61,71Vers:GROUPES-BE91000000143476Communication:/***007/9867/01629***";
// $reg = '#((\*){3})([0-9]{3})/([0-9]{4})/([0-9]{5})(\*){3}#';
// $rep = preg_match($reg, $t,$match);
// var_dump($match,$rep);exit;
// $resp =CommStructure::isCommStructure($expression);
$form = new Form($db);
$htmlother = new FormOther($db);
$formfile = new FormFile($db);
$formAbonn = new FormAbonnement($db);
// Init $array_match_file_to_database from _SESSION
$serialized_array_match_file_to_database=isset($_SESSION["dol_array_match_file_to_database"])?$_SESSION["dol_array_match_file_to_database"]:'';
$array_match_file_to_database=array();
$fieldsarray=explode(',',$serialized_array_match_file_to_database);

function entete_import ($step,$param ) {
	global $langs;
	$titleofmodule ='Abonnement';
	llxHeader('',$langs->trans("NewImport"),'EN:Module_Imports_En|FR:Module_Imports|ES:M&oacute;dulo_Importaciones');

	$head = import_prepare_head1($param,$step);

	dol_fiche_head($head, 'step'.$step, $langs->trans("NewImport"));


	print '<table width="100%" class="border">';

	// Module
	print '<tr><td width="25%">'.$langs->trans("Module").'</td>';
	print '<td>';
	// Special cas for import common to module/services
	print $titleofmodule;
	print '</td></tr>';

	// Lot de donnees a importer
	print '<tr><td width="25%">'.$langs->trans("Operation").'</td>';
	print '<td>Paiement des commandes d\'abonnement';
	//print img_object($objimport->array_import_module[0]->getName(),$objimport->array_import_icon[0]).' ';
	//print $objimport->array_import_label[0];
	print '</td></tr>';

	print '</table>';
	print '<br>'."\n";
}
// foreach($fieldsarray as $elem)
// {
// 	$tabelem=explode('=',$elem,2);
// 	$key=$tabelem[0];
// 	$val=(isset($tabelem[1])?$tabelem[1]:'');
// 	if ($key && $val)
// 	{
// 		$array_match_file_to_database[$key]=$val;
// 	}
// }


/*
 * Actions
 */

/*
if ($action=='downfield' || $action=='upfield')
{
	$pos=$array_match_file_to_database[$_GET["field"]];
	if ($action=='downfield') $newpos=$pos+1;
	if ($action=='upfield') $newpos=$pos-1;
	// Recherche code avec qui switcher
	$newcode="";
	foreach($array_match_file_to_database as $code=>$value)
	{
		if ($value == $newpos)
		{
			$newcode=$code;
			break;
		}
	}
	//print("Switch pos=$pos (code=".$_GET["field"].") and newpos=$newpos (code=$newcode)");
	if ($newcode)   // Si newcode trouve (protection contre resoumission de page)
	{
		$array_match_file_to_database[$_GET["field"]]=$newpos;
		$array_match_file_to_database[$newcode]=$pos;
		$_SESSION["dol_array_match_file_to_database"]=$serialized_array_match_file_to_database;
	}
}
*/
if ($action == 'builddoc')
{
	// Build import file
	$result=$objimport->build_file($user, GETPOST('model','alpha'), $datatoimport, $array_match_file_to_database);
	if ($result < 0)
	{
		setEventMessage($objimport->error, 'errors');
	}
	else
	{
		setEventMessage($langs->trans("FileSuccessfullyBuilt"));
	}
}

if ($action == 'deleteprof')
{
	if ($_GET["id"])
	{
		$objimport->fetch($_GET["id"]);
		$result=$objimport->delete($user);
	}
}

// Save import config to database
if ($action == 'add_import_model')
{
	if ($import_name)
	{
		// Set save string
		$hexa='';
		foreach($array_match_file_to_database as $key=>$val)
		{
			if ($hexa) $hexa.=',';
			$hexa.=$key.'='.$val;
		}

		$objimport->model_name = $import_name;
		$objimport->datatoimport = $datatoimport;
		$objimport->hexa = $hexa;

		$result = $objimport->create($user);
		if ($result >= 0)
		{
			setEventMessage($langs->trans("ImportModelSaved",$objimport->model_name));
		}
		else
		{
			$langs->load("errors");
			if ($objimport->errno == 'DB_ERROR_RECORD_ALREADY_EXISTS')
			{
				setEventMessage($langs->trans("ErrorImportDuplicateProfil"), 'errors');
			}
			else {
				setEventMessage($objimport->error, 'errors');
			}
		}
	}
	else
	{
		setEventMessage($langs->trans("ErrorFieldRequired",$langs->transnoentities("ImportModelName")), 'errors');
	}
}
//var_dump(confirm_deletefile,$step);exit;
if ($step == 2 && $datatoimport)
{
	if (GETPOST('sendit') && ! empty($conf->global->MAIN_UPLOAD_DOC))
	{
		dol_mkdir($conf->import->dir_temp);
		$nowyearmonth=dol_print_date(dol_now(),'%Y%m%d%H%M%S');

		$fullpath=$conf->import->dir_temp . "/" . $nowyearmonth . '-'.$_FILES['userfile']['name'];
		if (dol_move_uploaded_file($_FILES['userfile']['tmp_name'], $fullpath,1) > 0)
		{
			dol_syslog("File ".$fullpath." was added for import");
		}
		else
		{
			$langs->load("errors");
			setEventMessage($langs->trans("ErrorFailedToSaveFile"), 'errors');
		}
	}

	// Delete file
	if ($action == 'confirm_deletefile' && $confirm == 'yes')
	{
		$langs->load("other");

		$param='&datatoimport='.$datatoimport.'&format='.$format;
		if ($excludefirstline) $param.='&excludefirstline=1';

		$file = $conf->import->dir_temp . '/' . GETPOST('urlfile');	// Do not use urldecode here ($_GET and $_REQUEST are already decoded by PHP).
		$ret=dol_delete_file($file);
		if ($ret) setEventMessage($langs->trans("FileWasRemoved", GETPOST('urlfile')));
		else setEventMessage($langs->trans("ErrorFailToDeleteFile", GETPOST('urlfile')), 'errors');
		Header('Location: '.$_SERVER["PHP_SELF"].'?step='.$step.$param);
		exit;
	}
}

if ($step == 3 && $action == 'select_model')
{
	// Reinit match arrays
	$_SESSION["dol_array_match_file_to_database"]='';
	$serialized_array_match_file_to_database='';
	$array_match_file_to_database=array();

	// Load model from $importmodelid and set $array_match_file_to_database
	// and $_SESSION["dol_array_match_file_to_database"]
	$result = $objimport->fetch($importmodelid);
	if ($result > 0)
	{
		$serialized_array_match_file_to_database=$objimport->hexa;
		$fieldsarray=explode(',',$serialized_array_match_file_to_database);
		foreach($fieldsarray as $elem)
		{
			$tabelem=explode('=',$elem);
			$key=$tabelem[0];
			$val=$tabelem[1];
			if ($key && $val)
			{
				$array_match_file_to_database[$key]=$val;
			}
		}
		$_SESSION["dol_array_match_file_to_database"]=$serialized_array_match_file_to_database;
	}
}

if ($action == 'saveorder')
{
	// Enregistrement de la position des champs
	dol_syslog("boxorder=".$_GET['boxorder']." datatoimport=".$_GET["datatoimport"], LOG_DEBUG);
	$part=explode(':',$_GET['boxorder']);
	$colonne=$part[0];
	$list=$part[1];
	dol_syslog('column='.$colonne.' list='.$list);

	// Init targets fields array
	$fieldstarget=$objimport->array_import_fields[0];

	// Reinit match arrays. We redefine array_match_file_to_database
	$serialized_array_match_file_to_database='';
	$array_match_file_to_database=array();
	$fieldsarray=explode(',',$list);
	$pos=0;
	foreach($fieldsarray as $fieldnb)	// For each elem in list. fieldnb start from 1 to ...
	{
		// Get name of database fields at position $pos and put it into $namefield
		$posbis=0;$namefield='';
		foreach($fieldstarget as $key => $val)	// key:   val:
		{
			//dol_syslog('AjaxImport key='.$key.' val='.$val);
			if ($posbis < $pos)
			{
				$posbis++;
				continue;
			}
			// We found the key of targets that is at position pos
			$namefield=$key;
			//dol_syslog('AjaxImport Field name found for file field nb '.$fieldnb.'='.$namefield);

			break;
		}

		if ($fieldnb && $namefield)
		{
			$array_match_file_to_database[$fieldnb]=$namefield;
			if ($serialized_array_match_file_to_database) $serialized_array_match_file_to_database.=',';
			$serialized_array_match_file_to_database.=($fieldnb.'='.$namefield);
		}

		$pos++;
	}

	// We save new matching in session
	$_SESSION["dol_array_match_file_to_database"]=$serialized_array_match_file_to_database;
	dol_syslog('dol_array_match_file_to_database='.$serialized_array_match_file_to_database);
}




/*
 * View
 */



function import_prepare_head1($param, $maxstep=0)
{
	global $langs;

	if (empty($maxstep)) $maxstep=5;

	$h=0;
	$head = array();
	$i=1;
	while($i <= $maxstep)
	{
		$head[$h][0] = $_SERVER["PHP_SELF"].'?step='.$i.$param;
		$head[$h][1] = $langs->trans("Step")." ".$i;
		$head[$h][2] = 'step'.$i;
		$h++;
		$i++;
	}

	return $head;
}

 
//var_dump($step);exit;
// STEP 2: Page to select input format file
if ($step == 1 )
{
	// Clean saved file-database matching
	$serialized_array_match_file_to_database='';
	$array_match_file_to_database=array();
	$_SESSION["dol_array_match_file_to_database"]='';
	
	$param='';
	$datatoimport='abonnement_1';
	$param='&datatoimport='.$datatoimport;
	if ($excludefirstline) $param.='&excludefirstline=1';
	if ($separator) $param.='&separator='.urlencode($separator);
	if ($enclosure) $param.='&enclosure='.urlencode($enclosure);
	
	entete_import($step,$param);

	print '<form name="userfile" action="'.$_SERVER["PHP_SELF"].'" enctype="multipart/form-data" METHOD="POST">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="max_file_size" value="'.$conf->maxfilesize.'">';

	print $langs->trans("ChooseFormatOfFileToImport",img_picto('','filenew')).'<br>';
	print '<table class="noborder" width="100%" cellspacing="0" cellpadding="4">';

	$filetoimport='';
	$var=true;

	// Add format informations and link to download example
	print '<tr class="liste_titre"><td colspan="6">';
	print $langs->trans("FileMustHaveOneOfFollowingFormat");
	print '</td></tr>';
	$liste=$objmodelimport->liste_modeles($db);
	$key ='csv';
	$pictkey =  "mime/other";
	 
		$var=!$var;
		print '<tr '.$bc[$var].'>';
		print '<td width="16">'.img_picto_common($key,$pictkey).'</td>';
    	
    	print '<td>'.'CSV'.'</td>';
		print '<td align="center">
		<a href="'.DOL_URL_ROOT.'/imports/emptyexample.php?format='.$key.$param.'" target="_blank">'.$langs->trans("DownloadEmptyExample").'</a></td>';
		// Action button
		print '<td align="right">';
		print '<a href="'.$_SERVER["PHP_SELF"].'?step=2&format='.$key.$param.'">'.img_picto($langs->trans("SelectFormat"),'filenew').'</a>';
		print '</td>';
		print '</tr>';
	

	print '</table></form>';

    dol_fiche_end();

}


// STEP 3: Page to select file
if ($step == 2 && $datatoimport)
{
	$param='&datatoimport='.$datatoimport.'&format='.$format;
	if ($excludefirstline) $param.='&excludefirstline=1';
	if ($separator) $param.='&separator='.urlencode($separator);
	if ($enclosure) $param.='&enclosure='.urlencode($enclosure);

	$liste=$objmodelimport->liste_modeles($db);

	llxHeader('',$langs->trans("NewImport"),'EN:Module_Imports_En|FR:Module_Imports|ES:M&oacute;dulo_Importaciones');

    $head = import_prepare_head1($param, $step);

	dol_fiche_head($head, 'step'.$step, $langs->trans("NewImport"));

	/*
	 * Confirm delete file
	 */
	if ($action == 'delete')
	{
		print $form->formconfirm($_SERVER["PHP_SELF"].'?urlfile='.urlencode(GETPOST('urlfile')).'&step='.$step.$param, $langs->trans('DeleteFile'), $langs->trans('ConfirmDeleteFile'), 'confirm_deletefile', '', 0, 1);

	}

	print '<table width="100%" class="border">';

	// Module
	print '<tr><td width="25%">'.$langs->trans("Module").'</td>';
	print '<td>';
	print $titleofmodule;
	print '</td></tr>';

	// Lot de donnees a importer
	print '<tr><td width="25%">'.$langs->trans("DatasetToImport").'</td>';
	print '<td>';
	print img_object($objimport->array_import_module[0]->getName(),$objimport->array_import_icon[0]).' ';
	print $objimport->array_import_label[0];
	print '</td></tr>';

	print '</table><br>';
	print '<b>'.$langs->trans("InformationOnSourceFile").'</b>';
	print '<table width="100%" class="border">';
	//print '<tr><td colspan="2"><b>'.$langs->trans("InformationOnSourceFile").'</b></td></tr>';

	// Source file format
	print '<tr><td width="25%">'.$langs->trans("SourceFileFormat").'</td>';
	print '<td>';
    $text=$objmodelimport->getDriverDescForKey($format);
    print $form->textwithpicto($objmodelimport->getDriverLabelForKey($format),$text);
    print '</td><td align="right" class="nowrap"><a href="'.DOL_URL_ROOT.'/imports/emptyexample.php?format='.$format.$param.'" target="_blank">'.$langs->trans("DownloadEmptyExample").'</a>';

	print '</td></tr>';

	print '</table>';
	print '<br>'."\n";


	print '<form name="userfile" action="'.$_SERVER["PHP_SELF"].'" enctype="multipart/form-data" METHOD="POST">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="max_file_size" value="'.$conf->maxfilesize.'">';

	print '<table class="noborder" width="100%" cellspacing="0" cellpadding="4">';

	$filetoimport='';
	$var=true;

	print '<tr><td colspan="6">'.$langs->trans("ChooseFileToImport",img_picto('','filenew')).'</td></tr>';

	print '<tr class="liste_titre"><td colspan="6">'.$langs->trans("FileWithDataToImport").'</td></tr>';

	// Input file name box
	$var=false;
	print '<tr '.$bc[$var].'><td colspan="6">';
	print '<input type="file"   name="userfile" size="20" maxlength="80"> &nbsp; &nbsp; ';
	print '<input type="submit" class="button" value="'.$langs->trans("AddFile").'" name="sendit">';
	print '<input type="hidden" value="'.$step.'" name="step">';
	print '<input type="hidden" value="'.$format.'" name="format">';
	print '<input type="hidden" value="'.$excludefirstline.'" name="excludefirstline">';
	print '<input type="hidden" value="'.$separator.'" name="separator">';
	print '<input type="hidden" value="'.$enclosure.'" name="enclosure">';
	print '<input type="hidden" value="'.$datatoimport.'" name="datatoimport">';
	print "</tr>\n";

	// Search available imports
	$filearray=dol_dir_list($conf->import->dir_temp, 'files', 0, '', '', 'name', SORT_DESC);
	if (count($filearray) > 0)
	{
		$dir=$conf->import->dir_temp;

		// Search available files to import
		$i=0;
		foreach ($filearray as $key => $val)
		{
		    $file=$val['name'];

			// readdir return value in ISO and we want UTF8 in memory
			if (! utf8_check($file)) $file=utf8_encode($file);

			if (preg_match('/^\./',$file)) continue;

			$modulepart='import';
			$urlsource=$_SERVER["PHP_SELF"].'?step='.$step.$param.'&filetoimport='.urlencode($filetoimport);
			$relativepath=$file;
			$var=!$var;
			print '<tr '.$bc[$var].'>';
			print '<td width="16">'.img_mime($file).'</td>';
			print '<td>';
    		print '<a data-ajax="false" href="'.DOL_URL_ROOT.'/document.php?modulepart='.$modulepart.'&file='.urlencode($relativepath).'&step=3'.$param.'" target="_blank">';
    		print $file;
    		print '</a>';
			print '</td>';
			// Affiche taille fichier
			print '<td align="right">'.dol_print_size(dol_filesize($dir.'/'.$file)).'</td>';
			// Affiche date fichier
			print '<td align="right">'.dol_print_date(dol_filemtime($dir.'/'.$file),'dayhour').'</td>';
			// Del button
			print '<td align="right"><a href="'.$_SERVER['PHP_SELF'].'?action=delete&step=2'.$param.'&urlfile='.urlencode($relativepath);
			print '">'.img_delete().'</a></td>';
			// Action button
			print '<td align="right">';
			print '<a href="'.$_SERVER['PHP_SELF'].'?step=3'.$param.'&filetoimport='.urlencode($relativepath).'">'.img_picto($langs->trans("NewImport"),'filenew').'</a>';
			print '</td>';
			print '</tr>';
		}
	}

	print '</table></form>';

    dol_fiche_end();
}


// STEP 4: Page to make matching between source file and database fields
if ($step == 3 && $datatoimport)
{
	$model=$format;
	$liste=$objmodelimport->liste_modeles($db);

	// Create classe to use for import
	$dir = DOL_DOCUMENT_ROOT . "/core/modules/import/";
	$file = "import_".$model.".modules.php";
	$classname = "Import".ucfirst($model);
	require_once $dir.$file;
	$obj = new $classname($db,$datatoimport);
	$separator=";";
	if ($model == 'csv') {
	    $obj->separator = $separator;
	    $obj->enclosure = $enclosure;
	}

	// Load source fields in input file
	$fieldssource=array();
	$result=$obj->import_open_file($conf->import->dir_temp.'/'.$filetoimport,$langs);
	if ($result >= 0)
	{
		// Read first line
		$arrayrecord=$obj->import_read_record();
		// Put into array fieldssource starting with 1.
		$i=1;
		foreach($arrayrecord as $key => $val)
		{
			$fieldssource[$i]['example1']=dol_trunc($val['val'],24);
			$i++;
		}
		$obj->import_close_file();
	}
   // var_dump($arrayrecord);exit;
	// Load targets fields in database
	$fieldstarget=$objimport->array_import_fields[0];

	$maxpos=max(count($fieldssource),count($fieldstarget));

	//var_dump($array_match_file_to_database);

	// Is it a first time in page (if yes, we must initialize array_match_file_to_database)
	if (count($array_match_file_to_database) == 0)
	{
		// This is first input in screen, we need to define
		// $array_match_file_to_database
		// $serialized_array_match_file_to_database
		// $_SESSION["dol_array_match_file_to_database"]
		$pos=1;
		$num=count($fieldssource);
		while ($pos <= $num)
		{
			if ($num >= 1 && $pos <= $num)
			{
				$posbis=1;
				foreach($fieldstarget as $key => $val)
				{
					if ($posbis < $pos)
					{
						$posbis++;
						continue;
					}
					// We found the key of targets that is at position pos
					$array_match_file_to_database[$pos]=$key;
					if ($serialized_array_match_file_to_database) $serialized_array_match_file_to_database.=',';
					$serialized_array_match_file_to_database.=($pos.'='.$key);
					break;
				}
			}
			$pos++;
		}
		// Save the match array in session. We now will use the array in session.
		$_SESSION["dol_array_match_file_to_database"]=$serialized_array_match_file_to_database;
	}
	$array_match_database_to_file=array_flip($array_match_file_to_database);

	//print $serialized_array_match_file_to_database;
	//print $_SESSION["dol_array_match_file_to_database"];
	//var_dump($array_match_file_to_database);exit;

	// Now $array_match_file_to_database contains  fieldnb(1,2,3...)=>fielddatabase(key in $array_match_file_to_database)

	$param='&format='.$format.'&datatoimport='.$datatoimport.'&filetoimport='.urlencode($filetoimport);
	if ($excludefirstline) $param.='&excludefirstline=1';
	if ($separator) $param.='&separator='.urlencode($separator);
	if ($enclosure) $param.='&enclosure='.urlencode($enclosure);

	llxHeader('',$langs->trans("NewImport"),'EN:Module_Imports_En|FR:Module_Imports|ES:M&oacute;dulo_Importaciones');

    $head = import_prepare_head1($param,$step);

	dol_fiche_head($head, 'step'.$step, $langs->trans("NewImport"));

	print '<table width="100%" class="border">';

	// Module
	print '<tr><td width="25%">'.$langs->trans("Module").'</td>';
	print '<td>';
	$titleofmodule=$objimport->array_import_module[0]->getName();
	// Special cas for import common to module/services
	if (in_array($objimport->array_import_code[0], array('produit_supplierprices','produit_multiprice'))) $titleofmodule=$langs->trans("ProductOrService");
	print $titleofmodule;
	print '</td></tr>';

	// Lot de donnees a importer
	print '<tr><td width="25%">'.$langs->trans("DatasetToImport").'</td>';
	print '<td>';
	print img_object($objimport->array_import_module[0]->getName(),$objimport->array_import_icon[0]).' ';
	print $objimport->array_import_label[0];
	print '</td></tr>';

	print '</table><br>';
	print '<b>'.$langs->trans("InformationOnSourceFile").'</b>';
	print '<table width="100%" class="border">';
	//print '<tr><td colspan="2"><b>'.$langs->trans("InformationOnSourceFile").'</b></td></tr>';

	// Source file format
	print '<tr><td width="25%">'.$langs->trans("SourceFileFormat").'</td>';
	print '<td>';
    $text=$objmodelimport->getDriverDescForKey($format);
    print $form->textwithpicto($objmodelimport->getDriverLabelForKey($format),$text);
	print '</td></tr>';

	// Separator and enclosure
    if ($model == 'csv') {
		print '<tr><td width="25%">'.$langs->trans("CsvOptions").'</td>';
		print '<td>';
		print '<form>';
		print '<input type="hidden" value="'.$step.'" name="step">';
		print '<input type="hidden" value="'.$format.'" name="format">';
		print '<input type="hidden" value="'.$excludefirstline.'" name="excludefirstline">';
		print '<input type="hidden" value="'.$datatoimport.'" name="datatoimport">';
		print '<input type="hidden" value="'.$filetoimport.'" name="filetoimport">';
		print $langs->trans("Separator").' : ';
		print '<input type="text" size="1" name="separator" value="'.htmlentities($separator).'"/>';
		print '&nbsp;&nbsp;&nbsp;&nbsp;'.$langs->trans("Enclosure").' : ';
		print '<input type="text" size="1" name="enclosure" value="'.htmlentities($enclosure).'"/>';
		print '<input type="submit" value="'.$langs->trans('Update').'" class="button" />';
		print '</form>';
		print '</td></tr>';
    }

	// File to import
	print '<tr><td width="25%">'.$langs->trans("FileToImport").'</td>';
	print '<td>';
	$modulepart='import';
	$relativepath=GETPOST('filetoimport');
    print '<a data-ajax="false" href="'.DOL_URL_ROOT.'/document.php?modulepart='.$modulepart.'&file='.urlencode($relativepath).'&step=3'.$param.'" target="_blank">';
    print $filetoimport;
    print '</a>';
	print '</td></tr>';

	print '</table>';
	print '<br>'."\n";


   
	
	$var=true;
	$mandatoryfieldshavesource=true;

	
	/*
	 * Barre d'action
	 */
	print '<div class="tabsAction">';

	if (count($array_match_file_to_database))
	{
		if ($mandatoryfieldshavesource)
		{
			print '<a class="butAction" href="import.php?step=4'.$param.'&filetoimport='.urlencode($filetoimport).'">'.$langs->trans("NextStep").'</a>';
		}
		else
		{
			print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->transnoentitiesnoconv("SomeMandatoryFieldHaveNoSource")).'">'.$langs->trans("NextStep").'</a>';
		}
	}

	print '</div>';


}


// STEP 5: Summary of choices and launch simulation
if ($step == 4 && $datatoimport)
{
	$model=$format;
	$liste=$objmodelimport->liste_modeles($db);

	// Create classe to use for import
	$dir = DOL_DOCUMENT_ROOT . "/core/modules/import/";
	$file = "import_".$model.".modules.php";
	$classname = "Import".ucfirst($model);
	require_once $dir.$file;
	$obj = new $classname($db,$datatoimport);
	if ($model == 'csv') {
	    $obj->separator = $separator;
	    $obj->enclosure = $enclosure;
	}
	

	// Load source fields in input file
	$fieldssource=array();
	$nbreCommStructure = array();
	$nboflines=dol_count_nb_of_line($conf->import->dir_temp.'/'.$filetoimport);
	$result=$obj->import_open_file($conf->import->dir_temp.'/'.$filetoimport,$langs);
	if ($result >= 0)
	{
		// Read first line
		$arrayrecord=$obj->import_read_record();
		// Put into array fieldssource starting with 1.
		$i=1;
		//echo '<pre>';var_dump($arrayrecord);
		foreach($arrayrecord as $key => $val)
		{
			$fieldssource[$i]=dol_trunc($val['val'],24);
			$i++;
		}
		
		$obj->import_close_file();
	}
	$iligne=1;
	$result=$obj->import_open_file($conf->import->dir_temp.'/'.$filetoimport,$langs);
	
	while ($sourcelinenb < $nboflines && ! $endoffile)
	{
		$iligne++;
		$errors =array();
		// Read line and stor it into $arrayrecord
		$arrayrecord=$obj->import_read_record();
		//var_dump($arrayrecord);
		if ($arrayrecord === false)
		{
			$arrayofwarnings[$iligne][0]=array('lib'=>'File has '.$nboflines.' lines. However we reach end of file after record '.$sourcelinenb.'. This may occurs when some records are split onto several lines.','type'=>'EOF_RECORD_ON_SEVERAL_LINES');
			$endoffile++;
			continue;
		}
		if ($excludefirstline && $iligne == 1) continue;
		$montant = $arrayrecord[6]['val'];
				$devise = $arrayrecord[7]['val'];
				$datepaiement = $arrayrecord[4]['val'];
				$libelle = $arrayrecord[8]['val'];
				//echo '<br>';
				//($montant=floatval(str_replace(',', '.', $montant)));
				$resp =CommStructure::isCommStructure($libelle);
				if($resp['response']) {
					$nbreCommStructure[] =array('devise'=>$devise,'montant'=>$montant,
							'$datepaiement'=>$datepaiement,'comm'=>$resp['matches'][0]);
				}
			
	}
	
	//var_dump($nbreCommStructure,'jjj');
	//exit;
// 	for($iligne=0;$iligne<$nboflines;$iligne++){
// 		// Read first line
// 		$arrayrecord=$obj->import_read_record();
// 		echo '<pre>';var_dump($arrayrecord);
// 		$montant = $arrayrecord[6];
// 		$devise = $arrayrecord[7];
// 		$datepaiement = $arrayrecord[4];
// 		$libelle = $arrayrecord[8];
// 		echo '88888'.$libelle;
// 	    //$resp =CommStructure::isCommStructure($expression);
// 	}exit;
	// 			var_dump($expression1,'communication valide');
	
 
	$param='&leftmenu=import&format='.$format.'&datatoimport='.$datatoimport.'&filetoimport='.urlencode($filetoimport).'&nboflines='.$nboflines.'&separator='.urlencode($separator).'&enclosure='.urlencode($enclosure);
	$param2 = $param;
	if ($excludefirstline) {
		$param.='&excludefirstline=1';
	}

	llxHeader('',$langs->trans("NewImport"),'EN:Module_Imports_En|FR:Module_Imports|ES:M&oacute;dulo_Importaciones');

    $head = import_prepare_head1($param,$step);

	dol_fiche_head($head, 'step'.$step, $langs->trans("NewImport"));

	print '<form name="userfile" action="'.$_SERVER["PHP_SELF"].'" enctype="multipart/form-data" METHOD="POST">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	
	print '<table width="100%" class="border">';

	// Module
	print '<tr><td width="25%">'.$langs->trans("Module").'</td>';
	print '<td>';
	$titleofmodule=$objimport->array_import_module[0]->getName();
	// Special cas for import common to module/services
	if (in_array($objimport->array_import_code[0], array('produit_supplierprices','produit_multiprice'))) $titleofmodule=$langs->trans("ProductOrService");
	print $titleofmodule;
	print '</td></tr>';

	// Lot de donnees a importer
	print '<tr><td>'.$langs->trans("DatasetToImport").'</td>';
	print '<td>';
	print img_object($objimport->array_import_module[0]->getName(),$objimport->array_import_icon[0]).' ';
	print $objimport->array_import_label[0];
	print '</td></tr>';

	print '</table><br>';
	print '<b>'.$langs->trans("InformationOnSourceFile").'</b>';
	print '<table width="100%" class="border">';
	//print '<tr><td colspan="2"><b>'.$langs->trans("InformationOnSourceFile").'</b></td></tr>';

	// Source file format
	print '<tr><td width="25%">'.$langs->trans("SourceFileFormat").'</td>';
	print '<td>';
    $text=$objmodelimport->getDriverDescForKey($format);
    print $form->textwithpicto($objmodelimport->getDriverLabelForKey($format),$text);
	print '</td></tr>';

	// File to import
	print '<tr><td>'.$langs->trans("FileToImport").'</td>';
	print '<td>';
	$modulepart='import';
	$relativepath=GETPOST('filetoimport');
    print '<a data-ajax="false" href="'.DOL_URL_ROOT.'/document.php?modulepart='.$modulepart.'&file='.urlencode($relativepath).'&step=4'.$param.'" target="_blank">';
    print $filetoimport;
    print '</a>';
    print '</td></tr>';

	// Nb of fields
	print '<tr><td>';
	print $langs->trans("NbOfSourceLines");
	print '</td><td>';
	print $nboflines;
	print '</td></tr>';

	// Checkbox do not import first line
	print '<tr><td>';
	print $langs->trans("Option");
	print '</td><td>';
	print '<input type="checkbox" name="excludefirstline" value="1"';
	print ($excludefirstline?' checked="checked"':'');
//	print ' onClick="javascript: window.location=\''.$_SERVER["PHP_SELF"].'?step='.$step.'excludefirstline='.($excludefirstline?'0':'1').$param2.'\';"';
	print '>';
	print ' '.$langs->trans("DoNotImportFirstLine");
	print '</td></tr>';

	print '</table>';
	print '<br>';
	
	print '<b>'.$langs->trans("Information sur le virement").'</b>';
	print '<table width="100%" class="border">';
	
	print '<tr><td width="25%">';
	print $langs->trans('AccountToCredit');
	print '</td>';
	print '<td>'.$formAbonn->select_comptes($accountid,'accountid',0,'',2).'</td>';
	print '</tr>';
	print '</table>';
	print '<br>';

	print '<b>'.$langs->trans("InformationOnTargetTables").'</b>';
	print '<table width="100%" class="border">';
	//print '<tr><td colspan="2"><b>'.$langs->trans("InformationOnTargetTables").'</b></td></tr>';

	
	// Fields imported
	print '<tr><td>';
	print $langs->trans("FieldsTarget").'</td><td>';
	$chaine='';
	foreach ($fieldssource as $fieldcsv) { 
		$chaine .= $fieldcsv.'->';
	}
	echo $chaine;
	print '</td></tr>';
	
	// Tables imported
	print '<tr><td width="25%">';
	print $langs->trans("Nombre de communication structurée .");
	print '</td><td>';
	print count($nbreCommStructure);
	print '</td></tr>';
	
	
	$step1 = $step+1;
	print '<input type="hidden" value="'.$step1.'" name="step">';
	print '<input type="hidden" value="'.$format.'" name="format">';
	print '<input type="hidden" value="'.$excludefirstline.'" name="excludefirstline">';
	print '<input type="hidden" value="'.$datatoimport.'" name="datatoimport">';
	print '<input type="hidden" value="'.$filetoimport.'" name="filetoimport">';
	print '<input type="hidden" value="import" name="leftmenu">';
	print '<input type="hidden" value="'.$importid.$param.'" name="importid">';
	
	;

	print '</table>';

    dol_fiche_end();


  print '<center>';
    //    if ($user->rights->import->run)
    	
    //    {
    if(count($nbreCommStructure)>0)
  print '<input type="submit" value="'.$langs->trans("Lancer l'import dans dolibarr").'" class="button" />';
  else
           
  print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'">'.$langs->trans("Retour").'</a>';
            
        //}
        
        print '</center>';
        print '</form>';
}


// // STEP 6: Real import
if ($step == 5 && $datatoimport)
{
	$model=$format;
	$liste=$objmodelimport->liste_modeles($db);
	$importid=$_REQUEST["importid"];

	// Create classe to use for import
	$dir = DOL_DOCUMENT_ROOT . "/core/modules/import/";
	$file = "import_".$model.".modules.php";
	$classname = "Import".ucfirst($model);
	require_once $dir.$file;
	$obj = new $classname($db,$datatoimport);
	if ($model == 'csv') {
	    $obj->separator = $separator;
	    $obj->enclosure = $enclosure;
	}
	$separator=';';
	$obj->separator =$separator;
	// Load source fields in input file
	$fieldssource=array();
	$result=$obj->import_open_file($conf->import->dir_temp.'/'.$filetoimport,$langs);
	if ($result >= 0)
	{
		// Read first line
		$arrayrecord=$obj->import_read_record();
		// Put into array fieldssource starting with 1.
		
		$i=1;
		foreach($arrayrecord as $key => $val)
		{
			$fieldssource[$i]['example1']=dol_trunc($val['val'],24);
			$i++;
		}
		$obj->import_close_file();
	}
	
	$nboflines=(! empty($_GET["nboflines"])?$_GET["nboflines"]:dol_count_nb_of_line($conf->import->dir_temp.'/'.$filetoimport));

	$param='&format='.$format.'&datatoimport='.$datatoimport.'&filetoimport='.urlencode($filetoimport).'&nboflines='.$nboflines;
	if ($excludefirstline) $param.='&excludefirstline=1';
	if ($separator) $param.='&separator='.urlencode($separator);
	if ($enclosure) $param.='&enclosure='.urlencode($enclosure);

	llxHeader('',$langs->trans("NewImport"),'EN:Module_Imports_En|FR:Module_Imports|ES:M&oacute;dulo_Importaciones');

    $head = import_prepare_head1($param,$step);

	dol_fiche_head($head, 'step'.$step, $langs->trans("NewImport"));
	$arrayoferrors=array();
	$arrayofwarnings=array();
	$maxnboferrors=empty($conf->global->IMPORT_MAX_NB_OF_ERRORS)?50:$conf->global->IMPORT_MAX_NB_OF_ERRORS;
	$maxnbofwarnings=empty($conf->global->IMPORT_MAX_NB_OF_WARNINGS)?50:$conf->global->IMPORT_MAX_NB_OF_WARNINGS;
	$nboferrors=0;
	$nbofwarnings=0;
	
	$importid=dol_print_date(dol_now(),'%Y%m%d%H%M%S');
	
	print '<table width="100%" class="border">';
	
	// Module
	print '<tr><td width="25%">'.$langs->trans("Module").'</td>';
	print '<td>';
	$titleofmodule=$objimport->array_import_module[0]->getName();
	// Special cas for import common to module/services
	if (in_array($objimport->array_import_code[0], array('produit_supplierprices','produit_multiprice'))) $titleofmodule=$langs->trans("ProductOrService");
	print $titleofmodule;
	print '</td></tr>';
	
	// Lot de donnees a importer
	print '<tr><td>'.$langs->trans("DatasetToImport").'</td>';
	print '<td>';
	print img_object($objimport->array_import_module[0]->getName(),$objimport->array_import_icon[0]).' ';
	print $objimport->array_import_label[0];
	print '</td></tr>';
	
	print '</table><br>';
	print '<b>'.$langs->trans("InformationOnSourceFile").'</b>';
	print '<table width="100%" class="border">';
	//print '<tr><td colspan="2"><b>'.$langs->trans("InformationOnSourceFile").'</b></td></tr>';
	
	// Source file format
	print '<tr><td width="25%">'.$langs->trans("SourceFileFormat").'</td>';
	print '<td>';
	$text=$objmodelimport->getDriverDescForKey($format);
	print $form->textwithpicto($objmodelimport->getDriverLabelForKey($format),$text);
	print '</td></tr>';
	
	// File to import
	print '<tr><td>'.$langs->trans("FileToImport").'</td>';
	print '<td>';
	$modulepart='import';
	$relativepath=GETPOST('filetoimport');
	print '<a data-ajax="false" href="'.DOL_URL_ROOT.'/document.php?modulepart='.$modulepart.'&file='.urlencode($relativepath).'&step=4'.$param.'" target="_blank">';
	print $filetoimport;
	print '</a>';
	print '</td></tr>';
	
	// Nb of fields
	print '<tr><td>';
	print $langs->trans("NbOfSourceLines");
	print '</td><td>';
	print $nboflines;
	print '</td></tr>';
	print '</table>';
	
	//var_dump($array_match_file_to_database);
	
	
	$excludefirstline =1;
	 //$sourcelinenb = 1;
	
	// Open input file
	$nbok=0;
	$pathfile=$conf->import->dir_temp.'/'.$filetoimport;
	$result=$obj->import_open_file($pathfile,$langs);
	
	if ($result > 0)
	{
		global $tablewithentity_cache;
		$tablewithentity_cache=array();
		$sourcelinenb=0; $endoffile=0;
		require_once DOL_DOCUMENT_ROOT.'/abonnement/class/abonnement.class.php';
		require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
		require_once DOL_DOCUMENT_ROOT.'/abonnement/class/virementcmde.class.php';
		$error ='';
		$errors = array();
		$nbreCommStructureSuccess =array();
		// Loop on each input file record
		while ($sourcelinenb < $nboflines && ! $endoffile)
		{
			$sourcelinenb++;
			$errors =array();
			// Read line and stor it into $arrayrecord
			$arrayrecord=$obj->import_read_record();
			;
			if ($arrayrecord === false)
			{
				$arrayofwarnings[$sourcelinenb][0]=array('lib'=>'File has '.$nboflines.' lines. However we reach end of file after record '.$sourcelinenb.'. This may occurs when some records are split onto several lines.','type'=>'EOF_RECORD_ON_SEVERAL_LINES');
				$endoffile++;
				continue;
			}
			if ($excludefirstline && $sourcelinenb == 1) continue;
			
			
			$libelle = $arrayrecord[8]['val'];
			//echo '<br>'.$montant;
			//($montant=floatval(str_replace(',', '.', $montant)));
			$resp =CommStructure::isCommStructure($libelle);
			
			if($resp['response']) {
				$montant = $arrayrecord[6]['val'];
				$montant = floatval(str_replace(',', '.', $montant));
				$devise = trim($arrayrecord[7]['val']);
				$datepaiement = trim($arrayrecord[4]['val']);
				//var_dump(date('y-m-d',getTimestamp($datepaiement)),$datepaiement);exit;
				
				$refCmdComm = $resp['matches'][0];
				$num_mvt = trim($arrayrecord[3]['val']);
				$comm_structure = $resp['matches'][0];
				$nbreCommStructure[] =array('devise'=>$devise,'montant'=>$montant,
						'datepaiement'=>$datepaiement,'comm'=>$resp['matches'][0]);
				
				$i=0;
				$abonne = new Abonnement($db);
				$cmd = new Commande($db);
				//$abonne->updateExtrafieldsCommande($cmd, array());
				$histvire = new Virementcmde($db);
				
				if(!$histvire->isExiste($comm_structure,$num_mvt,getTimestamp($datepaiement))) {
				$refCmd = CommStructure::getRefcommande($refCmdComm);
				
				$re = $cmd->fetch(null,trim($refCmd)); 
				
				if($re>0) {
					$db->begin();
				//	$cmd = new Commande($db);
					
				$re = $cmd->cloture($user);
				 $re = $abonne->createInvoiceAndContratFromCommande($cmd,abs($montant),'1',GETPOST('accountid'));
				//var_dump($re,'fin');exit;
				//var_dump($refCmd);
				 if(!$re) {
				 	$errors[] =$abonne->errors;
				 }	
				 
				 if (count($errors)>0) { 
				 	$arrayoferrors[$sourcelinenb]=$errors;
				 	$db->rollback();
				 }
				 //if (count($cmd->warnings)) $arrayofwarnings[$sourcelinenb]=$cmd->warnings;
				 if (count($errors)<=0) {
				 	$nbok++;
				 	$nbreCommStructureSuccess[] =array('devise'=>$devise,'montant'=>$montant,
				 			'datepaiement'=>$datepaiement,'comm'=>$resp['matches'][0],'orderRef'=>$cmd->ref);
				 	$histvire = new Virementcmde($db);
				 	$histvire->montant = $montant;
				 	$histvire->devise = $devise;
				 	$histvire->communication = $resp['matches'][0];
				 	$histvire->date_mvt = getTimestamp($datepaiement);
				 	$histvire->num_mvt = $num_mvt;
				 	$histvire->fk_commande = $cmd->id;
				 	$res = $histvire->create($user);
				 	//var_dump($datepaiement,'hhh');
				 	///var_dump($res);exit;
				 	$db->commit();
				 }
				 
				} else {
					$errors[] ="La commande avec la référence <b> $refCmd</b> n'existe pas";
				}
			} else {
				$refCmd = CommStructure::getRefcommande($refCmdComm);
				$re = $cmd->fetch(null,trim($refCmd));
				
				$warrings [] = "La commande avec la référence <b> <a class='butAction' href=' ".DOL_URL_ROOT."/commande/card.php?id=$cmd->id '>"."$refCmd </a></b> déjà payée	";
				//var_dump($warrings);exit;
			}
			//var_dump($warring);exit;
		}
        if(count($errors)) {
        	$arrayoferrors [$sourcelinenb]=$errors;
        }
        if(count($warrings)) {
        	$arrayofwarnings [$sourcelinenb]=$warrings;
        }	
		} 
		// Close file
		$obj->import_close_file();
	}
	else
	{
		print $langs->trans("ErrorFailedToOpenFile",$pathfile);
	}
	
	



    dol_fiche_end();
    // Show Errors
    //var_dump($arrayoferrors);
    if (count($arrayoferrors))
    {
    	print img_error().' <b>'.$langs->trans("ErrorsOnXLines",count($arrayoferrors)).'</b><br>';
    	print '<table width="100%" class="border"><tr><td>';
    	foreach ($arrayoferrors as $key => $val)
    	{
    		$nboferrors++;
    		if ($nboferrors > $maxnboferrors)
    		{
    			print $langs->trans("TooMuchErrors",(count($arrayoferrors)-$nboferrors))."<br>";
    			break;
    		}
    		print '* '.$langs->trans("Line").' '.$key.'<br>';
    		foreach($val as $i => $err)
    		{
    			print ' &nbsp; &nbsp; > '.$err.'<br>';
    		}
    	}
    	print '</td></tr></table>';
    	print '<br>';
    }

    
    if (count($arrayofwarnings))
    {
    	print img_warning().' <b>'.$langs->trans("WarningsOnXLines",count($arrayofwarnings)).'</b><br>';
    	print '<table width="100%" class="border"><tr><td>';
    	foreach ($arrayofwarnings as $key => $val)
    	{
    		$nbofwarnings++;
    		if ($nbofewarnings > $maxnboferrors)
    		{
    			print $langs->trans("TooMuchWarning",(count($nbofewarnings)-$nbofwarnings))."<br>";
    			break;
    		}
    		print '* '.$langs->trans("Line").' '.$key.'<br>';
    		foreach($val as $i => $warn)
    		{
    			print ' &nbsp; &nbsp; > '.$warn.'<br>';
    		}
    	}
    	print '</td></tr></table>';
    	print '<br>';
    }

	// Show result
	print '<center>';
	print '<br>';
	// Show OK
	if (! count($arrayoferrors) && ! count($arrayofwarnings)) print img_picto($langs->trans("OK"),'tick').' <b>'.$langs->trans("NoError").'</b><br><br>';
	else print $langs->trans("NbOfLinesOK",$nbok).'</b><br><br>';
	
	print $langs->trans("NbOfLinesImported",$nbok).'</b><br><br>';
	print $langs->trans("Nombre de communication structurée").' : '.count($nbreCommStructure).'<br>';
	//print $langs->trans("YouCanUseImportIdToFindRecord",$importid).'<br>';
	if(count($nbreCommStructureSuccess) > 0) {
	echo '<table>';
	echo'<tr>';
		echo'<td> Numéro Commande </td>';
		echo'<td>Date</td>';
		echo'<td>Devise</td>';
		echo'<td>Communication structurée</td>';
		echo'<td>Montant</td>';
		echo'</tr>';
		array('devise'=>$devise,'montant'=>$montant,
				'datepaiement'=>$datepaiement,'comm'=>$resp['matches'][0]);
		$totalSomme = 0;
	foreach ($nbreCommStructureSuccess as $cmdSuccess) {
		$totalSomme += $cmdSuccess['montant'];
		echo'<tr>';
		echo'<td>'.$cmdSuccess['orderRef'].'</td>';
		echo'<td>'.$cmdSuccess['datepaiement'].'</td>';
		echo'<td>'.$cmdSuccess['devise'].'</td>';
		echo'<td>'.$cmdSuccess['comm'].'</td>';
		echo'<td>'.$cmdSuccess['montant'].'</td>';
		
		echo'</tr>';
		
	}
	}
	print '</center>';
}



print '<br>';


llxFooter();

$db->close();


/**
 * Function to put the movable box of a source field
 *
 * @param	array	$fieldssource	List of source fields
 * @param	int		$pos			Pos
 * @param	string	$key			Key
 * @param	boolean	$var			Line style (odd or not)
 * @param	int		$nostyle		Hide style
 * @return	void
 */
function show_elem($fieldssource,$pos,$key,$var,$nostyle='')
{
	global $langs,$bc;

	print "\n\n<!-- Box ".$pos." start -->\n";
	print '<div class="box" style="padding: 0px 0px 0px 0px;" id="boxto_'.$pos.'">'."\n";

	print '<table summary="boxtable'.$pos.'" width="100%" class="nobordernopadding">'."\n";
	if ($pos && $pos > count($fieldssource))	// No fields
	{
		print '<tr '.($nostyle?'':$bc[$var]).' height="20">';
		print '<td class="nocellnopadding" width="16" style="font-weight: normal">';
		print img_picto(($pos>0?$langs->trans("MoveField",$pos):''),'uparrow','class="boxhandle" style="cursor:move;"');
		print '</td>';
		print '<td style="font-weight: normal">';
		print $langs->trans("NoFields");
		print '</td>';
		print '</tr>';
	}
	elseif ($key == 'none')	// Empty line
	{
		print '<tr '.($nostyle?'':$bc[$var]).' height="20">';
		print '<td class="nocellnopadding" width="16" style="font-weight: normal">';
		print '&nbsp;';
		print '</td>';
		print '<td style="font-weight: normal">';
		print '&nbsp;';
		print '</td>';
		print '</tr>';
	}
	else	// Print field of source file
	{
		print '<tr '.($nostyle?'':$bc[$var]).' height="20">';
		print '<td class="nocellnopadding" width="16" style="font-weight: normal">';
		// The image must have the class 'boxhandle' beause it's value used in DOM draggable objects to define the area used to catch the full object
		print img_picto($langs->trans("MoveField",$pos),'uparrow','class="boxhandle" style="cursor:move;"');
		print '</td>';
		print '<td style="font-weight: normal">';
		print $langs->trans("Field").' '.$pos;
		$example=$fieldssource[$pos]['example1'];
		if ($example)
		{
		    if (! utf8_check($example)) $example=utf8_encode($example);
		    print ' (<i>'.$example.'</i>)';
		}
		print '</td>';
		print '</tr>';
	}

	print "</table>\n";

	print "</div>\n";
	print "<!-- Box end -->\n\n";
}


/**
 * Return not used field number
 *
 * @param 	array	$fieldssource	Array of field source
 * @param	array	$listofkey		Array of keys
 * @return	void
 */
function getnewkey(&$fieldssource,&$listofkey)
{
	$i=count($fieldssource)+1;
	// Max number of key
	$maxkey=0;
	foreach($listofkey as $key=>$val)
	{
		$maxkey=max($maxkey,$key);
	}
	// Found next empty key
	while($i <= $maxkey)
	{
		if (empty($listofkey[$i])) break;
		else $i++;
	}

	$listofkey[$i]=1;
	return $i;
}


function getTimestamp($mydate){
	$mydate = str_replace('.', '/', $mydate);
	$mydate = str_replace('-', '/', $mydate);
	@list($jour,$mois,$annee)=explode('/',$mydate);
   return mktime(0,0,0,$mois,$jour,$annee);
}
