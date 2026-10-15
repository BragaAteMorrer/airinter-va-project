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

<div style="text-align: center"><span style="font-size: medium">Téléchargement 1ère&#160;période 1982-1997&#160;<br />
<br />
</span></div>
<p style="text-align: center"><font face="Arial">ATTENTION: </font><font face="Arial">Il est IMPERATIF de supprimer les versions précédentes avant l'installation.</font><br />
&#160;<br />
<table border="1" cellspacing="1" cellpadding="1" width="754" style="width: 754px; height: 848px">
    <tbody>
        <tr>
            <td style="text-align: center"><img title="cara12.jpg" alt="cara12.jpg" width="270" height="124" src="photo/cara12.jpg" /></td>
            <td style="text-align: center">&#160;<span style="font-size: small">&#160;Sud-Aviation SE-210 Caravelle 12</span></td>
            <td style="text-align: center"><span style="text-decoration: underline"><span style="font-size: small"><a href="http://www.airinter-va.org/tempo/Caravelle12V4.0.exe">FS2004Ver4.0</a></span></span></td>
        </tr>
        <tr>
            <td style="text-align: center"><img title="dam.jpg" alt="dam.jpg" width="270" height="93" src="photo/dam.jpg" /></td>
            <td style="text-align: center"><span style="font-size: small">&#160;&#160; Dassault Mercure 100<br />
            </span></td>
            <td style="text-align: center"><span style="text-decoration: underline"><span style="font-size: small"><a href="http://www.airinter-va.org/tempo/CatC/MercureFS2004/FS2004MercurePackageVer2.4.exe">FS2004Ver2.4</a><br />
            <br />
            <a href="http://www.airinter-va.org/tempo/CatC/MercureFS2004/MercureVer2.4Patch1.exe">Patch 1</a></span></span></td>
        </tr>
        <tr>
            <td style="text-align: center"><span style="text-decoration: underline"><img title="a300b4.jpg" alt="a300b4.jpg" width="270" height="124" src="photo/a300b4.jpg" /></span></td>
            <td style="text-align: center"><span style="font-size: small">Airbus A-300-B2-1C <br />
            <br />
            Airbus A-300-B4-2C</span></td>
            <td style="text-align: center"><span style="text-decoration: underline"><span style="font-size: small"><a href="http://www.airinter-va.org/tempo/CatE/A300FS2004/A300V3.1.exe">FS2004Ver3.1</a><br />
            <br />
            <a href="http://www.airinter-va.org/tempo/CatE/A300FS2004/A300Ver3.1Patch1.exe">Patch 1</a></span></span></td>
        </tr>
        <tr>
            <td style="text-align: center"><span style="text-decoration: underline"><img title="b747.jpg" alt="b747.jpg" width="270" height="133" src="photo/b747.jpg" /></span></td>
            <td style="text-align: center"><span style="font-size: small">Boeing 747-128A</span></td>
            <td style="text-align: center"><span style="text-decoration: underline"><span style="font-size: x-small"><a href="http://www.airinter-va.org/tempo/CatE/B747/B747V1.0.exe">FS2004Ver1.0</a><br />
            <br />
            <a href="http://www.airinter-va.org/tempo/CatE/B747/B747V1.0PATCH1.exe">Patch 1</a><br />
            <br />
            <a href="http://www.airinter-va.org/tempo/CatE/B747/B747V1.0PATCH2.exe">Patch 2</a><br />
            <br />
            <a href="http://www.airinter-va.org/tempo/CatE/B747/B747TCASSETUP.exe">Setup Tcas</a></span></span></td>
        </tr>
        <tr>
            <td style="text-align: center"><span style="text-decoration: underline"><span style="font-size: x-small"><img title="f100.jpg" alt="f100.jpg" width="270" height="117" src="photo/f100.jpg" /></span></span></td>
            <td style="text-align: center"><span style="font-size: medium">Fokker 100</span></td>
            <td style="text-align: center"><span style="text-decoration: underline"><span style="font-size: small"><a href="http://www.airinter-va.org/tempo/CatD/F100ITF/F100IT2.0.exe">FS2004Ver2.0</a></span></span></td>
        </tr>
        <tr>
            <td style="text-align: center"><span style="text-decoration: underline"><span style="font-size: small"><img title="a320-111.jpg" alt="a320-111.jpg" width="270" height="104" src="photo/a320-111.jpg" /></span></span></td>
            <td style="text-align: center"><span style="font-size: small">Airbus A320-111<br />
            <br />
            Airbus A320-211<br />
            <br />
            Airbus A321-111</span></td>
            <td style="text-align: center"><span style="text-decoration: underline"><span style="font-size: small"><a href="http://www.airinter-va.org/tempo/CatD/PackAirbusA32x/PackAirbusA32xVer1.5.exe">FS2004Ver1.5</a><br />
            <br />
            <a href="http://www.airinter-va.org/tempo/CatD/PackAirbusA32x/PackAirbusA32xVer1.5PATCH1.exe">Patch 1</a></span></span></td>
        </tr>
        <tr>
            <td style="text-align: center"><span style="text-decoration: underline"><span style="font-size: small"><img title="a330.jpg" alt="a330.jpg" width="270" height="114" src="photo/a330.jpg" /></span></span></td>
            <td style="text-align: center"><span style="font-size: small">Airbus A330-301</span></td>
            <td style="text-align: center"><span style="text-decoration: underline"><span style="font-size: small"><a href="http://www.airinter-va.org/tempo/CatE/A300FS2004/AirInterA330301Ver1.5.exe">FS2004Ver1.5</a><br />
            <br />
            <a href="http://www.airinter-va.org/tempo/CatE/A300FS2004/AirInterA330301Ver1.5PATCH1.exe">Patch 1</a><br />
            <br />
            <a href="http://www.airinter-va.org/tempo/CatE/A300FS2004/AirInterA330301Ver1.5PATCH2.exe">Patch 2</a></span></span></td>
        </tr>
    </tbody>
</table>
</p>

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
