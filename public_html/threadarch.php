<?php
/*******************************************************************************
 *   Forum Thread Archive
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
 *  v6.00.15 (November 29, 2023) : Correction archive functionnality
 ******************************************************************************/

header('Pragma: no-cache');
define('CHEMIN', '');
include CHEMIN.'inc/includes.inc';
include CHEMIN.'inc/funcarch.php';
include CHEMIN.'inc/forum.inc';
if ($serviz[13] == '') {
        die('STOP ! Forum is not active !');
    }
$widepage = $forum[3];
$gyforum  = new GY_forum();
$gyforum->frtyp = 'THA';
$result  = $gyforum->FR_ok($userprefs[1], $lng);
$tconfig = $gyforum->tconfig;
$topmess = $gyforum->topmess;
if ($result[0] == 'THA') {
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
$result  = $gyforum->THA_content();
$topmess = $gyforum->topmess;
if ($userprefs[3] == '' || $userprefs[3] == BOX_LEFT.BOX_RIGHT) {
    $left_empty = array_reduce($xposbox[$tconfig][BOX_LEFT], 'EstNonVide', true);
    $userprefs3 = $left_empty ? BOX_RIGHT : BOX_LEFT;
}
if ($result[1] == 1) {
    include CHEMIN.'inc/hpage.inc';
    if (function_exists('htable1'))
        htable1($topmess, $result[0].$tconfig, '100%');
    else
        htable($topmess, '100%');
    if (!empty($result[3])) echo '<p>'.$result[3].'</p>';
    if (!empty($result[2])) echo BeginJavascript().'window.location="'.CHEMIN.$result[2].'";'.EndJavascript();
    btable();
    include 'inc/bpage.inc';
    exit();
}
include CHEMIN.'inc/hpage.inc';
if (function_exists('htable1'))
    htable1($topmess, $result[0].$tconfig, '100%');
else
    htable($topmess, '100%');
echo $result[1];
btable();
include 'inc/bpage.inc';
?>