/* global tinymce */
( function() {
	tinymce.PluginManager.add( 'query_shortcode', function( editor ) {
		editor.addButton( 'query_shortcode', {
			text: '{Query}',
			icon: false,
			onclick: function() {
				wp.mce.query_shortcode.popupwindow(editor);
			}
		});
	});
})();
