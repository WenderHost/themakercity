<?php
namespace TheMakerCity\shortcodes;

/**
 * Generates a list of Maker posts filtered by taxonomy attributes.
 *
 * Outputs a <ul> of Maker CPT items, with optional linking, filtered by
 * maker-category, maker-space-type, and maker-tag taxonomies. Filtering uses
 * AND logic across taxonomies and within each taxonomy’s slug list. The
 * `spacetype` attribute also supports the special keyword `all`, which returns
 * all Maker posts that have at least one maker-space-type term.
 *
 * @since 1.0.0
 *
 * @param array $atts {
 *     Optional. Shortcode attributes.
 *
 *     @type string $category   Comma-separated list of maker-category slugs.
 *     @type string $spacetype  Comma-separated list of maker-space-type slugs, or
 *                              the keyword `all` to match any maker-space-type term.
 *     @type string $tags       Comma-separated list of maker-tag slugs.
 *     @type bool   $link       Whether to link each item to its Maker post.
 *                              Defaults to true.
 * }
 *
 * @return string HTML markup for the Maker list, or a fallback message if no results.
 */
function maker_list_shortcode( $atts ) {
  $atts = shortcode_atts(
    [
      'category'  => '',
      'spacetype' => '',
      'tags'      => '',
      'link'      => true,
    ],
    $atts,
    'maker_list'
  );

  $tax_query = [];

  // maker-category
  if ( ! empty( $atts['category'] ) ) {
    $slugs = array_filter( array_map( 'trim', explode( ',', $atts['category'] ) ) );
    if ( ! empty( $slugs ) ) {
      $tax_query[] = [
        'taxonomy' => 'maker-category',
        'field'    => 'slug',
        'terms'    => $slugs,
        'operator' => 'AND',
      ];
    }
  }

	// maker-space-type
	if ( ! empty( $atts['spacetype'] ) ) {

	  // Special keyword: "all" → return Makers that have ANY maker-space-type term
	  if ( strtolower( $atts['spacetype'] ) === 'all' ) {
	    $tax_query[] = [
	      'taxonomy' => 'maker-space-type',
	      'field'    => 'slug',
	      'terms'    => get_terms(
	        [
	          'taxonomy'   => 'maker-space-type',
	          'fields'     => 'slugs',
	          'hide_empty' => false,
	        ]
	      ),
	      'operator' => 'IN', // match posts with ANY of the terms
	    ];

	  } else {
	    // Normal behavior: comma-separated list using AND logic
	    $slugs = array_filter( array_map( 'trim', explode( ',', $atts['spacetype'] ) ) );

	    if ( ! empty( $slugs ) ) {
	      $tax_query[] = [
	        'taxonomy' => 'maker-space-type',
	        'field'    => 'slug',
	        'terms'    => $slugs,
	        'operator' => 'AND',
	      ];
	    }
	  }
	}


  // maker-tag
  if ( ! empty( $atts['tags'] ) ) {
    $slugs = array_filter( array_map( 'trim', explode( ',', $atts['tags'] ) ) );
    if ( ! empty( $slugs ) ) {
      $tax_query[] = [
        'taxonomy' => 'maker-tag',
        'field'    => 'slug',
        'terms'    => $slugs,
        'operator' => 'AND',
      ];
    }
  }

  $query_args = [
    'post_type'      => 'maker',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'order'					 => 'ASC',
    'orderby'				 => 'title',
  ];

  if ( ! empty( $tax_query ) ) {
    $tax_query['relation'] = 'AND';
    $query_args['tax_query'] = $tax_query;
  }

  $makers = new \WP_Query( $query_args );

  if ( ! $makers->have_posts() ) {
    return 'No makers found.';
  }

  $output = '<ul>';

  while ( $makers->have_posts() ) {
    $makers->the_post();

    $title = get_the_title();
    $link  = get_permalink();

    if ( filter_var( $atts['link'], FILTER_VALIDATE_BOOLEAN ) ) {
      $output .= '<li><a href="' . esc_url( $link ) . '">' . esc_html( $title ) . '</a></li>';
    } else {
      $output .= '<li>' . esc_html( $title ) . '</li>';
    }
  }

  wp_reset_postdata();

  $output .= '</ul>';

  return $output;
}
add_shortcode( 'maker_list', __NAMESPACE__ . '\\maker_list_shortcode' );
