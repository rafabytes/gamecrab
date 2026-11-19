<?php
/**
 * SEO on-page: título, description, canonical, Open Graph, robots e JSON-LD.
 * Se um plugin de SEO (Yoast, Rank Math, SEOPress, AIOSEO) estiver ativo, o tema se recolhe.
 *
 * @package gamecrab
 */

defined( 'ABSPATH' ) || exit;

function gc_seo_plugin_active(): bool {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'AIOSEO_VERSION' );
}

/**
 * Título <title>.
 */
add_filter(
	'document_title_separator',
	fn() => '·'
);

add_filter(
	'pre_get_document_title',
	function ( $title ) {
		if ( gc_seo_plugin_active() ) {
			return $title;
		}
		if ( is_singular( 'jogo' ) ) {
			$d = gc_get_data();
			if ( $d['seo_title'] ) {
				return $d['seo_title'] . ' · gameCrab';
			}
		}
		if ( is_front_page() ) {
			return 'gameCrab · Cheats, códigos e manhas de jogos verificados';
		}
		if ( is_post_type_archive( 'jogo' ) ) {
			return 'Todos os cheats e códigos de jogos · gameCrab';
		}
		if ( is_tax( 'plataforma' ) ) {
			return sprintf( 'Cheats e códigos para %s · gameCrab', single_term_title( '', false ) );
		}
		if ( is_tax( 'franquia' ) ) {
			return sprintf( 'Cheats de %s: todos os jogos da série · gameCrab', single_term_title( '', false ) );
		}
		return $title;
	}
);

/**
 * Meta description do contexto atual.
 */
function gc_meta_description(): string {
	$desc = '';
	if ( is_singular( 'jogo' ) ) {
		$d    = gc_get_data();
		$desc = $d['seo_description'] ?: get_the_excerpt();
	} elseif ( is_front_page() ) {
		$desc = 'Cheats, códigos e manhas de GTA 6, GTA 5, GTA San Andreas, The Sims 1 a 4 e Dress to Impress (Roblox). Lista atualizada, com selo de verificação e passo a passo para PS5, Xbox e PC.';
	} elseif ( is_post_type_archive( 'jogo' ) ) {
		$desc = 'Todos os cheats do gameCrab em um só lugar: códigos para PlayStation, Xbox, PC e Roblox, com status de verificação e instruções de como ativar.';
	} elseif ( is_tax() ) {
		$term = get_queried_object();
		$desc = $term->description ?: sprintf( 'Cheats e códigos de %s organizados por jogo, com status de verificação e passo a passo de ativação.', $term->name );
	} elseif ( is_singular() ) {
		$desc = has_excerpt() ? get_the_excerpt() : wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', get_the_ID() ) ), 28, '…' );
	}
	$desc = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $desc ) ) );
	return mb_strlen( $desc ) > 160 ? rtrim( mb_substr( $desc, 0, 157 ) ) . '…' : $desc;
}

/**
 * URL canônica do contexto atual.
 */
function gc_canonical(): string {
	$paged = max( 1, (int) get_query_var( 'paged' ) );
	$url   = '';
	if ( is_front_page() ) {
		$url = home_url( '/' );
	} elseif ( is_singular() ) {
		$url = get_permalink();
	} elseif ( is_post_type_archive( 'jogo' ) ) {
		$url = get_post_type_archive_link( 'jogo' );
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$url = get_term_link( get_queried_object() );
	} elseif ( is_home() ) {
		$url = get_permalink( (int) get_option( 'page_for_posts' ) ) ?: home_url( '/' );
	}
	if ( $url && ! is_wp_error( $url ) && $paged > 1 && ! is_singular() ) {
		$url = trailingslashit( $url ) . user_trailingslashit( 'page/' . $paged, 'paged' );
	}
	return is_wp_error( $url ) ? '' : (string) $url;
}

/**
 * Robots: busca e 404 não indexam.
 */
add_filter(
	'wp_robots',
	function ( $robots ) {
		if ( is_search() || is_404() ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
			unset( $robots['max-image-preview'] );
		}
		return $robots;
	}
);

/**
 * Meta tags no <head>.
 */
add_action(
	'wp_head',
	function () {
		echo '<meta name="color-scheme" content="dark light">' . "\n";
		echo '<meta name="theme-color" content="#08080b" media="(prefers-color-scheme: dark)">' . "\n";
		echo '<meta name="theme-color" content="#e8e9ed" media="(prefers-color-scheme: light)">' . "\n";
		// O ícone do site do WP (site_icon) imprime os próprios <link>; o SVG fica como fallback.
		if ( ! has_site_icon() ) {
			echo '<link rel="icon" href="' . esc_url( GC_URI . '/assets/favicon.svg' ) . '" type="image/svg+xml">' . "\n";
		}

		if ( gc_seo_plugin_active() ) {
			return;
		}

		$desc      = gc_meta_description();
		$canonical = gc_canonical();
		$title     = wp_get_document_title();
		$image     = GC_URI . '/assets/og-default.png';
		if ( is_singular() && has_post_thumbnail() ) {
			$image = (string) get_the_post_thumbnail_url( null, 'large' );
		}

		if ( $desc ) {
			echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
		}
		if ( $canonical && ! is_singular() ) {
			// Em páginas singulares o core já imprime rel=canonical.
			echo '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n";
		}

		$og = array(
			'og:locale'      => 'pt_BR',
			'og:site_name'   => 'gameCrab',
			'og:type'        => is_singular( array( 'jogo', 'post' ) ) ? 'article' : 'website',
			'og:title'       => $title,
			'og:description' => $desc,
			'og:url'         => $canonical,
			'og:image'       => $image,
			'og:image:alt'   => 'gameCrab — cheats e códigos de jogos',
		);
		foreach ( $og as $prop => $val ) {
			if ( $val ) {
				echo '<meta property="' . esc_attr( $prop ) . '" content="' . esc_attr( $val ) . '">' . "\n";
			}
		}
		if ( is_singular() ) {
			echo '<meta property="article:modified_time" content="' . esc_attr( get_the_modified_date( 'c' ) ) . '">' . "\n";
		}
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";

		gc_print_jsonld();
	},
	2
);

/**
 * Dados estruturados (schema.org) em um único @graph.
 */
function gc_print_jsonld(): void {
	$home  = home_url( '/' );
	$org   = array(
		'@type' => 'Organization',
		'@id'   => $home . '#org',
		'name'  => 'gameCrab',
		'url'   => $home,
		'logo'  => array(
			'@type' => 'ImageObject',
			'url'   => GC_URI . '/assets/logo-512.png',
		),
		'parentOrganization' => array(
			'@type' => 'Organization',
			'name'  => 'appMaster',
		),
	);
	$site  = array(
		'@type'           => 'WebSite',
		'@id'             => $home . '#website',
		'url'             => $home,
		'name'            => 'gameCrab',
		'inLanguage'      => 'pt-BR',
		'publisher'       => array( '@id' => $home . '#org' ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => $home . '?s={search_term_string}',
			),
			'query-input' => 'required name=search_term_string',
		),
	);
	$graph = array( $org, $site );

	$crumbs = gc_breadcrumb_items();
	if ( count( $crumbs ) > 1 ) {
		$list = array();
		foreach ( $crumbs as $i => $c ) {
			$item = array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'name'     => $c[0],
			);
			if ( $c[1] ) {
				$item['item'] = $c[1];
			}
			$list[] = $item;
		}
		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'@id'             => gc_canonical() . '#breadcrumb',
			'itemListElement' => $list,
		);
	}

	if ( is_singular( 'jogo' ) ) {
		$d     = gc_get_data();
		$url   = get_permalink();
		$game  = array(
			'@type'        => 'VideoGame',
			'name'         => trim( preg_replace( '/^(cheats|códigos)\s+(de|do|da)\s+/iu', '', get_the_title() ) ),
			'gamePlatform' => gc_platform_names(),
		);
		if ( $d['developer'] ) {
			$game['author'] = array(
				'@type' => 'Organization',
				'name'  => $d['developer'],
			);
		}
		$graph[] = array(
			'@type'            => 'Article',
			'@id'              => $url . '#article',
			'headline'         => $d['seo_title'] ?: get_the_title(),
			'description'      => gc_meta_description(),
			'url'              => $url,
			'mainEntityOfPage' => $url,
			'inLanguage'       => 'pt-BR',
			'datePublished'    => get_the_date( 'c' ),
			'dateModified'     => get_the_modified_date( 'c' ),
			'author'           => array( '@id' => $home . '#org' ),
			'publisher'        => array( '@id' => $home . '#org' ),
			'image'            => has_post_thumbnail() ? (string) get_the_post_thumbnail_url( null, 'large' ) : GC_URI . '/assets/og-default.png',
			'about'            => $game,
		);
		if ( ! empty( $d['faq'] ) ) {
			$qa = array();
			foreach ( $d['faq'] as $f ) {
				if ( empty( $f['q'] ) || empty( $f['a'] ) ) {
					continue;
				}
				$qa[] = array(
					'@type'          => 'Question',
					'name'           => wp_strip_all_tags( $f['q'] ),
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => wp_strip_all_tags( $f['a'] ),
					),
				);
			}
			if ( $qa ) {
				$graph[] = array(
					'@type'      => 'FAQPage',
					'@id'        => $url . '#faq',
					'mainEntity' => $qa,
				);
			}
		}
	} elseif ( is_post_type_archive( 'jogo' ) || is_tax( array( 'plataforma', 'franquia' ) ) || is_front_page() ) {
		global $wp_query;
		$posts = is_front_page()
			? get_posts(
				array(
					'post_type'      => 'jogo',
					'posts_per_page' => 12,
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
				)
			)
			: $wp_query->posts;
		$items = array();
		foreach ( $posts as $i => $p ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'url'      => get_permalink( $p ),
				'name'     => get_the_title( $p ),
			);
		}
		if ( $items ) {
			$graph[] = array(
				'@type'           => 'ItemList',
				'@id'             => gc_canonical() . '#games',
				'itemListElement' => $items,
			);
		}
	}

	echo '<script type="application/ld+json">' . wp_json_encode(
		array(
			'@context' => 'https://schema.org',
			'@graph'   => $graph,
		),
		JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
	) . '</script>' . "\n";
}
