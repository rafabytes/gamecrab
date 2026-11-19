<?php
/**
 * CPT "jogo" (URL /cheats/{slug}/) e taxonomias "plataforma" e "franquia".
 *
 * @package gamecrab
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		register_post_type(
			'jogo',
			array(
				'labels'        => array(
					'name'               => __( 'Jogos', 'gamecrab' ),
					'singular_name'      => __( 'Jogo', 'gamecrab' ),
					'add_new'            => __( 'Adicionar jogo', 'gamecrab' ),
					'add_new_item'       => __( 'Adicionar novo jogo', 'gamecrab' ),
					'edit_item'          => __( 'Editar jogo', 'gamecrab' ),
					'new_item'           => __( 'Novo jogo', 'gamecrab' ),
					'view_item'          => __( 'Ver cheats', 'gamecrab' ),
					'search_items'       => __( 'Buscar jogos', 'gamecrab' ),
					'not_found'          => __( 'Nenhum jogo encontrado', 'gamecrab' ),
					'all_items'          => __( 'Todos os jogos', 'gamecrab' ),
					'menu_name'          => __( 'Cheats', 'gamecrab' ),
				),
				'public'        => true,
				'has_archive'   => 'cheats',
				'rewrite'       => array(
					'slug'       => 'cheats',
					'with_front' => false,
				),
				'menu_icon'     => 'dashicons-games',
				'menu_position' => 5,
				'show_in_rest'  => true,
				'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'page-attributes' ),
				'taxonomies'    => array( 'plataforma', 'franquia' ),
			)
		);

		register_taxonomy(
			'plataforma',
			array( 'jogo' ),
			array(
				'labels'            => array(
					'name'          => __( 'Plataformas', 'gamecrab' ),
					'singular_name' => __( 'Plataforma', 'gamecrab' ),
				),
				'public'            => true,
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array(
					'slug'       => 'plataforma',
					'with_front' => false,
				),
			)
		);

		register_taxonomy(
			'franquia',
			array( 'jogo' ),
			array(
				'labels'            => array(
					'name'          => __( 'Franquias', 'gamecrab' ),
					'singular_name' => __( 'Franquia', 'gamecrab' ),
				),
				'public'            => true,
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array(
					'slug'       => 'franquia',
					'with_front' => false,
				),
			)
		);

		register_post_meta(
			'jogo',
			'_gc_data',
			array(
				'type'          => 'string',
				'single'        => true,
				'show_in_rest'  => false,
				'auth_callback' => fn() => current_user_can( 'edit_posts' ),
			)
		);
	}
);

/**
 * A ficha de cheats é estruturada: a tela clássica dá espaço total à meta box.
 */
add_filter(
	'use_block_editor_for_post_type',
	fn( $use, $type ) => 'jogo' === $type ? false : $use,
	10,
	2
);

/**
 * Arquivo /cheats/ ordena por menu_order e depois título; mostra todos.
 */
add_action(
	'pre_get_posts',
	function ( WP_Query $q ) {
		if ( is_admin() || ! $q->is_main_query() ) {
			return;
		}
		if ( $q->is_post_type_archive( 'jogo' ) || $q->is_tax( array( 'plataforma', 'franquia' ) ) ) {
			$q->set( 'posts_per_page', 48 );
			$q->set(
				'orderby',
				array(
					'menu_order' => 'ASC',
					'title'      => 'ASC',
				)
			);
		}
	}
);
