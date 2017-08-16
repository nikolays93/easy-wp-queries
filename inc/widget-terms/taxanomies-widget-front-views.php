<?php
namespace SQUERY\Widget_Terms;

function get_item_id( $term = 0, $instance = array() ){
	if( ! $term instanceof WP_Term )
		return '';

	return sanitize_html_class( $instance['widget_id'] . '-term-' . $term->term_id );
}

function check_active_class( $term = 0, $instance = array() ){
	if( ! $term instanceof WP_Term )
		return '';

	$_current = get_queried_object();
	$active_terms = array();

	if( is_single() ){
		$terms = wp_get_object_terms( (int) $_current->ID, $term->taxonomy );
		if( ! is_wp_error($terms) ){
			foreach ($terms as $_term) {
				$active_terms[] = (int) $_term->term_id;
			}
		}
	}
	else {
		if( $current_id = $_current->term_id )
			$active_terms[] = absint($current_id);

		if( $parent_id = $_current->parent )
			$active_terms[] = absint($parent_id);
	}

	if( in_array($term->term_id, $active_terms) )
		return true;

	return false;
}

function get_item_class( $term = 0, $instance = array() ){
	if( ! $term instanceof WP_Term )
		return '';

	$_classes = array();
	$_classes[] = 'tax-' . $term->taxonomy . '-item';
	$_classes[] = 'tax-' . $term->taxonomy . '-item-' . $term->term_id;

	if ( $term->parent > 0 ) {
		$_classes[] = 'child-term';
		$_classes[] = 'parent-' . $term->parent;
	}

	if( get_term_children($term->term_id, $term->taxonomy) )
		$_classes[] = 'has-child';

	if( check_active_class( $term, $instance ) )
		$_classes[] = 'active';

	$classes = array_map( 'sanitize_html_class', $classes );

	return implode( ' ', $classes );
}

function get_term_thumbnail( $term = 0, $instance = array() ){
	if( ! $term instanceof WP_Term )
		return '';

	add_filter('acatw_thumb_meta_field', 'is_woocommerce_thumb_meta_field', 10, 3);

		// You may use advanced_custom_fields for ex.
	$_thumbnail_id = apply_filters( 'acatw_thumb_meta_field', get_term_meta( $term->term_id, '_thumbnail_id', true ), $term, $instance );
	$_thumbnail_id = absint( $_thumbnail_id );

		// no thumbnail
		// @todo placeholder?
	if( ! $_thumbnail_id ) {
		return '';
	}

	$_classes = array();
	$_classes[] = 'acatw-term-image';
	$_classes[] = 'acatw-alignleft';

		// was registered size selected?
	$_size = $instance['thumb_size'];

		// custom size entered
	if( empty( $_size ) ){
		$_w = absint( $instance['thumb_size_w'] );
		$_h = absint( $instance['thumb_size_h'] );
		$_size = "acatw-thumbnail-{$_w}-{$_h}";
	}

		// check if the size is registered
	$_size_exists = get_image_size( $_size );

	if( $_size_exists ){
		$_get_size = $_size;
		$_w = absint( $_size_exists['width'] );
		$_h = absint( $_size_exists['height'] );
		$_classes[] = "size-{$_size}";
	} else {
		$_get_size = array( $_w, $_h);
	}

	$classes = apply_filters( 'acatw_term_thumb_class', $_classes, $term, $instance );
	$classes = ( ! is_array( $classes ) ) ? (array) $classes : $classes ;
	$classes = array_map( 'sanitize_html_class', $classes );

	$class_str = implode( ' ', $classes );

	$_thumb = wp_get_attachment_image(
		$_thumbnail_id,
		$_get_size,
		false,
		array(
			'class' => $class_str,
			'alt' => $term->name,
			)
		);

	$thumb = apply_filters( 'acatw_term_thumbnail', $_thumb, $term, $instance );

	return $thumb;
}

function get_term_excerpt( $term = 0, $instance = array(), $trim = 'words' ){
	if( ! $term instanceof WP_Term )
		return '';

	$_text = $term->description;

	if( '' === $_text ) {
		return '';
	}

	$_text = strip_shortcodes( $_text );
	$_text = str_replace(']]>', ']]&gt;', $_text);

	$text = apply_filters( 'acatw_term_excerpt', $_text, $term, $instance );

	$_length = ( ! empty( $instance['desc_length'] ) ) ? absint( $instance['desc_length'] ) : 55 ;
	$length = apply_filters( 'acatw_term_excerpt_length', $_length );

	$_aposiopesis = ( ! empty( $instance['excerpt_more'] ) ) ? $instance['excerpt_more'] : '&hellip;' ;
	$aposiopesis = apply_filters( 'acatw_term_excerpt_more', $_aposiopesis );

	if( 'chars' === $trim ){
		$text = wp_html_excerpt( $text, $length, $aposiopesis );
	} else {
		$text = wp_trim_words( $text, $length, $aposiopesis );
	}

	return $text;
}

function item_thumb_div( $term = 0, $instance = array(), $echo = true ){
	if( ! $term instanceof WP_Term )
		return '';

	$_html = '';
	$_thumb = get_term_thumbnail( $term, $instance );

	$_classes = array();
	$_classes[] = 'acatw-term-thumbnail';

	$classes = apply_filters( 'acatw_thumbnail_div_class', $_classes, $instance, $term );
	$classes = ( ! is_array( $classes ) ) ? (array) $classes : $classes ;
	$classes = array_map( 'sanitize_html_class', $classes );

	$class_str = implode( ' ', $classes );

	if( '' !== $_thumb ) {

		$_html .= sprintf('<span class="%1$s">%2$s</span>',
			$class_str,
			sprintf('<a href="%s">%s</a>',
				esc_url( get_term_link( $term ) ),
				$_thumb
			)
		);

	};

	$html = apply_filters( 'acatw_item_thumbnail_div', $_html, $term, $instance );

	if( $echo ) {
		echo $html;
	} else {
		return $html;
	}
}

function term_post_count( $term = 0, $instance = array(), $echo = true ){
	if( ! $term instanceof WP_Term )
		return '';

	/* translators: 1: Number of posts 2: post type name */
	$_count_text = sprintf( __( '%1$d %2$s', 'advanced-categories-widget'),
		number_format_i18n( $term->count ),
		( $term->count > 1 ) ? 'Posts' : 'Post'
	);

	$html = sprintf( '<span class="acatw-post-count term-post-count"><a href="%1$s" rel="bookmark">%2$s</a></span>',
		esc_url( get_term_link( $term ) ),
		$_count_text
	);

	if( ! $echo )
		return $html;

	echo $html;
}

/******************************** Public Views ********************************/
function start_list( $instance, $categories, $echo = true ){
	$tag = 'ul';

	if( in_array($instance['list_style'], array('div', 'ol')) )
		$tag = $instance['list_style'];

	$html = sprintf( '<%1$s class="%2$s">', $tag, 'taxanomies-widget term-list' );

	if( !$echo )
		return $html;

	echo $html;
}

function start_list_item( $term, $instance, $categories, $echo = true ){
	if( ! $term instanceof WP_Term )
		return '';

	$html = sprintf( '<%1$s id="%2$s" class="%3$s">',
		( 'div' === $instance['list_style'] ) ? 'div' : 'li',
		get_item_id( $term, $instance ),
		get_item_class( $term, $instance ) );

	if( !$echo )
		return $html;

	echo $html;
}

function list_item( $term, $instance, $categories, $echo = true ){
	$item_desc  = get_term_excerpt( $term, $instance );
	$item_id    = get_item_id( $term, $instance );

	$thumb_div =  !empty( $instance['show_thumb'] ) ? item_thumb_div($term, $instance, false) : '';
	$post_count = !empty( $instance['show_count'] ) ? term_post_count($term, $instance, false) : '';

	ob_start();
	?>
		<div id="term-<?php echo $item_id ;?>">

				<div class="term-header acatw-term-header">
					<?php
					echo $thumb_div;

					printf( '<%1$s class="term-title acatw-term-title"><a href="%2$s" rel="bookmark">%3$s</a></%1$s>',
						($term->parent) ? 'h4' : 'h3',
						esc_url( get_term_link( $term ) ),
						sprintf( __( '%s', 'advanced-categories-widget'), $term->name )
					);

					echo $post_count;
					?>
				</div><!-- /.term-header -->

				<?php
				if( $instance['show_desc'] ) {
					echo '<span class="term-summary acatw-term-summary">';
					echo $item_desc;
					echo '</span><!-- /.term-summary -->';
				}
				?>

		</div><!-- #term-## -->
	<?php
	$html = ob_get_clean();

	if( ! $echo )
		return $html;

	echo $html;
}

function end_list_item( $term, $instance, $categories, $echo = true ){
	$html = sprintf( '</%1$s>', ( 'div' === $instance['list_style'] ) ? 'div' : 'li' );

	if( ! $echo )
		return $html;

	echo $html;
}

function end_list( $instance, $categories, $echo = true ){
	$html = "</ul>\n";

	if( isset($instance['list_style']) && in_array($instance['list_style'], array('div', 'ol')) )
		$html = "</{$instance['list_style']}>\n";

	if( !$echo )
		return $html;

	echo $html;
}