<?php
/*******************************************************************************
 *   Admin RSS News
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
 * v6.00.19 (December 15, 2024) : Tohelp modify
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'rss.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[34] == '') {
        $nomzone = $admin[602];
        include 'access.inc';
        exit();
    }
}

$form      = import('form');

$qtype     = TYP_RSS;
$bname     = 0;
$bemail    = 0;
$bcat      = 1;
$btitre    = 1;
$bcontenu  = 0;
$bnouveau  = 0;
$qformonly = 0;
$qtitle    = $admin[603];
$qintro    = '<br />'.ToHelp($lng, '&pg=1022&mnuid=937&tconfig=0#z2').$admin[604].(empty($form) ? $admin[223] : '');
$qseeall   = $admin[605];
$qwrite    = $admin[606];
$qcat      = $admin[607];
$qtitre    = $admin[608];
$qcontenu  = '';
$qnouveau  = '';

include 'inc/admin.inc';
?>
