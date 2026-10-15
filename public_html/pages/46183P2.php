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

<br />

<div style="text-align: center"><span style="font-size: medium">Téléchargement 2ème&#160;période 1970-1981&#160;<br />
<br />
</span></div>
<p style="text-align: center"><font face="Arial">ATTENTION: </font><font face="Arial">Il est IMPERATIF de supprimer les versions précédentes avant l'installation.<br />
<br />
</font></p>
<table border="1" cellspacing="1" cellpadding="1" width="696" style="width: 696px; height: 634px">
    <tbody>
        <tr>
            <td style="text-align: center"><img title="f27.jpg" alt="f27.jpg" width="244" height="133" src="photo/f27.jpg" /></td>
            <td style="text-align: center"><span style="font-size: small">Fokker F-27-200<br />
            <br />
            Fokker F-27-500</span></td>
            <td style="text-align: center"><span style="text-decoration: underline"><span style="font-size: small"><a href="http://www.airinter-va.org/tempo/CatC/Fokker27FS2004/Fokker27Ver2.0.exe">FS2004Ver2.0</a></span></span></td>
        </tr>
        <tr>
            <td style="text-align: center"><img title="vsc708.jpg" alt="vsc708.jpg" width="270" height="133" src="photo/vsc708.jpg" /></td>
            <td style="text-align: center"><span style="font-size: small">Vickers Viscount 708<br />
            <br />
            Vickers Viscount 724</span></td>
            <td style="text-align: center"><span style="text-decoration: underline"><span style="font-size: small"><a href="http://www.airinter-va.org/tempo/CatB/VickersViscountFS04/AirInterViscount708724FS2004V3.5.exe">FS2004Ver3.5</a><br />
            <br />
            <a href="http://www.airinter-va.org/tempo/CatB/VickersViscountFS04/AirInterViscount708724Ver3.5PATCH1.exe">Patch 1</a><br />
            <br />
            <a href="http://www.airinter-va.org/tempo/CatB/VickersViscountFS04/Viscount708724PATCH2.exe">Patch 2</a></span></span></td>
        </tr>
        <tr>
            <td style="text-align: center"><img title="cara3.jpg" alt="cara3.jpg" width="270" height="110" src="photo/cara3.jpg" /></td>
            <td style="text-align: center"><font size="3">Sud-Aviation&#160;Caravelle VI-R<br />
            <br />
            &#160; Sud-Aviation&#160;Caravelle&#160;III</font></td>
            <td style="text-align: center"><span style="text-decoration: underline"><span style="font-size: small"><a href="http://www.airinter-va.org/tempo/CaravelleIIIVIRV4.0.exe">FS2004Ver4.0</a></span></span></td>
        </tr>
        <tr>
            <td style="text-align: center"><img title="dam.jpg" alt="dam.jpg" width="270" height="93" src="photo/dam.jpg" /></td>
            <td style="text-align: center"><font size="3">Dassault Mercure 100</font><br />
            &#160;</td>
            <td style="text-align: center"><span style="text-decoration: underline"><span style="font-size: small"><a href="http://www.airinter-va.org/tempo/CatC/MercureFS2004/FS2004MercurePackageVer2.4.exe">FS2004Ver2.4</a><br />
            <br />
            <a href="http://www.airinter-va.org/tempo/CatC/MercureFS2004/MercureVer2.4Patch1.exe">Patch 1</a></span></span></td>
        </tr>
        <tr>
            <td style="text-align: center"><img title="a300b2.jpg" alt="a300b2.jpg" width="252" height="133" src="photo/a300b2.jpg" /></td>
            <td style="text-align: center"><span style="font-size: small">Airbus A-300-B2-1C <br />
            <br />
            Airbus A-300-B4-2C</span></td>
            <td style="text-align: center"><span style="text-decoration: underline"><span style="font-size: small"><a href="http://www.airinter-va.org/tempo/CatE/A300FS2004/A300V3.1.exe">FS2004Ver3.1</a><br />
            <br />
            <a href="http://www.airinter-va.org/tempo/CatE/A300FS2004/A300Ver3.1Patch1.exe">Patch 1</a></span></span></td>
        </tr>
    </tbody>
</table>

<br />

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
