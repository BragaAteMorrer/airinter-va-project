<?php
/*******************************************************************************
 *   Load Includes
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2020 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.24 (August 12, 2026) : Check for CONFIG existence
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'includes.inc')) {
    header('location:../index.php');
    die();
}
if (@preg_match("!//|/\*|#|:/|\.\./|%2E\./|\.%2E/|%2E%2E/|document.cookie|script!i", $_SERVER["QUERY_STRING"])) {
    die("Requête non autorisée - Request not allowed");
}
error_reporting( E_ALL ^ E_NOTICE );

$endurl = strip_tags($_SERVER['REQUEST_URI']);
$lang   = array('','');

if (!defined('PHP700')) define('PHP700', version_compare(PHP_VERSION, '7.0.0', '>='));
@ob_implicit_flush();

include CHEMIN.'inc/functions.php';
if(!file_exists(CONFIG))
{
	die("!!! Erreur - Veuillez contacter le webmaster du site. !!! Error - Please contact the site's webmaster");
}
else
{
	include CONFIG;
}
define('SITE3', $site[3]);
$tconfig = 0;

$_SERVER['HTTPS'] = is_ssl() ? 'on' : '';

if (substr($serviz[101], -2) != 'px') $serviz[101] = '800px';

include CHEMIN.'inc/func_groups.php';
include CHEMIN.'inc/func_groupscol.php';
include CHEMIN.'inc/func_groupsnwl.php';
include CHEMIN.'inc/func_boxes.php';
include CHEMIN.'inc/func_blogboxes.php';

date_default_timezone_set($site['TZ']);
$pg  = import('pg');
$lng = import('lng');

$userprefs = array_pad(array(), 31, '');
if (!empty($_COOKIE[USER_COOKIE])) {
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
if (empty($page[14])) {
    $page[14] = 'no_skin';
}
if (!empty($userprefs[10])) {
    $page[14] = $userprefs[10];
}
if (!empty($selskin)) {
    $page[14] = $selskin;
}
if (empty($selskin)) {
    $selskin = $page[14];
}

if (empty($lng)) {
    $lng = $lang[0];
} elseif (($lng != $lang[0]) && ($lng != $lang[1])) {
    $lng = $lang[0];
}

if (!isset($_SERVER['REQUEST_URI'])) {
    $REQUEST_URI = $_SERVER['SCRIPT_NAME']."?".$_SERVER['QUERY_STRING'];
}

$pathconf = is_file(CHEMIN.'skins/'.$page[14].'/skin'.INCEXT) ? $page[14] : 'no_skin';
$fileconf = 'confweb';
include CHEMIN.'skins/'.$pathconf.'/skin'.INCEXT;
include CHEMIN.'inc/funcskin'.INCEXT;
include CHEMIN.'skins/'.$pathconf.'/confskin'.INCEXT;
include CHEMIN.'admin/inc/check_confs'.INCEXT;
include CHEMIN.'skins/'.$pathconf.'/'.$fileconf.INCEXT;
$z2 = !isset($body[16]) ? '#z2' : ($body[16] == 'on' ? '#z2' : '');

$flag1 = '<i class="flg-'.$lang[0].' align-middle" title="'.$lang[0].'"></i>';
$flag2 = '<i class="flg-'.$lang[1].' align-middle" title="'.$lang[1].'"></i>';
$aml0 = 'articles.php?lng='.$lng.'&pg='.$serviz[177].'&tconfig='.$serviz[178];
$aml1 = $lng.'-'.$urlrw[20].'-102-'.$serviz[177].'-'.$serviz[178];
$aml  = '<a href="'.(!empty($serviz[177]) ? CHEMIN.($site['URLR'] == 'on' ? $aml1 : $aml0).$z2 : '#').'" title="">';
$wbm0 = $site['PG'].'.php?lng='.$lng.'&typ='.TYP_MAIL;
$wbm1 = $lng.'-'.$urlrw[14].'-'.TYP_MAIL;
$wbm  = '<a href="'.CHEMIN.($site['URLR'] == 'on' ? $wbm1 : $wbm0).$z2.'" title="">';
include CHEMIN.INCREP.'lang/'.$lng.'-help'.INCEXT;
include CHEMIN.INCREP.'lang/'.$lng.'-admin'.INCEXT;
include CHEMIN.INCREP.'lang/'.$lng.'-web'.INCEXT;

include CHEMIN.'admin/inc/admfunctions.php';
StartTimer();
?>