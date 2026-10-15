<?php
/*******************************************************************************
 *   Functions for editors
 *******************************************************************************
 *   GuppY PHP Script - version 6
 *   CeCILL Copyright (C) 2004-2023 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.11 (March 09, 2023) : maj ckeditor
 ******************************************************************************/

if (stristr($_SERVER["SCRIPT_NAME"], "editors_functions.php")) {
	header("location:../index.php");
	die();
}

/**
 * Affiche l'éditeur dans la partie web 
 * 
 * @param string  $name    Nom de l'éditeur
 * @param string  $width   Largeur de l'éditeur (voir fichier de condif)
 * @param string  $height  Hauteur de l'éditeur
 * @param string  $content Contenu de l'éditeur
 * @param array   $params  Paramètres optionnels
 * @return string
 */
function display_editor($name, $width, $height, $value, $params = array()) {
	global $site, $lng;
    
    if (is_file(CHEMIN.'inc/ckeditor/ckeditor.php')) {
        require_once CHEMIN.'inc/ckeditor/ckeditor.php';
        $path        = 'inc/ckeditor/';
        $site[37]    = empty($site[37]) ? 'moonocolor_v1.1' : $site[37];
        $toolbarName = empty($params['toolbarName']) ? 'Guppy_out_min' : $params['toolbarName'];
        $smileyBar   = empty($params['smileyBar']) ? '' : $params['smileyBar'];

        $oCKeditor                         = new CKeditor() ;
        $oCKeditor->basePath               = $site[3].$path;
        $oCKeditor->config['width']        = $width;
        $oCKeditor->config['height']       = $height;
        $oCKeditor->config['baseHref']     = $site[3];
        $oCKeditor->config['customConfig'] = $site[3].'inc/ckeditor_config/guppy_ckconfig.js';
        $oCKeditor->config['contentsCss']  = [ $site[3].'inc/ckeditor_config/guppy_contents.css', $site[3].'inc/ckeditor_config/guppy_contents_plus.css' ];
        $oCKeditor->config['language']     = is_file(CHEMIN.'inc/ckeditor/lang/'.$lng.'.js') ? $lng : 'en';	
        $oCKeditor->config['toolbar']      = $toolbarName;
        $oCKeditor->config['smiley_path']  = 'inc/ckeditor/plugins/smiley/images/';
        $oCKeditor->config['skin']         = $site[37].', '.$site[3].'inc/ckeditor_config/skins/'.$site[37].'/';
        echo '<div class="text-center">'.$oCKeditor->editor($name, $value).'</div>';
    }
    else
        return false;
}

// --------------------------------------------------------------------

/**
 * Insère les fonctions javascripts nécessaires pour les éditeurs
 */ 
$headinc .= '<script src="'.CHEMIN.'inc/ckeditor_config/editors_scripts.js"></script>';

?>