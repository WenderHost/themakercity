<?php

namespace TheMakerCity\users;

/**
 * Add "Date Created" column to the WP Admin User listing.
 *
 * @param array $columns The existing columns.
 * @return array Modified columns with "Date Created" added.
 */
function add_user_date_created_column( $columns ) {
  $columns['date_created'] = 'Date Created';
  return $columns;
}
add_filter( 'manage_users_columns', __NAMESPACE__ . '\\add_user_date_created_column' );

/**
 * Grants the 'subscriber' and 'maker' roles the capabilities needed to use
 * the frontend Profile Editor's media/gallery uploader.
 *
 * 'upload_files' alone isn't enough: WordPress' native media-library upload
 * handler (wp_ajax_upload_attachment()) also requires `edit_post` on the
 * Maker CPT post being attached to. Since the Maker CPT uses the standard
 * `post` capability type with map_meta_cap on, editing your own post - even
 * as its author - additionally requires the base `edit_posts` capability,
 * and WordPress won't surface a saved change to a published post without
 * `edit_published_posts` either. Neither role has ever been granted those,
 * so every Maker/Subscriber failed that check with "Sorry, you are not
 * allowed to attach files to this post.", regardless of owning the post.
 *
 * Deliberately NOT granting `edit_others_posts` or `publish_posts`: these
 * roles should only ever be able to edit their own already-published
 * profile, not other Makers' profiles or publish new posts outright.
 *
 * @since 1.0.0
 *
 * @return void
 */
function allow_upload_files_for_roles() {
  $roles = array( 'subscriber', 'maker' );
  $caps  = array( 'upload_files', 'edit_posts', 'edit_published_posts' );

  foreach ( $roles as $role_name ) {
    $role = get_role( $role_name );

    if ( ! $role )
      continue;

    foreach ( $caps as $cap ) {
      if ( ! $role->has_cap( $cap ) )
        $role->add_cap( $cap );
    }
  }
}
add_action( 'init', __NAMESPACE__ . '\\allow_upload_files_for_roles' );

/**
 * Redirects 'subscriber' and 'maker' users away from the WordPress admin area.
 *
 * If a user with the 'subscriber' or 'maker' role attempts to access the 
 * admin dashboard, they are redirected to the '/profile-editor/' page. 
 * This restriction does not apply to AJAX requests.
 *
 * @since 1.0.0
 *
 * @return void
 */
function redirect_limited_roles_from_admin() {
  if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
    $user = wp_get_current_user();
    
    // Check if the user is a 'subscriber' or 'maker'
    $restricted_roles = array( 'subscriber', 'maker' );
    if ( array_intersect( $restricted_roles, (array) $user->roles ) ) {
      wp_redirect( home_url( '/profile-editor/' ) );
      exit;
    }
  }
}
add_action( 'admin_init', __NAMESPACE__ . '\\redirect_limited_roles_from_admin' );

/**
 * Checks and retrieves the current user's Maker profile ID.
 *
 * This function retrieves the `maker_profile_id` user meta for the current user.
 * If the value is not set, it will search for a Maker CPT authored by the current user.
 * If a Maker CPT is found, the function updates the user's `maker_profile_id` meta
 * with the Maker CPT's post ID. If no Maker CPT is found, the function returns `false`.
 *
 * @return int|false The Maker CPT post ID if found, or false if no Maker CPT is found or user is not logged in.
 */
function check_maker_profile_id() {
  // Get the current user
  $current_user = wp_get_current_user();

  // Check if current user is logged in
  if ( ! $current_user || 0 === $current_user->ID ) {
    return false; // User not logged in
  }

  // Get the 'maker_profile_id' user meta value
  $maker_profile_id = get_user_meta( $current_user->ID, 'maker_profile_id', true );

  // If the maker_profile_id is already set, return it
  if ( $maker_profile_id ) {
    return $maker_profile_id;
  }

  // Query Maker CPTs with current user as author
  $args = [
    'post_type'   => 'maker', // Adjust 'maker' to your actual CPT slug if needed
    'author'      => $current_user->ID,
    'post_status' => 'publish', // You can adjust the post status as per your needs
    'numberposts' => 1,
  ];

  $maker_cpt = get_posts( $args );

  // If a Maker CPT is found
  if ( ! empty( $maker_cpt ) ) {
    $maker_post_id = $maker_cpt[0]->ID;

    // Set the maker_profile_id user meta value
    update_user_meta( $current_user->ID, 'maker_profile_id', $maker_post_id );

    // Return the Maker CPT post_id
    return $maker_post_id;
  }

  // No Maker CPT found, return false
  return false;
}


/**
 * Display the "Date Created" value for each user in the new column.
 *
 * @param string $value The column value.
 * @param string $column_name The name of the column.
 * @param int $user_id The ID of the current user.
 * @return string The modified column value.
 */
function show_user_date_created_column( $value, $column_name, $user_id ) {
  if ( 'date_created' === $column_name ) {
    $user = get_userdata( $user_id );
    $registered_date = $user->user_registered;
    // Format the date as "F j, Y g:i A" (e.g., September 13, 2024 3:45 PM)
    $value = date( 'F j, Y g:i A', strtotime( $registered_date ) );
  }
  return $value;
}
add_filter( 'manage_users_custom_column', __NAMESPACE__ . '\\show_user_date_created_column', 10, 3 );

/**
 * Make the "Date Created" column sortable.
 *
 * @param array $columns The existing sortable columns.
 * @return array The modified sortable columns.
 */
function make_date_created_column_sortable( $columns ) {
  $columns['date_created'] = 'user_registered';
  return $columns;
}
add_filter( 'manage_users_sortable_columns', __NAMESPACE__ . '\\make_date_created_column_sortable' );


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
      // Redirect them to the '/profile-editor/' page
      return home_url( '/profile-editor/' );
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
  }
}
add_action( 'acf/save_post', __NAMESPACE__ . '\\custom_save_maker_post', 20 );

/**
 * Logs the outcome of a Maker CPT save, focused on the `additional_images`
 * gallery field, so gallery-upload reports can be cross-referenced against
 * actual server-side save results instead of relying solely on user reports.
 *
 * Runs after ACF's own field-saving (default priority 10 on `acf/save_post`),
 * so `get_field()` reflects the just-saved value.
 *
 * @param int $post_id The Post ID.
 */
function log_maker_gallery_save( $post_id ) {
  if ( is_admin() || 'maker' !== get_post_type( $post_id ) )
    return;

  $current_user  = wp_get_current_user();
  $gallery       = get_field( 'additional_images', $post_id );
  $gallery_count = is_array( $gallery ) ? count( $gallery ) : 0;
  $acfe_active   = defined( 'ACFE_VERSION' );

  uber_log( '[gallery-save] Maker CPT saved via frontend form.', [
    'post_id'        => $post_id,
    'user_id'        => $current_user->ID,
    'user_email'     => $current_user->user_email,
    'gallery_count'  => $gallery_count,
    'acfe_active'    => $acfe_active ? 'yes' : 'no',
    'has_upload_cap' => user_can( $current_user, 'upload_files' ) ? 'yes' : 'no',
    'has_edit_cap'   => user_can( $current_user, 'edit_post', $post_id ) ? 'yes' : 'no',
  ] );
}
add_action( 'acf/save_post', __NAMESPACE__ . '\\log_maker_gallery_save', 20 );

/**
 * Records the timestamp of a Maker's most recent edit made through the
 * frontend Profile Editor, in a dedicated postmeta value distinct from
 * WordPress' own `post_modified`.
 *
 * `post_modified` isn't a reliable signal for "the Maker edited their
 * profile" - it also gets bumped by admin/webmaster edits in wp-admin,
 * bulk imports (see scripts/import-makers.php), and any other process
 * that calls wp_update_post() on a Maker CPT. This meta value only ever
 * gets set here, gated the same way as custom_save_maker_post() above
 * (`! is_admin()`), so it only reflects genuine frontend submissions.
 *
 * Read by the "Last Edit" admin column in
 * lib/fns/maker-cpt.admin-columns.php.
 *
 * @param int $post_id The Post ID.
 */
function track_maker_profile_edit_timestamp( $post_id ) {
  if ( is_admin() || 'maker' !== get_post_type( $post_id ) )
    return;

  update_post_meta( $post_id, '_maker_profile_last_edited', current_time( 'mysql' ) );
}
add_action( 'acf/save_post', __NAMESPACE__ . '\\track_maker_profile_edit_timestamp', 20 );

/**
 * Handles the "Share Your System Info" report, and the richer client-side
 * diagnostic events logged by the gallery watchdog (see `lib/js/scripts/
 * gallery-watchdog.js`). Both post JSON to this same nonce-protected
 * endpoint; the `event` field distinguishes a manual system-info report
 * from an automatically-captured gallery failure.
 */
function send_system_info_handler() {

    // Get JSON payload. This endpoint receives `Content-Type: application/json`
    // bodies, so the nonce is NOT in $_POST/$_REQUEST — check_ajax_referer()
    // can't see it there, hence the manual wp_verify_nonce() below instead.
    $data = json_decode( file_get_contents( 'php://input' ), true );

    $response = array();
    $response['message'] = '';
    $response['status'] = '';

    if( $data && ( empty( $data['nonce'] ) || ! wp_verify_nonce( $data['nonce'], 'mkc_client_report' ) ) ) {
      uber_log( '[client-report] Rejected: invalid or missing nonce.' );
      $response['status'] = 'error';
      $response['message'] = 'Invalid request.';
      wp_send_json( $response, 403 );
    }

    if( $data ) {
      $current_user = wp_get_current_user();
      $event        = isset( $data['event'] ) ? sanitize_text_field( $data['event'] ) : 'manual_report';
      $user_email   = isset( $data['user_email'] ) ? sanitize_text_field( $data['user_email'] ) : $current_user->user_email;

      // Read-only lookup (unlike check_maker_profile_id(), doesn't write
      // meta as a side effect) so this diagnostic can check edit_post
      // against the reporting user's own Maker profile, if they have one.
      $maker_profile_id = get_user_meta( $current_user->ID, 'maker_profile_id', true );
      $has_edit_cap     = $maker_profile_id ? user_can( $current_user, 'edit_post', $maker_profile_id ) : null;

      // Always log server-side first, so we have a record even if the
      // outbound email fails or is never triggered.
      uber_log( '[client-report] ' . $event, [
        'user_id'        => $current_user->ID,
        'user_email'     => $user_email,
        'ip'             => isset( $data['ip'] ) ? sanitize_text_field( $data['ip'] ) : null,
        'browser'        => isset( $data['browser'] ) ? sanitize_text_field( $data['browser'] ) : null,
        'os'             => isset( $data['os'] ) ? sanitize_text_field( $data['os'] ) : null,
        'screen'         => isset( $data['screenResolution'] ) ? sanitize_text_field( $data['screenResolution'] ) : null,
        'has_upload_cap' => user_can( $current_user, 'upload_files' ) ? 'yes' : 'no',
        'has_edit_cap'   => is_null( $has_edit_cap ) ? 'n/a (no maker_profile_id)' : ( $has_edit_cap ? 'yes' : 'no' ),
        'acfe_active'    => defined( 'ACFE_VERSION' ) ? 'yes' : 'no',
        'detail'         => isset( $data['detail'] ) ? sanitize_text_field( $data['detail'] ) : null,
        'js_error'       => isset( $data['jsError'] ) ? sanitize_textarea_field( $data['jsError'] ) : null,
      ] );

      $to      = get_option( 'admin_email' );
      $subject = "New {$event} Report [{$user_email}]";

      $message  = "Report type: {$event}\n";
      $message .= "User: {$user_email} (ID {$current_user->ID})\n\n";
      $message .= "<strong>IP Address:</strong> " . ( isset( $data['ip'] ) ? sanitize_text_field( $data['ip'] ) : 'n/a' ) . "\n";
      $message .= "<strong>Browser:</strong> " . ( isset( $data['browser'] ) ? sanitize_text_field( $data['browser'] ) : 'n/a' ) . "\n";
      $message .= "<strong>Operating System:</strong> " . ( isset( $data['os'] ) ? sanitize_text_field( $data['os'] ) : 'n/a' ) . "\n";
      $message .= "<strong>Screen Resolution:</strong> " . ( isset( $data['screenResolution'] ) ? sanitize_text_field( $data['screenResolution'] ) : 'n/a' ) . "\n";
      $message .= "<strong>Has upload_files capability:</strong> " . ( user_can( $current_user, 'upload_files' ) ? 'yes' : 'no' ) . "\n";
      $message .= "<strong>Has edit_post capability on own profile:</strong> " . ( is_null( $has_edit_cap ) ? 'n/a (no maker_profile_id)' : ( $has_edit_cap ? 'yes' : 'no' ) ) . "\n";
      $message .= "<strong>ACF Extended Pro active:</strong> " . ( defined( 'ACFE_VERSION' ) ? 'yes' : 'no' ) . "\n";
      if ( ! empty( $data['detail'] ) )
        $message .= "<strong>Detail:</strong> " . sanitize_text_field( $data['detail'] ) . "\n";
      if ( ! empty( $data['jsError'] ) )
        $message .= "<strong>JS Error:</strong> " . sanitize_textarea_field( $data['jsError'] ) . "\n";

      $headers = ["Content-Type: text/html; charset=UTF-8"];

      if( wp_mail( $to, $subject, $message, $headers ) ) {
        $response['status'] = "success";
        wp_send_json( $response, 200 );
      } else {
        uber_log( '[client-report] wp_mail() failed to send report to admin_email.', [ 'to' => $to, 'subject' => $subject ] );
        $response['status'] = 'error';
        $response['message'] = 'Error sending email.';
        wp_send_json( $response, 500 );
      }
    } else {
      uber_log( '[client-report] Received request with no/invalid JSON payload.' );
      $response['status'] = 'error';
      $response['message'] = 'No data received.';
      wp_send_json( $response, 400 );
    }

    wp_die();
}
add_action('wp_ajax_send_system_info', __NAMESPACE__ . '\\send_system_info_handler');
add_action('wp_ajax_nopriv_send_system_info', __NAMESPACE__ . '\\send_system_info_handler');