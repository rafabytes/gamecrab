<?php
/**
 * Post do blog.
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
				<p class="gc-eyebrow"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p>
				<h1 class="gc-page-hero__title"><?php the_title(); ?></h1>
			</div>
		</header>
		<div class="gc-wrap">
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="gc-page__media"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?></figure>
			<?php endif; ?>
			<div class="gc-prose gc-page__content">
				<?php the_content(); ?>
			</div>
		</div>
	</article>
	<?php
endwhile;

get_footer();
