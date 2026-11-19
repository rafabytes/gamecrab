<?php
/**
 * gameCrab — bootstrap do tema.
 *
 * @package gamecrab
 */

defined( 'ABSPATH' ) || exit;

define( 'GC_VERSION', '1.0.0' );
define( 'GC_DIR', get_template_directory() );
define( 'GC_URI', get_template_directory_uri() );

$gc_includes = array(
	'inc/setup.php',
	'inc/assets.php',
	'inc/post-types.php',
	'inc/data.php',
	'inc/template-tags.php',
	'inc/seo.php',
	'inc/rest.php',
	'inc/metabox.php',
	'inc/seeder.php',
);

foreach ( $gc_includes as $gc_file ) {
	require_once GC_DIR . '/' . $gc_file;
}
