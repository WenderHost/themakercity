<?php
/**
 * WP-CLI commands for The Maker City.
 *
 * Registered under the `wp makers` namespace. This file is a no-op outside
 * of WP-CLI, so it's safe to require unconditionally from functions.php.
 */

namespace TheMakerCity\cli;

if ( ! defined( 'WP_CLI' ) || ! \WP_CLI )
  return;

/**
 * Manages the Maker directory from the command line.
 */
class Makers_Command {

  /**
   * Every column the export knows how to produce, in the order they'd appear.
   *
   * @var array
   */
  const AVAILABLE_FIELDS = [
    'name',
    'first_name',
    'last_name',
    'email',
    'login_email',
    'profile_email',
    'maker_page_title',
    'maker_page_url',
    'maker_id',
    'login_status',
    'last_modified',
    'last_self_edit',
  ];

  /**
   * The date the `_maker_profile_last_edited` tracking went live, so the run
   * summary can say what a blank last_self_edit actually means.
   *
   * Set by commit 586ae61, "Add \"Last Edit\" admin column tracking genuine
   * Profile Editor saves" (2026-08-05), which deployed on push. Nothing before
   * that date was ever recorded, and there's no backfilling it — post_modified
   * is exactly the unreliable signal that commit replaced.
   *
   * @var string
   */
  const SELF_EDIT_TRACKING_SINCE = '2026-08-05';

  /**
   * The columns produced when --fields isn't given.
   *
   * `name` is a single column rather than first/last: the Maker profile's ACF
   * `name` field is one free-text box, and plenty of entries are things like
   * "Deb Meritsky and Marc Rotman" that don't survive being split. Pass
   * --fields=first_name,last_name if a mail merge needs the halves.
   *
   * `last_self_edit` is the only date here by default, and it is deliberately
   * left blank rather than falling back to `last_modified`: post_modified is
   * bumped by admin edits and imports too, so standing it in would dress up a
   * value that says nothing about the Maker as one that does. Blank means "no
   * self-edit on record".
   *
   * @var array
   */
  const DEFAULT_FIELDS = [
    'name',
    'email',
    'maker_page_title',
    'maker_page_url',
    'last_self_edit',
    'login_status',
  ];

  /**
   * Exports Maker directory contacts as CSV.
   *
   * Produces one CSV row per Maker profile, drawn from the profile's own ACF
   * fields and, where one exists, the WordPress user account linked to it.
   * `name` comes from the profile, `email` prefers the linked WordPress user's
   * address; the columns listed below let you take either source explicitly.
   *
   * Writes a timestamped CSV into the current directory by default; pass
   * --file to put it elsewhere, or --stdout to pipe it. Makers with no usable
   * email address are skipped and listed in the run summary.
   *
   * ## OPTIONS
   *
   * [--file=<path>]
   * : Where to write the CSV. Pass a directory and the file is named
   * automatically as YYYY-MM-DD_HHMM_makers.csv in site local time.
   * ---
   * default: .
   * ---
   *
   * [--stdout]
   * : Print the CSV to STDOUT instead of writing a file, for piping. The run
   * summary goes to STDERR so the piped CSV stays clean.
   *
   * [--fields=<fields>]
   * : Comma-separated columns to include, in the order given. See AVAILABLE
   * COLUMNS below.
   * ---
   * default: name,email,maker_page_title,maker_page_url,last_self_edit,login_status
   * ---
   *
   * [--status=<statuses>]
   * : Comma-separated post statuses to include.
   * ---
   * default: publish
   * ---
   *
   * [--linked-users-only]
   * : Only include Makers whose profile is linked to a WordPress account, i.e.
   * login_status=linked. Note this is narrower than "can log in": Makers with
   * login_status=account_only can log in too, they just never have.
   *
   * [--not-updated-since=<date>]
   * : Only include Makers with no last_self_edit on or after this date
   * (anything strtotime() reads, e.g. 2025-01-01 or "12 months ago"). Makers
   * with no self-edit on record stay in the list.
   *
   * ## AVAILABLE COLUMNS
   *
   * * name             - the profile's ACF `name`, else the linked user's name.
   * * first_name       - `name` up to the first space. Lossy: plenty of entries
   *                      are two people, e.g. "Deb Meritsky and Marc Rotman".
   * * last_name        - the rest of `name` after the first space.
   * * email            - login_email, else profile_email.
   * * login_email      - the linked user's address. Empty if none is linked.
   * * profile_email    - the profile's ACF `email`.
   * * maker_page_title - the Maker post title, HTML entities decoded.
   * * maker_page_url   - public permalink of the Maker page.
   * * maker_id         - the Maker post ID.
   * * login_status     - can this Maker log in and edit their page?
   *                      `linked`       - account linked to the profile; they
   *                                       have opened their profile editor.
   *                      `account_only` - an account exists on their email
   *                                       address but has never been linked, so
   *                                       they can log in (via a password
   *                                       reset) but never have.
   *                      `none`         - no account on their email address;
   *                                       they'd need one created first.
   * * last_self_edit   - date the Maker last saved their page in the frontend
   *                      Profile Editor. The only trustworthy activity signal.
   *                      Tracking began 2026-08-05, so empty means nothing on
   *                      record since then, not never.
   * * last_modified    - post_modified, as Y-m-d. Off by default, and not a
   *                      proxy for Maker activity: admin edits and imports bump
   *                      it too, so it means "last touched by anyone".
   *
   * ## EXAMPLES
   *
   *     # Default outreach list: a timestamped CSV in the current directory.
   *     $ wp makers export
   *
   *     # Mail-merge shape, with the name split into halves.
   *     $ wp makers export --fields=first_name,last_name,email,maker_page_title
   *
   *     # Pipe it somewhere instead of writing a file.
   *     $ wp makers export --stdout | pbcopy
   *
   *     # Makers who can log in but haven't touched their page in a year.
   *     $ wp makers export --linked-users-only --not-updated-since="12 months ago"
   *
   * @when after_wp_load
   */
  public function export( $args, $assoc_args ) {
    $statuses    = array_filter( array_map( 'trim', explode( ',', \WP_CLI\Utils\get_flag_value( $assoc_args, 'status', 'publish' ) ) ) );
    $fields      = self::parse_fields( \WP_CLI\Utils\get_flag_value( $assoc_args, 'fields', implode( ',', self::DEFAULT_FIELDS ) ) );
    $linked_only = (bool) \WP_CLI\Utils\get_flag_value( $assoc_args, 'linked-users-only', false );
    $stale_since = \WP_CLI\Utils\get_flag_value( $assoc_args, 'not-updated-since', null );

    if ( ! is_null( $stale_since ) ) {
      $stale_since = strtotime( $stale_since );
      if ( false === $stale_since )
        \WP_CLI::error( 'Could not understand --not-updated-since. Try a date like 2025-01-01, or "12 months ago".' );
    }

    $makers = get_posts( [
      'post_type'      => 'maker',
      'post_status'    => $statuses,
      'posts_per_page' => -1,
      'orderby'        => 'title',
      'order'          => 'ASC',
      'fields'         => 'ids',
    ] );

    if ( empty( $makers ) )
      \WP_CLI::error( 'No Maker profiles found for status: ' . implode( ', ', $statuses ) );

    $user_ids_by_maker = self::get_user_ids_by_maker();
    $user_ids_by_email = self::get_user_ids_by_email();

    $rows           = [];
    $by_status      = [ 'linked' => 0, 'account_only' => 0, 'none' => 0 ];
    $with_self_edit = 0;
    $no_email  = [];
    $skipped   = 0;

    foreach ( $makers as $maker_id ) {
      $user   = isset( $user_ids_by_maker[ $maker_id ] ) ? get_user_by( 'id', $user_ids_by_maker[ $maker_id ] ) : false;
      $record = self::build_record( $maker_id, $user, $user_ids_by_email );

      if ( $linked_only && ! $user ) {
        $skipped++;
        continue;
      }

      // A Maker with no self-edit on record hasn't updated their page since the
      // cutoff as far as we know, so they stay in the list. Only a recorded
      // self-edit at or after the cutoff takes them out of it.
      $self_edit = $record['last_self_edit'];

      if ( ! is_null( $stale_since ) && '' !== $self_edit && strtotime( $self_edit ) >= $stale_since ) {
        $skipped++;
        continue;
      }

      if ( '' === $record['email'] || ! is_email( $record['email'] ) ) {
        $no_email[] = sprintf( '%s (ID %d)', $record['maker_page_title'], $maker_id );
        continue;
      }

      $by_status[ $record['login_status'] ]++;

      if ( '' !== $record['last_self_edit'] )
        $with_self_edit++;

      $row = [];
      foreach ( $fields as $field )
        $row[ $field ] = $record[ $field ];

      $rows[] = $row;
    }

    if ( empty( $rows ) )
      \WP_CLI::error( 'No Makers matched.' );

    $csv       = self::to_csv( $rows );
    $to_stdout = (bool) \WP_CLI\Utils\get_flag_value( $assoc_args, 'stdout', false );

    $headline = '';
    $notes    = [];

    if ( $to_stdout ) {
      $headline = sprintf( '%d Makers exported.', count( $rows ) );
    } else {
      $path = self::resolve_path( \WP_CLI\Utils\get_flag_value( $assoc_args, 'file', '.' ) );
      if ( false === file_put_contents( $path, $csv ) )
        \WP_CLI::error( 'Could not write to: ' . $path );

      $headline = sprintf( '%d Makers exported to %s', count( $rows ), $path );
    }

    // Noun phrases rather than sentences, so a count of 1 doesn't read wrong.
    $notes[] = sprintf( '📧 %d have logged in and opened their profile editor.', $by_status['linked'] );
    $notes[] = sprintf( '🔑 %d have an account but have never used it — a password reset gets them in.', $by_status['account_only'] );
    $notes[] = sprintf( '🚫 %d have no account at all — one has to be created before they can edit.', $by_status['none'] );
    $notes[] = sprintf( '✏️  %d with a self-edit on record (tracking began %s; blanks predate it).', $with_self_edit, self::SELF_EDIT_TRACKING_SINCE );

    if ( $skipped )
      $notes[] = sprintf( '🔍 %d left out by the filters you gave.', $skipped );

    if ( ! empty( $no_email ) ) {
      $notes[] = sprintf( '⚠️  %d with no valid email address, not included:', count( $no_email ) );
      foreach ( $no_email as $label )
        $notes[] = '     ' . $label;
    }

    // With --stdout the CSV owns STDOUT, so the summary has to go to STDERR to
    // keep a pipe clean. Otherwise it's ordinary command output.
    if ( $to_stdout ) {
      \WP_CLI::print_value( $csv );
      self::report( '✅ ' . $headline );
      foreach ( $notes as $note )
        self::report( '   ' . $note );
    } else {
      \WP_CLI::success( $headline );
      foreach ( $notes as $note )
        \WP_CLI::log( '   ' . $note );
    }
  }

  /**
   * Assembles every available column for one Maker, whether or not the caller
   * asked for it. Cheap enough to build wholesale, and it keeps the field
   * selection logic in one place.
   *
   * @param int          $maker_id The Maker post ID.
   * @param \WP_User|false $user   The linked user account, or false.
   * @return array Column name => value.
   */
  private static function build_record( $maker_id, $user, $user_ids_by_email = [] ) {
    // Two sources, because neither covers the directory on its own: the ACF
    // fields on the profile are set on nearly every Maker, while only about
    // half have a linked WordPress account — and that account is the one that
    // can log in, so its address is the one worth writing to.
    $profile_email = trim( (string) get_post_meta( $maker_id, 'email', true ) );
    $login_email   = $user ? trim( (string) $user->user_email ) : '';

    // The Maker's own ACF `name` wins over the user account's first/last name:
    // it's what they entered about themselves, and it's set on nearly every
    // profile, where the user-account name fields are almost always empty.
    $name = trim( (string) get_post_meta( $maker_id, 'name', true ) );
    if ( '' === $name && $user )
      $name = trim( $user->first_name . ' ' . $user->last_name );

    $parts     = '' === $name ? [] : preg_split( '/\s+/', $name, 2 );
    $self_edit = get_post_meta( $maker_id, '_maker_profile_last_edited', true );

    // A missing link doesn't mean a missing account. The `maker_profile_id`
    // meta is written lazily by check_maker_profile_id() the first time a Maker
    // opens their profile editor, so most Makers created by the CSV imports
    // have an account sitting on their profile email that has never been
    // linked — they can log in via a password reset, they just never have.
    if ( $user )
      $login_status = 'linked';
    elseif ( '' !== $profile_email && isset( $user_ids_by_email[ strtolower( $profile_email ) ] ) )
      $login_status = 'account_only';
    else
      $login_status = 'none';

    return [
      'name'             => $name,
      'first_name'       => isset( $parts[0] ) ? $parts[0] : '',
      'last_name'        => isset( $parts[1] ) ? $parts[1] : '',
      'email'            => '' !== $login_email ? $login_email : $profile_email,
      'login_email'      => $login_email,
      'profile_email'    => $profile_email,
      'maker_page_title' => html_entity_decode( get_the_title( $maker_id ), ENT_QUOTES, 'UTF-8' ),
      'maker_page_url'   => get_permalink( $maker_id ),
      'maker_id'         => $maker_id,
      'login_status'     => $login_status,
      'last_modified'    => substr( (string) get_post_field( 'post_modified', $maker_id ), 0, 10 ),
      'last_self_edit'   => $self_edit ? substr( (string) $self_edit, 0, 10 ) : '',
    ];
  }

  /**
   * Validates and normalises the --fields value.
   *
   * @param string $fields Comma-separated column names.
   * @return array List of valid column names, in the order given.
   */
  private static function parse_fields( $fields ) {
    $requested = array_filter( array_map( 'trim', explode( ',', $fields ) ) );
    $unknown   = array_diff( $requested, self::AVAILABLE_FIELDS );

    if ( ! empty( $unknown ) )
      \WP_CLI::error( sprintf( "Unknown field(s): %s\nAvailable: %s", implode( ', ', $unknown ), implode( ', ', self::AVAILABLE_FIELDS ) ) );

    if ( empty( $requested ) )
      \WP_CLI::error( '--fields needs at least one column.' );

    return $requested;
  }

  /**
   * Maps Maker post IDs to the WordPress user account linked to them.
   *
   * The link lives in the `maker_profile_id` user meta, so this reads it in a
   * single query rather than per-Maker. A Maker with more than one linked user
   * keeps the lowest user ID, which is the oldest account.
   *
   * @return array Maker post ID => user ID.
   */
  private static function get_user_ids_by_maker() {
    global $wpdb;

    $map  = [];
    $meta = $wpdb->get_results( "SELECT user_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = 'maker_profile_id' AND meta_value != '' ORDER BY user_id ASC" );

    foreach ( $meta as $row ) {
      $maker_id = intval( $row->meta_value );
      if ( $maker_id && ! isset( $map[ $maker_id ] ) )
        $map[ $maker_id ] = intval( $row->user_id );
    }

    return $map;
  }

  /**
   * Maps lowercased user email addresses to user IDs, so login_status can tell
   * "no account" from "an account nobody has linked yet" without a
   * get_user_by() call per Maker.
   *
   * @return array email => user ID.
   */
  private static function get_user_ids_by_email() {
    global $wpdb;

    $map = [];

    foreach ( $wpdb->get_results( "SELECT ID, user_email FROM {$wpdb->users} WHERE user_email != ''" ) as $row )
      $map[ strtolower( $row->user_email ) ] = intval( $row->ID );

    return $map;
  }

  /**
   * Renders rows as a CSV string, with a header row taken from the first row's
   * keys. Written through a php://temp handle so fputcsv() handles all the
   * quoting/escaping.
   *
   * @param array $rows List of associative arrays, all with the same keys.
   * @return string The CSV.
   */
  private static function to_csv( $rows ) {
    $handle = fopen( 'php://temp', 'r+' );

    // The $escape argument is passed explicitly: it's optional, but omitting
    // it is deprecated as of PHP 8.4, and '' is the value that gives standard
    // RFC 4180 quoting rather than PHP's legacy backslash escaping.
    fputcsv( $handle, array_keys( reset( $rows ) ), ',', '"', '' );
    foreach ( $rows as $row )
      fputcsv( $handle, $row, ',', '"', '' );

    rewind( $handle );
    $csv = stream_get_contents( $handle );
    fclose( $handle );

    return $csv;
  }

  /**
   * Resolves the --file value to a full path, naming the file
   * YYYY-MM-DD_HHMM_makers.csv (in site local time) when a directory is given.
   *
   * @param string $target The --file value.
   * @return string Full path to write to.
   */
  private static function resolve_path( $target ) {
    if ( is_dir( $target ) )
      return rtrim( $target, '/' ) . '/' . date_i18n( 'Y-m-d_Hi' ) . '_makers.csv';

    return $target;
  }

  /**
   * Writes a line of run summary to STDERR.
   *
   * @param string $line The line to write.
   */
  private static function report( $line ) {
    fwrite( STDERR, $line . "\n" );
  }
}

\WP_CLI::add_command( 'makers', __NAMESPACE__ . '\\Makers_Command' );
