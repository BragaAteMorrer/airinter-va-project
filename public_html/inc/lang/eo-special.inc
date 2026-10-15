<?php
/*******************************************************************************
 *   Calendar function
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2020 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.00 (December 15, 2020) : initial release
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'eo-special.inc')) {
    header('location:../index.php');
    die();
}

function days_easter($annee=NULL) {
	$annee = intval($annee == NULL ? date('Y') : $annee);
	
	$g  = $annee % 19;
	$c  = intval($annee / 100);
	$c4 = intval($c / 4);
	$e  = intval((8 * $c + 13) / 25);
	$h  = intval(((19 * $g) + $c - $c4 - $e + 15)) % 30;
	$k  = intval($h / 28);
	$p  = intval(29 / ($h + 1));
	$q  = intval((21 - $g) / 11);
	$i  = ($k * $p * $q - 1) * $k + $h;
	$b  = intval($annee / 4) + $annee;
	$j1 = $b + $i + 2 + $c4 - $c;
	$j2 = $j1 % 7;
	
	return 7 + $i - $j2;
}

function cal_ferie($jour, $mois, $annee) {
    static $ferie = array();
    $jref = sprintf('%02d/%02d', $jour, $mois);
    if (!isset($ferie[$annee])) {
        // $easter  = easter_days($annee);
        $easter  = days_easter($annee);
        $paques  = date('d/m', mktime(12, 0, 0, 3, 21+$easter, $annee));
        // determino de la aliaj nefiksaj religiaj festoj (other feasts)
        $lunpaq  = date('d/m', mktime(12, 0, 0, 3, 21+$easter+1, $annee));   // Paska Lundo
        $ascens  = date('d/m', mktime(12, 0, 0, 3, 21+$easter+39, $annee));  // Ĉieliro
        $lunpent = date('d/m', mktime(12, 0, 0, 3, 21+$easter+50, $annee));  // Pentekosta Lundo

        // listo de la feriaj tagoj en la jaro, tt/mm (all public holidays of the year, dd/mm)
        //   -----        vi povas almeti aŭ ŝanĝi viajn feriajn tagojn ĉi tie     -------
        $ferie[$annee] = array('01/01', $paques, $lunpaq, '01/05', '08/05', $ascens,
            $lunpent, '14/07', '15/08', '01/11', '11/11', '25/12');
    }
    return in_array($jref, $ferie[$annee]);
}
