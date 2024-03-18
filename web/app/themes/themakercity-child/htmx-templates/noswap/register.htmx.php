<?php
// No direct access.
defined('ABSPATH') || exit('Direct access not allowed.');

// Check if nonce is valid.
if (!isset($_SERVER['HTTP_X_WP_NONCE']) || !wp_verify_nonce($_SERVER['HTTP_X_WP_NONCE'], 'hxwp_nonce')) {
  hxwp_die('Nonce verification failed.');
}

if ( ! isset( $hxvals['action'] ) || $hxvals['action'] != 'htmx_register') {
  hxwp_die('Invalid action. $hxvals[\'action\'] = ' . $hxvals['action'] );
}

use function TheMakerCity\users\{create_user};

$email = ( is_array( $hxvals ) && array_key_exists( 'email', $hxvals ) && is_email( $hxvals['email'] ) )? $hxvals['email'] : null ;
$name = ( is_array( $hxvals ) && array_key_exists( 'name', $hxvals ) )? strip_tags( $hxvals['name'] ) : null ;
$business_description = ( is_array( $hxvals ) && array_key_exists( 'business_description', $hxvals ) )? strip_tags( $hxvals['business_description'] ) : null ;

$user_id = create_user( $name, $email, $business_description );
if( ! is_wp_error( $user_id ) ){
  $user_data = get_userdata( $user_id );

  $unapproved_users_url = site_url( '/wp-admin/users.php?role=unapproved' );

  $business_description = get_user_meta( $user_id, 'business_description', true );
  wp_mail( get_option( 'admin_email' ) , 'New Maker: ' . $user_data->display_name, "<em>{$user_data->display_name}</em> has registered for a Maker Profile using the following details:\n\n<strong>Email:</strong> {$user_data->user_email}\n\n<strong>Business Description:</strong>\n{$business_description}\n\n<a href=\"{$unapproved_users_url}\">Click here</a> to approve/unapprove this user." );
  $json_response = [
    'newAccount' => [
      'css'     => 'alert-success',
      'message' => 'We have received your request for a Maker\'s Directory Profile. Someone on our staff will review your request and get back to you soon.'
    ],
    'resetRegistrationForm' => [],
  ];
  header( 'HX-Trigger: ' . json_encode( $json_response ) );
} else {
  $json_response = [
    'newAccount' => [
      'css'     => 'alert-danger',
      'message' => $user_id->get_error_message(),
    ],
  ];
  header( 'HX-Trigger: ' . json_encode( $json_response ) );
}