<?php
namespace TheMakerCity\shortcodes;

/**
 * Implements the `[makericon]` shortcode.
 *
 * @param      array   $atts {
 *   @type  string  $color  The hex color code for the logo. Also accepts the keyword `random` to load a random color and background image combo.
 *   @type  int     $width  The width in pixels of the logo
 *   @type  bool    $transparent  If TRUE, interior of logo is rendered transparent
 *   @type  string  $fill  The fill color for the logo
 *   @type  int     $instance  Sets the ID of the HTML elements
 *   @type  bool    $link  If TRUE, logo will link to the site's home page
 * }
 *
 * @return     string  The HTML for the logo
 */
function maker_icon( $atts = [] ){
  static $instance = 0;

  $args = shortcode_atts([
    'color'       => '000000',
    'width'       => 100,
    'transparent' => false,
    'fill'        => 'fff',
    'instance'    => $instance,
    'link'        => true,
  ], $atts );

  $instance++;

  if( $args['transparent'] === 'false' ) $args['transparent'] = false;
  if( $args['link'] === 'false' ) $args['link'] = false;

  $color = $args['color'];

  $background_image = null;
  if( 'random' == $args['color'] ){
    $colors = [
      0 =>[ 'hex' => '000', 'ext' => 'png' ],
      1 =>[ 'hex' => '85cec5', 'ext' => 'jpg' ],
      2 =>[ 'hex' => '000', 'ext' => 'jpg' ],
      3 =>[ 'hex' => 'ea5a69', 'ext' => 'jpg' ],
    ];
    $color_key = array_rand( $colors );
    $color = $colors[ $color_key ]['hex'];
    $ext = $colors[ $color_key ]['ext'];
    $background_image = 'width: 180px; height: 150px; background-image: url(\'' . MAKR_STYLESHEET_DIR_URI . 'lib/img/maker-icon-bkgrd_0' . $color_key . '.'. $ext .'\'); background-size: 220px 150px; background-repeat: no-repeat; background-position: center center; display: flex; align-content: center; justify-content: center;';
  }

  $color = ( '#' != substr( $args['color'], 0 ) ) ? '#' . $color : $color ;

  $fill = ( '#' != substr( $args['fill'], 0 ) ) ? '#' . $args['fill'] : $args['fill'] ;
  $icon_background = ( $args['transparent'] )? 'transparent' : $fill;

  $svg = '<style>#makr-icon-and-bkgrd_' . $args['instance'] . '{' . $background_image . '}</style><div id="makr-icon-and-bkgrd_' . $args['instance'] . '"><svg width="' . $args['width'] . '" id="makr-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 174 174"><style>.icon-background_' . $args['instance'] . '{fill:' . $icon_background . '}</style><g><path class="icon-background_' . $args['instance'] . '" d="M4.6 5h164v164H4.6z"/><path fill="' . $color . '" d="M-.4 0v174h174V0H-.4zm169 169H4.6V5h164v164z"/></g><path fill="' . $color . '" d="M71.5 40.2L57.6 54.1 43.7 40.2h-5.4v38.6h38.6V40.2h-5.4zm0 7.6V68L61.4 57.9l10.1-10.1zm-27.8 0l10.1 10.1 3.8 3.8 11.7 11.7H43.7V47.8zM38.3 95.1v38.6h38.6v-5.4L63 114.4l13.9-13.9v-5.4H38.3zm30.9 33.2H49l10.1-10.1 10.1 10.1zm0-27.8l-10.1 10.1-3.8 3.8-11.7 11.7v-25.6h25.6zM134.9 73.4l-16.2-33.2h-6.2L96.4 73.4v5.4H135v-5.4zm-19.3-27.2L129 73.4h-26.8l13.4-27.2zM134.9 100.5v-5.4H96.3v38.6h5.4v-27.9l28.1 27.9h5.1v-2.8l-11.1-11.1H134.9v-19.3zm-5.4 13.9h-11.1l-13.9-13.9h25v13.9z"/></svg></div>';

  if( $args['link'] )
    $svg = '<a href="' . home_url() . '" style="width: ' . $args['width'] . 'px; display: block;">' . $svg . '</a>';

  return $svg;
}
add_shortcode( 'makericon', __NAMESPACE__ . '\\maker_icon' );