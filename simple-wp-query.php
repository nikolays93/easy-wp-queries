<?php

/*
Plugin Name: Simple WordPress Queries Shortcode
Description: Add Query shortcode
Plugin URI: http://#
Version: 1.1 alpha
Author: NikolayS93
Author URI: https://vk.com/nikolays_93
Author EMAIL: nikolayS93@ya.ru
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) )
	exit;

class SimpleWPQuery_Plugin {
	const SETTINGS_NAME = 'SWPQ';

	private function __clone() {}
	private function __wakeup() {}
	private function __construct() {
		self::define_constants();
	}
	private static $instance = null;
	public static function get_instance() {
		if ( ! isset( self::$instance ) )
			self::$instance = new self;

		return self::$instance;
	}

	public function init(){
		add_action( 'admin_init', array( $this, 'init_plugin' ), 20 );
		require SQUERY_DIR . '/inc/queries.php';
	}

	private static function define_constants(){
		define('SQUERY_DIR', substr(plugin_dir_path( __FILE__ ), 0, -1) );
	}

	public function init_plugin() {
		/** MCE Editor */
		if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') )
			return;

		add_action('wp_ajax_update_squery_settings', array($this, 'ajax_update_settings'));

		// foreach ( array('post.php','post-new.php') as $hook ) {
		// 	add_action( "admin_head-$hook", array($this, 'mce_variables') );
		// }

		add_action( 'admin_head', array( $this, 'add_mce_script' ) );
		add_filter("mce_external_plugins", array($this, 'mce_plugin'));
		add_filter("mce_buttons", array($this, 'mce_button'));
	}

	function ajax_update_settings() {
		if( ! wp_verify_nonce( $_POST['security'], __CLASS__ ) )
			wp_die('Ошибка! нарушены правила безопасности');

		if( ! isset($_POST['template_dir']) )
			wp_die('Данные не переданны');

		$dir = get_template_directory() . '/' . sanitize_text_field( $_POST['template_dir'] );
		if( !is_dir($dir) )
			wp_die('Нет доступа к указаной папке в активной теме (' . $dir . ')');


		$update = update_option( self::SETTINGS_NAME, array('template_dir' => $_POST['template_dir']) );
		if( $update )
			echo "1";

		wp_die();
	}

	/** Register Shortcode Button MCE */
	function mce_plugin($plugin_array){
		$plugin_array['query_shortcode'] = plugins_url( 'js/query_button.js', __FILE__ );
		return $plugin_array;
	}
	function mce_button($buttons){
		$buttons[] = 'query_shortcode';
		return $buttons;
	}
	function add_mce_script(){
		if ( ! isset( get_current_screen()->id ) || get_current_screen()->base != 'post' )
			return;

		wp_enqueue_script( 'query-sc', plugins_url( 'js/query_shortcode.js', __FILE__ ), array( 'shortcode', 'wp-util', 'jquery' ), false, true );

		$options = get_option( self::SETTINGS_NAME );
		$post_types = get_post_types( array('public' => true) );
		$types = array();
		foreach ($post_types as $value => $text) {
			$types[] = array('value' => $value, 'text' => $text);
		}
		wp_localize_script('query-sc', 'queryMCEVar',
			array(
				'postTypes' => $types,
				'security' => wp_create_nonce( __CLASS__ ),
				'template' => isset($options['template_dir']) ? $options['template_dir'] : '',
				)
			);
	}
	static public function activate(){ add_option( self::SETTINGS_NAME, array('template_dir' => '') ); }
	static public function uninstall(){ delete_option( self::SETTINGS_NAME ); }
}

add_action( 'plugins_loaded', function(){ SimpleWPQuery_Plugin::get_instance()->init(); });
register_activation_hook( __FILE__, array( 'SimpleWPQuery_Plugin', 'activate' ) );
register_uninstall_hook( __FILE__, array( 'SimpleWPQuery_Plugin', 'uninstall' ) );
