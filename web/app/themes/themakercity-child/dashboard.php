<?php
/**
 * The user dashboard template.
 *
 * Loads the relevant template part,
 * the loop is executed (when needed) by the relevant template part.
 *
 * @package TheMakerCity
 */

// Redirect non-authenticated users
//if( ! is_user_logged_in() )
  //wp_redirect( home_url() );

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

add_action( 'wp_enqueue_scripts', function(){
  wp_dequeue_style( 'hello-elementor' );
}, 99 );

/**
 * Set the following page related variables:
 *
 * $current_route - the current URL path
 * $current_title - the title for the path
 */
$current_url = $_SERVER['REQUEST_URI'];
$dashboard_routes = [ 'profile' => 'My Profile', 'account' => 'My Account' ];
foreach( $dashboard_routes as $route => $title ){
  if( false !== strpos( $current_url, '/' . $route ) ){
    $current_route = $route;
    $current_title = $title . ' | ' . get_bloginfo( 'title' );
  }
}

get_template_part( 'templates/layout/head', null, [ 'title' => $current_title ] );
if( is_user_logged_in() ){
  get_template_part( 'templates/layout/header' );
  get_template_part( 'templates/' . $current_route );
} else {
  get_template_part( 'templates/sign-in' );
}
get_template_part( 'templates/layout/footer' );
