/* global tinymce */
( function() {
	tinymce.PluginManager.add( 'query_shortcode', function( editor ) {
		editor.addButton( 'query_shortcode', {
			// text: '{Query}',
			// type: 'menubutton',
			// icon: false,
			// menu: [{
			// 	text: 'Вставить шорткод [query]',
			// 	onclick: function() {
			// 		wp.mce.query_shortcode.popupwindow(editor);
			// 	}
			// }]
			text: '{Query}',
			onclick: function() {
				wp.mce.query_shortcode.popupwindow(editor);
			}
		});
	});
})();
