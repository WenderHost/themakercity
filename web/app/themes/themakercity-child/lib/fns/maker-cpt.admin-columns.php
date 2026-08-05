<?php
/**
 * Admin columns for Maker CPT.
 */
add_filter( 'manage_maker_posts_columns', 'tcm_maker_edit_columns' );
function tcm_maker_edit_columns( $columns ) {
  $new_columns = array();

  // Checkbox.
  if ( isset( $columns['cb'] ) ) {
    $new_columns['cb'] = $columns['cb'];
  }

  // Title.
  $new_columns['title'] = __( 'Title', 'textdomain' );

  // Custom taxonomy columns.
  $new_columns['maker_category']   = __( 'Category', 'textdomain' );
  $new_columns['maker_space_type'] = __( 'Space Type', 'textdomain' );
  $new_columns['maker_tag']        = __( 'Tags', 'textdomain' );

  // Author and Date (existing).
  if ( isset( $columns['author'] ) ) {
    $new_columns['author'] = $columns['author'];
  }

  if ( isset( $columns['date'] ) ) {
    $new_columns['date'] = $columns['date'];
  }

  // Last genuine Profile Editor edit (see track_maker_profile_edit_timestamp()
  // in lib/fns/users.php) - placed next to Date so the two can be compared.
  $new_columns['maker_last_edited'] = __( 'Last Edit', 'textdomain' );

  return $new_columns;
}

/**
 * Populate custom taxonomy columns.
 */
add_action( 'manage_maker_posts_custom_column', 'tcm_maker_custom_column', 10, 2 );
function tcm_maker_custom_column( $column, $post_id ) {
  switch ( $column ) {
    case 'maker_category':
      tcm_maker_admin_terms_list( $post_id, 'maker-category' );
      break;

    case 'maker_space_type':
      tcm_maker_admin_terms_list( $post_id, 'maker-space-type' );
      break;

    case 'maker_tag':
      tcm_maker_admin_terms_list( $post_id, 'maker-tag' );
      break;

    case 'maker_last_edited':
      tcm_maker_last_edited_column( $post_id );
      break;
  }
}

/**
 * Renders the "Last Edit" column: the timestamp of this Maker's most
 * recent genuine frontend Profile Editor submission (tracked via
 * track_maker_profile_edit_timestamp() in lib/fns/users.php), NOT
 * WordPress' `post_modified`, which also changes on admin edits and bulk
 * imports and so doesn't reliably indicate the Maker's own activity.
 *
 * This tracking only started once that function shipped, so existing
 * profiles show "Never recorded" until their next frontend edit - there's
 * no way to backfill accurate history, since `post_modified` is exactly
 * the unreliable signal this column exists to replace.
 *
 * @param int $post_id The Post ID.
 */
function tcm_maker_last_edited_column( $post_id ) {
  $last_edited = get_post_meta( $post_id, '_maker_profile_last_edited', true );

  if ( empty( $last_edited ) ) {
    echo '<span style="color:#a7aaad;font-style:italic;">Never recorded</span>';
    return;
  }

  $timestamp = strtotime( $last_edited );

  printf(
    '%1$s at %2$s<br><span style="color:#a7aaad;">(%3$s ago)</span>',
    esc_html( date_i18n( get_option( 'date_format' ), $timestamp ) ),
    esc_html( date_i18n( get_option( 'time_format' ), $timestamp ) ),
    esc_html( human_time_diff( $timestamp, current_time( 'timestamp' ) ) )
  );
}

/**
 * Make the "Last Edit" column sortable.
 *
 * @param array $columns Sortable columns.
 * @return array Modified sortable columns.
 */
function tcm_maker_sortable_columns( $columns ) {
  $columns['maker_last_edited'] = 'maker_last_edited';
  return $columns;
}
add_filter( 'manage_edit-maker_sortable_columns', 'tcm_maker_sortable_columns' );

/**
 * Modify the query to sort by "Last Edit".
 *
 * Deliberately avoids the `meta_key` + `orderby=meta_value` shortcut
 * (it adds its own unaliased `wp_postmeta.meta_key = 'xxx'` WHERE
 * condition that silently excludes every Maker with no postmeta row at
 * all - confirmed locally, it returned zero results) AND the EXISTS/
 * NOT-EXISTS named-clause `meta_query` workaround usually recommended
 * instead (also confirmed locally: WP_Meta_Query reuses the bare,
 * unaliased `wp_postmeta` table - joined with no meta_key restriction
 * at all - for the first clause, so ORDER BY ends up sorting by
 * whichever of a post's *other*, unrelated meta values happens to
 * survive the GROUP BY collapse, not a real NULL). Both are documented
 * WP core gotchas; hand-building one unambiguous LEFT JOIN, aliased and
 * filtered to our exact meta_key in its ON clause, sidesteps them.
 *
 * Note this checks `$typenow`/`$pagenow` rather than
 * `$query->is_main_query()`: the admin list table builds its own
 * standalone WP_Query rather than reusing the global main query, so
 * `is_main_query()` is always false here - confirmed locally, since
 * relying on it (as tcm_maker_filter_query_by_taxonomies() above
 * already did, pre-existing) silently no-ops this hook entirely.
 *
 * @param \WP_Query $query The current query.
 */
function tcm_maker_sort_by_last_edited( $query ) {
  global $pagenow, $typenow;

  if ( ! is_admin() || 'edit.php' !== $pagenow || 'maker' !== $typenow ) {
    return;
  }

  if ( 'maker_last_edited' !== $query->get( 'orderby' ) ) {
    return;
  }

  $order = 'ASC' === strtoupper( $query->get( 'order' ) ) ? 'ASC' : 'DESC';

  add_filter( 'posts_join', function( $join ) {
    global $wpdb;
    $join .= " LEFT JOIN {$wpdb->postmeta} AS tcm_last_edited ON ( {$wpdb->posts}.ID = tcm_last_edited.post_id AND tcm_last_edited.meta_key = '_maker_profile_last_edited' )";
    return $join;
  } );

  add_filter( 'posts_orderby', function() use ( $order ) {
    return "tcm_last_edited.meta_value {$order}";
  } );
}
add_action( 'pre_get_posts', 'tcm_maker_sort_by_last_edited' );

/**
 * Helper to output linked term list in admin columns.
 */
function tcm_maker_admin_terms_list( $post_id, $taxonomy ) {
  $terms = get_the_terms( $post_id, $taxonomy );

  if ( is_wp_error( $terms ) || empty( $terms ) ) {
    echo '&#8212;';
    return;
  }

  $links = array();

  foreach ( $terms as $term ) {
    $url = add_query_arg(
      array(
        'post_type' => 'maker',
        $taxonomy   => $term->slug,
      ),
      admin_url( 'edit.php' )
    );

    $links[] = sprintf(
      '<a href="%1$s">%2$s</a>',
      esc_url( $url ),
      esc_html( $term->name )
    );
  }

  echo implode( ', ', $links );
}

/**
 * Add taxonomy dropdown filters above the Maker list table.
 */
add_action( 'restrict_manage_posts', 'tcm_maker_taxonomy_filters' );
function tcm_maker_taxonomy_filters() {
  global $typenow;

  if ( 'maker' !== $typenow ) {
    return;
  }

  $taxonomies = array(
    'maker-category'   => __( 'All Categories', 'textdomain' ),
    'maker-space-type' => __( 'All Space Types', 'textdomain' ),
    'maker-tag'        => __( 'All Tags', 'textdomain' ),
  );

  foreach ( $taxonomies as $taxonomy => $label ) {
    $selected = isset( $_GET[ $taxonomy ] ) ? sanitize_text_field( wp_unslash( $_GET[ $taxonomy ] ) ) : '';

    wp_dropdown_categories(
      array(
        'show_option_all' => $label,
        'taxonomy'        => $taxonomy,
        'name'            => $taxonomy,
        'orderby'         => 'name',
        'selected'        => $selected,
        'hierarchical'    => true,
        'show_count'      => false,
        'hide_empty'      => false,
        'value_field'     => 'slug',
      )
    );
  }
}

/**
 * Make the taxonomy dropdown filters actually filter the Maker list.
 */
add_filter( 'parse_query', 'tcm_maker_filter_query_by_taxonomies' );
function tcm_maker_filter_query_by_taxonomies( $query ) {
  global $pagenow;

  if ( ! is_admin() || 'edit.php' !== $pagenow || ! $query->is_main_query() ) {
    return;
  }

  $post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : '';

  if ( 'maker' !== $post_type ) {
    return;
  }

  $taxonomies = array(
    'maker-category',
    'maker-space-type',
    'maker-tag',
  );

  $tax_query = array();

  foreach ( $taxonomies as $taxonomy ) {
    if ( empty( $_GET[ $taxonomy ] ) || ! is_string( $_GET[ $taxonomy ] ) ) {
      continue;
    }

    $term = sanitize_text_field( wp_unslash( $_GET[ $taxonomy ] ) );

    // "0" is the "All" option from wp_dropdown_categories().
    if ( '0' === $term ) {
      continue;
    }

    $tax_query[] = array(
      'taxonomy' => $taxonomy,
      'field'    => 'slug',
      'terms'    => $term,
    );
  }

  if ( ! empty( $tax_query ) ) {
    if ( count( $tax_query ) > 1 ) {
      $tax_query['relation'] = 'AND';
    }

    $query->set( 'tax_query', $tax_query );
  }
}
