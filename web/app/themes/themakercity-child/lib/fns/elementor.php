<?php

namespace TheMakerCity\elementor;

add_action( 'elementor/query/maker_directory', function( $query ){
  $query->set( 'orderby', 'post_title' );
  $query->set( 'order', 'ASC' );
});

add_action( 'elementor/query/related_makers', function( $query ) {
    // Ensure we are on a single Maker CPT page.
    if ( !is_singular( 'maker' ) ) {
        return;
    }

    // Get the current post ID.
    $current_post_id = get_the_ID();

    // Get the terms of the current maker in the 'maker-category' taxonomy.
    $terms = wp_get_post_terms( $current_post_id, 'maker-category', array( 'fields' => 'ids' ) );

    // Check if terms exist.
    if ( !empty( $terms ) && !is_wp_error( $terms ) ) {
        // Modify the query to get related makers.
        $query->set( 'post_type', 'maker' ); // Ensure we are querying for 'maker' CPT.
        $query->set( 'post__not_in', array( $current_post_id ) ); // Exclude the current maker from the query.
        $query->set( 'posts_per_page', 3 ); // Limit to three posts.
        $query->set( 'orderby', 'rand' ); // Order by random.

        // Set the tax_query to fetch makers that share any of the same 'maker-category' terms.
        $query->set( 'tax_query', array(
            array(
                'taxonomy' => 'maker-category',
                'field'    => 'term_id',
                'terms'    => $terms,
                'operator' => 'IN',
            ),
        ));
    }
} );

