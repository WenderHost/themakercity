<?php
/**
 * Theme functions and definitions.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * https://developers.elementor.com/docs/hello-elementor-theme/
 *
 * @package TheMakerCityChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_CHILD_VERSION', '2.0.0' );
define( 'MAKR_STYLESHEET_DIR', trailingslashit( get_stylesheet_directory( __FILE__ ) ) );

/**
 * Include required files
 */
require_once( MAKR_STYLESHEET_DIR . 'lib/fns/debugging.php' );
require_once( MAKR_STYLESHEET_DIR . 'lib/fns/enqueues.php' );
require_once( MAKR_STYLESHEET_DIR . 'lib/fns/templates.php' );
require_once( MAKR_STYLESHEET_DIR . 'lib/fns/utilities.php' );