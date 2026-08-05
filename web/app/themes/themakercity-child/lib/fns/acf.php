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
 * Prevents ACF Extended Pro's frontend/input JS (`acf-extended-input`,
 * i.e. `acfe-input.js`) from loading on the profile editor page.
 *
 * ACF Extended Pro monkey-patches ACF Pro's native Gallery field JS
 * (`acf.models.GalleryField.prototype.onClickAdd` / `.editAttachment`)
 * without keeping a reference to the original methods, which breaks the
 * "Add to gallery" button on this page's frontend `acf_form()`. There's
 * no clean way to un-patch it once that script has run, so instead we
 * stop `acf-extended-input` from being enqueued in the first place, for
 * this one route only.
 *
 * This used to be handled by deactivating the whole plugin via
 * `deactivate_plugins()`, which is NOT request-scoped — it flips the
 * plugin off site-wide via the `active_plugins` option, breaking it for
 * every visitor and also breaking the `profile_faq` field on this same
 * page (an `acfe_dynamic_render` field type supplied by this plugin).
 * See git commit a3e18ca, "BUGFIX: Accessing /profile/ deactivates ACF
 * Extended Pro". Dequeuing just the one script/style for this one
 * request is safe and fully request-scoped — nothing persists to the
 * database, and the PHP-registered `acfe_dynamic_render` field type
 * (which `profile_faq` relies on for rendering, via our own
 * `render_field` callback below) is untouched since that's a separate,
 * always-registered PHP field type, not part of this JS bundle.
 *
 * Also note: this route (`/profile-editor/`) is a virtual rewrite route
 * (see lib/fns/routes.php), not a real WP_Post page, so `is_page()`
 * won't detect it — the `maker_template`/`maker_slug` query vars set by
 * `custom_query_vars()` are the only reliable way to identify it.
 *
 * @return void
 */
function dequeue_acf_extended_on_profile_editor() {
  $maker_template = get_query_var( 'maker_template' );
  $maker_slug     = get_query_var( 'maker_slug' );

  if ( 'dashboard' !== $maker_template || 'profile-editor' !== $maker_slug )
    return;

  add_action( 'acf/input/admin_enqueue_scripts', function(){
    wp_dequeue_script( 'acf-extended-input' );
    wp_dequeue_style( 'acf-extended-input' );
  }, 20 ); // After ACFE's own priority-10 callback enqueues them.
}
add_action( 'template_redirect', __NAMESPACE__ . '\\dequeue_acf_extended_on_profile_editor' );


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
          <?= get_alert(['type' => 'success', 'description' => '<strong>08/05/2026 (15:08):</strong> <em><strong>Been having issues uploading images to your Maker Profile?</strong></em> As of today, I\'m hopeful I\'ve gotten those fixed. If not, click the button below to share your system info with me so I can further diagnose. ~The Maker City Webmaster']) ?>
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