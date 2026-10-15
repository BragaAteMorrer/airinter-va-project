<?php
/*******************************************************************************
 *   CHARTER reading Module
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
include CHEMIN.'inc/forum.inc';
$gyforum  = new GY_forum();
$gyforum->frtyp = 'FRRU';
$result  = $gyforum->FR_ok($userprefs[1], $lng);
$tconfig = $gyforum->tconfig;
$topmess = $gyforum->topmess;
if ($result[0] == 'FRRU') {
    if (function_exists('htable1'))
        htable1($topmess, $result[0].$tconfig, '100%');
    else
        htable($topmess, '100%');
    echo $result[1];
    btable();
    exit();
}
$result  = $gyforum->FR_rules();
$topmess = $gyforum->topmess;
if (function_exists('htable1'))
    htable1($topmess, $result[0], '100%');
else
    htable($topmess, '100%');
echo $result[1];
btable();
?>