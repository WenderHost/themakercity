<?php
namespace TheMakerCity\shortcodes;
use function TheMakerCity\utilities\get_alert;

/**
 * Shortcode: [maker-map category="maker-spaces"]
 *
 * Displays a Google Map populated with Maker CPTs that have ACF map data.
 * Data is pulled from the custom REST API endpoint /makers/v1/locations.
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML for the map container.
 */
function maker_map_shortcode( $atts ) {

  if( empty( GOOGLE_MAPS_API_KEY ) )
    return get_alert( [ 'type' => 'warning', 'description' => 'GOOGLE_MAPS_API_KEY not found. Please set <code>GOOGLE_MAPS_API_KEY</code> in your <code>.env</code>. <code>GOOGLE_MAPS_API_KEY = ' . GOOGLE_MAPS_API_KEY . '</code>' ] );

  $atts = shortcode_atts(
    [
      'category' => 'maker-spaces',
    ],
    $atts,
    'maker-map'
  );

  // Unique ID for multiple maps on a page
  $map_id = 'maker-map-' . uniqid();

  // Enqueue Google Maps + custom script
  wp_enqueue_script(
    'google-maps-api',
    'https://maps.googleapis.com/maps/api/js?key=' . GOOGLE_MAPS_API_KEY,
    [],
    null,
    true
  );

  wp_enqueue_script(
    'markerclusterer',
    'https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js',
    [ 'google-maps-api' ],
    null,
    true
  );  

  wp_enqueue_script(
    'maker-map',
    MAKR_STYLESHEET_DIR_URI . 'lib/js/scripts/maker-map.js',
    [ 'google-maps-api' ],
    filemtime( MAKR_STYLESHEET_DIR . 'lib/js/scripts/maker-map.js' ),
    true
  );

  // Pass data to JS
  wp_localize_script(
    'maker-map',
    'makerMapData',
    [
      'endpoint' => esc_url( rest_url( 'makers/v1/locations' ) ),
      'category' => sanitize_text_field( $atts['category'] ),
      'mapId'    => $map_id,
    ]
  );

  // Output container
  return sprintf(
    '<style>.maker-map-filters {
  margin-bottom: 10px;
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  align-items: center;
}

.maker-filter-item {
  font-size: 0.9rem;
  white-space: nowrap;
}
</style>
    <div class="maker-map-wrapper">
       <div id="%1$s" class="maker-map" style="width:100%%;height:500px;"></div>
     </div>',
    esc_attr( $map_id ),
    selected( 'maker-spaces', $atts['category'], false )
  );

}
add_shortcode( 'maker-map', __NAMESPACE__ . '\\maker_map_shortcode' );
