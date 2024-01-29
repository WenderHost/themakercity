<?php

namespace TheMakerCity\shortcodes;
use function TheMakerCity\utilities\{get_alert};

function maker_primary_image( $atts ){
  $args = shortcode_atts([
    'css_classes' => 'maker-primary-image',
    'link'        => false,
  ], $atts );

  global $post;
  if( 'maker' != get_post_type( $post ) )
    return get_alert(['description' => 'This shortcode only works with the <code>maker</code> CPT.', 'type' => 'warning']);

  $css_classes = explode( ' ', $args['css_classes'] );

  $link_open = '';
  $link_close = '';
  $link = ( 'true' === $args['link'] )? (bool) $args['link'] : false ;
  if( $link ){
    $link_open = '<a href="' . get_the_permalink( $post->ID ) . '">';
    $link_close = '</a>';
  }

  $primary_image = get_field( 'primary_image' );
  $attachment_id = $primary_image['ID'];

  $collaborator = get_field('collaborator');
  $collaborator_html = '';

  $img_src = esc_url( wp_get_attachment_image_url( $attachment_id, 'large' ) );
  $img_srcset = esc_attr( wp_get_attachment_image_srcset( $attachment_id, 'large' ) );
  $img_sizes = wp_get_attachment_image_sizes( $attachment_id, 'large' );

  if( 'yes' == $collaborator ){
    $css_classes[] = 'collaborator';
    $collaborator_html = '<div class="collaborator-icon">c</div>';
  }

  if( is_array( $css_classes ) )
    $css_classes = implode( ' ', $css_classes );

  $html = "<div class=\"{$css_classes}\">{$link_open}<img src=\"{$img_src}\" srcset=\"{$img_srcset}\" sizes=\"{$img_sizes}\" />{$link_close}{$collaborator_html}</div>";
  return $html;
}
add_shortcode( 'primary_image', __NAMESPACE__ . '\\maker_primary_image' );