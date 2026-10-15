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
    config.toolbar_Default =  [  // Barre par défaut (idem Guppy_out_min)
        ['PasteText','PasteFromWord'],['TextColor','BGColor'],['Bold','Italic','Underline'],['Subscript','Superscript'],
        ['Citation'],['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],['Table','HorizontalRule','ckawesome'],
        ['Link','Unlink'],['NumberedList','BulletedList'],['Outdent','Indent'],['Undo','Redo'],['Smiley','Preview','Maximize']
    ];
    //
    config.toolbar_Guppy_out = [  // Barre pour forum, blog, nouvelles, réactions aux articles, commentaire blog, contact, sans image
		['PasteText','PasteFromWord'],['TextColor','BGColor'],['Bold','Italic','Underline'],['Subscript','Superscript'],
        ['Citation','pbckcode'],['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],['Table','HorizontalRule','ckawesome'],
        ['Link','Unlink'],['NumberedList','BulletedList'],['Outdent','Indent'],['Undo','Redo'],['Smiley','Preview','Maximize']
    ] ;
    //
    config.toolbar_Guppy_out_img = [  // Barre pour forum, blog, nouvelles, réactions aux articles, commentaire blog, avec image
		['PasteText','PasteFromWord'],['TextColor','BGColor','Image'],['Bold','Italic','Underline'],['Subscript','Superscript'],
        ['Citation','pbckcode'],['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],['Table','HorizontalRule','ckawesome'],
        ['Link','Unlink'],['NumberedList','BulletedList'],['Outdent','Indent'],['Undo','Redo'],['Smiley','Preview','Maximize']
    ] ;
    //
    config.toolbar_Guppy_out_min = [ // Barre pour le livre d or
		['PasteText','PasteFromWord'],['TextColor','BGColor'],['Bold','Italic','Underline'],['Subscript','Superscript'],
        ['Citation'],['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],['Table','HorizontalRule','ckawesome'],
        ['Link','Unlink'],['NumberedList','BulletedList'],['Outdent','Indent'],['Undo','Redo'],['Smiley','Preview','Maximize']
    ] ;
    //
    config.toolbar_Guppy_out_min_img = [ // Barre pour contact avancé
		['PasteText','PasteFromWord'],['TextColor','BGColor','Image'],['Bold','Italic','Underline'],['Subscript','Superscript'],
        ['Citation','pbckcode'],['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],['Table','HorizontalRule','ckawesome'],
        ['Link','Unlink'],['NumberedList','BulletedList'],['Outdent','Indent'],['Undo','Redo'],['Smiley','Preview','Maximize']
    ] ;
    //
		
	config.fillEmptyBlocks = false;
	
    config.colorButton_enableMore = true;  // Affiche (true) ou non (false) le bouton "Plus de couleurs..." 

    config.disableNativeSpellChecker = false;   // Active (false) ou désactive (true) le vérificateur orthographique des navigateurs (firefox et safari)
    config.disableNativeTableHandles = false;   // Active (false) ou désactive (true) les outils présents nativement dans les navigateurs (actuellement Firefox seulement)
    config.disableObjectResizing = false;        // Active (false) ou désactive (true) le redimensionnement des images et des tableaux    

	config.extraPlugins = 'table,image,nbsp,citation,ckawesome,pbckcode';
	
    config.entities = false;   
    
    config.format_tags    = 'p;h1;h2;h3;h4;h5;h6;pre;address;div' ;
    config.font_names     = 'Arial;Comic Sans MS;Courier New;Tahoma;Times New Roman;Verdana' ;
    config.fontSize_sizes = '8/8px;9/9px;10/10px;11/11px;12/12px;13/13px;14/14px;15/15px;16/16px;18/18px;20/20px;22/22px;24/24px;26/26px;28/28px;36/36px;48/48px;72/72px';
    
    config.forcePasteAsPlainText = false;
    config.forceSimpleAmpersand = false;
	config.allowedContent = true;
    config.resize_dir = 'vertical';
    
    config.templates = 'default';
	
};