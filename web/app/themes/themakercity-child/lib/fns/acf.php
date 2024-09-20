<?php
namespace TheMakerCity\acf;

/**
 * Sets the Google Maps API key for ACF Google Map fields.
 *
 * This function checks if the `GOOGLE_MAPS_API_KEY` constant is defined, and if so, assigns it to the ACF Google Map API configuration. If the constant is not defined, it sets the API key to null.
 *
 * @param array $api The existing Google Maps API configuration.
 *
 * @return array The modified API configuration with the API key set.
 */
function my_acf_google_map_api( $api ){

    $api['key'] = ( defined('GOOGLE_MAPS_API_KEY') )? GOOGLE_MAPS_API_KEY : null ;
    return $api;
}
add_filter('acf/fields/google_map/api', __NAMESPACE__ . '\\my_acf_google_map_api');

/**
 * Disables the ACF Extended Pro plugin on the 'profile' page.
 *
 * This function checks the custom routing variables `maker_template` and `maker_slug` to determine if
 * the current page is the 'profile' page. If so, it deactivates the ACF Extended Pro plugin to prevent it from loading.
 *
 * @global array $custom_routes The array containing custom route configurations.
 *
 * @return void
 */
function disable_acf_extended_on_profile() {
  // Get the value of the maker_template query variable
  $maker_template = get_query_var( 'maker_template' );
  $maker_slug = get_query_var( 'maker_slug' );

  // Check if the maker_template is 'dashboard' and the maker_slug is 'profile'
  global $custom_routes;
  if( ! empty( $maker_template ) ){
    foreach ( $custom_routes as $route ) {
      if ( $maker_template === 'dashboard' && $maker_slug === 'profile' ) {
        // Deactivate the ACF Extended Pro plugin
        deactivate_plugins( 'acf-extended-pro/acf-extended.php' );
      }
    }
  }
}
add_action( 'template_redirect', __NAMESPACE__ . '\\disable_acf_extended_on_profile' );
