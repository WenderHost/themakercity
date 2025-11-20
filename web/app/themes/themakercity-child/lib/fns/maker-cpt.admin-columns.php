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
  }
}

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
