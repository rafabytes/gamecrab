<?php
/**
 * Formulário de busca.
 *
 * @package gamecrab
 */

$gc_sid = wp_unique_id( 'gc-s-' );
?>
<form class="gc-searchform" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" data-search-form>
	<label for="<?php echo esc_attr( $gc_sid ); ?>" class="sr-only"><?php esc_html_e( 'Buscar jogo ou cheat', 'gamecrab' ); ?></label>
	<?php echo gc_icon( 'search' ); // phpcs:ignore ?>
	<input id="<?php echo esc_attr( $gc_sid ); ?>" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Buscar jogo ou cheat…', 'gamecrab' ); ?>" autocomplete="off">
	<button type="submit" class="gc-btn gc-btn--primary"><?php esc_html_e( 'Buscar', 'gamecrab' ); ?></button>
</form>
