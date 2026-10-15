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
 * v6.00.19 (August 26, 2024) : Correct TOhelp
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'optddmenu.inc')) {
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

$ddmenubox = import('ddmenubox');
$dbmenu    = SelectDBFieldsByField(ReadDBFields(DBDDMENUBOX), 4, $ddmenubox);
$typmenu   = $dbmenu[0][1];

$qtype     = TYP_OPTDDMENU;
$bname     = 1;
$bemail    = 1;
$bcat      = $typmenu == 'DDL' ? 0 : 1;
$btitre    = 1;
$bcontenu  = 1;
$bnouveau  = $typmenu == 'DDL' ? 0 : 1;
$qformonly = 0;
$qtitle    = $admin[1219];
$qintro    = '<p style="text-align:center;">'.ToHelp($lng, '&pg=1036&mnuid=937&tconfig=0#z2').'</p>'.$admin[1223].'<strong>'.$dbmenu[0][$lang[0] == $lng ? 2 : 3].'</strong>'.(empty($form) ? $admin[223].$admin[1733] : '');
$qseeall   = $admin[1224];
$qwrite    = $admin[1225];
$qcat      = $typmenu == 'DDL' ? '' : $admin[1353];
$qtitre    = $admin[1226];
$qcontenu  = $typmenu == 'DDL' ? $admin[1389] : $admin[1231];
$qnouveau  = $typmenu == 'DDL' ? '' : $admin[1228];

include 'inc/admin.inc';
?>
