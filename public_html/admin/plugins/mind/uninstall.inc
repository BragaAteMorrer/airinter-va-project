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
 
if (stristr($_SERVER['SCRIPT_NAME'], 'uninstall.inc')) {
	header('location:../index.php');
	die();
}

function RemoveSkinsBox() {
    $boxes[0] = 'plugins/mind/boxmind||fbox_mind';
    $allskindir = array();
    $hd = opendir(CHEMIN.'skins/');
    while ($name = readdir($hd)) {
        if ($name != '.' && $name != '..' && is_dir(CHEMIN.'skins/'.$name)) {
            $allskindir[] = CHEMIN.'skins/'.$name.'/';
        }
    }
    $fileconfs = array('confweb');
    foreach ($allskindir as $skindir) {
        foreach ($fileconfs as $fileconf) {
            unset($xposbox, $templates);
            include $skindir.$fileconf.'.inc';
            foreach ($boxes as $box) {
                foreach (array(BOX_LEFT, BOX_CENTER, BOX_RIGHT) as $pos) {
                    if (isset($xposbox[BOX_EDITO][$pos])) {
                        foreach ($xposbox[BOX_EDITO][$pos] as $e=>$item) {
                            $parts = explode('W', $item);
                            if ($box == $parts[0]) $xposbox[BOX_EDITO][$pos][$e] = '';
                        }
                    }
                }
                foreach ($templates as $key=>$name) {
                    foreach (array(BOX_LEFT, BOX_CENTER, BOX_RIGHT) as $pos) {
                        if (isset($xposbox[$key][BOX_TOP][$pos])) {
                            foreach ($xposbox[$key][BOX_TOP][$pos] as $t=>$item) {
                                $parts = explode('W', $item);
                                if ($box == $parts[0]) $xposbox[$key][BOX_TOP][$pos][$t] = '';
                            }
                        }
                        if (isset($xposbox[$key][BOX_BOTTOM][$pos])) {
                            foreach ($xposbox[$key][BOX_BOTTOM][$pos] as $b=>$item) {
                                $parts = explode('W', $item);
                                if ($box == $parts[0]) $xposbox[$key][BOX_BOTTOM][$pos][$b] = '';
                            }
                        }
                    }
                    foreach (array(BOX_LEFT, BOX_RIGHT, BOX_ABOVE, BOX_UNDER) as $pos) {
                        $n = array_search($box, $xposbox[$key][$pos]);
                        if ($n !== false) $xposbox[$key][$pos][$n] = '';
                    }
                }
            }
            $meskin = $skindir;
            include CHEMIN.'admin/inc/'.$fileconf.'.inc';
        }
    }
}

function mind_uninstall($keepdatas) {
    global $sid;
    RemoveSkinsBox();
	include CHEMIN.'admin/plugins/mind/plugin.inc';
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
			if ($reg[0] != $plugin_datas_uninstall['unreg'])
				$tmp[] = $reg;
		WriteDBFields(DBPLUGIN, $tmp);
	}
    $htaccess = file_get_contents(CHEMIN.'.htaccess');
    if (1 == preg_match('!plugins/mind!', $htaccess)) {
        setURLrewriting('');
        setURLrewriting('on');
    }
	header('location:'.CHEMIN.'admin/admin.php?lng='.$lng);
	die();
}
?>