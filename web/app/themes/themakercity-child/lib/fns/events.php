<?php

namespace TheMakerCity\events;

function custom_makers_event_callback( $query ) {
    // Get today's date in the correct format
    $today = current_time( 'Y-m-d H:i:s' );

    // Set up the WP_Query arguments
    $args = array(
        'post_type' => 'event', // Your custom post type
        'posts_per_page' => 1, // Retrieve only one event
        'meta_query' => array(
            array(
                'key' => 'date', // The custom field key
                'value' => $today, // Compare with today's date
                'compare' => '>=', // Get events today or in the future
                'type' => 'DATETIME' // Specify the type
            )
        ),
        'orderby' => 'meta_value', // Order by the meta value
        'order' => 'ASC' // Ascending order
    );

    // Set the query arguments
    $query->set( 'meta_query', $args['meta_query'] );
    $query->set( 'post_type', $args['post_type'] );
    $query->set( 'posts_per_page', $args['posts_per_page'] );
    $query->set( 'orderby', $args['orderby'] );
    $query->set( 'order', $args['order'] );
}

// Hook the custom function to the Elementor query
add_action( 'elementor/query/makers_event_query', __NAMESPACE__ . '\\custom_makers_event_callback' );

/**
 * Add "Event Date" column to the Admin list of Event CPT, before the "Date" column.
 */
function add_event_date_column( $columns ) {
  $new_columns = [];
  
  foreach ( $columns as $key => $value ) {
    // Insert the "Event Date" column before the "Date" column.
    if ( 'date' === $key ) {
      $new_columns['event_date'] = __( 'Event Date', 'textdomain' );
    }
    $new_columns[ $key ] = $value;
  }

  return $new_columns;
}
add_filter( 'manage_event_posts_columns', __NAMESPACE__ . '\\add_event_date_column' );

/**
 * Populate the "Event Date" column with data.
 */
function display_event_date_column( $column, $post_id ) {
  if ( 'event_date' === $column ) {
    $event_date = get_field( 'event_date', $post_id ); // Replace 'date' with your actual ACF field key if needed.
    if ( $event_date ) {
      echo date( 'M j, Y - g:ia', strtotime( $event_date ) );
    } else {
      echo __( 'No Date Set', 'textdomain' );
    }
  }
}
add_action( 'manage_event_posts_custom_column', __NAMESPACE__ . '\\display_event_date_column', 10, 2 );

/**
 * Make "Event Date" column sortable.
 */
function make_event_date_column_sortable( $columns ) {
  $columns['event_date'] = 'event_date';
  return $columns;
}
add_filter( 'manage_edit-event_sortable_columns', __NAMESPACE__ . '\\make_event_date_column_sortable' );

/**
 * Modify the query to sort by the ACF "date" field.
 */
function sort_event_by_date( $query ) {
  if ( ! is_admin() || ! $query->is_main_query() ) {
    return;
  }

  if ( 'event_date' === $query->get( 'orderby' ) ) {
    $query->set( 'meta_key', 'event_date' ); 
    $query->set( 'orderby', 'meta_value' );
    $query->set( 'meta_type', 'DATETIME' );
  }
}
add_action( 'pre_get_posts', __NAMESPACE__ . '\\sort_event_by_date' );
