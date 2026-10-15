/***
 * @author Domenico Gigante [reloadlab.it]
 * @version 1.0
 ***/

// set CKeditor lang
CKEDITOR.plugins.setLang('bootstrapMediaEmbed', 'en', {
    toolbar: 'Embed Media',
    dialogTitle: 'Embed Media',
    dialogRatio: 'Choose media ratio',
	selectRatio: [
		'Original size',
		'Ratio 21:9',
		'Ratio 16:9',
		'Ratio 4:3',
		'Ratio 1:1'
	],
    dialogArea: 'Paste Embed Code Here'
});