<?php

namespace TheMakerCity\elementor;

add_action( 'elementor/query/maker_directory', function( $query ){
  $query->set( 'orderby', 'post_title' );
  $query->set( 'order', 'ASC' );
});

add_action( 'elementor/query/related_makers', function( $query ) {
    // Ensure we are on a single Maker CPT page.
    if ( !is_singular( 'maker' ) ) {
        return;
    }

    // Get the current post ID.
    $current_post_id = get_the_ID();

    // Get the terms of the current maker in the 'maker-category' taxonomy.
    $terms = wp_get_post_terms( $current_post_id, 'maker-category', array( 'fields' => 'ids' ) );

    // Check if terms exist.
    if ( !empty( $terms ) && !is_wp_error( $terms ) ) {
        // Modify the query to get related makers.
        $query->set( 'post_type', 'maker' ); // Ensure we are querying for 'maker' CPT.
        $query->set( 'post__not_in', array( $current_post_id ) ); // Exclude the current maker from the query.
        $query->set( 'posts_per_page', 3 ); // Limit to three posts.
        $query->set( 'orderby', 'rand' ); // Order by random.

        // Set the tax_query to fetch makers that share any of the same 'maker-category' terms.
        $query->set( 'tax_query', array(
            array(
                'taxonomy' => 'maker-category',
                'field'    => 'term_id',
                'terms'    => $terms,
                'operator' => 'IN',
            ),
        ));
    }
} );


/**
 * Taken from [Feature Request: Option to temporarily hide sections](https://github.com/elementor/elementor/issues/18183)
 */
(function() {
    # Add new control after the Custom CSS control on the Advanced tab.
    add_action(
        'elementor/element/after_section_end',
        function($element, $section_id, $args) {
            if ($section_id === 'section_custom_css') {
                $element->start_controls_section(
                    'omitter_section',
                    [
                        'tab' => \Elementor\Controls_Manager::TAB_ADVANCED,
                        'label' => esc_html__( 'Output', 'yourtextdomain' ),
                    ]
                );
                $element->add_control(
                    'omit_element',
                    [
                        'type' => \Elementor\Controls_Manager::SWITCHER,
                        'label' => 'Omit from output',
                        'label_on' => esc_html__( 'Omit', 'yourtextdomain' ),
                        'label_off' => esc_html__( 'Inherit', 'yourtextdomain' ),
                        'description' => esc_html__( 'Prevent element and its associated assets from being rendered in markup, not merely hide it with CSS.', 'yourtextdomain' ),
                        'selectors_dictionary' => [
                            '' => '',
                            'yes' => 'opacity: 0.25; content: " "; display: block; position: absolute; z-index: 99999; width: 100%; height: 100%; background-image: repeating-linear-gradient(45deg, #f6d32d, #f6d32d 5px, #000 7px, #000 17px, #f6d32d 19px, #f6d32d 24px);',
                        ],
                        'selectors' => [
                            '{{WRAPPER}}::before' => '{{VALUE}}',
                        ],
                    ]
                );
                $element->end_controls_section();
            }
        },
        PHP_INT_MAX,
        3
    );

    # Dynamically add filter to each element type, without knowing them in advance.
    # There is no generic 'elementor/frontend/should_render' hook, so this acts as an equivalent.
    $omitter = function($should_render, $element) {
        return $should_render
            && (
                !($settings = $element->get_settings())
                || empty($settings['omit_element'])
            );
    };
    $element_types = [];
    add_action(
        'elementor/frontend/before_render',
        function($element) use ($omitter, &$element_types) {
            $element_type = $element->get_type();
            if (!array_key_exists($element_type, $element_types)) {
                add_filter(
                    "elementor/frontend/$element_type/should_render",
                    $omitter,
                    PHP_INT_MAX,
                    2
                );
                $element_types[$element_type] = true;
            }
        },
        PHP_INT_MAX
    );
})();