<?php
/*
Plugin Name: Taxanomies Widget
Plugin URI:
Description: Add Query Taxanomy Terms Widget.
Version: 1.0.0
Author: NikolayS93
Author URI: https://vk.com/nikolays_93
Author EMAIL: nikolayS93@ya.ru
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/**
 * @todo : Add Expandble Script
 */

namespace TaxWidget;

if ( ! defined( 'ABSPATH' ) )
  exit; // disable direct access

define('T_WIDGET_DIR', rtrim(plugin_dir_path( __FILE__ )));
define('T_WIDGET_URL', rtrim(plugin_dir_url( __FILE__ )));
define('T_WIDGET_NAME', plugin_basename( __FILE__ ));

function register_taxanomies_widget(){

  register_widget( 'TaxWidget\TaxanomiesWidget' );
}

function _init() {
    require T_WIDGET_DIR . '/inc/taxanomies-widget-utils.php';
    require T_WIDGET_DIR . '/inc/class-taxanomies-widget.php';
    require T_WIDGET_DIR . '/inc/taxanomies-widget-front-views.php';

    add_action( 'widgets_init', 'TaxWidget\register_taxanomies_widget' );

    // enqueue admin scripts & styles
    add_action( 'admin_enqueue_scripts', 'TaxWidget\admin_scripts' );
    add_action( 'customize_controls_enqueue_scripts', 'TaxWidget\admin_scripts' );

    add_action( 'admin_enqueue_scripts', 'TaxWidget\admin_styles' );
    add_action( 'customize_controls_enqueue_scripts', 'TaxWidget\admin_styles' );
    add_action( 'customize_controls_enqueue_scripts', 'TaxWidget\front_styles' );

    // initialize front styles
    add_action( 'wp_enqueue_scripts', 'TaxWidget\front_styles' );
}
add_action( 'plugins_loaded', 'TaxWidget\_init', 99 );

function admin_scripts( $hook ){
  global $pagenow;

  if( 'customize.php' == $pagenow || 'widgets.php' == $pagenow || 'widgets.php' == $hook )
    wp_enqueue_script( 'widget-panels', T_WIDGET_URL . '/js/terms-widget.js', array( 'jquery' ), '', true );
}

function admin_styles(){
  wp_enqueue_style( 'widget-panels', T_WIDGET_URL . '/css/terms-widget.css', array(), '1.0' );
}

function front_styles(){
  wp_enqueue_style( 'taxanomies-widget', T_WIDGET_URL . '/css/front.css', null, null );
}