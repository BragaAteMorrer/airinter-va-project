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
 
if (stristr($_SERVER['SCRIPT_NAME'], 'uninstall.inc')) {
	header('location:../index.php');
	die();
}

function clk_uninstall($keepdatas) {
    global $sid;
    include CHEMIN.'admin/plugins/clock/plugin.inc';
    foreach ($plugin_datas_uninstall['dirs'] as $dir) {
        if ($keepdatas == 'on' && 1 == preg_match('!data/!', $dir)) continue;
        RecursiveRemoteDir(CHEMIN.$dir);
    }
    foreach ($plugin_datas_uninstall['files'] as $file) {
        if ($keepdatas == 'on' && 1 == preg_match('!data/config/rules/!', $file)) continue;
        @unlink(CHEMIN.$file);
    }
    if (!empty($plugin_datas_uninstall['unreg'])) {
        $regs = ReadDBFields(DBPLUGIN);
        $tmp  = array();
        foreach ($regs as $reg)
            if ($reg[0] != $plugin_datas_uninstall['unreg']) $tmp[] = $reg;
        WriteDBFields(DBPLUGIN, $tmp);
    }
    $htaccess = file_get_contents(CHEMIN.'.htaccess');
    if (1 == preg_match('!plugins/clock!', $htaccess)) {
        setURLrewriting('');
        setURLrewriting('on');
    }
    header('location:'.CHEMIN.'admin/admin.php?lng='.$lng);
    die();
}
?>