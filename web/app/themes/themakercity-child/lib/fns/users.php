<?php

namespace TheMakerCity\users;

/**
 * Adds user roles.
 */
function add_user_roles() {
  add_role( 'unapproved', 'Unapproved', [] );
}
add_action( 'init', __NAMESPACE__ . '\\add_user_roles' );

/**
 * Creates a user.
 *
 * @param      string  $name                  The user's name
 * @param      string  $email                 The email
 * @param      string  $business_description  The business description
 *
 * @return     $user_id/WP_Error       The new user's ID.
 */
function create_user( $name, $email, $business_description ) {
  // Ensure the user does not already exist
  if ( ! email_exists( $email ) ) {
    $user_data = array(
      'user_login'    => $email,
      'user_email'    => $email,
      'display_name'  => $name,
      'role'          => 'unapproved', // Set the user role to "unapproved"
      'user_pass'     => null,
    );

    $user_id = wp_insert_user( $user_data );

    // Check for errors
    if ( ! is_wp_error( $user_id ) ) {
      // If no error, set the user's business description
      update_user_meta( $user_id, 'business_description', $business_description );

      return $user_id;
    } else {
      return new \WP_Error( 'user_creation_failed', $user_id->get_error_message() );
    }
  } else {
    // User already exists
    return new \WP_Error( 'user_exists', 'A user with the email ' . $email . ' already exists.' );
  }
}

