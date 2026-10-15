<?php
/*******************************************************************************
 *   Forum My Publications
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
 *  v6.00.11 (March 09, 2023) : correction access Forum
 ******************************************************************************/

header('Pragma: no-cache');
define('CHEMIN', '');
include CHEMIN.'inc/includes.inc';
include CHEMIN.'inc/forum.inc';
if ($serviz[13] == '') {
        die('STOP ! Forum is not active !');
    }
$gyforum  = new GY_forum();
$gyforum->frtyp = 'FRMP';
$result  = $gyforum->FR_ok($userprefs[1], $lng);
$tconfig = $gyforum->tconfig;
$topmess = $gyforum->topmess;
if ($result[0] == 'FRMP') {
    if (function_exists('htable1'))
        htable1($topmess, $result[0].$tconfig, '100%');
    else
        htable($topmess, '100%');
    echo $result[1];
    btable();
    exit();
}
$result  = $gyforum->FR_myposts();
$topmess = $gyforum->topmess;
if (function_exists('htable1'))
    htable1($topmess, $result[0], '100%');
else
    htable($topmess, '100%');
echo $result[1];
btable();
?>