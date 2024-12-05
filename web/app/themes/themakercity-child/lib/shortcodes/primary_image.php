<?php

namespace TheMakerCity\shortcodes;
use function TheMakerCity\utilities\{get_alert};

function maker_primary_image( $atts ){

  $args = shortcode_atts([
    'css_classes'       => 'maker-primary-image',
    'style'             => null,
    'link'              => false,
    'show'              => 'primary_image',
    'size'              => 'large',
    'show_placeholder'  => false,
    'show_collaborator' => true,
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

  $size = ( $args['size'] && in_array( $args['size'], ['thumbnail','medium','large','full'] ) )? $args['size'] : 'large' ;

  $show_placeholder = ( 'true' === $args['show_placeholder'] )? (bool) $args['show_placeholder'] : false ;
  if ( $args['show_collaborator'] === 'false' ) $args['show_collaborator'] = false; // just to be sure...
  $show_collaborator = (bool) $args['show_collaborator'];

  if( 'gallery' == $args['show'] ){
    $gallery = get_field( 'additional_images' );
    if( is_array( $gallery ) ){
      $image = $gallery[0];
    } else {
      $image = get_field( 'primary_image' );
    }
  } else {
    $image = get_field( 'primary_image' );
  }

  $attachment_id = ( is_array( $image ) && array_key_exists( 'ID', $image ) )? $image['ID'] : false ;

  $img_src = esc_url( wp_get_attachment_image_url( $attachment_id, $size ) );
  $img_srcset = esc_attr( wp_get_attachment_image_srcset( $attachment_id, $size ) );
  $img_sizes = wp_get_attachment_image_sizes( $attachment_id, $size );

  $collaborator_html = '';
  if( $show_collaborator ){
    $collaborator = get_field('collaborator');
    if( 'yes' == $collaborator ){
      $css_classes[] = 'collaborator';
      $collaborator_html = '<div class="collaborator-icon">c</div>';
    }
  }

  if( is_array( $css_classes ) )
    $css_classes = implode( ' ', $css_classes );

  $html = '';
  if( $img_src ){
    $html = "<div class=\"{$css_classes}\" style=\"{$args['style']}\">{$link_open}<img src=\"{$img_src}\" srcset=\"{$img_srcset}\" sizes=\"{$img_sizes}\" />{$link_close}{$collaborator_html}</div>";
  } else if( $show_placeholder ) {
    $html = "<div class=\"{$css_classes} placeholder\">{$link_open}<div class=\"placeholder\" style=\"width: 100%; min-height: 420px; background-color: #eee\"></div>{$link_close}{$collaborator_html}</div>";
  }

  return $html;
}
add_shortcode( 'primary_image', __NAMESPACE__ . '\\maker_primary_image' );