<?php
/*******************************************************************************
 *   Load Includes
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
 * v6.00.24 (August 12, 2026) : Delete lines log Check for CONFIG existence
 ******************************************************************************/

if (version_compare(phpversion(), '5.6.0', '<')) {
    die(sprintf('Insufficient version of PHP (%s) : 5.6.0 or higher necessary', phpversion()));
}

if (@preg_match("!//|/\*|#|:/|\.\./|%2E\./|\.%2E/|%2E%2E/|document.cookie|alert!i", $_SERVER["QUERY_STRING"])) {
    die("Requête non autorisée - Request not allowed");
}
if (@preg_match("!//|/\*|#|:/|\.\./|%2E\./|\.%2E/|%2E%2E/|document.cookie|alert|wp-!i", $_SERVER['PHP_SELF'])) {
    die("Requête non autorisée - Request not allowed");
}
$cherchscript = substr_count($_SERVER['PHP_SELF'],'alert');
$cherchscript1 = substr_count($_SERVER['PHP_SELF'],'target');
$cherchscript2 = substr_count($_SERVER['PHP_SELF'],'title');
$cherchscript = $cherchscript + $cherchscript1 + $cherchscript2;
if ($cherchscript > 0)
 die("Requête non autorisée - Request not allowed");

if (stristr($_SERVER['SCRIPT_NAME'], 'includes.inc')) {
    header('location:index.php');
    die();
}

error_reporting( E_ALL ^ E_NOTICE );

$endurl  = strip_tags($_SERVER['REQUEST_URI']);
$lang    = array('','');
$charset = 'UTF-8';
$tconfig = 0;
$cryptos = array(2=>'cryptotexte', 3=>'cryptoseltexte');

include CHEMIN.'inc/functions.php';
if (file_exists(CONFIG))
	include CONFIG;
else
	{
		die("!!! Erreur - Veuillez contacter le webmaster du site. !!! Error - Please contact the site's webmaster");
	}
define('SITE3', $site[3]);

if (isset($_GET['securekey'])) {
    ClearCookies();
    if (!defined('NO_CRYPT') && $serviz[100] > 0) {
        include CRYPTREP.$cryptos[$serviz[100]].'.php';
        include CRYPTREP.$cryptos[$serviz[100]].'.fct.php';
    }
    $location = CHEMIN.'index.php?lng='.$lng.'&selskin='.$page[14].'&changekey=on';
    echo '
<script>
  window.location="'.$location.'";
</script>';
    header("location:".$location); 
    exit();
}

if (!defined('NO_CRYPT') && $serviz[100] > 0) include CRYPTREP.$cryptos[$serviz[100]].'.fct.php';
if (substr($serviz[101], -2) != 'px') $serviz[101] = '800px';

include CHEMIN.'inc/func_groups.php';
include CHEMIN.'inc/func_groupscol.php';
include CHEMIN.'inc/func_groupsnwl.php';
include CHEMIN.'inc/func_boxes.php';
include CHEMIN.'inc/func_blogboxes.php';
include CHEMIN.'inc/func_forum.php';
include CHEMIN.'inc/func_news.php';

$prt = import('prt');
if (!in_array($prt, array(-3, -2, -1, 0, 1, 2, 3))) $prt = 0;
date_default_timezone_set($site['TZ']);

function TestInteger($name) {
    $GLOBALS[$name] = import($name);
    if (isset($GLOBALS[$name])) {
        $n = preg_match('!^([0-9]+)!', $GLOBALS[$name], $res);
        if ($n == 0 || $res[1] == '')
            $GLOBALS[$name] = NULL;
        else
            $GLOBALS[$name] = $res[1];
    }
}

TestInteger('pg');
TestInteger('id');
TestInteger('num');

$lng        = import('lng');
$userprefs  = array_pad(array(), 31, '');
if (!empty($_COOKIE[USER_COOKIE]) && !isset($_GET['changekey'])) {
    $usercookie = gy_decrypt(CRYPT_KEY, $_COOKIE[USER_COOKIE]);
    $positem    = 0;
    for ($i = 0; $i < 31; $i++) {
        $poslen        = $positem + 2;
        $posdata       = $positem + 4;
        $lendata       = intval(substr($usercookie, $poslen, 2));
        $userprefs[$i] = substr($usercookie, $posdata, $lendata);
        $userprefs[$i] = $userprefs[$i] == 'off' ? '' : $userprefs[$i];
        $positem       = $posdata + $lendata;
    }
    if (($userprefs[0] == $lang[0] || $userprefs[0] == $lang[1]) && empty($lng)) {
        $lng = strip_tags($userprefs[0]);
    }
    $userprefs[0] = strip_tags($userprefs[0]);
    $userprefs[1] = preg_replace('![^-a-zA-Z0-9_]!i', '', substr(strip_tags($userprefs[1]), 0, $serviz[148]));
    $userprefs[2] = strip_tags($userprefs[2]);
    $userprefs[3] = strip_tags($userprefs[3]);
    $userprefs[4] = strip_tags($userprefs[4]);
    $userprefs[5] = strip_tags($userprefs[5]);
    $userprefs[6] = str_replace('\n', '<br />', strip_tags($userprefs[6]));
    $userprefs[7]  = strip_tags($userprefs[7]);
    $userprefs[8]  = strip_tags($userprefs[8]);
    $userprefs[9]  = strip_tags($userprefs[9]);
    $userprefs[10] = strip_tags($userprefs[10]);
    $userprefs[11] = strip_tags($userprefs[11]);
    $userprefs[12] = strip_tags($userprefs[12]);
    $userprefs[13] = strip_tags($userprefs[13]);
    $userprefs[14] = strip_tags($userprefs[14]);
    $userprefs[15] = strip_tags($userprefs[15]);
    $userprefs[16] = strip_tags($userprefs[16]);
    $userprefs[17] = strip_tags($userprefs[17]);
    $userprefs[18] = strip_tags($userprefs[18]);
    $userprefs[19] = strip_tags($userprefs[19]);
    $userprefs[20] = strip_tags($userprefs[20]);
    $userprefs[21] = strip_tags($userprefs[21]);
    $userprefs[22] = strip_tags($userprefs[22]);
    $userprefs[23] = strip_tags($userprefs[23]);
    $userprefs[24] = strip_tags($userprefs[24]);
    $userprefs[25] = strip_tags($userprefs[25]);
    $userprefs[26] = strip_tags($userprefs[26]);
    $userprefs[27] = strip_tags($userprefs[27]);
    $userprefs[28] = strip_tags($userprefs[28]);
    $userprefs[29] = strip_tags($userprefs[29]);
    $userprefs[30] = strip_tags($userprefs[30]);
}

if (!isset($selskin)) $selskin = import('selskin');
$selskin = preg_replace('`[^-a-z0-9_]`i', '', $selskin);
if (is_file(CHEMIN.'data/config/rotaskins.dtb')) {
    $now_time  = date('YmdHi');
    $skn       = $page[14];
    $rotaskins = @file(CHEMIN.'data/config/rotaskins.dtb');
    if (!empty($rotaskins)) {
        for ($i = 0; $i < count($rotaskins); $i++) {
            $rota    = explode(CONNECTOR, $rotaskins[$i]);
            $rota[0] = substr($rota[0], 0, 4) == '----' ? str_replace('----', date('Y'), $rota[0]) : $rota[0];
            $rotaskins[$i] = $rota[0].CONNECTOR.$rota[1];
        }
        rsort($rotaskins);
        foreach ($rotaskins as $rotaskin) {
            $rota     = explode(CONNECTOR, $rotaskin);
            if ($now_time >= $rota[0]) {
                $skn = $rota[1];
                $skn = $skn[strlen($skn)-1] == chr(10) ? substr($skn, 0, -1) : $skn;
                break;
            }
        }
        if (!empty($skn) && $skn != $page[14] && $selskin != $page[14] && $userprefs[10] == '') {
            $selskin  = $skn;
        }
    }
}
if (empty($page[14])) {
    $page[14] = 'no_skin';
}
// ne pas modifier les 2 lignes suivantes / do not modify the 2 following lines
if (!empty($userprefs[10])) { $page[14] = $userprefs[10]; }
/// Debut Fork GHOST
elseif (is_file(CHEMIN.'plugins/ghost/ghost.inc')) {
    include_once CHEMIN.'plugins/ghost/ghost.inc';
    if (!empty($_COOKIE[GHOST_COOKIE])) {
        $tmpprefs   = array_pad(array(), 31, '');
        $usercookie = $_COOKIE[GHOST_COOKIE];
        $positem    = 0;
        for ($i = 0; $i < 31; $i++) {
            $poslen       = $positem + 2;
            $posdata      = $positem + 4;
            $lendata      = intval(substr($usercookie, $poslen, 2));
            $tmpprefs[$i] = substr($usercookie, $posdata, $lendata);
            $tmpprefs[$i] = $tmpprefs[$i] == 'off' ? '' : $tmpprefs[$i];
            $positem      = $posdata + $lendata;
        }
        $page[14] = strip_tags($tmpprefs[10]);
        unset($tmprefs);
    }
}
/// Fin Fork GHOST					
if (!empty($selskin)) { $page[14] = $selskin; }
if (empty($selskin)) { $selskin = $page[14]; }

$pathconf = is_file(CHEMIN.'skins/'.$page[14].'/skin'.INCEXT) ? $page[14] : 'no_skin';
$fileconf = 'confweb';
include CHEMIN.'skins/'.$pathconf.'/skin'.INCEXT;
include CHEMIN.'inc/funcskin'.INCEXT;
include CHEMIN.'skins/'.$pathconf.'/confskin'.INCEXT;
include CHEMIN.'skins/'.$pathconf.'/'.$fileconf.INCEXT;
$z2 = !isset($body[16]) ? '#z2' : ($body[16] == 'on' ? '#z2' : '');

$lng  = empty($lng) ? $lang[0] : (!in_array($lng, $lang) ? $lang[0] : $lng);
$aml0 = 'articles.php?lng='.$lng.'&pg='.$serviz[177].'&tconfig='.$serviz[178];
$aml1 = $lng.'-'.$urlrw[20].'-102-'.$serviz[177].'-'.$serviz[178];
$aml  = '<a href="'.(!empty($serviz[177]) ? CHEMIN.($site['URLR'] == 'on' ? $aml1 : $aml0).$z2 : '#').'" title="">';
$wbm0 = $site['PG'].'.php?lng='.$lng.'&typ='.TYP_MAIL;
$wbm1 = $lng.'-'.$urlrw[14].'-'.TYP_MAIL;
$wbm  = '<a href="'.CHEMIN.($site['URLR'] == 'on' ? $wbm1 : $wbm0).$z2.'" title="">';
include CHEMIN.INCREP.'lang/'.$lng.'-help'.INCEXT;
include(CHEMIN.INCREP."lang/".$lng."-web".INCEXT);

$usertime = !empty($members[21]) ? intval($members[21]) : 157680000;
$expires  = $userprefs[1] == $serviz[31] || is_file(CHEMIN.'admin/'.REDACREP.$userprefs[1].INCEXT) ? 157680000 : $usertime;
$aconnect = isset($_COOKIE[CNCT_COOKIE]) ? explode(CONNECTOR, gy_decrypt(CRYPT_KEY, $_COOKIE[CNCT_COOKIE])) : array(0, '');
$savetime = intval($aconnect[0]);
$ctrltime = time() - $savetime;
$exit     = !empty($members[21]) ? $ctrltime >= $expires : false;
if ($exit && $userprefs[1] != '' && FileDBExist(MSGREP.$userprefs[1].DBEXT)) {
    ClearCookies();
    if (!defined('NO_CRYPT') && $serviz[100] > 0) {
        include CRYPTREP.$cryptos[$serviz[100]].'.php';
        include CRYPTREP.$cryptos[$serviz[100]].'.fct.php';
    }
    $location = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng);
    echo '
<script>
  window.location="'.$location.'";
</script>';
    header("location:".$location); 
    exit();
}

$userconnect = gy_crypt(CRYPT_KEY, time().CONNECTOR.$userprefs[1].CONNECTOR.date('Ymdhi'));
// avant @setcookie(CNCT_COOKIE, $userconnect, time() + $expires, '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
// Modif pour 365 jours
@setcookie(CNCT_COOKIE, $userconnect, strtotime( '+365 days' ), '/', $_DOMAIN, $_SECURE, $_HTTPONLY);

if (!isset($_SERVER['REQUEST_URI'])) {
    $REQUEST_URI = $_SERVER['SCRIPT_NAME']."?".$_SERVER['QUERY_STRING'];
}
StartTimer();

if (!headers_sent()) header('Content-type: text/html; charset='.$charset);

if ($lng == $lang[0]) {
    $npage = @array($web[169], getLabel($nom[1]), getLabel($nom[24]), getLabel($nom[22]), getLabel($nom[9]), getLabel($nom[0]), getLabel($nom[3]), 
    getLabel($nom[7]), getLabel($nom[2]), '', getLabel($nom[20]), getLabel($nom[26]), $web[144], getLabel($nom[42]), $web[485], 
    $web[486], $web[487], $web[488], $web[489], $web[490]);
} else {
    $npage = array($web[169], getLabel($nom[11]), getLabel($nom[25]), getLabel($nom[23]), getLabel($nom[19]), getLabel($nom[10]), getLabel($nom[13]),
        getLabel($nom[17]), getLabel($nom[12]), '', getLabel($nom[21]), getLabel($nom[27]), $web[144], getLabel($nom[43]),
        $web[485], $web[486], $web[487], $web[488], $web[489], $web[490]);
}

$flag1 = '<i class="flg-'.$lang[0].' align-middle" title="'.$lang[0].'"></i>';
$flag2 = '<i class="flg-'.$lang[1].' align-middle" title="'.$lang[1].'"></i>';

if (stristr($_SERVER['SCRIPT_NAME'], 'calendar.php') === false && stristr($_SERVER['SCRIPT_NAME'], 'blogcal.php') === false && strpos($endurl, '/install/') === false) {
    @include CHEMIN.INCREP.'log.inc' ;
    @include CHEMIN.INCREP.'log_stats.inc' ;
}
include CHEMIN.INCREP.'security.inc';
	$datjourl = new DateTime('now');
	$dateheureminiutesecl = $datjourl->format('Y_m_d');
	$nomficlog = CHEMIN.'data/log/security'.'_'.$dateheureminiutesecl.'.log';
if (!isset($actionwaf)) $actionwaf = '1';
if ($actionwaf == 1) ExecuteBannishment();					 

if (!defined('NO_CRYPT') && $serviz[100] > 0 && empty($_COOKIE['GuppYCrypt'])) include CRYPTREP.$cryptos[$serviz[100]].'.php';
  
if (strpos($endurl, '/install/') === false) {
    if ($serviz[44] == 'on' &&  ($userprefs[1] == '' || (trim($serviz[31]) != $userprefs[1] && trim($serviz[176]) != $userprefs[1]))) {
        header('location: '.CHEMIN.'maintenance.php?lng='.$lng);
    }
}

?>
