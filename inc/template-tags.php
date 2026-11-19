<?php
/**
 * Helpers de template (logo, ícones, breadcrumbs, cards, códigos).
 *
 * @package gamecrab
 */

defined( 'ABSPATH' ) || exit;

/**
 * Marca: caranguejo geométrico original (não deriva de nenhuma marca existente).
 */
function gc_crab_svg( string $class = '', bool $decorative = true ): string {
	$a11y = $decorative ? 'aria-hidden="true" focusable="false"' : 'role="img" aria-label="gameCrab"';
	return '<svg class="' . esc_attr( $class ) . '" ' . $a11y . ' viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">'
		. '<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">'
		. '<path stroke-width="4" d="M19 35 Q12 31 11 23"/><path stroke-width="4" d="M45 35 Q52 31 53 23"/>'
		. '<path stroke-width="3.5" d="M17 41 8 43M18 45l-8 5M22 48l-6 7M47 41l9 2M46 45l8 5M42 48l6 7"/>'
		. '<path stroke-width="3" d="M27 29 25 21M37 29l2-8"/></g>'
		. '<g fill="currentColor"><path d="M10 23C3 21 2.5 11 8.5 7l3.5 6 3.5-4.5c3.5 4.5 1.5 13.5-5.5 14.5Z"/>'
		. '<path d="M54 23c7-2 7.5-12 1.5-16l-3.5 6-3.5-4.5c-3.5 4.5-1.5 13.5 5.5 14.5Z"/>'
		. '<ellipse cx="32" cy="39" rx="16.5" ry="11.5"/><circle cx="25" cy="19.5" r="3.6"/><circle cx="39" cy="19.5" r="3.6"/></g>'
		. '<g fill="var(--gc-crab-detail, #0a0a0e)"><circle cx="25.8" cy="19" r="1.4"/><circle cx="39.8" cy="19" r="1.4"/>'
		. '<rect x="21" y="36" width="22" height="3.2" rx="1.6"/><rect x="25" y="41.5" width="14" height="2.6" rx="1.3" opacity=".55"/></g>'
		. '</svg>';
}

/**
 * Logo completo (marca + wordmark).
 */
function gc_logo( string $tag = 'span' ): void {
	$tag = in_array( $tag, array( 'span', 'h1', 'p' ), true ) ? $tag : 'span';
	printf(
		'<a class="gc-logo" href="%1$s" rel="home" aria-label="%2$s">%3$s<%4$s class="gc-logo__word" aria-hidden="true"><span class="gc-logo__game">game</span><span class="gc-logo__crab">CRAB</span></%4$s></a>',
		esc_url( home_url( '/' ) ),
		esc_attr__( 'gameCrab — página inicial', 'gamecrab' ),
		gc_crab_svg( 'gc-logo__mark' ), // phpcs:ignore WordPress.Security.EscapeOutput
		$tag // phpcs:ignore WordPress.Security.EscapeOutput
	);
}

/**
 * Ícones SVG inline (stroke).
 */
function gc_icon( string $name, string $class = 'gc-icon' ): string {
	$paths = array(
		'search'   => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
		'sun'      => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
		'moon'     => '<path d="M20 14.5A8 8 0 0 1 9.5 4 8 8 0 1 0 20 14.5Z"/>',
		'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'copy'     => '<rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/>',
		'check'    => '<path d="m5 12 5 5L20 7"/>',
		'external' => '<path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
		'menu'     => '<path d="M4 7h16M4 12h16M4 17h10"/>',
		'close'    => '<path d="M6 6l12 12M18 6 6 18"/>',
		'shield'   => '<path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6l-8-3Z"/><path d="m9 12 2 2 4-4"/>',
		'alert'    => '<path d="M12 3 2 20h20L12 3Z"/><path d="M12 10v4M12 17h.01"/>',
		'info'     => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
		'pad'      => '<path d="M6 8h12a4 4 0 0 1 4 4v2a3 3 0 0 1-5.4 1.8L15 14H9l-1.6 1.8A3 3 0 0 1 2 14v-2a4 4 0 0 1 4-4Z"/><path d="M7 11v3M5.5 12.5h3M16 11.5h.01M18 13h.01"/>',
		'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
	);
	$inner = $paths[ $name ] ?? '';
	return '<svg class="' . esc_attr( $class ) . '" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $inner . '</svg>';
}

/**
 * Data no formato brasileiro.
 */
function gc_date_br( string $ymd, string $format = 'j \d\e F \d\e Y' ): string {
	$ts = strtotime( $ymd );
	return $ts ? wp_date( $format, $ts ) : $ymd;
}

/**
 * Itens de breadcrumb do contexto atual: [ [label, url], ... ].
 */
function gc_breadcrumb_items(): array {
	$items = array( array( __( 'Início', 'gamecrab' ), home_url( '/' ) ) );

	if ( is_singular( 'jogo' ) ) {
		$items[] = array( __( 'Cheats', 'gamecrab' ), get_post_type_archive_link( 'jogo' ) );
		$fr      = get_the_terms( get_the_ID(), 'franquia' );
		if ( $fr && ! is_wp_error( $fr ) ) {
			$items[] = array( $fr[0]->name, get_term_link( $fr[0] ) );
		}
		$items[] = array( get_the_title(), get_permalink() );
	} elseif ( is_post_type_archive( 'jogo' ) ) {
		$items[] = array( __( 'Cheats', 'gamecrab' ), get_post_type_archive_link( 'jogo' ) );
	} elseif ( is_tax( array( 'plataforma', 'franquia' ) ) ) {
		$items[] = array( __( 'Cheats', 'gamecrab' ), get_post_type_archive_link( 'jogo' ) );
		$items[] = array( single_term_title( '', false ), get_term_link( get_queried_object() ) );
	} elseif ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $anc ) {
			$items[] = array( get_the_title( $anc ), get_permalink( $anc ) );
		}
		$items[] = array( get_the_title(), get_permalink() );
	} elseif ( is_singular( 'post' ) ) {
		$blog = (int) get_option( 'page_for_posts' );
		if ( $blog ) {
			$items[] = array( get_the_title( $blog ), get_permalink( $blog ) );
		}
		$items[] = array( get_the_title(), get_permalink() );
	} elseif ( is_search() ) {
		$items[] = array( sprintf( __( 'Busca: %s', 'gamecrab' ), get_search_query() ), '' );
	}
	return $items;
}

/**
 * Breadcrumbs visíveis (o JSON-LD equivalente sai em seo.php).
 */
function gc_breadcrumbs(): void {
	$items = gc_breadcrumb_items();
	if ( count( $items ) < 2 ) {
		return;
	}
	echo '<nav class="gc-crumbs" aria-label="' . esc_attr__( 'Você está aqui', 'gamecrab' ) . '"><ol>';
	$last = count( $items ) - 1;
	foreach ( $items as $i => $item ) {
		if ( $i === $last ) {
			echo '<li><span aria-current="page">' . esc_html( $item[0] ) . '</span></li>';
		} else {
			echo '<li><a href="' . esc_url( $item[1] ) . '">' . esc_html( $item[0] ) . '</a></li>';
		}
	}
	echo '</ol></nav>';
}

/**
 * Selo de status.
 */
function gc_status_badge( string $status ): string {
	$all = gc_statuses();
	$s   = $all[ $status ] ?? $all['confirmado'];
	return '<span class="gc-badge gc-badge--' . esc_attr( $status ) . '" title="' . esc_attr( $s['desc'] ) . '">' . esc_html( $s['label'] ) . '</span>';
}

/**
 * Renderiza um código (botões como keycaps ou texto monoespaçado).
 */
function gc_render_code( array $entry ): string {
	if ( 'buttons' === $entry['type'] && ! empty( $entry['tokens'] ) ) {
		$labels = implode( ', ', wp_list_pluck( $entry['tokens'], 'l' ) );
		$html   = '<span class="gc-seq" role="img" aria-label="' . esc_attr( $labels ) . '">';
		foreach ( $entry['tokens'] as $t ) {
			$html .= '<kbd class="gc-key gc-key--' . esc_attr( str_replace( ' ', ' gc-key--', $t['k'] ) ) . '" aria-hidden="true">' . esc_html( $t['g'] ) . '</kbd>';
		}
		return $html . '</span>';
	}
	return '<code class="gc-code">' . esc_html( $entry['raw'] ) . '</code>';
}

/**
 * Nomes das plataformas do jogo.
 */
function gc_platform_names( $post = null ): array {
	$terms = get_the_terms( get_post( $post ), 'plataforma' );
	return ( $terms && ! is_wp_error( $terms ) ) ? wp_list_pluck( $terms, 'name' ) : array();
}

/**
 * Nome do jogo sem o prefixo "Cheats de"/"Códigos de".
 */
function gc_game_name( $post = null ): string {
	return (string) preg_replace( '/^(Cheats|Códigos)\s+(de|do|da)\s+/u', '', get_the_title( $post ) );
}

/**
 * Caixa de CD (jewel case): capa do jogo (imagem destacada) + disco com a arte no rótulo.
 *
 * @param string $title_tag Tag do título ('h2', 'h3' ou 'span').
 * @param bool   $hero      Versão grande da página do jogo (imagem eager + alt descritivo).
 */
function gc_case_box( $post, string $title_tag = 'h3', bool $hero = false, string $load = 'lazy' ): void {
	$post      = get_post( $post );
	$data      = gc_get_data( $post );
	$active    = $data['total'] - $data['counts']['expirado'];
	$title_tag = in_array( $title_tag, array( 'h2', 'h3', 'span' ), true ) ? $title_tag : 'h3';
	$has_art   = has_post_thumbnail( $post );
	$art_small = $has_art ? (string) get_the_post_thumbnail_url( $post, 'thumbnail' ) : '';
	$style     = $art_small ? ' style="--case-art: url(\'' . esc_url( $art_small ) . '\')"' : '';
	?>
	<div class="gc-case__box<?php echo $has_art ? ' has-art' : ''; ?>">
		<span class="gc-case__disc" aria-hidden="true"><span class="gc-case__disc-label"<?php echo $style; // phpcs:ignore ?>><?php echo $has_art ? '' : gc_crab_svg( 'gc-case__disc-crab' ); // phpcs:ignore ?></span></span>
		<div class="gc-case__cover">
			<?php
			if ( $has_art ) {
				echo get_the_post_thumbnail( // phpcs:ignore
					$post,
					'gc-cover',
					array(
						'class'         => 'gc-case__art',
						'alt'           => $hero ? sprintf( __( 'Capa do jogo %s', 'gamecrab' ), gc_game_name( $post ) ) : '',
						'loading'       => $hero || 'lazy' !== $load ? 'eager' : 'lazy',
						'fetchpriority' => $hero || 'high' === $load ? 'high' : 'auto',
						'sizes'         => $hero ? '(min-width: 900px) 360px, 78vw' : '(min-width: 1100px) 300px, (min-width: 640px) 45vw, 92vw',
					)
				);
			}
			?>
			<span class="gc-case__spine" aria-hidden="true">gameCRAB · GUIA DE CHEATS</span>
			<?php if ( $has_art ) : ?>
				<span class="gc-case__chip"><strong><?php echo esc_html( number_format_i18n( $active ) ); ?></strong> <?php echo esc_html( _n( 'código', 'códigos', $active, 'gamecrab' ) ); ?></span>
			<?php else : ?>
				<span class="gc-case__kicker"><?php echo esc_html( $data['kicker'] ); ?></span>
				<span class="gc-case__band"<?php echo $hero ? ' aria-hidden="true"' : ''; ?>>
					<<?php echo $title_tag; // phpcs:ignore ?> class="gc-case__title"><?php echo esc_html( $hero ? gc_game_name( $post ) : get_the_title( $post ) ); ?></<?php echo $title_tag; // phpcs:ignore ?>>
					<span class="gc-case__count"><strong><?php echo esc_html( number_format_i18n( $active ) ); ?></strong> <?php echo esc_html( _n( 'código', 'códigos', $active, 'gamecrab' ) ); ?></span>
				</span>
			<?php endif; ?>
		</div>
	</div>
	<?php
	// Com capa, o título fica abaixo da caixa (a arte já traz o logo do jogo).
	if ( $has_art && ! $hero ) {
		printf( '<%1$s class="gc-case__name">%2$s</%1$s>', $title_tag, esc_html( get_the_title( $post ) ) ); // phpcs:ignore
	}
}

/**
 * Card "jewel case" (capa de CD) de um jogo.
 */
/**
 * @param string $load 'lazy' (padrão), 'eager' ou 'high' (primeiro card acima da dobra).
 */
function gc_game_case( $post = null, string $heading = 'h3', string $load = 'lazy' ): void {
	$post = get_post( $post );
	if ( ! $post ) {
		return;
	}
	$data      = gc_get_data( $post );
	$platforms = gc_platform_names( $post );
	?>
	<article class="gc-case" style="--case-accent: <?php echo esc_attr( $data['accent'] ); ?>">
		<a class="gc-case__link" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
			<?php gc_case_box( $post, $heading, false, $load ); ?>
		</a>
		<?php if ( $platforms ) : ?>
			<ul class="gc-case__platforms" aria-label="<?php esc_attr_e( 'Plataformas', 'gamecrab' ); ?>">
				<?php foreach ( $platforms as $p ) : ?>
					<li><?php echo esc_html( $p ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</article>
	<?php
}

/**
 * Título de seção padrão.
 */
function gc_section_heading( string $eyebrow, string $title, string $id = '', string $tag = 'h2' ): void {
	printf(
		'<header class="gc-sechead"><p class="gc-eyebrow">%1$s</p><%3$s class="gc-h2"%4$s>%2$s</%3$s></header>',
		esc_html( $eyebrow ),
		wp_kses( $title, array( 'em' => array(), 'span' => array( 'class' => array() ), 'br' => array() ) ),
		$tag, // phpcs:ignore
		$id ? ' id="' . esc_attr( $id ) . '"' : ''
	);
}

/**
 * Walker-free menu fallback: lista padrão de links.
 */
function gc_menu_fallback(): void {
	$links = array(
		array( __( 'Todos os cheats', 'gamecrab' ), get_post_type_archive_link( 'jogo' ) ),
	);
	foreach ( array( 'gta', 'the-sims', 'roblox' ) as $slug ) {
		$t = get_term_by( 'slug', $slug, 'franquia' );
		if ( $t ) {
			$links[] = array( $t->name, get_term_link( $t ) );
		}
	}
	$about = get_page_by_path( 'sobre' );
	if ( $about ) {
		$links[] = array( get_the_title( $about ), get_permalink( $about ) );
	}
	echo '<ul class="gc-nav__list">';
	foreach ( $links as $l ) {
		echo '<li><a href="' . esc_url( $l[1] ) . '">' . esc_html( $l[0] ) . '</a></li>';
	}
	echo '</ul>';
}
