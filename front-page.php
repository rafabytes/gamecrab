<?php
/**
 * Home.
 *
 * @package gamecrab
 */

get_header();

$gc_stats = gc_site_stats();
$gc_games = get_posts(
	array(
		'post_type'      => 'jogo',
		'posts_per_page' => 12,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'title'      => 'ASC',
		),
	)
);

// Ticker: alguns códigos reais de cada jogo.
$gc_ticker = array();
foreach ( $gc_games as $gc_g ) {
	$gc_d = gc_get_data( $gc_g );
	$gc_n = 0;
	foreach ( $gc_d['sections'] as $gc_s ) {
		foreach ( $gc_s['cheats'] as $gc_c ) {
			if ( 'expirado' === $gc_c['status'] || ! $gc_c['rendered'] || $gc_n >= 2 ) {
				continue;
			}
			$gc_first    = reset( $gc_c['rendered'] );
			$gc_ticker[] = array( $gc_first['copy'], get_the_title( $gc_g ) );
			++$gc_n;
		}
	}
}
?>

<section class="gc-hero" aria-labelledby="hero-title">
	<div class="gc-hero__bg" aria-hidden="true"></div>
	<div class="gc-wrap gc-hero__grid">
		<div class="gc-hero__copy">
			<p class="gc-eyebrow gc-reveal">
				<span class="gc-led" aria-hidden="true"></span>
				<?php
				printf(
					/* translators: %s: data */
					esc_html__( 'Guia de cheats públicos · atualizado em %s', 'gamecrab' ),
					esc_html( $gc_stats['updated'] ? gc_date_br( $gc_stats['updated'], 'd/m/Y' ) : wp_date( 'd/m/Y' ) )
				);
				?>
			</p>
			<h1 id="hero-title" class="gc-hero__title">
				<span class="gc-line"><span>Tem código</span></span>
				<span class="gc-line"><span>pra isso.<span class="gc-hero__cursor" aria-hidden="true"></span></span></span>
			</h1>
			<p class="gc-hero__lead gc-reveal">
				<?php esc_html_e( 'GTA, The Sims, Roblox e muito mais: o código certo para o seu jogo, na sua plataforma. Com passo a passo, selo de verificação e botão de copiar.', 'gamecrab' ); ?>
			</p>

			<form class="gc-hero__search gc-reveal" role="search" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" data-search-form>
				<label for="hero-s" class="sr-only"><?php esc_html_e( 'Buscar jogo ou cheat', 'gamecrab' ); ?></label>
				<?php echo gc_icon( 'search' ); // phpcs:ignore ?>
				<input id="hero-s" type="search" name="s" placeholder="<?php esc_attr_e( 'motherlode, dinheiro infinito, tanque…', 'gamecrab' ); ?>" autocomplete="off">
				<button type="submit" class="gc-btn gc-btn--primary"><?php esc_html_e( 'Buscar', 'gamecrab' ); ?></button>
			</form>

			<dl class="gc-hero__stats gc-reveal">
				<div><dt><?php esc_html_e( 'Jogos', 'gamecrab' ); ?></dt><dd data-count="<?php echo esc_attr( $gc_stats['games'] ); ?>"><?php echo esc_html( number_format_i18n( $gc_stats['games'] ) ); ?></dd></div>
				<div><dt><?php esc_html_e( 'Códigos ativos', 'gamecrab' ); ?></dt><dd data-count="<?php echo esc_attr( $gc_stats['cheats'] ); ?>"><?php echo esc_html( number_format_i18n( $gc_stats['cheats'] ) ); ?></dd></div>
				<div><dt><?php esc_html_e( 'Confirmados', 'gamecrab' ); ?></dt><dd data-count="<?php echo esc_attr( $gc_stats['verified'] ); ?>"><?php echo esc_html( number_format_i18n( $gc_stats['verified'] ) ); ?></dd></div>
			</dl>
		</div>

		<div class="gc-hero__visual" aria-hidden="true">
			<div class="gc-cd" data-cd>
				<div class="gc-cd__disc">
					<div class="gc-cd__label">
						<svg class="gc-cd__ring" viewBox="0 0 400 400">
							<defs><path id="gc-cd-path" d="M200,200 m-160,0 a160,160 0 1,1 320,0 a160,160 0 1,1 -320,0"/></defs>
							<text><textPath href="#gc-cd-path" textLength="996" lengthAdjust="spacing">GAMECRAB ✦ GUIA DE CHEATS PÚBLICOS ✦ UM JOGO, TODAS AS PLATAFORMAS ✦ PS · XBOX · PC ✦</textPath></text>
						</svg>
						<div class="gc-cd__brand">
							<?php echo gc_crab_svg( 'gc-cd__crab' ); // phpcs:ignore ?>
							<span class="gc-cd__word"><span>game</span><b>CRAB</b></span>
						</div>
					</div>
					<div class="gc-cd__hole"></div>
				</div>
				<div class="gc-cd__glare"></div>
			</div>
			<div class="gc-hero__sticker"><span>100%</span><span>públicos</span></div>
		</div>
	</div>

	<?php if ( $gc_ticker ) : ?>
		<div class="gc-ticker" aria-hidden="true">
			<div class="gc-ticker__track">
				<?php for ( $gc_i = 0; $gc_i < 2; $gc_i++ ) : ?>
					<ul>
						<?php foreach ( $gc_ticker as $gc_t ) : ?>
							<li><code><?php echo esc_html( $gc_t[0] ); ?></code><span><?php echo esc_html( $gc_t[1] ); ?></span></li>
						<?php endforeach; ?>
					</ul>
				<?php endfor; ?>
			</div>
		</div>
	<?php endif; ?>
</section>

<section class="gc-section" aria-labelledby="jogos-title">
	<div class="gc-wrap">
		<div class="gc-sechead-row">
			<?php gc_section_heading( __( '01 — Biblioteca', 'gamecrab' ), __( 'Escolha o jogo.<br><em>Ative os códigos.</em>', 'gamecrab' ), 'jogos-title' ); ?>
			<a class="gc-link-arrow" href="<?php echo esc_url( get_post_type_archive_link( 'jogo' ) ); ?>"><?php esc_html_e( 'Ver todos os cheats', 'gamecrab' ); ?> <?php echo gc_icon( 'arrow' ); // phpcs:ignore ?></a>
		</div>
		<div class="gc-grid-cases">
			<?php
			foreach ( $gc_games as $gc_g ) {
				gc_game_case( $gc_g );
			}
			?>
		</div>
	</div>
</section>

<?php
$gc_platforms = get_terms(
	array(
		'taxonomy'   => 'plataforma',
		'hide_empty' => true,
		'orderby'    => 'count',
		'order'      => 'DESC',
	)
);
if ( $gc_platforms && ! is_wp_error( $gc_platforms ) ) :
	?>
<section class="gc-section gc-section--tight" aria-labelledby="plataformas-title">
	<div class="gc-wrap">
		<?php gc_section_heading( __( '02 — Plataformas', 'gamecrab' ), __( 'Do PS2 ao PS5, do PC ao Roblox.', 'gamecrab' ), 'plataformas-title' ); ?>
		<ul class="gc-platforms">
			<?php foreach ( $gc_platforms as $gc_p ) : ?>
				<li>
					<a href="<?php echo esc_url( get_term_link( $gc_p ) ); ?>">
						<span class="gc-platforms__name"><?php echo esc_html( $gc_p->name ); ?></span>
						<span class="gc-platforms__count"><?php echo esc_html( sprintf( _n( '%s jogo', '%s jogos', $gc_p->count, 'gamecrab' ), number_format_i18n( $gc_p->count ) ) ); ?></span>
						<?php echo gc_icon( 'arrow' ); // phpcs:ignore ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
<?php endif; ?>

<section class="gc-section" aria-labelledby="selo-title">
	<div class="gc-wrap gc-seal">
		<div class="gc-seal__copy">
			<?php gc_section_heading( __( '03 — Selo gameCrab', 'gamecrab' ), __( 'Cheat que não funciona <em>não entra</em> sem aviso.', 'gamecrab' ), 'selo-title' ); ?>
			<p class="gc-lead"><?php esc_html_e( 'Cada código carrega um status. Você sabe exatamente o que foi testado pela equipe, o que já está confirmado por fontes confiáveis e o que ainda está em observação.', 'gamecrab' ); ?></p>
			<a class="gc-link-arrow" href="<?php echo esc_url( gc_page_url( 'como-verificamos' ) ); ?>"><?php esc_html_e( 'Como verificamos', 'gamecrab' ); ?> <?php echo gc_icon( 'arrow' ); // phpcs:ignore ?></a>
		</div>
		<ul class="gc-seal__list">
			<?php foreach ( gc_statuses() as $gc_key => $gc_st ) : ?>
				<li>
					<?php echo gc_status_badge( $gc_key ); // phpcs:ignore ?>
					<p><?php echo esc_html( $gc_st['desc'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<section class="gc-manifesto" aria-labelledby="proposito-title">
	<div class="gc-manifesto__tracks" aria-hidden="true"></div>
	<div class="gc-wrap gc-manifesto__inner">
		<p class="gc-eyebrow"><?php esc_html_e( '04 — Nosso propósito', 'gamecrab' ); ?></p>
		<h2 id="proposito-title" class="gc-manifesto__title"><?php echo wp_kses( __( 'O código certo.<br>Na <em>sua</em> plataforma.', 'gamecrab' ), array( 'br' => array(), 'em' => array() ) ); ?></h2>
		<div class="gc-manifesto__cols">
			<p><?php esc_html_e( 'Quem procura um cheat quer uma coisa só: o código que funciona no jogo que está jogando agora. Só que o mesmo jogo pode estar no PlayStation, no Xbox, no PC ou no Roblox, e cada plataforma tem a sua sequência e o seu jeito de ativar.', 'gamecrab' ); ?></p>
			<p><?php esc_html_e( 'O gameCrab organiza tudo por jogo e por plataforma. Você escolhe onde joga, vê só os códigos que servem, copia com um toque e sabe na hora o que já foi confirmado. A identidade vem dos discos de cheat dos anos 90; a experiência é de agora.', 'gamecrab' ); ?></p>
		</div>
		<p class="gc-manifesto__note">
			<?php echo gc_icon( 'shield' ); // phpcs:ignore ?>
			<span><?php esc_html_e( 'O gameCrab não é um programa, acessório ou dispositivo: não instala nada nem altera seu jogo ou console. Só publicamos códigos e manhas que os próprios jogos aceitam, divulgados pelas desenvolvedoras ou pela comunidade.', 'gamecrab' ); ?></span>
		</p>
		<?php echo gc_crab_svg( 'gc-manifesto__crab' ); // phpcs:ignore ?>
	</div>
</section>

<?php
get_footer();
