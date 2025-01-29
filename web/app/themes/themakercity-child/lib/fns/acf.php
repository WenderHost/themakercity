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


function profile_faq( $field ){
  ?>
  <div class="accordion">
    <div class="accordion-item">
      <h2 class="accordion-header" id="headingOne">
        <button class="accordion-button fs-6 fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
         What type of image files can I upload?
        </button>
      </h2>

      <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#directory-steps">
        <div class="accordion-body">
          <p>Upload your images as JPG, GIF, or PNG files.</p><p>Note: The file type you choose varies depending on the type of image. For photographs, use JPG (it's a compression type made for photos by the Joint Photographic Experts Group, hence the name JPEG). For logos, GIF or PNG is great.</p><p>Try to keep your filesize bellow 150K for logos. Photos can be larger but we limit your uploads filesize to 2.5MB.</p>
        </div><!-- .accordion-body -->
      </div>
    </div><!-- .accordion-item -->
  </div>
  <?php
}
add_action( 'acf/render_field/name=profile_faq', __NAMESPACE__ . '\\profile_faq' );