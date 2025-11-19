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
    ]
  );
} );

/**
 * Callback for makers/v1/locations endpoint.
 *
 * New Behavior:
 * - Retrieve ALL Maker CPTs that have ANY maker-space-type term
 * - Include:
 *      space_types (slugs)
 *      categories (slugs from maker-category)
 * - Include top-level maker-space-types list (all terms, including empty)
 *
 * @param \WP_REST_Request $request
 * @return array
 */
function get_maker_locations( \WP_REST_Request $request ) {

  /**
   * ---------------------------------------------------------
   * 1. Query ALL makers that have at least one maker-space-type term
   * ---------------------------------------------------------
   */
  $query = new \WP_Query( [
    'post_type'      => 'maker',
    'posts_per_page' => -1,
    'tax_query'      => [
      [
        'taxonomy' => 'maker-space-type',
        'operator' => 'EXISTS',  // return all makers with ANY space-type term
      ],
    ],
  ] );

  $makers = [];

  /**
   * ---------------------------------------------------------
   * 2. Build maker objects
   * ---------------------------------------------------------
   */
  if ( $query->have_posts() ) {
    while ( $query->have_posts() ) {
      $query->the_post();

      // Must have a valid business_address map field
      $map_field = get_field( 'business_address' );
      if ( ! $map_field || ! isset( $map_field['lat'], $map_field['lng'] ) ) {
        continue;
      }

      // maker-category (legacy)
      $cat_terms = get_the_terms( get_the_ID(), 'maker-category' );
      $categories = $cat_terms && ! is_wp_error( $cat_terms )
        ? wp_list_pluck( $cat_terms, 'slug' )
        : [];

      // maker-space-type (NEW primary taxonomy)
      $space_terms = get_the_terms( get_the_ID(), 'maker-space-type' );
      $space_types = $space_terms && ! is_wp_error( $space_terms )
        ? wp_list_pluck( $space_terms, 'slug' )
        : [];

      $makers[] = [
        'id'          => get_the_ID(),
        'title'       => get_the_title(),
        'link'        => get_permalink(),
        'lat'         => (float) $map_field['lat'],
        'lng'         => (float) $map_field['lng'],
        'address'     => $map_field['address'] ?? '',
        'categories'  => $categories,   // still included
        'space_types' => $space_types,  // used for filtering in JS
      ];
    }
    wp_reset_postdata();
  }

  /**
   * ---------------------------------------------------------
   * 3. Load ALL maker-space-type terms (including empty)
   * ---------------------------------------------------------
   */
  $space_type_terms = get_terms( [
    'taxonomy'   => 'maker-space-type',
    'hide_empty' => false,
  ] );

  $maker_space_types = [];

  if ( ! is_wp_error( $space_type_terms ) && ! empty( $space_type_terms ) ) {
    foreach ( $space_type_terms as $t ) {
      $maker_space_types[] = [
        'slug'  => $t->slug,
        'name'  => $t->name,
        'count' => (int) $t->count,
      ];
    }

    // Sort alphabetically by name
    usort( $maker_space_types, function( $a, $b ) {
      return strcasecmp( $a['name'], $b['name'] );
    });

    $maker_space_types = array_values( $maker_space_types );
  }

  /**
   * ---------------------------------------------------------
   * 4. Final Response
   * ---------------------------------------------------------
   */
  return [
    'maker-space-types' => $maker_space_types,
    'makers'            => $makers,
  ];
}
