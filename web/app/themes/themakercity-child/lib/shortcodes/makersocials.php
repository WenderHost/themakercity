<?php
namespace TheMakerCity\shortcodes;
use function TheMakerCity\utilities\{get_alert};

function maker_social_icons( $atts ){
  $args = shortcode_atts([
    'foo' => 'bar'
  ], $atts );

  global $post;
  if( 'maker' != get_post_type( $post ) )
    return get_alert(['description' => 'This shortcode only works with the <code>maker</code> CPT.', 'type' => 'warning']);

  $links = [];
  if( have_rows( 'social_profiles') ):
    while( have_rows( 'social_profiles' ) ): the_row();
      $platform = get_sub_field('platform');
      if( 'website' == $platform )
        $platform = 'earth-americas';
      $link = get_sub_field('link');
      $links[] = '<a href="' . $link . '" target="_blank" title="' . esc_attr( get_the_title( $post ) . ' on ' . ucfirst( $platform ) ) . '"><icon class="fa fa-xl fa-' . $platform . '"></icon></a>';
    endwhile;
  endif;
  $email = get_field( 'email' );
  if( ! empty( $email ) && is_email( $email ) )
    $links[] = '<a href="mailto:' . $email . '?subject=The%20Maker%20City%20Directory%20Referral" target="_blank" title="Email ' . esc_attr( get_the_title( $post ) ) . '"><icon class="fa fa-xl fa-envelope"></icon></a>';
  //uber_log('🔔 $links = ' . print_r( $links, true ) );

  if( 0 < count( $links ) )
    return '<ul class="makersocials"><li>' . implode( '</li><li>', $links ) . '</li></ul>';
}
add_shortcode( 'makersocials', __NAMESPACE__ . '\\maker_social_icons' );