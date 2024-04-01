<?php
// No direct access.
defined('ABSPATH') || exit('Direct access not allowed.');

// Check if nonce is valid.
if (!isset($_SERVER['HTTP_X_WP_NONCE']) || !wp_verify_nonce($_SERVER['HTTP_X_WP_NONCE'], 'hxwp_nonce')) {
  hxwp_die('Nonce verification failed.');
}

if ( ! isset( $hxvals['action'] ) || $hxvals['action'] != 'htmx_passwordreset') {
  hxwp_die('Invalid action. $hxvals[\'action\'] = ' . $hxvals['action'] );
}

use function TheMakerCity\wplogin\{send_password_reset_email};

$email = ( isset( $hxvals ) && array_key_exists( 'email', $hxvals ) )? $hxvals['email'] : false ;

if( $email ){
  $result = send_password_reset_email( $email );
  if( ! is_wp_error( $result ) ){
    header( 'HX-Trigger: {"passwordReset":{"css":"alert-success", "message": "Check your inbox. We have sent an email with instructions for resetting your password."}}' );
  } else if( is_wp_error( $result ) ) {
    $data = array(
      'passwordReset' => array(
        'css'     => 'alert-danger',
        'message' => $result->get_error_message(),
      ),
    );
    header( 'HX-Trigger: ' . json_encode( $data ) ); // {"passwordReset":{"css":"alert-danger", "message": "' . $result->get_error_message() . '"}}
  }
}