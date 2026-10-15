/*
 * Plugin Citation for CKeditor 4.6.x
 * Version : 1.0 (2017/03/22)
 * Author  : Jérôme Croux (Jchouix) adapté par Lud Bienaimé (Saxbar)
 * Licence : CeCILL
 */

CKEDITOR.dialog.add( 'citationDialog', function( editor ) {
    function getSelectedHtml(oSelection) {
        var selection = oSelection, retval = '';
        
        if ( selection ) {
            var bookmarks = selection.createBookmarks(),
                range = selection.getRanges()[ 0 ],
                fragment = range.clone().cloneContents();
    
            selection.selectBookmarks( bookmarks );
    
            var childList = fragment.getChildren(),
                childCount = childList.count();
                
            for ( var i = 0; i < childCount; i++ ) {
                var child = childList.getItem( i );
                retval += ( child.getOuterHtml ? child.getOuterHtml() : child.getText() );
            }
        }
        
        return retval;
    };
    
    return {
        title    : editor.lang.citation.dialog_title,
        minWidth : 200,
        minHeight: 50,
        contents : [ {
            id   : 'tab-basic',
            elements: [
                {
                    type : 'text',
                    id   : 'author',
                    label: editor.lang.citation.label_author,
                    setup : function( element ) {
                        if (element.getChild( 0 ).getName() == 'blockquote') {
                            this.setValue( element.getChild( 0 ).getText() );
                        } else {
                            this.setValue('');
                        }
                    },
                    commit : function( data ) {
                        data.author = this.getValue();
                    }
                },
                {
                    type    : 'textarea',
                    id      : 'content',
                    label   : editor.lang.citation.label_textarea,
                    validate: CKEDITOR.dialog.validate.notEmpty( editor.lang.citation.error_textarea ),
                    style   : 'display:none;',
                    required: true,
                    setup : function( element ) {
                        var content = element.getChild( 0 );
                        if ( content ) {
                            this.setValue( content.getHtml() );
                        } else {
                            this.setValue('');
                        }
                    },
                    commit : function( data ) {
                        data.content = this.getValue();
                    }
                }
            ] }
        ],
        
        onShow : function() {
            var sel = editor.getSelection(), element = sel.getStartElement();
            
            if ( element ) {
                element = element.getAscendant( 'blockquote', true );
            }
            
            if ( !element || element.getName() != 'blockquote' ) {
                var content = editor.document.createElement( 'div' );
                content.setHtml( getSelectedHtml( sel ) );

                element = editor.document.createElement( 'blockquote' );
                element.append( content );
                
                this.insertMode = true;
            }
            else {
                this.insertMode = false;
            }	
            
            this.element = element;				
            this.setupContent( this.element );
        },
        
        onOk: function() {
            var dialog = this, data = {};

            var citation = editor.document.createElement( 'blockquote' );
            citation.setAttribute( 'class', 'citation' );
            citation.setStyle( 'margin', '2px 6px' );
            citation.setStyle( 'padding', '0' );
            citation.setStyle( 'border', '1px dashed #000' );
            
            var author = editor.document.createElement( 'p' );
            author.setStyle( 'background-color', '#F3F3F3' );
            author.setStyle( 'margin', '0' );
            author.setStyle( 'padding', '6px' );
            author.setStyle( 'font-weight', 'bold' );
            
            var content = editor.document.createElement( 'div' );
            content.setStyle( 'margin', '4px 6px' );
            content.setStyle( 'font-style', 'italic' );

            this.commitContent( data );
            
            author.setHtml(  editor.lang.citation.label_textarea + ( data.author ? ' \253 ' + data.author + ' \273' : '' ) );
            content.setHtml( data.content );

            if ( this.insertMode && data.content != '' ) {
                author.appendTo( citation );
                content.appendTo( citation );
                
                editor.insertElement( citation );
            }
        }
    };
});