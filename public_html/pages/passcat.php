<?php
/*
    Sample External Page - GuppY PHP Script - version 4.6
    CeCILL Copyright (C) 2004-2007 by Laurent Duveau
    Initiated by Laurent Duveau and Nicolas Alves
      Web site = http://www.freeguppy.org/
      e-mail   = info@freeguppy.org

    This is a sample external PHP script that integrates with GuppY

    Version History :
      v2.3 (27 July 2003)        : initial release (by Isabelle)
      v3.0 (25 February 2004)    : moved to pages/ directory
      v4.0 (06 December 2004)    : Added text in many languages (by Jean-Mi)
	    v4.5 (27 April 2005)       : Added page title (thanks Ricsen)
	    v4.6.0 (20 november 2006)  : corrected $topmess in htable() (by Ghazette)
	    v4.6.6 (14 April 2008)     : corrected bad position of include hpage.inc (by Icare)
*/

header("Pragma: no-cache");
define("CHEMIN", "../");
include(CHEMIN."inc/includes.inc");
if ($lng == $lang[0]) {
  $topmess = "Titre de la fenêtre en langue principale";
}
else {
  $topmess = "Window title in secondary language";
}
include(CHEMIN."inc/hpage.inc");

// Si vous voulez mettre une icône du répertoire img en tête de page:
// If you want to put an icon from img directory on top of the page:
$topmess = '<img src="'.CHEMIN.'img/logo022.gif" align="right" width="32" height="32" border="0" alt="My icon">'.$topmess;
if ($lng == $lang[0]) {
htable($topmess, "100%"); // Titre en langue principale
?>

<!-- Début du texte HTML dans la langue principale -->

<x-sas-window top="52" right="538" left="8" bottom="649"></x-sas-window><x-sas-remotesave file="" user="danielea86" server="perso-ftp.wanadoo.fr"></x-sas-remotesave>
<meta content="edo friart" name="Author" /><script language="JavaScript"> 
<!-- 
 
                     function checkPswd(ext) 
 
                     { 
 
                     if ((document.pswdForm.pswd.value == null) || (document.pswdForm.pswd.value == '')) 
 
                     alert('Mot de passe invalide.'); 
 
                     else this.location.href = document.pswdForm.pswd.value + ext; 
 
                     } 
 
                     //-->
</script><!--[if gte mso 9]><xml>
 <o:shapelayout v:ext="edit">
  <o:idmap v:ext="edit" data="1"/>
 </o:shapelayout></xml><![endif]-->
<div class="Section1">
<form name="pswdForm">
    <div id="Layer6" style="z-index: 6; position: absolute; top: 506px; left: 30px">
    <p class="MsoNormal">&#160;</p>
    </div>
    <div id="Layer1" style="z-index: 8; position: absolute; top: 42px; left: 100px">
    <p class="MsoNormal">&#160;</p>
    </div>
    <p align="center" style="text-align: center">&#160;</p>
    <p align="center" style="text-align: center">&#160;</p>
    <p align="center" style="text-align: center">&#160;</p>
    <p align="center" style="text-align: center"><b><i><span style="font-size: 24pt">Entrez votre code <span class="GramE">d' accès</span> et cliquez sur OK</span></i></b><i><span style="font-size: 24pt"> </span></i></p>
    <p align="center" style="text-align: center">&#160;</p>
    <p align="center" style="text-align: center"><input maxlength="40" size="24" type="password" name="pswd" />&#160; <o:p></o:p><input type="button" onClick="checkPswd('.php')" name="button" value="Ok" /></p>
    <p>&#160;<o:p>&#160;</o:p></p>
    <p align="center" style="text-align: center">&#160;</p>
    <p align="center" style="text-align: center"><img id="_x0000_i1025" border="0" alt="" width="160" height="101" src="img/Cadena.gif" /></p>
    <p>&#160;</p>
</form>
</div>

<!-- Fin du texte HTML en langue principale -->

<?php
}
else {
htable($topmess, "100%"); // Titre en langue secondaire
?>

<!-- Début du texte HTML dans la langue secondaire -->

<br />
Texte HTML dans la langue secondaire <br />
Text HTML in the secondary language <br />
HTML-Text in der sekundären Sprache <br />
Texto HTML en la lengua secundaria <br />
Testo HTML nella lingua secondaria <br />
Tekst HTML in de ondergeschikte taal <br />
Texto HTML na língua secundária <br />
<br />

<!-- Fin du texte HTML en langue secondaire -->

<?php
}
btable();
include(CHEMIN."inc/bpage.inc");
?>
