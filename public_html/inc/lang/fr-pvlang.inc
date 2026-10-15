<?php
/*
	Plugin Version : Plugin for GuppY 4.5
	under CeCILL v2 Free Software Agreement
	Copyright (C) 2006-2007 by  HPSAM
	Web site = http://www.hpsam.info
*/
if (stristr($_SERVER["SCRIPT_NAME"], "fr-pvlang.inc")) {
  header("location:../index.php");
  die();
}
$pv_error1 = "Impossible de vérifier la mise à jour car allow_url_fopen est désactivé.";
$pv_error2 = "La vérification de la mise à jour a échoué pour une raison inconnue.";
$pv_error3 = "Impossible de vérifier la mise à jour : Id non conforme.";
$pv_error4 = "Impossible de vérifier la mise à jour : Id non présent dans la base.";
$pv_error5 = "Impossible de vérifier la mise à jour : Id non active.";
$pv_ok1 = "Une nouvelle version est disponible";
$pv_ok2 = "version ";
$pv_ok3 = " du ";
$pv_ok4 = "Vous utilisez déjà la dernière version.";
$pv_ok5 = "Nom du plugin: ";
$pv_ok6 = "à jour";
$pv_lgversion[10] = "Attention, mettez d'abord à jour ";
$pv_lgversion[11] = "<font color=\"red\"><b>Attention</b>, cette version du plugin est conçue pour ";
$pv_lgversion[12] = "Version actuelle : ";
$pv_lgversion[13] = " vous utilisez GuppY ";
$pv_lgversion[14] = " pour GuppY ";
$pv_lgversion[15] = "Cette version ";
$pv_lgversion[16] = " n'est pas à jour, téléchargez la ";
$pv_lgversion[17] = "dernière version ";
$pv_lgversion[18] = "une version Php minimum ";
$pv_lgversion[19] = " et une version Php maximum ";
$pv_lgversion[20] = " entre ";
$pv_lgversion[21] = " et ";
$pv_lgversion[22] = " Contrôler les versions du plugin et/ou de Guppy ";


?>