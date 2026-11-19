<?php
/**
 * Páginas institucionais.
 *
 * @package gamecrab
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'gc-page' ); ?>>
		<header class="gc-page-hero">
			<div class="gc-wrap">
				<?php gc_breadcrumbs(); ?>
				<h1 class="gc-page-hero__title"><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="gc-page-hero__lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
			</div>
		</header>
		<div class="gc-wrap">
			<div class="gc-prose gc-page__content">
				<?php the_content(); ?>
			</div>
		</div>
	</article>
	<?php
endwhile;

get_footer();
