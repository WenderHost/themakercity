<?php
namespace TheMakerCity\enqueues;

/**
 * Load child theme scripts & styles.
 *
 * @return void
 */
function enqueue_scripts_styles() {
  //wp_enqueue_style('hello-elementor-child-style', get_stylesheet_directory_uri() . '/style.css', ['hello-elementor-theme-style'], HELLO_ELEMENTOR_CHILD_VERSION);
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_scripts_styles', 20 );