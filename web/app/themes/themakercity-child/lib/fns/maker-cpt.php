<?php

namespace TheMakerCity\makercpt;
use function TheMakerCity\users\create_maker_user;

/**
 * Creates a Maker CPT
 *
 * @param      array     $maker  The maker
 *
 * @return     mixed     The Maker $post_id, or  The WP_Error.
 */
function create_maker_cpt( $maker = array() ){
  if( is_array( $maker ) ){
    if ( empty( $maker['business_name'] ) ) {
      return new \WP_Error('invalid_data', 'Missing required maker information.');
    }

    // Step 1: Create a new post of type 'maker'
    $post_data = array(
      'post_title'    => wp_strip_all_tags( $maker['business_name'] ),
      'post_content'  => '',  // Assuming the content is not set at creation.
      'post_status'   => 'pending',  // or 'draft' depending on your requirement
      'post_type'     => 'maker'
    );

    $post_id = wp_insert_post($post_data);

    // Check if post was created successfully
    if ( is_wp_error($post_id) ) {
      return $post_id; // Return the error
    }

    // Step 2: Update ACF fields
    if ( function_exists('update_field') ) {
      update_field( 'name', $maker['name'], $post_id );
      update_field( 'email', $maker['email'], $post_id );
      update_field( 'collaborator', $maker['collaborator'], $post_id );
      update_field( 'description', $maker['description'], $post_id );
    } else {
      return new WP_Error('acf_error', 'ACF plugin is not active or update_field function is unavailable');
    }

    // Step 3: Set the maker-category taxonomy
    if ( ! empty( $maker['categories'] ) ) {
      $term_ids = array_map( 'intval', $maker['categories'] );
      wp_set_object_terms( $post_id, $term_ids, 'maker-category', false );
    }

    // Step 4: Set the post thumbnail
    if( ! empty( $maker['logo'] ) ){
      $attach_id = upload_and_set_acf_image_field( $post_id, $maker['logo'], 'logo') ;
      if ( is_wp_error( $attach_id ) ) {
        return $attach_id; // Return the error
      }
    }

    return $post_id;
  }
}

/**
 * Checks all "pending" Maker CPTs to see if they have the email.
 *
 * @param      string  $email  The email
 *
 * @return     bool    True if maker email exists, False otherwise.
 */
function maker_email_exists( $email ) {
  // Sanitize the email to ensure it is safe for use in queries
  $sanitized_email = sanitize_email( $email );

  // Setup the arguments for the WP_Query
  $args = array(
    'post_type'      => 'maker', // CPT 'maker'
    'post_status'    => 'pending',
    'posts_per_page' => 1,       // Only need to find one to confirm existence
    'meta_query'     => array(
      array(
        'key'     => 'email',    // Check the 'email' meta key
        'value'   => $sanitized_email, // Value to check against
        'compare' => '='         // Exact match
      )
    ),
    'fields'         => 'ids'    // Only get post IDs to improve performance
  );

  // Execute the query
  $query = new \WP_Query( $args );

  // Check if any posts were found
  if ( $query->have_posts() ) {
    return true; // Post with the specified email exists
  }

  return false; // No posts found with the specified email
}

/**
 * Handles the transition of a Maker CPT from `pending` to `publish`.
 *
 * @param      string  $new_status  The new status
 * @param      string  $old_status  The old status
 * @param      object  $post        The post
 */
function handle_maker_publish( $new_status, $old_status, $post ) {
  // Check if the post is of the type 'maker'
  if ($post->post_type !== 'maker') {
    return;
  }

  // Check if the transition is from 'pending' to 'publish'
  if ( $old_status === 'pending' && $new_status === 'publish' ) {
    // Check if the action has already been done
    $has_run = get_post_meta($post->ID, '_maker_publish_handled', true);
    if ($has_run)
      return;

    $user_id = create_maker_user( $post );

    // Mark this action as done for this post
    update_post_meta($post->ID, '_maker_publish_handled', true);
  }
}
add_action('transition_post_status', __NAMESPACE__ . '\\handle_maker_publish', 10, 3);


/**
 * Uploads and sets an ACF image field.
 *
 * @param      int       $post_id     The post identifier
 * @param      array     $file        The file
 * @param      string    $field_name  The field name
 *
 * @return     mixed     The image attachment ID, or the WP_Error.
 */
function upload_and_set_acf_image_field( $post_id, $file, $field_name = 'logo' ) {
  require_once(ABSPATH . 'wp-admin/includes/image.php');
  require_once(ABSPATH . 'wp-admin/includes/file.php');
  require_once(ABSPATH . 'wp-admin/includes/media.php');

  // Check if the file array is set and if the upload went okay
  if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
    return new WP_Error('upload_error', 'There was an error uploading the file.');
  }

  // The WordPress way to handle file uploads
  $upload_overrides = array('test_form' => false);
  $uploaded_file = wp_handle_upload($file, $upload_overrides);

  if (isset($uploaded_file['error'])) {
    return new WP_Error('upload_error', $uploaded_file['error']);
  }

  // Prepare an array of post data for the attachment.
  $attachment = array(
    'guid'           => $uploaded_file['url'],
    'post_mime_type' => $file['type'],
    'post_title'     => preg_replace('/\.[^.]+$/', '', basename($file['name'])),
    'post_content'   => '',
    'post_status'    => 'inherit'
  );

  // Insert the attachment.
  $attach_id = wp_insert_attachment($attachment, $uploaded_file['file'], $post_id);

  // Generate the metadata for the attachment and update the database record.
  $attach_data = wp_generate_attachment_metadata($attach_id, $uploaded_file['file']);
  wp_update_attachment_metadata($attach_id, $attach_data);

  // Set the ACF image field
  if (function_exists('update_field')) {
    update_field($field_name, $attach_id, $post_id);
  } else {
    return new WP_Error('acf_error', 'ACF plugin is not active or update_field function is unavailable');
  }

  return $attach_id;
}
