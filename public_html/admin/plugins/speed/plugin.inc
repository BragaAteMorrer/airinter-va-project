<?php
/*******************************************************************************
 *   Plugin Speed
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

if (is_file(CHEMIN.'plugins/speed/'.$lng.'-speed.inc')) {
    include CHEMIN.'plugins/speed/'.$lng.'-speed.inc';
} else {
    include CHEMIN.'plugins/speed/en-speed.inc';
}

$plugin_admin_name = $speed[7];
$plugin_admin_url  = 'speed/speed';
$plugin_admin_icon = 'speed/speed.gif';

$plugin_datas_uninstall = array(
   'dirs' => array(
   'plugins/speed',
   'admin/plugins/speed'
   ),
   'files' => array(
   'data/config/rules/speed.dtb',
   ),
   'unreg' => 'speed',
   'include' => 'admin/plugins/speed/uninstall.inc',
   'function' => 'speed_uninstall'
);

AddPluginBox($speed[7], 'plugins/speed/boxspeed', 'fbox_speed', BOX_ABOVE.BOX_UNDER);
?>