<?php
/*******************************************************************************
 *   Admin Blog topics
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
 * v6.00.19 (August 26, 2024) : Correction TOhelp
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'brub.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[39] == '') {
        $nomzone = $admin[15];
        include 'access.inc';
        exit();
    }
}

$form      = import('form');

$qtype     = TYP_BRUB;
$bname     = 1;
$bemail    = 1;
$bcat      = 0;
$btitre    = 1;
$bcontenu  = 0;
$bnouveau  = 0;
$qformonly = 0;
$qtitle    = $admin[145];
$qintro    = '<br />'.ToHelp($lng, '&pg=1074&mnuid=937&tconfig=0#z2').$admin[79].(empty($form) ? $admin[223].$admin[1733] : '');
$qseeall   = $admin[146];
$qwrite    = $admin[366];
$qcat      = '';
$qtitre    = $admin[1772];
$qcontenu  = '';
$qnouveau  = '';

include 'inc/admin.inc';
?>