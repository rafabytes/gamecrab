<?php
/**
 * Importador de conteúdo inicial (data/seed/*.json).
 * Idempotente: atualiza jogos/páginas existentes pelo slug.
 *
 * Uso: Aparência › gameCrab › "Importar conteúdo"  —  ou  —  wp gamecrab seed
 *
 * @package gamecrab
 */

defined( 'ABSPATH' ) || exit;

function gc_seed_content(): array {
	$log = array();
	$dir = GC_DIR . '/data/seed';

	// Permalinks amigáveis (SEO) — só se ainda estiver no padrão com index.php ou "plain".
	$structure = (string) get_option( 'permalink_structure' );
	if ( '' === $structure || false !== strpos( $structure, 'index.php' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
		$log[] = 'Permalinks: /%postname%/';
	}

	update_option( 'blogdescription', 'Cheats, códigos e manhas de jogos públicos — verificados e explicados' );

	// Jogos.
	$order = 0;
	foreach ( glob( $dir . '/games/*.json' ) as $file ) {
		$g = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( ! is_array( $g ) || empty( $g['slug'] ) ) {
			$log[] = 'JSON inválido: ' . basename( $file );
			continue;
		}
		$existing = get_page_by_path( $g['slug'], OBJECT, 'jogo' );
		$postarr  = array(
			'post_type'    => 'jogo',
			'post_status'  => $g['status'] ?? 'publish',
			'post_name'    => $g['slug'],
			'post_title'   => $g['title'],
			'post_excerpt' => $g['excerpt'] ?? '',
			'post_content' => $g['content'] ?? '',
			'menu_order'   => $g['order'] ?? ++$order,
		);
		if ( $existing ) {
			$postarr['ID'] = $existing->ID;
			$id            = wp_update_post( wp_slash( $postarr ), true );
		} else {
			$id = wp_insert_post( wp_slash( $postarr ), true );
		}
		if ( is_wp_error( $id ) ) {
			$log[] = 'Erro em ' . $g['slug'] . ': ' . $id->get_error_message();
			continue;
		}
		update_post_meta( $id, '_gc_data', wp_slash( wp_json_encode( gc_sanitize_data( $g['data'] ?? array() ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) );

		// Capa do jogo (data/seed/covers/{slug}.jpg|png) → imagem destacada.
		$covers = glob( $dir . '/covers/' . $g['slug'] . '.{jpg,jpeg,png,webp}', GLOB_BRACE );
		if ( $covers ) {
			$name   = preg_replace( '/^(Cheats|Códigos)\s+(de|do|da)\s+/u', '', $g['title'] );
			$att_id = gc_import_image( $covers[0], 'Capa de ' . $name, 'Capa do jogo ' . $name, $id );
			if ( $att_id ) {
				set_post_thumbnail( $id, $att_id );
			}
		}

		foreach ( array( 'plataforma' => $g['platforms'] ?? array(), 'franquia' => (array) ( $g['franchise'] ?? array() ) ) as $tax => $names ) {
			$ids = array();
			foreach ( $names as $name ) {
				$term = term_exists( $name, $tax );
				if ( ! $term ) {
					$term = wp_insert_term( $name, $tax );
				}
				if ( ! is_wp_error( $term ) ) {
					$ids[] = (int) $term['term_id'];
				}
			}
			wp_set_object_terms( $id, $ids, $tax );
		}
		$log[] = ( $existing ? 'Atualizado' : 'Criado' ) . ': ' . $g['title'];
	}

	// Descrições de taxonomias.
	$terms_file = $dir . '/terms.json';
	if ( file_exists( $terms_file ) ) {
		$terms = json_decode( (string) file_get_contents( $terms_file ), true ); // phpcs:ignore
		foreach ( (array) $terms as $tax => $list ) {
			foreach ( $list as $name => $info ) {
				$t = term_exists( $name, $tax );
				if ( ! $t ) {
					$t = wp_insert_term( $name, $tax );
				}
				if ( ! is_wp_error( $t ) ) {
					wp_update_term(
						(int) $t['term_id'],
						$tax,
						array(
							'description' => $info['description'] ?? '',
							'slug'        => $info['slug'] ?? sanitize_title( $name ),
						)
					);
				}
			}
		}
	}

	// Páginas institucionais.
	$pages_file = $dir . '/pages.json';
	if ( file_exists( $pages_file ) ) {
		foreach ( (array) json_decode( (string) file_get_contents( $pages_file ), true ) as $p ) { // phpcs:ignore
			$existing = get_page_by_path( $p['slug'] );
			$arr      = array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_name'    => $p['slug'],
				'post_title'   => $p['title'],
				'post_content' => $p['content'],
				'post_excerpt' => $p['excerpt'] ?? '',
			);
			if ( $existing ) {
				$arr['ID'] = $existing->ID;
				wp_update_post( wp_slash( $arr ) );
			} else {
				wp_insert_post( wp_slash( $arr ) );
			}
			$log[] = 'Página: ' . $p['title'];
		}
	}

	// Página de privacidade do WP (rascunho padrão) aponta para a nossa.
	$priv = get_page_by_path( 'politica-de-privacidade' );
	if ( $priv ) {
		update_option( 'wp_page_for_privacy_policy', $priv->ID );
	}

	// Remove o "Olá, mundo!" e a "Página de exemplo" padrão, se intocados.
	foreach ( array( array( 'ola-mundo', 'post' ), array( 'pagina-exemplo', 'page' ), array( 'hello-world', 'post' ), array( 'sample-page', 'page' ) ) as $def ) {
		$p = get_page_by_path( $def[0], OBJECT, $def[1] );
		if ( $p && $p->post_modified === $p->post_date ) {
			wp_trash_post( $p->ID );
			$log[] = 'Lixeira: ' . $p->post_title;
		}
	}

	// Menus.
	gc_seed_menu(
		'gameCrab — Principal',
		'primary',
		array(
			array( 'Todos os cheats', get_post_type_archive_link( 'jogo' ) ),
			array( 'GTA', gc_term_url( 'gta', 'franquia' ) ),
			array( 'The Sims', gc_term_url( 'the-sims', 'franquia' ) ),
			array( 'Roblox', gc_term_url( 'roblox', 'franquia' ) ),
			array( 'Sobre', gc_page_url( 'sobre' ) ),
		)
	);
	gc_seed_menu(
		'gameCrab — Rodapé',
		'footer',
		array(
			array( 'Sobre o gameCrab', gc_page_url( 'sobre' ) ),
			array( 'Como verificamos', gc_page_url( 'como-verificamos' ) ),
			array( 'Política de privacidade', gc_page_url( 'politica-de-privacidade' ) ),
		)
	);

	if ( gc_ensure_site_icon() ) {
		$log[] = 'Ícone do site definido.';
	}

	delete_transient( 'gc_site_stats' );
	delete_transient( 'gc_search_index' );
	flush_rewrite_rules( true );
	$log[] = 'Rewrite rules atualizadas.';
	return $log;
}

/**
 * Importa um arquivo local para a biblioteca de mídia (idempotente pelo hash do arquivo).
 */
function gc_import_image( string $path, string $title, string $alt, int $parent = 0 ): int {
	if ( ! is_readable( $path ) || ! @getimagesize( $path ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors
		return 0;
	}
	$hash     = md5_file( $path );
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_gc_seed_hash', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => $hash, // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	if ( $existing ) {
		return (int) $existing[0];
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	$upload = wp_upload_bits( basename( $path ), null, (string) file_get_contents( $path ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( ! empty( $upload['error'] ) ) {
		return 0;
	}
	$type   = wp_check_filetype( $upload['file'] );
	$att_id = wp_insert_attachment(
		array(
			'post_mime_type' => $type['type'],
			'post_title'     => $title,
			'post_status'    => 'inherit',
		),
		$upload['file'],
		$parent
	);
	if ( is_wp_error( $att_id ) || ! $att_id ) {
		return 0;
	}
	wp_update_attachment_metadata( $att_id, wp_generate_attachment_metadata( $att_id, $upload['file'] ) );
	update_post_meta( $att_id, '_wp_attachment_image_alt', $alt );
	update_post_meta( $att_id, '_gc_seed_hash', $hash );
	return (int) $att_id;
}

/**
 * Define o ícone do site do WordPress (favicon/app icon) com assets/site-icon.png, se ainda não houver.
 */
function gc_ensure_site_icon(): bool {
	if ( has_site_icon() ) {
		return false;
	}
	$id = gc_import_image( GC_DIR . '/assets/site-icon.png', 'gameCrab — ícone do site', 'gameCrab' );
	if ( ! $id ) {
		return false;
	}
	gc_site_icon_sizes( $id );
	update_option( 'site_icon', $id );
	return true;
}

/**
 * Gera os tamanhos próprios de ícone (32, 180, 192, 270, 512) como faz o Personalizador.
 */
function gc_site_icon_sizes( int $id ): void {
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-site-icon.php';
	$icon = new WP_Site_Icon();
	update_post_meta( $id, '_wp_attachment_context', 'site-icon' );
	add_filter( 'intermediate_image_sizes_advanced', array( $icon, 'additional_sizes' ) );
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, get_attached_file( $id ) ) );
	remove_filter( 'intermediate_image_sizes_advanced', array( $icon, 'additional_sizes' ) );
}

add_action(
	'after_switch_theme',
	function () {
		gc_ensure_site_icon();
	}
);

function gc_term_url( string $slug, string $tax ): string {
	$t = get_term_by( 'slug', $slug, $tax );
	return $t ? (string) get_term_link( $t ) : home_url( '/' );
}

function gc_page_url( string $slug ): string {
	$p = get_page_by_path( $slug );
	return $p ? (string) get_permalink( $p ) : home_url( '/' );
}

function gc_seed_menu( string $name, string $location, array $links ): void {
	$menu = wp_get_nav_menu_object( $name );
	$id   = $menu ? $menu->term_id : wp_create_nav_menu( $name );
	if ( is_wp_error( $id ) ) {
		return;
	}
	foreach ( (array) wp_get_nav_menu_items( $id ) as $item ) {
		wp_delete_post( $item->ID, true );
	}
	foreach ( $links as $i => $l ) {
		wp_update_nav_menu_item(
			$id,
			0,
			array(
				'menu-item-title'    => $l[0],
				'menu-item-url'      => $l[1],
				'menu-item-status'   => 'publish',
				'menu-item-type'     => 'custom',
				'menu-item-position' => $i + 1,
			)
		);
	}
	$locations              = get_theme_mod( 'nav_menu_locations', array() );
	$locations[ $location ] = $id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * Página em Aparência › gameCrab.
 */
add_action(
	'admin_menu',
	function () {
		add_theme_page(
			'gameCrab',
			'gameCrab',
			'manage_options',
			'gamecrab',
			function () {
				$log = array();
				if ( isset( $_POST['gc_seed'] ) && check_admin_referer( 'gc_seed' ) && current_user_can( 'manage_options' ) ) {
					$log = gc_seed_content();
				}
				?>
				<div class="wrap">
					<h1>gameCrab</h1>
					<p><?php esc_html_e( 'Importa/atualiza os jogos e cheats de data/seed/*.json, páginas institucionais, menus e permalinks. Seguro para rodar novamente: atualiza pelo slug.', 'gamecrab' ); ?></p>
					<form method="post">
						<?php wp_nonce_field( 'gc_seed' ); ?>
						<p><button class="button button-primary" name="gc_seed" value="1"><?php esc_html_e( 'Importar conteúdo', 'gamecrab' ); ?></button></p>
					</form>
					<?php if ( $log ) : ?>
						<div class="notice notice-success"><ul><?php foreach ( $log as $l ) { echo '<li>' . esc_html( $l ) . '</li>'; } ?></ul></div>
					<?php endif; ?>
				</div>
				<?php
			}
		);
	}
);

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command(
		'gamecrab seed',
		function () {
			foreach ( gc_seed_content() as $l ) {
				WP_CLI::log( $l );
			}
			WP_CLI::success( 'Conteúdo importado.' );
		}
	);
}
