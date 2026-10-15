<?php
/*******************************************************************************
 *   Sample External Page with form and captcha
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
 * v5.02.09 (June 06, 2019)      : improve access control
 ******************************************************************************/
 /*
                ////////////////////////////////////////////////////////////
                //                                                        //
                //  CE SCRIPT EST UN MODELE, VEUILLEZ UTILISER UNE COPIE  //
                //  THIS SCRIPT IS A MODEL, PLEASE USE A COPY             //
                //                                                        //
                ////////////////////////////////////////////////////////////
 */

// NE PAS SUPPRIMER NI DEPLACER ces déclarations
header('Pragma: no-cache');
// pour une utilisation de ce script à la racine du site modifier
// la définition de CHEMIN ci-dessous comme ceci : define("CHEMIN", '');
define("CHEMIN", '../'); // chemin relatif du dossier /pages
include CHEMIN.'inc/includes.inc';
$message = $erreur = '';

///////////////////////////////////////////////////////////////////////////
// traitement du captcha, NE PAS SUPPRIMER NI DEPLACER
$crypt   = isset($_POST['crypt']) ? strip_tags($_POST['crypt']) : NULL;
$cryptok = (!$crypt) ? false : chk_crypt($crypt);

///////////////////////////////////////////////////////////////////////////
// variables de fonctionnement à ADAPTER, NE PAS SUPPRIMER NI DEPLACER
$formgroup = ''; // autorisation d'accès : '' = tout le monde, 'all_members' = tous les membres, 'nom_du_groupe' = nom d'un groupe
$formName  = 'sample-form'; // nom de ce script sans l'extension, obligatoire
$isCaptcha = true; // mettre true ou false
$myIcon    = ''; // nom de l'icône (nom.ext) pour le titre, laisser vide si on ne veut pas d'icône
$class     = 'FRM1'; // nom de la règle css pour personnaliser le titre
$lstTo     = ''; // mettre 1 ou plusieurs emails de destinataires séparés par un espace
$isRGPD    = true; // affichage des conditions du RGPD si le formulaire contient des données parsonnelles, mettre true ou false
if ($lng == 'fr') {
    $topmess = 'Résultats de la semaine'; // indiquez votre titre, obligatoire
    $subject = 'Information'; // sujet du mail, obligatoire
} else {
    $topmess = 'Results of the week'; // indiquez votre titre, obligatoire
    $subject = 'Information'; // sujet du mail, obligatoire
}

///////////////////////////////////////////////////////////////////////////
// dispositif contre les failles de sécurité, NE PAS SUPPRIMER NI DEPLACER
if (import('validForm', 'GET') != NULL) die('STOP ! Variable $validForm : illegal origine !');
$validForm = import('validForm', 'POST');
$ctrl_spam = import('code_pseudo', 'POST');
$tconfig   = import('tconfig', '', true, 0);
switch ($validForm) {
case NULL :
    $token      = uniqid(rand(), true);
    $token_time = time();
    setcookie(POST_COOKIE, $token.CONNECTOR.$token_time, time() + (30 * 60));
    break;
case 'send' :
    $tokens     = explode(CONNECTOR, $_COOKIE[POST_COOKIE]);
    $token_time = time() - (15 * 60);
	if (empty($tokens[0]) || $tokens[1] < time() - (30 * 60)) {
        echo BeginJavascript().'
var sto = "'.str_replace('<br />', '\n', stripslashes($web[681])).'";
alert(sto);
'.EndJavascript();
        $validForm = NULL;
        $token      = uniqid(rand(), true);
        $token_time = time();
        setcookie(POST_COOKIE, $token.CONNECTOR.$token_time, time() + (30 * 60));
    }
	elseif ($tokens[1] + 2 > time()) {
        echo BeginJavascript().'
var sto = "'.str_replace('<br />', '\n', stripslashes($web[682])).'";
alert(sto);
'.EndJavascript();
        $validForm = NULL;
        $token      = uniqid(rand(), true);
        $token_time = time();
        setcookie(POST_COOKIE, $token.CONNECTOR.$token_time, time() + (30 * 60));
    }
    break;
}
if (!empty($ctrl_spam)) $validForm = '';
// fin du dispositif de sécurité

///////////////////////////////////////////////////////////////////////////
// libellés du formulaire à COMPLETER ET ADAPTER
if ($lng == 'fr') {
    // ne pas supprimer les deux variables ci-dessous //
    $myform['CP'] = 'M\'envoyer une copie de ce formulaire : ';
    $myform['AK'] = 'Demande d\'accusé de réception : ';
    ////////////////////////////////////////////////////
    
    $myform[0] = 'Merci de nous envoyer vos résultats.';
    $myform[1] = 'Vos Nom et Prénom';
    $myform[2] = 'Votre email';
    $myform[3] = 'Votre score';
    $myform[4] = 'Commentaires';
    $myform[5] = 'Résultats de';
    $myform[6] = 'EMail : ';
    $myform[7] = 'Score : ';
    $myform[8] = 'Equipement rendu';
    $myform[9] = 'Participation au prochain challenge ?';
    $myform[10] = 'Moyen de déplacement';
    $means      = array('Transport en commun', 'Personnel', 'Co-voiturage');
} else {
    // ne pas supprimer les deux variables ci-dessous //
    $myform['CP'] = 'Send me a copy of this form: ';
    $myform['AK'] = 'Request for acknowledgment of receipt: ';
    ////////////////////////////////////////////////////
    
    $myform[0] = 'Please send us your results.';
    $myform[1] = 'Your Name and Surname';
    $myform[2] = 'Your email';
    $myform[3] = 'Your score';
    $myform[4] = 'Comments';
    $myform[5] = 'Résults of';
    $myform[6] = 'EMail : ';
    $myform[7] = 'Score : ';
    $myform[8] = 'Equipment rendered';
    $myform[9] = 'Participation in the next challenge?';
    $myform[10] = 'Means of travel';
    $means      = array('Public transport', 'Personal', 'Car-sharing');
}

// récupération des variables du formulaire
// ne pas supprimer les trois variables ci-dessous //
$fld_cp = import('fld_cp', 'POST', true, '');
$fld_ak = import('fld_ak', 'POST', true, '');
$fld_rg = import('fld_rg', 'POST', true, '');
////////////////////////////////////////////////////

$fld_1 = import('fld_1', 'POST');
$fld_2 = checkEmail(import('fld_2', 'POST')); // contrôle du courriel après récupération
$fld_3 = import('fld_3', 'POST');
$fld_4 = import('fld_4', 'POST');
$fld_5 = import('fld_5', 'POST');
$fld_6 = import('fld_6', 'POST');
$fld_7 = import('fld_7', 'POST');

// traitement d'erreurs des champs obligatoires
$err_1 = empty($fld_1) && $validForm == 'send';
$err_2 = empty($fld_2) && $validForm == 'send';
$err_3 = empty($fld_3) && $validForm == 'send';
$err_crypt = $isCaptcha && $serviz[100] != '0' && ($userprefs[1] == '' || $serviz[98] == '') && !$cryptok && $validForm == 'send' ? true : false;
$err_rgpd  = $isRGPD && empty($fld_rg) && !OK_RGPD_date($userprefs[1]) && $validForm == 'send' ? true : false;

$erreur .= $err_1     ? '<li>'.$web[545].$myform[1].$web[546].'</li>': '';
$erreur .= $err_2     ? '<li>'.$web[545].$myform[2].$web[546].'</li>': '';
$erreur .= $err_3     ? '<li>'.$web[545].$myform[3].$web[546].'</li>': '';
$erreur .= $err_crypt ? '<li>'.$web[529].'</li>': '';
$erreur .= $err_rgpd  ? '<li>'.$web[405].'</li>': '';
// fin traitement d'erreurs

if ($validForm == 'send' && empty($erreur)) {
    // on a validé le formulaire
    // le code est valide
    ExecuteBlackList();
    
    // enregistrement de l'acceptation RGPD des membres connectés ==> NE PAS SUPPRIMER
    if (!empty($userprefs[1]) && $rgpd == 'on') setRGPDdate();
    
    // construction du message à envoyer
        
    // EXEMPLE A SUPPRIMER APRES LE TEST
    $message .= '<div>'.$myform[5].' : '.$fld_1.'</div>';
    $message .= '<div>'.$myform[6].$fld_2.'</div>';
    $message .= '<div>'.$myform[7].$fld_3.'</div>';
    $message .= '<div>'.$myform[8].' : '.($fld_5 == 'on' ? $web[313] : $web[314]).'</div>';
    $message .= '<div>'.$myform[9].' : '.$fld_6.'</div>';
    $message .= '<div>'.$myform[10].' : '.$fld_7.'</div>';
    $message .= empty($fld_4) ? '' : '<div>'.$myform[4].' : </div><div style="margin:0 20px;">'.str_replace("\n", "<br />", $fld_4).'</div>';
    // fin exemple
    
    // envoi du message au(x) destinataire(s)
    $std   = $supervision[5] == 'standard_3' || $supervision[5] == 'standard_4';
	$eol   = $std ? "\n" : "\r\n";
    $lstTo = trim($lstTo.($fld_cp == 'on' ? ' '.$fld_2 : ''));
    $from  = $fld_ak == 'on' && !empty($fld_2) ? trim($user[1]).$eol.'Disposition-Notification-To: '.$fld_2 : '';
    if (!empty($subject) && !empty($message) && !empty($lstTo)) {
        eMailHtmlTo($subject, $message, $lstTo, $from);
    }
}

// si vous voulez mettre une icône du répertoire img associée au titre
if ($myIcon)
    $topmess = '<img src="'.CHEMIN.'img/'.$myIcon.'" class="f-right img32x32 valignMiddle" alt="">'.$topmess;

///////////////////////////////////////////////////////////////////////////
// NE PAS SUPPRIMER NI DEPLACER
include CHEMIN.'inc/hpage.inc';
if (function_exists('htable1'))
    htable1($topmess, 'ART'.$tconfig, '100%');
else
    htable($topmess, '100%');

// contrôle des droits d'accès
if (!isAccessGranted($formgroup)) {
    echo '
<div class="textCenter web342">'.$web[342];
    if ($members[19] != '0' && $members[19] != '3') {
        $href1 = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[3].'-0-'.$tconfig.$z2 : 'connect.php?lng='.$lng.'&tconfig='.$tconfig.$z2);
        $href2 = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[15].'-5-new-'.$tconfig.$z2 : $site['USR'].'.php?lng='.$lng.'&uuser=new&tconfig='.$tconfig.$z2);
        echo '
  <p class="textCenter">
    '.SubmitButton($web[630], 'self.location.href=\''.$href1.'\'').
      (empty($userprefs[1]) ? '&nbsp;&nbsp;'.SubmitButton($web[160], 'self.location.href=\''.$href2.'\'') : '').'
  </p>';
    }
	echo '
</div>';
    btable();
    include CHEMIN."inc/bpage.inc";
    exit();
}

// on affiche les erreurs des champs obligatoires
if (!empty($erreur)) {
    echo displayErrorMsg($web[542], $erreur);
    $validForm = '';
}

if (empty($validForm)) {
    $fix = ' <span style="color:#FF0000;"> * </span>';
    $labelClass = TYP_SCREEN == 'WEB' ? 'width45 textRight' : 'width98 textCenter';
    $itemClass  = TYP_SCREEN == 'WEB' ? 'width50 textLeft' : 'width98 textCenter';
    // on n'a pas encore validé le formulaire ou le code captcha est faux
    echo '
  <form name="cryptoForm" action="'.CHEMIN.'pages/'.$formName.'.php?lng='.$lng.'&amp;tconfig='.$tconfig.'" method="post">
    <input type="hidden" name="validForm" value="send" />
    <input type="hidden" name="lng" value="'.$lng.'" />';
    
    // affichage entête du formulaire
    echo '
    <div class="textCenter fontBold">
      '.$myform[0].'<span class="displayNone"><input type="text" name="code_pseudo" value="" /></span>
    </div>
    <p>('.$fix.') '.$web[543].'</p>';
    
    // mettre ici le contenu de votre formulaire    
    // EXEMPLE A MODIFIER APRES LE TEST
    
    // saisie nom et prénom : champ texte simple
    echo '
    <div class="width98 marginAuto">
      <div class="displayInlineBlock '.$labelClass.'">'.$myform[1].$fix.' :</div>
      <div class="displayInlineBlock '.$itemClass.'">
        <input class="'.($err_1 ? 'errorInputText' : 'texte').'" type="text" name="fld_1" value="'.$fld_1.'" size="24" />
      </div>';
    
    // saisie adresse mail : champ texte simple
    echo '
      <div class="displayInlineBlock '.$labelClass.'">'.$myform[2].$fix.' :</div>
      <div class="displayInlineBlock '.$itemClass.'">
        <input class="'.($err_2 ? 'errorInputText' : 'texte').'" type="text" name="fld_2" value="'.$fld_2.'" size="28" />
      </div>';
    
    // saisie du score : champ texte simple
    echo '
      <div class="displayInlineBlock '.$labelClass.'">'.$myform[3].$fix.' :</div>
      <div class="displayInlineBlock '.$itemClass.'">
        <input class="'.($err_3 ? 'errorInputText' : 'texte').'" type="text" name="fld_3" value="'.$fld_3.'" size="8" />
      </div>';
    
    // saisie équipement rendu : champ case à cocher
    echo '
      <div class="displayInlineBlock '.$labelClass.'">'.$myform[8].' :</div>
      <div class="displayInlineBlock '.$itemClass.'">
        <input type="checkbox" name="fld_5"'.Checked($fld_5).' />
      </div>';
    
    // saisie prochain challenge : champ boutons radio
    echo '
      <div class="displayInlineBlock '.$labelClass.'">'.$myform[9].' :</div>
      <div class="displayInlineBlock '.$itemClass.'">
        <input type="radio" name="fld_6" value="'.$web[313].'"'.Checked($web[313] == $fld_6).' /><span'.($err_6 ? ' class="errorInputText"' : '').'> '.$web[313].'</span> 
        <input type="radio" name="fld_6" value="'.$web[314].'"'.Checked($web[314] == $fld_6).' /><span'.($err_6 ? ' class="errorInputText"' : '').'> '.$web[314].'</span> 
      </div>';
    
    // saisie du score : champ sélection simple
    echo '
      <div class="displayInlineBlock '.$labelClass.'">'.$myform[10].' :</div>
      <div class="displayInlineBlock '.$itemClass.'">
        <select name="fld_7">
          <option value="'.$means[0].'"'.Selected($fld_7 == $means[0]).'>'.$means[0].'</option>
          <option value="'.$means[1].'"'.Selected($fld_7 == $means[1]).'>'.$means[1].'</option>
          <option value="'.$means[2].'"'.Selected($fld_7 == $means[2]).'>'.$means[2].'</option>
        </select>
      </div>';
    
    // saisie de commentaires : champ texte libre 
    echo '
      <div class="displayInlineBlock '.$labelClass.' valignTop">'.$myform[4].' : </div>
      <div class="displayInlineBlock '.$itemClass.'">
        <textarea name="fld_4" class="width85" style="height: 72px; max-width: 320px;">'.$fld_4.'</textarea>
      </div>';
    
    echo '
    </div>';
    // fin exemple
        
    // catpcha ==> NE PAS SUPPRIMER
    if ($isCaptcha && $serviz[100] != '0' && ($userprefs[1] == '' || $serviz[98] != 'on')) {
        echo '
    <table class="textCenter width50 marginAuto">
      <tr>
        <td class="textCenter">';
        $lbl = $serviz[100] == '3' ? $web[642] : '';
        echo dsp_crypt(0, 1, 3, 2, $lbl, $serviz[115] == 'on' ? $web[643] : '');
        echo $web[530].'
          <input type="text" name="crypt" size="6" onpaste="return false;" />
        </td>
      </tr>
    </table>';
    }
    // <== catpcha
    
    // les deux lignes suivantes concernent la copie à l'envoyeur et l'accusé de réception
    // pour ne pas les utiliser les mettre simplement en commentaire
    echo '<div>'.$myform['CP'].'<input type="checkbox" name="fld_cp"'.Checked($fld_cp).' /></div>';
    echo '<div>'.$myform['AK'].'<input type="checkbox" name="fld_ak"'.Checked($fld_ak).' /></div>';
    
    // validation des conditions RGPD
    if ($isRGPD && !empty($serviz[177]) && !OK_RGPD_date($userprefs[1])) echo '
    <br />
    <fieldset class="width98 marginAuto">
      <p><strong>'.$web[646].'</strong></p>
      <p>'.$web[684].'</p>
      <div>'.$web[415].$fix.' : <input type="checkbox" name="fld_rg"'.Checked($fld_rg).' /></div>
    </fieldset>';
    
    echo '
    <p class="textCenter">'.SubmitButton($web[52]).'</p>
  </form>';
} elseif (!empty($message)) {
    // message faisant suite à la validation du formulaire
    echo '<div class="textLeft"><br />'.$message.'</div>';
}

btable();
include CHEMIN."inc/bpage.inc";
?>