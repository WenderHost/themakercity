<?php

namespace TheMakerCity\utilities;
use function TheMakerCity\templates\{render_template};

/**
 * Returns an HTML alert message
 *
 * @param      array  $atts {
 *   @type  string  $type         The alert type can be info, warning, success, or danger (defaults to `warning`).
 *   @type  string  $title        The title of the alert.
 *   @type  string  $description  The content of the alert.
 *   @type  string  $css_classes  Additional CSS classes to add to the alert parent <div>.
 *   @type  bool    $dismissable  Is the alert dismissable? (default FALSE)
 * }
 *
 * @return     html  The alert.
 */
function get_alert( $atts ){
  $args = shortcode_atts([
   'type'               => 'warning',
   'title'              => null,
   'description'        => 'Alert description goes here.',
   'css_classes'        => null,
   'dismissable'        => false,
  ], $atts );

  $args['dismissable'] = filter_var( $args['dismissable'], FILTER_VALIDATE_BOOLEAN );

  $data = [
    'description' => $args['description'],
    'title'       => $args['title'],
    'type'        => $args['type'],
    'css_classes' => $args['css_classes'],
    'dismissable' => $args['dismissable'],
  ];

  return render_template( 'alert', $data );
}

/**
 * Determines whether the current request is in Elementor edit mode.
 *
 * Checks both the `elementor-preview` query parameter and Elementor's
 * internal editor state to determine if the user is actively editing
 * with Elementor.
 *
 * @return bool True if Elementor edit mode is active, false otherwise.
 */
function is_elementor_edit_mode() {
  if ( isset( $_GET['elementor-preview'] ) ) {
    return true;
  }

  if ( did_action( 'elementor/loaded' ) ) {
    $elementor = \Elementor\Plugin::$instance;
    return $elementor->editor->is_edit_mode();
  }

  return false;
}
