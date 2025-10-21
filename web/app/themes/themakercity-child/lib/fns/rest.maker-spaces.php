<?php
namespace TheMakerCity\rest;

/**
 * Register REST API route for Maker CPT locations.
 */
add_action( 'rest_api_init', function () {
  register_rest_route(
    'makers/v1',
    '/locations',
    [
      'methods'             => 'GET',
      'callback'            => __NAMESPACE__ . '\\get_maker_locations',
      'permission_callback' => '__return_true',
      'args'                => [
        'maker-category' => [
          'description' => __( 'Slug of maker-category taxonomy term', 'themakercity' ),
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
 * Returns all Maker CPTs within a parent term and any child terms.
 * Adds a `categories` array to each result containing all assigned term slugs.
 *
 * @param \WP_REST_Request $request The REST API request.
 * @return array List of maker posts with location data.
 */
function get_maker_locations( \WP_REST_Request $request ) {
  $category_slug = sanitize_text_field( $request->get_param( 'maker-category' ) );
  $taxonomy      = 'maker-category';
  $term_ids      = [];

  // Try to find the term
  $term = get_term_by( 'slug', $category_slug, $taxonomy );

  if ( $term ) {
    // Get all children recursively
    $children = get_term_children( $term->term_id, $taxonomy );

    if ( ! is_wp_error( $children ) && ! empty( $children ) ) {
      $term_ids = array_merge( [ $term->term_id ], $children );
    } else {
      $term_ids = [ $term->term_id ];
    }
  }

  // If no valid term found, bail early
  if ( empty( $term_ids ) ) {
    return [];
  }

  // Query Makers in parent + child terms
  $query = new \WP_Query( [
    'post_type'      => 'maker',
    'posts_per_page' => -1,
    'tax_query'      => [
      [
        'taxonomy'         => $taxonomy,
        'field'            => 'term_id',
        'terms'            => $term_ids,
        'include_children' => false,
      ],
    ],
  ] );

  $results = [];

  if ( $query->have_posts() ) {
    while ( $query->have_posts() ) {
      $query->the_post();

      $map_field = get_field( 'business_address' ); // Adjust if your ACF field has a different name
      if ( ! $map_field || ! isset( $map_field['lat'], $map_field['lng'] ) ) {
        continue;
      }

      // Collect all maker-category term slugs
      $terms       = get_the_terms( get_the_ID(), $taxonomy );
      $categories  = $terms && ! is_wp_error( $terms )
        ? wp_list_pluck( $terms, 'slug' )
        : [];

      $results[] = [
        'id'         => get_the_ID(),
        'title'      => get_the_title(),
        'link'       => get_permalink(),
        'lat'        => (float) $map_field['lat'],
        'lng'        => (float) $map_field['lng'],
        'address'    => $map_field['address'] ?? '',
        'categories' => $categories,
      ];
    }
    wp_reset_postdata();
  }

  return $results;
}
