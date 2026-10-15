<?php
/*******************************************************************************
 *   Admin Photos
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
 * vv6.00.19 (December 15, 2024) : Tohelp modify
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'photo.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[18] == '') {
        $nomzone = $admin[322];
        include 'access.inc';
        exit();
    }
}

$form      = import('form');

$qtype     = TYP_PHOTO;
$bname     = 1;
$bemail    = 1;
$bcat      = 1;
$btitre    = 1;
$bcontenu  = 1;
$bnouveau  = 0;
$qformonly = 0;
$qtitle    = $admin[323];
$qintro    = '<br />'.ToHelp($lng, '&pg=1038&mnuid=937&tconfig=0#z2').$admin[634].(empty($form) ? $admin[223] : '');
$qseeall   = $admin[635];
$qwrite    = $admin[636];
$qcat      = $admin[327];
$qtitre    = $admin[328];
$qcontenu  = $admin[329];
$qnouveau  = $admin[330];

include 'inc/admin.inc';
?>
