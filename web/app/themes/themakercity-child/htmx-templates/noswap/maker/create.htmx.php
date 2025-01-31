<?php
// No direct access.
defined('ABSPATH') || exit('Direct access not allowed.');

// Check if nonce is valid.
if ( ! isset( $_SERVER['HTTP_X_WP_NONCE'] ) || ! wp_verify_nonce( $_SERVER['HTTP_X_WP_NONCE'], 'hxwp_nonce') ) {
  hxwp_die('Nonce verification failed.');
}

// Action = htmx_login
//*
if ( ! isset( $hxvals['action'] ) || $hxvals['action'] != 'htmx_maker_create') {
  hxwp_die('Invalid action. $hxvals[\'action\'] = ' . $hxvals['action'] );
}
/**/

use function TheMakerCity\makercpt\{create_maker_cpt,maker_email_exists};

/**
 * Build an array of Maker data.
 *
 * @var        array
 */
$maker_vars = array( 'business_name', 'name', 'email', 'collaborator', 'categories',  'description', 'primary_image' );
foreach( $maker_vars as $key ){
  switch( $key ){
    case 'primary_image':
      $maker['primary_image'] = ( isset( $_FILES['maker_primary_image'] ) ) ? $_FILES['maker_primary_image'] : null ;
      break;

    default:
      $maker[$key] = ( array_key_exists( 'maker_' . $key, $hxvals ) ) ? $hxvals[ 'maker_' . $key ] : null ;
  }

}

/**
 * Validate $maker, initialize our WP_Error object
 *
 * @var        WP_Error
 */
$errors = new WP_Error();

/**
 * STEP 1: Validate $maker['email']
 *
 * 1. Do any WordPress users exist with this email as the value for their `username` or `email`?
 * 2. Check if email exists as a value for any "draft" Maker CPTs.
 *
 * NOTES:
 * - For #2 above, we are checking "draft" Maker CPTs because upon publication, user accounts
 *   get created from that data.
 */
if( ! empty( $maker['email'] ) ){
  if( email_exists( $maker['email'] ) ){
    $errors->add( 'maker_email_exists', '<strong>Email Exists</strong><br>The <strong>email address</strong> you have provided already exists in our system. Please <a href="' . home_url('/sign-up/') . '">reset your password</a> to access your Maker profile.' );
  } else {
    if( maker_email_exists( $maker['email'] ) ){
      $errors->add( 'maker_profile_email_exists', '<strong>Already submitted your profile?</strong><br>We have an unapproved Maker Profile using the email address you have provided. If you\'ve recently submitted your Maker Profile, please allow 3-5 business days for our team to review and approve your profile. Otherwise, <a href="' . home_url( '/contact/' ) . '">contact us</a> if you have any questions.' );
    }
  }
}

/**
 * STEP 2: Validate Required fields
 */
foreach ( $maker as $key => $value ) {
  switch( $key ){
    case 'logo':
      // nothing, not required
      break;

    default:
      if( empty( $value ) ){
        $field_name = ucwords( str_replace( '_', ' ', $key ) );
        $errors->add( $key . '_required', sprintf( '<strong>%s</strong> can not be empty.', $field_name ) );
      }
  }
}

/**
 * FINAL STEP: Show Errors or Create the Maker CPT:
 */
if( $errors->has_errors() ){
  $error_messages = array();
  foreach( $errors->get_error_messages() as $error_message ){
    $error_messages[] = $error_message;
  }
  $json_response = [
    'createMaker' => [
      'css' => 'alert-danger',
      'message' => '<h2 class="fs-3 fw-bold">Apologies, we found some errors...</h2><p>Please correct the following errors:</p><ul><li>' . implode( '</li><li>', $error_messages ) . '</li></ul>',
    ],
  ];
} else {
  //*
  $post_id = create_maker_cpt( $maker );
  if( ! is_wp_error( $post_id ) ){
    /**
     * If we have successfully created a Maker CPT, we provide
     * the following hook.
     *
     * Current used in:
     * - /lib/fns/admins.php::notify_admin_new_maker_profile()
     */
    do_action( 'themakercity/after_maker_create', $post_id );
  }
  /**/
  $json_response = [
    'createMaker' => [
      'css' => 'alert-success',
      'message' => '<h2 class="fs-3 fw-bold">Thank You for Your Submission!</h2><p>We have received your Maker Profile submission. Please allow us time to review and approve your profile (<em>we\'re usually pretty fast, but give us a day or two &endash; just in case</em>). Once approved, we\'ll send you an email with the details.</p>',
    ],
    'resetFilePond' => [],
  ];
}
header( 'HX-Trigger: ' . json_encode( $json_response ) );
