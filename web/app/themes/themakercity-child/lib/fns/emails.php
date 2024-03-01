<?php

namespace TheMakerCity\emails;
use function TheMakerCity\templates\{render_template};

/**
 * Filter the email content to use a custom HTML template.
 *
 * @param array $args The original email arguments including 'to', 'subject', 'message', 'headers', 'attachments'.
 * @return array Filtered email arguments with custom HTML template.
 */
function wp_custom_email_template( $args ) {
  // Extracting the email data.
  $to = $args['to'];
  $subject = $args['subject'];
  $message = $args['message'];
  $headers = $args['headers'];
  $attachments = $args['attachments'];

  // Ensure $headers is an array.
  if ( !is_array( $headers ) ) {
    $headers = array();
  }

  // Check and set content type to HTML if not already set.
  $has_html_content_type = false;
  foreach ( $headers as $header ) {
    if ( strpos( strtolower( $header ), 'content-type: text/html' ) !== false ) {
      $has_html_content_type = true;
      break;
    }
  }
  if ( !$has_html_content_type ) {
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
  }

  // Prepare data for the template.
  $data = array(
    'title' => $subject,
    'content' => nl2br( $message ),
    // Add other data as needed.
  );

  // Retrieve our handlebars template:
  $email = render_template( 'email', $data );

  // Replace the original message with the template.
  $args['message'] = $email;
  $args['headers'] = $headers;

  return $args;
}

// Add the filter to wp_mail.
add_filter( 'wp_mail', __NAMESPACE__ . '\\wp_custom_email_template' );
