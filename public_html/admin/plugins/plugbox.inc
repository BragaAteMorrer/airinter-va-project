<?php
/*******************************************************************************
 *   Admin Plugins Boxes Manager
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
 * v6.00.00 (December 15, 2020) : initial release
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'plugbox.inc')) {
    header('location:../index.php');
    die();
}

$pluginlist = ExploreDir('plugins/');

if (!empty($pluginlist)) {
    for ($i = 0; $i < count($pluginlist); $i++) {
        $plugin_sidebox_name = '';	
        if(is_file('plugins/'.$pluginlist[$i].'/plugin.inc')) {
            include('plugins/'.$pluginlist[$i].'/plugin.inc');
			if (isset($plugin_sidebox_name) && '' != $plugin_sidebox_name) {
                AddPluginBox($plugin_sidebox_name, $plugin_sidebox_url);
            }
        }
    }
}
?>