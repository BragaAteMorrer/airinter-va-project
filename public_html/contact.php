<?php
/*******************************************************************************
 *   Form Advanced contact
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2023 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.15 (November 29, 2023) : Correction illegal variable
 ******************************************************************************/

header('Pragma: no-cache');
define('CHEMIN', '');
include CHEMIN.'inc/includes.inc';

$crypt   = isset($_POST['crypt']) ? strip_tags($_POST['crypt']) : NULL;
$cryptok = (!$crypt) ? false : chk_crypt($crypt);

$lng      = import('lng');
$ctconfig = import('ctconfig');
$ctconfig = empty($ctconfig) ? 'ctconfig' : $ctconfig;
$dbct     = SelectDBFieldsByField(ReadDBFields(CONFIGREP.'ctconfigs.dtb'), 1, $ctconfig);
$okct     = !empty($dbct) && $dbct[0][1] == $ctconfig && $dbct[0][2] == 'a' ? true : false;
if ($okct && is_file(CONFIGREP.$ctconfig.'.inc')) {
    include CONFIGREP.$ctconfig.'.inc';
} else {
    echo BeginJavascript().'
alert("STOP ! Config file not found !");
window.history.back();'.EndJavascript();
    die();
}

function FindIndexDBFields($fic, $submit0, $submit1, $submit2) {
    if (FileDBExist($fic)) {
        $DataDB = ReadDBFields($fic);
        for ($i = 0; $i < count($DataDB); $i++) {
            if (@stristr($DataDB[$i][0],$submit0)
            &&  @stristr($DataDB[$i][1],$submit1)
            &&  @stristr($DataDB[$i][2],$submit2)) {
                return $i;
            }
        }
    }
    return FALSE;
}

$t = ($lng == $lang[0]) ? 0 : 10 ;
$n = ($lng == $lang[0]) ? 3 : 4 ;

if (import('act', 'GET') != NULL) die('STOP ! Variable $act : illegal origine !');
$act       = import('act', 'POST');
$antispam  = import('antispam');
$ctrl_spam = import('code_pseudo', 'POST');
$tconfig   = $menuico[21] == 'ALL' ? import('tconfig', '', true, 0) : $menuico[21];

$REMOTE_ADDR = getIPadress($site[6] == 'on' ? (isset($_SERVER['HTTP_X_FORWARDED_FOR'])? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']): $_SERVER['REMOTE_ADDR']);
switch ($act) {
case NULL :
    $token      = uniqid(rand(), true);
    $token_time = time();
    setcookie(CTCT_COOKIE, $token.CONNECTOR.$token_time, time() + (30 * 60), '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
    break;
case 'send' :
    $tokens = explode(CONNECTOR, $_COOKIE[CTCT_COOKIE]);
	if (empty($tokens[0]) || $tokens[1] < time() - (30 * 60)) {
        echo BeginJavascript().'
var sto = "'.str_replace('<br />', '\n', stripslashes($web[681])).'";
alert(sto);
'.EndJavascript();
        $act        = NULL;
        $token      = uniqid(rand(), true);
        $token_time = time();
        setcookie(CTCT_COOKIE, $token.CONNECTOR.$token_time, time() + (30 * 60), '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
    }
	elseif ($tokens[1] + 2 > time()) {
        echo BeginJavascript().'
var sto = "'.str_replace('<br />', '\n', stripslashes($web[682])).'";
alert(sto);
'.EndJavascript();
        $act        = NULL;
        $token      = uniqid(rand(), true);
        $token_time = time();
        if (!isset($_COOKIE[CTCT_COOKIE]))
			setcookie(CTCT_COOKIE, $token.CONNECTOR.$token_time, time() + (30 * 60), '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
    }
    break;
}

$form0 = import('form0');
$form1 = import('form1');
$form2 = import('form2');
$text1 = import('text1');
$text2 = import('text2');
$text3 = import('text3');
$text4 = import('text4');
$text5 = import('text5');
$text6 = import('text6');
$sel0  = import('sel0');
$rad0  = import('rad0');
$chk1  = import('chk1');
$chk2  = import('chk2');
$chk3  = import('chk3');
$chk4  = import('chk4');
$chkar = import('chkar');
$rgpd  = import('rgpd', 'POST', true, '');

require CHEMIN.'inc/ckeditor_config/editors_functions.php';
require_once CHEMIN.'inc/htmlpurifier/library/HTMLPurifier.auto.php';
$configpg = HTMLPurifier_Config::createDefault();
$configpg->set('HTML.Allowed', '
  p[class|id|style],div[class|id|style],strong,em,u,b,sub,sup,span[class|style],pre[class],blockquote[class|style],
  a[href|target|title|style],img[src|alt|style],ul[style],ol[start|style],li');
$configpg->set('Attr.AllowedFrameTargets', array('_blank', '_self'));
$purifier = new HTMLPurifier($configpg);
$ptxt     = import('ptxt', 'POST', FALSE);
$ptxt     = $purifier->purify(isAuthorImg($userprefs[1]) ? $ptxt : getPGTXT($ptxt));

if (!empty($ctrl_spam)) $act = null;

$topmess = $lng == $lang[0] ? $ctlbl0 : $ctlbl1;
$okmail  = false;
$typ     = TYP_MAIL;

$err_form1  = empty($form1) && $act == 'send';
$err_form11 = !$err_form1 && 1 != preg_match('/^.{2,40}$/', $form1) && $act == 'send';
$err_form2  = empty(checkEmail($form2)) && $act == 'send';
$err_txt1   = $chtxt[1] == 'fix' && empty($text1) && $act == 'send';
$err_txt2   = $chtxt[2] == 'fix' && empty($text2) && $act == 'send';
$err_txt3   = $chtxt[3] == 'fix' && empty($text3) && $act == 'send';
$err_txt4   = $chtxt[4] == 'fix' && empty($text4) && $act == 'send';
$err_txt5   = $chtxt[5] == 'fix' && empty($text5) && $act == 'send';
$err_txt6   = $chtxt[6] == 'fix' && empty($text6) && $act == 'send';
$err_crypt  = $serviz[100] != '0' && $serviz[95] == 'on' && ($userprefs[1] == '' || $serviz[98] == '') && !$cryptok && $act == 'send' ? true : false;
$err_ptxt   = empty($ptxt) && $act == 'send' ? true : false;
$err_rgpd   = !empty($serviz[177]) && empty($rgpd) && !OK_RGPD_date($userprefs[1]) && $act == 'send' ? true: false;

$erreur  = '';
$erreur .= $err_form1  ? '<li>'.$web[40].'</li>': '';
$erreur .= $err_form11 ? '<li>'.$web[266].'</li>': '';
$erreur .= $err_form2  ? '<li>'.$web[42].'</li>': '';
$erreur .= $err_txt1   ? '<li>'.$web[545].$ftxt[1 + $t].$web[546].'</li>': '';
$erreur .= $err_txt2   ? '<li>'.$web[545].$ftxt[2 + $t].$web[546].'</li>': '';
$erreur .= $err_txt3   ? '<li>'.$web[545].$ftxt[3 + $t].$web[546].'</li>': '';
$erreur .= $err_txt4   ? '<li>'.$web[545].$ftxt[4 + $t].$web[546].'</li>': '';
$erreur .= $err_txt5   ? '<li>'.$web[545].$ftxt[5 + $t].$web[546].'</li>': '';
$erreur .= $err_txt6   ? '<li>'.$web[545].$ftxt[6 + $t].$web[546].'</li>': '';
$erreur .= $err_ptxt   ? '<li>'.$web[43].'</li>': '';
$erreur .= $err_crypt  ? '<li>'.$web[529].'</li>': '';
$erreur .= $err_rgpd   ? '<li>'.$web[405].'</li>': '';

if ($act == 'send' && empty($erreur)) { // envoi du mail
    
    include CONFIG;
        
    if (!empty($userprefs[1]) && $rgpd == 'on') setRGPDdate();

    $okmail = true;
    $eFiles = array();
    for ($f = 0; $f < $chfic[0]; $f++) {
        $filename = $_FILES['fil_in'.$f]['name'];
        $tmpname  = $_FILES['fil_in'.$f]['tmp_name'];
        if ($filename != '') {
            if (!is_uploaded_file($tmpname)) {
                $erreur .= '- '.basename($filename)." : ".$web[547].'\n';
                $okmail = false;
            } else {
                $eFiles[] = array($tmpname, $filename);
            }
        }
    }
	$index = FindIndexDBFields(DBANTISPAM, $antispam, getIPadress($site[6] == 'on' ? (isset($_SERVER['HTTP_X_FORWARDED_FOR'])? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']): $_SERVER['REMOTE_ADDR']), $typ);
	if ($index === false) {
		if (is_file('inc/postguest_antispam.inc')) include 'inc/postguest_antispam.inc';
		die('STOP ! Anti-SPAM !');
	} else {
		$fmail0 = $fdest[1];
		for ($d = 2; $d < count($fdest); $d++)
		{
			if (!isset($f2name[$d])) $f2name[$d] = '';
			if ($form0 == ($lang[0] == $lng ? $fname[$d] : $f2name[$d])) {
				$fmail0 = $fdest[$d];
				break;
			}
		}
        $bcc    = !empty($chkcc) ? 'bcc='.$user[1].'&' : '';
		$corps  = '<table style="width:100%;">';
		$corps .= '<tr><td style="width:30%;"><u>'.$web[330].'</u> : </td><td style="width:70%;">'.$form1.'</td></tr>'."\n";
		$corps .= '<tr><td><u> '.$web[101].'<br />'.$web[476].'</u>&nbsp;</td><td><a href="mailto:'.$form2.'?'.$bcc.'subject=Re: '.$web[330].stripslashes($site[($lang[0] == $lng ? 0 : 11)]).'">'.stripslashes($form2).'</a></td></tr>'."\n";
		$corps .= '<tr><td colspan="2"><hr /></td></tr>'."\n";
    // zones texte 1 à 3
		if ($text1) $corps .= '<tr><td><u> '.$ftxt[1 + $t].'</u> : </td><td>'.stripslashes($text1).'</td></tr>'."\n";
		if ($text2) $corps .= '<tr><td><u> '.$ftxt[2 + $t].'</u> : </td><td>'.stripslashes($text2).'</td></tr>'."\n";
		if ($text3) $corps .= '<tr><td><u> '.$ftxt[3 + $t].'</u> : </td><td>'.stripslashes($text3).'</td></tr>'."\n";
    //zone select
        if ($sel0 != '') $corps .= '<tr><td><u> '.$fsel[0 + $t].'</u> : </td><td>'.stripslashes($sel0).'</td></tr>'."\n";
    //zone boutons radio
		if ($rad0) $corps .= '<tr><td><u> '.$frad[0 + $t].'</u> : </td><td>'.stripslashes($rad0).'</td></tr>'."\n";
    // zone cases à cocher
		if ($chchk[0] != '' && $chk1 || $chk2 || $chk3 || $chk4) {
			$corps .= '<tr><td style="vertical-align:top"><u> '.$fchk[0 + $t].'</u> : </td><td>'."\n";
			if ($chk1) $corps .= '<span style="text-align:right;">- '.$fchk[1 + $t].'</span><br />'."\n";
			if ($chk2) $corps .= '<span style="text-align:right;">- '.$fchk[2 + $t].'</span><br />'."\n";
			if ($chk3) $corps .= '<span style="text-align:right;">- '.$fchk[3 + $t].'</span><br />'."\n";
			if ($chk4) $corps .= '<span style="text-align:right;">- '.$fchk[4 + $t].'</span><br />'."\n";
			$corps .= '</td></tr>'."\n";
		}
    // zones texte 4 à 6
		if ($text4) $corps .= '<tr><td><u> '.$ftxt[4 + $t].'</u> : </td><td>'.stripslashes($text4).'</td></tr>'."\n";
		if ($text5) $corps .= '<tr><td><u> '.$ftxt[5 + $t].'</u> : </td><td>'.stripslashes($text5).'</td></tr>'."\n";
		if ($text6) $corps .= '<tr><td><u> '.$ftxt[6 + $t].'</u> : </td><td>'.stripslashes($text6).'</td></tr>'."\n";
    // zone message
        $corps .= '<tr><td colspan="2"><hr /><u> '.$web[552].'</u> :<br />'.$ptxt.'</td></tr></table>'."\n";        
/*
    // confection de l'email
		$delimiteur = $serviz[155] == 'standard' ? '==S=E=P=A=R=A=T=O=R==' : '-----='.md5(uniqid(rand()));
        $eol   = $serviz[155] == 'standard' ? "\r\n" : "\n";
		$msg1  = "Ceci est un message au format MIME 1.0 multipart/mixed.".$eol;
		$msg1 .= "--$delimiteur".$eol;
		$msg1 .= "Content-Type: text/html; charset=utf-8".$eol;
		$msg1 .= "Content-Transfer-Encoding:8bit".$eol;
		$msg1 .= $eol; 
		$msg2 = '';
*/
        $nbf  = 0;
		$efilesattaches = '';
		$dossierDestination = CHEMIN.'fic/';

        for ($f = 0; $f < $chfic[0]; $f++) {
            $fichier_photo  = $_FILES['fil_in'.$f]['name'];
            $fichier_photo_tmpname  = $_FILES['fil_in'.$f]['tmp_name'];
			$prefix = time()."_";
			if ($fichier_photo != '')
			{
				$fichier_photo = $prefix.$fichier_photo;
				if (move_uploaded_file($fichier_photo_tmpname, $dossierDestination.$fichier_photo)) 
				{						
					SetChmod($dossierDestination.$fichier_photo);
					$fp = fopen($dossierDestination.$fichier_photo, 'rb');
					$attachment = fread($fp, filesize($dossierDestination.$fichier_photo));
					fclose($fp);
					$efilesattaches .= $fichier_photo.' ';
					$nbf++;
				}
			}
        }
    // confection partie texte
        $texte1  = strip_tags(preg_replace("!<br />|<br />|</p>!i", "\n", preg_replace("!<hr>|<hr />!i", "\n \n", $corps)));

    // envoi de l'email
		$msobj = $web[330].stripslashes($site[($lang[0] == $lng ? 0 : 11)]);
		$msobj = strtr($msobj, array('à'=>'a', 'é'=>'e', 'è'=>'e', 'ê'=>'e', 'ë'=>'e', 'î'=>'i', 'ï'=>'i', 'ô'=>'o', 'û'=>'u', 'û'=>'u'));
		$msobj = preg_replace('![^-a-zA-Z0-9_ ]!i', '.', $msobj);
		$eAR = '';
		if ($fdest[0] != '') $destinataireencopy = $fdest[0].' ';
		else $destinataireencopy = '';
		$eFrom    = $user[1];
		$FromTo   = $form1;
		$eTo      = $fmail0;
		$eSubject = $msobj;
		$eMsgHtml = $corps;
		$actioncomplete = 'C'; // pour contact
		$eMsgText = strip_tags(preg_replace("!<br />|<br />|</p>!i", "\n", preg_replace("!<hr>|<hr />!i", "\n \n", $eMsgHtml)));
		$eAR      = $chkar != '' ? $form2 : '';
		sendmail($eTo, $eFrom,$eSubject, $eMsgHtml, $efilesattaches, $actioncomplete, $destinataireencopy, $eAR, $form2, $form1='');
	}
}
else {
    $antispam = md5(uniqid(rand()));
    $antispams = ReadDBFields(DBANTISPAM);
    $timestamp = microtime(TRUE);
    array_push($antispams, array($antispam, getIPadress($site[6] == 'on' ? (isset($_SERVER['HTTP_X_FORWARDED_FOR'])? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']): $_SERVER['REMOTE_ADDR']), TYP_MAIL, $timestamp));
    $timestamp -= $serviz[52] * 60;
    while($antispams[0][3] < $timestamp) {
        array_shift($antispams);
    }
    if (count($antispams) > $serviz[51]) {
        array_shift($antispams);
    }
    WriteDBFields(DBANTISPAM, $antispams);
}

$headinc .= BeginJavascript().'
function VerifyForm() {
    var sto = "";
    var messok = "";
    var erreur = false;

    if (document.rapporter.form1.value == "") {
        sto += "  - '.addslashes($web[40]).'\n";
		document.rapporter.form1.className = "errorInputText";
        erreur = true;
    } else {
        regexp = /^.{2,'.max($serviz[148], 40).'}$/;
        if (!regexp.test(document.rapporter.form1.value)) {
            sto += "  - '.addslashes($web[266]).'\n";
			document.rapporter.form1.className = "errorInputText";
            erreur = true;
        }
    }
    regexp = /^[^\.\s]+(\.[^\.\s]+)*@[^\.\s]+(\.[^\.\s]+)+$/;
    if (!regexp.test(document.rapporter.form2.value)) {
        sto += "  - '.addslashes($web[42]).'\n";
		document.rapporter.form2.className = "errorInputText";
        erreur = true;
    }';
for ($i = 1; $i <= 6; $i++) {
    if ($chtxt[$i] == 'fix') {
        $headinc .= '
    if (document.rapporter.text'.$i.'.value == "") {
        sto += "  - '.addslashes($web[545].$ftxt[$i + $t].$web[546]).'\n";
		document.rapporter.text'.$i.'.className = "errorInputText";
        erreur = true;
    }';
    }
}
if ($chsel[0] == 'fix') 
    $headinc .= '
    if (document.rapporter.sel0.value == "???") {
        sto += "  - '.addslashes($web[545].$fsel[0 + $t].$web[549]).'\n";
		document.rapporter.sel0.className = "errorInputText";
        erreur = true;
    }';
if ($chrad[0] == 'fix') 
    $headinc .= '
    if (document.rapporter.rad0.value == "") {
        sto += "  - '.addslashes($web[545].$frad[0 + $t].$web[549]).'\n";
        erreur = true;
    }';
if ($chchk[0] == 'fix') {
    $chk = '';
    for ($i = 1; $i <= 4; $i++) {
        if ($fchk[$i + $t] != '') $chk .= '!document.rapporter.chk'.$i.'.checked && ';
    }
    $chk = substr($chk, 0, -3);
    $headinc .= '
    if ('.$chk.') {
        sto += "  - '.addslashes($web[545].$fchk[0 + $t].$web[550]).'\n";
        erreur = true;
    }';
}
$headinc .= '
    if (erreur) {
        sto = "'.addslashes($web[44]).'\n\n" + sto;
        alert(sto);
    return false;
    }
}'.EndJavascript();

include 'inc/hpage.inc';
if (function_exists('htable1'))
    htable1($topmess, 'CNT'.$tconfig, '100%');
else
    htable($topmess, '100%');

if (!isAccessGranted($members[20])) {
    echo '
<div class="container text-center web342">'.$web[342];
    if ($members[19] != '0' && $members[19] != '3') {
        $href1 = CHEMIN.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[3].'-0-'.$tconfig.$z2 
          : 'connect.php?lng='.$lng.'&tconfig='.$tconfig.$z2);
        $href2 = CHEMIN.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[15].'-5-new-'.$tconfig.$z2 
          : $site['USR'].'.php?lng='.$lng.'&uuser=new&tconfig='.$tconfig.$z2);
        echo '
  <p class="text-center">
    '.SubmitButton($web[630], 'self.location.href=\''.$href1.'\'').
      (empty($pseudo) ? '&nbsp;&nbsp;'.SubmitButton($web[160], 'self.location.href=\''.$href2.'\'') : '').'
  </p><br />';
    }
	echo '
</div>';
} else {
    if ($okmail) {
        CreateDir(TEMPREP);
        for ($f = 0; $f < $chfic[0]; $f++) {
            $filename = $_FILES['fil_in'.$f]['name'];
            if ($filename != '') {
                @unlink(TEMPREP.$filename);
            }
        }
        if ($chfic[0] != 0) $corps .= '<hr />';
		if ($efilesattaches != '')
		{
			$filename = explode(' ',$efilesattaches);
			for ($f = 0; $f < $chfic[0]; $f++) {
				if ($filename != '') {
					$corps .= '<u>PJ:</u> '.$filename[$f].'<br />';
					@unlink($dossierDestination.$filename[$f]);
				}
			}
		}
		
        echo '
<div class="container text-left dispMail">
  <p>'.$web[539].'</p>
  <div class="rep bord text-center dispMailBody">'.stripslashes($corps).'</div>
  <p class="text-left">'.$web[540].'</p>
</div>';
    } else {
        function msgtext($check, $name, $texte, $ftexte, $err_txt) {
			global $fix, $charset;
            if ($check == 'fix') $ftexte .= $fix;
            echo '
          <div class="d-table-row">
            <div class="d-table-cell">
              <div class="m-auto pg360 text-center">'.$ftexte.'</div>
              <div class="m-auto pg360 pb-2">
                <input class="'.($err_txt ? 'errorInputText' : 'texte').' form-control textFieldContact mx-auto" type="text" 
                  name="'.$name.'" value="'.htmlentities(stripslashes($texte), ENT_QUOTES, $charset).'" />
              </div>
            </div>
          </div>';
        }

		if (!empty($erreur))
			echo displayErrorMsg($web[542], $erreur);
        
        echo '
    <div class="text-center">';
	// Position Contact téléphonique  $ftelbelow[0] == '' => en haut
	if ($ftelbelow[0] == '') {
	// contact téléphonique
        if ($ftel[0] == 'on') {
            echo '  
    <div class="w-100 text-left">'.ForceToAbsolute(stripslashes($ftel[1 + $t])).'</div>';
        }
	}
	// Position Contact postal  $fletbelow[0] == '' => en haut
	if ($fletbelow[0] == '') {
    // contact par courrier
        if ($flet[0] == 'on') {
            echo '
    <div class="w-100 text-left">'.ForceToAbsolute(stripslashes($flet[1 + $t])).'</div>';
        }
	}	
    // contact par mail
        echo '
    <div class="mainct w-100 text-center">
      <form name="rapporter" 
        action="'.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[2].'-'.$tconfig.$z2 
          : $site['CT'].'.php?lng='.$lng.'&amp;tconfig='.$tconfig.$z2).'" 
        enctype="multipart/form-data" 
        method="post" 
        onsubmit="return VerifyForm(); return false;">
      <fieldset class="border rounded">
        <legend class="headContactW">&bull; '.$web[541].'</legend>
        <input type="hidden" name="antispam" value="'.$antispam.'" />
        <input type="hidden" name="act" value="send" />
        <input type="hidden" name="ctconfig" value="'.$ctconfig.'" />
        <div class="table-responsive-md">
        <div class="d-table w-100 m-auto text-left maxWidthContact">
          <div class="d-table-row">
            <div class="d-table-cell text-center">
              <div class="pb-2">'.$web[45].'</div>
              <span class="d-none"><input type="text" name="code_pseudo" value="" /></span>
            </div>
          </div>
          <div class="d-table-row">
            <div class="d-table-cell text-left pb-4">(<span class="text-danger"> * </span>)&nbsp;'.$web[543].'</div>
          </div>';
        $fix = '&nbsp;(<span class="text-danger"> * </span>)';
    // choix destinataire
        if (!isset($fdest[2])) { // si 1 seul destinataire pas de select
            echo '
          <div class="d-table-row">
            <div class="d-table-cell">
              <input type="hidden" name="form0" value="'.htmlentities($lang[0] == $lng ? $fname[1] : $f2name[1], ENT_QUOTES, $charset).'" />
            </div>
          </div>';
        } else {
            echo '
          <div class="d-table-row">
            <div class="d-table-cell">
              <div class="table-responsive-md">
              <div class="d-table w-100">
                <div class="d-table-row">
                  <div class="d-table-cell text-left w-50">'.htmlentities($lang[0] == $lng ? $fname[0] : $f2name[0], ENT_QUOTES, $charset).' : </div>
                  <div class="d-table-cell text-left w-50">
                    <select name="form0" class="form-control form-control-lg" style="max-width: 224px;">';
			for ($d = 1; $d < count($fdest); $d++) {
				if (!isset($f2name[$d])) $f2name[$d] = '';
                echo '
                      <option value="'.htmlentities($lang[0] == $lng 
                        ? $fname[$d] : $f2name[$d], ENT_QUOTES, $charset).'"'.Selected($form0 == ($lang[0] == $lng ? $fname[$d] 
                        : $f2name[$d])).'>
                      '.($lang[0] == $lng ? $fname[$d] : $f2name[$d]).'</option>';
			}
            echo '
			        </select>
                  </div>
                </div>
              </div>
              </div>
            </div>
          </div>';
        }
    // expéditeur
        $form1 = (empty($form1) && $userprefs[1] != '') ? $userprefs[1] : $form1;
        $form2 = (empty($form2) && $userprefs[2] != '') ? $userprefs[2] : $form2;
        echo '
          <div class="d-table-row">
            <div class="d-table-cell">
              <div class="input-group m-auto pg360 pb-2">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="far fa-user"></i></span>
                </div>
                <input class="'.($err_form1 || $err_form11 ? 'errorInputText' : 'texte').' form-control" type="text" name="form1" 
                  value="'.htmlentities(stripslashes($form1), ENT_QUOTES, $charset).'" placeholder="'.$fmail[1 + $t].'" />'.$fix.'
              </div>
            </div>
          </div>


          <div class="d-table-row">
            <div class="d-table-cell">
              <div class="input-group m-auto pg360 pb-2">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-at"></i></span>
                </div>
                <input class="'.($err_form2 ? 'errorInputText' : 'texte').' form-control" type="text" name="form2" 
                  value="'.stripslashes($form2).'" placeholder="'.$fmail[2 + $t].'" />'.$fix.'
              </div>
            </div>
          </div>';
    // zones texte 1 à 3
        if ($chtxt[1] != '') msgtext ($chtxt[1], 'text1', $text1, $ftxt[1 + $t], $err_txt1);
        if ($chtxt[2] != '') msgtext ($chtxt[2], 'text2', $text2, $ftxt[2 + $t], $err_txt2);
        if ($chtxt[3] != '') msgtext ($chtxt[3], 'text3', $text3, $ftxt[3 + $t], $err_txt3);
    // zone select
        if ($chsel[0] != '') {
            if ($chsel[0] == 'fix') $fsel[0 + $t] .= $fix;
            echo '
          <div class="d-table-row">
            <div class="d-table-cell">
              <div class="input-group pg360 m-auto pb-2">
                <div class="input-group-prepend">
                  <span class="input-group-text">'.$fsel[0 + $t].'</span>
                </div>
                <select name="sel0" class="form-control" style="max-width: 224px;">
                      <option value="???">???</option>';
            if ($fsel[1]) {
                echo '
                  <option value="'.$fsel[1 + $t].'"'.Selected($sel0 == $fsel[1 + $t]).'>'.$fsel[1 + $t].'</option>';
            }
            if ($fsel[2]) {
                echo '
                  <option value="'.$fsel[2 + $t].'"'.Selected($sel0 == $fsel[2 + $t]).'>'.$fsel[2 + $t].'</option>';
            }
            if ($fsel[3]) {
                echo '
                  <option value="'.$fsel[3 + $t].'"'.Selected($sel0 == $fsel[3 + $t]).'>'.$fsel[3 + $t].'</option>';
            }
            if ($fsel[4]) {
                echo '
                  <option value="'.$fsel[4 + $t].'"'.Selected($sel0 == $fsel[4 + $t]).'>'.$fsel[4 + $t].'</option>';
            }
            echo '
                </select>
              </div>
            </div>
          </div>';
        }
    // zone boutons radio
        if ($chrad[0] != '') {
            echo '
          <div class="d-table-row"><div class="d-table-cell">&nbsp;</div></div>';
            if ($chrad[0] == 'fix') $frad[0 + $t] .= $fix;
            echo '
          <div class="d-table-row">
            <div class="d-table-cell">
              <div class="table-responsive-md">
              <div class="d-table w-100">
                <div class="d-table-row">
                  <div class="d-table-cell text-left w-45 align-top">'.$frad[0 + $t].' : </div>
                  <div class="d-table-cell text-left w-55">';
            if ($frad[1]) {
                echo '
                    <div class="form-check">
                      <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="rad0" value="'.$frad[1 + $t].'"'.checked($rad0 == $frad[1 + $t]).' />
                          &nbsp;'.$frad[1 + $t].'
                      </label>
                    </div>';
            }
            if ($frad[2]) {
                echo '
                    <div class="form-check">
                      <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="rad0" value="'.$frad[2 + $t].'"'.checked($rad0 == $frad[2 + $t]).' />
                          &nbsp;'.$frad[2 + $t].'
                      </label>
                    </div>';
            }
            if ($frad[3]) {
                echo '
                    <div class="form-check">
                      <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="rad0" value="'.$frad[3 + $t].'"'.checked($rad0 == $frad[3 + $t]).' />
                          &nbsp;'.$frad[3 + $t].'
                      </label>
                    </div>';
            }
            if ($frad[4]) {
                echo '
                    <div class="form-check">
                      <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="rad0" value="'.$frad[4 + $t].'"'.checked($rad0 == $frad[4 + $t]).' />
                          &nbsp;'.$frad[4 + $t].'
                      </label>
                    </div>';
            }
            echo '
                  </div>
                </div>
              </div>
              </div>
            </div>
          </div>';
        }
    // zone à cocher
        if ($chchk[0] != '') {
            echo '
          <div class="d-table-row"><div class="d-table-cell">&nbsp;</div></div>';
            if ($chchk[0] == 'fix') $fchk[0 + $t] .= $fix;
            echo '
          <div class="d-table-row">
            <div class="d-table-cell">
              <div class="table-responsive-md">
              <div class="d-table w-100">
                <div class="d-table-row">
                  <div class="d-table-cell text-left w-45 align-top">'.$fchk[0 + $t].' : </div>
                  <div class="d-table-cell text-left w-55">';
            if ($fchk[1]) {
                echo '
                    <div class="form-check">
                      <label class="form-check-label">
                        <input type="checkbox" class="form-check-input" name="chk1"'.checked($chk1).' />&nbsp;'.$fchk[1 + $t].'
                      </label>
                    </div>';
            }
            if ($fchk[2]) {
                echo '
                    <div class="form-check">
                      <label class="form-check-label">
                        <input type="checkbox" class="form-check-input" name="chk2"'.checked($chk2).' />&nbsp;'.$fchk[2 + $t].'
                      </label>
                    </div>';
            }
            if ($fchk[3]) {
                echo '
                    <div class="form-check">
                      <label class="form-check-label">
                        <input type="checkbox" class="form-check-input" name="chk3"'.checked($chk3).' />&nbsp;'.$fchk[3 + $t].'
                      </label>
                    </div>';
            }
            if ($fchk[4]) {
                echo '
                    <div class="form-check">
                      <label class="form-check-label">
                        <input type="checkbox" class="form-check-input" name="chk4"'.checked($chk4).' />&nbsp;'.$fchk[4 + $t].'
                      </label>
                    </div>';
            }
            echo '
                  </div>
                </div>
              </div>
              </div>
            </div>
          </div>';
        }
    //zones texte 4 à 6
        echo '
          <div class="d-table-row"><div class="d-table-cell">&nbsp;</div></div>';
        if ($chtxt[4] != '') msgtext ($chtxt[4], 'text4', $text4, $ftxt[4 + $t], $err_txt4);
        if ($chtxt[5] != '') msgtext ($chtxt[5], 'text5', $text5, $ftxt[5 + $t], $err_txt5);
        if ($chtxt[6] != '') msgtext ($chtxt[6], 'text6', $text6, $ftxt[6 + $t], $err_txt6);
        echo '
        </div>
        </div>
        <div class="table-responsive-md">
        <div class="d-table w-100 m-auto maxWidthContact">
          <div class="d-table-row">
            <div class="d-table-cell text-center">';
    // zone du message
        $ptxt = addslashes($ptxt);
        echo '
              <div class="text-center pt-5 pb-5">'.$fmes[1 + $t].$fix.' :</div>';
        $cke_toolbar = !isAuthorImg($userprefs[1]) ? array('toolbarName'=>'Guppy_out_min') : array('toolbarName'=>'Guppy_out_min_img');
        echo display_editor('ptxt', '100%', 280, $ptxt, $cke_toolbar);
        // pièces jointes
        if ($chfic[0] != '0') {
            echo '
                <input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
                <div class="custom-files pt-5">'.$web[548].'</div>';
            for ($f = 0; $f < $chfic[0]; $f++) {
                echo '
                <input class="texte form-control-file border pg360 mx-auto" type="file" name="fil_in'.$f.'" size="40" value="" />';
            }
        }
        if ($far != '') { // accusé de réception
            echo '
            <div class="form-check py-5">
              <label class="form-check-label mr-4" for="chkar">'.$web[544].'</label>
              <input class="form-check-input mt-2" type="checkbox" id="chkar" name="chkar" />
            </div>';
        }
		echo '
		    </div>
		  </div>
        </div>
        </div>
        <div class="table-responsive-md">
        <div class="d-table w-100">';
        if (!empty($serviz[177]) && !OK_RGPD_date($userprefs[1])) echo '
          <div class="d-table-row">
            <div class="d-table-cell text-center">
              <fieldset class="w-95 m-auto text-left border rounded"><legend>'.$web[14].'</legend>
                <p><strong>'.$web[646].'</strong></p>
                <p>'.$web[684].'</p>
                <div class="form-check">
                  <label for="rgpd">'.$web[415].$fix.'&nbsp;:&nbsp;</label>
                  <input type="checkbox" class="form-check-input ml-2 mt-2" id="rgpd" name="rgpd"'.Checked($rgpd).' />
                </div>
              </fieldset>
            </div>
          </div>';
        if ($serviz[100] != '0' && $serviz[95] == 'on' && ($serviz[98] == '' || $userprefs[1] == '')) {
            $lbl = $serviz[100] == '3' ? $web[642] : '';
            echo '
          <div class="d-table-row">
            <div class="d-table-cell text-center mt-4 mx-auto">'.dsp_crypt(0, 1, 3, 2, $lbl, $serviz[115] == 'on' ? $web[643] : '').'</div>
          </div>
          <div class="d-table-row">
            <div class="d-table-cell text-center">
              '.($serviz[100] == '3' ? '' : $web[530]).'
              <input class="'.($err_crypt ? 'errorInputText' : 'texte').' inputcrypt m-auto form-control" 
                type="text" name="crypt" onpaste="return false;" />
            </div>
          </div>';
        }
        echo '
          <div class="d-table-row"><div class="d-table-cell text-center"><br />'.SubmitButton($web[52]).'</div></div>
        </div>
        </div>
      </fieldset>
      </form>
    </div>
    </div>'.displayQuickConfig(NULL, false, 'contact&act=2&ctconfig='.$ctconfig, '', '', 13);;
	// Position Contact téléphonique  $ftelbelow[0] == 'on' => en bas
	if ($ftelbelow[0] == 'on') {
	// contact téléphonique
        if ($ftel[0] == 'on') {
            echo '  
    <div class="w-100 text-left">'.ForceToAbsolute(stripslashes($ftel[1 + $t])).'</div>';
        }
	}
	// Position Contact postal  $fletbelow[0] == 'on' => en bas
	if ($fletbelow[0] == 'on') {
    // contact par courrier
        if ($flet[0] == 'on') {
            echo '
    <div class="w-100 text-left">'.ForceToAbsolute(stripslashes($flet[1 + $t])).'</div>';
        }
	}			
    }
}
btable();
include 'inc/bpage.inc';
?>
