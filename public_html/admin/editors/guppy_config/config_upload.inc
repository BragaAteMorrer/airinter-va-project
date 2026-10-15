<?php
/*******************************************************************************
 *   Upload réalisé par Djchouix - Licence CeCILL
 *   Web site = http://lebrikabrak.free.fr/
 *   e-mail   = lebrikabrak@free.fr
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
 *   v6.00.18 (February 11, 2024) : add avif type and control Php version
 ******************************************************************************/

if (stristr($_SERVER["SCRIPT_NAME"], "config_upload.inc")) {
	header("location:../index.php");
	die();
}
$pathRepUpload ='admin/editors/';  //chemin relatif du répertoire Upload ( à modifier si vous voulez placer ce répertoire ailleurs Ici)


if(FileDBExist(CHEMIN.'admin/'.REDACREP.$userprefs[1].INCEXT) && $drtuser[36] == "") {
    include CHEMIN.'admin/'.REDACREP.$userprefs[1].INCEXT;
	$allowedUpload = false;  //Autorisation (TRUE) ou NON (FALSE) pour Upload
	$allowedCreateRep = false;  //Autorisation (TRUE) ou NON (FALSE) pour la Création de Répertoire
	$allowedRenameRepFile = false;  //Autorisation (TRUE) ou NON (FALSE) pour Renommer des Répertoires ou des Fichiers
	$allowedDeleteRepFile = false;  //Autorisation (TRUE) ou NON (FALSE) pour la Suppression des Répertoires ou des Fichiers
} else {
	$allowedUpload = true;  //Autorisation (TRUE) ou NON (FALSE) pour Upload
	$allowedCreateRep = true;  //Autorisation (TRUE) ou NON (FALSE) pour la Création de Répertoire
	$allowedRenameRepFile = true;  //Autorisation (TRUE) ou NON (FALSE) pour Renommer des Répertoires ou des Fichiers
	$allowedDeleteRepFile = true;  //Autorisation (TRUE) ou NON (FALSE) pour la Suppression des Répertoires ou des Fichiers
}

$allowedExtFileUpload = array();  //Extensions des fichiers UNIQUEMENT autorisés à être uploadés (laisser vide pour autoriser toutes les extensions)
$deniedExtFileUpload = array();  //Extensions des fichiers NON autorisés à être uploadés (laisser vide pour autoriser toutes les extensions)


$accessRepUpload = array('file','img','photo','pages'); //Nom des répertoires à la racine de Guppy accessibles pour le fichier Upload	(création si nécessaire)
                                                        //Attention: pour les noms de répertoire, seuls les caractères alphanumériques (a-z A-Z 0-9) ainsi que les caractères (-  _)	sont autorisés et vous ne devez pas mettre d'espace.
$accessRepUploadImage = array('img','skins','photo'); //Nom des répertoires à la racine de Guppy accessibles via le menu pour l'insertion d'Images.(accès avec variable $uptype='Image')
                                              //Attention: vous devez choisir les répertoires uniquement parmi la liste des répertoires contenus dans la variable $accessRepUpload ci-dessus
$accessRepUploadLink = array('file'); //Nom des répertoires à la racine de Guppy accessibles via le menu pour l'insertion de Liens (accès avec variable $uptype='Link')
                                      //Attention: vous devez choisir les répertoires uniquement parmi la liste des répertoires contenus dans la variable $accessRepUpload ci-dessus
$accessRepUploadMedia = array('file','photo');	//Accès aux répertoires à la racine de Guppy pour les fichiers Audio et Video (accès avec variable $uptype='Media')
                                        //Attention: vous devez choisir les répertoires uniquement parmi la liste des répertoires contenus dans la variable $accessRepUpload ci-dessus
if (version_compare(PHP_VERSION, '8.1.0') >= 0 && version_compare(PHP_VERSION, '8.2.0') < 0) {
	$allowedExtImage = array('.jpg','.gif','.png','.bmp','.jpeg','.webp','.avif','.swf','.fla','.JPG','.GIF','.PNG','.BMP','.JPEG','.SWF','.FLA');  //Extensions UNIQUEMENT autorisées pour récupérer URL des Images (accès avec variable $uptype='Image')
}
else
$allowedExtImage = array('.jpg','.gif','.png','.bmp','.jpeg','.webp','.swf','.fla','.JPG','.GIF','.PNG','.BMP','.JPEG','.SWF','.FLA');  //Extensions UNIQUEMENT autorisées pour récupérer URL des Images (accès avec variable $uptype='Image')

$deniedExtLink = array();  //Extensions NON autorisées pour récupérer URL des Liens (laisser vide pour autoriser toutes les extensions) (accès avec variable $uptype='Link')
$allowedExtMedia = array('.jpg','.gif','.png','.bmp','.jpeg','.JPG','.GIF','.PNG','.BMP','.JPEG','.mp3','.mp4','.MP3','.MP4'); //Extensions UNIQUEMENT autorisées pour récupérer URL des fichiers Audio et Video (accès avec variable $uptype='Media')

//STYLE CSS DE LA POPUP UPLOAD
$colorTextTitre = '#000000';  //couleur du titre
$colorFondTitre = '#7F7F7F';  //couleur de fond du titre
$styleBordureTitre = '1px solid #7F7F7F';  //bordure du titre
$colorTextCorp = '#000000';   //couleur du texte
$colorFondCorp = '#EFEFEF';   //couleur du fond de page
$styleBordureCorp = '1px solid #7F7F7F';  //bordure du corps
$colorBodyUpload = '#EFEFEF';  //couleur de fond de page (popup upload)
$colorFondFileUpload = '#EFEFEF'; //couleur du fond du tableau (popup upload)
$colorFondFileUploadOver = '#E7E1EF';  //couleur de fond des fichiers lors du survol de la souris (popup upload)
$colorFileUploadOff = '#000000';  //couleur des liens off (popup upload)
$colorFileUploadOn = '#FF0000';  //couleur des liens on (popup upload)
?>