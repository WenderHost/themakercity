<?php

namespace TheMakerCity\shortcodes;
use function TheMakerCity\utilities\{get_alert};

/**
 * Generates the primary image HTML output for the 'maker' custom post type.
 *
 * This shortcode allows users to display a primary image or a gallery image 
 * associated with a 'maker' CPT. It supports customization of CSS classes, 
 * styles, linking, and placeholders.
 *
 * @param array $atts {
 *     Optional. An array of shortcode attributes.
 *
 *     @type string  $css_classes       CSS classes to apply to the image container. Default 'maker-primary-image'.
 *     @type string  $style             Inline styles for the image container. Default null.
 *     @type bool    $link              Whether to wrap the image with a permalink. Default false.
 *     @type string  $show              Determines which image to show ('primary_image' or 'gallery'). Default 'primary_image'.
 *     @type string  $size              Image size to display ('thumbnail', 'medium', 'large', 'full'). Default 'large'.
 *     @type bool    $show_placeholder  Whether to display a placeholder if no image is found. Default false.
 *     @type bool    $show_collaborator Whether to display collaborator information. Default true.
 * }
 * @return string HTML markup for the primary image or a warning message if the CPT is not 'maker'.
 */
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
    $html = "<div class=\"{$css_classes} placeholder\">{$link_open}<div class=\"placeholder\" style=\"width: 100%; aspect-ratio: 1/1; background-color: #eee\"></div>{$link_close}{$collaborator_html}</div>";
  }

  return $html;
}
add_shortcode( 'primary_image', __NAMESPACE__ . '\\maker_primary_image' );