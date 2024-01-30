<?php
namespace TheMakerCity\enqueues;

/**
 * Load child theme scripts & styles.
 *
 * @return void
 */
function enqueue_scripts_styles() {
  $css_dir = ( 'development' != MAKR_ENV )? 'css-dist' : 'css' ;
  wp_enqueue_style( 'makercity', get_stylesheet_directory_uri() . '/lib/' . $css_dir . '/main.css', null, filemtime( get_stylesheet_directory() . '/lib/' . $css_dir . '/main.css' ) );
  wp_enqueue_script( 'font-awesome', 'https://kit.fontawesome.com/f4de4ed03f.js', null, '1.0.0', false );
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_scripts_styles', 20 );

function dequeue_jquery_migrate( $scripts ) {
    if ( ! is_admin() && ! empty( $scripts->registered['jquery'] ) ) {
        $scripts->registered['jquery']->deps = array_diff(
            $scripts->registered['jquery']->deps,
            [ 'jquery-migrate' ]
        );
    }
}
add_action( 'wp_default_scripts', __NAMESPACE__ . '\\dequeue_jquery_migrate' );