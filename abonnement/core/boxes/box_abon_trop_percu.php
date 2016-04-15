<?php
/* 
 * Copyright (C) 2014 Florian HENRY <florian.henry@open-concept.pro>
 * Copyright (C) 2015 Raphaël Doursenaud <rdoursenaud@gpcsolutions.fr>
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
 * \file	core/boxes/box_abon_trop_percu.php
 * \ingroup	abonnement
 * \brief	paiement facture trop percu box
 */
include_once DOL_DOCUMENT_ROOT . "/core/boxes/modules_boxes.php";

/**
 * Class to manage the box
 */
class box_abon_trop_percu extends ModeleBoxes
{

	public $boxcode = "box_abon_trop_percu";

	public $boximg = "object_bill";

	public $boxlabel;

	public $depends = array(
		"abonnement"
	);

	public $db;

	public $param;

	public $info_box_head = array();

	public $info_box_contents = array();

	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $langs;
		$langs->load("boxes");
		$langs->load("abonnement@abonnement");
		
		$this->boxlabel = $langs->transnoentitiesnoconv("ABONListTropPercu");
	}

	/**
	 * Load data into info_box_contents array to show array later.
	 *
	 * @param int $max
	 *        	of records to load
	 * @return void
	 */
function loadBox($max=20)
	{
		global $conf, $user, $langs, $db;

		$this->max=$max;

		include_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
		$facturestatic=new Facture($db);

		$this->info_box_head = array('text' => $langs->trans("ABONListTropPercu",$max));

		if ($user->rights->facture->lire)
		{
			$socid = $user->socid;
			//$fuser = new User($db);
			//$fuser->fetch($user->id);
			//var_dump($socid);
			//exit;
			$sql = "SELECT f.rowid, f.facnumber, f.fk_statut, f.datef, f.type, f.total, f.total_ttc, f.paye, f.tms,";
			$sql.= " f.date_lim_reglement as datelimite,";
			$sql.= " s.nom as name, s.rowid as socid,";
			$sql.= " sum(pf.amount) as am";
			$sql.= " FROM ".MAIN_DB_PREFIX."societe as s,".MAIN_DB_PREFIX."facture as f";
			$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."paiement_facture as pf on f.rowid=pf.fk_facture";
			if (!$user->rights->societe->client->voir && !$socid) $sql.= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
			$sql.= " WHERE s.rowid = f.fk_soc AND f.paye = 0 AND f.fk_statut = 1";
			$sql.= " AND f.entity = ".$conf->entity;
			if (!$user->rights->societe->client->voir && !$socid) $sql.= " AND s.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;
			if ($socid) $sql.= " AND f.fk_soc = ".$socid;
			$sql.= " GROUP BY f.rowid, f.facnumber, f.fk_statut, f.datef, f.type, f.total, f.total_ttc, f.paye, f.tms, f.date_lim_reglement, s.nom, s.rowid";
			$sql.= " HAVING  sum(pf.amount) > f.total_ttc  ";
				
			$sql.= " ORDER BY f.datef ASC, f.facnumber ASC";
					
			$sql.= $db->plimit($max, 0);

			$result = $db->query($sql);
			if ($result)
			{
				$num = $db->num_rows($result);
				$now=dol_now();

				$i = 0;
				$l_due_date = $langs->trans('Late').' ('.strtolower($langs->trans('DateEcheance')).': %s)';

				while ($i < $num)
				{
					$objp = $db->fetch_object($result); 
					$datelimite=$db->jdate($objp->datelimite);

					$late='';
					if ($datelimite < ($now - $conf->facture->client->warning_delay)) $late = img_warning(sprintf($l_due_date,dol_print_date($datelimite,'day')));

					$this->info_box_contents[$i][0] = array('td' => 'align="left" width="16"',
                    'logo' => $this->boximg,
                    'url' => DOL_URL_ROOT."/compta/facture.php?facid=".$objp->rowid);

					$this->info_box_contents[$i][1] = array('td' => 'align="left"',
                    'text' => $objp->facnumber,
                    'text2'=> $late,
                    'url' => DOL_URL_ROOT."/compta/facture.php?facid=".$objp->rowid);

					$this->info_box_contents[$i][2] = array('td' => 'align="left" width="16"',
                    'logo' => 'company',
                    'url' => DOL_URL_ROOT."/comm/card.php?socid=".$objp->socid);

					$this->info_box_contents[$i][3] = array('td' => 'align="left"',
                    'text' => $objp->name,
                    'maxlength'=>44,
                    'url' => DOL_URL_ROOT."/comm/card.php?socid=".$objp->socid);

					$this->info_box_contents[$i][4] = array('td' => 'align="right"',
                    'text' => dol_print_date($datelimite,'day'),
					);
					$this->info_box_contents[$i][5] = array('td' => 'align="right"',
							'text' => price($objp->total_ttc),
					);
					$this->info_box_contents[$i][6] = array('td' => 'align="right"',
							'text' => price($objp->am),
					);
					$this->info_box_contents[$i][7] = array('td' => 'align="right" width="18"',
                    'text' => $facturestatic->LibStatut($objp->paye,$objp->fk_statut,3));

					$i++;
				}

				if ($num==0) $this->info_box_contents[$i][0] = array('td' => 'align="center"','text'=>$langs->trans("NoUnpaidCustomerBills"));

				$db->free($result);
			}
			else
			{
				$this->info_box_contents[0][0] = array(	'td' => 'align="left"',
    	        										'maxlength'=>500,
	            										'text' => ($db->error().' sql='.$sql));
			}
		}
		else {
			$this->info_box_contents[0][0] = array('td' => 'align="left"',
            'text' => $langs->trans("ReadPermissionNotAllowed"));
		}
	}

	/**
	 * Method to show box
	 *
	 * @param array $head
	 *        	with properties of box title
	 * @param array $contents
	 *        	with properties of box lines
	 * @return void
	 */
	public function showBox($head = null, $contents = null)
	{
		parent::showBox($this->info_box_head, $this->info_box_contents);
	}
}
