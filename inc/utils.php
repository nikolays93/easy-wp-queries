<?php

namespace SQUERY;

if ( ! defined( 'ABSPATH' ) )
	exit;

function object_to_array_recursive($obj) {
	if(is_object($obj)) $obj = (array) $obj;
	if(is_array($obj)) {
		$new = array();
		foreach($obj as $key => $val) {
			$new[$key] = object_to_array_recursive($val);
		}
	}
	else $new = $obj;
	return $new;
}

function sanitize_select_array( $options, $sort = true ){
	$options = ( ! is_array( $options ) ) ? (array) $options : $options ;

	// Clean the values (since it can be filtered by other plugins)
	$options = array_map( 'esc_html', $options );

	// Flip to clean the keys (used as <option> values in <select> field on form)
	$options = array_flip( $options );
	$options = array_map( 'sanitize_key', $options );

	// Flip back
	$options = array_flip( $options );

	if( $sort ) {
		asort( $options );
	};

	return $options;
}

function get_terms( $instance, $widget ){

		// if( empty( $instance['tax_term'] ) ) {
		// 	return array();
		// }

		$_include_taxonomies = array();
		//$_include_ids = array();
		$_exclude_ids = array();

		// foreach( $instance['tax_term'] as $taxonomy => $term_ids ) {
		// 	$_include_taxonomies[] = $taxonomy;
		// 	array_walk_recursive( $term_ids, function( $value, $key ) use ( &$_include_ids ) {
		// 		$_include_ids[$key] = $value;
		// 	} );
		// }

		$r = array(
			'taxonomy'   => $instance['tax'],
			'orderby'    => $instance['orderby'],
			'order'      => $instance['order'] ? $instance['order'] : '',
			'hide_empty' => 0,
		);

		$categories = get_terms( $r );

		if ( is_wp_error( $categories ) ) {
			$categories = array();
		} else {
			$categories = (array) $categories;
		}

		return $categories;
}

function get_terms_hierarchy( $instance, $widget ){

	// if( empty( $instance['tax_term'] ) ) {
	// 	return array();
	// }

	$_exclude_taxonomies = array();
	$_include_taxonomies = array();
	$_include_ids = array();

	// foreach( $instance['tax_term'] as $taxonomy => $term_ids ) {
	// 	$_include_taxonomies[] = $taxonomy;
	// 	array_walk_recursive( $term_ids, function( $value, $key ) use ( &$_include_ids ) {
	// 		$_include_ids[$key] = $value;
	// 	} );
	// }

	$r = array(
		'taxonomy'   => $instance['tax'],
		'orderby'    => $instance['orderby'],
		'order'      => $instance['order'],
		'hide_empty' => 0,
		'include'    => $_include_ids,
		'parent'     => 0,
	);

	$categories = \get_terms( $r );

	if ( is_wp_error( $categories ) ) {
		$categories = array();
	} else {
		foreach ($categories as &$category) {

			$r['parent'] = $category->term_id;
			$sub_categories = \get_terms( $r );

			if ( is_wp_error( $sub_categories ) ){
				$category->childs = array();
			} else {
				$category->childs = (array) $sub_categories;
			}

		}
	}

	return $categories;
}

/**
 * Utils Static class
 */
class SimpleQueries {
	const SETTINGS_NAME = 'SWPQ';

	protected static $notices = array();

	private function __construct(){}

	static function admin_notice_template(){
		$class = 'is-dismissible notice notice-' . self::$notices['status'];

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( __(self::$notices['msg']) ) );
	}

	/**
	 * Add WP Admin notice
	 *
	 * @param string $msg    notice content
	 * @param string $status info | warning | error | success
	 */
	public static function add_notice( $msg, $status = 'warning'){
		if( !is_string($msg) || ! $msg )
			return;

		self::$notices['msg'] = $msg;
		self::$notices['status'] = $status;
		add_action( 'admin_notices', array(__CLASS__, 'admin_notice_template') );
	}

	public static function add_sub_directory_file( $subdir, $filename='', $require=false ){
		$path = SQUERY_DIR . $subdir . '/' . $filename;
		if( is_readable($path) ){
			if( $require )
				require $path;
			else
				include $path;
		}
		else {
			$message = 'Файл ';
			$message.= $filename ? $filename : $subdir;
			$message.= ' не найден.';
			wp_die( $path );
			self::add_notice( $message );
		}
	}

	public static function activate(){ add_option( self::SETTINGS_NAME, array('template_dir' => '') ); }
	public static function uninstall(){ delete_option( self::SETTINGS_NAME ); }
}
