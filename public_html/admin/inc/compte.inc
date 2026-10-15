<?php
/*******************************************************************************
 *   Admin account member
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2024 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.24 (August 12, 2026) : correction variable skn_log 
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'compte.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[10] == '') {
        $nomzone = $admin[816];
        include 'access.inc';
        exit();
    }
}
$skn_logo = '';
$edit  = import('edit');
$uuser = import('uuser');
$regit = import('regit');
if ($uuser == NULL) $uuser = 'old';
$erreur = '';

$err_pseudo = false;
$err_uid    = false;
$err_uid2   = false;
$err_uemail = false;
$err_uweb   = false;
$err_uimg   = false;
$err_uext   = false;
$err_uenreg    = false;
$err_ucnct  = false;
$err_urgpd  = false;
$ishttps    = 1 != preg_match('!^https!', $site[3]) ? false : true;
$preprot    = $ishttps ? 'https://' : 'http://';
// LOg 
$datjourl = new DateTime('now');
$dateheureminiutesecl = $datjourl->format('Y_m_d');
$nomficlog = CHEMIN.'data/log/compte'.'_'.$dateheureminiutesecl.'.log';

 //   file_put_contents(($nomficlog, 'debut regit: '.$regit."\n", FILE_APPEND | LOCK_EX);

// fin de log
if ($regit == 1) {
	$pseudok = true;
	$msg = '';
	if ($uuser == 'new') {
		$upseudo = KeepGoodChars(RemoveConnector(RemoveQuote(stripslashes(CutLongWord(import('upseudo'), $serviz[148])))));
		$edit    = $upseudo;
	}
	else $userdb = ReadDBFields(MSGREP.$edit.DBEXT);
	
	$ulang      = import('ulang');
	$uemail     = checkEmail(CutLongWord(import('uemail')));
	$ushow      = import('ushow');
	$usign      = WrapLongWords(stripslashes(import('usign')));
	$usign      = RemoveConnector(stripslashes($usign));
	$usign      = str_replace(chr(10), '<br />', $usign);
	$uuid       = import('uuid');
	$uuid2      = import('uuid2');
	$avatar     = import('avatar');
	$uwebsite   = import('uwebsite');
	$ueditor    = 'on';
	$uextavatar = import('uextavatar');
	
	//   file_put_contents(($nomficlog, 'Retour import avatar ext uextavatar : '.$uextavatar."\n", FILE_APPEND | LOCK_EX);
	
	$umailct    = import('umailct');
	$umpct      = import('umpct');
	$usendmail  = import('usendmail');
	$usendct    = import('usendct');
	$uusemp     = import('uusemp');
	$uctmail    = import('uctmail');
	$uprfmemb   = import('uprfmemb');
	$ulstmemb   = import('ulstmemb');
	$uavaext    = import('uavaext');
	$uimgext    = import('uimgext');
	$uenreg     = import('uenreg');
	$ucnct      = import('ucnct');
	$uredir     = import('uredir');
	$ustatus    = import('ustatus');
	$urgpd      = import('urgpd');

	$uwebsite   = empty($uwebsite) ? 'http://' : $uwebsite;
	$uextavatar = empty($uextavatar) ? $preprot : $uextavatar;
	 //   file_put_contents(($nomficlog, ' Regit √† 1 uextavatar: '.$uextavatar."\n", FILE_APPEND | LOCK_EX);

	$err_pseudo = empty($edit) || mb_strtolower($edit) == 'admin' ? true: false;
    $err_uid    = ((empty($uuid) && $uuser == 'new') || (!empty($uuid) && !ctrlPasswordOK($uuid))) ? true: false;
	$err_uid   =  strlen($uuid) < 8  ? true: false;
	$err_uid2   = $uuid != $uuid2 ? true: false;
	$err_uemail = empty($uemail) ? true: false;
	$err_uweb   = 'http://' != $uwebsite && 'https://' != $uwebsite && 1 != preg_match('/(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/', $uwebsite) ? true : false;
	$err_uext   = $avatar == 'ava-00' && 1 != preg_match('/(http|https|inc\/img\/avatars):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/', $uextavatar) ? true : false;
	$err_uext   = $err_uext && !IsImage(substr($uextavatar, -3));

	$err_urgpd    = false;
	$date_urgpd = '';
	if (strlen($urgpd) > 0)
	{
		if (preg_match ('`(\d{1,4})(\d{1,2})(\d{2})`',$urgpd, $array_jours))
		{
			if (!checkdate($array_jours[2],$array_jours[3],$array_jours[1]))					
			{
				$err_urgpd    = true;
				$date_urgpd = $array_jours[2].'/'.$array_jours[3].'/'.$array_jours[1];
			}
		}
		else
		{
			$err_urgpd    = true;
			$date_urgpd   = $admin[20];
		}
	}
	$err_uenreg    = false;
	$date_uenreg = '';
	if (strlen($uenreg) > 0)
	{
		if (preg_match ('`(\d{1,4})(\d{1,2})(\d{2})`',$uenreg, $array_jours))
		{
			if (!checkdate($array_jours[2],$array_jours[3],$array_jours[1]))					
			{
				$err_uenreg    = true;
				$date_uenreg = $array_jours[2].'/'.$array_jours[3].'/'.$array_jours[1];
			}
		}
		else
		{
			$err_uenreg    = true;
			$date_uenreg   = $admin[20];
		}
	}	
	$err_ucnct    = false;
	$date_ucnct = '';
	if (strlen($ucnct) > 0)
	{
		if (preg_match ('`(\d{1,4})(\d{1,2})(\d{2})`',$ucnct, $array_jours))
		{
			if (!checkdate($array_jours[2],$array_jours[3],$array_jours[1]))					
			{
				$err_ucnct    = true;
				$date_ucnct = $array_jours[2].'/'.$array_jours[3].'/'.$array_jours[1];
			}
		}
		else
		{
			$err_ucnct    = true;
			$date_ucnct   = $admin[20];
		}
	}
	
	$erreur .= $err_pseudo ? '<li>'.$admin[1588].'</li>': '';
	$erreur .= $err_uid ? '<li>'.$admin[1610].'</li>': '';
	$erreur .= $err_uid2 ? '<li>'.$admin[1611].'</li>': '';
	$erreur .= $err_uemail ? '<li>'.$admin[544].'</li>': '';
	$erreur .= $err_uweb ? '<li>'.$admin[1585].$admin[1622].$admin[1587].'</li>': '';
	$erreur .= $err_uext ? '<li>'.$admin[1585].$admin[1624].$admin[1587].'</li>': '';
	$erreur .= $err_urgpd ? '<li>'.$admin[1889].' date_urgpd: '.$date_urgpd.'</li>': '';
	$erreur .= $err_uenreg ? '<li>'.$admin[1890].' date_uenreg: '.$date_uenreg.'</li>': '';
	$erreur .= $err_ucnct ? '<li>'.$admin[1891].' date_ucnct: '.$date_ucnct.'</li>': '';
	
	$uwebsite   = $err_uweb ? 'http://' : $uwebsite;
	$uextavatar = $err_uext ? $preprot : $uextavatar;
	
    if (empty($erreur)) {
		$dbmail = array();
		$bad    = array('.', '..', 'index.php');
		$files  = scandir(MSGREP);
		$files  = array_diff($files, $bad);
		foreach ($files as $file) {
			if (is_file(MSGREP.$file) && substr($file, -3) == 'dtb') {
				$userdb = ReadDBFields(MSGREP.$file);
				if (basename($file) == $edit.DBEXT) {
					if ($uuser == 'new') {
						$pseudok = false;
						$msg     = $admin[1625];
						break;
					}
				} elseif (isset($userdb[1][3])) {
					$dbmail[] = $userdb[1][3];
				}
			}
		}
		if ($pseudok && in_array($uemail, $dbmail)) {
			$pseudok = false;
			$msg    .= ' - '.$admin[1626];
		}
		if ($pseudok) {
            $ishttps = 1 != preg_match('!^https!', $site[3]) ? false : true;
			if ($uextavatar != $preprot) {
                $imghttps = 1 != preg_match('!^https!', $uextavatar) ? false : true;
                $uextavatar = $ishttps && !$imghttps ? 'inc/img/avatars/'.basename($uextavatar) : $uextavatar;
                $avaexist   = $ishttps && !$imghttps ? CHEMIN.$uextavatar : $uextavatar;
                if (file_exists($avaexist)) {
                    list($wimg, $himg, $timg, $aimg) = getimagesize($uextavatar);
                    if ($wimg == 0 || $himg == 0 || $wimg > 64 || $himg > 64) {
                        $uextavatar = $preprot;
                        $msg       .= ' - '.$admin[1628];
                    }
                }
			}

			if (empty($msg)) {
				$apachepassword  = Md5Crypt::apache ($uuid, $salt ='');	
 /*               $cryptpass = crypt($uuid, '');
                $decrypt   = crypt($uuid, '');
*/
				$cryptpass = $apachepassword;
                $decrypt   = $apachepassword;
                if (is_file(CHEMIN.'data/config/.htpasswd')) {
                    $htpasswd = @file(CHEMIN.'data/config/.htpasswd');
                     if (count($htpasswd) > 0 )
					{
						$protoht  = trim($htpasswd[0]);
						if (!empty($protoht)) {
							$cryptpass = $protoht == '#:0-1-0' ? gy_crypt(CRYPT_KEY, $uuid) : $apachepassword;
							$decrypt   = $protoht == '#:0-1-0' ? $uuid : $apachepassword;
						}
					}
                }
				$userdb        = (is_file(MSGREP.$edit.DBEXT)) ? ReadDBFields(MSGREP.$edit.DBEXT) : array();
				$userdb[0][0]  = $uuser == 'new' || $uuid != '' ? md5($uuid) : $userdb[0][0];
				$userdb[0][1]  = $uuser == 'new' || $uuid != '' ? $apachepassword : (isset($userdb[0][1]) ? $userdb[0][1] : $userdb[0][0]);
				$userdb[1][0]  = '0';
				$userdb[1][1]  = $ulang;
				$userdb[1][2]  = trim($edit);
				$userdb[1][3]  = trim($uemail);
				$userdb[1][4]  = '';
				$userdb[1][5]  = $ulang;
				$userdb[1][6]  = $ushow;
				$userdb[1][7]  = $usign;
				$userdb[1][8]  = $uuser == 'new' || $uuid != '' ? md5($uuid) : $userdb[1][8];
				$userdb[1][9]  = trim($avatar);
				$userdb[1][10] = $uwebsite == 'https://' || $uwebsite == 'http://' ? '' : $uwebsite;
				$userdb[1][11] = '';
				$userdb[1][12] = $ueditor;
				if ($uuser == 'new') {
					$userdb[1][13] = 'on';
					$userdb[1][14] = GetCurrentDateTime();
					$userdb[1][15] = '';
				}
				else {
					$userdb[1][13] = $ustatus == 'off' ? $ustatus : ($ustatus == 'ok' ? 'on' : $userdb[1][13]);
					$userdb[1][14] = $uenreg;
					$userdb[1][15] = $ucnct;
				}
				$userdb[1][16] = $uextavatar == $preprot || $avatar != 'ava-00' || $uavaext == 'on' ? '' : $uextavatar;
				$userdb[1][17] = isset($userdb[1][17]) ? $userdb[1][17] : '';
				$userdb[1][18] = $uusemp;
				$userdb[1][19] = $uctmail;
				$userdb[1][20] = $uprfmemb;
				$userdb[1][21] = $ulstmemb;
				$userdb[1][22] = $uavaext;
				$userdb[1][23] = $uimgext;
				$userdb[1][24] = $umailct;
				$userdb[1][25] = $umpct;
				$userdb[1][26] = $usendmail;
				$userdb[1][27] = $usendct;
				$userdb[1][28] = $uuser == 'new' ? '' : $urgpd;
				$userdb[1][29] = '';
				$userdb[1][30] = $uredir;
				$userdb[1][31] = $uuser == 'new' ? 'ok' : (empty($userdb[1][31]) ? 'ok' : ($ustatus == '1' || $ustatus == '2' || $ustatus == 'ok' ? $ustatus : $userdb[1][31]));
				$userdb[0][0]  = $userdb[1][31] == 'ok' && $userdb[0][0] != $userdb[1][8] ? $userdb[1][8] : $userdb[0][0];
				
                if ($uuser == 'new' && $userprefs[1] == $serviz[31]) {
					$userdb[2][0] = $serviz[31].CONNECTOR.GetCurrentDateTime().CONNECTOR.'&nbsp; '.
					$admin[1629].' '.sprintf($admin[1630], stripslashes($site[($lang[0] == $lng ? 0 : 11)]), $edit).CONNECTOR.'new'.CONNECTOR.''.
						CONNECTOR.''.CONNECTOR.''.CONNECTOR.$serviz[43].CONNECTOR.substr($page[23], 4);
				}
                if ($uuser == 'new' && $userprefs[1] == $serviz[176]) {
					$userdb[2][0] = $serviz[176].CONNECTOR.GetCurrentDateTime().CONNECTOR.'&nbsp; '.
					$admin[1629].' '.sprintf($admin[1630], stripslashes($site[($lang[0] == $lng ? 0 : 11)]), $edit).CONNECTOR.'new'.CONNECTOR.''.
						CONNECTOR.''.CONNECTOR.''.CONNECTOR.$serviz[43].CONNECTOR.substr($page[23], 4);
				}
				
				WriteDBFields(MSGREP.$edit.DBEXT, $userdb);
                
                if ($edit == $userprefs[1]) {
                    $userdata = '';
                    for ($i = 1; $i < 32; $i++) {
                        $stri      = sprintf("%02d", $i - 1);
                        $strlen    = empty($userdb[1][$i]) ? '03' : sprintf("%02d", strlen($userdb[1][$i]));
                        $userdata .= $stri.$strlen.(empty($userdb[1][$i]) ? 'off' : $userdb[1][$i]);
                    }
                    
// avant                    $usertime = !empty($members[21]) ? intval($members[21]) : 157680000;
// avant                    $expires  = (is_file(CHEMIN.'admin/'.REDACREP.$edit.INCEXT) || $edit == $serviz[31] || $edit == $serviz[176]) ? 157680000 : $usertime;
// avant                   setcookie(USER_COOKIE, gy_crypt(CRYPT_KEY, $userdata), time() + $expires, '/', $_DOMAIN, $_SECURE);
					setcookie(USER_COOKIE, gy_crypt(CRYPT_KEY, $userdata), strtotime( '+365 days' ), '/', $_DOMAIN, $_SECURE);
                }
				
				if ($uuser == 'new') {
					$adrimg = $skn_logo != '' ? $skn_logo : CHEMIN.'img/'.mb_substr($page[4], mb_strpos($page[4], '|') + 1);
                    $tconf  = $serviz[172] == 'ALL' ? 0 : $serviz[172];
					while ('../' == substr($adrimg, 0, 3)) $adrimg = substr($adrimg, 3);
					$to    = $uemail.($supervision[11] == 'on' ? ' '.$user[1] : '');
					$sujet = stripslashes($site[($lang[0] == $lng ? 0 : 11)]).' - '.$admin[1631];
					$bodycorps  = '<div style="margin:auto;padding:8px;width:600px;border: solid 1px #000000;border-radius:4px;">';
					$bodycorps .= '<p style="text-align:center;"><img src="'.$site[3].$adrimg.'" style="max-width:600px;" /></p><hr />';
					$bodycorps .= '<p style="text-align:left;font:12pt Arial;">';
					$bodycorps .= $admin[1632].sprintf($admin[1633], $edit, stripslashes($site[($lang[0] == $lng ? 0 : 11)])).'<br /><br />';
					$bodycorps .= $admin[1013].' <b>'.$edit.'</b><br />'.$admin[1634].' : <b>'.$uuid.'</b><br /><br />';
					$bodycorps .= $admin[1635];
					$bodycorps .= '<a href="'.$site[3].($site['URLR'] == 'on'? $lng.'-'.$urlrw[3].'-4-'.$edit.'-'.$uuid.'-'.$tconf : 'connect.php?lng='.$lng.'&pseudo='.$edit.'&uid='.$uuid.'&tconfig='.$tconf).'">'.stripslashes($site[($lang[0] == $lng ? 0 : 11)]).'</a><br />';
					$bodycorps .= $admin[1760].'<br />'.$user[0].'<br />';
					$bodycorps .= '</p></div>';
					eMailHtmlTo($sujet, $bodycorps, $to);			
				} elseif (!empty($uuid)) {
					UpdateCollaboratorPass($edit, md5($uuid));
                    UpdateProtectMember($edit, $decrypt);
				}
			}
		}
	}
	if (!empty($msg)) {
		$erreur .= '<li>'.$msg.'</li>';
        echo BeginJavascript().'
alert("'.addslashes($msg).'");
'.EndJavascript();
    }
	if (empty($erreur)) {
        echo BeginJavascript().'
window.location = "'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg=members&act=2";'.EndJavascript();
		exit();
	}
}

$headinc .= BeginJavascript().'
function VerifyForm() {
    var sto = "";
    var erreur = false;
	var uweb = document.userprefnow.uwebsite.value;
	var uext = document.userprefnow.uextavatar.value;
    regexp = /^[^\.\s]+(\.[^\.\s]+)*@[^\.\s]+(\.[^\.\s]+)+$/;
    if (!regexp.test(document.userprefnow.uemail.value)) {
        sto += \'  - '.addslashes($admin[544]).'\n\';
		document.userprefnow.uemail.className = "errorInputText";
        erreur = true;
    }
	';
if ($uuser != 'new')
{
	$headinc .= '
	var uid = document.userprefnow.uuid.value;
	var uid2 = document.userprefnow.uuid2.value;

    if (uid != "") {
        regexp = /^\\S{8,40}$/;
        if (!regexp.test(uid)) {
            sto += \'  - '.addslashes($admin[1610]).'\n\';
			document.userprefnow.uuid.className = "errorInputText";
            erreur = true;
        }
    }
	if (uid != uid2) {
		sto += \'  - '.addslashes($admin[1611]).' !\n\';
		document.userprefnow.uuid.className = "errorInputText";
		document.userprefnow.uuid2.className = "errorInputText";
		erreur = true;
	}';
}
else
	$headinc .= '
    regexp = /^[-a-zA-Z0-9_]{2,'.$serviz[148].'}$/;
    if (!regexp.test(document.userprefnow.upseudo.value)) {
        sto += \'  - '.addslashes($admin[1588]).'\n\';
		document.userprefnow.upseudo.className = "errorInputText";
        erreur = true;
    }';
$headinc .= '
	regexp = /(http|https|inc/img/avatars):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
    if (uweb != "http://" && uweb != "https://" && !regexp.test(uweb)) {
        sto += \'  - '.addslashes($admin[1585].$admin[1622].$admin[1587]).'\n\';
		document.userprefnow.uwebsite.className = "errorInputText";
		document.userprefnow.uwebsite.value = "http://";
        erreur = true;
    }
    if (uext != "'.$preprot.'" && !regexp.test(uext)) {
        sto += \'  - '.addslashes($admin[1585].$admin[1624].$admin[1587]).'\n\';
		document.userprefnow.uextavatar.className = "errorInputText";
		document.userprefnow.uextavatar.value = "'.$preprot.'";
        erreur = true;
    }
    if (erreur == true) {
        sto = "'.addslashes($admin[1636]).'\n\n" + sto;
        alert(sto);
		return false;
    }
}'.EndJavascript();

$topmess = '
  <a href="admin.php?lng='.$lng.'">Admin</a> >&nbsp;
  <a href="admin.php?lng='.$lng.'&amp;pg=members&amp;act=2">'.$admin[696].'</a> > '.($uuser == 'new' ? $admin[1308] : $admin[498]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';

if ($uuser != 'new' && !FileDBExist(MSGREP.$edit.DBEXT)) {
    echo BeginJavascript().'
window.location = "'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg=members&act=2";'.EndJavascript();
	exit();
} else {
	if ($uuser == 'new') {
		$userdb       = array();
		$userdb[0][0] = '';
		$userdb[1]    = array_pad(array(), 32, '');		
		$value_pseudo = ' value=""';
	} else {
		$userdb = ReadDBFields(MSGREP.$edit.DBEXT);
		$value_pseudo = ' value="'.$edit.'" readonly="readonly"';
	}
	htable($uuser == 'new' ? $admin[1308] : $admin[498].' - '.$edit, '100%');
	if (!empty($erreur))
		echo displayErrorMsg($admin[1636], $erreur);
	echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=978&mnuid=937&tconfig=0#z2').'</p>
<br />
<hr />
<style type="text/css">
.cmpt-password {
	font-size: 9px;
	text-transform: uppercase;
	position: absolute;
	cursor: pointer;
	margin: 6px 0 0 -26px;
}
</style>
<script text="javascript">
  $(document).ready(function(){
    $(".cmpt-password").click(function() {
        if($(this).prev("input").prop("type") == "password") {
            $(this).prev("input").prop("type","text");
            $(this).children("img").prop("title","'.$admin[1748].'");
        } else {
            $(this).prev("input").prop("type","password");
            $(this).children("img").prop("title","'.$admin[1747].'");
        }
    });
    $("#testuuid").on("keyup", function(e) {
      var strongRegex = new RegExp("^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\\\W).*$", "ig");
      var mediumRegex = new RegExp("^(?=.{8,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g");
      var okRegex = new RegExp("(?=.{8,}).*", "g");
      if (okRegex.test($(this).val()) === false) {
        $("#uuid-strength").removeClass().addClass("alert alert-error").html("'.$help[21].'");
      } else if (strongRegex.test($(this).val())) {
        $("#uuid-strength").removeClass().addClass("alert alert-success").html("'.$help[22].'");
      } else if (mediumRegex.test($(this).val())) {
        $("#uuid-strength").removeClass().addClass("alert alert-info").html("'.$help[23].'");
      } else {
        $("#uuid-strength").removeClass().addClass("alert alert-error").html("'.$help[24].'");
      }
      return true;
    });
  });
</script>
<div class="w-100 m-auto" style="max-width:720px;">
  <form name="userprefnow" id="userprefnow" action="admin.php?lng='.$lng.'&amp;pg=compte" method="post" onsubmit="return VerifyForm(); return false;">
    <div class="row quest p-0 mt-2 w-100 m-auto">
	  <div class="col-md-6">
        '.SubmitButton($admin[857].$admin[696], 'self.location.href=\'admin.php?lng='.$lng.'&pg=members&act=2\'', $imggauche, '', 'button').'
      </div>
      <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[38], '', '', $imgdroite).'</div></div>
    </div>
    <input type="hidden" name="regit" value="1" />';
	if ($uuser == 'new') {
		$mpass = GenerateUID();
		echo '
    <input type="hidden" name="uuser" value="new" />
    <input type="hidden" name="uuid" value="'.$mpass.'" />
    <input type="hidden" name="uuid2" value="'.$mpass.'" />';
	}
	else
		echo '
    <input type="hidden" name="edit" value="'.$edit.'" />';
	echo '
    <br />
  	<fieldset class="m-0 p-0" style="margin-top:-10px;">'.legend($admin[810]).'
      <div class="table-responsive-md">
      <table class="table m-auto mb-3 w-100">
		<tr>
		  <td style="width:65%;padding:4px 2px;"><label for="uusemp">&bull; '.sprintf($admin[1637], $edit).'</label></td>
		  <td style="padding:4px 2px;"><input type="checkbox" id="uusemp" name="uusemp"'.Checked($userdb[1][18] == 'on').' /></td>
		</tr>
		<tr>
		  <td style="padding:4px 2px;"><label for="uctmail">&bull; '.sprintf($admin[1638], $edit).'</label></td>
		  <td style="padding:4px 2px;"><input type="checkbox" id="uctmail" name="uctmail"'.Checked($userdb[1][19] == 'on').' /></td>
		</tr>
		<tr>
		  <td style="padding:4px 2px;"><label for="uprfmemb">&bull; '.sprintf($admin[1639], $edit).'</label></td>
		  <td style="padding:4px 2px;"><input type="checkbox" id="uprfmemb" name="uprfmemb"'.Checked($userdb[1][20] == 'on').' /></td>
		</tr>
		<tr>
		  <td style="padding:4px 2px;"><label for="ulstmemb">&bull; '.sprintf($admin[1640], $edit).'</label></td>
		  <td style="padding:4px 2px;"><input type="checkbox" id="ulstmemb" name="ulstmemb"'.Checked($userdb[1][21] == 'on').' /></td>
		</tr>
		<tr>
		  <td style="padding:4px 2px;"><label for="uavaext">&bull; '.$admin[1641].'</label></td>
		  <td style="padding:4px 2px;"><input type="checkbox" id="uavaext" name="uavaext"'.Checked($userdb[1][22] == 'on').' /></td>
		</tr>
		<tr>
		  <td style="padding:2px;">&bull; '.$admin[195].'</td>
		  <td style="padding:2px;">
		    <select class="form-control" style="max-width: 160px;" name="ustatus">
			  <option value="">'.ucfirst($admin[1108]).'</option>
			  <option value="ok"'.Selected($userdb[1][31] == 'ok').'>'.$admin[1471].'</option>
              <option value="1"'.Selected($userdb[1][31] == '1').'>'.$admin[1302].'</option>
              <option value="2"'.Selected($userdb[1][31] == '2').'>'.$admin[1303].'</option>
			  <option value="off"'.Selected($userdb[1][13] == 'off').'>'.$admin[1472].'</option>
			</select>
		  </td>
		</tr>
      </table>
      </div>
      <br />
    </fieldset>
    <br />
  	<fieldset class="m-0 p-0" style="margin-top:-10px;">'.legend($admin[1644]).'
      <div class="table-responsive-md">
      <table class="table m-auto mb-3 w-100">
        <tr>
          <td class="w-55 align-middle">&bull; '.$admin[1013].'</td>
          <td><input type="text" class="w-90 form-control '.($err_pseudo ? 'errorInputText' : 'texte').'" name="upseudo" size="30"'.$value_pseudo.' /></td>
        </tr>';
	if ($uuser != 'new') 
		echo '
        <tr><td colspan="2" style="padding:4px;"><div class="" id="uuid-strength"></div></td></tr>
        <tr>
          <td class="w-55 align-middle">&bull; '.$admin[1634].' :</td>
          <td>
            <div class="d-inline-block testuid input-group mb-3 w-80">
              <input 
                id="testuid" 
                type="password" 
                class="form-control '.($err_uid ? 'errorInputText' : 'texte').' w-90 float-left" 
                name="uuid" 
                value="" 
              />
              <div id="testuid-eye" class="input-group-append c-pointer" style="padding-top:0.3rem;">
                <span class="input-group-text ml-n2" style="font-size:1.2rem;height:2.5rem;border:1px solid #000;border-radius:0 4px 4px 0;">
                  <i id="testuid-i" class="far fa-eye"></i>
                </span>
              </div>
            </div>
            <script>
              document.getElementById("testuid-eye").addEventListener("click", function(e){
                var pwd = document.getElementById("testuid");
                var eye = document.getElementById("testuid-i");
                if(pwd.getAttribute("type") == "password") {
                  pwd.setAttribute("type", "text");
                  eye.setAttribute("class", "far fa-eye-slash");
                } else {
                  pwd.setAttribute("type", "password");
                  eye.setAttribute("class", "far fa-eye");
                }
              });
            </script>
            <script>
              $(document).ready(function(){
                $(\'.popuserpwd\').popover({
                  title:"'.$admin[1634].'",
                  content:"'.$help[3].'",
                  trigger:"hover",
                  placement:"auto",
                  html:true
                });
              });
            </script>
            <div class="d-inline-block ml-4 overpop">
              <a class="popuserpwd" title="'.$admin[1634].'">
                <i class="far fa-comment-dots"></i>
              </a>
            </div>
			<div style="clear:both"></div>
          </td>
        </tr>
        <tr>
          <td class="w-55 align-middle">&bull; '.$admin[1645].' :</td>
          <td>
            <div class="d-inline-block testuid input-group mb-3 w-80">
              <input 
                id="testuid2" 
                type="password" 
                class="form-control '.($err_uid2 ? 'errorInputText' : 'texte').' w-90 float-left" 
                name="uuid2" 
                value="" 
              />
              <div id="testuid2-eye" class="input-group-append c-pointer" style="padding-top:0.3rem;">
                <span class="input-group-text ml-n2" style="font-size:1.2rem;height:2.55rem;border:1px solid #000;border-radius:0 4px 4px 0;">
                  <i id="testuid2-i" class="far fa-eye"></i>
                </span>
              </div>
            </div>
            <script>
              document.getElementById("testuid2-eye").addEventListener("click", function(e){
                var pwd = document.getElementById("testuid2");
                var eye = document.getElementById("testuid2-i");
                if(pwd.getAttribute("type") == "password") {
                  pwd.setAttribute("type", "text");
                  eye.setAttribute("class", "far fa-eye-slash");
                } else {
                  pwd.setAttribute("type", "password");
                  eye.setAttribute("class", "far fa-eye");
                }
              });
            </script>
        </td>
        </tr>';
	echo '
        <tr>
          <td class="w-55 align-middle">&bull; '.$admin[1646].'</td>
          <td><input type="text" class="form-control w-90 '.($err_uemail ? 'errorInputText' : 'texte').'" name="uemail" size="40" value="'.$userdb[1][3].'" /></td>
        </tr>
        <tr>
          <td class="w-55 align-middle">&bull; '.$admin[1622].'</td>
          <td><input type="text" class="form-control w-90 '.($err_uweb ? 'errorInputText' : 'texte').'" name="uwebsite" size="40" value="'.(empty($userdb[1][10]) || $userdb[1][10] == 'user9' ? $preprot : $userdb[1][10]).'" /></td>
        </tr>
        <tr>
          <td class="w-55 align-middle">&bull; '.$admin[1647].'</td>
          <td><textarea class="form-control w-100" name="usign" rows="2">'.stripslashes($userdb[1][7] == 'user6' ? '' : $userdb[1][7]).'</textarea></td>
        </tr>
		<tr>
		  <td class="w-55 align-middle" style="padding:4px 2px;"><label for="ushow">&bull; '.$admin[1648].' :</label></td>
		  <td style="padding:4px 2px;"><input type="checkbox" id="ushow" name="ushow"'.Checked($userdb[1][6] == 'on').' /></td>
		</tr>';
	echo '
		<tr>
		  <td class="w-55 align-middle" style="padding:4px 2px;"><label for="umailct">&bull; '.$admin[1649].'</label></td>
		  <td style="padding:4px 2px;"><input type="checkbox" id="umailct" name="umailct"'.Checked($userdb[1][24] == 'on').' /></td>
		</tr>
		<tr>
		  <td class="w-55 align-middle" style="padding:4px 2px;"><label for="umpct">&bull; '.$admin[1650].'</label></td>
		  <td style="padding:4px 2px;"><input type="checkbox" id="umpct" name="umpct"'.Checked($userdb[1][25] == 'on').' /></td>
		</tr>
		<tr>
		  <td class="w-55 align-middle" style="padding:4px 2px;"><label for="usendmail">&bull; '.$admin[1651].'</label></td>
		  <td style="padding:4px 2px;"><input type="checkbox" id="usendmail" name="usendmail"'.Checked($userdb[1][26] == 'on').' /></td>
		</tr>
		<tr>
		  <td class="w-55 align-middle" style="padding:4px 2px;"><label for="usendct">&bull; '.$admin[1652].'</label></td>
		  <td style="padding:4px 2px;"><input type="checkbox" id="usendct" name="usendct"'.Checked($userdb[1][27] == 'on').' /></td>
		</tr>
      </table>
      </div>
      <br />
    </fieldset>
    <br />
  	<fieldset class="m-0 p-0" style="margin-top:-10px;">'.legend($admin[1653]).'
      <div class="table-responsive-md">
      <table class="table m-auto mb-2 w-100">';
	if ($lang[1] != '') {
		echo '
        <tr>
          <td class="w-45 align-middle">&bull; '.$admin[947].'</td>
          <td>
            <select class="form-control w-100" style="max-width:70px;" name="ulang">
              <option value="'.$lang[0].'"'.Selected($userdb[1][1] == $lang[0]).'>'.$lang[0].'</option>
              <option value="'.$lang[1].'"'.Selected($userdb[1][1] == $lang[1]).'>'.$lang[1].'</option>
            </select>
          </td>
        </tr>';
	} else {
		echo '
        <input type="hidden" name="ulang" value="'.$lang[0].'" />';
	}
	echo '
        <tr>
          <td class="w-45 align-middle"><label for="uredir">&bull; '.$admin[1660].'</label></td>
          <td><input type="checkbox" id="uredir" name="uredir"'.Checked($userdb[1][30]).' /></td>
        </tr>
      </table>
      </div>
      <div class="table-responsive-md">
	  <table class="table m-auto mb-2 w-100">
        <tr>
          <td colspan="2" style="text-align:center;">';
	if ($page[23] != 'none') {
		echo '
            <fieldset class="m-0 p-0">'.legend($admin[1662]).'
			  <div style="max-height:420px;overflow:auto;">
                <div class="table-responsive-md">
                <table class="table m-auto w-60">
                  <tr>';
            @include CHEMIN.'inc/img/avatars/'.$page[23].'.inc';
            $nbrimgavatar = 0; $j = $imgavatars;
            for ($i = 1; $i < $imgavatars; $i++) {
                $ava  = substr('0'.(string)$i, -2);
                echo '
			        <td class="text-center">
                      <label for="'.$ava.'">
                        <i class="ava-'.$ava.'" title="'.$page[23].'-'.$ava.'"></i>
			            <div>
                          <input id="'.$ava.'" type="radio" value="ava-'.$ava.'" name="avatar"'.Checked($userdb[1][9] == 'ava-'.$ava).' />
                        </div>
                      </label>
			        </td>';
                $nbrimgavatar++; $j--;
                if ($nbrimgavatar == 7 && $j != 0) {
                    $nbrimgavatar = 0;
                    echo '
                  </tr>
		          <tr>
            ';
                }
            }
            $vid = 7 - $imgavatars%7 ;
            if ($vid != 7) {
                for ($i = 0; $i < $vid; $i++)
                    echo '
		            <td class="text-center">&nbsp;</td>';
            }
            echo '
                  </tr>
                </table>
                </div>
			  </div>';
		echo '
            </fieldset>
		    <div style="width:480px;text-align:center;margin:16px auto;">
		      <div class="d-inline-block float-left pt-3">
 		        <label>
                  <input type="radio" value="ava-00" name="avatar"'.Checked($userdb[1][9] == 'ava-00' || (!empty($userdb[1][16]) && $userdb[1][16] != $preprot)).' />
                  '.$admin[1624].' :
                </label>
		      </div>
		      <div class="d-inline-block float-left">
			    <input type="text" class="form-control '.($err_uext ? 'errorInputText' : 'texte').'" name="uextavatar" 
                  style="width:320px;" value="'.(empty($userdb[1][16]) ? $preprot : $userdb[1][16]).'" />
		      </div>
              <script>
                $(document).ready(function(){
                  $(\'.popavaext\').popover({
                    title:"'.$admin[1624].'",
                    content:"'.$help[8].'",
                    trigger:"hover",
                    placement:"auto",
                    html:true
                  });
                });
              </script>
              <div class="d-inline-block float-left overpop">&nbsp;
                <a class="popavaext" title="'.$admin[1624].'">
                  <i class="far fa-comment-dots"></i>
                </a>
              </div>
		    </div>
		    <div class="clearfix"></div>';
		if ($userdb[1][9] == 'ava-00' && !empty($userdb[1][16]))
			echo '
		    <div style="text-align:center;width:100%;margin:auto;">
		      <img src="'.$userdb[1][16].'" alt="" title="'.$edit.'" style="max-width:60px; max-height:60px;" />
		    </div>';
	}
	echo '
          </td>
        </tr>
      </table>
      </div>
    </fieldset>';
	if (!is_numeric($userdb[1][28])) $userdb[1][28] = '';
	if ($uuser != 'new')
		echo '
  	<fieldset class="m-0 mt-3 p-0">
      <div class="table-responsive-md">
	  <table class="table m-auto mb-4 w-100">
		<tr>
		  <td class="w-40 align-middle">'.$admin[1663].'</td>
		  <td class="w-20 align-middle"><b>'.(isset($userdb[1][15])? FormatDate($userdb[1][14]): '???').'</b></td>
		  <td class="w-40 align-middle"><input type="texte" class="form-control '.($err_uenreg ? 'errorInputText' : 'texte').'" name="uenreg" size="14" value="'.$userdb[1][14].'" /></td>
		</tr>
		<tr>
		  <td>'.$admin[1664].'</td>
		  <td><b>'.(isset($userdb[1][15])? FormatDate($userdb[1][15]): '???').'</b></td>
		  <td><input type="texte" class="form-control '.($err_ucnct ? 'errorInputText' : 'texte').'" name="ucnct" size="14" value="'.$userdb[1][15].'" /></td>
		</tr>
		<tr>
		  <td>'.$admin[1759].'</td>
		  <td><b>'.(isset($userdb[1][28])? FormatDate($userdb[1][28]): '???').'</b></td>
		  <td><input type="texte" class="form-control '.($err_urgpd ? 'errorInputText' : 'texte').'" name="urgpd" size="14" value="'.$userdb[1][28].'" /></td>
		</tr>
      </table>
      </div>
    </fieldset>
    ';
	echo '
    <div class="row quest p-0 mt-2 w-100 m-auto">
	  <div class="col-md-6">
        '.SubmitButton($admin[857].$admin[696], 'self.location.href=\'admin.php?lng='.$lng.'&pg=members&act=2\'', $imggauche, '', 'button').'
      </div>
      <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[38], '', '', $imgdroite).'</div></div>
    </div>
  </form>
</div>';
}

btable();
include CHEMIN.'admin/bpage.inc';
?>
