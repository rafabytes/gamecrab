<?php
/**
 * Meta box "Ficha de cheats" — editor visual (src/admin.js) com fallback em JSON.
 * Sem dependência de plugins (ACF etc.).
 *
 * @package gamecrab
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'add_meta_boxes_jogo',
	function () {
		add_meta_box( 'gc-data', __( 'Ficha de cheats (gameCrab)', 'gamecrab' ), 'gc_render_metabox', 'jogo', 'normal', 'high' );
	}
);

function gc_render_metabox( WP_Post $post ): void {
	wp_nonce_field( 'gc_save_data', 'gc_data_nonce' );
	$json = get_post_meta( $post->ID, '_gc_data', true );
	if ( ! $json ) {
		$json = wp_json_encode(
			array(
				'variants' => array(
					array(
						'key'   => 'code',
						'label' => 'Código',
						'type'  => 'text',
					),
				),
				'sections' => array(),
			)
		);
	}
	$pretty = wp_json_encode( json_decode( $json, true ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	?>
	<div id="gc-editor" data-statuses="<?php echo esc_attr( wp_json_encode( wp_list_pluck( gc_statuses(), 'label' ) ) ); ?>"></div>
	<details class="gc-json-fallback">
		<summary><?php esc_html_e( 'Editar como JSON (avançado)', 'gamecrab' ); ?></summary>
		<p class="description"><?php esc_html_e( 'O editor visual acima mantém este campo sincronizado. Sem JavaScript, edite diretamente aqui.', 'gamecrab' ); ?></p>
		<textarea id="gc-data-json" name="gc_data_json" rows="18" class="large-text code" spellcheck="false"><?php echo esc_textarea( $pretty ); ?></textarea>
	</details>
	<?php
}

add_action(
	'save_post_jogo',
	function ( $post_id ) {
		if ( ! isset( $_POST['gc_data_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gc_data_nonce'] ) ), 'gc_save_data' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( ! isset( $_POST['gc_data_json'] ) ) {
			return;
		}
		$data = json_decode( wp_unslash( $_POST['gc_data_json'] ), true ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitizado em gc_sanitize_data().
		if ( ! is_array( $data ) ) {
			return;
		}
		update_post_meta( $post_id, '_gc_data', wp_slash( wp_json_encode( gc_sanitize_data( $data ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) );
	}
);

/**
 * Sanitização estrita do esquema.
 */
function gc_sanitize_data( array $d ): array {
	$txt  = fn( $v ) => sanitize_text_field( (string) $v );
	$rich = fn( $v ) => wp_kses(
		(string) $v,
		array(
			'strong' => array(),
			'em'     => array(),
			'code'   => array(),
			'a'      => array( 'href' => array() ),
			'br'     => array(),
		)
	);

	$out = array(
		'seo_title'       => $txt( $d['seo_title'] ?? '' ),
		'seo_description' => $txt( $d['seo_description'] ?? '' ),
		'kicker'          => $txt( $d['kicker'] ?? '' ),
		'developer'       => $txt( $d['developer'] ?? '' ),
		'release'         => $txt( $d['release'] ?? '' ),
		'accent'          => sanitize_hex_color( $d['accent'] ?? '' ) ?: '#ff4d2e',
		'updated'         => preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) ( $d['updated'] ?? '' ) ) ? $d['updated'] : current_time( 'Y-m-d' ),
		'notice'          => array(),
		'how_to'          => array(),
		'variants'        => array(),
		'sections'        => array(),
		'tips'            => array(),
		'faq'             => array(),
		'sources'         => array(),
	);

	if ( ! empty( $d['notice']['text'] ) ) {
		$out['notice'] = array(
			'tone'  => in_array( $d['notice']['tone'] ?? '', array( 'info', 'warn' ), true ) ? $d['notice']['tone'] : 'info',
			'title' => $txt( $d['notice']['title'] ?? '' ),
			'text'  => $rich( $d['notice']['text'] ),
		);
	}

	foreach ( (array) ( $d['how_to'] ?? array() ) as $h ) {
		$steps = array_values( array_filter( array_map( $rich, (array) ( $h['steps'] ?? array() ) ) ) );
		if ( $steps ) {
			$out['how_to'][] = array(
				'title' => $txt( $h['title'] ?? '' ),
				'steps' => $steps,
			);
		}
	}

	$keys = array();
	foreach ( (array) ( $d['variants'] ?? array() ) as $v ) {
		$key = sanitize_key( $v['key'] ?? '' );
		if ( ! $key || isset( $keys[ $key ] ) ) {
			continue;
		}
		$keys[ $key ] = true;
		$row          = array(
			'key'   => $key,
			'label' => $txt( $v['label'] ?? $key ),
			'type'  => in_array( $v['type'] ?? '', array( 'buttons', 'text', 'phone' ), true ) ? $v['type'] : 'text',
		);
		if ( ! empty( $v['derive'] ) ) {
			$row['derive'] = sanitize_key( $v['derive'] );
		}
		$out['variants'][] = $row;
	}

	$statuses = array_keys( gc_statuses() );
	foreach ( (array) ( $d['sections'] ?? array() ) as $s ) {
		$cheats = array();
		foreach ( (array) ( $s['cheats'] ?? array() ) as $c ) {
			if ( empty( $c['name'] ) ) {
				continue;
			}
			$codes = array();
			foreach ( (array) ( $c['codes'] ?? array() ) as $k => $code ) {
				$k = sanitize_key( $k );
				if ( isset( $keys[ $k ] ) && '' !== trim( (string) $code ) ) {
					$codes[ $k ] = $txt( $code );
				}
			}
			$cheats[] = array(
				'name'   => $txt( $c['name'] ),
				'effect' => $rich( $c['effect'] ?? '' ),
				'codes'  => $codes,
				'status' => in_array( $c['status'] ?? '', $statuses, true ) ? $c['status'] : 'confirmado',
				'unlock' => $rich( $c['unlock'] ?? '' ),
				'note'   => $rich( $c['note'] ?? '' ),
			);
		}
		$out['sections'][] = array(
			'title'  => $txt( $s['title'] ?? '' ),
			'intro'  => $rich( $s['intro'] ?? '' ),
			'cheats' => $cheats,
		);
	}

	$out['tips'] = array_values( array_filter( array_map( $rich, (array) ( $d['tips'] ?? array() ) ) ) );

	foreach ( (array) ( $d['faq'] ?? array() ) as $f ) {
		if ( ! empty( $f['q'] ) && ! empty( $f['a'] ) ) {
			$out['faq'][] = array(
				'q' => $txt( $f['q'] ),
				'a' => $rich( $f['a'] ),
			);
		}
	}

	foreach ( (array) ( $d['sources'] ?? array() ) as $src ) {
		$url = esc_url_raw( $src['url'] ?? '' );
		if ( $url ) {
			$out['sources'][] = array(
				'label' => $txt( $src['label'] ?? $url ),
				'url'   => $url,
			);
		}
	}

	return $out;
}
