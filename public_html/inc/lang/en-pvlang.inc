<?php
/*
	Plugin Version : Plugin for GuppY 4.5
	under CeCILL v2 Free Software Agreement
	Copyright (C) 2006-2007 by  HPSAM
	Web site = http://www.hpsam.info

	Translated by HL : http://www.loeil-de-pique.com/
*/
if (stristr($_SERVER["SCRIPT_NAME"], "en-pvlang.inc")) {
  header("location:../index.php");
  die();
}
$pv_error1 = "Unable to check for upgrade since allow_url_fopen is disabled.";
$pv_error2 = "Check for upgrade failed for unknown reasons.";
$pv_error3 = "Unable to check for upgrade  : incorrect Id.";
$pv_error4 = "Unable to check for upgrade  : Id not found in the database.";
$pv_error5 = "Unable to check for upgrade  : Id disabled.";
$pv_ok1 = "A new version is available.";
$pv_ok2 = "version ";
$pv_ok3 = " Date ";
$pv_ok4 = "You are running the latest version.";
$pv_ok5 = "Plugin name: ";
$pv_lgversion[10] = "Please note: Update first ";
$pv_lgversion[11] = "<font color=\"red\"><b>Please note </b>: This version of the plugin is designed for ";
$pv_lgversion[12] = "Current version : ";
$pv_lgversion[13] = " You use GuppY ";
$pv_lgversion[14] = " for GuppY ";
$pv_lgversion[15] = "Thisversion ";
$pv_lgversion[16] = "  is out of date </font>, download it  ";
$pv_lgversion[17] = "last version ";
$pv_lgversion[18] = "a versionn Php minimum ";
$pv_lgversion[19] = " and a version Php maximum ";
$pv_lgversion[20] = " behind ";
$pv_lgversion[21] = " and ";
$pv_lgversion[22] = " Control all versions of plugin and/or of Guppy ";
?>