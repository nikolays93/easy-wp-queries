<?php
namespace SQUERY;

if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Static Class
 */
class MCE_Widget extends SimpleQueries {
    public static $widget_id = 'query_shortcode';

    static function init_mce_widget(){
        if ( !current_user_can('edit_posts') || !current_user_can('edit_pages') )
            return;

        add_action('wp_ajax_update_squery_settings', array(__CLASS__, 'ajax_update_settings'));

        add_action( 'admin_head', array( __CLASS__, 'add_mce_script' ) );
        add_filter("mce_external_plugins", array( __CLASS__, 'mce_plugin' ));
        add_filter("mce_buttons", array( __CLASS__, 'mce_button') );
    }

    static function ajax_update_settings() {
        if( ! wp_verify_nonce( $_POST['security'], __CLASS__ ) )
            wp_die('Ошибка! нарушены правила безопасности');

        if( ! isset($_POST['template_dir']) )
            wp_die('Данные не переданны');

        $dir = get_template_directory() . '/' . sanitize_text_field( $_POST['template_dir'] );
        if( !is_dir($dir) )
            wp_die('Нет доступа к указаной папке в активной теме (' . $dir . ')');


        $update = update_option( parent::SETTINGS_NAME, array('template_dir' => $_POST['template_dir']) );
        if( $update )
            echo 1;

        wp_die();
    }

    /** Register Shortcode Button MCE */
    static function mce_plugin($plugin_array){
        $plugin_array[self::$widget_id] = SQUERY_ASSETS . '/js/query_button.js';
        return $plugin_array;
    }

    static function mce_button($buttons){
        $buttons[] = self::$widget_id;
        return $buttons;
    }

    static function add_mce_script(){
        if ( ! isset( get_current_screen()->id ) || get_current_screen()->base != 'post' )
            return;

        wp_enqueue_script( 'query-sc', SQUERY_ASSETS . '/js/query_shortcode.js', array( 'shortcode', 'wp-util', 'jquery' ), false, true );

        $options = get_option( parent::SETTINGS_NAME );
        $post_types = get_post_types( array('public' => true) );
        $types = array();
        foreach ($post_types as $value => $text) {
            $types[] = (object) array('value' => $value, 'text' => $text);
        }
        wp_localize_script('query-sc', 'queryMCEVar',
            array(
                'postTypes' => $types,
                'security' => wp_create_nonce( __CLASS__ ),
                'template' => isset($options['template_dir']) ? $options['template_dir'] : '',
                )
            );
    }
}

