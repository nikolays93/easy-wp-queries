/* global tinyMCE */
(function($){
    var media = wp.media,
        shortcode_string = custom_query_settings.shortcode;

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
                            type    : 'single',
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
                        type  : 'listbox',
                        name  : 'type',
                        label : 'Тип записи',
                        values: custom_query_settings.types,
                        value : values.type
                    },
                    // {
                    //  type: 'textbox',
                    //  name: 'cat',
                    //  label: 'Categories ID (for post)',
                    //  placeholder: '6,12,18',
                    //  value: values.cat
                    // },
                    // {
                    //  type: 'textbox',
                    //  name: 'slug',
                    //  label: 'Category SLUG (for post)',
                    //  placeholder : 'articles',
                    //  value: values.slug
                    // },
                    {
                        type  : 'listbox',
                        name  : 'status',
                        label : 'Статус записей', //'Post Status',
                        values: custom_query_settings.statuses,
                        value : values.status
                    },
                    {
                        type  : 'listbox',
                        name  : 'orderby',
                        label : 'Сортировать по:', // 'Order By',
                        values: custom_query_settings.orderby,
                        value : values.orderby || 'menu_order date'
                    },
                    {
                        type  : 'listbox',
                        name  : 'order',
                        label : 'Сортировать',//'Order',
                        values: [
                            {text: 'По убыванию',//'DESC',
                            value: 'desc'},
                            {text: 'По возрастанию',//'ASC',
                            value: 'asc'},
                        ],
                        value : values.order
                    },
                    {
                        type   : 'textbox',
                        subtype: 'number',
                        name   : 'max',
                        label  : 'Ограничить количество записей',//'Max Posts',
                        tooltip : '(-1 = без ограничения)',
                        // placeholder: '5',
                        value  : values.max || -1
                    },
                    {
                        type : 'textbox',
                        name : 'parent',
                        label: 'Старшая страница (Родитель)', //'Parent (for hierarchy)',
                        value: values.parent
                    },
                    {
                        type       : 'textbox',
                        name       : 'id',
                        label      : 'ID записей через запятую',
                        placeholder: '8,10,32',
                        value      : values.id
                    },
                    {
                        type : 'textbox',
                        name : 'tax',
                        label: 'Таксаномия', //'Taxanomy',
                        value: values.tax
                    },
                    {
                        type : 'textbox',
                        name : 'terms',
                        label: 'Термины таксаномий', //'Terms of tax',
                        value: values.terms
                    },
                    {
                        type: 'textbox',
                        name: 'wrap_tag',
                        label: 'Тэг контейнера',//'Tag Wrapper',
                        placeholder : 'div',
                        value: values.wrap_tag
                    },
                    {
                        type: 'textbox',
                        name: 'container',
                        label: 'Класс контейнера', //'Container Class',
                        placeholder : 'true|false|string',
                        value: values.innercontent
                    },
                    {
                        type   : 'textbox',
                        subtype: 'number',
                        name   : 'columns',
                        label  : 'Столбцов', //'Columns',
                        value  : values.columns || 4
                    },
                    {
                        type   : 'textbox',
                        name   : 'template',
                        label  : 'Необычный дизайн', //'Custom Template',
                        value  : values.template
                    },
                ],
                onsubmit: onsubmit_callback
            } );
        }
    };

    wp.mce.views.register( shortcode_string, wp.mce.query_shortcode );

}(jQuery));
