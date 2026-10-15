<?php
/*******************************************************************************
 *   Admin Articles Reactions
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

if (stristr($_SERVER['SCRIPT_NAME'], 'react.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[16] == '') {
        $nomzone = $admin[476];
        include 'access.inc';
        exit();
    }
}

$form      = import('form');

$qtype     = TYP_REACT;
$bname     = 1;
$bemail    = 1;
$bcat      = 0;
$btitre    = 0;
$bcontenu  = 1;
$bnouveau  = 2;
$qformonly = 0;
$qtitle    = $admin[477];
$qintro    = '<p style="text-align:center;">'.ToHelp($lng, '&pg=1054&mnuid=937&tconfig=0#z2').'</p>'.$admin[478].(empty($form) ? $admin[223] : '');
$qseeall   = $admin[172];
$qwrite    = '';
$qcat      = '';
$qtitre    = $admin[173];
$qcontenu  = $admin[174];
$qnouveau  = $admin[261];

$dbart     = ReadDbFields(DBART);

include 'inc/admin.inc';
?>
