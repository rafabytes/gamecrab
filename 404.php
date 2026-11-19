<?php
/**
 * 404.
 *
 * @package gamecrab
 */

get_header();
?>
<section class="gc-404">
	<div class="gc-wrap gc-404__inner">
		<p class="gc-404__code" aria-hidden="true">4<span><?php echo gc_crab_svg( 'gc-404__crab' ); // phpcs:ignore ?></span>4</p>
		<h1 class="gc-page-hero__title"><?php esc_html_e( 'Esse código não existe.', 'gamecrab' ); ?></h1>
		<p class="gc-page-hero__lead"><?php esc_html_e( 'O caranguejo andou de lado e a página sumiu. Tente buscar o jogo ou o cheat:', 'gamecrab' ); ?></p>
		<?php get_search_form(); ?>
		<p><a class="gc-link-arrow" href="<?php echo esc_url( get_post_type_archive_link( 'jogo' ) ); ?>"><?php esc_html_e( 'Ver todos os cheats', 'gamecrab' ); ?> <?php echo gc_icon( 'arrow' ); // phpcs:ignore ?></a></p>
	</div>
</section>
<?php
get_footer();
