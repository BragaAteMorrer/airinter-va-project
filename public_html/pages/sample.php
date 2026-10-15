<?php
/*******************************************************************************
 *   Sample External Page
 *******************************************************************************
 *   GuppY PHP Script - version 5.0
 *   CeCILL Copyright (C) 2004-2014 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = http://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V5 developed by Lud Bienaimé
 *      with the participation of Jean-Michel Misrachi and the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v5.0.0 (February 15, 2014) : initial release.
 ******************************************************************************/

header('Pragma: no-cache');
define('CHEMIN', '../');
include CHEMIN.'inc/includes.inc';

if ($lng == $lang[0]) {
    $topmess = 'Titre de la fenêtre en langue principale';
}
else {
    $topmess = 'Window title in secondary language';
}

include CHEMIN.'inc/hpage.inc';

// Si vous voulez mettre une icône du répertoire img en tête de page:
// If you want to put an icon from img directory on top of the page:
$topmess = '<img src="'.CHEMIN.'img/my_icon.gif" style="float:right;width:32px;height:32px;" alt="My icon">'.$topmess;

htable($topmess, "100%");

if ($lng == $lang[0]) {
    echo '
<!-- Début du texte HTML dans la langue principale -->

<br />
Texte HTML dans la langue principale <br />
Text HTML in the principal language <br />
HTML-Text in der Hauptsprache <br />
Texto HTML en la lengua principal <br />
Testo HTML nella lingua principale <br />
Tekst HTML in de belangrijkste taal <br />
Texto HTML na língua principal <br />
<br />

<!-- Fin du texte HTML en langue principale -->
';
}
else {
    echo '
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
';
}
btable();
include CHEMIN.'inc/bpage.inc';
?>