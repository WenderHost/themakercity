<?php
/**
 * The user dashboard template.
 *
 * Loads the relevant template part,
 * the loop is executed (when needed) by the relevant template part.
 *
 * @package TheMakerCity
 */
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

add_action( 'wp_enqueue_scripts', function(){
  // Remove unused scripts/styles
  wp_dequeue_style('admin-bar');
  wp_dequeue_script('admin-bar');
  wp_dequeue_style( 'hello-elementor' );

  // Load AdmiKit
  wp_enqueue_script( 'adminkit' );
  wp_enqueue_style( 'adminkit' );

  wp_enqueue_script( 'systeminfo' );
}, 99 );

// Don't show admin bar
add_filter('show_admin_bar', '__return_false');

/**
 * Retrieve the URL of the current request:
 */
$current_url = $_SERVER['REQUEST_URI'];

/**
 * Generate an array of valid slugs for accessing partials via this template
 */
$valid_slugs = [];
foreach( $custom_routes as $route ){
  $valid_slugs[] = $route['slug'];
}

/**
 * Set the following page related variables:
 *
 * @param  string  $current_slug   The current URL path
 * @param  string  $current_title  The title for the path
 * @param  bool    $auth_required  TRUE if authorization is required.
 */
$current_slug = 'sign-in';
$current_title = get_bloginfo( 'title' );
$auth_required = true;

// $custom_routes is defined in /lib/fns/routes.php
foreach( $custom_routes as $route ){
  if( false !== strpos( $current_url, '/' . $route['slug'] ) ){
    $current_slug = $route['slug'];
    $current_title = $route['title'] . ' | ' . get_bloginfo( 'title' );
    $auth_required = $route['auth_required'];
  }
}

/**
 * Redirect to home if $current_slug is not a Dashboard
 * route or requires authorization and the user is not
 * logged in:
 */
if(
  ! in_array( $current_slug, $valid_slugs )
  || ( ! is_user_logged_in() && $auth_required )
){
  wp_redirect( home_url() );
} else if( is_user_logged_in() && in_array( $current_slug, [ 'sign-in','sign-up', 'apply' ] ) ){
  // Conversely, if the user is logged in, no need to show the
  // "Sign In" or "Sign Up" pages, redirect to "My Profile":
  wp_redirect( home_url( '/profile-editor/' ) );
}

/**
 * Load the appropriate template partials:
 */
if( 'apply' == $current_slug ){
  wp_enqueue_style( 'choicesjs' );
  wp_enqueue_script( 'choicesjs' );
  wp_enqueue_style( 'filepond' );
  wp_enqueue_script( 'filepond-init' );
}

get_template_part( 'wp-templates/layout/head', null, [ 'title' => $current_title ] );
if( is_user_logged_in() ){
    get_template_part( 'wp-templates/layout/header' );
    get_template_part( 'wp-templates/' . $current_slug );
} else {
  if( ! $auth_required ){
    get_template_part( 'wp-templates/' . $current_slug );
  } else {
    //wp_die('Authorization required.');
    get_template_part( 'wp-templates/sign-in' );
  }
}
get_template_part( 'wp-templates/layout/footer' );
