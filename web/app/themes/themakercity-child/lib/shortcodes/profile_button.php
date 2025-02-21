<?php
namespace TheMakerCity\shortcodes;

/**
 * Gets the profile button text.
 *
 * @return     string  The profile button text.
 */
function get_profile_button_text(){
  return ( is_user_logged_in() )? 'Edit Your Profile' : 'Maker Log In' ;
}
add_shortcode( 'profile_button_text', __NAMESPACE__ . '\\get_profile_button_text' );

/**
 * Returns links for accessing your Maker Profile
 *
 * @return     string  The profile links.
 */
function get_profile_links(){
  if( is_user_logged_in() ){
    $links = '<a href="' . home_url( '/profile-editor/' ) . '">Edit Your Profile</a> &bull; <a href="' . wp_logout_url( home_url() ) . '">Log Out</a>';
  } else {
    $links = '<a href="' . home_url( '/sign-in/' ) . '">Maker Log In</a>';
  }
  return '<div class="profile-links">' . $links . '</div>';
}
add_shortcode( 'profile_links', __NAMESPACE__ . '\\get_profile_links' );