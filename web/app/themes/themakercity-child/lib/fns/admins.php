<?php

namespace TheMakerCity\admins;

/**
 * Notifies an admin of a new maker profile.
 *
 * @param      int  $post_id  The Post ID
 */
function notify_admin_new_maker_profile( int $post_id ):void {

  if ( 'maker' == get_post_type( $post_id ) ) {
    // Prepare email data
    $to = get_bloginfo('admin_email'); // Get the admin email

    // Get Maker Details
    $name = get_field( 'name', $post_id );
    $email = get_field( 'email', $post_id );
    $business_name = get_the_title( $post_id );

    $subject = 'New Maker Application for ' . $name;
    $body = '<p>' . $name . ' &lt;<a href="mailto:' . $email . '">' . $email . '</a>&gt; has applied to be listed in the Maker Directory. <a href="' . get_permalink( $post_id ) .'">Click Here</a> to view this maker\'s profile for ' . $business_name . '.</p><p>To approve the maker, simply "Publish" their profile. Then the system will make their profile live, setup their login, and send the maker an email.</p>';
    $headers = array('Content-Type: text/html; charset=UTF-8');

    // Get all admin emails
    $admin_emails = get_admin_emails();
    // Remove the primary admin email if it's in the list to avoid duplication
    $admin_emails = array_diff($admin_emails, array($to));
    // If there are other admin emails, add them as CC
    if (!empty($admin_emails)) {
      $headers[] = 'Cc: ' . implode(', ', $admin_emails);
    }
    
    // Send the email
    wp_mail( $to, $subject, $body, $headers );

    // Optionally, add any other actions you wish to perform on form submission
  }
}
add_action('themakercity/after_maker_create', __NAMESPACE__ . '\\notify_admin_new_maker_profile' );

/**
 * Returns an array of admin emails.
 *
 * @return     array  The admin emails.
 */
function get_admin_emails():array {
  $admin_emails = array();
  $args = array(
    'role'    => 'administrator',
    'fields'  => array('user_email')
  );
  $admin_users = get_users($args);

  foreach ($admin_users as $user) {
    $admin_emails[] = $user->user_email;
  }

  return $admin_emails;
}
