<?php
/**
 * Plugin Name: Block REST Batch Endpoint (emergency mitigation)
 * Description: Blocks WordPress core's /wp-json/batch/v1 endpoint. Added 2026-08-05 as an emergency mitigation for an actively-exploited SQL injection reachable through it (see sites/themakercity.org/incidents/). Safe to remove once WordPress core ships a fix and the site has been re-verified.
 *
 * Priority PHP_INT_MAX is deliberate: ACF Pro's own rest_pre_dispatch hook
 * (class-acf-rest-api.php) runs at the default priority 10 and unconditionally
 * returns null from its callback, clobbering whatever a same-or-earlier-priority
 * filter returned. Running last guarantees this filter's WP_Error wins.
 */

add_filter(
	'rest_pre_dispatch',
	function ( $result, $server, $request ) {
		if ( '/batch/v1' === $request->get_route() ) {
			return new WP_Error(
				'rest_batch_disabled',
				__( 'The REST API batch endpoint is disabled on this site.' ),
				array( 'status' => 403 )
			);
		}
		return $result;
	},
	PHP_INT_MAX,
	3
);
