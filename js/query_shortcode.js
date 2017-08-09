/* global tinyMCE */
(function($){
	var media = wp.media, shortcode_string = 'query';
	wp.mce = wp.mce || {};
	wp.mce.query_shortcode = {
		shortcode_data: {},
		// template: media.template( 'editor-boutique-banner' ),
		getContent: function() {
		// Контент внутри объекта
			// var options = this.shortcode.attrs.named;
			// options.innercontent = this.shortcode.content;
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
					// Insert content when the window form is submitted
					var args = {
							tag     : shortcode_string,
							attrs : {
								type : e.data.type,
								columns : e.data.columns,
								max : e.data.max
							}
						};

					// defaults
					if(e.data.id) args.attrs.id = e.data.id;
					// if(e.data.max && e.data.max != "-1") args.attrs.max = e.data.max;
					if(e.data.status && e.data.status != 'public') args.attrs.status = e.data.status;
					if(e.data.order && e.data.order != 'desc') args.attrs.order = e.data.order;
					if(e.data.orderby && e.data.orderby != 'menu_order date') args.attrs.orderby = e.data.orderby;
					// if(e.data.cat) args.attrs.cat = e.data.cat;
					// if(e.data.slug) args.attrs.slug = e.data.slug;
					if(e.data.parent) args.attrs.parent = e.data.parent;
					if(e.data.wrap_tag) args.attrs.wrap_tag = e.data.wrap_tag;
					if(e.data.tax) args.attrs.tax = e.data.tax;
					if(e.data.terms) args.attrs.terms = e.data.terms;

					if(e.data.template) args.attrs.template = e.data.template;

					editor.insertContent( wp.shortcode.string( args ) );
				};
			}

			editor.windowManager.open( {
				title: 'Simple WordPress Query',
				body: [
					{
						type       : 'textbox',
						name       : 'id',
						label      : 'Posts ID',
						placeholder: '8,10,32',
						value      : values.id
					},
					{
						type   : 'textbox',
						subtype: 'number',
						name   : 'max',
						label  : 'Max Posts',
						// placeholder: '5',
						value  : values.max || -1
					},
					{
						type  : 'listbox',
						name  : 'type',
						label : 'Post Type',
						values: queryMCEVar.postTypes,
						value : values.type
					},
					// {
					// 	type: 'textbox',
					// 	name: 'cat',
					// 	label: 'Categories ID (for post)',
					// 	placeholder: '6,12,18',
					// 	value: values.cat
					// },
					// {
					// 	type: 'textbox',
					// 	name: 'slug',
					// 	label: 'Category SLUG (for post)',
					// 	placeholder : 'articles',
					// 	value: values.slug
					// },
					{
						type : 'textbox',
						name : 'parent',
						label: 'Parent (for hierarchy)',
						value: values.parent
					},
					{
						type  : 'listbox',
						name  : 'status',
						label : 'Post Status',
						values: [
							{text: 'Public', value: 'public'},
							{text: 'Future', value: 'future'},
							{text: 'Any', value: 'any'}
						],
						value : values.status
					},
					{
						type  : 'listbox',
						name  : 'orderby',
						label : 'Order By',
						values: [
							{text: 'None', value: 'none'},
							{text: 'ID', value: 'ID'},
							{text: 'Author', value: 'author'},
							{text: 'Title', value: 'title'},
							{text: 'Name', value: 'name'},
							{text: 'Type', value: 'type'},
							{text: 'Date', value: 'date'},
							{text: 'Modified', value: 'modified'},
							{text: 'Parent', value: 'parent'},
							{text: 'Rand', value: 'rand'},
							{text: 'Comment_count', value: 'comment_count'},
							{text: 'Relevance', value: 'relevance'},
							{text: 'Menu', value: 'menu_order date'},
							// {text: 'meta_value', value: 'meta_value'},
							// {text: 'meta_value_num', value: 'meta_value_num'},
							// {text: 'post__in', value: 'post__in'},
							// {text: 'post_name__in', value: 'post_name__in'},
							// {text: 'post_parent__in', value: 'post_parent__in'}
						],
						value : values.orderby || 'menu_order date'
					},
					{
						type  : 'listbox',
						name  : 'order',
						label : 'Order',
						values: [
							{text: 'DESC', value: 'desc'},
							{text: 'ASC', value: 'asc'},
						],
						value : values.order
					},
					{
						type : 'textbox',
						name : 'tax',
						label: 'Taxanomy',
						value: values.tax
					},
					{
						type : 'textbox',
						name : 'terms',
						label: 'Terms of tax',
						value: values.terms
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
						type   : 'textbox',
						subtype: 'number',
						name   : 'columns',
						label  : 'Columns',
						value  : values.columns || 4
					},
					{
						type   : 'textbox',
						name   : 'template',
						label  : 'Custom Template',
						value  : values.template
					},
				],
				onsubmit: onsubmit_callback
			} );
		},
		popupsettings: function(editor, values, onsubmit_callback){
			if(typeof onsubmit_callback !== 'function'){
				onsubmit_settings = function( e ) {
					if( e.data.template_dir == queryMCEVar.template )
						return;

					wp.ajax.send( "update_squery_settings", {
						success: function(data){
							queryMCEVar.template = e.data.template_dir;
							editor.windowManager.alert('Настройки успешно сохранены. Теперь шаблон будет доступен по адресу: \r\n [active_theme]/' + queryMCEVar.template + '/content-[post_type|custom_tpl]-query.php');
						},
						error: function(data){
							editor.windowManager.alert( data || 'Unregistred error!' );
						},
						data: {
							security: queryMCEVar.security,
							template_dir: e.data.template_dir
						}
					});
				};
			}

			editor.windowManager.open( {
				title: 'Simple WordPress Query',
				body: [{
					type   : 'textbox',
					name   : 'template_dir',
					label  : 'Templates DIR',
					placeholder: 'template-parts',
					value: queryMCEVar.template
				}],
				onsubmit: onsubmit_settings
			} );
		}
	};
	wp.mce.views.register( shortcode_string, wp.mce.query_shortcode );
}(jQuery));
