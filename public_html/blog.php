<?php
/*******************************************************************************
 *   Blog
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
 * v6.00.00 (December 15, 2020) : initial release
 ******************************************************************************/

header('Pragma: no-cache');
define('CHEMIN', '');
include CHEMIN.'inc/includes.inc';
include CHEMIN.'inc/blog.inc';
$gybl      = new GY_blog();
$gybl->typ = 'BL';
$result    = $gybl->BLS_ok($userprefs[1], $lng);
$tconfig   = $gybl->tconfig;
$topmess   = $gybl->topmess;
switch ($result[0]) {
case 'h0' : header('HTTP/1.0 403 Forbidden');
case 'h1' : header('location:'.$site[3].'error.php?lng='.$lng.'&err=404');
case 'h2' : header('HTTP/1.0 404 Not Found');
case 'BL' :
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
$result  = $gybl->BL_content();
$topmess = $gybl->topmess;
include CHEMIN.'inc/hpage.inc';
if (function_exists('htable1'))
    htable1($topmess, $gybl->typ.$tconfig, '100%');
else
    htable($topmess, '100%');
echo $gybl->txtart2;
btable();
include 'inc/bpage.inc';
?>