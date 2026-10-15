<?php
/*******************************************************************************
 *   Mind Plugin
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

if (is_file(CHEMIN.'plugins/mind/'.$lng.'-mind.inc')) {
    include CHEMIN.'plugins/mind/'.$lng.'-mind.inc';
} else {
    include CHEMIN.'plugins/mind/en-mind.inc';
}

$plugin_admin_name = $mind[7];
$plugin_admin_url  = 'mind/mind';
$plugin_admin_icon = 'mind/mind.gif';

$plugin_datas_uninstall = array(
   'dirs' => array(
   'plugins/mind',
   'admin/plugins/mind'
   ),
   'files' => array(
   'data/config/rules/mind.dtb',
   ),
   'unreg' => 'mind',
   'include' => 'admin/plugins/mind/uninstall.inc',
   'function' => 'mind_uninstall'
);

AddPluginBox($mind[8], 'plugins/mind/boxmind', 'fbox_mind', 'LRAUTBE');
?>