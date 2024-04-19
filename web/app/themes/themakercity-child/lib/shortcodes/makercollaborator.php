<?php
namespace TheMakerCity\shortcodes;
use function TheMakerCity\utilities\{get_alert};

/**
 * Shows the "Open to Collab..." note.
 *
 * @return     string  HTML for the "Open to Collab..." note.
 */
function maker_collaborator_note(){
  global $post;

  if( is_single() && 'maker' == get_post_type( $post ) ){
    $collaborator = get_field( 'collaborator', $post );
    if( 'yes' == $collaborator ){
      return '<div class="collaborator-note"><div class="collaborator-icon">c</div> <div>Open to Collaborating with Other Makers</div></div>';
    }
  } else {
    return get_alert(['description' => 'The <code>[makercollaborator]</code> shortcode only works on Maker CPT pages.']);
  }
}
add_shortcode( 'makercollaborator', __NAMESPACE__ . '\\maker_collaborator_note' );