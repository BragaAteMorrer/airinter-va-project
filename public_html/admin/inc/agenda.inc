<?php
/*******************************************************************************
 *   Admin agenda
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

if (stristr($_SERVER['SCRIPT_NAME'], 'agenda.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[37] == '') {
        $nomzone = $admin[624];
        include 'access.inc';
        exit();
    }
}

$form       = import('form');

$qtype      = TYP_AGENDA;
$qseeall    = $admin[627];
$qwrite     = $admin[628];
$qwriteform = 3;
$bname      = 1;
$bemail     = 1;
$bcat       = 1;
$btitre     = 0;
$bcontenu   = 1;
$bnouveau   = 0;
$qformonly  = 0;
$qtitle     = $admin[625];
$qintro     = '<br />'.ToHelp($lng, '&pg=1064&mnuid=937&tconfig=0#z2').$admin[626].(empty($form) ? $admin[223].$admin[1733] : '');
$qcat       = $admin[630];
$qtitre     = '';
$qcontenu   = $admin[629];
$qnouveau   = '';

include 'inc/admin.inc';
?>