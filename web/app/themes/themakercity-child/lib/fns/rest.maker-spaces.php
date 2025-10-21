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
 * Adds:
 *  - `categories`: array of all maker-category slugs assigned to each Maker
 *  - top-level `maker-filters`: list of populated child terms with counts
 *
 * @param \WP_REST_Request $request The REST API request.
 * @return array List of makers and filter metadata.
 */
function get_maker_locations( \WP_REST_Request $request ) {
  $category_slug = sanitize_text_field( $request->get_param( 'maker-category' ) );
  $taxonomy      = 'maker-category';
  $term_ids      = [];
  $maker_filters = [];

  // Try to find the parent term
  $term = get_term_by( 'slug', $category_slug, $taxonomy );

  if ( $term ) {
    // Get all children recursively
    $children = get_term_children( $term->term_id, $taxonomy );

    if ( ! is_wp_error( $children ) && ! empty( $children ) ) {
      $term_ids = array_merge( [ $term->term_id ], $children );

      // Build maker-filters array (children only, hide empty)
      $filter_terms = get_terms( [
        'taxonomy'   => $taxonomy,
        'include'    => $children,
        'hide_empty' => true, // ✅ show only terms with Makers
      ] );

      if ( ! is_wp_error( $filter_terms ) && ! empty( $filter_terms ) ) {
        $maker_filters = array_map(
          function ( $t ) {
            return [
              'slug'  => $t->slug,
              'name'  => sprintf( '%s (%d)', $t->name, $t->count ), // ✅ append count
              'count' => (int) $t->count,
            ];
          },
          $filter_terms
        );
        
        // ✅ Ensure JSON encodes as a proper array, not object
        $maker_filters = array_values( $maker_filters );        
      }
    } else {
      $term_ids = [ $term->term_id ];
    }
  }

  // If no valid term found, bail early
  if ( empty( $term_ids ) ) {
    return [
      'maker-filters' => [],
      'makers'        => [],
    ];
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

  $makers = [];

  if ( $query->have_posts() ) {
    while ( $query->have_posts() ) {
      $query->the_post();

      $map_field = get_field( 'business_address' );
      if ( ! $map_field || ! isset( $map_field['lat'], $map_field['lng'] ) ) {
        continue;
      }

      // Collect all maker-category term slugs
      $terms      = get_the_terms( get_the_ID(), $taxonomy );
      $categories = $terms && ! is_wp_error( $terms )
        ? wp_list_pluck( $terms, 'slug' )
        : [];

      $makers[] = [
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

  // Final structured response
  return [
    'maker-filters' => $maker_filters,
    'makers'        => $makers,
  ];
}
