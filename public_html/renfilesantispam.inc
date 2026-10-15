<?php
/*******************************************************************************
 *   Admin Rename files
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2024 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'renfilesantispam.inc')) {
    header('location:../index.php');
    die();
}

function rename_antispam_files() {
	global $lng;
	@define('CHEMIN', '../../');
	include CONFIG;
	$save     = false;
    $renfiles = array('PG'  => 'postguest',
                      'BLS' => 'blogs',
                      'BL'  => 'blog',
                      'FR'  => 'forum',
                      'FRT' => 'fortopic',
                      'TH'  => 'thread',
                      'GB'  => 'guestbk',
                      'NE'  => 'news',
                      'USR' => 'user',
                      'NWL' => 'newsletter',
                      'DN'  => 'download',
                      'CT'  => 'contact');

	foreach ($renfiles as $key => $file) {
        if (@$site[$key] != $file && !empty(@$site[$key]) && file_exists(CHEMIN.@$site[$key].'.php') && @file_exists(CHEMIN.$file.'.php')) {
            $perms = substr(sprintf('%o', @fileperms(CHEMIN.$site[$key].'.php')), -4);
            @SetChmod(CHEMIN.$site[$key].'.php');
            if (@unlink(CHEMIN.$site[$key].'.php')) {
                if (!rename(CHEMIN.$file.'.php', CHEMIN.$site[$key].'.php')) {
                    $site[$key] = $file;
                    $save = true;
                    @SetChmod(CHEMIN.$site[$key].'.php', $perms);
                }
            }
        }
    }  
    
    // re-activation de l'url-rw
    if ($site['URLR'] == 'on') {
        setURLrewriting('');
        setURLrewriting('on');
    }
}	
?>