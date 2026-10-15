<?php
/*******************************************************************************
 *   MinieditorTextarea réalisé par Djchouix - Licence CeCILL
 *   Web site = http://lebrikabrak.free.fr/
 *   e-mail   = lebrikabrak@free.fr
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2020 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = http://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V5 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.00 (December 15, 2020) : initial release
 ******************************************************************************/

if (stristr($_SERVER["SCRIPT_NAME"], "fr_upload.inc")) {
  header("location:../index.php");
  die();
}

$lang_upload[1] = "Echec de la création car un répertoire portant le même nom existe déjà.<br /><br />Veuillez recommencer en changeant le nom du répertoire à créer";
$lang_upload[2] = "Echec car un fichier portant le même nom existe déjà.<br /><br />Veuillez recommencer en changeant le nom du fichier";
$lang_upload[3] = "Echec car un répertoire portant le même nom existe déjà.<br /><br />Veuillez recommencer en changeant le nom du répertoire";
$lang_upload[4] = "Echec de la suppression du répertoire car le serveur ne l'autorise pas.<br /><br />Veuillez le supprimer en utilisant votre logiciel FTP.";
$lang_upload[5] = "Echec de l'upload car ce n'est pas un fichier de la forme \"nom_du_fichier.ext\" ";
$lang_upload[6] = "Echec de l'upload car un fichier portant le même nom existe déjà.<br /><br />Veuillez recommencer en changeant le nom du fichier à uploader";
$lang_upload[7] = "C'est ici que vous gérez les fichiers et les répertoires sur votre site.<br /><i class='fas fa-search-location' style='font-size: 1.1rem;' title='Ouvrir'></i> : permet d'ouvrir le répertoire<br /><i class='far fa-eye text-info align-middle' title='Voir'></i> : permet de visualiser le fichier.<br /><i class='fas fa-edit align-middle' title='Renommer'></i> : permet de renommer le fichier ou le répertoire<br /><i class='fas fa-times text-danger align-middle' title='Supprimer'></i> : permet de supprimer le fichier ou le répertoire.<br />";
$lang_upload[8] = "Créer un répertoire";
$lang_upload[9] = "Nom du répertoire";
$lang_upload[10] = "Créer";
$lang_upload[11] = 'Cliquer ici pour afficher l\'aide';
$lang_upload[12] = 'Cliquer ici pour masquer l\'aide';
$lang_upload[13] = "Charger un fichier";
$lang_upload[14] = "Répertoire";
$lang_upload[15] = "Fichier";
$lang_upload[16] = "Taille (octet)";
$lang_upload[17] = "Action";
$lang_upload[18] = "Charger sur le site";
$lang_upload[19] = "Pour récupérer l'adresse (URL) du fichier, veuillez cliquez sur son nom.";
$lang_upload[20] = 'Vous devez remplir le champ';
$lang_upload[21] = "Vous n'êtes pas autoriser à uploader ce type de fichier. Veuillez contacter l'administrateur du site.";
$lang_upload[22] = 'Aperçu';
$lang_upload[23] = 'Vous êtes sur le point de supprimer le fichier';
$lang_upload[24] = 'Vous êtes sur le point de supprimer le répertoire';
$lang_upload[25] = 'ainsi que tout son contenu (fichiers et sous-répertoires)';
$lang_upload[26] = 'Attention, la suppression est irréversible.\n Veuillez confirmer cette suppression pour qu\'elle devienne effective.';
$lang_upload[27] = 'Veuillez rentrez le nouveau nom du fichier';
$lang_upload[28] = 'taper ici le nouveau nom du fichier sans l\'extension';
$lang_upload[29] = 'Veuillez rentrez le nouveau nom du répertoire';
$lang_upload[30] = 'taper ici le nouveau nom du répertoire';
$lang_upload[31] = 'Attention, vous devez utiliser uniquement des caractères non accentués et/ou des chiffres et/ou des caractères(- _) sans espace dans le nom.';
$lang_upload[32] = "Ouvrir";
$lang_upload[33] = "Retour";
$lang_upload[34] = "Répertoire Parent";
$lang_upload[35] = "Voir";
$lang_upload[36] = "Renommer";
$lang_upload[37] = "Supprimer";
$lang_upload[38] = "Sélectionner";
?>
