<?php
/*******************************************************************************
 *   Admin newsletter
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

if (stristr($_SERVER['SCRIPT_NAME'], 'nwl.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[33] == '') {
        $nomzone = $admin[546];
        include 'access.inc';
        exit();
    }
}

$qtype     = TYP_NWL;
$qmenu     = '<a href="admin/admin.php?lng='.$lng.'&amp;pg=newsletter">'.$admin[535].'</a> ||';
$qseeall   = $admin[539];
$qwrite    = $admin[538];
$bname     = 0;
$bemail    = 0;
$bcat      = 0;
$btitre    = 1;
$bcontenu  = 1;
$bnouveau  = 0;
$qformonly = 0;
$qtitle    = $admin[545];
$qintro    = '<br />'.ToHelp($lng, '&pg=1088&mnuid=937&tconfig=0#z2').$admin[531].(empty($form) ? $admin[223].$admin[1733] : '');
$qcat      = '';
$qtitre    = $admin[537];
$qcontenu  = $admin[536];
$qnouveau  = '';

include 'inc/admin.inc';
?>
