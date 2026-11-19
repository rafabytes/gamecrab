<?php
/**
 * Suportes do tema, menus e limpeza do <head>.
 *
 * @package gamecrab
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	function () {
		load_theme_textdomain( 'gamecrab', GC_DIR . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support(
			'html5',
			array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
		);
		add_theme_support( 'editor-styles' );

		register_nav_menus(
			array(
				'primary' => __( 'Menu principal', 'gamecrab' ),
				'footer'  => __( 'Menu do rodapé', 'gamecrab' ),
			)
		);

		add_image_size( 'gc-card', 720, 640, true );
		add_image_size( 'gc-og', 1200, 630, true );
		add_image_size( 'gc-cover', 480, 720, false ); // capas dos jogos (cards de CD)
	}
);

/**
 * Sub-sizes de JPEG em WebP (capas mais leves; original preservado).
 * PNG fica como PNG: o GD não converte PNG com paleta indexada para WebP (erro fatal).
 */
add_filter(
	'image_editor_output_format',
	function ( $formats ) {
		$formats['image/jpeg'] = 'image/webp';
		return $formats;
	}
);

/**
 * Limpa o <head> de coisas que não usamos (performance + menos ruído).
 */
add_action(
	'init',
	function () {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

		// Tema clássico com design system próprio: sem global styles / SVG filters do editor no front.
		remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
		remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
		remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
	}
);

add_filter( 'feed_links_show_comments_feed', '__return_false' );

/**
 * Sem comentários neste momento (sem registro de usuários).
 */
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );

/**
 * Nunca expor usuários (privacidade + segurança): sem sitemap de autores
 * e sem arquivo de autor indexável.
 */
add_filter(
	'wp_sitemaps_add_provider',
	function ( $provider, $name ) {
		return 'users' === $name ? false : $provider;
	},
	10,
	2
);

add_action(
	'template_redirect',
	function () {
		if ( is_author() ) {
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	}
);

/**
 * Classe no <body> para cada tipo de página — útil para transições do Swup.
 */
add_filter(
	'body_class',
	function ( $classes ) {
		$classes[] = 'gc';
		return $classes;
	}
);

/**
 * Excerpt mais curto e sem "[...]".
 */
add_filter( 'excerpt_length', fn() => 28 );
add_filter( 'excerpt_more', fn() => '…' );
