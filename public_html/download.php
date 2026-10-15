<?php
/*******************************************************************************
 *   Download
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
 * v6.00.04 (December 14, 2021) : correction php 8
 ******************************************************************************/

header('Pragma: no-cache');
define('CHEMIN', '');
include CHEMIN.'inc/includes.inc';
//include(CHEMIN.INCREP."lang/".$lng."-web".INCEXT);
include CHEMIN.'inc/download.inc';
$gydwnl  = new GY_download();
$result  = $gydwnl->DWNL_ok($userprefs[1], $lng);
$tconfig = $gydwnl->tconfig;
$topmess = $gydwnl->topmess;
switch ($result[0]) {
case 'h0' : header('HTTP/1.0 403 Forbidden');
case 'h1' : header('location:'.$site[3].'error.php?lng='.$lng.'&err=404');
case 'h2' : header('HTTP/1.0 404 Not Found');
case 'DWNL' :
    include CHEMIN.'inc/hpage.inc';
    if (function_exists('htable1'))
        htable1($topmess, $result[0].$tconfig, '100%');
    else
        htable($topmess, '100%');
    echo $result[1];
    btable();
    include 'inc/bpage.inc';
    exit();
}
$result  = $gydwnl->DWNL_content();
$topmess = $gydwnl->topmess;
include CHEMIN.'inc/hpage.inc';
if (function_exists('htable1'))
    htable1($topmess, $result[0].$tconfig, '100%');
else
    htable($topmess, '100%');
echo $result[1];
btable();
include 'inc/bpage.inc';
?>