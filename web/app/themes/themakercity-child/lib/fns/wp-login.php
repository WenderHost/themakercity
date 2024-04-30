<?php

namespace TheMakerCity\wplogin;

function custom_login_logo() {
  echo '
    <style type="text/css">
      #login h1 a, .login h1 a {
        background-image: url(' . get_stylesheet_directory_uri() . '/lib/img/maker-icon_512x512.png);
        padding-bottom: 30px;
        background-size: contain;
        width: 100%;
        height: 72px;
      }
    </style>
  ';
}
add_action( 'login_enqueue_scripts', __NAMESPACE__ . '\\custom_login_logo' );

function custom_login_redirect( $redirect_to, $request, $user ) {
  // Specify the default URL to redirect to after login.
  // Replace 'http://www.yourwebsite.com/custom-dashboard' with your desired URL.
  $custom_redirect_url = home_url( '/profile/'); //'http://www.yourwebsite.com/custom-dashboard';

  // Security check to verify if $user is a WP_User object.
  // If there's no user, or if logging in failed, don't change the redirect.
  if ( !is_a( $user, 'WP_User' ) ) {
    return $redirect_to;
  }

  // Optionally, you can determine the redirection URL based on user roles or specific user checks.
  // Example for redirecting based on user role:
  // if ( in_array( 'administrator', (array) $user->roles ) ) {
  //   // Redirect administrators to the default WordPress Dashboard.
  //   $custom_redirect_url = admin_url();
  // } else {
  //   // Non-administrator users are redirected elsewhere.
  //   $custom_redirect_url = home_url('/custom-page');
  // }

  // Return the custom redirect URL.
  return $custom_redirect_url;
}
add_filter( 'login_redirect', __NAMESPACE__ . '\\custom_login_redirect', 10, 3 );

function send_password_reset_email( $user_email ) {
  // Ensure the email address is provided.
  if ( empty( $user_email ) ) {
    return new \WP_Error( 'no_email_provided', 'No email address provided.' );
  }

  // Check if any user exists with the provided email address.
  $user = get_user_by( 'email', $user_email );
  if ( !$user ) {
    return new \WP_Error( 'no_user_found', 'No user found with that email address.<ol style="margin-top: 1em;"><li>If you already have a Maker Profile, <a href="mailto:support@themarkercity.org?subject=Lookup%20My%20Account">email us</a>, and we can lookup the email address associated with your profile.</li><li>Otherwise, continue with Step 2 below.</li></ol>' );
  }

  // Attempt to send the password reset email.
  $result = retrieve_password( $user_email );

  if ( is_wp_error( $result ) ) {
    // Return WP_Error object if there was an error sending the reset email.
    return $result;
  } else {
    // Return true if the password reset email was sent successfully.
    return true;
  }
}

/**
 * Filters the message sent when a user resets his/her password.
 *
 * @param      string  $message     The message
 * @param      string  $key         The key
 * @param      string  $user_login  The user login
 * @param      object  $userdata    The userdata
 *
 * @return     string  The filtered message.
 */
function filter_password_reset_message( $message, $key, $user_login, $userdata ){
  // Generate a password reset link
  $reset_key = get_password_reset_key( $userdata );
  $reset_link = add_query_arg([
    'action' => 'rp',
    'key' => $reset_key,
    'login' => rawurlencode($user_login)
    ],
    network_site_url('/wp-login.php', 'https')
  );

  $message = get_field( 'email_copy_for_password_reset', 'option', false );
  $message = str_replace( ['{name}', '{username}', '{password_reset_link}'], [ $userdata->display_name, $user_login, $reset_link ], $message );

  // The following corrects for the behavior of the TinyMCE link
  // inserter if you provide a variable like {password_reset_link}
  // as the link value:
  if( stristr( $message, 'http://http') )
    $message = str_replace( 'http://http', 'http', $message );

  return $message;
}
add_filter( 'retrieve_password_message', __NAMESPACE__ . '\\filter_password_reset_message', 10, 4 );

