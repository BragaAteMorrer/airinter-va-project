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

<div style="text-align: center"><span style="font-size: medium">Téléchargement 3ème&#160;période 1954-1969&#160;<br />
<br />
</span></div>
<p style="text-align: center"><font face="Arial">ATTENTION: </font><font face="Arial">Il est IMPERATIF de supprimer les versions précédentes avant d'installer cette version.<br />
</font></p>
<table border="1" cellspacing="1" cellpadding="1" width="777" style="width: 777px; height: 890px">
    <tbody>
        <tr>
            <td style="text-align: center"><img title="dc3.jpg" alt="dc3.jpg" width="270" height="127" src="photo/dc3.jpg" /></td>
            <td style="text-align: center"><span style="font-size: small">Douglas DC-3</span></td>
            <td style="text-align: center"><u><span style="font-size: small"><a href="http://www.airinter-va.org/tempo/CatA/DC3FS2004/AirInterDC3ver3.5.exe">FS2004Ver3.5</a></span></u></td>
        </tr>
        <tr>
            <td style="text-align: center"><u><span style="font-size: small"><img title="viking.jpg" alt="viking.jpg" width="270" height="120" src="photo/viking.jpg" /></span></u></td>
            <td style="text-align: center"><span style="font-size: small">Vickers Viking</span></td>
            <td style="text-align: center"><u><span style="font-size: small"><a href="http://www.airinter-va.org/tempo/CatA/VickersVikingFS2004/AirInterViking2.0.exe">FS2004Ver2.0</a></span></u></td>
        </tr>
        <tr>
            <td style="text-align: center"><u><span style="font-size: small"><img title="dc6.jpg" alt="dc6.jpg" width="264" height="133" src="photo/dc6.jpg" /></span></u></td>
            <td style="text-align: center"><span style="font-size: small">Douglas DC-4<br />
            <br />
            Douglas DC-6B</span></td>
            <td style="text-align: center"><u><span style="font-size: small"><a href="http://www.airinter-va.org/tempo/CatA/DC4FS2004/PackDouglasDCseriesVer4.1.exe">FS2004Ver4.1</a></span></u></td>
        </tr>
        <tr>
            <td style="text-align: center"><u><span style="font-size: small"><img title="l749.jpg" alt="l749.jpg" width="270" height="112" src="photo/l749.jpg" /></span></u></td>
            <td style="text-align: center"><span style="font-size: small">Loockheed L-749 Constellation<br />
            <br />
            Loockheed L-1049 Super Constellation</span></td>
            <td style="text-align: center"><u><span style="font-size: small"><a href="http://www.airinter-va.org/tempo/CatB/L749/PackLockheedLseriesVer4.0.exe">FS2004Ver4.0</a></span></u></td>
        </tr>
        <tr>
            <td style="text-align: center"><u><span style="font-size: small"><img title="vsc724.jpg" alt="vsc724.jpg" width="270" height="128" src="photo/vsc724.jpg" /></span></u></td>
            <td style="text-align: center"><span style="font-size: small">Vickers Viscount 708<br />
            <br />
            Vickers Viscount 724</span></td>
            <td style="text-align: center"><u><span style="font-size: small"><a href="http://www.airinter-va.org/tempo/CatB/VickersViscountFS04/AirInterViscount708724FS2004V3.5.exe">FS2004Ver3.5</a><br />
            <br />
            <a href="http://www.airinter-va.org/tempo/CatB/VickersViscountFS04/AirInterViscount708724Ver3.5PATCH1.exe">Patch 1</a><br />
            <br />
            <a href="http://www.airinter-va.org/tempo/CatB/VickersViscountFS04/Viscount708724PATCH2.exe">Patch 2</a></span></u></td>
        </tr>
        <tr>
            <td style="text-align: center"><u><span style="font-size: small"><img title="n262.jpg" alt="n262.jpg" width="270" height="98" src="photo/n262.jpg" /></span></u></td>
            <td style="text-align: center"><span style="font-size: small">Aérospatiale Nord 262-B Frégate</span></td>
            <td style="text-align: center"><u><span style="font-size: small"><a href="http://www.airinter-va.org/tempo/CatC/Nord262FS2004/AirInterNord262BVer3.0.exe">FS2004Ver3.0</a><br />
            <br />
            <a href="http://www.airinter-va.org/tempo/CatC/Nord262FS2004/AirInterNord262BVer3.0Patch1.exe">Patch 1</a></span></u></td>
        </tr>
        <tr>
            <td style="text-align: center"><u><span style="font-size: small"><img title="f27.jpg" alt="f27.jpg" width="244" height="133" src="photo/f27.jpg" /></span></u></td>
            <td style="text-align: center"><span style="font-size: small">Fokker F-27-200<br />
            <br />
            Fokker F-27-500</span></td>
            <td style="text-align: center"><u><span style="font-size: small"><a href="http://www.airinter-va.org/tempo/CatC/Fokker27FS2004/Fokker27Ver2.0.exe">FS2004Ver2.0</a></span></u></td>
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
