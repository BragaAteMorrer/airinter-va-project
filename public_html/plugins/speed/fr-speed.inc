<?php
/*******************************************************************************
 *   Plugin Speed
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

if (stristr($_SERVER['SCRIPT_NAME'], 'fr-speed.inc')) {
    header('location:index.php');
    die();
}

$speed[1] = "Admin - Plugin exemple Vitesse de Connexion";
$speed[2] = "GuppY est fourni avec quelques plugins très simples, à titre d'exemple (pour les développeurs) et d'embellissement de votre site GuppY (pour les webmasters).";
$speed[3] = "Ce test fait une évaluation en calculant le débit moyen pour charger une image depuis votre site web vers votre PC.";
$speed[4] = "Accès par :";
$speed[5] = "Ce plugin s'inscrit dans la barre de modules haute.";
$speed[6] = "Le voir fonctionner :";
$speed[7] = "Vitesse de Connexion";
$speed[8] = "Inscrire le Plugin Vitesse de Connexion dans la barre de modules : ";
$speed[9] = "Evaluation en cours...";
$speed[10] = "Connexion impossible";
$speed[11] = "Connexion Modem 28k";
$speed[12] = "Connexion Modem 56k";
$speed[13] = "Connexion Haut débit";
$speed[14] = "Connexion locale";
$speed[15] = "Ko/sec";
$speed[16] = "Evaluation de la vitesse de connexion de votre PC à ce site";
$speed[17] = "<p>Cliquez sur le bouton pour évaluer la vitesse de votre connexion entre votre PC et ce site web.</p><p>Ce test ne fait qu'une évaluation en calculant le débit moyen pour charger une image depuis le site vers votre PC.</p><p>Le résultat mesure donc la vitesse de chargement d'une image à un moment donné. Evidemment, si d'autres chargements sont en cours, la mesure est faussée.</p>";
$speed[18] = "Lancer l'évaluation de la vitesse de connexion";
$speed[19] = "Résultat :";
$speed[20] = 'Désinstaller';
$speed[21] = '<strong>ATTENTION ! La désinstallation est définitive.</strong><br/>';
?>