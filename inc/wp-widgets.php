<?php

/**
 * @todo : Add Expandble Script
 */

namespace SQUERY\Widget_Terms;

if ( ! defined( 'ABSPATH' ) )
  exit; // disable direct access

function register_taxanomies_widget(){

  register_widget( 'SQUERY\Widget_Terms\Widget' );
}

function widgets_init() {
    add_action( 'widgets_init', __NAMESPACE__ . '\register_taxanomies_widget' );

    // enqueue admin scripts & styles
    add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\admin_scripts' );
    add_action( 'customize_controls_enqueue_scripts', __NAMESPACE__ . '\admin_scripts' );

    add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\admin_styles' );
    add_action( 'customize_controls_enqueue_scripts', __NAMESPACE__ . '\admin_styles' );
    add_action( 'customize_controls_enqueue_scripts', __NAMESPACE__ . '\front_styles' );

    // initialize front styles
    add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\front_styles' );
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\widgets_init', 99 );

function admin_scripts( $hook ){
  global $pagenow;

  if( 'customize.php' == $pagenow || 'widgets.php' == $pagenow || 'widgets.php' == $hook )
    wp_enqueue_script( 'widget-panels', SQUERY_ASSETS . '/js/terms-widget.js', array( 'jquery' ), '', true );
}

function admin_styles(){
  wp_enqueue_style( 'widget-panels', SQUERY_ASSETS . '/css/terms-widget.css', array(), '1.0' );
}

function front_styles(){
  wp_enqueue_style( 'taxanomies-widget', SQUERY_ASSETS . '/css/front.css', null, null );
}