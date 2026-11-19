<?php
/**
 * REST: índice leve de busca (jogos + cheats) consumido pela paleta de busca (React).
 * GET /wp-json/gamecrab/v1/index
 *
 * @package gamecrab
 */

defined( 'ABSPATH' ) || exit;

function gc_build_search_index(): array {
	$cached = get_transient( 'gc_search_index' );
	if ( is_array( $cached ) ) {
		return $cached;
	}
	$games = array();
	$posts = get_posts(
		array(
			'post_type'      => 'jogo',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		)
	);
	foreach ( $posts as $p ) {
		$d      = gc_get_data( $p );
		$url    = get_permalink( $p );
		$cheats = array();
		foreach ( $d['sections'] as $s ) {
			foreach ( $s['cheats'] as $c ) {
				if ( 'expirado' === $c['status'] ) {
					continue;
				}
				$first    = $c['rendered'] ? reset( $c['rendered'] ) : null;
				$cheats[] = array(
					'n' => $c['name'],
					'e' => wp_strip_all_tags( $c['effect'] ),
					'c' => $first ? $first['copy'] : '',
					't' => $first ? $first['type'] : 'text',
					'u' => $url . '#' . $c['id'],
				);
			}
		}
		$games[] = array(
			'id'        => $p->ID,
			'title'     => get_the_title( $p ),
			'url'       => $url,
			'kicker'    => $d['kicker'],
			'accent'    => $d['accent'],
			'platforms' => gc_platform_names( $p ),
			'cheats'    => $cheats,
		);
	}
	set_transient( 'gc_search_index', $games, DAY_IN_SECONDS );
	return $games;
}

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'gamecrab/v1',
			'/index',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => function () {
					$res = rest_ensure_response( gc_build_search_index() );
					$res->header( 'Cache-Control', 'public, max-age=600' );
					return $res;
				},
			)
		);
	}
);
