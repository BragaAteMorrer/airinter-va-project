<?php
/*******************************************************************************
 *   Mind Plugin
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

if (stristr($_SERVER['SCRIPT_NAME'], 'fr-mind.inc')) {
    header('location:index.php');
    die();
}

$mind[1] = "Admin - Plugin exemple Devinette";
$mind[2] = "GuppY est fourni avec quelques plugins très simples, à titre d'exemple (pour les développeurs) et d'embellissement de votre site GuppY (pour les webmasters).";
$mind[3] = "Je peux lire dans vos pensées. Vous n'y croyez pas ? Essayez ! Vous n'en reviendrez pas...";
$mind[4] = "Accès par :";
$mind[5] = "Une boîte latérale, à configurer dans";
$mind[6] = "Le voir fonctionner :";
$mind[7] = "Devinette";
$mind[8] = "Boîte Devinette";
$mind[9] = "J'essaye !";
$mind[10] = "La machine à lire dans les pensées";
$mind[11] = "1. Pensez à un nombre à deux chiffres (exemple : 54)";
$mind[12] = "2. Soustrayez chacun de ces deux chiffres (5 et 4) au nombre choisi (exemple : 54 - 5 - 4 = 45)";
$mind[13] = "3. Recherchez ce résultat dans la liste et mémorisez le symbole correspondant";
$mind[14] = "<strong>Concentrez-vous très fort sur ce symbole.</strong> Cliquez ensuite sur le bouton et la machine le devinera !";
$mind[15] = "Devinez mes pensées";
$mind[16] = "Voici le symbole que j'ai lu dans vos pensées :";
$mind[17] = "Redevinez mes pensées";
$mind[18] = 'Désinstaller';
$mind[19] = '<strong>ATTENTION ! La désinstallation est définitive.</strong><br/>';
?>