<?php
namespace TheMakerCity\makerlinkcpt;

/**
 * Add and order admin columns for the Maker Link CPT.
 *
 * @param array $columns Existing admin columns.
 * @return array Modified columns with Title, Maker, Maker Link Categories, and Date.
 */
function makerlink_set_admin_columns( $columns ) {
  return [
    'cb'                    => '<input type="checkbox" />',
    'title'                 => __( 'Title', 'textdomain' ),
    'maker'                 => __( 'Maker', 'textdomain' ),
    'maker_link_categories' => __( 'Maker Link Categories', 'textdomain' ),
    'date'                  => __( 'Date', 'textdomain' ),
  ];
}
add_filter( 'manage_maker-link_posts_columns', __NAMESPACE__ . '\\makerlink_set_admin_columns' );

/**
 * Render the custom column content for Maker Link CPT.
 *
 * @param string $column  Column slug.
 * @param int    $post_id Current post ID.
 */
function makerlink_render_custom_columns( $column, $post_id ) {
  switch ( $column ) {
    case 'maker':
      $maker = get_field( 'maker', $post_id );

      if ( $maker instanceof \WP_Post ) {
        $url = get_edit_post_link( $maker->ID );
        printf(
          '<a href="%s">%s</a>',
          esc_url( $url ),
          esc_html( get_the_title( $maker->ID ) )
        );
      } else {
        echo '<em>' . esc_html__( 'No Maker', 'textdomain' ) . '</em>';
      }
      break;

    case 'maker_link_categories':
      $terms = get_the_terms( $post_id, 'maker-link-category' );

      if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
        $links = [];

        foreach ( $terms as $term ) {
          $url = add_query_arg(
            [
              'post_type'            => 'maker-link',
              'maker-link-category'  => $term->slug,
            ],
            admin_url( 'edit.php' )
          );

          $links[] = sprintf(
            '<a href="%s">%s</a>',
            esc_url( $url ),
            esc_html( $term->name )
          );
        }

        echo implode( ', ', $links );
      } else {
        echo '<em>' . esc_html__( 'No Categories', 'textdomain' ) . '</em>';
      }
      break;
  }
}
add_action( 'manage_maker-link_posts_custom_column', __NAMESPACE__ . '\\makerlink_render_custom_columns', 10, 2 );

/**
 * Make custom columns sortable.
 *
 * @param array $columns Sortable columns.
 * @return array Modified sortable columns.
 */
function makerlink_sortable_columns( $columns ) {
  $columns['maker']                 = 'maker';
  $columns['maker_link_categories'] = 'maker_link_categories';
  return $columns;
}
add_filter( 'manage_edit-maker-link_sortable_columns', __NAMESPACE__ . '\\makerlink_sortable_columns' );

/**
 * Modify query to enable sorting by Maker and Maker Link Categories.
 *
 * @param \WP_Query $query The current query.
 */
function makerlink_sort_by_custom_columns( $query ) {
  if ( ! is_admin() || ! $query->is_main_query() ) {
    return;
  }

  $orderby = $query->get( 'orderby' );

  // Sort by Maker post title.
  if ( 'maker' === $orderby ) {
    $query->set( 'meta_key', 'maker' );
    $query->set( 'orderby', 'meta_value' );
  }

  // Sort by taxonomy.
  if ( 'maker_link_categories' === $orderby ) {
    $query->set( 'orderby', 'taxonomy' );
    $query->set( 'tax_query', [
      [
        'taxonomy' => 'maker-link-category',
        'field'    => 'term_id',
      ],
    ] );
  }
}
add_action( 'pre_get_posts', __NAMESPACE__ . '\\makerlink_sort_by_custom_columns' );
