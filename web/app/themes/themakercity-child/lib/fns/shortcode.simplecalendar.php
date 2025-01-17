<?php

function get_simple_calendar($atts) {
  // Extract shortcode attributes
  $atts = shortcode_atts(array(
    'type' => 'meetings'
  ), $atts, 'maker');

  ob_start();

  if (have_rows('meetings__date')) {
    $opening_tag = '';
    $closing_tag = '';
    $item_opening_tag = '';
    $item_closing_tag = '';
    $before_date = '';
    $after_date = '';

    switch ($atts['type']) {
      case 'markets':
        $opening_tag = '<div class="markets-list">';
        $closing_tag = '</div>';
        $item_opening_tag = '<div class="market-item">';
        $item_closing_tag = '</div>';
        $before_date = '<p>';
        $after_date = '</p>';
        break;
      default:
        $opening_tag = '<div class="meetings-list"><ul>';
        $closing_tag = '</ul><em>All topics are tentative</em></div>';
        $item_opening_tag = '<li>';
        $item_closing_tag = '</li>';
        $before_date = '';
        $after_date = ' - ';
        break;
    }

    echo $opening_tag;

    while (have_rows('meetings__date')): the_row();
      $date = get_sub_field('date_meetings');
      $link = get_sub_field('meetings_link');
      $description = get_sub_field('meetings_short_description');

      echo $item_opening_tag;

      echo $before_date . esc_html($date) . $after_date;

      if (!empty($description)) {
        if (!empty($link)) {
          echo '<a href="' . esc_url($link) . '">' . esc_html($description) . '</a>';
        } else {
          echo esc_html($description);
        }
      } else {
        echo 'TBD';
      }

      echo $item_closing_tag;

    endwhile;

    echo $closing_tag;
  } else {
    echo '<p>No content found.</p>';
  }

  return ob_get_clean();
}
add_shortcode('simplecalendar', 'get_simple_calendar');