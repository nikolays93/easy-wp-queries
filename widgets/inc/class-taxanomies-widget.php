<?php

namespace TaxWidget;

if ( ! defined( 'ABSPATH' ) )
  exit; // disable direct access

class TaxanomiesWidget extends \WP_Widget
{

	public function __construct(){
		$widget_options = array(
			'classname'                   => 'widget_acw_advanced_categories advanced-categories-widget',
			'description'                 => __( 'A categories widget with extended features.' ),
			'customize_selective_refresh' => true,
			);

		$control_options = array();

		parent::__construct(
			'advanced-categories-widget', // $this->id_base
			__( 'Advanced Taxanomies' ),  // $this->name
			$widget_options,              // $this->widget_options
			$control_options              // $this->control_options
		);

		$this->alt_option_name = 'widget_acw_advanced_categories';
	}


	/**
	 * Outputs the content for the current widget instance.
	 *
	 * Use 'widget_title' to filter the widget title.
	 *
	 * @access public
	 *
	 * @since 1.0
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current Categories widget instance.
	 */
	public function widget( $args, $instance ){
		if ( ! isset( $args['widget_id'] ) )
			$args['widget_id'] = $this->id;

		$instance = wp_parse_args( (array) $instance, widget_defaults() );

		// build out the instance for devs
		$instance['id_base']       = $this->id_base;
		$instance['widget_number'] = $this->number;
		$instance['widget_id']     = $this->id;
		$is_hierarchical           = $instance['hierarchy'];

		if( $is_hierarchical )
			$categories = get_widget_categories_hierarchy( $instance, $this );
		else
			$categories = get_widget_categories( $instance, $this );

		// widget title
		$_title = sizeof($categories >= 1) ? apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) : '';

		echo $args['before_widget'];

		if( $_title ) {
			echo $args['before_title'] . $_title . $args['after_title'];
		};

		do_action( 'acatw_widget_title_after', $instance );

		?>


		<div class="advanced-categories-widget advanced-categories-wrap">

			<?php

			if( ! empty( $categories ) ) :

				start_list( $instance, $categories );

					// TODO: Move to Category Walker
					foreach( $categories as $term ) {
						start_list_item( $term, $instance, $categories );
							list_item( $term, $instance, $categories );

							if($is_hierarchical && isset($term->childs) && sizeof($term->childs) >= 1 ){
								start_list( $instance, $categories );
								foreach ($term->childs as $sub_term) {
									start_list_item( $sub_term, $instance, $categories );
									list_item( $sub_term, $instance, $categories );
									end_list_item( $sub_term, $instance, $categories );
								}
								end_list( $instance, $categories );
							}

						end_list_item( $term, $instance, $categories );
					}

				end_list( $instance, $categories );

			endif;

			do_action( 'acatw_category_list_after', $instance );

			?>

		</div><!-- /.advanced-categories-wrap -->

		<?php echo $args['after_widget']; ?>

	<?php
	}

	public function update( $new_instance, $old_instance ){
		$instance = $old_instance;

		// general
		$instance['title']     = sanitize_text_field( $new_instance['title'] );
		$instance['tax']       = sanitize_key( $new_instance['tax'] );
		$instance['orderby']   = sanitize_text_field( $new_instance['orderby'] );
		$instance['order']     = sanitize_text_field( $new_instance['order'] );

		// taxonomies & filters
		if( is_array( $new_instance['tax_term'] ) ) {
			$_tax_terms = array();
			foreach( $new_instance['tax_term'] as $key => $val ) {
				if( is_array( $val ) ){
					$_val = array_map( 'absint', $val );
					$_val = array_filter( $_val );
				} else {
					$_val = absint( $val );
				}
				$_tax_terms[$key] = $_val;
			}
			$instance['tax_term'] = $_tax_terms;
		} else {
			$instance['tax_term'] = absint( $new_instance['tax_term'] );
		}

		// thumbnails
		$instance['show_thumb']   = isset( $new_instance['show_thumb'] ) ? 1 : 0 ;
		$instance['thumb_size']   = sanitize_text_field( $new_instance['thumb_size'] );

		$_thumb_size_w            = absint( $new_instance['thumb_size_w'] );
		$instance['thumb_size_w'] = ( $_thumb_size_w < 1 ) ? 55 : $_thumb_size_w ;

		$_thumb_size_h            = absint( $new_instance['thumb_size_h'] );
		$instance['thumb_size_h'] = ( $_thumb_size_h < 1 ) ? $_thumb_size_w : $_thumb_size_h ;

		// excerpts
		$instance['show_desc']    = isset( $new_instance['show_desc'] ) ? 1 : 0 ;
		$instance['desc_length']  = absint( $new_instance['desc_length'] );

		// list format
		$instance['hierarchy']    = isset( $new_instance['hierarchy'] ) ? 1 : 0 ;
		$instance['expandable']    = isset( $new_instance['expandable'] ) ? 1 : 0 ;
		$instance['list_style']   = ( '' !== $new_instance['list_style'] ) ? sanitize_key( $new_instance['list_style'] ) : 'ul ';

		// post count
		$instance['show_count']   = isset( $new_instance['show_count'] ) ? 1 : 0 ;

		// build out the instance for devs
		$instance['id_base']       = $this->id_base;
		$instance['widget_number'] = $this->number;
		$instance['widget_id']     = $this->id;

		$instance = apply_filters('acatw_update_instance', $instance, $new_instance, $old_instance, $this );

		do_action( 'acatw_update_widget', $this, $instance, $new_instance, $old_instance );

		return $instance;
	}

	public function form( $instance ){
		$instance = wp_parse_args( (array) $instance, widget_defaults() );

		include( get_plugin_sub_path('inc', 'taxanomies-widget-callback.php') );
	}

}