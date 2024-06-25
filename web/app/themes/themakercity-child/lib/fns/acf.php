<?php
namespace TheMakerCity\acf;

function my_acf_google_map_api( $api ){

    $api['key'] = ( defined('GOOGLE_MAPS_API_KEY') )? GOOGLE_MAPS_API_KEY : null ;
    return $api;
}
add_filter('acf/fields/google_map/api', __NAMESPACE__ . '\\my_acf_google_map_api');