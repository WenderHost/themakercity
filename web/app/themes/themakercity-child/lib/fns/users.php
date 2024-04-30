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
 * Creates a maker user.
 *
 * @param      object  $post   The Maker CPT post object.
 *
 * @return     int       The User ID.
 */
function create_maker_user( $post ){
  if( is_null( $post ) )
    return new \WP_Error( 'not_a_post_obj', 'Received a `null` $post object.' );

  if( 'maker' !== $post->post_type )
    return new \WP_Error( 'not_a_maker_cpt', 'The provided $post is not a Maker CPT object.' );

  $maker_post_handled = get_post_meta( $post->ID, '_maker_publish_handled', true );
  // Convert the meta value to boolean explicitly
  $is_handled = ! empty( $maker_publish_handled ) && 'false' !== $maker_publish_handled;
  if( ! $is_handled && 'publish' == $post->post_status ){
    $name   = get_post_meta( $post->ID, 'name', true );
    $email  = get_post_meta( $post->ID, 'email', true );

    if( email_exists( $email ) )
      return new \WP_Error( 'email_exists', 'The supplied email is in use by a WP User.' );

    $user_data = array(
      'user_login'    => $email,
      'user_email'    => $email,
      'display_name'  => $name,
      'role'          => 'maker', // Set the user role to "maker"
      'user_pass'     => wp_generate_password(),
    );
    $user_id = wp_insert_user( $user_data );
    if( is_wp_error( $user_id ) )
      return new \WP_Error( 'user_creation_failed', $user_id->get_error_message() );

    add_user_meta( $user_id, 'maker_profile_id', $post->ID, true );

    // Set the Maker Profile's post_author to this user
    $update_post = wp_update_post( ['ID' => $post->ID, 'post_author' => $user_id ], true );

    // START Email User
    $user_info = get_userdata( $user_id );
    $user_email = $user_info->user_email;
    $user_login = $user_info->user_login;
    $profile_link = get_permalink( $post->ID );

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
    $subject = ( 'development' == WP_ENV )? 'Welcome to Maker City' : 'Welcome to The Maker City Directory!' ;

    $message = get_field( 'email_copy_for_new_user_new_listing', 'option', false );
    $message = str_replace( ['{name}', '{username}', '{password_reset_link}', '{profile_link}'], [ $user_info->display_name, $user_login, $reset_link, $profile_link ], $message );

    // The following corrects for the behavior of the TinyMCE link
    // inserter if you provide a variable like {password_reset_link}
    // as the link value:
    if( stristr( $message, 'http://http') )
      $message = str_replace( 'http://http', 'http', $message );

    // Send the email
    wp_mail( $user_email, $subject, $message );
    // END Email User

    return $user_id;
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

/**
 * Sends a Maker user a new account email when their status is changed from "Subscriber" to "Maker".
 *
 * @param      int  $user_id       The user ID.
 * @param      string  $role       The role
 * @param      array  $old_roles   The old roles
 */
function send_reset_link_on_role_update($user_id, $role, $old_roles) {
  // Check if the new role is 'maker' and if one of the old roles was 'unapproved'
  if ( 'maker' === $role && in_array( 'subscriber', $old_roles ) ) {
    $user_info = get_userdata( $user_id );
    $user_email = $user_info->user_email;
    $user_login = $user_info->user_login;
    $display_name = $user_info->display_name;

    // Generate a password reset link
    $reset_key = get_password_reset_key( $user_info );
    $reset_link = add_query_arg([
      'action' => 'rp',
      'key' => $reset_key,
      'login' => rawurlencode($user_login)
      ],
      network_site_url('/wp-login.php', 'https')
    );

    // Email subject and message
    $subject = ( 'development' == WP_ENV )? 'Give your page a refresh!' : 'Give your page a refresh! [The Maker City]' ;

    $message = get_field( 'email_copy_for_existing_listing_new_user', 'option', false );
    $message = str_replace( ['{name}', '{username}', '{password_reset_link}'], [ $display_name, $user_login, $reset_link ], $message );

    // The following corrects for the behavior of the TinyMCE link
    // inserter if you provide a variable like {password_reset_link}
    // as the link value:
    if( stristr( $message, 'http://http') )
      $message = str_replace( 'http://http', 'http', $message );

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
    /*
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
    /**/
  }
}
add_action( 'acf/save_post', __NAMESPACE__ . '\\custom_save_maker_post', 20 );

function custom_save_maker_validation() {
  // Check if the email field is set and not empty.
  if ( isset( $_POST['acf']['field_657b6a41e0962'] ) && ! empty( $_POST['acf']['field_657b6a41e0962'] ) ) {
    $email = $_POST['acf']['field_657b6a41e0962'];

    // Use WP function to check if a user exists with this email.
    if ( email_exists( $email ) ) {
      // Add a custom validation error.
      acf_add_validation_error('acf[field_657b6a41e0962]', 'A Maker with this email already exists.');
      //acf_add_validation_error('acf[field_657b6a41e0962]', 'A Maker with this email (' . $email . ') already exists. If this is your email, try <a href="' . home_url( '/sign-up/' ) . '">resetting your password</a>.');
    }
  }
}
//add_filter('acf/validate_save_post', __NAMESPACE__ . '\\custom_save_maker_validation', 10, 0);

function validate_maker_email( $valid, $value, $field, $input_name ){
  $printr_field = print_r( $field, true );
  uber_log("🪵 Running validate_maker_email( $valid, $value, $printr_field, $input_name );");
  if( $valid !== true ){
    return $valid;
  }

  if( email_exists( $value ) ){
    return __( 'A Maker with this email already exists.' );
  }

  return $valid;
}
//add_filter( 'acf/validate_value/key=field_657b6a41e0962', __NAMESPACE__ . '\\validate_maker_email', 10, 4 );

