<?php
namespace TheMakerCity\shortcodes;

/**
 * Retrieves the title of the current post and appends the event date (if available).
 *
 * This function uses the `get_the_title()` and `get_field()` functions to retrieve
 * the title and a custom field `event_date` associated with the current post. The
 * formatted title and date are returned as a single string.
 *
 * @global WP_Post $post The current post object.
 *
 * @return string The formatted title and event date, or just the title if the event date is empty.
 */
function get_title_and_date(){
	global $post;

	$title = get_the_title( $post );
	$date = get_field( 'event_date', $post->ID );
	if( ! empty( $date ) )
    $title.= ' &endash; ' . $date;
  return $title;
}
add_shortcode( 'title_and_date', __NAMESPACE__ . '\\get_title_and_date' );