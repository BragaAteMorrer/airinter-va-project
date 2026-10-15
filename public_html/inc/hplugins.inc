<?php
/*******************************************************************************
 *   Registered Plugins Management
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
 * v6.00.03 (August 10, 2021) : adaptation to php 8
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'hplugins.inc')) {
    header('location:../index.php');
    die();
}

$PluginsDB = ReadDBFields(DBPLUGIN);

for ($i = 0; $i < count($PluginsDB); $i++) {
	unset($plugin_bar_name, $plugin_bar_icon, $plugin_bar_url, $plugin_bar_alturls, $plugin_bar_anchor);

	if (is_file(CHEMIN.'plugins/'.$PluginsDB[$i][0].'/plugin.inc')) {
		include CHEMIN.'plugins/'.$PluginsDB[$i][0].'/plugin.inc';
	}
    if (!empty($plugin_bar_icon) && @is_file(CHEMIN.'plugins/'.$PluginsDB[$i][0].'/'.$plugin_bar_icon)) {
        $plugin_bar_icon = '<img class="mx-0 mt-n1" style="width:2rem;" src="'.CHEMIN.'plugins/'.$PluginsDB[$i][0].'/'.$plugin_bar_icon.'" alt="" />';
    }
    else {
        $plugin_bar_icon = '<i class="fas fa-plug"></i>';
    }

	// if (isset($plugin_bar_name) && $plugin_bar_name != '') {
	if (!empty($plugin_bar_name)) {
        $PluginsDB[$i][1] = empty($PluginsDB[$i][1]) ? 'ALL' : $PluginsDB[$i][1];
        $PluginsDB[$i][2] = empty($PluginsDB[$i][2]) ? 'ALL' : $PluginsDB[$i][2];
		$menu[$plugin_bar_name.'_'.$PluginsDB[$i][0]] = array(
			'id'       => $PluginsDB[$i][0],
            'z2'       => isset($plugin_bar_anchor) && $z2 ? $plugin_bar_anchor : $z2,
			'name'     => strip_tags($plugin_bar_name),
			'title'    => $plugin_bar_name,
			'icon'     => $plugin_bar_icon,
            'pageexec' => $PluginsDB[$i][1] == 'ALL' ? $tconfig : $PluginsDB[$i][1],
            'pagedisp' => $PluginsDB[$i][2] == 'ALL' ? $tconfig : $PluginsDB[$i][2],
            'granted'  => true,
			'url'      => ($site['URLR'] == 'on') ? $plugin_bar_url : 'plugins/'.$PluginsDB[$i][0].'/'.$plugin_bar_url,
			'alturls'  => isset($plugin_bar_alturls) && is_array($plugin_bar_alturls) ? $plugin_bar_alturls : array(),
			'base'     => parse_url($plugin_bar_url, PHP_URL_PATH),
			'target'   => '_SELF'
		);
	}
}
?>