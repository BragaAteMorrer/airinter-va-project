<?php
/*******************************************************************************
 *   Clock Language Strings
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2013 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.00 (December 15, 2019) : initial release
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'fr-clock.inc')) {
    header('location:index.php');
    die();
}

$clock[1] = "Admin - Plugin exemple Horloge";
$clock[2] = "GuppY est fourni avec quelques plugins très simples, à titre d'exemple (pour les développeurs) et d'embellissement de votre site GuppY (pour les webmasters).";
$clock[3] = "<b>Horloge</b> affiche une montre digitale programmée en Javascript.";
$clock[4] = "Accès par :";
$clock[5] = "Basique, par la balise HTML &lt;A&gt; :";
$clock[6] = "Le voir fonctionner :";
$clock[7] = "Horloge";
$clock[8] = "Remarques :<br />- ce qui est entre [ et ] est facultatif<br />- vous pouvez aussi indiquer la langue en rajoutant ?lng=xx (où xx=fr ou en) à la fin de l'URL, comme ceci : clock.php?lng=fr pour lancer Horloge en français.";
$clock[9] = 'Désinstaller';
$clock[10] = '<strong>ATTENTION ! La désinstallation est définitive.</strong><br/>';
?>