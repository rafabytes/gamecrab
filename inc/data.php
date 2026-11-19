<?php
/**
 * Modelo de dados dos cheats (meta _gc_data em JSON) + normalização.
 *
 * Esquema bruto:
 * {
 *   seo_title, seo_description, kicker, developer, release, accent, updated,
 *   notice: { tone: info|warn, title, text },
 *   how_to: [ { title, steps: [] } ],
 *   variants: [ { key, label, type: buttons|text|phone, derive? } ],
 *   sections: [ { title, intro, cheats: [ { name, effect, codes: {key: raw}, status, unlock, note } ] } ],
 *   tips: [], faq: [ { q, a } ], sources: [ { label, url } ]
 * }
 *
 * @package gamecrab
 */

defined( 'ABSPATH' ) || exit;

/**
 * Status possíveis de um cheat.
 */
function gc_statuses(): array {
	return array(
		'testado'     => array(
			'label' => __( 'Testado', 'gamecrab' ),
			'desc'  => __( 'Testado pela equipe gameCrab.', 'gamecrab' ),
		),
		'confirmado'  => array(
			'label' => __( 'Confirmado', 'gamecrab' ),
			'desc'  => __( 'Confirmado por fontes confiáveis (oficiais ou múltiplos guias).', 'gamecrab' ),
		),
		'a-confirmar' => array(
			'label' => __( 'A confirmar', 'gamecrab' ),
			'desc'  => __( 'Divulgado por uma fonte, ainda sem confirmação independente.', 'gamecrab' ),
		),
		'expirado'    => array(
			'label' => __( 'Expirado', 'gamecrab' ),
			'desc'  => __( 'Não funciona mais.', 'gamecrab' ),
		),
	);
}

/**
 * Tabela de botões. kind: dpad | face | shoulder.
 */
function gc_button_map(): array {
	return array(
		'UP'       => array( '↑', 'Cima', 'dpad' ),
		'DOWN'     => array( '↓', 'Baixo', 'dpad' ),
		'LEFT'     => array( '←', 'Esquerda', 'dpad' ),
		'RIGHT'    => array( '→', 'Direita', 'dpad' ),
		'X'        => array( '✕', 'Xis', 'face ps-cross' ),
		'CIRCLE'   => array( '○', 'Bola', 'face ps-circle' ),
		'SQUARE'   => array( '□', 'Quadrado', 'face ps-square' ),
		'TRIANGLE' => array( '△', 'Triângulo', 'face ps-triangle' ),
		'L1'       => array( 'L1', 'L1', 'shoulder' ),
		'L2'       => array( 'L2', 'L2', 'shoulder' ),
		'L3'       => array( 'L3', 'L3', 'shoulder' ),
		'R1'       => array( 'R1', 'R1', 'shoulder' ),
		'R2'       => array( 'R2', 'R2', 'shoulder' ),
		'R3'       => array( 'R3', 'R3', 'shoulder' ),
		'XA'       => array( 'A', 'A', 'face xb-a' ),
		'XB'       => array( 'B', 'B', 'face xb-b' ),
		'XX'       => array( 'X', 'X', 'face xb-x' ),
		'XY'       => array( 'Y', 'Y', 'face xb-y' ),
		'LB'       => array( 'LB', 'LB', 'shoulder' ),
		'LT'       => array( 'LT', 'LT', 'shoulder' ),
		'RB'       => array( 'RB', 'RB', 'shoulder' ),
		'RT'       => array( 'RT', 'RT', 'shoulder' ),
		'LS'       => array( 'LS', 'LS', 'shoulder' ),
		'RS'       => array( 'RS', 'RS', 'shoulder' ),
	);
}

/**
 * Converte sequência PlayStation em Xbox (mesma posição física dos botões).
 */
function gc_ps_to_xbox( string $raw ): string {
	$map = array(
		'X'        => 'XA',
		'CIRCLE'   => 'XB',
		'SQUARE'   => 'XX',
		'TRIANGLE' => 'XY',
		'L1'       => 'LB',
		'L2'       => 'LT',
		'R1'       => 'RB',
		'R2'       => 'RT',
		'L3'       => 'LS',
		'R3'       => 'RS',
	);
	$out = array();
	foreach ( gc_split_tokens( $raw ) as $t ) {
		$out[] = $map[ $t ] ?? $t;
	}
	return implode( ', ', $out );
}

/**
 * Divide "R1, R2, LEFT" em tokens normalizados.
 */
function gc_split_tokens( string $raw ): array {
	$aliases = array(
		'CIMA'      => 'UP',
		'BAIXO'     => 'DOWN',
		'ESQUERDA'  => 'LEFT',
		'DIREITA'   => 'RIGHT',
		'O'         => 'CIRCLE',
		'BOLA'      => 'CIRCLE',
		'QUADRADO'  => 'SQUARE',
		'TRIANGULO' => 'TRIANGLE',
		'CROSS'     => 'X',
	);
	$parts = preg_split( '/[\s,]+/', strtoupper( trim( $raw ) ) );
	$out   = array();
	foreach ( (array) $parts as $p ) {
		if ( '' === $p ) {
			continue;
		}
		$out[] = $aliases[ $p ] ?? $p;
	}
	return $out;
}

/**
 * Tokens renderizáveis de uma sequência de botões.
 */
function gc_tokens( string $raw ): array {
	$map    = gc_button_map();
	$tokens = array();
	foreach ( gc_split_tokens( $raw ) as $t ) {
		if ( isset( $map[ $t ] ) ) {
			$tokens[] = array(
				'g' => $map[ $t ][0],
				'l' => $map[ $t ][1],
				'k' => $map[ $t ][2],
			);
		} else {
			$tokens[] = array(
				'g' => $t,
				'l' => $t,
				'k' => 'shoulder',
			);
		}
	}
	return $tokens;
}

/**
 * Dados brutos do jogo.
 */
function gc_get_raw_data( $post = null ): array {
	$post = get_post( $post );
	if ( ! $post ) {
		return array();
	}
	$json = get_post_meta( $post->ID, '_gc_data', true );
	$data = is_string( $json ) && '' !== $json ? json_decode( $json, true ) : array();
	return is_array( $data ) ? $data : array();
}

/**
 * Dados normalizados e enriquecidos (ids/âncoras, códigos derivados, tokens).
 */
function gc_get_data( $post = null ): array {
	static $cache = array();
	$post = get_post( $post );
	if ( ! $post ) {
		return array();
	}
	if ( isset( $cache[ $post->ID ] ) ) {
		return $cache[ $post->ID ];
	}

	$raw  = gc_get_raw_data( $post );
	$data = wp_parse_args(
		$raw,
		array(
			'seo_title'       => '',
			'seo_description' => '',
			'kicker'          => '',
			'developer'       => '',
			'release'         => '',
			'accent'          => '#ff4d2e',
			'updated'         => get_the_modified_date( 'Y-m-d', $post ),
			'notice'          => array(),
			'how_to'          => array(),
			'variants'        => array(),
			'sections'        => array(),
			'tips'            => array(),
			'faq'             => array(),
			'sources'         => array(),
		)
	);

	if ( empty( $data['variants'] ) ) {
		$data['variants'] = array(
			array(
				'key'   => 'code',
				'label' => __( 'Código', 'gamecrab' ),
				'type'  => 'text',
			),
		);
	}

	$statuses = gc_statuses();
	$used_ids = array();
	$total    = 0;
	$counts   = array_fill_keys( array_keys( $statuses ), 0 );

	foreach ( $data['sections'] as $si => &$section ) {
		$section = wp_parse_args(
			$section,
			array(
				'title'  => '',
				'intro'  => '',
				'cheats' => array(),
			)
		);
		$section['id'] = gc_unique_id( gc_slug( $section['title'] ?: 'secao-' . ( $si + 1 ) ), $used_ids );

		foreach ( $section['cheats'] as &$cheat ) {
			$cheat = wp_parse_args(
				$cheat,
				array(
					'name'   => '',
					'effect' => '',
					'codes'  => array(),
					'status' => 'confirmado',
					'unlock' => '',
					'note'   => '',
				)
			);
			if ( ! isset( $statuses[ $cheat['status'] ] ) ) {
				$cheat['status'] = 'confirmado';
			}
			$cheat['id'] = gc_unique_id( gc_slug( $cheat['name'] ?: 'cheat' ), $used_ids );

			$rendered = array();
			foreach ( $data['variants'] as $variant ) {
				$key  = $variant['key'];
				$code = isset( $cheat['codes'][ $key ] ) ? trim( (string) $cheat['codes'][ $key ] ) : '';
				if ( '' === $code && ! empty( $variant['derive'] ) && ! empty( $cheat['codes'][ $variant['derive'] ] ) ) {
					$code = gc_ps_to_xbox( (string) $cheat['codes'][ $variant['derive'] ] );
				}
				if ( '' === $code ) {
					continue;
				}
				$entry = array(
					'raw'  => $code,
					'type' => $variant['type'],
				);
				if ( 'buttons' === $variant['type'] ) {
					$entry['tokens'] = gc_tokens( $code );
					$entry['copy']   = implode( ' ', wp_list_pluck( $entry['tokens'], 'g' ) );
				} else {
					$entry['copy'] = $code;
				}
				$rendered[ $key ] = $entry;
			}
			$cheat['rendered'] = $rendered;

			++$total;
			++$counts[ $cheat['status'] ];
		}
		unset( $cheat );
	}
	unset( $section );

	$data['total']  = $total;
	$data['counts'] = $counts;

	$cache[ $post->ID ] = $data;
	return $data;
}

/**
 * Slug ASCII limpo para âncoras (sem %xx de símbolos como § ou +).
 */
function gc_slug( string $text ): string {
	$text = strtolower( remove_accents( wp_strip_all_tags( $text ) ) );
	$text = preg_replace( '/[^a-z0-9]+/', '-', $text );
	return trim( (string) $text, '-' );
}

/**
 * Garante ids únicos dentro da página (âncoras).
 */
function gc_unique_id( string $base, array &$used ): string {
	$base = $base ?: 'item';
	$id   = $base;
	$i    = 2;
	while ( isset( $used[ $id ] ) ) {
		$id = $base . '-' . $i++;
	}
	$used[ $id ] = true;
	return $id;
}

/**
 * Total de cheats ativos (não expirados) do site — cacheado.
 */
function gc_site_stats(): array {
	$stats = get_transient( 'gc_site_stats' );
	if ( is_array( $stats ) ) {
		return $stats;
	}
	$ids   = get_posts(
		array(
			'post_type'      => 'jogo',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);
	$stats = array(
		'games'    => count( $ids ),
		'cheats'   => 0,
		'verified' => 0,
		'updated'  => '',
	);
	foreach ( $ids as $id ) {
		$d                  = gc_get_data( $id );
		$stats['cheats']   += $d['total'] - $d['counts']['expirado'];
		$stats['verified'] += $d['counts']['testado'] + $d['counts']['confirmado'];
		if ( $d['updated'] > $stats['updated'] ) {
			$stats['updated'] = $d['updated'];
		}
	}
	set_transient( 'gc_site_stats', $stats, DAY_IN_SECONDS );
	return $stats;
}

add_action(
	'save_post_jogo',
	function () {
		delete_transient( 'gc_site_stats' );
		delete_transient( 'gc_search_index' );
	}
);
