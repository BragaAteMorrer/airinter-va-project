<?php
/*******************************************************************************
 *   Admin Banners
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

if (stristr($_SERVER['SCRIPT_NAME'], 'banner.inc')) {
	header('location:../index.php');
	die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
	include REDACREP.$userprefs[1].INCEXT ;
	if ($drtuser[27] == '') {
		$nomzone = $admin[263];
		include 'access.inc';
		exit();
	}
}

$form      = import('form');

$qtype     = TYP_BANNER;
$bname     = 0;
$bemail    = 0;
$bcat      = 1;
$btitre    = 1;
$bcontenu  = 0;
$bnouveau  = 1;
$qformonly = 0;
$qtitle    = $admin[264];
$qintro    = '<p style="text-align:center;">'.ToHelp($lng, '&pg=1024&mnuid=937&tconfig=0#z2').'</p>'.$admin[265].(empty($form) ? $admin[223] : '');
$qseeall   = $admin[266];
$qwrite    = $admin[267];
$qcat      = $admin[269];
$qtitre    = $admin[268];
$qcontenu  = "";
$qnouveau  = $admin[918];

include 'inc/admin.inc';
?>