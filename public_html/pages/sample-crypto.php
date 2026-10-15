<?php
/*******************************************************************************
 *   Sample External Page with captcha
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
 * v5.02.05 (April 24, 2018)     : removing sessions and captcha image
 ******************************************************************************/

header("Pragma: no-cache");
define("CHEMIN", "../");
include CHEMIN.'inc/includes.inc';

$crypt   = isset($_POST['crypt']) ? strip_tags($_POST['crypt']) : NULL;
$cryptok = (!$crypt) ? false : chk_crypt($crypt);

$validForm = import('validForm');

if ($validForm) { // on a validé le formulaire
    if ($serviz[100] != '0' && ($userprefs[1] == '' || $serviz[98] == '')) {
        // le code n'est pas valide
        echo BeginJavascript().'
    alert("'.addslashes($web[265]).'\n\n - '.addslashes($web[529]).'");
    '.EndJavascript();
    } else {
        // le code est valide
        /* mettre ici les opérations de traitement suite à la validation du formulaire
         * vérifications des valeurs,
         * mails à l'administrateur,
         * mise à jour d'une base...
         */
    }
}

if ($lng == $lang[0]) {
  $topmess = "Titre de la fenêtre en langue principale";
}
else {
  $topmess = "Window title in secondary language";
}
include CHEMIN.'inc/hpage.inc';

// Si vous voulez mettre une icône du répertoire img en tête de page:
// If you want to put an icon from img directory on top of the page:
$topmess = '<img src="'.CHEMIN.'img/my_icon.gif" style="float:right;width:32px;height:32px;" alt="My icon">'.$topmess;
htable($topmess, "100%"); // Titre en langue principale

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

<!-- Fin du texte HTML en langue principale -->';

	// catpcha ==>
	if (!$validForm || !$cryptok) {
		// on n'a pas encore validé le formulaire ou le code est faux
		echo '
	  <form name="cryptoForm" action="" method="post">
  		<input type="hidden" name="validForm" value="true" />
  		<input type="hidden" name="lng" value="'.$lng.'" />';
			/*
			Ici le contenu de votre formulaire
			 */
		echo '
		<table>
		  <tr>
			<td style="text-align:center;">';
        if ($serviz[100] != '0' && ($userprefs[1] == '' || $serviz[98] != 'on')) {
			$lbl = $serviz[100] == '3' ? $web[642] : '';
            echo dsp_crypt(0, 1, 3, 2, $lbl, $serviz[115] == 'on' ? $web[643] : '');
            if ($serviz[100] == '1') echo '<br />';
            echo $web[530].'
            <input type="text" name="crypt" size="6" onpaste="return false;" />
            <p style="text-align:center;">'.SubmitButton($web[154]).'</p><br />';
        }
		echo '
            </td>
		  </tr>
		</table>
      </form>';
	}
	else {
		// message faisant suite à la validation du formulaire avec code OK
	}
	// <== catpcha
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

<!-- Fin du texte HTML en langue secondaire -->';

	// catpcha ==>
	if (!$validForm || !$cryptok) {
		// on n'a pas encore validé le formulaire ou le code est faux
		echo '
	  <form name="cryptoForm" action="" method="post">
  		<input type="hidden" name="validForm" value="true" />
  		<input type="hidden" name="lng" value="'.$lng.'" />';
			/*
			Ici le contenu de votre formulaire
			 */
		echo '
		<table>
		  <tr>
			<td style="text-align:center;">';
        if ($serviz[100] != '0' && ($userprefs[1] == '' && $serviz[98] != 'on')) {
			$lbl = $serviz[100] == '3' ? $web[642] : '';
            echo dsp_crypt(0, 1, 3, 2, $lbl, $serviz[115] == 'on' ? $web[643] : '');
            if ($serviz[100] == '1') echo '<br />';
            echo $web[530].'
            <input type="text" name="crypt" size="6" onpaste="return false;" />
            <p style="text-align:center;">'.SubmitButton($web[154]).'</p><br />';
        }
		echo '
            </td>
		  </tr>
		</table>
      </form>';
	}
	else {
		// message faisant suite à la validation du formulaire avec code OK
	}
	// <== catpcha
}

btable();
include(CHEMIN."inc/bpage.inc");
?>