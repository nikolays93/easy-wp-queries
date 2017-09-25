<?php
class SimpleWPQuery {
  protected $temp;

  function __construct(){
    add_shortcode('query', array($this, 'queries'));
  }

  function get_query_template($template, $slug=false, $template_args = array()){
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

    if($req = locate_template($templates)){
      require $req;
      return;
    }

    if( ! is_admin() && defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY){
      echo "<pre>";
      echo "Шаблон не найден по адресу: <br>" . get_template_directory() . '/<br>';
      print_r($templates);
      echo "</pre>";
    }
  }
  function get_container($part=false, $class = 'container-fluid', $tag = 'div', $post_type = false){
    $result = "";

    if( $class ) {
      $class .= " custom-query";

      if( $post_type == 'product' ) {
        $tag = 'ul';
        $class .= ' products';
      }

      if( $part == "start" ) {
        if( $post_type == 'product' ) $result.= '<section class="woocommerce">';
        $result.= "<{$tag} class='{$class}'>";
        if( strpos($class, 'container') !== false || strpos($class, 'container-fluid') !== false ) {
          $result.= '<div class="row">';
        }
      }

      if( $part == 'end' ) {
        if( strpos($class, 'container') !== false || strpos($class, 'container-fluid') !== false ) {
          $result.= '</div><!-- .row -->';
        }
        $result.= "</{$tag}><!-- .{$class} -->";
        if( $post_type == 'product' ) $result.= '</section>';
      }
    }
    return $result;
  }
  function queries( $atts, $content = null ) {
    // todo: add oreder by
    // default args
    extract( shortcode_atts( array(
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
      'template' => false, // for custom template
    ), $atts ) );

    if($parent == 'this' || $parent == '(this)'){
      $parent = array( get_the_id() );
    }
    elseif(!empty($parent)){
      $parent = explode(',', $parent);
    }

    if($status == "alltime")
      $status = array('publish', 'future');

    switch ($container) {
      case 'true': $container = 'container';  break;
      case 'false': $container = false; break;
    }

    $args = apply_filters('easy_queries_args', array(
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

    if( $terms ){
      if( ! $tax ){
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

    if( ! $template ) {
      $template = ($args['post_type'] != 'post') ? $args['post_type'] : '';
    }

    $query = new WP_Query($args);

    if( $max > 1 )
      $this->set_query_variables('is_singular', '');

    if($type != 'page')
      $this->set_query_variables('is_page', '');

    // шаблон
    ob_start();
    if ( $query->have_posts() ) {
      echo $this->get_container('start', $container, $wrap_tag, $args['post_type']);
      while ( $query->have_posts() ) {
        $query->the_post();

        $options = get_option( SimpleWPQuery_Plugin::SETTINGS_NAME );
        $tempalte_dir = ( !empty($options['template_dir']) ) ? $options['template_dir'] : 'template-parts';

        $this->get_query_template( $tempalte_dir . '/content', $template, array(
          'post_type' => $args['post_type'],
          'query'   => $args,
          'columns' => $columns,
          ));
      }
      echo $this->get_container('end', $container, $wrap_tag);
    } else {
      if(is_wp_debug()){
        echo "<h4>Режим отладки:</h4>";
        echo 'Не найдено записей по данному запросу<hr>';
        var_dump($args);
        echo '<hr>template: ', $template, '<br>';
        echo 'container: ', $container, '<br>';
        echo 'columns: ', $columns, '<br>';
      }
    }
    $this->reset_query_variables();
    wp_reset_postdata();
    return ob_get_clean();
  }

  function set_query_variables($var, $value){
    global $wp_query;
    $this->temp[$var] = $wp_query->$var;
    $wp_query->$var = $value;
  }
  function reset_query_variables(){
    global $wp_query;
    if( sizeof($this->temp) !== 0 ){
      foreach ($this->temp as $key => $value) {
        $wp_query->$key = $value;
      }
    }
  }
}
new SimpleWPQuery();