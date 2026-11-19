<?php
/**
 * Cabeçalho.
 *
 * @package gamecrab
 */

?><!doctype html>
<html <?php language_attributes(); ?> data-theme="night">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="gc-skip" href="#conteudo"><?php esc_html_e( 'Pular para o conteúdo', 'gamecrab' ); ?></a>

<header class="gc-header" data-header>
	<div class="gc-header__inner gc-wrap">
		<?php gc_logo(); ?>

		<nav id="site-nav" class="gc-nav" aria-label="<?php esc_attr_e( 'Principal', 'gamecrab' ); ?>" data-nav>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'gc-nav__list',
					'fallback_cb'    => 'gc_menu_fallback',
					'depth'          => 1,
				)
			);
			?>
		</nav>

		<div class="gc-header__actions">
			<a class="gc-btn-icon gc-search-trigger" href="<?php echo esc_url( home_url( '/?s=' ) ); ?>" data-open-search aria-keyshortcuts="Control+K Meta+K /">
				<?php echo gc_icon( 'search' ); // phpcs:ignore ?>
				<span class="gc-search-trigger__label"><?php esc_html_e( 'Buscar cheat', 'gamecrab' ); ?></span>
				<kbd class="gc-search-trigger__kbd" aria-hidden="true">/</kbd>
				<span class="sr-only"><?php esc_html_e( '(atalho: barra ou Ctrl+K)', 'gamecrab' ); ?></span>
			</a>

			<button class="gc-btn-icon gc-theme-toggle" type="button" data-theme-toggle aria-pressed="false">
				<span class="gc-theme-toggle__track" aria-hidden="true">
					<span class="gc-theme-toggle__thumb"><?php echo gc_icon( 'moon', 'gc-icon gc-icon--moon' ) . gc_icon( 'sun', 'gc-icon gc-icon--sun' ); // phpcs:ignore ?></span>
				</span>
				<span class="sr-only" data-theme-label><?php esc_html_e( 'Ativar modo dia', 'gamecrab' ); ?></span>
			</button>

			<button class="gc-btn-icon gc-menu-toggle" type="button" aria-expanded="false" aria-controls="site-nav" data-menu-toggle>
				<?php echo gc_icon( 'menu', 'gc-icon gc-icon--open' ) . gc_icon( 'close', 'gc-icon gc-icon--close' ); // phpcs:ignore ?>
				<span class="sr-only"><?php esc_html_e( 'Menu', 'gamecrab' ); ?></span>
			</button>
		</div>
	</div>
</header>

<div id="swup" class="gc-main transition-fade">
<main id="conteudo" tabindex="-1">
