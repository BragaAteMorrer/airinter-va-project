<?php
/*******************************************************************************
 *   Admin Blog Reactions
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

if (stristr($_SERVER['SCRIPT_NAME'], 'reblog.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[40] == '') {
        $nomzone = $admin[476];
        include 'access.inc';
        exit();
    }
}

$form      = import('form');

$qtype     = TYP_REBLOG;
$bname     = 1;
$bemail    = 1;
$bcat      = 0;
$btitre    = 0;
$bcontenu  = 1;
$bnouveau  = 2;
$qformonly = 0;
$qtitle    = $admin[771];
$qintro    = '<br />'.ToHelp($lng, '&pg=1080&mnuid=937&tconfig=0#z2').$admin[772].(empty($form) ? $admin[223] : '');
$qseeall   = $admin[172];
$qwrite    = '';
$qcat      = '';
$qtitre    = $admin[36];
$qcontenu  = $admin[37];
$qnouveau  = $admin[261];

$dbblog    = ReadDbFields(DBBLOG);

include 'inc/admin.inc';
?>
