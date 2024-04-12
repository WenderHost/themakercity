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
define( 'MAKR_STYLESHEET_DIR_URI', trailingslashit( get_stylesheet_directory_uri( __FILE__ ) ) );
define( 'MAKR_ENV', getenv( 'WP_ENV' ) );

/**
 * Include required files
 */
require_once MAKR_STYLESHEET_DIR . 'lib/fns/admins.php';
require_once MAKR_STYLESHEET_DIR . 'lib/fns/adminbar.php';
require_once MAKR_STYLESHEET_DIR . 'lib/fns/debugging.php';
require_once MAKR_STYLESHEET_DIR . 'lib/fns/elementor.php';
require_once MAKR_STYLESHEET_DIR . 'lib/fns/emails.php';
require_once MAKR_STYLESHEET_DIR . 'lib/fns/enqueues.php';
require_once MAKR_STYLESHEET_DIR . 'lib/fns/htmx-api-wp.php';
require_once MAKR_STYLESHEET_DIR . 'lib/fns/routes.php';
require_once MAKR_STYLESHEET_DIR . 'lib/fns/templates.php';
require_once MAKR_STYLESHEET_DIR . 'lib/fns/utilities.php';
require_once MAKR_STYLESHEET_DIR . 'lib/fns/users.php';
require_once MAKR_STYLESHEET_DIR . 'lib/fns/wp-login.php';
require_once MAKR_STYLESHEET_DIR . 'lib/shortcodes/logo.php';
require_once MAKR_STYLESHEET_DIR . 'lib/shortcodes/makersocials.php';
require_once MAKR_STYLESHEET_DIR . 'lib/shortcodes/makerstatusalert.php';
require_once MAKR_STYLESHEET_DIR . 'lib/shortcodes/primary_image.php';
require_once MAKR_STYLESHEET_DIR . 'lib/shortcodes/profile_button.php';
