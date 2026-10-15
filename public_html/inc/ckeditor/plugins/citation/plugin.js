/*
 * Plugin Citation for CKeditor 4.6.x
 * Version : 1.0 (2017/03/22)
 * Author  : Jérôme Croux (Jchouix) adapté par Lud Bienaimé (Saxbar)
 * Licence : CeCILL
 */

CKEDITOR.plugins.add( 'citation', {
    icons: 'citation',
    lang : 'da,de,en,eo,es,fr,id,it,nl', // %REMOVE_LINE_CORE%
    init : function( editor ) {
        editor.addCommand( 'citation', new CKEDITOR.dialogCommand( 'citationDialog' ) );
        editor.ui.addButton( 'Citation', {
            label  : editor.lang.citation.label_button,
            command: 'citation',
            toolbar: 'insert'
        });
        
        CKEDITOR.dialog.add( 'citationDialog', this.path + 'dialogs/citation.js' );
    }
});