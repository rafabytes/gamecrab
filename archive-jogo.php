<?php
/**
 * Arquivo de jogos (/cheats/) e taxonomias (plataforma, franquia).
 *
 * @package gamecrab
 */

get_header();

$gc_is_tax = is_tax( array( 'plataforma', 'franquia' ) );
$gc_term   = $gc_is_tax ? get_queried_object() : null;
$gc_title  = $gc_is_tax ? sprintf( __( 'Cheats de %s', 'gamecrab' ), $gc_term->name ) : __( 'Todos os cheats', 'gamecrab' );
if ( is_tax( 'plataforma' ) ) {
	$gc_title = sprintf( __( 'Cheats para %s', 'gamecrab' ), $gc_term->name );
}
$gc_desc = $gc_is_tax && $gc_term->description ? $gc_term->description : __( 'Escolha um jogo para ver todos os códigos, com status de verificação e o passo a passo para ativar em cada plataforma.', 'gamecrab' );
?>

<header class="gc-page-hero">
	<div class="gc-wrap">
		<?php gc_breadcrumbs(); ?>
		<p class="gc-eyebrow"><?php echo esc_html( sprintf( _n( '%s jogo', '%s jogos', (int) $wp_query->found_posts, 'gamecrab' ), number_format_i18n( (int) $wp_query->found_posts ) ) ); ?></p>
		<h1 class="gc-page-hero__title"><?php echo esc_html( $gc_title ); ?></h1>
		<p class="gc-page-hero__lead"><?php echo esc_html( $gc_desc ); ?></p>

		<?php
		$gc_filters = get_terms(
			array(
				'taxonomy'   => 'plataforma',
				'hide_empty' => true,
			)
		);
		if ( $gc_filters && ! is_wp_error( $gc_filters ) ) :
			?>
			<nav class="gc-filter" aria-label="<?php esc_attr_e( 'Filtrar por plataforma', 'gamecrab' ); ?>">
				<ul class="gc-chips">
					<li><a class="gc-chip" href="<?php echo esc_url( get_post_type_archive_link( 'jogo' ) ); ?>" <?php echo is_post_type_archive( 'jogo' ) ? 'aria-current="page"' : ''; ?>><?php esc_html_e( 'Todas', 'gamecrab' ); ?></a></li>
					<?php foreach ( $gc_filters as $gc_f ) : ?>
						<li><a class="gc-chip" href="<?php echo esc_url( get_term_link( $gc_f ) ); ?>" <?php echo ( $gc_term && $gc_term->term_id === $gc_f->term_id ) ? 'aria-current="page"' : ''; ?>><?php echo esc_html( $gc_f->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endif; ?>
	</div>
</header>

<section class="gc-section gc-section--flush" aria-label="<?php esc_attr_e( 'Jogos', 'gamecrab' ); ?>">
	<div class="gc-wrap">
		<?php if ( have_posts() ) : ?>
			<div class="gc-grid-cases">
				<?php
				while ( have_posts() ) :
					the_post();
					$gc_i = (int) $wp_query->current_post;
					gc_game_case( null, 'h2', 0 === $gc_i ? 'high' : ( $gc_i < 4 ? 'eager' : 'lazy' ) );
				endwhile;
				?>
			</div>
			<?php
			the_posts_pagination(
				array(
					'mid_size'  => 1,
					'prev_text' => __( 'Anterior', 'gamecrab' ),
					'next_text' => __( 'Próxima', 'gamecrab' ),
				)
			);
			?>
		<?php else : ?>
			<p class="gc-empty"><?php esc_html_e( 'Nenhum jogo por aqui ainda. O caranguejo está cavando.', 'gamecrab' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
