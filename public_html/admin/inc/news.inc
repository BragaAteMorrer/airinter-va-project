<?php
/*******************************************************************************
 *   Admin News
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

if (stristr($_SERVER['SCRIPT_NAME'], 'news.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[14] == '') {
        $nomzone = $admin[13];
        include 'access.inc';
        exit();
    }
}

$form      = import('form');

$qtype     = TYP_NEWS;
$bname     = 1;
$bemail    = 1;
$bcat      = 0;
$btitre    = 1;
$bcontenu  = 1;
$bnouveau  = 2;
$qformonly = 0;
$qtitle    = $admin[125];
$qintro    = '<p style="text-align:center;">'.ToHelp($lng, '&pg=1050&mnuid=937&tconfig=0#z2').'</p>'.$admin[227].(empty($form) ? $admin[223].$admin[1733] : '');
$qseeall   = $admin[126];
$qwrite    = $admin[127];
$qcat      = '';
$qtitre    = $admin[162];
$qcontenu  = $admin[128];
$qnouveau  = $admin[261];

include 'inc/admin.inc';
?>
