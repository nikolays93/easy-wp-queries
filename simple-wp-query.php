<?php

/*
Plugin Name: Simple WP_Query (widget && shortcode extended)
Description: 
Plugin URI: http://#
Version: 1.0 alpha
Author: NikolayS93
Author URI: https://vk.com/nikolays_93
Author EMAIL: nikolayS93@ya.ru
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) )
	exit;

class SimpleWPQuery_Plugin {
	private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}

	private static $instance = null;
	public static function get_instance() {
		if ( ! isset( self::$instance ) )
			self::$instance = new self;

		return self::$instance;
	}

	public function init(){
		self::define_constants();

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

		foreach ( array('post.php','post-new.php') as $hook ) {
			add_action( "admin_head-$hook", array($this, 'mce_variables') );
		}

		add_action( 'admin_head', array( $this, 'add_mce_script' ) );
		add_filter("mce_external_plugins", array($this, 'mce_plugin'));
		add_filter("mce_buttons", array($this, 'mce_button'));
	}

	function mce_variables() {
		$ptypes = get_post_types( array('public' => true) );
		
		?>
		<script type='text/javascript'>
			var queryMCEVar = {
				'postTypes': [<?php
				$i = 0;
				foreach ($ptypes as $type) {
					if($i == 0){
						echo "'{$type}'";
						$i++;
						continue;
					}
					echo ",'{$type}'";
				}
				?>],
			};
		</script>
		<?php
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
	}
}
add_action( 'plugins_loaded', function(){ SimpleWPQuery_Plugin::get_instance()->init(); });
