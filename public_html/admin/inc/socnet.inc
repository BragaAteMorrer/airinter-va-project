<?php
/*******************************************************************************
 *   Social networks
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

if (stristr($_SERVER['SCRIPT_NAME'], 'socnet.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[50] == '') {
        $nomzone = $admin[336];
        include 'access.inc';
        exit();
    }
}

$form      = import('form');

$qtype     = TYP_SOCNET;
$bname     = 1;
$bemail    = 0;
$bcat      = 0;
$btitre    = 1;
$bcontenu  = 1;
$bnouveau  = 1;
$qformonly = 0;
$qtitle    = $admin[361];
$qintro    = '<br />'.$admin[362].ToHelp($lng, '&pg=1048&mnuid=937&tconfig=0#z2').(empty($form) ? $admin[223] : '');
$qseeall   = $admin[363];
$qwrite    = $admin[365];
$qcat      = '';
$qtitre    = $admin[396];
$qcontenu  = $admin[1231];
$qnouveau  = $admin[456];

include 'inc/admin.inc';
?>
