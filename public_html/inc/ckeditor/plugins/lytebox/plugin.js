/**
 * plugin Lytebox for CKEditor (guppy v5.x)
 *
 * Author : jchouix
 * website : http://lebrikabrak.info
 * licence : GPL
 * version : 1.2 (2013/11/17)
 *
 * This plugin is based to the original script written by Sven Zinke (www.webutler.de)
 */
 
( function()
{
    CKEDITOR.plugins.add( 'lytebox', { lang : [CKEDITOR.lang.detect(CKEDITOR.config.language)] } );
    
    CKEDITOR.on( 'dialogDefinition', function( ev )
    {
    	var dialogName = ev.data.name;
    	var dialogDefinition = ev.data.definition;
    	var editor = ev.editor;
    	
    	if ( dialogName == 'image' )
    	{
        	function getSelectedLink( editor )
        	{
        		try
        		{
        			var selection = editor.getSelection();
        			if ( selection.getType() == CKEDITOR.SELECTION_ELEMENT )
        			{
        				var selectedElement = selection.getSelectedElement();
        				if ( selectedElement.is( 'a' ) )
        					return selectedElement;
        			}
        
        			var range = selection.getRanges( true )[ 0 ];
        			range.shrink( CKEDITOR.SHRINK_TEXT );
        			var root = range.getCommonAncestor();
        			return root.getAscendant( 'a', true );
        		}
        		catch( e ) { return null; }
        	}
        	
        	function updateLinkClassField()
        	{
                var d = CKEDITOR.dialog.getCurrent();
                
                var ZoomField = d.getContentElement( 'info', 'BoxZoom' ),
                    linkClassField = d.getContentElement( 'info', 'BoxLinkClass' );
                                        
                var linkClassValue = '';
                    
                if(ZoomField.getValue() == true) {
                    linkClassValue = 'lytebox';
                    linkClassField.setValue(linkClassValue);
                }
                else {
                    linkClassField.setValue('');
                }
        	}
    
            var infoTab = dialogDefinition.getContents('info');
            
            infoTab.add(
            {
    			type : 'vbox',
    			padding : 0,
    			children :
    			[
                    {
            			type : 'hbox',
                        style : 'margin:5px 0 0 0;width:100px;vertical-align: middle;',
            			children :
                        [
                            {
                    			type : 'html',
                    			html : editor.lang.lytebox.imgzoom,
                                style : 'display:block;margin-top:3px;'
                            },
                            {
                    			type : 'checkbox',
                    			id : 'BoxZoom',
                    			label : ' ',
                    			'default' : '',
                                style : 'margin-right:5px;padding:0px',

                    			onChange : function ()
                    			{
                                    var d = CKEDITOR.dialog.getCurrent();
                                    
                                    var UrlField = d.getContentElement( 'info', 'txtUrl' ),
                                        LinkField = d.getContentElement( 'Link', 'txtUrl' ),
                                        orgLnkUrl = UrlField.getValue(),
                                        boxLnkUrl;
                                   
                                    if(orgLnkUrl != '') {
                                        var fileName = orgLnkUrl.substr( orgLnkUrl.lastIndexOf('/') + 1 );
                                        var filePath = orgLnkUrl.substring(0, orgLnkUrl.lastIndexOf('/'));
                                        boxLnkUrl = filePath + '/' + fileName;
                                    } else {
                                        alert("Vous devez sélectionner une image");
                                    }
                                    
                                    if(UrlField.getValue() == '')
                                        this.setValue( false, 'change' );
                                    
                                    if(this.getValue() == false) {
                                        LinkField.setValue( '' );
                                    }
                                    else {
                                        LinkField.setValue( boxLnkUrl );
                                    }
                                    
                                    updateLinkClassField();
                    			},
                    			onShow : function ()
                    			{
                                    var d = CKEDITOR.dialog.getCurrent();
                                    
                                    var UrlField = d.getContentElement( 'info', 'txtUrl' ),
                                        LinkField = d.getContentElement( 'Link', 'txtUrl' ),
                                        orgUrlField = UrlField.getValue();
                                        orgLinkField = LinkField.getValue();
                                    
                                    if(orgUrlField != '') {
                                        var fileName = orgUrlField.substr( orgUrlField.lastIndexOf('/') + 1 );
                                        var filePath = orgUrlField.substring(0, orgUrlField.lastIndexOf('/'));
                                        var boxLnkUrl = filePath + '/' + fileName;
                                    }
                                    if(orgLinkField == boxLnkUrl) {
                                        this.setValue( true, 'change' );
                                    }
                                    else {
                                        this.setValue( false, 'change' );
                                    }
                    			}
                            },
                            {
                    			type : 'text',
                    			id : 'BoxLinkClass',
                    			label : '',
                    			'default' : '',
                    			'style' : 'display: none',
                    			onHide : function ()
                    			{
                                    this.setValue( '' );
    							},
    							setup : function()
    							{
                                    var link = getSelectedLink( editor );
                                    if(link) {
        								this.setValue( link.getAttribute( 'class' ) || '' );
        								
                                        var d = CKEDITOR.dialog.getCurrent();
                                        
                                        var LinkField = d.getContentElement( 'Link', 'txtUrl' ),
                                            orgLinkField = LinkField.getValue(),
                                            ZoomField = d.getContentElement( 'info', 'BoxZoom' );
                        				
                                        if(orgLinkField != '' && this.getValue() != '') {
                                            var linkClassAttr = this.getValue();
                                            linkClass = linkClassAttr.substring(0, 8);
                                            
                                            if(linkClass == 'lytebox') {
                                                ZoomField.setValue( true, 'change' );
                                            }
                                        }
                                    }
                    			},

    							commit : function() //ie
    							{
    								if ( this.getValue() != '' ) {
    									this.linkElement = this.getDialog().linkElement;
                                        this.linkElement.setAttribute( 'class', this.getValue() );
    								}
    							}
                            }
                        ]
                    }
        		]
            }, 'txtAlt' );
    	}
    });
})();