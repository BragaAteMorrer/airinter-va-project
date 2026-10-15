<?php
/*******************************************************************************
 *   Plugins Functions
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

if (stristr($_SERVER['SCRIPT_NAME'], 'funcplug.inc')) {
    header('location:../index.php');
    die();
}

function PluginRegistered($pluginame) {
    if (FileDBExist(DBPLUGIN)) {
        $DataDB = ReadDBFields(DBPLUGIN);
        for ($i = 0; $i < count($DataDB); $i++) {
            if ($DataDB[$i][0] == $pluginame) {
                return true;
            }
        }
    }
    return false;
}

function RegisterPlugin($pluginame, $pluginexec='ALL', $plugindisp='ALL') {
    $newplug[0] = $pluginame;
    $newplug[1] = $pluginexec;
    $newplug[2] = $plugindisp;
    AppendDBFields(DBPLUGIN, $newplug);
}

function UnregisterPlugin($pluginame) {
    $oldplug = ReadDBFields(DBPLUGIN);
    $newplug = array();
    for ($i = 0 ; $i < count($oldplug); $i++) {
        if ($oldplug[$i][0] != $pluginame) {
            $newplug[] = $oldplug[$i];
        }
    }
    WriteDBFields(DBPLUGIN, $newplug);
}
?>