<?php
/*******************************************************************************
 *   Site Index Top of Page
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2022 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.19 (August 20, 2024) : add Opcache code
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'hpage.inc')) {
    header('location:../index.php');
    die();
}

if (function_exists('opcache_get_configuration'))
{
/*
echo 'Dans constantes.php Opcachedisable pour les disques 1 pour oui, 0 pour enable?: '.OPCACHEDISABLE.'<br />';
echo 'Dans constantes.php Opcachetempo en seconde: '.OPCACHETEMPOSECONDE.'<br />';
echo 'Dans constantes.php Mise en place des Ini_set pour oui Disable/Enable =>1 , pour Non =>0: '.OPCACHEINISET.'<br />';
	// Appel du tableau des configurations OPcache enregistrées dans php.ini
	$mropcacheconfiguration = opcache_get_configuration();
	echo '<br />';
	var_dump($mropcacheconfiguration); // Affichage du tableau
	echo '<br />';
*/
	// Si opcache est active mettre en disable
	if ($site[7] == 'on')
	{
		if (OPCACHEDISABLE == 1)
		{
			if (OPCACHEINISET == 1) // Gestion des options caches enable/disable
			{	
				if (ini_get('opcache') == 1) 
				{
					ini_set('opcache.enable', '0');
	//				echo ' OPcache désactivé fait<br />';
				}
			}
		}
	}
} // fin de existance opcache_get_configuration()
$toppage = stripslashes($site[($lang[0] == $lng ? 0 : 11)]);
if (($page[22]) && (!empty($topmess))) {
    $toppage .= ' - '.strip_tags($topmess);
}
if ($serviz[32] == 'on' && $serviz[42] == 'on' && FileDBExist(CHEMIN.'admin/redac/'.$userprefs[1].INCEXT)) {
    include CHEMIN.'admin/redac/'.$userprefs[1].INCEXT;
}
echo '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="'.$lng.'">';

	///////////////////////////////////////////////////////////////////////////////////
	//                                                                               //
	//                GuppY is protected by the CeCILL FREE LICENSE.                 //
	//  REMOVING OR MODIFYING THE FOLLOWING COPYRIGHT NOTICE IS STRICTLY FORBIDDEN.  //
	//                                                                               //
	///////////////////////////////////////////////////////////////////////////////////

echo '

  <!--[  GuppY v'.MP_VERSION.' CeCILL Copyright (C) '.MP_COPYRIGHT.' by Laurent Duveau - https://www.freeguppy.org/  ]-->

<head>
<meta http-equiv="content-type" content="text/html; charset='.$charset.'" />
<meta name="application-name" content="'.$toppage.'" />

<title>'.$toppage.'</title>

<meta name="description" content="'.$site[1].'" />
<meta name="generator" content="GuppY CMS" />
<meta name="author" content="'.$site[9].'" />
<meta name="keywords" content="'.$site[2].'" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">';
echo '
<link rel="shortcut icon" href="'.$site[3].'favicon.ico">';

echo '
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" 
  integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/css/bootstrap.min.css" 
  integrity="sha512-rt/SrQ4UNIaGfDyEXZtNcyWvQeOq0QLygHluFQcSjaGB04IxWhal71tKuzP6K8eYXYB6vJV4pHkXcmFGGQ1/0w==" crossorigin="anonymous" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.3.1/styles/default.min.css" 
  integrity="sha512-3xLMEigMNYLDJLAgaGlDSxpGykyb+nQnJBzbkQy2a0gyVKL2ZpNOPIj1rD8IPFaJbwAgId/atho1+LBpWu5DhA==" crossorigin="anonymous" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/SocialIcons/1.0.1/soc.min.css" 
  integrity="sha512-PTz/4lAo890ortUEd041dNdebPVxpjxZiTTgW8DXUTIiPZQGXFua9U7izCygP7NqHUDmaDF4F1CswmblvYq4Vw==" crossorigin="anonymous" />
<link rel="stylesheet" href="'.CHEMIN.'admin/admstyle.css" />
<link rel="stylesheet" href="'.CHEMIN.'admin/admbs4style.css" />
<link rel="stylesheet" href="'.CHEMIN.'admin/inc/img/admin/admin.css" />
<link rel="stylesheet" href="'.CHEMIN.'admin/inc/img/files/admfiles.css" />
<link rel="stylesheet" href="'.CHEMIN.'inc/lang/flags.css" />
<link rel="stylesheet" href="'.CHEMIN.'inc/img/browser/browsers.css" />
<link rel="stylesheet" href="'.CHEMIN.'inc/img/avatars/'.$page[23].'.css" />
<link rel="stylesheet" href="'.CHEMIN.'inc/img/general/forum.css" />
<link rel="stylesheet" href="'.CHEMIN.'inc/img/general/general.css" />
<link rel="stylesheet" href="'.CHEMIN.'admin/inc/img/admin/admin_secure.css" />
';

echo BeginJavascript().'
var charset = "'.$charset.'";
var site0 = "'.htmlspecialchars($site[($lang[0] == $lng ? 0 : 11)]).'";
var site3 = "'.$site[3].'";
'.EndJavascript();

// ordre de chargement : jquery, popper, bootstrap (ou jquery, bundle)
echo 
  JavascriptFile(CHEMIN.'inc/hpage.js').'
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" 
  integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/js/bootstrap.bundle.min.js" 
	integrity="sha512-igl8WEUuas9k5dtnhKqyyld6TzzRjvMqLC79jkgT3z02FvJyHAuUtyemm/P/jYSne1xwFI06ezQxEwweaiV7VA==" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js" 
  integrity="sha512-0QbL0ph8Tc8g5bLhfVzSqxe9GERORsKhIn1IrpxDAgUsbBGz/V7iSav2zzW325XGd1OMLdL4UiqRJj702IeqnQ==" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.3.1/highlight.min.js" 
  integrity="sha512-Pbb8o120v5/hN/a6LjF4N4Lxou+xYZ0QcVF8J6TWhBbHmctQWd8O6xTDmHpE/91OjPzCk4JRoiJsexHYg4SotQ==" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/SocialIcons/1.0.1/soc.min.js" 
  integrity="sha512-eUJ3eP9+avp5kHKhfx5gB0vzLEgMkZiOmZcpVqJmlg9hMMse2SChMOTSDWl6oYGfmYW2N7oO/W6CqpgYBneqKw==" crossorigin="anonymous">
</script>
<script src="https://unpkg.com/bootstrap-show-password@1.2.1/dist/bootstrap-show-password.min.js"></script>
';

$loader = '';
$unloader = '';

echo $headinc;

echo '
</head>
<body'.
    (!empty($loader)  ? ' onload="'.$loader.'"' : '').
    (!empty($unloader) ? ' onunload="'.$unloader.'"' : '').'>';
echo '
<div id="page">';
echo '
  <div id="header">
';

if ($serviz[44] == "on") {
    // Site en travaux
    echo '
    <div id="work" class="notice" style="padding:10px; border:3px solid red; text-align:center; font-weight:bold; font-size:larger;">
      ATTENTION : site en travaux - CAUTION: work in progress
    </div>
';
}
echo '
  </div>
  <a id="top"></a>
';

$bccount = 0;
$wposbox = array();
foreach ($templates as $keyconf=>$valueconf) {
	foreach (array(BOX_LSIDE, BOX_LEFT, BOX_CENTER, BOX_RIGHT, BOX_RSIDE) as $key) {
		foreach ($xposbox[$keyconf][BOX_TOP][$key] as $i=>$value) {
			$args  = '';
			if (!empty($value)) {
				$pos   = strrpos($value, 'W');
				$args  = $pos === false ? '' : substr($value, $pos + 1);
				$value = $pos === false ? $value : substr($value, 0, $pos);
				$value = substr($value, -1) == '|' ? substr($value, 0, -2) : $value;			
			}
			$xposbox[$keyconf][BOX_TOP][$key][$i] = $value;
			$wposbox[$keyconf][BOX_TOP][$key][$i] = $args;
		}
		foreach ($xposbox[$keyconf][BOX_BOTTOM][$key] as $i=>$value) {
			$args  = '';
			if (!empty($value)) {
				$pos   = strrpos($value, 'W');
				$args  = $pos === false ? '' : substr($value, $pos + 1);
				$value = $pos === false ? $value : substr($value, 0, $pos);
				$value = substr($value, -1) == '|' ? substr($value, 0, -2) : $value;
			}
			$xposbox[$keyconf][BOX_BOTTOM][$key][$i] = $value;
			$wposbox[$keyconf][BOX_BOTTOM][$key][$i] = $args;
		}
	}
}
foreach (array(BOX_LSIDE, BOX_LEFT, BOX_CENTER, BOX_RIGHT, BOX_RSIDE) as $key) {
	foreach ($xposbox[BOX_EDITO][$key] as $i=>$value) {
		$args  = '';
		if (!empty($value)) {
			$pos   = strrpos($value, 'W');
			$args  = $pos === false ? '' : substr($value, $pos + 1);
			$value = $pos === false ? $value : substr($value, 0, $pos);
			$value = substr($value, -1) == '|' ? substr($value, 0, -2) : $value;
		}
		$xposbox[BOX_EDITO][$key][$i] = $value;
		$wposbox[BOX_EDITO][$key][$i] = $args;
	}
}

// php or html scripts located in directory admin/incadmin are automatically include at the top of the page
if (is_dir('incadmin')) { 
	$hd = opendir('incadmin');
	while ($file = readdir($hd)) { 
        if (1 == preg_match('!^[^_].*(\.inc|\.inc\.php)$!', $file)) {
			include 'incadmin/'.$file;
		}
	}
}

$tconfig = !isset($templates[$tconfig]) ? 0 : $tconfig;

$flags = '';
if ($lang[1] != '') {
    $flagurl = str_replace('&', '&amp;', $endurl);
    if ($lng == $lang[0]) $flagurl = str_replace('lng='.$lang[0], 'lng='.$lang[1], $flagurl); 
    if ($lng == $lang[1]) $flagurl = str_replace('lng='.$lang[1], 'lng='.$lang[0], $flagurl); 
    $flags = '
  <div>
    <a href="'.$flagurl.'"><i class="flg-'.$lang[$lng == $lang[0] ? 1 : 0].'" title="'.$lang[$lng == $lang[0] ? 1 : 0].'"></i></a>
  </div>';
}
echo '
  <div id="AdminBoxes">
    <!-- Begin of ADMIN TOPBOXES -->
	<div class="row titre text-center mb-2 py-2 rounded-pill">
      <div class="col-md-1 part-left px-3">'.$flags.'</div>
      <div class="col-md-6 part-right px-0">
        '.$site[($lang[0] == $lng ? 0 : 11)].' ['.MP_VERSION.'] &nbsp;<i class="far fa-hand-point-right"></i>&nbsp;
      </div>';
if (!isset($grpcol))  $grpcol = 'ALL';
if ($userprefs[1] != '') {
    $userstatus = isset($drtuser[42]) ? $drtuser[42] : 'admin';
    echo '
      <div class="col-md-5 part-left pl-1 pr-0">'.$userprefs[1].' '.$userstatus.' : '.($grpcol != '' ? $grpcol : 'ALL').'</div>';
}
echo '
    </div>
    ';
?>
