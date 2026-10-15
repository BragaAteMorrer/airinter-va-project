<?php
/*******************************************************************************
 *   Admin Clock Sample Plugin
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2013 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.00 (December 15, 2019) : initial release
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'plugin.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(CHEMIN.'plugins/clock/'.$lng.'-clock.inc')) {
    include CHEMIN.'plugins/clock/'.$lng.'-clock.inc';
} else {
    include CHEMIN.'plugins/clock/en-clock.inc';
}

$plugin_admin_name = $clock[7];
$plugin_admin_url  = 'clock/clock';
$plugin_admin_icon = 'clock/clock.gif';

$plugin_datas_uninstall = array(
   'dirs' => array(
   'plugins/clock',
   'admin/plugins/clock'
   ),
   'files' => array(
   'data/config/rules/clock.dtb'
   ),
   'unreg' => 'clock',
   'include' => 'admin/plugins/clock/uninstall.inc',
   'function' => 'clock_uninstall'
);
?>