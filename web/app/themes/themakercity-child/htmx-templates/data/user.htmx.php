<?php
// No direct access.
defined( 'ABSPATH' ) || exit( 'Direct access not allowed.' );

// Check if nonce is valid.
if (! isset( $_SERVER['HTTP_X_WP_NONCE'] ) || ! wp_verify_nonce( $_SERVER['HTTP_X_WP_NONCE'], 'hxwp_nonce' ) ) {
  hxwp_die( 'Nonce verification failed.' );
}

$current_user = wp_get_current_user();
uber_log('🪵 $current_user->ID = ' . $current_user->ID );
if( $current_user ){
  $maker_profile_id = get_user_meta( $current_user->ID, 'maker_profile_id', true );
  echo $maker_profile_id;
}