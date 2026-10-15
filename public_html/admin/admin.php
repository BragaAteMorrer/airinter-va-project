<?php
/*******************************************************************************
 *   Admin Main page
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
 * v6.00.24 (August 12, 2026) : correction
 ******************************************************************************/

header('Pragma: no-cache');
define('CHEMIN', '../');
include 'includes.inc';
$regit   = import('regit');
$replace = import('replace');
$plug    = import('plug');
$pg      = import('pg', 'GET');
$lng      = import('lng', 'GET');

if (!isset($serviz[31]) || !isset($serviz[176]))
{
    die("!!! Erreur - Veuillez contacter le webmaster du site. !!! Error - Please contact the site's webmaster");
}
if (@preg_match("!//|/\*|#|:/|\.\./|%2E\./|\.%2E/|%2E%2E/|document.cookie|alert|script|script!i", $_SERVER["QUERY_STRING"])) {
    die("Requête non autorisée - Request not allowed");
}
if (stristr($_SERVER['REQUEST_URI'], $lng.'-'.$urlrw[0].'-19')) echo '<script>window.location = "admin.php?lng='.$lng.'";</script>';

if (!is_file(CHEMIN.'admin/'.REDACREP.$userprefs[1].INCEXT)) 
{
		if (!isAdmin($userprefs[1])) {
		// Vous n'avez accès à l'administration
		$topmess = $admin[23];
		include 'hpage.inc';
		htable($admin[23], "100%");
		echo '
	<br />
	<p style="text-align:center;line-height:20px;font-size:14px;"><b>'.$admin[849].'</b></p>
	<form name="logout" action="'.CHEMIN.'index.php?lng='.$lng.'" method="POST"><div style="text-align:center;">'.SubmitButton($admin[464]).'</div></form>';
		btable();
		include 'bpage.inc';
		exit;
	}
}

$logout = import('logout');
if ($logout == 1) {
    if (empty(trim($serviz[31]))) {
        echo BeginJavascript().'
alert("'.$admin[1753].'");
window.location = "admin.php?lng='.$lng.'&pg=config5";'.EndJavascript();
    } else {
        if (isset($_COOKIE[ADMP_COOKIE])) {
            setcookie(ADMP_COOKIE, '', -1);
        }
        echo BeginJavascript().'
window.location = "'.CHEMIN.'index.php?lng='.$lng.'";'.EndJavascript();
    }
}

$fctwri = NULL;
if (FileDBExist(REDACREP.$userprefs[1].INCEXT)) {
    $wri = $userprefs[1];
    include REDACREP.$wri.INCEXT;
    $mdp    = $drtuser[38];
	$fctwri = $drtuser[42];
}
else {
    $wri    = 'admin';
    $fctwri = 'admin';
    $mdp    = $userprefs[1] == '' ? md5('pass') : $userprefs[7];
}

$grpcols = ReadMembersCol();
$myname  = $userprefs[1];
$myemail = $userprefs[2];
if (!isset($grpcols[$myname][0])) $grpcols[$myname][0] = 'ALL';
$grpcol  = isset($grpcols[$myname]) ? $grpcols[$myname][0] : 'ALL';

$boutonleft = $boutonright = '';
$imgdroite  = ' <i class="fas fa-caret-right align-middle" style="font-size: 1.8rem;"></i>';
$imggauche  = '<i class="fas fa-caret-left align-middle" style="font-size: 1.8rem;"></i> ';

if ($userprefs[10] == '') $userprefs[10] = $page[14];
if (isset($selskin)) $userprefs[10] = $selskin;
if ($wri == "admin") {
    $mdp    = $userprefs[1] == '' ? md5('pass') : $userprefs[7];
    $fctwri = 'admin';
} else {
    if (FileDBExist(CHEMIN.'admin/'.REDACREP.$wri.INCEXT)) {
        include(CHEMIN.'admin/'.REDACREP.$wri.INCEXT);
        $mdp    = $drtuser[38];
        $fctwri = $drtuser[42];
    } else {
        $mdp = "bad";
    }
}
$ctrlmdp = abs(crc32($mdp));
$admcookie = '';
$okctrlmdp = false;
if (isset($_COOKIE[ADMP_COOKIE]))
{
	$admcookie = $_COOKIE[ADMP_COOKIE];
	$strlen    = substr($admcookie, 0, 2);
	$okctrlmdp = substr($admcookie, 2, intval($strlen)) == $ctrlmdp ? true : false;
}
else
{
    $admcookie  = $ctrlmdp.'-img-file-img-3-1-1-web-web';
    $adminprefs = explode('-', $admcookie);
    $admdata    = '';
    for ($i = 0; $i < count($adminprefs); $i++) {
        $strlen   = sprintf("%02d", strlen($adminprefs[$i]));
        $admdata .= $strlen.$adminprefs[$i];
    }
    setcookie(ADMP_COOKIE, $admdata);
    echo BeginJavascript().'
window.location = "admin.php?lng='.$lng.'";'.EndJavascript();
}
if (!$okctrlmdp ) {
    die($admin[2028]);
}
$widepage  = 'on';
if(is_file(CONTROLSKIN))
{
	$handle = fopen(CONTROLSKIN, "r");
	$size = filesize(CONTROLSKIN);
	$controleskin = array();
	if ($size !== false)    
	{
		$controleskinlect  = fread($handle, filesize(CONTROLSKIN));
		$controleskind = explode("\n", $controleskinlect);
	}
	if ($controleskind[0] == 'oui')
	{
		echo '
	  <div class="alert alert-danger">
		<strong>'.$admin[2034].'</strong>
	  </div>';
	}
}
################################################################################
if (!empty($_COOKIE[ADMP_COOKIE]) && empty($pg)) {
    // Page d'accueil d'admin.
    $topmess = $admin[1];
    include 'hpage.inc';
    $dirsave  = CHEMIN.'save';
    $ctrlsave = false;
    if (is_dir($dirsave)) {
        $allsave = scandir($dirsave);
        if (FALSE !== $allsave) { 
            foreach ($allsave as $onesave) {
                $info = pathinfo($onesave);
                if (isset($info['extension']) && $info['extension'] == 'zip') {
                    $ctrlsave = true;
                    break;
                }
            }
            unset($allsave);
        }
    }
    $proto   = $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';
    $realurl = $proto.str_replace("/admin","/",$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])); //real site url
    if (($wri == $userprefs[1] && FileDBExist(REDACREP.$wri.INCEXT) && $okctrlmdp && $serviz[42] == "on")) {
        // Collaborateur
        include 'adminredac.php';
		htable($admin[1], "100%");
		echo '
<div style="text-align:center;">'.ToHelp($lng, '&pg=940&mnuid=937&tconfig=0#z2').'
  <p><br />
    <strong>'.$admin[2].'</strong><br />
    '.$admin[3].'<br />
    '.$admin[4].'<br />
  </p>
</div>';
		if ($site[3] != $realurl) {
			include 'inc/check_config.inc';
		}
		elseif (file_exists(CHEMIN.'install/install.php')) {
			include 'inc/check_install.inc';
        }
		if ($ctrlsave) {
			include 'inc/check_save.inc';
		}
		if (!in_array($fctwri, array('redac1', 'redac2', 'modo')) && ($serviz[3] == 'on'))
			include_once 'inc/notes.inc';
		$dirinc = CHEMIN.'admin/incadmin';
		if (is_dir($dirinc)) { 
			$hd = opendir($dirinc);
			while ($file = readdir($hd)) { 
				if (1 == preg_match('!^[^_].*(\.inc|\.inc\.php)$!', $file)) {
					include $dirinc.'/'.$file;
				}
			}
		}
    }
    elseif (($wri == "admin" && $okctrlmdp) || is_file(CHEMIN.'install/install.php')) {
        // Administrateur
        include 'administrateur.php';
		htable($admin[1], "100%");
		echo '
<div style="text-align:center;">'.ToHelp($lng, '&pg=940&mnuid=937&tconfig=0#z2').'
  <p><br />
    <strong>'.$admin[2].'</strong><br />
    '.$admin[3].'<br />
    '.$admin[4].'<br />
  </p>
</div>';
		if ($site[3] != $realurl) {
			include 'inc/check_config.inc';
		}
		elseif (file_exists(CHEMIN.'install/install.php')) {
			include 'inc/check_install.inc';
		}
		if ($ctrlsave) {
			include 'inc/check_save.inc';
		}
        
		include_once 'inc/notes.inc';
		$dirinc = CHEMIN.'admin/incadmin';
		if (is_dir($dirinc)) { 
			$hd = opendir($dirinc);
			while ($file = readdir($hd)) { 
				if (1 == preg_match('!^[^_].*(\.inc|\.inc\.php)$!', $file)) {
					include $dirinc.'/'.$file;
				}
			}
		}
    }
    else {
        // Vous n'avez accès à l'administration
		htable($admin[1], "100%");
        echo '
<br />
<p style="text-align:center;"><b>'.$admin[849].'</b></p>';
    }
    btable();
    include 'bpage.inc';
}
################################################################################
elseif (!empty($_COOKIE[ADMP_COOKIE]) && !empty($pg)) {
    // Autres pages d'admin.
    if (($pg == 'plugin') && (file_exists('plugins/'.$plug.'.inc'))) {
        // pages de plugin
        $admcookie  = $ctrlmdp.'-img-file-img-3-1-1-web-web';
        $adminprefs = explode('-', $admcookie);
        $admdata    = '';
        for ($i = 0; $i < count($adminprefs); $i++) {
            $strlen   = sprintf("%02d", strlen($adminprefs[$i]));
            $admdata .= $strlen.$adminprefs[$i];
        }
        setcookie(ADMP_COOKIE, $admdata);
        $plugindir = substr($plug, 0, strpos($plug,'/'));
        include 'plugins/'.$plugindir.'/plugin.inc';
        if(FileDBExist(REDACREP.$userprefs[1].INCEXT) && $drtuserplg[$plugindir] == '') {
            $nomzone = $plugin_admin_name;
            include 'inc/access.inc';
        }
        else {
            include 'plugins/'.$plug.'.inc';
        }
		exit;
    }
    elseif (file_exists('inc/'.$pg.'.inc')) {
        // Pages standards
        include 'inc/'.$pg.'.inc';
		exit;
    }
    else {
        // Pages inexistantes
		$topmess = $admin[1];
		include 'hpage.inc';
		if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
		if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
		htable($admin[1], "100%");
        echo '
<br />
<p style="text-align:center;"><b>'.$admin[20].'</b> '.$admin[21].'</p>
<br />
<p style="text-align:center;"><a href="'.CHEMIN.'admin/admin.php?lng='.$lng.'">'.$admin[22].'</a></p>';
		btable();
		include 'bpage.inc';
    }
} else {
    echo '
<script>window.location = "admin.php?lng='.$lng.'";</script>';
}
?>
