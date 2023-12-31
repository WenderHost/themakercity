<?php
namespace TheMakerCity\enqueues;

/**
 * Load child theme scripts & styles.
 *
 * @return void
 */
function enqueue_scripts_styles() {
  $main_css = ( 'development' != MAKR_ENV )? 'dist/main.css' : 'main.css' ;
  wp_enqueue_style( 'bizplanner', get_stylesheet_directory_uri() . '/lib/css/' . $main_css, null, filemtime( get_stylesheet_directory() . '/lib/css/' . $main_css ) );
  wp_enqueue_script( 'font-awesome', 'https://kit.fontawesome.com/f4de4ed03f.js', null, '1.0.0', false );
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_scripts_styles', 20 );