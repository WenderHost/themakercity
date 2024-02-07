<?php

namespace TheMakerCity\routes;

/**
 * CUSTOM ROUTES
 *
 * Defines our custom routes by specifying a "slug" and "template" for each array key.
 *
 * The advantage of this setup allows us to load theme templates
 * for specified routes without having to setup a page inside
 * the WordPress admin.
 *
 * - The "slug" will be the route accessed in the browser.
 * - The "template" will be the template file without the ".php" extension loaded from this theme.
 */
$custom_routes = [
  0 => [
    'slug'      => 'profile',
    'template'  => 'dashboard',
  ],
];

/**
 * Add custom query_vars to work with our custom routes
 *
 * @param      array  $vars   The variables
 *
 * @return     array  The query_vars array with new variables added
 */
function custom_query_vars($vars) {
    $vars[] = 'maker_template';
    return $vars;
}
add_filter( 'query_vars', __NAMESPACE__ . '\\custom_query_vars' );

/**
 * Add our theme's custom routes
 */
function custom_routes(){
  global $custom_routes;
  if( is_array( $custom_routes ) ):
    foreach( $custom_routes as $route ){
      add_rewrite_rule('^' . $route['slug'] . '/?', 'index.php?maker_template=' . $route['template'], 'top');
    }
  endif;
}
add_action( 'init', __NAMESPACE__ . '\\custom_routes' );

/**
 * Include this theme's custom templates
 *
 * @param      string  $template  The template
 *
 * @return     string  Path to the template
 */
function custom_template_include( $template ){
  $maker_template = get_query_var( 'maker_template' );
  if( ! empty( $maker_template ) ):
    $new_template = locate_template( [ $maker_template . '.php' ] );
    if( '' != $new_template )
      $template = $new_template;
  endif;

  return $template;
}
add_filter( 'template_include', __NAMESPACE__ . '\\custom_template_include' );

/**
 * Register our custom routes upon theme activation
 */
function custom_theme_activate(){
  // Add our custom routes
  custom_routes();

  // Flush the rewrite rules
  flush_rewrite_rules();
}
add_action( 'after_switch_theme', __NAMESPACE__ . '\\custom_theme_activate' );
