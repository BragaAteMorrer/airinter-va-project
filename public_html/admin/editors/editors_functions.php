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
 * v6.00.06 (January 13, 2022) : update ckeditor
 ******************************************************************************/

if (stristr($_SERVER["SCRIPT_NAME"], "editors_functions.php")) {
	header("location:../index.php");
	die();
}

/**
 * Affiche l'éditeur dans la partie administration 
 * 
 * @param string  $name    Nom de l'éditeur
 * @param string  $width   Largeur de l'éditeur (voir fichier de condif)
 * @param string  $height  Hauteur de l'éditeur
 * @param string  $content Contenu de l'éditeur
 * @param array   $params  Paramètres optionnels
 * @return string
 */
function display_admin_editor($name, $width, $height, $content, $params = array())
{
    $path_editors = 'admin/editors/';
    
    WYSIWYG_admin_editor($name, $width, $height, $content, $params, $path_editors);
}

// --------------------------------------------------------------------

/**
 * Affiche un éditeur WYSIWYG 
 * 
 * @param string $name Nom de l'éditeur
 * @param string $width Largeur de l'éditeur (voir fichier de condif)
 * @param string $height Hauteur de l'éditeur
 * @param string $value Contenu de l'éditeur
 * @param 
 * @return string
 */
function WYSIWYG_admin_editor($name, $width, $height, $value, $params = array(), $path_editors = '')
{
    // settype($params, 'array');
    
    /**
     * Editeur CKEditor
     */
    if (is_file(CHEMIN.'inc/ckeditor/ckeditor.php'))
    {
        require_once CHEMIN.'inc/ckeditor/ckeditor.php';
        
        $path = 'inc/ckeditor/';
        $toolbarName = empty($params['toolbarName'])? 'Guppy_in' : $params['toolbarName'];
     
        return CKeditor($name, $path, $width, $height, $value, $toolbarName, $path_editors);
    }
    //
    
    return FALSE;   
}


// --------------------------------------------------------------------

/**
 * Affiche l'éditeur WYSIWYG CKEditor
 * 
 * @param string $name Nom de l'éditeur
 * @param string $path Chemin relatif du répertoire contenant l'éditeur
 * @param string $width Largeur de l'éditeur (voir fichier de condif)
 * @param string $height Hauteur de l'éditeur
 * @param string $value Contenu de l'éditeur
 * @param string $toolbarname Nom de la barre d'outil de l'éditeur
 * @param string $path_editors Chemin relatif du répertoire contenant la configuration de l'éditeur
 * @return string
 */
function CKeditor($name, $path, $width, $height, $value, $toolbarname, $path_editors)
{
	global $site, $lng;

	/**
     * Création de l'éditeur
     */
	$oCKeditor = new CKeditor() ;
	$oCKeditor -> basePath = $site[3].$path;
    $oCKeditor -> config['width'] = (strpos($_SERVER['HTTP_USER_AGENT'], "WebKit") !== false)? '577px' : $width;   // Correction bug avec Safari et Chrome
    $oCKeditor -> config['width'] = $width;
	$oCKeditor -> config['height'] = $height;
	$oCKeditor -> config['baseHref'] = $site[3];
	$oCKeditor -> config['customConfig'] = $site[3].$path_editors.'ckeditor_config/guppy_ckconfig.js';
	$oCKeditor -> config['contentsCss'] = $site[3].$path_editors.'ckeditor_config/guppy_contents.css';
	$oCKeditor -> config['language'] = is_file(CHEMIN.'inc/ckeditor/lang/'.$lng.'.js')? $lng : 'en';	
	$oCKeditor -> config['toolbar'] = $toolbarname;
	$oCKeditor -> config['smiley_path'] = $site[3].'inc/ckeditor/plugins/smiley/images/';
    $oCKeditor -> config['filebrowserBrowseUrl'] = $site[3].$path_editors.'upload/upload.php?lng='.$lng.'&uptype=Link&namerepconfig=ckeditor_config&pathconfig='.$path_editors ; //récupération du répertoire file, img, photo, pages de guppy
    $oCKeditor -> config['filebrowserImageBrowseUrl'] = $site[3].$path_editors.'upload/upload.php?lng='.$lng.'&uptype=Image&namerepconfig=ckeditor_config&pathconfig='.$path_editors; //récupération du répertoire img et photo de guppy
    $oCKeditor -> config['filebrowserImageBrowseLinkUrl'] = $site[3].$path_editors.'upload/upload.php?lng='.$lng.'&uptype=Link&namerepconfig=ckeditor_config&pathconfig='.$path_editors ; //récupération du répertoire file, img, photo, pages de guppy
    //
    
    // Affichage de l'éditeur
    echo '<div class="text-center">'.$oCKeditor -> editor($name, $value).'</div>';
	//
}

// --------------------------------------------------------------------

/**
 * Insère les fonctions javascripts nécessaires pour les éditeurs
 */ 
$headinc .= '<script src="'.CHEMIN.'admin/editors/editors_scripts.js"></script>';

// --------------------------------------------------------------------

/**
 * Transforme un chemin relatif en chemin absolu pour images et liens
 * (fonction obsolète)
 */ 
function PathRelativeAbsolute($chaine) {
	return $chaine;
}

// --------------------------------------------------------------------

/**
 * Transforme un chemin absolu en chemin relatif pour images et liens
 */
function PathAbsoluteRelative($chaine)
{
	global $site;

	$str = $chaine;
	
	$in = array(
				'\\',
				'<p>&nbsp;</p>',
				'<br type="_moz" />',
				'<u>',
				'</u>',
				'style="TEXT-DECORATION: underline"',
				'="'.$site[3]
	);
	$out = array(
				'&#92;',
				'<br />',
				'',
				'<span style="text-decoration:underline;">',
				'</span>',
				'style="text-decoration: underline;"',
				'="'
	);

	$str = str_replace($in, $out, $str);
	$str = preg_replace("` src=\"(?:\.\.\/)+`", ' src="', $str);
	$str = preg_replace('`(<br />|&#160;)$`', '', $str);
	
	return $str;
}

?>