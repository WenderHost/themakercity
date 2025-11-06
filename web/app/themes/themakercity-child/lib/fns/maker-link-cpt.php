<?php
namespace TheMakerCity\makerlinkcpt;

/**
 * Adjust the Thumbnail column width for Maker Link CPT list table.
 */
function makerlink_admin_column_styles() {
  $screen = get_current_screen();
  if ( isset( $screen->post_type ) && 'maker-link' === $screen->post_type ) {
    echo '<style>
      .column-thumbnail {
        width: 70px !important;
        max-width: 70px !important;
        text-align: center;
      }
      .column-thumbnail img {
        width: 64px;
        height: 64px;
        object-fit: cover;
        border-radius: 4px;
      }
    </style>';
  }
}
add_action( 'admin_head', __NAMESPACE__ . '\\makerlink_admin_column_styles' );

/**
 * Renders the Maker Link caption.
 *
 * Shortcode: [maker_link_caption link="true" target="_blank"]
 *
 * Output:
 * <strong>{Maker Name}</strong><br>
 * {Description}<br>
 * <span class="price">{Price}</span>
 *
 * If `link="true"`, wraps the output in an <a> tag linking to the value
 * of the "link" ACF field on the current maker-link post.
 * Optionally specify `target` to control link behavior.
 *
 * @param array $atts {
 *   Optional. Shortcode attributes.
 *
 *   @type bool   $link   Whether to wrap the caption in a link. Default false.
 *   @type string $target The link target attribute (e.g. "_blank"). Default "_self".
 * }
 * @return string HTML markup for the caption.
 */
function makerlink_caption_shortcode( $atts = [] ) {
  $atts = shortcode_atts(
    [
      'link'   => false,
      'target' => '_self',
    ],
    $atts,
    'maker_link_caption'
  );

  $atts['link'] = filter_var( $atts['link'], FILTER_VALIDATE_BOOLEAN );

  if ( ! is_singular( 'maker-link' ) ) {
    return '';
  }

  $post_id     = get_the_ID();
  $maker_post  = get_field( 'maker', $post_id );
  $description = get_field( 'description', $post_id );
  $price       = get_field( 'price', $post_id );
  $link_url    = get_field( 'link', $post_id );

  $maker_name = $maker_post instanceof \WP_Post ? $maker_post->post_title : '';

  if ( ! $maker_name && ! $description && ! $price ) {
    return '';
  }

  $output  = '<strong>' . esc_html( $maker_name ) . '</strong><br>';
  $output .= esc_html( $description ) . '<br>';
  $output .= '<span class="price">$' . esc_html( $price ) . '</span>';

  if ( $atts['link'] && $link_url ) {
    $output = sprintf(
      '<a href="%s" target="%s">%s</a>',
      esc_url( $link_url ),
      esc_attr( $atts['target'] ),
      $output
    );
  }

  return $output;
}
add_shortcode( 'maker_link_caption', __NAMESPACE__ . '\\makerlink_caption_shortcode' );



/**
 * Add and order admin columns for the Maker Link CPT.
 *
 * @param array $columns Existing admin columns.
 * @return array Modified columns with Thumbnail, Title, Maker, Maker Link Categories, Maker Link Tags, and Date.
 */
function makerlink_set_admin_columns( $columns ) {
  return [
    'cb'                    => '<input type="checkbox" />',
    'thumbnail'             => __( 'Thumbnail', 'textdomain' ),
    'title'                 => __( 'Title', 'textdomain' ),
    'maker'                 => __( 'Maker', 'textdomain' ),
    'maker_link_categories' => __( 'Maker Link Categories', 'textdomain' ),
    'maker_link_tags'       => __( 'Maker Link Tags', 'textdomain' ),
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
    case 'thumbnail':
      $thumbnail = get_the_post_thumbnail(
        $post_id,
        [ 64, 64 ],
        [
          'style' => 'width:64px;height:64px;object-fit:cover;border-radius:4px;',
        ]
      );

      if ( $thumbnail ) {
        echo $thumbnail;
      } else {
        echo '<div style="width:64px;height:64px;background:#f0f0f0;border:1px solid #ccc;border-radius:4px;"></div>';
      }
      break;

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
              'post_type'           => 'maker-link',
              'maker-link-category' => $term->slug,
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

    case 'maker_link_tags':
      $terms = get_the_terms( $post_id, 'maker-link-tag' );

      if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
        $links = [];

        foreach ( $terms as $term ) {
          $url = add_query_arg(
            [
              'post_type'      => 'maker-link',
              'maker-link-tag' => $term->slug,
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
        echo '<em>' . esc_html__( 'No Tags', 'textdomain' ) . '</em>';
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
  $columns['maker_link_tags']       = 'maker_link_tags';
  return $columns;
}
add_filter( 'manage_edit-maker-link_sortable_columns', __NAMESPACE__ . '\\makerlink_sortable_columns' );

/**
 * Modify query to enable sorting by Maker, Maker Link Categories, and Maker Link Tags.
 *
 * @param \WP_Query $query The current query.
 */
function makerlink_sort_by_custom_columns( $query ) {
  if ( ! is_admin() || ! $query->is_main_query() ) {
    return;
  }

  $orderby = $query->get( 'orderby' );

  if ( 'maker' === $orderby ) {
    $query->set( 'meta_key', 'maker' );
    $query->set( 'orderby', 'meta_value' );
  }

  if ( 'maker_link_categories' === $orderby ) {
    $query->set( 'orderby', 'taxonomy' );
    $query->set(
      'tax_query',
      [
        [
          'taxonomy' => 'maker-link-category',
          'field'    => 'term_id',
        ],
      ]
    );
  }

  if ( 'maker_link_tags' === $orderby ) {
    $query->set( 'orderby', 'taxonomy' );
    $query->set(
      'tax_query',
      [
        [
          'taxonomy' => 'maker-link-tag',
          'field'    => 'term_id',
        ],
      ]
    );
  }
}
add_action( 'pre_get_posts', __NAMESPACE__ . '\\makerlink_sort_by_custom_columns' );
