/***
 * Bootstrap 4 Embed Media Dialog for ckEditor 4 based on Fabian Vogelsteller's Embed Media Dialog on https://github.com/frozeman/MediaEmbed
 *
 * Wrap any embed like an <iframe> in a parent element with .embed-responsive and an aspect ratio.
 * The plugin allows you to insert multiple iframes at the same time, managed as separate elements.
 * Bootstrap classes for responsive embed are applied only to iframe, video, embed and object tags.
 * The other tags are not modified in any way.
 *
 * Plugin name:      bootstrapMediaEmbed
 * Menu button name: BootstrapMediaEmbed
 *
 * Youtube Editor Icon
 * http://paulrobertlloyd.com/
 *
 * @author Domenico Gigante [reloadlab.it]
 * @version 1.0
 ***/
(function(){
	CKEDITOR.plugins.add('bootstrapMediaEmbed',
	{
		icons: 'bootstrapmediaembed', // %REMOVE_LINE_CORE%
		hidpi: true, // %REMOVE_LINE_CORE%
		lang: 'en,it',
		init: function(editor){
			
			// Bootstrap Embedable element
			var embedable = [
				'iframe',
				'embed',
				'video',
				'object'
			];
			
			// Bootstrap embed responsive ratio
			var ratio = [
				'embed-responsive-21by9',
				'embed-responsive-16by9',
				'embed-responsive-4by3',
				'embed-responsive-1by1'
			];
			
			var me = this;
			CKEDITOR.dialog.add('BootstrapMediaEmbedDialog', function(instance){
				
				return {
					title: editor.lang.bootstrapMediaEmbed.dialogTitle,
					minWidth: 550,
					minHeight: 200,
					contents: [
						{
							id: 'iframe',
							expand: true,
							elements: [
								{
									id: 'embedArea',
									type: 'textarea',
									label: editor.lang.bootstrapMediaEmbed.dialogArea,
									'autofocus': 'autofocus',
									setup: function(element){
									},
									commit: function(element){
									}
								},
								{
									id: 'selectRatio',
									type: 'select',
									label: editor.lang.bootstrapMediaEmbed.dialogRatio,
									items: [
										[editor.lang.bootstrapMediaEmbed.selectRatio[0],'none'],
										[editor.lang.bootstrapMediaEmbed.selectRatio[1],0],
										[editor.lang.bootstrapMediaEmbed.selectRatio[2],1],
										[editor.lang.bootstrapMediaEmbed.selectRatio[3],2],
										[editor.lang.bootstrapMediaEmbed.selectRatio[4],3]
									],
									'default': 'none' 
								}
							]
						}
					],
					onOk: function(){
						
						// Embed Textarea value
						var ea = this.getContentElement('iframe', 'embedArea').getValue();
						
						// Responsive Ratio Select value
						var rr = this.getContentElement('iframe', 'selectRatio').getValue();
						
						if(ea && ea.trim() != ''){
							
							// Temporary div container
							var tmp = instance.document.createElement('div');
							
							tmp.setHtml(ea);
							
							// Elements to insert in Editor
							var ch = tmp.getChildren();

							var elem;
							while(elem = ch.getItem(0)){
								
								// Node name equal to iframe, video, embed or object
								if(embedable.indexOf(elem.getName()) != -1){
									
									// Container div
									var div = instance.document.createElement('div');
									
									// Add Bootstrap's css class for responsive design
									if(ratio[rr]){
										
										div.setAttribute('class', 'embed-responsive ' + ratio[rr]);
										
										elem.setAttribute('class', 'embed-responsive-item');
									}
									
									// Append element to container
									div.append(elem);
									
									// Insert into Editor
									instance.insertElement(div);
								} 
								// None of embedable elements
								else{
									
									// Container div
									var div = instance.document.createElement('div');
									
									// Append element to container
									div.append(elem);
									
									// Insert into Editor
									instance.insertElement(div);
								}
							}
							
							// Remove temporary div to free memory
							tmp.remove();
						}
					}
				};
			});
			
			editor.addCommand('BootstrapMediaEmbed', 
				new CKEDITOR.dialogCommand('BootstrapMediaEmbedDialog',
					{
						allowedContent: 'iframe[*],video[*],audio[*],source[*],embed[*],object[*]'
					}
				)
			);
			
			editor.ui.addButton('BootstrapMediaEmbed',
				{
					label: editor.lang.bootstrapMediaEmbed.toolbar,
					command: 'BootstrapMediaEmbed',
					toolbar: 'bootstrapMediaEmbed'
				}
			);
		}
	});
})();
