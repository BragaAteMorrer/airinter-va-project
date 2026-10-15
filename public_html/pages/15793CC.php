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

<div style="text-align: center"><span style="font-size: medium"><strong>Téléchargement ICS - Charter<br />
<br />
</strong><font size="2">ATTENTION: <font face="Arial">Il est IMPERATIF de supprimer les versions précédentes avant installation.</font></font><br />
<br />
</span></div>
<table border="1" cellspacing="1" cellpadding="1" width="769" style="width: 769px; height: 507px">
    <tbody>
        <tr>
            <td><img title="b737_ics.jpg" alt="b737_ics.jpg" width="230" height="133" src="photo/b737_ics.jpg" /><br />
            <img title="vanguard.jpg" alt="vanguard.jpg" width="257" height="133" src="photo/vanguard.jpg" /><br />
            <img title="hercules.jpg" alt="hercules.jpg" width="270" height="117" src="photo/hercules.jpg" /></td>
            <td style="text-align: center"><span style="font-size: medium">Pack ICS Version 2.0&#160;<br />
            <br />
            </span><span style="font-size: small">B-737-2Q2C<br />
            Vickers Vanguard 953C<br />
            Lockheed L-100-30 HerculesCargo</span></td>
            <td style="text-align: center"><u><span style="font-size: small"><a href="http://www.airinter-va.org/tempo/ICS/PackICSVer2.0.exe">FS2004&#160;Ver 2.0</a></span></u></td>
        </tr>
        <tr>
            <td style="text-align: left"><img title="b727_aci.jpg" alt="b727_aci.jpg" width="270" height="111" src="photo/b727_aci.jpg" /></td>
            <td style="text-align: center"><span style="font-size: medium">Boeing 727-200 ACI<br />
            <br />
            <font size="2">Si vous possédez le B737 de Dreamfleet<br />
            téléchargez la version merge dreemfleet</font></span></td>
            <td style="text-align: center"><span style="font-size: small"><u><br />
            <a href="http://www.airinter-va.org/tempo/charter/ACIBoeing727200ADVCVer1.0.exe">FS2004 Ver 1.0</a><br />
            <br />
            <a href="http://www.airinter-va.org/tempo/charter/ACIBoeing727200ADVCDreamfleetmergeVer1.0.exe">FS2004 Ver 1.0 merge dreamfleet</a><br />
            <br />
            </u></span></td>
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
