<?php

namespace TheMakerCity\users;

/**
 * Adds user roles.
 */
function add_user_roles() {
  add_role( 'unapproved', 'Unapproved', [] );
  add_role( 'maker', 'Maker', [] );
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
      'user_pass'     => wp_generate_password(),
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

/**
 * Redirect `maker` users upon login.
 *
 * @param      string  $redirect_to  The redirect to URL
 * @param      obj     $request      The request object
 * @param      obj     $user         The user object
 *
 * @return     string  The Redirect URL
 */
function custom_login_redirect( $redirect_to, $request, $user ) {
  // Is there a user to check?
  if ( isset( $user->roles ) && is_array( $user->roles ) ) {
    // Check if the user has the 'maker' role
    if ( in_array( 'maker', $user->roles ) ) {
      // Redirect them to the '/profile/' page
      return home_url( '/profile/' );
    } else {
      // Otherwise, return the default redirect_to location
      return $redirect_to;
    }
  } else {
    // If no user data is available, return default redirect_to location
    return $redirect_to;
  }
}
add_filter( 'login_redirect', __NAMESPACE__ . '\\custom_login_redirect', 10, 3 );

function send_reset_link_on_role_update($user_id, $role, $old_roles) {
  // Check if the new role is 'maker' and if one of the old roles was 'unapproved'
  if ( 'maker' === $role && in_array( 'unapproved', $old_roles ) ) {
    $user_info = get_userdata( $user_id );
    $user_email = $user_info->user_email;
    $user_login = $user_info->user_login;

    // Generate a password reset link
    $reset_key = get_password_reset_key( $user_info );
    $reset_link = add_query_arg([
      'action' => 'rp',
      'key' => $reset_key,
      'login' => rawurlencode($user_login)
      ],
      network_site_url('wp-login.php', 'login')
    );

    // Email subject and message
    $subject = ( 'development' == WP_ENV )? 'Welcome to Maker City' : 'Welcome to The Maker City Directory - Reset Your Password' ;
    $message = "Your access to The Maker City Directory has been approved! To set your new password, please visit the following link:\n\n\n";
    $message .= '<table align=\'center\' style=\'text-align:center\'><tr><td align=\'center\' style=\'text-align:center; font-size: 18px; font-weight: bold;\'><a href="' . $reset_link . '">Set Your Password</a></td></tr></table>';
    $message .= "\n\nThis link will expire in 24 hours.";

    // Send the email
    wp_mail( $user_email, $subject, $message );
  }
}
add_action('set_user_role', __NAMESPACE__ . '\\send_reset_link_on_role_update', 10, 3);

/**
 * Handles our custom save routines when saving a Maker CPT.
 *
 * @param      int  $post_id  The Post ID
 */
function custom_save_maker_post( $post_id ) {
  // Check if this is a frontend form submission
  if ( ! is_admin() && 'maker' == get_post_type( $post_id ) && 'publish' == get_post_status( $post_id ) ) {

    $user_id = get_current_user_id();
    $maker_profile_id_exists = metadata_exists( 'user', $user_id, 'maker_profile_id' );

    /**
     * MAKER PROFILE ID (user_meta:maker_profile_id)
     *
     * A Maker user's `maker_profile_id` coresponds to the $post_id
     * of his/her Maker CPT (i.e. his/her Maker profile).
     *
     * If the user doesn't have a `maker_profile_id`, create one. Or
     * check to make sure the user is editing his/her Maker profile,
     * and BAIL if not:
     */
    if( ! $maker_profile_id_exists ){
      add_user_meta( $user_id, 'maker_profile_id', $post_id, true );
    } else {
      // Bail if user isn't editing his/her Maker profile:
      $maker_profile_id = get_user_meta( $user_id, 'maker_profile_id', true );
      if( $maker_profile_id != $post_id )
        return;
    }

    /**
     * MAKER PROFILE $post_title
     *
     * Set the $post_title for the Maker CPT to the same value as
     * the `name` field.
     */
    $profile_name = get_post_meta( $post_id, 'name', true );
    if( ! empty( $profile_name ) ){
      $post_data = [
        'ID'          => $post_id,
        'post_title'  => $profile_name,
        'post_name'   => sanitize_title( $profile_name ),
      ];

      remove_action( 'acf/save_post', __NAMESPACE__ . '\\custom_save_maker_post', 20 );
      wp_update_post( $post_data );
      add_action( 'acf/save_post', __NAMESPACE__ . '\\custom_save_maker_post', 20 );
    }
  }
}
//add_action( 'acf/save_post', __NAMESPACE__ . '\\custom_save_maker_post', 20 );
