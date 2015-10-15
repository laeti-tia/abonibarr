<?php
/**
 *  \file       communication_structure.class.php
 *  \ingroup    abonnement
 *  
 */

/**
 *	Put here description of your class
 */
class CommStructure
{
	const REGEX = '#((\*){3})([0-9]{3})/([0-9]{4})/([0-9]{5})(\*){3}#';
	const TIRET ='-';
	const DEBCOM='CO';
	public static function generate($refCommande) {
		// 	if(strlen((string)$refCommande)!= 10 ) {
		// 		return $refCommande;
		// 	}
		$refCommande = preg_replace('#-#i', '', $refCommande);
		$refCommande = preg_replace('#CO#i', '00', $refCommande);
	
		$base = (float) $refCommande;
		$control = $base - floor($base / 97) * 97;
	
		if ($control == 0) {
			$control = 97;
		}
	
		$base_s = (string) $refCommande;
		$control_s = (string) $control;
	
		if ($control < 10) {
			$control_s = "0" . $control_s;
		}
	
		$com = $base_s . $control_s;
		return '***'.substr($com, 0, 3) . "/" . substr($com, 3, 4) . "/" . substr($com, 7, 5).'***';
	
	}
	public static function isCommStructure($chaine){
		$rep = preg_match(self::REGEX, $chaine,$matches);
		return array('response'=>$rep,'matches'=> $matches);
	}
	
	static function getRefcommande($commStruct) {
		$ref1 ='';
		$ref2 = '';
		$ref ='';
		$ref = str_replace('***', '', $commStruct);
		 $ref = str_replace('/', '', $ref);
		 $ref = substr($ref, 2, 8);
		$ref1 = substr($ref, 0, 4);
		$ref2 = substr($ref, 4, 4);
		return self::DEBCOM.$ref1.'-'.$ref2;
		
		
		
	}
}
