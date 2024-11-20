<?php
// No direct access.
defined('ABSPATH') || exit('Direct access not allowed.');
uber_log('👉 Attempting to LOGIN...');
// Check if nonce is valid.
/*
if ( ! isset( $_SERVER['HTTP_X_WP_NONCE'] ) || ! wp_verify_nonce( $_SERVER['HTTP_X_WP_NONCE'], 'hxwp_nonce' ) ) {
  hxwp_die( 'Nonce verification failed.' );
}
/**/

// Action = htmx_login
//*
if ( ! isset( $hxvals['action'] ) || $hxvals['action'] != 'htmx_login') {
  hxwp_die('Invalid action. $hxvals[\'action\'] = ' . $hxvals['action'] );
}
/**/

$email = ( is_array( $hxvals ) && array_key_exists( 'email', $hxvals ) && is_email( $hxvals['email'] ) )? $hxvals['email'] : null ;
$password = ( is_array( $hxvals ) && array_key_exists( 'password', $hxvals ) )? $hxvals['password'] : null ;
$remember_me = ( is_array( $hxvals) && array_key_exists( 'remember-me', $hxvals ) )? (bool) $hxvals['remember-me'] : false ;

uber_log('👉 Received: ' . "\n\$email = $email");

$data = [];
$user = wp_signon( [ 'user_login' => $email, 'user_password' => $password, 'remember' => $remember_me ] );
$status = ( is_wp_error( $user ) )? 'fail' : 'success' ;
$action = ( 'success' == $status )? 'loginSuccess' : 'loginFail' ;

if( 'success' == $status ){
  //$data['message'] =  'Success! You\'re logged in. Redirecting...' ;
  //$data['redirect_url'] = home_url( '/profile/' );
  header('HX-Redirect: ' . home_url( '/profile/' ) );
} elseif( is_wp_error( $user ) ) {
  $err_codes = $user->get_error_codes();
  uber_log('$err_codes = ' . print_r( $err_codes,true ) );
  $errors = [];
  if ( in_array( 'invalid_email', $err_codes ) || in_array( 'empty_username', $err_codes ) ) {
    $errors[] = 'Check your username. Is it a valid email address?';
  }
  if ( in_array( 'invalid_username', $err_codes ) ) {
    $errors[] = 'Invalid username.';
  }
  if ( in_array( 'incorrect_password', $err_codes ) ) {
    $errors[] = 'Please check your password.';
  }
  $data['message'] = '<p>Please correct the following errors:</p> <ul><li>' . implode('</li><li>', $errors ) . '</li></ul>';
  ?>
<div class="alert alert-warning" id="login-message" role="alert">
  <div class="alert-message">
    <p>Please correct the following errors:</p>
    <ul>
      <li><?= implode( '</li><li>', $errors ) ?></li>
    </ul>
  </div>
</div>
  <?php
}

/**
 * Filter `hxwp/header_response` adding the HX-Trigger Event as an array key on the $response
 */
/*
add_filter( 'hxwp/header_response', function( $response, $action, $status, $data ){
  if( ! in_array( $action, ['loginSuccess','loginFail'] ) )
    return $response;

  switch( $action ){
    case 'loginSuccess':
      $response['loginSuccess'] = [
        'message' => $data['message'],
        'redirect_url' => $data['redirect_url'],
        'css' => 'alert-success',
      ];
      break;

    case 'loginFail':
      $response['loginFail'] = [
        'message' => $data['message'],
        'redirect_url' => false,
        'css' => 'alert-warning',
      ];
      break;
  }

  //$response[$action] = true;

  return $response;
}, 10, 4 );

hxwp_send_header_response(
  $status,
  $data,
  $action
);
/**/