<?php
/*******************************************************************************
 *   Confirm User preferencies
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
 * v6.00.04 (December 14, 2021) : add config path
 ******************************************************************************/

header('Pragma: no-cache');
define('CHEMIN', '');
include CHEMIN.'inc/includes.inc';
include CHEMIN.'inc/user.inc';
include CHEMIN.'data/config/config.inc';
$gyusr   = new GY_user();
$result  = $gyusr->USR_ok($userprefs[1], $lng);
$tconfig = $gyusr->tconfig;
if (!empty($result[0])) {
    $topmess = $gyusr->topmess;
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
$result  = $gyusr->USR_content();
$topmess = $gyusr->topmess;
include CHEMIN.'inc/hpage.inc';
if (function_exists('htable1'))
    htable1($topmess, $result[0], '100%');
else
    htable($topmess, '100%');
echo $result[1];
btable();
include 'inc/bpage.inc';
?>