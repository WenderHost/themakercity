<?php
namespace TheMakerCity\htmx;

/**
 * Handles sanitizing arrays of params.
 *
 * @param      string $sanitized   The parameter after it has been run through sanitize_text_field().
 * @param      mixed  $unsanitized  The unsanitized value.
 *
 * @return     mixed  The sanitized paramter.
 */
function sanitize_array_params( $sanitized, $unsanitized ){
  if( is_array( $unsanitized ) ){
    foreach( $unsanitized as $key => $value ){
      $values[$key] = sanitize_text_field( $value );
    }
    $sanitized = $values;
  }

  return $sanitized;
}
add_filter( 'hxwp/sanitize_param_value', __NAMESPACE__ . '\\sanitize_array_params', 10, 2 );