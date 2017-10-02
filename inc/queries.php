<?php

/**
 * @filter 'custom_query_defaults'
 *         @var $defaults
 *         @description set defaults
 * @filter 'custom_query_class'
 *         @var "custom-query"
 *         @description add wrapper class
 * @filter 'custom_queries_args'
 *         @var $args
 *         @description change args after parse ( ex. type = bestsellers )
 * @filter custom_query_template_dir
 *         @var 'tempalte-parts'
 *         @description directory folder name for search templates
 */
class SimpleWPQuery {
    protected static $tmp;

    protected static function get_default_args()
    {
        $defaults = array(
            'id' => false,
            'max' => '4', /* count show */
            'type' => 'post', // page, product..
            'cat' => '', /* category ID */
            'slug' => '', // category slug
            'parent' => '',
            'status' => 'publish', // publish, future, alltime (publish+future) //
            'order' => 'DESC', // ASC || DESC
            'orderby' => 'menu_order date',
            'wrap_tag' => 'div',
            'container' => 'container-fluid', //true=container, false=noDivContainer, string=custom container
            'tax' => false,
            'terms' => false,
            // template attrs
            'columns' => '4', // 1 | 2 | 3 | 4 | 10 | 12
            'template' => '', // for custom template
            );

        return apply_filters( 'custom_query_defaults', $defaults );
    }

    protected static function search_query_template( $template, $slug=false, $template_args = array() )
    {
        extract($template_args);

        if( $post_type == 'product' ) {
            $templates[] = 'woocommerce/content-'.$slug.'-query.php';
            $templates[] = 'woocommerce/content-'.$slug.'.php';
        }

        if( $slug ) {
            $templates[] = $template.'-'.$slug.'-query.php';
            $templates[] = $template.'-'.$slug.'.php';
        }

        $templates[] = $template.'-query.php';
        $templates[] = $template.'.php';

        if($req = locate_template($templates)) {
            require $req;
            return;
        }

        if( ! is_admin() && defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY) {
            echo "<pre>";
            echo "Шаблон не найден по адресу: <br>" . get_template_directory() . '/<br>';
            print_r($templates);
            echo "</pre>";
        }
    }

    protected static function set_container( $part=false, $class = 'container-fluid', $tag = 'div', $post_type = false )
    {
        $result = "";

        // do not display for empty container class
        if( ! $class ) {
            return $result;
        }

        $classes[] = $result;
        $classes[] = apply_filters( 'custom_query_class', "custom-query" );

        if( 'product' === $post_type ) {
            $tag = 'ul';
            $classes[] = 'products';
        }

        $classes = array_filter($classes, 'esc_attr');
        reset( $classes );

        switch ( $part ) {
            case 'start':
                if( 'product' === $post_type ) {
                    // add .woocommerce for css
                    $result .= '<section class="woocommerce">';
                }

                $result .= sprintf('<%s class="%s">',
                    esc_attr( $tag ),
                    implode(' ', $classes)
                    );

                if( current( $classes ) === 'container' && $tag !== 'ul' ) {
                    $result .= '<div class="row">';
                }
            break;

            case 'end':
                if( current( $classes ) === 'container' && $tag !== 'ul' ) {
                    $result.= '</div><!-- .row -->';
                }

                $result .= sprintf('</%s><!-- .%s -->',
                    esc_attr( $tag ),
                    implode('.', $classes)
                    );

                if( 'product' === $post_type ) {
                    $result .= '</section>';
                }
            break;
        }

        return $result;
    }

    public static function queries( $atts, $content = null ) {
        // default args
        extract( shortcode_atts( self::get_default_args(), $atts ) );

        if( is_array($parent) ){
            $parent = explode(',', $parent);
        }
        elseif( in_array($parent, array('this', '(this)', '$this')) ){
            $parent = array( get_the_id() );
        }

        if($status == "alltime") {
            $status = array('publish', 'future');
        }

        switch ($container) {
            case 'true': $container = 'container'; break;
            case 'false': $container = false; break;
        }

        $args = apply_filters('custom_queries_args', array(
          'p' => $id,
          'cat'=> $cat,
          'post_type' => $type,
          'posts_per_page' => $max,
          'category_name'=> $slug,
          'post_parent__in' => $parent,
          'order' => $order,
          'orderby' => $orderby,
          'post_status' => $status,
          ) );

        if( $terms ) {
            if( ! $tax ) {
                $tax = ($type == 'product') ? 'product_cat' : 'category';
            }

            $terms = array_filter(explode(',', $terms), 'absint');

            if(sizeof($terms) >= 1){
                $args['tax_query'] = array(
                  array(
                    'taxonomy' => sanitize_text_field( $tax ),
                    'terms'    => $terms,
                    ),
                  );
            }
        }

        if( ! $template && 'post' !== $args['post_type'] ) {
            $template = $args['post_type'];
        }

        $query = new WP_Query($args);

        if( $max > 1 ) {
            self::set_query_variables('is_singular', '');
        }

        if( $type != 'page' ) {
            self::set_query_variables('is_page', '');
        }

        // шаблон
        ob_start();
        if ( $query->have_posts() ) {

            echo self::set_container('start', $container, $wrap_tag, $args['post_type']);

            while ( $query->have_posts() ) {
                $query->the_post();

                $options = get_option( SimpleWPQuery_Plugin::SETTINGS_NAME );
                $tempalte_dir = apply_filters( 'custom_query_template_dir', 'template-parts' );

                self::search_query_template( $tempalte_dir . '/content', $template, array(
                  'post_type' => $args['post_type'],
                  'query'   => $args,
                  'columns' => $columns,
                  ) );
            }

            echo self::set_container('end', $container, $wrap_tag, $args['post_type']);

        }
        else {
            if( defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY ) {
                echo "<h4>Режим отладки:</h4>";
                echo 'Не найдено записей по данному запросу<hr>';
                var_dump($args);
                echo '<hr>template: ', $template, '<br>';
                echo 'container: ', $container, '<br>';
                echo 'columns: ', $columns, '<br>';
            }
        }

        self::reset_query_variables();
        wp_reset_postdata();

        return ob_get_clean();
    }

    /**
     * Set temporary data
     */
    private static function set_query_variables($var, $value){
        global $wp_query;

        self::$tmp[$var] = $wp_query->$var;

        $wp_query->$var = $value;
    }

    /**
     * Return temporary data
     */
    private static function reset_query_variables(){
        global $wp_query;

        if( sizeof(self::$tmp) !== 0 ){
            foreach (self::$tmp as $key => $value) {
                $wp_query->$key = $value;
            }
        }
    }
}
