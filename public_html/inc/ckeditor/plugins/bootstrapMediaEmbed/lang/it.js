/***
 * @author Domenico Gigante [reloadlab.it]
 * @version 1.0
 ***/

// set CKeditor lang
CKEDITOR.plugins.setLang('bootstrapMediaEmbed', 'it', {
    toolbar: 'Incorporamento codice video',
    dialogTitle: 'Incorpora codice video',
    dialogRatio: 'Scegli le proporzioni del video',
	selectRatio: [
		'Dimensioni originali',
		'Proporzione 21:9',
		'Proporzione 16:9',
		'Proporzione 4:3',
		'Proporzione 1:1'
	],
    dialogArea: 'Incolla qui il codice da incorporare'
});