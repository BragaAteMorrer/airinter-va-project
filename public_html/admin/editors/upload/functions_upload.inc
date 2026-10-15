<?php
/*******************************************************************************
 *   MinieditorTextarea réalisé par Djchouix - Licence CeCILL
 *   Web site = http://lebrikabrak.free.fr/
 *   e-mail   = lebrikabrak@free.fr
 *******************************************************************************
 *   GuppY PHP Script - version 6
 *   CeCILL Copyright (C) 2004-2020 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = http://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V5 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.06 (January 13, 2022) : update ckeditor
 ******************************************************************************/

if (stristr($_SERVER["SCRIPT_NAME"], "functions_upload.inc")) {
	header("location:../index.php");
	die();
}

//Fonction pour contrôler et corriger le nom d'un répertoire ou d'un fichier
function cleanName($name,$up=false) {
	$nameclean = trim($name);
	$nameclean = str_replace(' ','_',$nameclean);
	$nameclean = str_replace('à','a',$nameclean);
	$nameclean = preg_replace('![éèê]!','e',$nameclean);
	$nameclean = preg_replace('![îï]!','i',$nameclean);
	$nameclean =($up)? preg_replace('![^-a-zA-Z0-9_\.]!','',$nameclean) : preg_replace('![^-a-zA-Z0-9_]!','',$nameclean);
	return $nameclean;
}

//Fonction pour créer un répertoire accessible à l'upload s'il n'existe pas
function createNewRep($newrep) {
    MakeDir(CHEMIN.$newrep);
	SetChmod(CHEMIN.$newrep);
	@copy(CHEMIN."admin/inc/index.php", CHEMIN.$newrep."/index.php");
	SetChmod(CHEMIN.$newrep."/index.php");
};

//Fonction pour controler la validité des répertoires accessible à l'upload
function controlNameRepUpload($nameRepUp) {
	$nbRepUp = count($nameRepUp);
	for ($i=0; $i<$nbRepUp; $i++) {
		if($nameRepUp[$i] == '' || !preg_match('!^[-a-z0-9_]+$!i',$nameRepUp[$i]) ) {
			unset($nameRepUp[$i]);
		} else {
			if ($nameRepUp[$i] == "0" ) {$nameRepUp[$i] = "0_";}
			if(!is_dir(CHEMIN.$nameRepUp[$i])) createNewRep($nameRepUp[$i]);
		}
	}
	if(count($nameRepUp) > 0) {
		$nameRepUp = array_values($nameRepUp);
		return $nameRepUp;
	} else {
		return die('Erreur dans la variable $accessRepUpload..Veuillez vérifier qu\'elle n\'est pas vide ou que les noms des répertoires sont écrits correctement.');
	}
}
/*
$accessRepUpload = array('file','img','photo','pages','skins');	//Nom des répertoires à la racine de Guppy accessibles pour le fichier Upload	(création si nécessaire) 
                                                                //Attention: pour les noms de répertoire, seuls les caractères alphanumériques (a-z A-Z 0-9) ainsi que les caractères (-  _)	sont autorisés et vous ne devez pas mettre d'espace.	
$accessRepUploadImage = array('img','photo','skins'); //Nom des répertoires à la racine de Guppy accessibles via le menu pour l'insertion d'Images.(accès avec variable $uptype='Image')
                                                      //Attention: vous devez choisir les répertoires uniquement parmi la liste des répertoires contenus dans la variable $accessRepUpload ci-dessus
$accessRepUploadLink = array('file','img','photo','pages');	//Nom des répertoires à la racine de Guppy accessibles via le menu pour l'insertion de Liens (accès avec variable $uptype='Link')
                                                            //Attention: vous devez choisir les répertoires uniquement parmi la liste des répertoires contenus dans la variable $accessRepUpload ci-dessus
$accessRepUploadMedia = array('img','photo'); //Accès aux répertoires à la racine de Guppy pour les fichiers Audio et Video (accès avec variable $uptype='Media')
                 
$accessRepUpload = controlNameRepUpload($accessRepUpload);
$accessRepUploadImage = controlAccessRepUpload($accessRepUploadImage,$accessRepUpload,'Image');
$accessRepUploadLink = controlAccessRepUpload($accessRepUploadLink,$accessRepUpload,'Link');
$accessRepUploadMedia = controlAccessRepUpload($accessRepUploadMedia,$accessRepUpload,'Media');
*/
//Fonction pour controler la validité des répertoires accessible à l'upload
function controlAccessRepUpload($accessRepUploadType,$accessRepUpload,$type) {
	$nbRepUp = count($accessRepUploadType);
	for ($i=0; $i<$nbRepUp; $i++) {
		if ($accessRepUploadType[$i] == "0" ) {$accessRepUploadType[$i] = "0_";}
		if(!in_array($accessRepUploadType[$i],$accessRepUpload) || $accessRepUploadType[$i] == '' || !preg_match('!^[-a-z0-9_]+$!i',$accessRepUploadType[$i]) ) unset($accessRepUploadType[$i]);
	}
	if(count($accessRepUploadType) > 0) {
		$accessRepUploadType = array_values($accessRepUploadType);
		return $accessRepUploadType;
	} else {
		return die('Erreur dans la variable $accessRepUpload'.$type.'.Veuillez vérifier qu\'elle n\'est pas vide ou que les noms des répertoires sont écrits correctement.');
	}
}

//Fonction qui permet de supprimer des sous-répertoires
function DelSubRep($sous_dossier) { 
	$d = opendir($sous_dossier); 
	while ($f = readdir($d)) {
        if(is_file($sous_dossier.$f)) {
            @unlink($sous_dossier.$f);
		}	
        if($f != '.' && $f != '..' && is_dir($sous_dossier.$f)) {
		    DelSubRep($sous_dossier.$f.'/');
            @rmdir($sous_dossier.$f);			 
		}
	}
	closedir($d);
}

?>