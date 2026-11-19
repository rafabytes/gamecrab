<?php
/**
 * Card de post/página (blog e busca).
 *
 * @package gamecrab
 */

$gc_type = get_post_type();
?>
<article <?php post_class( 'gc-post-card' ); ?>>
	<a class="gc-post-card__link" href="<?php the_permalink(); ?>">
		<p class="gc-eyebrow">
			<?php
			if ( 'jogo' === $gc_type ) {
				esc_html_e( 'Cheats', 'gamecrab' );
			} elseif ( 'page' === $gc_type ) {
				esc_html_e( 'Página', 'gamecrab' );
			} else {
				echo esc_html( get_the_date() );
			}
			?>
		</p>
		<h2 class="gc-post-card__title"><?php the_title(); ?></h2>
		<p class="gc-post-card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<span class="gc-link-arrow" aria-hidden="true"><?php esc_html_e( 'Abrir', 'gamecrab' ); ?> <?php echo gc_icon( 'arrow' ); // phpcs:ignore ?></span>
	</a>
</article>
