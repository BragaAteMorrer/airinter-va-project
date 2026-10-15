<?php
/*******************************************************************************
 *   Zone members configuration
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
 * v6.00.24 (August 12, 2026) : Modify send message to users or groups
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'members.inc')) {
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
if (!isset($serviz[31]) || !isset($serviz[176]))
{
    die("!!! Erreur - Veuillez contacter le webmaster du site. !!! Error - Please contact the site's webmaster");
}
require CHEMIN.'admin/editors/editors_functions.php';
$regit   = import('regit');
$act     = import('act');
$idpg    = import('idpg');
$del     = import('del');
$acc     = import('acc');
$susp    = import('susp');
$suppr   = import('suppr');
$group   = import('group');
$member  = import('member');
$msg1    = import('msg1');
$selmbrgrp  = import('selmbrgrp', 'POST', '',0); // 0 ==> chx usr, 1 ==> chx grp
// LOg 
/*
$datjourl = new DateTime('now');
$dateheureminiutesecl = $datjourl->format('Y_m_d');
$nomficlog = CHEMIN.'data/log/members'.'_'.$dateheureminiutesecl.'.log';
file_put_contents($nomficlog, 'ligne 47 strlen msg1: '.strlen($msg1)."\n", FILE_APPEND | LOCK_EX);
*/
// fin de log
$msg2    = import('msg2'); // message langue 2
$implistgroup    = import('implistgroup', 'POST', FALSE); // liste reservé au groupe
$implistuser    = import('implistuser', 'POST', FALSE); //Liste Multiple user

$usearch = import('usearch');
$search  = import('search');
$depuis  = import('depuis');
$tri     = import('tri');
$nokey   = import('nokey', 'GET', TRUE, 0);

include CONFIG;
if (is_file(CHEMIN.'skins/'.$page[14].'/confskin'.INCEXT)){
    @include CHEMIN.'skins/'.$page[14].'/confskin'.INCEXT;
} else {
    @include CHEMIN.'skins/no_skin/confskin'.INCEXT;
}


$err_msg1 = $err_msg2 = '';
if ($act == 9) // controle si message présent?
{
	$headinc .= BeginJavascript().'
	function VerifyForm() {
	var sto    = "";
	var erreur = false;
	var lng2   = "'.$lang[1].'";

	var m1 = document.membmsg.msg1.value;
	if (m1.length < 1) {
		sto += "  - '.addslashes($admin[1692].$admin[1177]).'\n";
		document.membmsg.msg1.className = "errorInputText";
		erreur = true;
	}
	if (erreur == true) {
		sto = "'.addslashes($admin[1695]).'\n\n" + sto;
		alert(sto);
		return false;
	}
	}'.EndJavascript();
	$nbrmembsel = $nbrgroupsel = 0;
	$err_msg1 = $err_msg2 = $err_implistuser= '';
	// $err_msg1 = empty($msg1) && $regit == 4 ? true: false;
	if (strlen($msg1) == 0)
		$err_msg1 ='<li>'.$admin[1692].$admin[1177].'</li>';
	if (strlen($msg2) == 0 && $lang[1] != '')
		$err_msg2 ='<li>'.$admin[1692].$admin[1178].'</li>';
	if (is_array($implistuser))
		$nbrmembsel = count($implistuser);
	if (is_array($implistgroup))
		$nbrgroupsel = count($implistgroup);
	if ($selmbrgrp == 0)
	{
		if ($nbrmembsel == 0)
		$err_implistuser = '<li>'.$admin[2000].'</li>';
	}
	if ($selmbrgrp == 1)
	{
		if ($nbrgroupsel == 0)
		$err_implistuser = '<li>'.$admin[2001].'</li>';;
	}
	$erreur  = '';
	$erreur .= $err_msg1;
	$erreur .= $err_msg2;
	$erreur .= $err_implistuser;
	if (!empty($erreur) && $act == 9 ) $act = 3;
	if ($act == 9)
	{
		$act = 4;
		$regit = 5;
	}
}
if ($regit == 'fast') {
    $dbids = explode(' ', trim(import('ids')));
    $grps  = ReadGroups();
    $lst   = $grps[$group];
    @usort($lst, 'AsciiCompare');
    $mess = '';
    foreach ($dbids as $ii) {
        $member = $lst[$ii];
        if (isset($_POST['s_'.$ii]) && $_POST['s_'.$ii] == 'on') {
            $mess .= DelMember($group, $member) ? ucfirst($admin[928]).' '.$group.' : '.$admin[1693].' '.$member.$admin[930].' !<br />' : ucfirst($admin[1693]).' '.$member.' '.mb_strtolower($admin[1604], $charset).$admin[930].' !<br />';
        }
    }
    $act = 6;
}

if ($regit == 2) {
    include CONFIG;
    $menuico[11] = import('menuico11'); // Icône connexion
    $nom[14]     = import('nom14', 'POST', false); // Bouton Connexion langue principale
    $nom[15]     = import('nom15', 'POST', false); // Bouton connexion deuxième langue
    $nom[28]     = import('nom28', 'POST', false); // Bouton déconnexion langue principale
    $nom[29]     = import('nom29', 'POST', false); // Bouton déonnexion deuxième langue
    include('inc/confupdt.inc');
    if ($site[7] == 'on' || PHP700) {
        @ob_flush();
        sleep(PHP700 ? 3 : $site[8]);
        echo BeginJavascript().'window.location="'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg=members&act=2";'.EndJavascript();
    }
}
if ($regit == 3) {
    include CONFIG;
    $members[19] = import('members19'); // Inscription membres on/off/mail/ok webmaster
    include('inc/confupdt.inc');
    if ($site[7] == 'on' || PHP700) {
        @ob_flush();
        sleep(PHP700 ? 3 : $site[8]);
        echo BeginJavascript().'window.location="'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg=members&act=2";'.EndJavascript();
    }
}
if ($regit == 4) {
    $msg = '';
    for($i=0; $i<20; $i++) {
		$upseudo = KeepGoodChars(RemoveConnector(RemoveQuote(stripslashes(CutLongWord(import('upseudo'.$i), $serviz[148])))));
        $uemail  = checkEmail(CutLongWord(import('uemail'.$i)));
        if (empty($upseudo) || mb_strtolower($upseudo) == 'admin' || empty($uemail) || is_file(MSGREP.$upseudo.DBEXT)) continue;
        if (!empty($upseudo) && is_file(MSGREP.$upseudo.DBEXT)) $msg .= '-'.$upseudo.' : '.$admin[1714];
		$dbmail  = array();
		$bad     = array('.', '..', 'index.php');
		$files   = scandir(MSGREP);
		$files   = array_diff($files, $bad);
        $ok      = true;
		foreach ($files as $file) {
			if (is_file(MSGREP.$file) && substr($file, -3) == 'dtb') {
                $userdb = ReadDBFields(MSGREP.$file);
                if ($userdb[1][3] == $uemail) {
                    $ok = false;
                    $msg .= '-'.$upseudo.' : '.$admin[1626];
                    break;
                }
			}
		}
        if (!$ok) continue;
        $uuid          = trim(import('uuid'.$i));
        $uuid          = empty($uuid) ? GenerateUID() : $uuid;
//        $cryptpass     = crypt($uuid);
		$apachepassword  = Md5Crypt::apache ($uuid, $salt ='');
		$cryptpass     = $apachepassword;
        $userdb        = array();
        $userdb[0][0]  = md5($uuid);
        $userdb[0][1]  = $cryptpass;
        $userdb[1][0]  = '0';
        $userdb[1][1]  = $lang[0];
        $userdb[1][2]  = $upseudo;
        $userdb[1][3]  = trim($uemail);
        $userdb[1][4]  = 'LR';
        $userdb[1][5]  = $lang[0];
        $userdb[1][6]  = 'on';
        $userdb[1][7]  = '';
        $userdb[1][8]  = md5($uuid);
        $userdb[1][9]  = '';
        $userdb[1][10] = '';
        $userdb[1][11] = '';
        $userdb[1][12] = 'on';
        $userdb[1][13] = 'on';
        $userdb[1][14] = GetCurrentDateTime();
        $userdb[1][15] = '';
        $userdb[1][16] = '';
        $userdb[1][17] = '';
        $userdb[1][18] = import('uusemp'.$i);
        $userdb[1][19] = import('uctmail'.$i);
        $userdb[1][20] = import('uprfmemb'.$i);
        $userdb[1][21] = import('ulstmemb'.$i);
        $userdb[1][22] = import('uavaext'.$i);
        $userdb[1][23] = import('uimgext'.$i);
        $userdb[1][24] = '';
        $userdb[1][25] = '';
        $userdb[1][26] = '';
        $userdb[1][27] = '';
        $userdb[1][28] = '';
        $userdb[1][29] = import('unobox'.$i);
        $userdb[1][30] = '';
        $userdb[1][31] = 'ok';
        if ($userprefs[1] == $serviz[31]) {
            $userdb[2][0] = $serviz[31].CONNECTOR.GetCurrentDateTime().CONNECTOR.'&nbsp; '.
            $admin[1629].' '.sprintf($admin[1630], stripslashes($site[($lang[0] == $lng ? 0 : 11)]), $upseudo).CONNECTOR.'new'.CONNECTOR.''.
                CONNECTOR.''.CONNECTOR.''.CONNECTOR.$serviz[43].CONNECTOR.substr($page[23], 4);
        }
        if ($userprefs[1] == $serviz[176]) {
            $userdb[2][0] = $serviz[176].CONNECTOR.GetCurrentDateTime().CONNECTOR.'&nbsp; '.
            $admin[1629].' '.sprintf($admin[1630], stripslashes($site[($lang[0] == $lng ? 0 : 11)]), $upseudo).CONNECTOR.'new'.CONNECTOR.''.
                CONNECTOR.''.CONNECTOR.''.CONNECTOR.$serviz[43].CONNECTOR.substr($page[23], 4);
        }
        WriteDBFields(MSGREP.$upseudo.DBEXT, $userdb);
        $adrimg = $skn_logo != '' ? $skn_logo : CHEMIN.'img/'.mb_substr($page[4], mb_strpos($page[4], '|'));
        $tconf  = $serviz[172] == 'ALL' ? 0 : $serviz[172];
        while ('../' == substr($adrimg, 0, 3)) $adrimg = substr($adrimg, 3);
        $to    = $uemail;
        $sujet = stripslashes($site[($lang[0] == $lng ? 0 : 11)]).' - '.$admin[1631];
        $bodycorps  = '<div style="margin:auto;padding:8px;width:600px;border: solid 1px #000000;border-radius:4px;">';
        $bodycorps .= '<p style="text-align:center;"><img src="'.$site[3].$adrimg.'" style="max-width:600px;" /></p><hr />';
        $bodycorps .= '<p style="text-align:left;font:12pt Arial;">';
        $bodycorps .= $admin[1632].sprintf($admin[1633], $upseudo, stripslashes($site[($lang[0] == $lng ? 0 : 11)])).'<br /><br />';
        $bodycorps .= $admin[1013].' <b>'.$upseudo.'</b><br />'.$admin[1634].' : <b>'.$uuid.'</b><br /><br />';
        $bodycorps .= $admin[1635];
        $bodycorps .= '<a href="'.$site[3].($site['URLR'] == 'on'? $lng.'-'.$urlrw[3].'-4-'.$upseudo.'-'.$uuid.'-'.$tconf : 'connect.php?lng='.$lng.'&pseudo='.$upseudo.'&uid='.$uuid.'&tconfig='.$tconf).'">'.stripslashes($site[($lang[0] == $lng ? 0 : 11)]).'</a><br />';
        $bodycorps .= $admin[1760].'<br />'.$user[0].'<br />';
        $bodycorps .= '</p></div>';
        eMailHtmlTo($sujet, $bodycorps, $to);			
        $sujet = stripslashes($site[($lang[0] == $lng ? 0 : 11)]).' - '.$admin[1631];
        $bodycorps  = $admin[1629].' '.$user[0].'<br /><br />';
        $bodycorps .= $admin[1631].' : <b>'.$upseudo.'</b> '.stripslashes($site[($lang[0] == $lng ? 0 : 11)]).'<br /><br />';
        $bodycorps .= $admin[518].' '.getIPadress($site[6] == 'on' ? (isset($_SERVER['HTTP_X_FORWARDED_FOR'])? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']): $_SERVER['REMOTE_ADDR']).'<br /><br />';
        $to    = $user[1];
        eMailHtmlTo($sujet, $bodycorps, $to);
    }
    echo '<script>window.location="'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg=members&act=2&msg='.$msg.'";</script>';
}

include_once CHEMIN.'inc/func_groups.php';

function InputSelectGroups($name, $value) {
    global $admin, $dbgroups;
    $out = '
          <select name="'.$name.'">
            <option value=""> -- '.$admin[1604].' -- </option>
			<option value="all_members"'.Selected('all_members' == $value).'>'.$admin[690].'</option>';
    foreach ($dbgroups as $dbgroup) {
        $out .= '
            <option value="'.$dbgroup[0].'"'.Selected($dbgroup[0] == $value).'>'.$dbgroup[0].'</option>';
    }
    $out .= '
          </select>';
    return $out;
}

################################################################################
// gestion membres
if ($act == 2) {
	$deladmins = array();
	
	function DeleteRedac($redacname) {
		global $deladmins;
		$dbredac    = array();
		$file_redac = opendir(REDACREP);
		while ($nomfichier = readdir($file_redac)) {
			if( substr($nomfichier, -3) == 'inc' ) {
				$dbredac[] = str_replace(substr($nomfichier, -4), '', $nomfichier);
			}
		}
		closedir($file_redac);
		foreach ($dbredac as $redac) {
			if ($redac == $redacname) {
				DestroyDBFile(REDACREP.$redacname.'.inc');
				$deladmins[] = $redacname;
				break;
			}
		}
	}
	
    if (!empty($del)) {
        DeleteMemberProtect($del);
        DestroyMemberCol($del);
        DestroyMember($del);
        DestroyDBFile(MSGREP.$del.DBEXT);
        DestroyDBFile(COUNTMSGREP.$del.DBEXT);
		DeleteRedac($del);
    }
    if (!empty($acc)) {
		$userdb        = ReadDBFields(MSGREP.$acc.DBEXT);
		$uuid          = $userdb[0][0];
		$okmail        = $uuid != $userdb[1][8];
		$userdb[0][0]  = $uuid == $userdb[1][8] ? $userdb[1][8] : md5($uuid);
		$userdb[1][31] = 'ok';
		WriteDBFields(MSGREP.$acc.DBEXT, $userdb);
		if ($okmail) {
			$to    = $userdb[1][3];
			$sujet = stripslashes($site[($lang[0] == $lng ? 0 : 11)]).' - '.$admin[1696];
			$bodycorps  = $admin[1632].sprintf($admin[1697], $acc, stripslashes($site[($lang[0] == $lng ? 0 : 11)])).'<br /><br />';
			$bodycorps .= $admin[1013].' '.$acc.'<br />'.$admin[1634].' : ???<br />'.$admin[1698];
			$bodycorps .= '<a href="'.$site[3].'index.php?lng='.$lng.'">'.stripslashes($site[($lang[0] == $lng ? 0 : 11)]).'</a><br />';
			$bodycorps .= $admin[1760].'<br />'.$user[0].'<br />';
			eMailHtmlTo($sujet,$bodycorps,$to);
		}
    }
	if (!empty($susp) && $susp != $userprefs[1]) {
		$userdb = ReadDBFields(MSGREP.$susp.DBEXT);
		$userdb[1][13] = ($userdb[1][13] == 'on' ? 'off' : 'on');
		WriteDBFields(MSGREP.$susp.DBEXT, $userdb);
        $to = $userdb[1][3];
		if ($userdb[1][13] == 'on') {
            $sujet = stripslashes($site[($lang[0] == $lng ? 0 : 11)]).' - '.$admin[696];
            $bodycorps  = $admin[1629].sprintf($admin[852], $susp, stripslashes($site[($lang[0] == $lng ? 0 : 11)])).'<br /><br />';
            $bodycorps .= '<a href="'.$site[3].'index.php?lng='.$lng.'">'.stripslashes($site[($lang[0] == $lng ? 0 : 11)]).'</a><br />';
            eMailHtmlTo($sujet, $bodycorps, $to);
		}
		else {
            $sujet = stripslashes($site[($lang[0] == $lng ? 0 : 11)]).' - '.$admin[696];
            $bodycorps  = $admin[1629].sprintf($admin[1136], $susp, stripslashes($site[($lang[0] == $lng ? 0 : 11)])).'<br /><br />';
            $bodycorps .= $admin[1699].'<br />'.$user[($lang[0] == $lng ? 0 : 11)].'<br />';
            eMailHtmlTo($sujet, $bodycorps, $to);
		}
        unset($susp);
        echo '<script>window.location="'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg=members&act=2#tblmemb";</script>';
	}
	if ($suppr == 'ok') {
        if (!empty($depuis)) {
            $datesup    = date('Ymd', time() - ($depuis * 24 * 3600));
            $file_users = opendir(MSGREP);
            set_time_limit(0);
            while ($nomfichier = readdir($file_users)) {
                if (substr($nomfichier, -3) == 'dtb') {
                    $pseudo = str_replace(substr($nomfichier, -4), '', $nomfichier);
                    if ($pseudo != $userprefs[1]) {
                        $userdb = ReadDBFields(MSGREP.$nomfichier);
                        $to     = $userdb[1][3];
                        $ok1    = substr($userdb[1][15], 0, 8) > substr($userdb[1][14], 0, 8);
                        $del    = $ok1 ? substr($userdb[1][15], 0, 8) < $datesup : substr($userdb[1][14], 0, 8) < $datesup;
                        if ($del) {
                            DestroyDBFile(MSGREP.$nomfichier);
                            DeleteRedac($pseudo);
                        }
                    }
                }
            }
            closedir($file_users);
        }
	}
	if (!empty($deladmins)) {
		$deladmins = array_unique($deladmins);
		$admins    = ReadDBFields(ADMINS);
		$dbadmin   = array();
		foreach ($admins as $adm) {
			foreach ($deladmins as $del) {
				if ($adm[0] != $del) $dbadmin[] = $adm;
			}
		}
		WriteDBFields(ADMINS, $dbadmin);
	}
	unset($deladmins);
    $dbusers = array();
    $file_users = opendir(MSGREP);
    while ($nomfichier = readdir($file_users)) {
        if (substr($nomfichier, -3) == 'dtb') {
			$pseudo = str_replace(substr($nomfichier, -4), '', $nomfichier);
			if ($pseudo == $userprefs[1]) {
				$dbusers[] = array('pseudo' => $pseudo, 'ok' => 'on', 'reg' => $userprefs[13], 'date' => $userprefs[14], 
                                   'mail' => $userprefs[2], 'status' => $userprefs[30], 'web' => $userprefs[9], 'rgpd' => $userprefs[27]);
			} else {
				$userdb = ReadDBFields(MSGREP.$nomfichier);
				if (isset($userdb[1][13])) {
					$userdb[1][31] = empty($userdb[1][31]) ? '0' : $userdb[1][31];
					$dbusers[] = array('pseudo' => $pseudo, 'ok' => $userdb[1][13], 'reg' => $userdb[1][14], 'date' => $userdb[1][15], 
                                       'mail' => $userdb[1][3], 'status' => $userdb[1][31], 'web' => $userdb[1][10], 'rgpd' => $userdb[1][28]);
				}
			}
        }
    }
    closedir($file_users);
	sort($dbusers);
	$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.$admin[696];
	include CHEMIN.'admin/hpage.inc';
	if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
	if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
    htable($admin[696], '100%');
    echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=978&mnuid=937&tconfig=0#z2').$admin[701].$admin[700].'<br /></p>
<hr /><br />
<div style="z-index:999;background:#EFEFEF;">
  <fieldset id="newmembers" style="display:none;width:100%;height:1480px;margin:0 auto 36px;">
    <p style="padding:0 36px;text-align:center;">'.$help[27].'</p>
    <i class="fas fa-times text-danger float-right c-pointer" style="font-size:1.4rem;margin:-70px 6px 0 0;" 
      title="'.$admin[458].'" onmouseup="cache(\'newmembers\');"></i>
    <form name="membconnect" action="admin.php?lng='.$lng.'&amp;pg=members" method="post">
      <input type="hidden" name="regit" value="4" />
      <div class="table-responsive-md">
      <table class="table table-bordered w-100" style="max-height:700px;">
        <tr>
          <td style="border:1px solid #000;text-align:center;width:13%;vertical-align:middle;">'.$admin[1013].'</td>
          <td style="border:1px solid #000;text-align:center;width:12%;vertical-align:middle;">'.$admin[1634].'</td>
          <td style="border:1px solid #000;text-align:center;width:19%;vertical-align:middle;">'.$admin[1646].'</td>
          <td style="border:1px solid #000;text-align:center;width:8%;vertical-align:middle;">'.sprintf($admin[1637], '').'</td>
          <td style="border:1px solid #000;text-align:center;width:8%;vertical-align:middle;">'.sprintf($admin[1638], '').'</td>
          <td style="border:1px solid #000;text-align:center;width:8%;vertical-align:middle;">'.sprintf($admin[1639], '').'</td>
          <td style="border:1px solid #000;text-align:center;width:8%;vertical-align:middle;">'.sprintf($admin[1640], '').'</td>
          <td style="border:1px solid #000;text-align:center;width:8%;vertical-align:middle;">'.$admin[1641].'</td>
          <td style="border:1px solid #000;text-align:center;width:8%;vertical-align:middle;">'.$admin[1642].'</td>
          <td style="border:1px solid #000;text-align:center;width:8%;vertical-align:middle;">'.$admin[1643].'</td>
        </tr>';
    for ($i=0;$i<20;$i++) {
        echo '
        <tr>
          <td style="width:13%;vertical-align:middle;"><input type="text" class="form-control texte" name="upseudo'.$i.'" style="width:130px;" /></td>
          <td style="width:12%;vertical-align:middle;"><input type="text" class="form-control texte" name="uuid'.$i.'" style="width:120px;" /></td>
          <td style="width:19%;vertical-align:middle;"><input type="text" class="form-control texte" name="uemail'.$i.'" style="width:190px;" /></td>
          <td style="width:8%;text-align:center;vertical-align:middle;"><input type="checkbox" name="uusemp'.$i.'" /></td>
          <td style="width:8%;text-align:center;vertical-align:middle;"><input type="checkbox" name="uctmail'.$i.'" /></td>
          <td style="width:8%;text-align:center;vertical-align:middle;"><input type="checkbox" name="uprfmemb'.$i.'" /></td>
          <td style="width:8%;text-align:center;vertical-align:middle;"><input type="checkbox" name="ulstmemb'.$i.'" /></td>
          <td style="width:8%;text-align:center;vertical-align:middle;"><input type="checkbox" name="uavaext'.$i.'" /></td>
          <td style="width:8%;text-align:center;vertical-align:middle;"><input type="checkbox" name="uimgext'.$i.'" /></td>
          <td style="width:8%;text-align:center;vertical-align:middle;"><input type="checkbox" name="unobox'.$i.'" /></td>
        </tr>';
    }
    echo '
      </table>
      </div>
      <p style="text-align:center;">'.SubmitButton($admin[38]).'</p>
    </form>';
    echo '
  </fieldset>
</div>';

    $msg = import('msg');
    if (!empty($msg)) {
        $msg = str_replace('-', '<br />- ', $msg);
        echo '
<div class="modal fade" id="myModal" role="dialog">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title">'.$admin[20].'</h2>
        <button class="close" data-dismiss="modal" type="button">×</button>
      </div>
      <div class="modal-body">
        <p>'.$msg.'</p>
      </div>
    </div>
  </div>
</div>
        ';
        unset($msg);
    }

    echo '
<div class="w-100 my-0 mx-auto p-0" style="max-width:968px;">
  <div style="border:0;margin:-20px 0px 10px -10px;">
    <fieldset class="w-98 float-left" style="max-width:424px;height:auto;">
      <form name="membconnect" action="admin.php?lng='.$lng.'&amp;pg=members" method="post">
        <input type="hidden" name="regit" value="2" />
        <input type="hidden" name="act" value="2" />
        <div class="table-responsive-md">
        <table class="table table-bordered m-auto text-center" style="max-width:400px;">
          <tr>
	        <td colspan="2" style="text-align:center;">
              <label for="menuico11"><strong>'.$admin[703].'</strong></label>
              <input id="menuico11" name="menuico11" type="checkbox" '.Checked($menuico[11]).' />
	        </td>
          </tr>
          <tr class="rep">
            <td class="w-35" style="vertical-align:middle; text-align:center;">'.$admin[704].'</td>
            <td class="w-65">
              <div>
                '.($lang[1] != '' ? '<div class="d-inline-block">'.$flag1.'</div>' : '').'
	            <div class="d-inline-block">
                  <input class="form-control texte" type="text" name="nom14" value="'.$nom[14].'" size="20" />
                </div>
              </div>'.($lang[1] != '' ? '
              <div>
                <div class="d-inline-block">'.$flag2.'</div>
	            <div class="d-inline-block">
    	          <input class="form-control texte" type="text" name="nom15" value="'.$nom[15].'" size="20" />
                </div>
              </div>' : '').'
	        </td>
          </tr>
          <tr class="rep">
            <td style="vertical-align:middle;text-align:center;">'.$admin[705].'</td>
            <td>
              <div>
                '.($lang[1] != '' ? '<div class="d-inline-block">'.$flag1.'</div>' : '').'
	            <div class="d-inline-block">
                  <input class="form-control texte" type="text" name="nom28" value="'.$nom[28].'" size="20" />
                </div>
              </div>'.($lang[1] != '' ? '
              <div>
                <div class="d-inline-block">'.$flag2.'</div>
                <div class="d-inline-block">
	              <input class="form-control texte" type="text" name="nom29" value="'.$nom[29].'" size="20" />
                </div>
              </div>' : '').'
	        </td>
          </tr>
          <tr><td colspan="2" style="text-align:center;">'.SubmitButton($admin[38]).'</td></tr>
        </table>
        </div>
      </form>
    </fieldset>
    <fieldset class="w-98 mr-0 float-right" style="max-width:514px;">
      <form name="membnew" action="admin.php?lng='.$lng.'&amp;pg=members" method="post">
        <input type="hidden" name="regit" value="3" />
        <input type="hidden" name="act" value="2" />
        <div style="text-align:center;">
          <div class="d-inline-block"><strong>'.$admin[1283].' :&nbsp;</strong></div>
          <div class="d-inline-block">
            <select class="form-control" name="members19" onchange="javascript:membnew.submit();">
              <option value = ""'.Selected($members[19] == "").'>'.$admin[1300].'</option>
              <option value = "0"'.Selected($members[19] == "0").'>'.$admin[1301].'</option>
              <option value = "1"'.Selected($members[19] == "1").'>'.$admin[1302].'</option>
              <option value = "2"'.Selected($members[19] == "2").'>'.$admin[1303].'</option>
              <option value = "3"'.Selected($members[19] == "3").'>'.$admin[1277].'</option>
            </select>
          </div>
        </div>
      </form>
      <form name="newmember" action="admin.php?lng='.$lng.'&amp;pg=compte&amp;uuser=new" method="post">
        <p style="text-align:center;">'.SubmitButton($admin[1278]).'</p>
      </form>';
    if ($userprefs[1] == $serviz[31] || $userprefs[1] == $serviz[176]) echo '
      <p style="text-align:center;">'.SubmitButton($admin[1763], 'montre(\'newmembers\');').'</p>';
    echo '
    </fieldset>
  </div>';
	$nbdb = count($dbusers);
	echo '
  <a id="lstmemb"></a>
  <fieldset class="w-100 m-0 mt-2 mr-2 p-0">'.legend(sprintf($admin[1428], $nbdb)).'
	<fieldset style="border:0;margin:0 0 -10px;">
      <div class="table-responsive-md">
      <table class="table table-bordered w-100">
        <tr>
          <td class="w-50">
            <fieldset>'.legend($admin[1429]).'
              <form name="searchname" method="post" action="admin.php?lng='.$lng.'&amp;pg=members#lstmemb">
                <input type="hidden" name="search" value="ok" />
                <input type="hidden" name="act" value="2" />
                <div class="w-100 m-auto text-center pr-3">
                  '.$admin[517].' / '.$admin[199].'
                  <input type="text" class="form-control texte" name="usearch" value="'.$usearch.'" size="36" />
                </div>
                <div style="margin:auto;text-align:center;">
                  '.SubmitButton($admin[1700]).'
                </div>
              </form>';
    if (!empty($usearch)) {
        $nm = $nokey - 1;
        $np = $nokey + 1;
        echo '
              <div style="text-align:center;margin:auto;margin-top:-3px;">
                <a href="admin.php?lng='.$lng.'&amp;pg=members&amp;search=ok&amp;act=2&amp;usearch='.$usearch.'&amp;nokey='.($nm).'#lstmemb" title="">
                  <b>'.$admin[1701].'</b>
                </a>&nbsp;&nbsp;
                <a href="admin.php?lng='.$lng.'&amp;pg=members&amp;search=ok&amp;act=2&amp;usearch='.$usearch.'&amp;nokey='.($np).'#lstmemb" title="">
                  <b>'.$admin[1702].'</b>
                </a>
              </div>';
    }
    echo '
            </fieldset>
          </td>
          <td class="w-50">
            <fieldset>'.legend($admin[1463]).'
              <form name="membersup" method="post" action="admin.php?lng='.$lng.'&amp;pg=members#lstmemb">
                <input type="hidden" name="suppr" value="ok" />
                <input type="hidden" name="act" value="2" />
                <div style="margin:auto;text-align:center;">
                  '.$admin[1376].' : <input type="text" class="form-control w-100 mx-auto texte" name="depuis" style="max-width:64px;" />
                  '.SubmitButton(ucfirst($admin[31])).'
                </div>
              </form>
            </fieldset>
          </td>
        </tr>
      </table>
      </div>
	</fieldset>';
    if (empty($idpg)) {
        $idpg = 1;
    }
    else {
        while ($nbdb <= ($idpg - 1)* $serviz[75]) $idpg--;
    }
	if (empty($tri)) $tri = 'P';
	$tmpi   = 0;
	$tmp[0] = array();
	$tmp[1] = array();
	$tmp[2] = array();
	$tmp[3] = array();
	$tmp[4] = array();
	$tmp[5] = array();
	$tmp[6] = array();
	$tmp[7] = array();
	$tmp[8] = array();
	for ($i = 0; $i < $nbdb; $i++) {
		$tmp[0][$tmpi] = strtolower($dbusers[$i]['pseudo']);
		$tmp[1][$tmpi] = $dbusers[$i]['ok'];
		$tmp[2][$tmpi] = $dbusers[$i]['reg'];
		$tmp[3][$tmpi] = $dbusers[$i]['date'];
		$tmp[4][$tmpi] = $dbusers[$i]['mail'];
		$tmp[5][$tmpi] = $dbusers[$i]['status'];
		$tmp[6][$tmpi] = $dbusers[$i]['pseudo'];
		$tmp[7][$tmpi] = $dbusers[$i]['web'];
		$tmp[8][$tmpi] = $dbusers[$i]['rgpd'];
		$tmpi++;
	}
	if ($nbdb > 0 ) {
        switch ($tri) {
        case 'P' :
			$cr = array_multisort($tmp[0], SORT_ASC, $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8]);
            break;
        case '-P' :
			$cr = array_multisort($tmp[0], SORT_DESC, $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8]);
            break;
        case 'R' :
			$cr = array_multisort($tmp[2], SORT_ASC, $tmp[0], $tmp[1], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8]);
            break;
        case '-R' :
			$cr = array_multisort($tmp[2], SORT_DESC, $tmp[0], $tmp[1], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8]);
            break;
        case 'C' :
			$cr = array_multisort($tmp[3], SORT_ASC, $tmp[0], $tmp[1], $tmp[2], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8]);
            break;
        case '-C' :
			$cr = array_multisort($tmp[3], SORT_DESC, $tmp[0], $tmp[1], $tmp[2], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8]);
            break;
        case 'E' :
			$cr = array_multisort($tmp[5], SORT_ASC, $tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[6], $tmp[7], $tmp[8]);
            break;
        case '-E' :
			$cr = array_multisort($tmp[5], SORT_DESC, $tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[6], $tmp[7], $tmp[8]);
            break;
		}
        $spseudo = '';
		if ($search == 'ok' && !empty($usearch)) {
            $keys = array();
            $pseudos = array();
			for ($ii = 0; $ii < $nbdb; $ii++)
				if (strpos($tmp[0][$ii], strtolower($usearch)) !== false || strpos($tmp[4][$ii], trim($usearch)) !== false) {
					$keys[]    = $ii;
					$pseudos[] = $tmp[0][$ii];
				}
            $nokey   = min($nokey, count($keys));
            $nokey   = $nokey == count($keys) ? 0 : $nokey;
			if (isset($keys[$nokey])) $key     = $keys[$nokey];
			else  $key     = 0;
			if (isset($pseudos[$nokey])) $spseudo = $pseudos[$nokey];
			else
				$spseudo = '';
			$idpg    = floor($key / $serviz[75]) + 1;
		}
		$pseudoup    = $tri == 'P' ? '<i class="fas fa-angle-up text-danger" style="font-size: 1.4rem;"></i>' : '<i class="fas fa-angle-down" style="font-size: 1.4rem;"></i>';
		$pseudodown  = $tri == '-P' ? '<i class="fas fa-angle-down text-danger"></i>' : '<i class="fas fa-angle-down"></i>';
		$regup       = $tri == 'R' ? '<i class="fas fa-angle-up text-danger"></i>' : '<i class="fas fa-angle-down"></i>';
		$regdown     = $tri == '-R' ? '<i class="fas fa-angle-down text-danger"></i>' : '<i class="fas fa-angle-down"></i>';
		$connectup   = $tri == 'C' ? '<i class="fas fa-angle-up text-danger"></i>' : '<i class="fas fa-angle-down"></i>';
		$connectdown = $tri == '-C' ? '<i class="fas fa-angle-down text-danger"></i>' : '<i class="fas fa-angle-down"></i>';
		$stateup     = $tri == 'R' ? '<i class="fas fa-angle-up text-danger"></i>' : '<i class="fas fa-angle-down"></i>';
		$statedown   = $tri == '-R' ? '<i class="fas fa-angle-down text-danger"></i>' : '<i class="fas fa-angle-down"></i>';
		$navbar = GetNavBar('admin.php?lng='.$lng.'&amp;pg=members&amp;act=2&amp;tri='.$tri.'&amp;idpg=', $nbdb, $idpg, $serviz[75]);
		echo $navbar.'
    <br />
    <a id="tblmemb"></a>
    <div class="table-responsive-md">
    <table class="table table-bordered w-100 m-auto">
      <tr style="background-color:#bababa;height:64px;">
        <td style="width:25%;text-align:center;vertical-align:middle;">
		  <div>
            <a href="admin.php?lng='.$lng.'&amp;pg=members&amp;act=2&amp;tri='.($tri == 'P' ? '-P' : 'P').'&amp;idpg='.$idpg.'#lstmemb" title="'.$admin[517].'">
		      <b>'.$admin[517].'</b>'.($tri == 'P' ? $pseudoup : $pseudodown).'
            </a><b> / '.$admin[1014].'</b>
		  </div>
		</td>
        <td style="width:17%;text-align:center;vertical-align:middle;"><div><b>'.ucfirst($admin[928]).'</b></div></td>
        <td style="width:13%;text-align:center;vertical-align:middle;">
		  <a href="admin.php?lng='.$lng.'&amp;pg=members&amp;act=2&amp;tri='.($tri == 'R' ? '-R' : 'R').'&amp;idpg='.$idpg.'#lstmemb" title="'.$admin[1462].'">
		    <div><b>'.$admin[1462].'</b></div><div>'.($tri == 'R' ? $regup : $regdown).'</div>
		  </a>
		</td>
        <td style="width:14%;text-align:center;vertical-align:middle;">
		  <a href="admin.php?lng='.$lng.'&amp;pg=members&amp;act=2&amp;tri='.($tri == 'C' ? '-C' : 'C').'&amp;idpg='.$idpg.'#lstmemb" title="'.$admin[1664].'">
		    <div><b>'.$admin[1664].'</b></div><div>'.($tri == 'C' ? $connectup : $connectdown).'</div>
		  </a>
		</td>
        <td style="width:11%;text-align:center;vertical-align:middle;"><div><b>'.$admin[1757].'</b></div></td>
        <td style="width:8%;text-align:center;vertical-align:middle;"><div><b>'.$admin[186].'</b></div></td>
        <td style="width:6%;text-align:center;vertical-align:middle;">
		  <a href="admin.php?lng='.$lng.'&amp;pg=members&amp;act=2&amp;tri='.($tri == 'E' ? '-E' : 'E').'&amp;idpg='.$idpg.'#lstmemb" title="'.$admin[851].'">
		    <div><b>'.$admin[851].'</b></div><div>'.($tri == 'E' ? $stateup : $statedown).'</div>
		  </a>
		</td>
        <td style="width:6%;text-align:center;vertical-align:middle;"><div><b>'.$admin[1019].'</b></div></td>
      </tr>';	
		$grps = ReadGroups();
		for ($i = $serviz[75]*($idpg-1); $i < Min($serviz[75]*$idpg, $tmpi); $i++) {
			$alt = $tmp[5][$i] == '1' || $tmp[5][$i] == '2' ? '?' : ($tmp[1][$i] == 'on' ? $admin[1021] : $admin[1020]);
			$img = $tmp[5][$i] == '1' || $tmp[5][$i] == '2' 
              ? '<i class="fas fa-question text-warning" style="font-size: 1.4rem;"></i>' 
              : ($tmp[1][$i] == 'off' 
                ? '<i class="fas fa-toggle-off text-danger" style="font-size: 1.4rem;"></i>' 
                : '<i class="fas fa-toggle-on text-primary" style="font-size: 1.4rem;"></i>');
			$userdb = ReadDBFields(GROUPS);
			echo '
      <tr '.($i % 2 == 0 ? 'class="rep"' : 'class="quest"').' style="text-align:center;margin:auto;">
        <td style="vertical-align:middle;text-align:left;padding:4px 0;'.($spseudo == $tmp[0][$i] ? 'background-color:#00FF00;' : '').'">'.$tmp[6][$i].
          ($tmp[4][$i] == '' ? '' : '
          <ul class="keyword keyword-horizontal" style="float:right;height:auto;">
            <li class="dir" style="margin:0 10px 0 5px;"><strong>=></strong>
              <ul>
                <li style="max-width:700px;">
                  <a href="mailto:'.$tmp[4][$i].'">'.$tmp[4][$i].'</a>
                  '.($tmp[7][$i] == '' ? '' : ' <a href="'.$tmp[7][$i].'" title="'.$tmp[7][$i].'" target="_blank">
                    <i class="fas fa-globe"></i>
                  </a>').'
                </li>
              </ul>
            </li>
          </ul>').'
        </td>';
			echo '
        <td style="vertical-align:middle;text-align:left;">';
			$dbgroup = ReadDBFields(GROUPS);
			for ($x = 0; $x < count($dbgroup); $x++) {
				for ($m = 0; $m < count($dbgroup[$x]); $m++) {
					if ($dbgroup[$x][$m] == $tmp[6][$i])
						echo $dbgroup[$x][0].'<br />';
				}
			}
			echo '
        </td>
        <td class="align-middle">'.FormatDate(substr($tmp[2][$i], 0, 8)).'</td>
        <td class="align-middle">'.FormatDate(substr($tmp[3][$i], 0, 8)).'</td>
        <td class="align-middle">'.FormatDate(substr($tmp[8][$i], 0, 8)).'</td>';
            if ($wri == $userprefs[1] && ($tmp[6][$i] == $serviz[31] || $tmp[6][$i] == $serviz[176]))
                echo '
        <td colspan="2"></td>';
            else {
                echo '
        <td class="align-middle">';
                if ($tmp[6][$i] != $userprefs[1]) 
                    echo '
          <a href="admin.php?lng='.$lng.'&amp;pg=members&amp;act=2&amp;idpg='.$idpg.'&amp;del='.$tmp[6][$i].'#lstmemb">
	        <i class="fas fa-times text-danger" style="font-size: 1.4rem;" title="'.$admin[186].'"></i>
		  </a>';
                echo '
        </td>
        <td class="align-middle">';
                if ($tmp[6][$i] != $userprefs[1]) {
                    echo '
          <script>
            $(document).ready(function(){
              $(\'.popuserpwd\').popover({
                title:"'.$admin[851].'",
                content:"'.$help[7].'",
                trigger:"hover",
                placement:"auto",
                html:true
              });
            });
          </script>';
                    if ($tmp[5][$i] != '1' && $tmp[5][$i] != '2') {
                        echo '
		  <div class="popuserpwd" style="padding-left:30%;">
	        <a href="admin.php?lng='.$lng.'&amp;pg=members&amp;act=2&amp;idpg='.$idpg.'&amp;susp='.$tmp[6][$i].'#lstmemb">
		      '.$img.'
			</a>
		  </div>';
                    } 
                    else {
                        $urlsup = 'admin.php?lng='.$lng.'&amp;pg=members&amp;act=2&amp;idpg='.$idpg.'&amp;del='.$tmp[6][$i].'#lstmemb';
                        $urlacc = 'admin.php?lng='.$lng.'&amp;pg=members&amp;act=2&amp;idpg='.$idpg.'&amp;acc='.$tmp[6][$i].'#lstmemb';
                        echo '
		  <div class="popuserpwd" style="padding-left:42%;">
	        <a onmouseup="montre(\'moveto\');">
		      '.$img.'
			</a>
		  </div>
		  <div id="moveto" class="rep" style="position:absolute;right:112px;display:none;width:auto;height:auto;text-align:center;border:1px solid;border-radius:8px;">
		    <div style="padding-bottom:6px;"><b>'.$admin[$tmp[5][$i] == '1' ? 1302 : 1303].'</b></div>
			<div>'.SubmitButton($admin[186], 'self.location.href=\''.$urlsup.'\'').'  '.SubmitButton($admin[1471], 'self.location.href=\''.$urlacc.'\'').'</div>
			<div style="float:right;margin:2px 4px 0 0;">
              <i class="fas fa-caret-up" title="'.$admin[458].'" onmouseup="cache(\'moveto\')"></i>
            </div>
		    <div style="clear:both;"></div>
		  </div>';
                    }
                }
                echo '
        </td>';
            }
            echo '
        <td style="vertical-align:middle;text-align:center;">
          <a href="admin.php?lng='.$lng.'&amp;pg=compte&amp;edit='.$tmp[6][$i].'">
		    <i class="far fa-edit" title="'.$admin[1019].'"></i>
		  </a>
        </td>
      </tr>';
        }
        echo '
    </table>
    </div>
    <br />'.$navbar;
	}
	echo '
  </fieldset>
</div>';
}
################################################################################
// écrire aux membres
elseif ($act == 3) {
	$topmess = '
	<a href="admin.php?lng='.$lng.'">Admin</a> > '.$admin[697];
    $dbusers = array();
    $file_users = opendir(MSGREP);
    while ($nomfichier = readdir($file_users)) {
        if (substr($nomfichier, -3) == 'dtb') {
			$pseudo = str_replace(substr($nomfichier, -4), '', $nomfichier);
			if ($pseudo != $userprefs[1]) $dbusers[] = $pseudo;
        }
    }
    closedir($file_users);
    @usort($dbusers, 'AsciiCompare');
    $dbgroup = ReadDBFields(GROUPS);

	include CHEMIN.'admin/hpage.inc';
	if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
	if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
    htable($admin[697], "100%");
	if (!empty($erreur))
		echo displayErrorMsg($admin[1636], $erreur);
echo '
<script>
function disablegroup()
{
	document.getElementById("implistuser").disabled="";
	document.getElementById("implistgroup").disabled="true";

}
function disablembr()
{
	document.getElementById("implistgroup").disabled="";
	document.getElementById("implistuser").disabled="true";
}
</script>';
    echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=980&mnuid=937&tconfig=0#z2').$admin[698].'<br />'.$admin[699].'</p>
<hr />
'.JavascriptFile(CHEMIN.'admin/inc/tinymsg.js').'
<div class="w-100 m-auto" style="max-width:540px;">
  <form name="membmsg" action="admin.php?lng='.$lng.'&amp;pg=members" method="post" onsubmit="return VerifyForm(); return false;">
    <fieldset>
      <input type="hidden" name="regit" value="5" />
	  <input type="hidden" name="act" value="9" />
      <div class="table-responsive-md">
      <table class="table table-bordered text-center m-auto">';
	$selectdisable = 'disabled';
	$selectchoice = 'Checked';
	
	if (count($dbgroup) > 0) $selectdisable = '';
	if ($selectdisable == '')
	{
		echo '
		<tr>
			<td style="text-align:right;vertical-align:top;padding-top:6px;"><strong>'.$admin[1999].': </strong></td>
			<td style="text-align:left;">		
				<div class="d-inline-block">
					<label align="left"><input type="radio" id="selmbrgrp" value="0" name="selmbrgrp" '.$selectchoice.' onclick="disablegroup()" /> '.$admin[1997].'</label><br/>
				</div>';
		echo '
				<div class="d-inline-block">
				<label>
					<input type="radio" id="selmbrgrp" value="1" name="selmbrgrp" onclick="disablembr()" /> '.$admin[1998].'</label><br/>
				</div>';
	echo '
			</td/
		</tr>';
	}
	echo '
	    <tr>
		  <td style="text-align:right;vertical-align:top;padding-top:6px;"><strong>'.$admin[697].': </strong></td>
		  <td style="text-align:left;">
			<select multiple="multiple" size="'.(count($dbusers) > 8 ? 9 : count($dbusers) + 1).'" id="implistuser" name="implistuser[]" class="form-control" style="width:140px;">
			 <option value="ALL" >'.$admin[690].'</option>'; 
    foreach($dbusers as $user) {
        echo '
			  <option value="'.$user.'">'.$user.'</option>';
    }

    echo '
			</select>
		  </td>
		</tr>';
	if ($selectchoice == 'Checked')
		$selectdisable = 'disabled';
	{
		echo '
		<tr>
		  <td style="text-align:right;"><strong>'.$admin[1123].': </strong></td>
		  <td style="text-align:left;">
			<select multiple="multiple" size="'.(count($dbgroup) > 8 ? 9 : count($dbgroup) + 1).'"name="implistgroup[]" id="implistgroup" '.$selectdisable.' class="form-control" style="width:100px;" >
			  <option value=""> -- '.$admin[1604].' -- </option>';
		for ($x = 0; $x < count($dbgroup); $x++) {
			echo '
				  <option value="'.$dbgroup[$x][0].'">'.$dbgroup[$x][0].'</option>';
		}
		echo '
			</select>';
	}
	echo '
		  </td>
		</tr>
	  </table>
      </div>';
	  $msg1 = '';
    echo '
      <div class="table-responsive-md">
	  <table class="table table-bordered w-100">
	    <tr>
		  <td style="text-align:center;">'.$flag1.'</td>
	    </tr>
	    <tr>';
	echo '
		  <td style="background-color:#FFFFFF;text-align:center;">
            <textarea class="w-98 mx-auto form-control '.($err_msg1 ? 'errorInputText' : '').'" style="width: 50%;" rows="10" cols="10" name="msg1" ></textarea>
          </td>
	    </tr>
	    <tr>
		  <td  style="text-align:center;vertical-align:middle">
            <a href="javascript:format1(\'b\')" class="c-pointer" title="'.$admin[1703].'"><i class="fas fa-bold"></i></a>&nbsp;
            <a href="javascript:format1(\'i\')" class="c-pointer" title="'.$admin[1705].'"><i class="fas fa-italic"></i></a>&nbsp;
            <a href="javascript:format1(\'u\')" class="c-pointer" title="'.$admin[1704].'"><i class="fas fa-underline"></i></a>&nbsp;
            <a href="javascript:dolink1(\'href=http://\')" class="c-pointer" title="'.$admin[1706].'"><i class="fas fa-link"></i></a>&nbsp;&nbsp;';
      if ($page[12] != 'none') DrawSmileys("1");
      echo '
		  </td>
	    </tr>';
    if ($lang[1] != "") {
		$msg2 = '';
        echo '
	    <tr>
		  <td style="text-align:center;"><br />'.$flag2.'</td>
	    </tr>
	    <tr>
		  <td style="background-color:#FFFFFF;text-align:center;">
            <textarea class="w-98 mx-auto form-control '.($err_msg2 ? 'errorInputText' : '').'" style="width: 50%;" rows="10" cols="10" name="msg2"></textarea>
          </td>
	    </tr>
	    <tr>
		  <td  style="text-align:center;vertical-align:middle">
            <a href="javascript:format2(\'b\')" class="c-pointer" title="'.$admin[1703].'"><i class="fas fa-bold"></i></a>&nbsp;
            <a href="javascript:format2(\'i\')" class="c-pointer" title="'.$admin[1705].'"><i class="fas fa-italic"></i></a>&nbsp;
            <a href="javascript:format2(\'u\')" class="c-pointer" title="'.$admin[1704].'"><i class="fas fa-underline"></i></a>&nbsp;
            <a href="javascript:dolink2(\'href=http://\')" class="c-pointer" title="'.$admin[1706].'"><i class="fas fa-link"></i></a>&nbsp;&nbsp;';
        if ($page[12] != 'none') DrawSmileys("2");
        echo '
		  </td>
	    </tr>';
    }
    echo '
	  </table>
      </div>
	  <br />
	</fieldset>
    <div class="row quest p-0 mt-2 w-98 m-auto">
	  <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
      <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[694], '', '', $imgdroite).'</div></div>
    </div>
  </form>
</div>';
}
################################################################################
// envoyer un message
elseif ($act == 4) {
	$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.$admin[697];
	include CHEMIN.'admin/hpage.inc';
	if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
	if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
    htable($admin[697], '100%');
	$groupes = '';
	$users = '';
	if ($regit == 5) {
		$m1   = $msg1;
		$m2   = $msg2;
		$msg1 = str_replace(chr(10),'<br />', $msg1);
		$msg1 = str_replace(chr(13),'', $msg1);
		$msg1 = str_replace('[b]',  '<b>', $msg1);
		$msg1 = str_replace('[/b]', '</b>', $msg1);
		$msg1 = str_replace('[i]',  '<i>', $msg1);
		$msg1 = str_replace('[/i]', '</i>', $msg1);
		$msg1 = str_replace('[u]',  '<u>', $msg1);
		$msg1 = str_replace('[/u]', '</u>', $msg1);
		$msg1 = str_replace('[l]href=','<a href="', $msg1);
		$msg1 = str_replace('[/l]', '" target="_blank"><i class="fas fa-globe align-middle" title="'.$admin[1038].'"></i></a>', $msg1);
		$msg1 = str_replace('"',    "'", $msg1);

		$msg2 = str_replace(chr(10),'<br />', $msg2);
		$msg2 = str_replace(chr(13),'', $msg2);
		$msg2 = str_replace('[b]',  '<b>', $msg2);
		$msg2 = str_replace('[/b]', '</b>', $msg2);
		$msg2 = str_replace('[i]',  '<i>', $msg2);
		$msg2 = str_replace('[/i]', '</i>', $msg2);
		$msg2 = str_replace('[u]',  '<u>', $msg2);
		$msg2 = str_replace('[/u]', '</u>', $msg2);
		$msg2 = str_replace('[l]href=','<a href="', $msg2);
		$msg2 = str_replace('[/l]', '" target="_blank"><i class="fas fa-globe align-middle" title="'.$admin[1038].'"></i></a>', $msg2);
		$msg2 = str_replace('"',    "'", $msg2);
/*
   selmbrgrp si 0 choix membre, si 1 choix group
*/
		echo '
			<p style="text-align:center;">'.$admin[689].'<br /></p>
			<hr />';
		echo '
			<p style="text-align:center;">'.$admin[697].' :<br /></p>';
		$msgdest  = '';
		if ($selmbrgrp == 0)
		{
			foreach ($implistuser as $user)
			{
				$users .= $user.' ';	
			}
			$msgdest  = $users;
		}
		else
		{
			if (is_array($implistgroup))
		    {
				foreach ($implistgroup as $groupe)
				{
					if ($implistgroup != $admin[1604]) 
						$groupes .= $groupe.' ';	
				}
				$msgdest =(($groupes != '') ? $admin[1123].': <strong>'.$groupes.'</strong>' : '');
			}
		}
		echo '
  <form name="membmsg" action="admin.php?lng='.$lng.'&amp;pg=members" method="post">
    <fieldset>
      <input type="hidden" name="selmbrgrp" value="'.$selmbrgrp.'" />
	  <input type="hidden" name="tabimplistuser" value="'.$users.'" />
	  <input type="hidden" name="tabimplistgroup" value="'.$groupes.'" />
	  <input type="hidden" name="msg1" value="'.$msg1.'" />';
	if (!isset($msg))
			echo '<input type="hidden" name="msg2" value="'.$msg2.'" />';
	 echo '<input type="hidden" name="act" value="4" />
<div class="w-100 m-auto" style="max-width:540px;">
  <p class="forum" style="text-align:left; padding:5px 20px;">'.$msgdest.'</p>
  <fieldset>
  <p style="text-align:center;">'.$flag1.'</p>
  <div class="bord w-100 mx-auto my-0" style="max-width:500px;">
'.PathToImage(replaceimg($msg1));
		if ($lang[1] != "") {
			echo '
  </div>
  <br />
  <p style="text-align:center;">'.$flag2.'</p>
  <div class="bord w-100 mx-auto my-0" style="max-width:500px;">
'.PathToImage(replaceimg($msg2));
		}
		echo '
  </div>
  </fieldset>
  <div class="row quest p-0 mt-2 w-100 m-auto">
    <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
    <div class="col-md-6">
      <div style="float:right">
        '.SubmitButton($admin[694], 'self.location.href=\'admin.php?lng='.$lng.'&amp;pg=members&amp;act=4\'', '', $imgdroite).'
      </div>
    </div>
  </div>
</div>';
		echo '
<hr />
<p style="text-align:center;">
  <a href="admin.php?lng='.$lng.'&amp;pg=members&amp;act=3&amp;msg1='.$msg1.'&amp;msg2='.$msg2.'">'.$admin[688].'</a>
</p>
</form>';
	} else {
		$mbrs = array();
		if ($selmbrgrp == 1)
		{
			$tabimplistgroup = import('tabimplistgroup');
			$implistgroup = explode(' ', $tabimplistgroup);
			$grps = ReadGroups();
			foreach ($implistgroup as $groupe)
			{
				if ($groupe != '')
					$mbrs  = $grps[$groupe];
			}	
		}
		else
		{
			$tabimplistuser = import('tabimplistuser');
			$mbrs = explode(' ', $tabimplistuser);
		}
		$mbrs       = array_unique($mbrs);
		$dbusers    = array();
		$file_users = opendir(MSGREP);
		while ($nomfichier = readdir($file_users)) {
			if (substr($nomfichier, -3) == 'dtb' && $nomfichier != $userprefs[1].DBEXT) {
				$nomuser = str_replace(substr($nomfichier, -4), '', $nomfichier);
				if (in_array($nomuser, $mbrs)) $dbusers[] = $nomuser;
			}
		}
		closedir($file_users);
		@sort($dbusers);
		echo '
<fieldset>
  <br />
  <p style="text-align:center;">'.$admin[692].'</p>
  <p style="text-align:center;">'.count($dbusers).' '.$admin[516].'</p>';
		if (FileDBExist(MSGREP.$userprefs[1].DBEXT)) {
			$dbmsg = ReadDBFields(MSGREP.$userprefs[1].DBEXT);
		}
		$msgsend = $msg2 != '' ? $msg1.'<br /><hr /><br />'.$msg2 : $msg1;
		
		$dbmsg    = array();
		$dbmsg[0] = $admin[690];
		$dbmsg[1] = GetCurrentDateTime();
		$dbmsg[2] = RemoveConnector(stripslashes($msgsend));
		$dbmsg[3] = 'lu';
		$dbmsg[4] = 'send';
		$dbmsg[5] = '';
		$dbmsg[6] = '';
		$dbmsg[7] = $userprefs[8];
		AppendDBFields(MSGREP.$userprefs[1].DBEXT, $dbmsg);
		foreach ($dbusers as $uuser) {
			$userdb      = ReadDBFields(MSGREP.$uuser.DBEXT);
			$msgsend = $lang[0] == $userdb[1][1] ? $msg1 : $msg2;
			$dbmsg       = array(
				$userprefs[1],
				GetCurrentDateTime(),
				RemoveConnector(stripslashes($msgsend)),
				'new',
				'',
				'',
				'',
				$userprefs[8]);
			AppendDBFields(MSGREP.$uuser.DBEXT, $dbmsg);
            $sender = empty($userprefs[1]) ? $user[0] : $userprefs[1];
			if (isset($userdb[1][26])) 
			{
				if ($userdb[1][26] == 'on') {
					$eto   = $userdb[1][3];
					$sujet = stripslashes($site[($lang[0] == $lng ? 0 : 11)]).' - '.$admin[1707];
					$bodycorps  = '<div style="margin:auto;padding:8px;width:440px;border: solid 1px #000000;border-radius:4px;">';
					$bodycorps .= '<p style="text-align:left;font:12pt Arial;">';
					$bodycorps .= $admin[1632].sprintf($admin[1708], $uuser, $sender).'<a href="'.$site[3].'" title="'.$site[3].'">'.$site[3].'</a>';
					$bodycorps .= '</p></div>';
					eMailHtmlTo($sujet, $bodycorps, $eto);
				}
			}
		}
		echo '
  <hr />
  <p style="text-align:center;"><b>'.$admin[693].'</b></p>
  <br />';
		echo '
</fieldset>';
	}
}
################################################################################
// gestion des groupes
elseif ($act == 5) {
    $mess = '';
    if ($regit == 5 && $group != '') { //ajouter groupe
        $group = preg_replace('! !', '_', $group);
        $mess = '<br />'.AddGroup($group) ? ucfirst($admin[928]).' '.$group.$admin[929].' !<br />' : ucfirst($admin[928]).' '.$group.$admin[931].' !<br />';
    } elseif ($regit == 6) { // supprimer groupe
        $mess = '<br />'.DelGroup($group) ? ucfirst($admin[928]).' '.$group.$admin[930].' !<br />' : ucfirst($admin[928]).' '.$group.' '.mb_strtolower($admin[1604], $charset).$admin[930].' !<br />';
    }
    $grps = ReadGroups();
	$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[925]);
	include CHEMIN.'admin/hpage.inc';
	if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
	if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
    htable($admin[925], '100%');
    echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=976&mnuid=937&tconfig=0#z2').$admin[701].'<br />'.$admin[926].'</p>
<hr />'.$mess.'
<div class="w-100 m-auto" style="max-width:400px;">
  <form name="membgrp" action="admin.php?lng='.$lng.'&amp;pg=members"  method="post">
    <fieldset style="padding-top:12px;">
      <div class="table-responsive-md">
	  <table class="table table-bordered w-100 text-center m-auto" style="max-width:300px;">
	    <tr class="forum">
		  <td style="width:66%;text-align:left;"><b>'.ucfirst($admin[928]).'</b></td>
		  <td colspan="2" style="text-align:center;"><b>'.$admin[680].'</b></td>
		</tr>';
    foreach ($grps as $grp=>$mbrs) {
    echo '
		<tr class="rep">
		  <td style="width:33%;text-align:left;">'.$grp.'</td>
		  <td style="text-align:center;padding:4px 0;">
		    <a href="admin.php?lng='.$lng.'&amp;pg=members&amp;act=6&amp;group='.$grp.'" title="'.$admin[144].' '.$grp.'">
			  <i class="far fa-edit" style="font-size: 1.3rem;"></i>
            </a>
		  </td>
		  <td style="text-align:center;padding:4px 0;">
		    <a href="admin.php?lng='.$lng.'&amp;pg=members&amp;act=5&amp;regit=6&amp;group='.$grp.'" title="'.$admin[186].' '.$grp.'">
			  <i class="fas fa-times text-danger" style="font-size: 1.4rem;"></i>
            </a>
		  </td>
		</tr>';
    }
    unset($grps);
    echo '
	  </table>
      </div>
      <br />';
    echo '
      <input type="hidden" name="regit" value="5" />
	  <input type="hidden" name="act" value="5" />
	  <div class="text-center">
        <div class="d-inline-block">'.$admin[1590].' '.$admin[928].' : </div>
        <div class="d-inline-block"><input class="form-control texte" type="text" name="group" value="" style="width:196px;" /></div>
      </div>
	</fieldset>
    <div class="row quest p-0 mt-2 w-100 m-auto">
	  <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
      <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[38], '', '', $imgdroite).'</div></div>
    </div>
  </form>
</div>';
}
################################################################################
// gestion des membres du groupe
elseif ($act == 6) { 
	$aMembers = (array)import('aMembers', 'POST');
    if ($regit == 'add' && $group != '' && count($aMembers) > 0) { 
        $mess = '';
		foreach ($aMembers as $member) {
            if (!empty($member))
                $mess .= AddMember($group, $member) ? ucfirst($admin[928]).' '.$group.' : '.$admin[1693].' '.$member.$admin[929].' !<br />' : ucfirst($admin[1693]).' '.$member.$admin[931].' !<br />';
		}
    }
	$mess = empty($mess) ? '' : '<br />'.$mess;
	$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > <a href="admin.php?lng='.$lng.'&amp;pg=members&amp;act=5">'.$admin[925].'</a> > '.str_replace('Admin - ', '', $admin[928]);
	include CHEMIN.'admin/hpage.inc';
	if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
	if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
    htable(ucfirst($admin[928]).' '.$group, '100%');
    echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=976&mnuid=937&tconfig=0#z2').$admin[701].$admin[927].'</p>
<hr />'.$mess;
    if (FileDBExist(GROUPS)) {
        echo '
<div class="w-100 m-auto" style="max-width:400px;">
  <p class="forum" style="text-align:left; padding:5px 20px;">'.$group.'</p>
  <form name="fast_actions" action="admin.php?lng='.$lng.'&amp;pg=members" method="post">
  <fieldset style="padding-top:12px;">
    <input type="hidden" name="regit" value="fast" />
	<input type="hidden" name="act" value="6" />
	<input type="hidden" name="group" value="'.$group.'" />
    <div class="table-responsive-md">
	<table class="table table-bordered w-100 text-center m-auto" style="max-width:330px;">
      <tr>
        <td colspan="2">
          <div class="float-right mb-2" style="padding-right:32px;">
            '.SubmitButton($admin[1602]).'
            <i class="fas fa-level-down-alt align-middle" style="font-size: 1.4rem;"></i>
          </div>
        </td>
      </tr>
	  <tr class="forum">
		<td class="text-left pl-1 py-1"><b>'.ucfirst($admin[1692]).'</b></td>
		<td class="text-center py-1" style="width:76px;"><b>'.$admin[680].'</b></td>
	  </tr>';
        $grps = ReadGroups();
        $lst  = $grps[$group];
        @usort($lst, 'AsciiCompare');
        $count = count($lst);
        $nbpg  = 15;
        if (empty($idpg)) {
            $idpg = 1;
        } else {
            while ($count <= ($idpg - 1)* $nbpg) $idpg--;
        }
        $navbar = GetNavBar('admin.php?lng='.$lng.'&amp;pg=members&amp;act=6&amp;group='.$group.'&amp;idpg=', $count, $idpg, $nbpg);
        $ids    = '';
        for ($i = ($idpg - 1)*$nbpg; $i < Min($nbpg*$idpg, $count); $i++) {
			if (!empty($lst[$i])) {
				echo '
	  <tr class="rep">
		<td class="text-left pl-1 py-1">'.$lst[$i].'</td>
		<td class="text-center py-1">';
                MakeRadioGroup(array( array("d", "s", $i)));
                echo '
		</td>
	  </tr>';
            }
            $ids .= $i." ";
        }
        unset($grps);
        echo '
	</table>
    </div>
    <br />
    <input type="hidden" name="ids" value="'.$ids.'" />
  </form>
  <div class="text-center">'.$navbar.'</div>';
        echo '
  <form name="members" action="admin.php?lng='.$lng.'&amp;pg=members"  method="post">
    <input type="hidden" name="regit" value="add" />
	<input type="hidden" name="act" value="6" />
	<input type="hidden" name="group" value="'.$group.'" />
	<div class="text-center">'.$admin[1590].' '.$admin[1693].' :</div>
	<div class="text-center mb-4">';
        $dbuserspref = array();
        $file_users=opendir(MSGREP);
        while ($nomfichier=readdir($file_users)) {
			if ( substr($nomfichier,-3) == "dtb") {
				$nomuser = str_replace(substr($nomfichier,-4),"",$nomfichier);
				if (!in_array($nomuser, $lst)) $dbuserspref[] = $nomuser;
			}
        }
        closedir($file_users);
        @usort($dbuserspref,'AsciiCompare');
		$size = min(20, count($dbuserspref) + 2);
        echo '
	  <select name="aMembers[]" id="aMembers" size="'.$size.'" multiple="multiple" class="form-control mx-auto" style="width:160px;">';
        for ($i = 0; $i < count($dbuserspref); $i++) {
			echo '
		<option value="'.$dbuserspref[$i].'">'.$dbuserspref[$i].'</option>';
        }
        echo '
	  </select>
	</div>
    <div class="row quest p-0 mt-2 w-100 m-auto">
	  <div class="col-md-6">'.SubmitButton($admin[925], 'self.location.href=\'admin.php?lng='.$lng.'&pg=members&act=5\'', $imggauche, '', 'button').'
      </div>
      <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[38], '', '', $imgdroite).'</div></div>
    </div>
  </fieldset>
  </form>
</div>';
    }
    echo '
  <hr />
  <p class="text-center"><a href="admin.php?lng='.$lng.'&pg=members&act=5" title="">'.$admin[857].$admin[925].'</a></p>
  <hr />';
}
btable();
include CHEMIN.'admin/bpage.inc';
?>
