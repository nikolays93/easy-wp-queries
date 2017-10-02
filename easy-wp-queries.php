<?php

/*
Plugin Name: Simple WordPress Queries Shortcode
Description: Add Query shortcode
Plugin URI: http://#
Version: 1.3
Author: NikolayS93
Author URI: https://vk.com/nikolays_93
Author EMAIL: nikolayS93@ya.ru
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) )
    exit;

define('SQUERY_DIR', rtrim(plugin_dir_path( __FILE__ ), '/') );

class SimpleWPQuery_Plugin {
    const SETTINGS_NAME = 'SWPQ';
    const SHORTCODE = 'query';

    private function __construct() {}

    static function init()
    {
        add_action( 'admin_init', array( __CLASS__, 'init_mce_plugin' ), 20 );

        require SQUERY_DIR . '/inc/queries.php';
        add_shortcode( self::SHORTCODE, array('SimpleWPQuery', 'queries') );
    }

    static function init_mce_plugin()
    {
        /** MCE Editor */
        if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
            return;
        }

        add_action('wp_ajax_update_squery_settings', array(__CLASS__, 'ajax_update_settings'));

        // foreach ( array('post.php','post-new.php') as $hook ) {
        //  add_action( "admin_head-$hook", array(__CLASS__, 'mce_variables') );
        // }

        add_action('admin_head', array( __CLASS__, 'add_mce_script' ));

        add_filter("mce_external_plugins", array(__CLASS__, 'mce_plugin'));
        add_filter("mce_buttons", array(__CLASS__, 'mce_button'));
    }

    /** Register Shortcode Button MCE */
    static function mce_plugin($plugin_array)
    {
        $plugin_array['query_shortcode'] = plugins_url( 'js/query_button.js', __FILE__ );

        return $plugin_array;
    }

    static function mce_button($buttons)
    {
        $buttons[] = 'query_shortcode';

        return $buttons;
    }

    static function add_mce_script()
    {
        if ( ! isset( get_current_screen()->id ) || get_current_screen()->base != 'post' ) {
            return;
        }

        $req = array( 'shortcode', 'wp-util', 'jquery' );
        wp_enqueue_script( 'query-sc', plugins_url( 'js/query_shortcode.js', __FILE__ ), $req, false, true );

        wp_localize_script( 'query-sc',
            'custom_query_settings',
            array(
                'shortcode' => self::SHORTCODE,
                'types'     => self::get_post_type_list(),
                'statuses'  => self::get_status_list(),
                'orderby'   => self::get_order_by_list(),
                ) );
    }

    static function get_post_type_list()
    {
        $post_types = get_post_types( array('public' => true) );
        $types = array();
        foreach ($post_types as $value => $text) {
            $types[] = (object) array('value' => $value, 'text' => __( ucfirst($text) ) );
        }

        return $types;
    }

    static function get_status_list()
    {
        $statuses = array(
            (object) array(
                'text' => __( 'Published' ),
                'value' => 'publish'
                ),
            (object) array(
                'text' => __( 'Scheduled' ),
                'value' => 'future'
                ),
            (object) array(
                'text' => __( 'За все время' ),
                'value' => 'alltime'
                ),
            (object) array(
                'text' => __( 'Any' ),
                'value' => 'any',
                ),
            );

        return $statuses;
    }

    static function get_order_by_list()
    {
        $order_by = array(
            (object) array(
                'text' => __( 'None' ),
                'value' => 'none'
                ),
            (object) array(
                'text' => __('ID'),
                'value' => 'ID'
                ),
            (object) array(
                'text' => __('Author'),
                'value' => 'author'
                ),
            (object) array(
                'text' => __('Title'),
                'value' => 'title'
                ),
            (object) array(
                'text' => __('Name'),
                'value' => 'name'
                ),
            (object) array(
                'text' => __('Type'),
                'value' => 'type'
                ),
            (object) array(
                'text' => __('Date'),
                'value' => 'date'
                ),
            (object) array(
                'text' => __('Modified'),
                'value' => 'modified'
                ),
            (object) array(
                'text' => __('Parent'),
                'value' => 'parent'
                ),
            (object) array(
                'text' => __('Random'),
                'value' => 'rand'
                ),
            (object) array(
                'text' => __('Comment'),
                'value' => 'comment_count'
                ),
            (object) array(
                'text' => __('Relevance'),
                'value' => 'relevance'
                ),
            (object) array(
                'text' => __('Menu'),
                'value' => 'menu_order date'
                ),
            // {text: 'meta_value', value: 'meta_value'},
            // {text: 'meta_value_num', value: 'meta_value_num'},
            // {text: 'post__in', value: 'post__in'},
            // {text: 'post_name__in', value: 'post_name__in'},
            // {text: 'post_parent__in', value: 'post_parent__in'}
            );

        return $order_by;
    }
}

add_action( 'plugins_loaded', array('SimpleWPQuery_Plugin', 'init') );
