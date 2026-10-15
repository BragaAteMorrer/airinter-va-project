<?php
/*******************************************************************************
 *   Admin Links
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

if (stristr($_SERVER['SCRIPT_NAME'], 'faq.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[21] == '') {
        $nomzone = $admin[206];
        include 'access.inc';
        exit();
    }
}

$form      = import('form');

$qtype     = TYP_FAQ;
$bname     = 1;
$bemail    = 1;
$bcat      = 1;
$btitre    = 2;
$bcontenu  = 1;
$bnouveau  = 0;
$qformonly = 0;
$qtitle    = $admin[200];
$qintro    = '<p style="text-align:center;">'.ToHelp($lng, '&pg=1060&mnuid=937&tconfig=0#z2').'</p>'.$admin[201].(empty($form) ? $admin[223].$admin[1733] : '');
$qseeall   = $admin[202];
$qwrite    = $admin[203];
$qcat      = $admin[233];
$qtitre    = $admin[204];
$qcontenu  = $admin[205];
$qnouveau  = '';

include 'inc/admin.inc';
?>
