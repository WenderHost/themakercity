<?php
namespace TheMakerCity\shortcodes;
use function TheMakerCity\utilities\{get_alert,is_elementor_edit_mode};

/**
 * Shortcode: [maker-map]
 *
 * Displays a Google Map populated with Maker CPTs that have ACF map data.
 * Data is pulled from the custom REST API endpoint /makers/v1/locations.
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML for the map container.
 */
function maker_map_shortcode( $atts ) {

  if ( empty( GOOGLE_MAPS_API_KEY ) ) {
    return get_alert( [
      'type'        => 'warning',
      'description' => 'GOOGLE_MAPS_API_KEY not found. Please set <code>GOOGLE_MAPS_API_KEY</code> in your <code>.env</code>. <code>GOOGLE_MAPS_API_KEY = ' . GOOGLE_MAPS_API_KEY . '</code>'
    ] );
  }

  // No more category attribute — cleaning this up
  $atts = shortcode_atts(
    [
      'height' => 500,
    ],
    $atts,
    'maker-map'
  );
  $height = ( is_numeric( $atts['height'] ) )? $atts['height'] : 500 ;

  // Unique ID for multiple maps on a page
  $map_id = 'maker-map-' . uniqid();

  // Enqueue Google Maps + custom script
  wp_enqueue_script(
    'google-maps-api',
    'https://maps.googleapis.com/maps/api/js?key=' . GOOGLE_MAPS_API_KEY . '&libraries=marker&map_ids=6bc30825e5dd2d0987897fd0',
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
      'mapId'    => $map_id,
    ]
  );

  // Elementor admin preview skeleton
  if ( is_elementor_edit_mode() ) {
    return '
      <div class="maker-map-skeleton" 
           style="
             width:100%;
             height:' . intval( $height ) . 'px;
             display:flex;
             align-items:center;
             justify-content:center;
             background:#f9f9f9;
             border:1px dashed #c8c8c8;
             color:#555;
             font-size:16px;
             font-style:italic;
           ">
         Maker Spaces Map (height: ' . intval( $height ) . 'px)
      </div>
    ';
  }


  // Output container
  return sprintf(
    '<div class="maker-map-wrapper">
        <div id="%1$s" class="maker-map" style="width:100%%;height:%2$dpx;"></div>
     </div>',
    esc_attr( $map_id ),
    $height
  );
}

add_shortcode( 'maker-map', __NAMESPACE__ . '\\maker_map_shortcode' );
