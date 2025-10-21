<?php
/**
 * Register REST API route for Maker CPT locations.
 */
add_action( 'rest_api_init', function () {
  register_rest_route(
    'makers/v1',
    '/locations',
    [
      'methods'             => 'GET',
      'callback'            => 'get_maker_locations',
      'permission_callback' => '__return_true', // public access
      'args'                => [
        'maker-category' => [
          'description' => __( 'Slug of maker-category taxonomy term', 'textdomain' ),
          'type'        => 'string',
          'required'    => false,
          'default'     => 'maker-spaces',
        ],
      ],
    ]
  );
} );

/**
 * Callback for makers/v1/locations endpoint.
 *
 * @param WP_REST_Request $request The REST API request.
 * @return array List of maker posts with location data.
 */
function get_maker_locations( WP_REST_Request $request ) {
  $category_slug = sanitize_text_field( $request->get_param( 'maker-category' ) );

  $query = new WP_Query( [
    'post_type'      => 'maker',
    'posts_per_page' => -1,
    'tax_query'      => [
      [
        'taxonomy' => 'maker-category',
        'field'    => 'slug',
        'terms'    => $category_slug,
      ],
    ],
  ] );

  $results = [];

  if ( $query->have_posts() ) {
    while ( $query->have_posts() ) {
      $query->the_post();
      $map_field = get_field( 'business_address' ); // adjust this to your ACF field name

      if ( $map_field && isset( $map_field['lat'], $map_field['lng'] ) ) {
        $results[] = [
          'id'      => get_the_ID(),
          'title'   => get_the_title(),
          'link'    => get_permalink(),
          'lat'     => (float) $map_field['lat'],
          'lng'     => (float) $map_field['lng'],
          'address' => $map_field['address'] ?? '',
        ];
      }
    }
    wp_reset_postdata();
  }

  return $results;
}
