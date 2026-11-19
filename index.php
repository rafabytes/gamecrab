<?php
/**
 * Fallback genérico (blog, arquivos de posts).
 *
 * @package gamecrab
 */

get_header();
?>

<header class="gc-page-hero">
	<div class="gc-wrap">
		<?php gc_breadcrumbs(); ?>
		<h1 class="gc-page-hero__title">
			<?php
			if ( is_home() && ! is_front_page() ) {
				single_post_title();
			} elseif ( is_archive() ) {
				the_archive_title();
			} else {
				esc_html_e( 'Novidades', 'gamecrab' );
			}
			?>
		</h1>
	</div>
</header>

<section class="gc-section gc-section--flush">
	<div class="gc-wrap">
		<?php if ( have_posts() ) : ?>
			<div class="gc-posts">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/post-card' );
				endwhile;
				?>
			</div>
			<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
		<?php else : ?>
			<p class="gc-empty"><?php esc_html_e( 'Nada publicado por aqui ainda.', 'gamecrab' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
