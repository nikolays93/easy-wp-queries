<?php
namespace SQUERY\Widget_Terms;

if ( ! defined( 'ABSPATH' ) )
  exit; // disable direct access

function widget_defaults(){
	$_defaults = array(
		'title'          => __( 'Categories' ),
		'tax'            => 'category',
		'orderby'        => 'name',
		'order'          => 'asc',
		'tax_term'       => '',
		'show_thumb'     => 0,
		'thumb_size'     => 0,
		'thumb_size_w'   => 55,
		'thumb_size_h'   => 55,
		'show_desc'      => 1,
		'desc_length'    => 15,
		'hierarchy'      => 1,
		'expandable'     => 0,
		'list_style'     => 'ul',
		'show_count'     => 0,
		);

	$defaults = apply_filters( 'acatw_instance_defaults', $_defaults );

	return $defaults;
}

function fieldset_header_html( $fieldset = 'general', $title = 'General Settings' ){
	ob_start();
	?>

	<div class="widgin-section-top" data-fieldset="<?php echo $fieldset; ?>">
		<div class="widgin-top-action">
			<a class="widgin-action-indicator hide-if-no-js" data-fieldset="<?php echo $fieldset; ?>" href="#"></a>
		</div>
		<div class="widgin-section-title">
			<h4 class="widgin-section-heading" data-fieldset="<?php echo $fieldset; ?>">
				<?php printf( __( '%s', 'advanced-categories-widget' ), $title ); ?>
			</h4>
		</div>
	</div>

	<?php
	$field = ob_get_clean();

	return $field;
}

function is_woocommerce_thumb_meta_field( $field, $term, $instance ){
	if( function_exists('is_woocommerce') && $instance['tax'] == 'product_cat' || $instance['tax'] == 'product_tag'){
		return get_term_meta( $term->term_id, 'thumbnail_id', true );
	}
}
function _excerpt_sample(){
	return __( 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sequi consequatur quibusdam deserunt sapiente eum repellat amet vel et officiis? Laboriosam optio debitis, laborum provident accusamus est harum dignissimos quos officia? Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sequi consequatur quibusdam deserunt sapiente eum repellat amet vel et officiis? Laboriosam optio debitis, laborum provident accusamus est harum dignissimos quos officia? Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sequi consequatur quibusdam deserunt sapiente eum repellat amet vel et officiis? Laboriosam optio debitis, laborum provident accusamus est harum dignissimos quos officia?');
}

function get_allowed_taxonomies(){
	$_ptaxes = array();

	$_ptaxes['category'] = 'Category';

	$taxes = apply_filters( 'acatw_allowed_taxonomies', $_ptaxes );
	$taxes = sanitize_select_array( $taxes );

	return $taxes;
}

function get_allowed_image_sizes( $fields = 'name' ){
	global $_wp_additional_image_sizes;
	$wp_defaults = array( 'thumbnail', 'medium', 'medium_large', 'large' );

	$_sizes = get_intermediate_image_sizes();

	if( count( $_sizes ) ) {
		sort( $_sizes );
		$_sizes = array_combine( $_sizes, $_sizes );
	}

	$_sizes = apply_filters( 'acatw_allowed_image_sizes', $_sizes );
	$sizes = sanitize_select_array( $_sizes );

	if( count( $sizes )&& 'all' === $fields ) {

		$image_sizes = array();
		natsort( $sizes );

		foreach ( $sizes as $_size ) {
			if ( in_array( $_size, $wp_defaults ) ) {
				$image_sizes[$_size]['name']   = $_size;
				$image_sizes[$_size]['width']  = absint( get_option( "{$_size}_size_w" ) );
				$image_sizes[$_size]['height'] = absint(  get_option( "{$_size}_size_h" ) );
				$image_sizes[$_size]['crop']   = (bool) get_option( "{$_size}_crop" );
			} else if( isset( $_wp_additional_image_sizes[ $_size ] )  ) {
				$image_sizes[$_size]['name']   = $_size;
				$image_sizes[$_size]['width']  = absint( $_wp_additional_image_sizes[ $_size ]['width'] );
				$image_sizes[$_size]['height'] = absint( $_wp_additional_image_sizes[ $_size ]['height'] );
				$image_sizes[$_size]['crop']   = (bool) $_wp_additional_image_sizes[ $_size ]['crop'];
			}
		}

		$sizes = $image_sizes;

	};

	return $sizes;
}
function img_sizes_select_html( $instance, $widget ){
	$sizes = get_allowed_image_sizes( $fields = 'all' );

	if( count( $sizes ) ) : ?>

	<select name="<?php echo $widget->get_field_name('thumb_size'); ?>" id="<?php echo $widget->get_field_id('thumb_size'); ?>" class="widefat">
		<option value></option>
		<?php foreach( $sizes as $name => $size  ) {
			$width = absint( $size['width'] );
			$height = absint( $size['height'] );
			$dimensions = ' (' . $width . ' x ' . $height . ')';
			printf( '<option data-width="%1$s" data-height="%2$s" value="%3$s" %4$s>%5$s%6$s</option>' . "\n",
				$width,
				$height,
				esc_attr( $name ),
				selected( $instance['thumb_size'] , $name, false ),
				esc_html( $size['name'] ),
				$dimensions
				);
			} ?>
		</select>

	<?php endif;
}
function get_image_size( $size = 'thumbnail', $fields = 'all' ){
	$sizes = get_allowed_image_sizes( $_fields = 'all' );

	if( count( $sizes ) && isset( $sizes[$size] ) ){
		if( 'all' === $fields ) {
			return $sizes[$size];
		} else {
			return $sizes[$size]['name'];
		}
	}

	return false;
}

function _taxanomies_list(){
	$all_taxes = array();
	$product_attrs = array();
	if( function_exists('wc_get_attribute_taxonomies') ){
		foreach (wc_get_attribute_taxonomies() as $key => $value) {
			$product_attrs[] = 'pa_' . $value->attribute_name;
		}
	}

	foreach (get_taxonomies() as $key => $value) {
		if( !in_array($key, $product_attrs) )
			$all_taxes[$key] = __(ucfirst($value), 'advanced-categories-widget');
	}

	return \SQUERY\sanitize_select_array( $all_taxes );
}

function _orderby_term_list(){
	$_orderby = array(
		''           => __( 'Term ID', 'advanced-categories-widget'),
		'name'       => __( 'Category Name', 'advanced-categories-widget' ),
		'count'      => __( 'Post Count', 'advanced-categories-widget' ),
		);

	$params = \SQUERY\sanitize_select_array( $_orderby, false );

	return $params;
}

/**
 * @todo : terms filter
 */
function build_field_tax_term( $instance, $widget ){
	ob_start();
	$taxonomies = get_allowed_taxonomies();

	if( count( $taxonomies ) ) :
		foreach ( $taxonomies as $name => $label ) {
			build_term_select( $name, $label, $instance, $widget );
		}
	endif;

	?>

	<?php
	$field = ob_get_clean();

	return $field;
}
function build_term_select( $taxonomy, $label, $instance, $widget ){
	$args = apply_filters( 'acatw_build_term_select_args', array( 'hide_empty' => 0, 'number' => 99 ) );
	$args['fields'] = 'all'; // don't allow override
	$args['taxonomy'] = $taxonomy; // don't allow override
	$_terms = get_terms( $taxonomy, $args );

	if( empty( $_terms ) || is_wp_error( $_terms ) ) {
		return;
	}
	?>

	<?php printf( '<p>%s:</p>', sprintf( __( '%s', 'advanced-categories-widget' ), $label ) ); ?>

	<div class="widgin-multi-check">
		<?php foreach( $_terms as $_term ) : ?>
			<?php
			$checked = (  ! empty( $instance['tax_term'][$_term->taxonomy][$_term->term_id] )) ? 'checked="checked"' : '' ;

			printf( '<input id="%1$s" name="%2$s" value="%3$s" type="checkbox" %4$s/><label for="%1$s">%5$s (%6$s)</label><br />',
				$widget->get_field_id( 'tax_term-' . $taxonomy . '-' . $_term->term_id ),
				$widget->get_field_name( 'tax_term' ) . '['.$taxonomy.']['.$_term->term_id.']',
				$_term->term_id,
				$checked,
				sprintf( __( '%s', 'advanced-categories-widget' ), $_term->name ),
				$_term->count
			);
			?>
		<?php endforeach; ?>
	</div>
	<?php
}
