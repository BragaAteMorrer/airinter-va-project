<?php
/*******************************************************************************
 *   Admin Articles
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

if (stristr($_SERVER['SCRIPT_NAME'], 'optmenu.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[17] == '') {
        $nomzone = $admin[1210];
        include 'access.inc';
        exit();
    }
}

$form      = import('form');

$menubox   = import('menubox');
$dbmenu    = SelectDBFieldsByField(ReadDBFields(DBMENUBOX), 4, $menubox);

$qtype     = TYP_OPTMENU;
$bname     = 1;
$bemail    = 1;
$bcat      = 1;
$btitre    = 1;
$bcontenu  = 1;
$bnouveau  = 1;
$qformonly = 0;
$qtitle    = $admin[1219];
$qintro    = '<p style="text-align:center;">'.ToHelp($lng, '&pg=1148&mnuid=1030&tconfig=0#z2').'</p>'.$admin[1223].'<strong>'.$dbmenu[0][$lang[0] == $lng ? 2 : 3].' ( n° '.$menubox.' )</strong>'.(empty($form) ? $admin[223].$admin[1733] : '');
$qseeall   = $admin[1224];
$qwrite    = $admin[1225];
$qcat      = $admin[1227];
$qtitre    = $admin[1226];
$qcontenu  = $admin[1231];
$qnouveau  = $admin[1228];

include 'inc/admin.inc';
?>
