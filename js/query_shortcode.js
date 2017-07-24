/* global tinyMCE */
(function($){
	var media = wp.media, shortcode_string = 'query';
	wp.mce = wp.mce || {};
	wp.mce.query_shortcode = {
		shortcode_data: {},
		// template: media.template( 'editor-boutique-banner' ),
		getContent: function() {
		// Контент внутри объекта
			var options = this.shortcode.attrs.named;
			options.innercontent = this.shortcode.content;
			// return this.template(options);
			return '<p style="text-align: center;">{SimpleWPQuery}</p>';
		},
		edit: function( data ) {
			var shortcode_data = wp.shortcode.next(shortcode_string, data);
			var values = shortcode_data.shortcode.attrs.named;
			values.innercontent = shortcode_data.shortcode.content;
			wp.mce.query_shortcode.popupwindow(tinyMCE.activeEditor, values);
		},
		popupwindow: function(editor, values, onsubmit_callback){
			values = values || [];
			if(typeof onsubmit_callback !== 'function'){
				onsubmit_callback = function( e ) {
					// Insert content when the window form is submitted (this also replaces during edit, handy!)
					var args = {
							tag     : shortcode_string,
							// type    : e.data.innercontent.length ? 'closed' : 'single',
							// content : e.data.innercontent,
							attrs : {
								max     : e.data.max,
								type : e.data.type,
							}
						};
					if(e.data.id)
						args.attrs.id = e.data.id;
					if(e.data.status && e.data.status != 'public')
						args.attrs.status = e.data.status;
					if(e.data.order && e.data.order != 'desc')
						args.attrs.order = e.data.order;
					if(e.data.cat)
						args.attrs.cat = e.data.cat;
					if(e.data.slug)
						args.attrs.slug = e.data.slug;
					if(e.data.parent)
						args.attrs.parent = e.data.parent;
					if(e.data.wrap_tag)
						args.attrs.wrap_tag = e.data.wrap_tag;
					if(e.data.container)
						args.attrs.container = e.data.container;
					if(e.data.tax)
						args.attrs.tax = e.data.tax;
					if(e.data.terms)
						args.attrs.terms = e.data.terms;

					editor.insertContent( wp.shortcode.string( args ) );
				};
			}
			editor.windowManager.open( {
				title: 'Simple WordPress Query',
				body: [
					{
						type: 'textbox',
						name: 'id',
						label: 'Posts ID',
						placeholder: '8,10,32',
						value: values.id
					},
					{
						type: 'textbox',
						subtype: 'number',
						name: 'max',
						label: 'Max Posts',
						placeholder: '5',
						value: values.max
					},
					{
						type: 'listbox',
						name: 'type',
						label: 'Post Type',
						values: [
							{text: 'Post', value: 'post'},
							{text: 'Page', value: 'page'},
							{text: 'Product', value: 'product'}
						],
						value: values.type
					},
					{
						type: 'textbox',
						name: 'cat',
						label: 'Categories ID (for post)',
						placeholder: '6,12,18',
						value: values.cat
					},
					{
						type: 'textbox',
						name: 'slug',
						label: 'Category SLUG (for post)',
						placeholder : 'articles',
						value: values.slug
					},
					{
						type: 'textbox',
						name: 'parent',
						label: 'Parent (for hierarchy)',
						value: values.parent
					},
					{
						type: 'listbox',
						name: 'status',
						label: 'Post Status',
						values: [
							{text: 'Public', value: 'public'},
							{text: 'Future', value: 'future'},
							{text: 'Any', value: 'any'}
						],
						value: values.status
					},
					{
						type: 'listbox',
						name: 'order',
						label: 'Order',
						values: [
							{text: 'DESC', value: 'desc'}
							{text: 'ASC', value: 'asc'},
						],
						value: values.order
					},
					{
						type: 'textbox',
						name: 'wrap_tag',
						label: 'Tag Wrapper',
						placeholder : 'div',
						value: values.wrap_tag
					},
					{
						type: 'textbox',
						name: 'container',
						label: 'Container Class',
						placeholder : 'true|false|string',
						value: values.innercontent
					},
					{
						type: 'textbox',
						name: 'tax',
						label: 'Taxanomy',
						value: values.tax
					},
					{
						type: 'textbox',
						name: 'terms',
						label: 'Terms of tax',
						value: values.terms
					},
					// columns
					// template
					
				],
				onsubmit: onsubmit_callback
			} );
		}
	};
	wp.mce.views.register( shortcode_string, wp.mce.query_shortcode );
}(jQuery));
