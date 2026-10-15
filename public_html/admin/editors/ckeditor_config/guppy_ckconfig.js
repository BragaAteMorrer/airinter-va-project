/*
Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:

    /**
     * Définitions des barres d'outils
     */
    config.toolbar_Default =    // Barre par défaut (ensemble de tous les outils)
    [
    	['Source','-','Save','NewPage','Preview','-','Templates'],
    	['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
    	['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
    	['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'],
    	'/',
    	['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
    	['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'],
    	['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
    	['Link','Unlink','Anchor'],
    	['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
    	'/',
    	['Styles','Format','Font','FontSize'],
    	['TextColor','BGColor'],
    	['Maximize', 'ShowBlocks','-','About']
    ];
    
    config.toolbar_Guppy_in = [     // Barre affichée par l'éditeur intégré
    	['Source','NewPage','Templates'],
		['Cut','Copy','Paste','PasteText','PasteFromWord'],
        ['SpellChecker', 'Scayt','Undo','Redo','SelectAll','Find','Replace','RemoveFormat'],
        ['Table','Citation','HorizontalRule','SpecialChar','ckawesome'],
    	'/',
    	['Bold','Italic','Underline','Strike','Subscript','Superscript'],
        ['NumberedList','BulletedList','Outdent','Indent'],
        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
		['TextColor','BGColor','Image','Youtube','Html5video'],
		['Link','Unlink','Anchor'],
    	'/',
    	['Styles','Format','Font','FontSize'],['Iframe','pbckcode'],
        ['Smiley'],['CreateDiv','ShowBlocks','Preview','Print','Maximize','About']
    ] ;
    //
		
	config.fillEmptyBlocks = false;
	
    config.colorButton_enableMore = true;  // Affiche (true) ou non (false) le bouton "Plus de couleurs..." 

    config.disableNativeSpellChecker = false;   // Active (false) ou désactive (true) le vérificateur orthographique des navigateurs (firefox et safari)
    config.disableNativeTableHandles = false;   // Active (false) ou désactive (true) les outils présents nativement dans les navigateurs (actuellement Firefox seulement)
    config.disableObjectResizing = false;        // Active (false) ou désactive (true) le redimensionnement des images et des tableaux    

	config.extraPlugins = 'table,image,html5video,widget,widgetselection,clipboard,lineutils,nbsp,pbckcode,citation,ckawesome,youtube,lytebox';
	
    config.entities = false;   
    
    config.format_tags    = 'p;h1;h2;h3;h4;h5;h6;pre;address;div' ;
    config.font_names     = 'Arial;Comic Sans MS;Courier New;Tahoma;Times New Roman;Verdana' ;
    config.fontSize_sizes = '8/8px;9/9px;10/10px;11/11px;12/12px;13/13px;14/14px;15/15px;16/16px;18/18px;20/20px;22/22px;24/24px;26/26px;28/28px;36/36px;48/48px;72/72px';
  
    config.forcePasteAsPlainText = false;
    config.forceSimpleAmpersand = false;
	config.allowedContent = true;
	
	config.youtube_responsive = true;
	config.youtube_controls = true;
       
    config.resize_dir = 'vertical';
    
    config.skin = 'moonocolor_v1.1, ' + site3 + 'admin/editors/ckeditor_config/custom/skins/moonocolor_v1.1/';
    
    config.templates_files =
    [
        site3 + 'admin/editors/ckeditor_config/custom/templates/templates/default.js'
    ];
    config.templates = 'default';
    
    config.stylesSet = 'styles:'+ site3 + 'admin/editors/ckeditor_config/custom/styles/styles.js';
	
};
