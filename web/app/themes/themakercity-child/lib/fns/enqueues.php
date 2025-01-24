<?php
namespace TheMakerCity\enqueues;

/**
 * Load child theme scripts & styles.
 *
 * @return void
 */
function enqueue_scripts_styles() {
  $css_dir = ( 'development' != MAKR_ENV )? 'css-dist' : 'css' ;
  wp_enqueue_style( 'makercity', MAKR_STYLESHEET_DIR_URI . 'lib/' . $css_dir . '/main.css', null, filemtime( MAKR_STYLESHEET_DIR . 'lib/' . $css_dir . '/main.css' ) );
  wp_enqueue_script( 'font-awesome', 'https://kit.fontawesome.com/f4de4ed03f.js', null, '1.0.0', false );

  // AdminKit Scripts/Styles
  wp_register_script( 'adminkit', MAKR_STYLESHEET_DIR_URI . 'adminkit-pro/dist/js/app.js', null, filemtime( MAKR_STYLESHEET_DIR . 'adminkit-pro/dist/js/app.js' ), true );
  wp_register_style( 'adminkit', MAKR_STYLESHEET_DIR_URI . 'adminkit-pro/dist/css/light.css', null, filemtime( MAKR_STYLESHEET_DIR . 'adminkit-pro/dist/css/light.css' ) );

  wp_register_style( 'choicesjs', MAKR_STYLESHEET_DIR_URI . 'lib/css-dist/choices.min.css', null, filemtime( MAKR_STYLESHEET_DIR . 'lib/css-dist/choices.min.css' ) );
  wp_register_script( 'choicesjs', MAKR_STYLESHEET_DIR_URI . 'lib/js/choices.min.js', null, filemtime( MAKR_STYLESHEET_DIR . 'lib/js/choices.min.js' ) );

  wp_enqueue_script( 'global', MAKR_STYLESHEET_DIR_URI . 'lib/js/dist/global.js', null, filemtime( MAKR_STYLESHEET_DIR . 'lib/js/dist/global.js' ), true );

  wp_register_style( 'filepond', MAKR_STYLESHEET_DIR_URI . 'lib/js/dist/filepond.init.css', null, filemtime( MAKR_STYLESHEET_DIR . 'lib/js/dist/filepond.init.css' ) );
  //wp_register_script( 'filepond', 'https://unpkg.com/filepond@^4/dist/filepond.js' );
  wp_register_script( 'filepond-init', MAKR_STYLESHEET_DIR_URI . 'lib/js/dist/filepond.init.js', null, filemtime( MAKR_STYLESHEET_DIR . 'lib/js/dist/filepond.init.js' ), true );

}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_scripts_styles', 20 );

function add_type_attribute( $attributes ) {
  // Only do this for a specific script.
  if ( isset( $attributes['id'] ) && 'filepond-init-js' === $attributes['id'] ) {
    uber_log('🪵 Adding `type` attribute to ' . $attributes['id'] );
    $attributes['type'] = 'module';
  }

  return $attributes;
}
//add_filter( 'wp_script_attributes', __NAMESPACE__ . '\\add_type_attribute', 10, 1 );

function dequeue_jquery_migrate( $scripts ) {
    if ( ! is_admin() && ! empty( $scripts->registered['jquery'] ) ) {
        $scripts->registered['jquery']->deps = array_diff(
            $scripts->registered['jquery']->deps,
            [ 'jquery-migrate' ]
        );
    }
}
add_action( 'wp_default_scripts', __NAMESPACE__ . '\\dequeue_jquery_migrate' );