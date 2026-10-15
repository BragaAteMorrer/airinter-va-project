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
*   v6.00.16 (December 14, 2023) : Integration audio and video files
 ******************************************************************************/

if (stristr($_SERVER["SCRIPT_NAME"], "jscript_upload.inc")) {
	header("location:../index.php");
	die();
}
?>
//Récupération de l'URL du fichier
function openURL(url,type,alt,width,height) {
	//Icon and banner
	if (type == 'img') {
	   url = url.replace(/^.+\/img\//,'');
              id = window.name;
	    window.opener.document.getElementById(id).value = url;
	}
	//Download
	if (type == 'file') {
      url = url.replace(/^.+\/file\//,'file/');
              id = window.name;
	    window.opener.document.getElementById(id).value = url;
	}
	//audio Video
    if (type == 'media') {
		url = url.replace(/^.+\/photo\/gal_/,'');
             id = window.name;
	    window.opener.document.getElementById(id).value = url;
	}
    window.close();
}
