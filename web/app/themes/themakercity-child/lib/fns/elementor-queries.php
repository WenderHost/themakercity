<?php

namespace TheMakerCity\elementor;

add_action( 'elementor/query/maker_directory', function( $query ){
  $query->set( 'orderby', 'post_title' );
  $query->set( 'order', 'ASC' );
});