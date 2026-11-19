<?php
/**
 * Página de cheats de um jogo.
 *
 * Conteúdo 100% renderizado no servidor (SEO / sem JS). O React (CheatExplorer)
 * assume o bloco #codigos depois do carregamento, adicionando busca, filtro por
 * plataforma, botão de copiar e o "replay" das sequências de botões.
 *
 * @package gamecrab
 */

get_header();

while ( have_posts() ) :
	the_post();
	$gc         = gc_get_data();
	$gc_plats   = gc_platform_names();
	$gc_active  = $gc['total'] - $gc['counts']['expirado'];
	$gc_title   = get_the_title();
	$gc_toc     = array();
	if ( $gc['how_to'] ) {
		$gc_toc[] = array( 'como-ativar', __( 'Como ativar', 'gamecrab' ) );
	}
	foreach ( $gc['sections'] as $gc_s ) {
		$gc_toc[] = array( $gc_s['id'], $gc_s['title'] );
	}
	if ( $gc['tips'] ) {
		$gc_toc[] = array( 'dicas', __( 'Dicas', 'gamecrab' ) );
	}
	if ( $gc['faq'] ) {
		$gc_toc[] = array( 'perguntas-frequentes', __( 'Perguntas frequentes', 'gamecrab' ) );
	}
	if ( $gc['sources'] ) {
		$gc_toc[] = array( 'fontes', __( 'Fontes', 'gamecrab' ) );
	}

	// Dados para o React (sem campos supérfluos).
	$gc_payload = array(
		'title'    => $gc_title,
		'variants' => $gc['variants'],
		'statuses' => wp_list_pluck( gc_statuses(), 'label' ),
		'sections' => array_map(
			fn( $s ) => array(
				'id'     => $s['id'],
				'title'  => $s['title'],
				'intro'  => $s['intro'],
				'cheats' => array_map(
					fn( $c ) => array(
						'id'       => $c['id'],
						'name'     => $c['name'],
						'effect'   => $c['effect'],
						'status'   => $c['status'],
						'unlock'   => $c['unlock'],
						'note'     => $c['note'],
						'rendered' => $c['rendered'],
					),
					$s['cheats']
				),
			),
			$gc['sections']
		),
	);
	?>

<article class="gc-game" style="--game-accent: <?php echo esc_attr( $gc['accent'] ); ?>">
	<header class="gc-game__hero">
		<div class="gc-game__glow" aria-hidden="true"></div>
		<div class="gc-wrap">
			<?php gc_breadcrumbs(); ?>
			<div class="gc-game__head">
				<div class="gc-game__case">
					<div class="gc-case gc-case--hero" style="--case-accent: <?php echo esc_attr( $gc['accent'] ); ?>">
						<?php gc_case_box( get_post(), 'span', true ); ?>
					</div>
				</div>

				<div class="gc-game__intro">
					<?php if ( $gc['kicker'] ) : ?>
						<p class="gc-eyebrow"><?php echo esc_html( $gc['kicker'] ); ?></p>
					<?php endif; ?>
					<h1 class="gc-game__title"><?php echo esc_html( $gc_title ); ?></h1>
					<?php if ( has_excerpt() ) : ?>
						<p class="gc-game__lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>

					<ul class="gc-game__meta">
						<li><?php echo gc_icon( 'clock' ); // phpcs:ignore ?> <?php esc_html_e( 'Atualizado em', 'gamecrab' ); ?> <time datetime="<?php echo esc_attr( $gc['updated'] ); ?>"><?php echo esc_html( gc_date_br( $gc['updated'] ) ); ?></time></li>
						<li><?php echo gc_icon( 'pad' ); // phpcs:ignore ?> <?php echo esc_html( sprintf( _n( '%s código ativo', '%s códigos ativos', $gc_active, 'gamecrab' ), number_format_i18n( $gc_active ) ) ); ?></li>
						<?php if ( $gc['counts']['testado'] + $gc['counts']['confirmado'] ) : ?>
							<li><?php echo gc_icon( 'shield' ); // phpcs:ignore ?> <?php echo esc_html( sprintf( __( '%s confirmados', 'gamecrab' ), number_format_i18n( $gc['counts']['testado'] + $gc['counts']['confirmado'] ) ) ); ?></li>
						<?php endif; ?>
					</ul>

					<?php if ( $gc_plats ) : ?>
						<ul class="gc-chips" aria-label="<?php esc_attr_e( 'Plataformas', 'gamecrab' ); ?>">
							<?php
							foreach ( get_the_terms( get_the_ID(), 'plataforma' ) as $gc_term ) {
								echo '<li><a class="gc-chip" href="' . esc_url( get_term_link( $gc_term ) ) . '">' . esc_html( $gc_term->name ) . '</a></li>';
							}
							?>
						</ul>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</header>

	<div class="gc-wrap gc-game__layout">
		<?php if ( count( $gc_toc ) > 2 ) : ?>
			<aside class="gc-toc" aria-labelledby="toc-title">
				<div class="gc-toc__inner">
					<h2 id="toc-title" class="gc-toc__title"><?php esc_html_e( 'Nesta página', 'gamecrab' ); ?></h2>
					<ol>
						<?php foreach ( $gc_toc as $gc_t ) : ?>
							<li><a href="#<?php echo esc_attr( $gc_t[0] ); ?>" data-toc-link><?php echo esc_html( $gc_t[1] ); ?></a></li>
						<?php endforeach; ?>
					</ol>
				</div>
			</aside>
		<?php endif; ?>

		<div class="gc-game__body">
			<?php if ( ! empty( $gc['notice']['text'] ) ) : ?>
				<div class="gc-notice gc-notice--<?php echo esc_attr( $gc['notice']['tone'] ); ?>" role="note">
					<?php echo gc_icon( 'warn' === $gc['notice']['tone'] ? 'alert' : 'info' ); // phpcs:ignore ?>
					<div>
						<?php if ( $gc['notice']['title'] ) : ?>
							<p class="gc-notice__title"><?php echo esc_html( $gc['notice']['title'] ); ?></p>
						<?php endif; ?>
						<p><?php echo wp_kses_post( $gc['notice']['text'] ); ?></p>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( get_the_content() ) : ?>
				<div class="gc-prose gc-game__content">
					<?php the_content(); ?>
				</div>
			<?php endif; ?>

			<?php if ( $gc['how_to'] ) : ?>
				<section class="gc-block" aria-labelledby="como-ativar">
					<h2 id="como-ativar" class="gc-h3"><?php echo esc_html( sprintf( __( 'Como ativar os cheats de %s', 'gamecrab' ), preg_replace( '/^(Cheats|Códigos)\s+(de|do|da)\s+/u', '', $gc_title ) ) ); ?></h2>
					<div class="gc-howto">
						<?php foreach ( $gc['how_to'] as $gc_h ) : ?>
							<div class="gc-howto__card">
								<?php if ( $gc_h['title'] ) : ?>
									<h3 class="gc-howto__title"><?php echo esc_html( $gc_h['title'] ); ?></h3>
								<?php endif; ?>
								<ol class="gc-steps">
									<?php foreach ( $gc_h['steps'] as $gc_step ) : ?>
										<li><span><?php echo wp_kses_post( $gc_step ); ?></span></li>
									<?php endforeach; ?>
								</ol>
							</div>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<section id="codigos" class="gc-block" aria-label="<?php esc_attr_e( 'Lista de códigos', 'gamecrab' ); ?>">
				<div data-cheat-explorer>
					<?php foreach ( $gc['sections'] as $gc_s ) : ?>
						<section class="gc-cheat-section" aria-labelledby="<?php echo esc_attr( $gc_s['id'] ); ?>">
							<h2 id="<?php echo esc_attr( $gc_s['id'] ); ?>" class="gc-h3"><?php echo esc_html( $gc_s['title'] ); ?> <span class="gc-count"><?php echo esc_html( count( $gc_s['cheats'] ) ); ?></span></h2>
							<?php if ( $gc_s['intro'] ) : ?>
								<p class="gc-cheat-section__intro"><?php echo wp_kses_post( $gc_s['intro'] ); ?></p>
							<?php endif; ?>
							<ul class="gc-cheats">
								<?php foreach ( $gc_s['cheats'] as $gc_c ) : ?>
									<li class="gc-cheat gc-cheat--<?php echo esc_attr( $gc_c['status'] ); ?>" id="<?php echo esc_attr( $gc_c['id'] ); ?>">
										<div class="gc-cheat__head">
											<h3 class="gc-cheat__name"><?php echo esc_html( $gc_c['name'] ); ?></h3>
											<?php echo gc_status_badge( $gc_c['status'] ); // phpcs:ignore ?>
										</div>
										<?php if ( $gc_c['effect'] ) : ?>
											<p class="gc-cheat__effect"><?php echo wp_kses_post( $gc_c['effect'] ); ?></p>
										<?php endif; ?>
										<?php if ( $gc_c['rendered'] ) : ?>
											<dl class="gc-cheat__codes">
												<?php
												foreach ( $gc['variants'] as $gc_v ) :
													if ( empty( $gc_c['rendered'][ $gc_v['key'] ] ) ) {
														continue;
													}
													?>
													<div class="gc-cheat__code">
														<dt><?php echo esc_html( $gc_v['label'] ); ?></dt>
														<dd><?php echo gc_render_code( $gc_c['rendered'][ $gc_v['key'] ] ); // phpcs:ignore ?></dd>
													</div>
												<?php endforeach; ?>
											</dl>
										<?php endif; ?>
										<?php if ( $gc_c['unlock'] ) : ?>
											<p class="gc-cheat__meta"><strong><?php esc_html_e( 'Como desbloquear:', 'gamecrab' ); ?></strong> <?php echo wp_kses_post( $gc_c['unlock'] ); ?></p>
										<?php endif; ?>
										<?php if ( $gc_c['note'] ) : ?>
											<p class="gc-cheat__meta"><strong><?php esc_html_e( 'Obs.:', 'gamecrab' ); ?></strong> <?php echo wp_kses_post( $gc_c['note'] ); ?></p>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
						</section>
					<?php endforeach; ?>
				</div>
				<script type="application/json" data-cheat-data><?php echo wp_json_encode( $gc_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP ); ?></script>
			</section>

			<?php if ( $gc['tips'] ) : ?>
				<section class="gc-block" aria-labelledby="dicas">
					<h2 id="dicas" class="gc-h3"><?php esc_html_e( 'Dicas do caranguejo', 'gamecrab' ); ?></h2>
					<ul class="gc-tips">
						<?php foreach ( $gc['tips'] as $gc_tip ) : ?>
							<li><?php echo wp_kses_post( $gc_tip ); ?></li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<?php if ( $gc['faq'] ) : ?>
				<section class="gc-block" aria-labelledby="perguntas-frequentes">
					<h2 id="perguntas-frequentes" class="gc-h3"><?php esc_html_e( 'Perguntas frequentes', 'gamecrab' ); ?></h2>
					<div class="gc-faq">
						<?php foreach ( $gc['faq'] as $gc_f ) : ?>
							<details class="gc-faq__item">
								<summary><h3><?php echo esc_html( $gc_f['q'] ); ?></h3></summary>
								<div class="gc-faq__a"><p><?php echo wp_kses_post( $gc_f['a'] ); ?></p></div>
							</details>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( $gc['sources'] ) : ?>
				<section class="gc-block" aria-labelledby="fontes">
					<h2 id="fontes" class="gc-h3"><?php esc_html_e( 'Fontes consultadas', 'gamecrab' ); ?></h2>
					<ul class="gc-sources">
						<?php foreach ( $gc['sources'] as $gc_src ) : ?>
							<li><a href="<?php echo esc_url( $gc_src['url'] ); ?>" target="_blank" rel="noopener nofollow" data-no-swup><?php echo esc_html( $gc_src['label'] ); ?> <?php echo gc_icon( 'external' ); // phpcs:ignore ?><span class="sr-only"><?php esc_html_e( '(abre em nova aba)', 'gamecrab' ); ?></span></a></li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>
		</div>
	</div>

	<?php
	$gc_terms   = wp_get_post_terms( get_the_ID(), 'franquia', array( 'fields' => 'ids' ) );
	$gc_related = get_posts(
		array(
			'post_type'      => 'jogo',
			'posts_per_page' => 4,
			'post__not_in'   => array( get_the_ID() ),
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		)
	);
	// Prioriza a mesma franquia.
	usort(
		$gc_related,
		function ( $a, $b ) use ( $gc_terms ) {
			$fa = (int) has_term( $gc_terms, 'franquia', $a );
			$fb = (int) has_term( $gc_terms, 'franquia', $b );
			return $fb <=> $fa;
		}
	);
	$gc_related = array_slice( $gc_related, 0, 3 );
	if ( $gc_related ) :
		?>
		<section class="gc-section gc-related" aria-labelledby="relacionados">
			<div class="gc-wrap">
				<?php gc_section_heading( __( 'Continue jogando', 'gamecrab' ), __( 'Mais discos na prateleira', 'gamecrab' ), 'relacionados' ); ?>
				<div class="gc-grid-cases gc-grid-cases--3">
					<?php
					foreach ( $gc_related as $gc_r ) {
						gc_game_case( $gc_r );
					}
					?>
				</div>
			</div>
		</section>
	<?php endif; ?>
</article>

	<?php
endwhile;

get_footer();
