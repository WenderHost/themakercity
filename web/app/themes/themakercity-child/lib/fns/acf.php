<?php
namespace TheMakerCity\acf;
use function TheMakerCity\utilities\get_alert;

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
         <i class="fas fa-image" style="margin-right: 8px;"></i> What type of image files can I upload?
        </button>
      </h2>

      <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#directory-steps">
        <div class="accordion-body">
          <p>Upload your images as JPG, GIF, or PNG files.</p><p>Note: The file type you choose varies depending on the type of image. For photographs, use JPG (it's a compression type made for photos by the Joint Photographic Experts Group, hence the name JPEG). For logos, GIF or PNG is great.</p><p>Try to keep your filesize bellow 150K for logos. Photos can be larger but we limit your uploads filesize to 2.5MB.</p>
        </div><!-- .accordion-body -->
      </div>
    </div><!-- .accordion-item -->
    <div class="accordion-item">
      <h2 class="accordion-header" id="headingTwo">
        <button class="accordion-button fs-6 fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
         <i class="fas fa-hand" style="margin-right: 8px;"></i> Help! "Add to gallery" isn't working.
        </button>
      </h2>

      <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#directory-steps">
        <div class="accordion-body">
          <?= get_alert(['type' => 'success', 'description' => '<strong>04/01/2025 (14:13) - NOTE:</strong> Earlier today there was an issue loading some of the backend scripts that power the profile editor. We have just pushed an update that should fix that issue.']) ?>
          <?= get_alert(['type' => 'success', 'description' => '<strong>02/22/2025 (07:55) - Update:</strong> This feature should be working now. We fixed a setting which was preventing all users from uploading images. Apologies for the inconvenience. ~The Maker City Webmaster']) ?>
          <p>Our webmaster is gathering details of affected user's systems so we can better diagnose and fix this issue. If the "Add to gallery" button isn't working, share your browser/OS details with our webmaster by clicking this button:</p>
          <button class="btn btn-primary" type="button" id="send-system-info">Share Your System Info</button>
          <p id="status"></p>
          <p>NOTE: You only need to send your details once. Please allow our webmaster time to process your info and work on a solution.</p>
        </div><!-- .accordion-body -->
      </div>
    </div><!-- .accordion-item -->    
  </div>
  <?php
}
add_action( 'acf/render_field/name=profile_faq', __NAMESPACE__ . '\\profile_faq' );