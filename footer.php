<?php
/**
 * Rodapé.
 *
 * @package gamecrab
 */

?>
</main>
</div><!-- #swup -->

<footer class="gc-footer">
	<div class="gc-wrap">
		<div class="gc-footer__big" aria-hidden="true">
			<span data-t="APERTE"></span><span data-t="START."></span>
		</div>

		<div class="gc-footer__grid">
			<div class="gc-footer__brand">
				<?php gc_logo(); ?>
				<p><?php esc_html_e( 'Guia de cheats, códigos e manhas públicos, organizados por jogo e plataforma: verificados, explicados e prontos para copiar. Um projeto appMaster.', 'gamecrab' ); ?></p>
			</div>

			<nav aria-label="<?php esc_attr_e( 'Jogos', 'gamecrab' ); ?>">
				<h2 class="gc-footer__title"><?php esc_html_e( 'Jogos', 'gamecrab' ); ?></h2>
				<ul>
					<?php
					$gc_footer_games = get_posts(
						array(
							'post_type'      => 'jogo',
							'posts_per_page' => 8,
							'orderby'        => 'menu_order',
							'order'          => 'ASC',
						)
					);
					foreach ( $gc_footer_games as $gc_g ) {
						echo '<li><a href="' . esc_url( get_permalink( $gc_g ) ) . '">' . esc_html( get_the_title( $gc_g ) ) . '</a></li>';
					}
					?>
				</ul>
			</nav>

			<nav aria-label="<?php esc_attr_e( 'Institucional', 'gamecrab' ); ?>">
				<h2 class="gc-footer__title"><?php esc_html_e( 'gameCrab', 'gamecrab' ); ?></h2>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'depth'          => 1,
						'fallback_cb'    => false,
					)
				);
				?>
			</nav>
		</div>

		<div class="gc-footer__legal">
			<p>© <?php echo esc_html( wp_date( 'Y' ) ); ?> gameCrab · appMaster. <?php esc_html_e( 'O gameCrab é um site de divulgação de cheats públicos: não vendemos nem distribuímos programas, acessórios ou modificações. Não somos afiliados a nenhuma publicadora; nomes de jogos e marcas pertencem aos seus respectivos donos e são citados apenas para identificação.', 'gamecrab' ); ?></p>
		</div>
	</div>
</footer>

<div id="gc-palette-root"></div>
<div class="sr-only" role="status" aria-live="polite" data-announcer></div>

<?php wp_footer(); ?>
</body>
</html>
