<?php

namespace TheMakerCity\shortcodes;
use function TheMakerCity\utilities\get_alert;

function get_maker_status_alert(){
  global $post;
  $post_status = get_post_status( $post );
  if( 'publish' != $post_status ){
    return get_alert(['description' => 'This profile is currently set as <code>' . $post_status . '</code>. <a href="' . get_edit_post_link( $post->ID ) . '">Edit this profile</a> and click "Publish" to activate it in the directory and send the associated Maker a New User email.']);
  }
}
add_shortcode( 'makerstatusalert', __NAMESPACE__ . '\\get_maker_status_alert' );
