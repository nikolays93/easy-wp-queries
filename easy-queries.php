<?php

/*
Plugin Name: Simple WordPress Queries
Description:
Plugin URI: http://#
Version: 1.2.1
Author: NikolayS93
Author URI: https://vk.com/nikolays_93
Author EMAIL: nikolayS93@ya.ru
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/
namespace SQUERY;

define('SQUERY_DIR', rtrim(plugin_dir_path( __FILE__ )));
define('SQUERY_URL', rtrim(plugin_dir_url( __FILE__ )));
define('SQUERY_ASSETS', SQUERY_URL . 'assets');
define('SQUERY_NAME', plugin_basename( __FILE__ ));

if ( ! defined( 'ABSPATH' ) )
	exit;

function load_files(){
    require SQUERY_DIR . '/inc/utils.php';
    $views_dir = 'inc/views';
    SimpleQueries::add_sub_directory_file( $views_dir, 'post-queries.php', $req = true );
    SimpleQueries::add_sub_directory_file( $views_dir, 'term-queries.php', $req = true );

    $terms_dir = 'inc/widget-terms';
    SimpleQueries::add_sub_directory_file($terms_dir, 'taxanomies-widget-utils.php', $req = 1);
    SimpleQueries::add_sub_directory_file($terms_dir, 'class-widget.php', $req = 1);
    SimpleQueries::add_sub_directory_file($terms_dir, 'taxanomies-widget-front-views.php', $req = 1);

    SimpleQueries::add_sub_directory_file( 'inc', 'mce-widget.php' );
    SimpleQueries::add_sub_directory_file( 'inc', 'wp-widgets.php' );
}

function _init(){
    load_files();

	add_action( 'admin_init', array('SQUERY\MCE_Widget', 'init_mce_widget'), 20 );
    Widget_Terms\widgets_init();
}
add_action( 'plugins_loaded', 'SQUERY\_init');

register_activation_hook( __FILE__, array( 'SQUERY\SimpleQueries', 'activate' ) );
register_uninstall_hook(  __FILE__, array( 'SQUERY\SimpleQueries', 'uninstall' ) );