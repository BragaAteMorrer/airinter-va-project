<?php
/*******************************************************************************
 *   Admin Downloads
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
 * v6.00.19 (August 26, 2024) : Tohelp modify
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'dnload.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[19] == '') {
        $nomzone = $admin[18];
        include 'access.inc';
        exit();
    }
}

$form      = import('form');

$qtype     = TYP_DNLOAD;
$bname     = 1;
$bemail    = 1;
$bcat      = 1;
$btitre    = 1;
$bcontenu  = 1;
$bnouveau  = 1;
$qformonly = 0;
$qtitle    = $admin[135];
$qintro    = '<p style="text-align:center;">'.ToHelp($lng, '&pg=1056&mnuid=937&tconfig=0#z2').'</p>'.$admin[228].(empty($form) ? $admin[223].$admin[1733] : '');
$qseeall   = $admin[136];
$qwrite    = $admin[137];
$qcat      = $admin[251];
$qtitre    = $admin[138];
$qcontenu  = $admin[140];
$qnouveau  = $admin[139];

include 'inc/admin.inc';
?>
