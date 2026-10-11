<?php

if ( ! defined('ABSPATH') ) exit;

add_filter( 'comments_template_query_args', function ( $args ) {
    
	$args['type__not_in'] = array_merge(
		$args['type__not_in'] ?? [],
		[ECM_REVIEWS_TYPE]
	);

	return $args;
});

add_action( 'pre_get_comments', function ( WP_Comment_Query $query ) {
	if ( empty( $query->query_vars['type'] ) ) {
		$query->query_vars['type__not_in'] = [ECM_REVIEWS_TYPE];
	}
});

add_filter( 'wp_list_comments_args', function ( $args ) {

	if ( empty( $args['type'] )  ) {
		$args['type'] = 'comment';
	}

	return $args;
});
