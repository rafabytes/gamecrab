<?php
/**
 * Enfileiramento de assets compilados pelo Parcel (/dist).
 *
 * @package gamecrab
 */

defined( 'ABSPATH' ) || exit;

/**
 * URL + versão (filemtime) de um arquivo de /dist.
 */
function gc_asset( string $file ): array {
	$path = GC_DIR . '/dist/' . $file;
	return array(
		'url'     => GC_URI . '/dist/' . $file,
		'version' => file_exists( $path ) ? (string) filemtime( $path ) : GC_VERSION,
		'exists'  => file_exists( $path ),
	);
}

add_action(
	'wp_enqueue_scripts',
	function () {
		$css = gc_asset( 'main.css' );
		$js  = gc_asset( 'main.js' );

		wp_enqueue_style( 'gamecrab', $css['url'], array(), $css['version'] );

		wp_enqueue_script(
			'gamecrab',
			$js['url'],
			array(),
			$js['version'],
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);

		wp_localize_script(
			'gamecrab',
			'gcConfig',
			array(
				'home'    => home_url( '/' ),
				'rest'    => esc_url_raw( rest_url( 'gamecrab/v1/' ) ),
				'archive' => get_post_type_archive_link( 'jogo' ),
				'search'  => home_url( '/?s=' ),
			)
		);

		// Tema clássico: não precisamos dos estilos globais do editor de blocos no front,
		// exceto em conteúdo editorial (páginas/posts) que podem usar blocos.
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
		if ( ! is_singular( array( 'post', 'page', 'jogo' ) ) ) {
			wp_dequeue_style( 'wp-block-library' );
		}
	},
	20
);

/**
 * Script inline no <head>, antes de qualquer pintura, para aplicar o tema
 * (dia/noite) sem "flash". Lê preferência salva ou a do sistema.
 */
add_action(
	'wp_head',
	function () {
		?>
<script>(function(){try{var t=localStorage.getItem('gc-theme');if(t!=='day'&&t!=='night'){t=matchMedia('(prefers-color-scheme: light)').matches?'day':'night';}document.documentElement.dataset.theme=t;}catch(e){document.documentElement.dataset.theme='night';}document.documentElement.classList.add('js');})();</script>
		<?php
	},
	0
);

/**
 * Preload das fontes latinas críticas (nomes com hash do Parcel → glob).
 */
add_action(
	'wp_head',
	function () {
		foreach ( array( 'manrope-latin-wght-normal' ) as $font ) {
			$files = glob( GC_DIR . '/dist/' . $font . '.*.woff2' );
			if ( $files ) {
				echo '<link rel="preload" href="' . esc_url( GC_URI . '/dist/' . basename( $files[0] ) ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
			}
		}
	},
	1
);

/**
 * Admin: assets do editor de cheats (meta box).
 */
add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen || 'jogo' !== $screen->post_type || ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		$js  = gc_asset( 'admin.js' );
		$css = gc_asset( 'admin.css' );
		wp_enqueue_style( 'gamecrab-admin', $css['url'], array(), $css['version'] );
		wp_enqueue_script( 'gamecrab-admin', $js['url'], array(), $js['version'], true );
	}
);

/**
 * Preconnect/hints: nada externo — fontes são self-hosted pelo Parcel.
 * Removemos o dns-prefetch de s.w.org (emoji) que não usamos.
 */
add_filter(
	'wp_resource_hints',
	function ( $urls, $relation ) {
		if ( 'dns-prefetch' === $relation ) {
			$urls = array_filter(
				$urls,
				fn( $u ) => ! is_string( $u ) || false === strpos( $u, 's.w.org' )
			);
		}
		return $urls;
	},
	10,
	2
);
