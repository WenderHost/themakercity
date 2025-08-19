<?php
/**
 * Logs debug output with contextual data.
 *
 * @param mixed $message  Main log message or data.
 * @param mixed $context  Optional. Extra array/object to dump alongside the message.
 */
function uber_log( $message = null, $context = null ) {
    static $counter = 1;

    $bt     = debug_backtrace();
    $caller = $bt[0] ?? [ 'file' => 'unknown', 'line' => '??' ];

    if ( 1 === $counter ) {
        error_log( "\n\n" . str_repeat( '-', 25 ) . ' STARTING DEBUG [' . date( 'h:i:sa', current_time( 'timestamp' ) ) . '] ' . str_repeat( '-', 25 ) . "\n\n" );
    }

    if ( is_array( $message ) || is_object( $message ) ) {
        $message = '[DUMP] ' . print_r( $message, true );
    } elseif ( is_array( $context ) || is_object( $context ) ) {
        $message = rtrim( (string) $message ) . ' ' . print_r( $context, true );
    } elseif ( ! is_string( $message ) ) {
        $message = var_export( $message, true );
    }

    $entry  = "\n{$counter}. " . basename( $caller['file'] ) . '::' . $caller['line'] . "\n{$message}\n---\n";
    error_log( $entry );
    $counter++;

    return $entry;
}
