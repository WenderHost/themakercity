<?php

namespace TheMakerCity\emoji;

/**
 * Disable the emoji support in WordPress.
 */
function disable_wp_emojis() {
  // Remove emoji scripts and styles from the front-end
  remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
  remove_action( 'wp_print_styles', 'print_emoji_styles' );

  // Remove emoji support from the admin area
  remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
  remove_action( 'admin_print_styles', 'print_emoji_styles' );

  // Disable the DNS prefetch for emojis
  add_filter( 'emoji_svg_url', '__return_false' );

  // Remove the emoji script from TinyMCE editor
  add_filter( 'tiny_mce_plugins', function( $plugins ) {
    return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
  });

  // Prevent emojis from being converted in content
  add_filter( 'wp_resource_hints', function( $urls, $relation_type ) {
    if ( 'dns-prefetch' === $relation_type ) {
      $urls = array_diff( $urls, array( 'https://s.w.org/images/core/emoji/2/svg/' ) );
    }
    return $urls;
  }, 10, 2 );
}
add_action( 'init', __NAMESPACE__ . '\\disable_wp_emojis' );
