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
    header('location:index.php');
    die();
}

if (is_file(CHEMIN.'plugins/speed/'.$lng.'-speed.inc')) {
    include CHEMIN.'plugins/speed/'.$lng.'-speed.inc';
} else {
    include CHEMIN.'plugins/speed/en-speed.inc';
}

$plugin_bar_name   = $speed[7];
$plugin_bar_url    = $site['URLR'] == 'on' ? 'plugins/speed/'.$lng.'-speedcnt' : 'speed.php?lng='.$lng;
$plugin_bar_icon   = 'speed.gif';
$plugin_bar_anchor = true;
?>