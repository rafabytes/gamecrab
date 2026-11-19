<?php
/**
 * Resultados de busca (fallback sem JS da paleta React).
 * Busca também dentro dos códigos (meta _gc_data), não só no título/conteúdo.
 *
 * @package gamecrab
 */

get_header();

$gc_q       = get_search_query();
$gc_matches = array();
if ( '' !== $gc_q ) {
	$gc_needle = remove_accents( mb_strtolower( $gc_q ) );
	foreach ( gc_build_search_index() as $gc_game ) {
		foreach ( $gc_game['cheats'] as $gc_c ) {
			$gc_hay = remove_accents( mb_strtolower( $gc_c['n'] . ' ' . $gc_c['e'] . ' ' . $gc_c['c'] ) );
			if ( false !== strpos( $gc_hay, $gc_needle ) ) {
				$gc_matches[] = $gc_c + array( 'g' => $gc_game['title'] );
			}
		}
	}
}
?>

<header class="gc-page-hero">
	<div class="gc-wrap">
		<?php gc_breadcrumbs(); ?>
		<h1 class="gc-page-hero__title">
			<?php
			/* translators: %s: termo buscado */
			printf( esc_html__( 'Resultados para “%s”', 'gamecrab' ), esc_html( $gc_q ) );
			?>
		</h1>
		<?php get_search_form(); ?>
	</div>
</header>

<section class="gc-section gc-section--flush">
	<div class="gc-wrap">
		<?php if ( $gc_matches ) : ?>
			<h2 class="gc-h3"><?php echo esc_html( sprintf( _n( '%s código encontrado', '%s códigos encontrados', count( $gc_matches ), 'gamecrab' ), number_format_i18n( count( $gc_matches ) ) ) ); ?></h2>
			<ul class="gc-result-list">
				<?php foreach ( array_slice( $gc_matches, 0, 60 ) as $gc_m ) : ?>
					<li>
						<a href="<?php echo esc_url( $gc_m['u'] ); ?>">
							<span class="gc-result-list__game"><?php echo esc_html( $gc_m['g'] ); ?></span>
							<strong><?php echo esc_html( $gc_m['n'] ); ?></strong>
							<code class="gc-code"><?php echo esc_html( $gc_m['c'] ); ?></code>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<h2 class="gc-h3"><?php esc_html_e( 'Páginas', 'gamecrab' ); ?></h2>
			<div class="gc-posts">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/post-card' );
				endwhile;
				?>
			</div>
			<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
		<?php elseif ( ! $gc_matches ) : ?>
			<p class="gc-empty"><?php esc_html_e( 'Nenhum resultado. Tente o nome do jogo ou do efeito (ex.: “dinheiro”, “tanque”, “motherlode”).', 'gamecrab' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
