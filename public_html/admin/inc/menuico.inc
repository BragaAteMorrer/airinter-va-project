<?php
/*******************************************************************************
 *   Admin Menubox
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

if (stristr($_SERVER['SCRIPT_NAME'], 'menuico.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[29] == '') {
        $nomzone = $admin[876];
        include 'access.inc';
        exit();
    }
}

$form      = import('form');

$qtype     = TYP_MENUICO;
$bname     = 1;
$bemail    = 1;
$bcat      = 1;
$btitre    = 1;
$bcontenu  = 1;
$bnouveau  = 1;
$qformonly = 0;
$qtitle    = $admin[1238];
$qintro    = '<br />'.$admin[1239].ToHelp($lng, '&pg=1028&mnuid=937&tconfig=0#z2').(empty($form) ? $admin[223] : '');
$qseeall   = $admin[1224];
$qwrite    = $admin[1225];
$qcat      = $admin[193];
$qtitre    = $admin[1237];
$qcontenu  = $admin[1231];
$qnouveau  = $admin[1228];

include 'inc/admin.inc';
?>
