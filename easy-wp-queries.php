<?php

/*
Plugin Name: Simple WP Post Queries Shortcode and Widget
Description: Add Query shortcode
Plugin URI: http://#
Version: 1.3
Author: NikolayS93
Author URI: https://vk.com/nikolays_93
Author EMAIL: nikolayS93@ya.ru
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

namespace CDevelopers\Query\Post;

if ( ! defined( 'ABSPATH' ) )
    exit;

const DOMAIN = 'simple-post-queries';

class Utils
{
    const OPTION = 'SWPQ';
    const SHORTCODE = 'query';

    private static $initialized;
    private static $settings;
    private function __construct() {}
    private function __clone() {}

    static function activate() { add_option( self::OPTION, array() ); }
    static function uninstall() { delete_option(self::OPTION); }

    private static function include_required_classes()
    {
        // $class_dir = self::get_plugin_dir('classes');
        // $classes = array(
        //     );

        // foreach ($classes as $classname => $path) {
        //     if( ! class_exists($classname) ) {
        //         require_once $path;
        //     }
        // }

        // includes
        require_once __DIR__ . '/include/queries.php';
    }

    public static function initialize()
    {
        if( self::$initialized ) {
            return false;
        }

        load_plugin_textdomain( DOMAIN, false, DOMAIN . '/languages/' );
        self::include_required_classes();

        add_action( 'admin_init', array( __CLASS__, 'init_mce_plugin' ), 20 );
        add_shortcode( self::SHORTCODE, array('SimpleWPQuery', 'queries') );

        self::$initialized = true;
    }

    /**
     * Записываем ошибку
     */
    public static function write_debug( $msg, $dir )
    {
        if( ! defined('WP_DEBUG_LOG') || ! WP_DEBUG_LOG )
            return;

        $dir = str_replace(__DIR__, '', $dir);
        $msg = str_replace(__DIR__, '', $msg);

        $date = new \DateTime();
        $date_str = $date->format(\DateTime::W3C);

        if( $handle = @fopen(__DIR__ . "/debug.log", "a+") ) {
            fwrite($handle, "[{$date_str}] {$msg} ({$dir})\r\n");
            fclose($handle);
        }
        elseif (defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY) {
            echo __("Не удается получить доступ к файлу ", DOMAIN) . __DIR__ . "/debug.log";
            echo "{$msg} ({$dir})";
        }
    }

    /**
     * Загружаем файл если существует
     */
    public static function load_file_if_exists( $file_array, $args = array() )
    {
        $cant_be_loaded = __('The file %s can not be included', DOMAIN);
        if( is_array( $file_array ) ) {
            $result = array();
            foreach ( $file_array as $id => $path ) {
                if ( ! is_readable( $path ) ) {
                    self::write_debug(sprintf($cant_be_loaded, $path), __FILE__);
                    continue;
                }

                $result[] = include_once( $path );
            }
        }
        else {
            if ( ! is_readable( $file_array ) ) {
                self::write_debug(sprintf($cant_be_loaded, $file_array), __FILE__);
                return false;
            }

            $result = include_once( $file_array );
        }

        return $result;
    }

    public static function get_plugin_dir( $path = false )
    {
        $result = __DIR__;

        switch ( $path ) {
            case 'classes': $result .= '/includes/classes'; break;
            case 'settings': $result .= '/includes/settings'; break;
            default: $result .= '/' . $path;
        }

        return $result;
    }

    public static function get_plugin_url( $path = false )
    {
        $result = plugins_url(basename(__DIR__) );

        switch ( $path ) {
            default: $result .= '/' . $path;
        }

        return $result;
    }

    /**
     * Получает настройку из self::$settings или из кэша или из базы данных
     */
    public static function get( $prop_name, $default = false )
    {
        if( ! self::$settings )
            self::$settings = get_option( self::OPTION, array() );

        if( 'all' === $prop_name ) {
            if( is_array(self::$settings) && count(self::$settings) )
                return self::$settings;

            return $default;
        }

        return isset( self::$settings[ $prop_name ] ) ? self::$settings[ $prop_name ] : $default;
    }

    public static function get_settings( $filename, $args = array() )
    {

        return self::load_file_if_exists( self::get_plugin_dir('settings') . '/' . $filename, $args );
    }

    static function init_mce_plugin()
    {
        /** MCE Editor */
        if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
            return;
        }

        // add_action('wp_ajax_update_squery_settings', array(__CLASS__, 'ajax_update_settings'));
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
        $plugin_array['query_shortcode'] = plugins_url( 'admin/js/query_button.js', __FILE__ );

        return $plugin_array;
    }

    static function mce_button($buttons)
    {
        $buttons[] = 'query_shortcode';

        return $buttons;
    }

    static function get_post_type_list()
    {
        $post_types = get_post_types( array('public' => true) );
        $types = array();
        foreach ($post_types as $value => $text) {
            $types[] = (object) array('value' => $value, 'text' => __( ucfirst($text) ) );
        }

        return apply_filters( 'SWPQ_post_type_list', $types );
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

        return apply_filters( 'SWPQ_status_list', $statuses );
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
            );

        return apply_filters( 'SWPQ_order_by_list', $order_by );
    }

    static function add_mce_script()
    {
        if ( ! isset( get_current_screen()->id ) || get_current_screen()->base != 'post' ) {
            return;
        }

        wp_enqueue_script( 'query-sc', plugins_url( 'admin/js/query_shortcode.js', __FILE__ ),
            array( 'shortcode', 'wp-util', 'jquery' ), false, true );
        wp_localize_script( 'query-sc',
            'qOpt',
            array(
                'nonce'     => '',
                'shortcode' => self::SHORTCODE,

                'types'     => self::get_post_type_list(),
                'categories' => '',
                'pages' => '',
                'taxanomies' => '',
                'terms' => '',
                // '' => '',
                'statuses'  => self::get_status_list(),
                'orderby'   => self::get_order_by_list(),
                ) );
    }
}

register_activation_hook( __FILE__, array( __NAMESPACE__ . '\Utils', 'activate' ) );
register_uninstall_hook( __FILE__, array( __NAMESPACE__ . '\Utils', 'uninstall' ) );
// register_deactivation_hook( __FILE__, array( __NAMESPACE__ . '\Utils', 'deactivate' ) );

add_action( 'plugins_loaded', array( __NAMESPACE__ . '\Utils', 'initialize' ), 10 );
