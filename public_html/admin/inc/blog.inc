<?php
/*******************************************************************************
 *   Admin Blog
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

if (stristr($_SERVER['SCRIPT_NAME'], 'blog.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[39] == '') {
        $nomzone = $admin[770];
        include 'access.inc';
        exit();
    }
}

$form      = import('form');

$qtype     = TYP_BLOG;
$bname     = 1;
$bemail    = 1;
$bcat      = 1;
$btitre    = 1;
$bcontenu  = 1;
$bnouveau  = 2;
$qformonly = 0;
$qtitle    = $admin[771];
$qintro    = '<br />'.ToHelp($lng, '&pg=1078&mnuid=937&tconfig=0#z2').$admin[772].(empty($form) ? $admin[223].$admin[1733] : '');
$qseeall   = $admin[782];
$qwrite    = $admin[783];
$qcat      = $admin[794];
$qtitre    = $admin[784];
$qcontenu  = $admin[785];
$qnouveau  = $admin[261];

include 'inc/admin.inc';
?>