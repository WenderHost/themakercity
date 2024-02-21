<?php
namespace TheMakerCity\adminbar;

/**
 * Removes the admin bar
 */
function remove_admin_bar() {
  //*
  global $current_user;
  $user_roles = $current_user->roles;
  if( is_array( $user_roles ) && in_array( 'administrator', $user_roles ) )
    return;
  /**/

  show_admin_bar(false);
}
add_action('wp', __NAMESPACE__ . '\\remove_admin_bar');