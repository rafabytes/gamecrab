<?php
/**
 * Gera assets/og-default.png (1200x630) e assets/logo-512.png com GD.
 * Uso: php bin/make-images.php
 *
 * @package gamecrab
 */

$root  = dirname( __DIR__ );
$fonts = 'C:/Windows/Fonts/';
$black = is_file( $fonts . 'seguibl.ttf' ) ? $fonts . 'seguibl.ttf' : $fonts . 'ariblk.ttf';
$light = is_file( $fonts . 'segoeuil.ttf' ) ? $fonts . 'segoeuil.ttf' : $fonts . 'arial.ttf';
$mono  = $fonts . 'consolab.ttf';

/** Desenha o caranguejo (mesma geometria do SVG) em escala. */
function gc_draw_crab( $im, float $ox, float $oy, float $s, int $col, int $detail ): void {
	imagesetthickness( $im, (int) round( 4 * $s ) );
	$arc = function ( $pts ) use ( $im, $col, $ox, $oy, $s ) {
		for ( $i = 0; $i < count( $pts ) - 1; $i++ ) {
			imageline( $im, (int) ( $ox + $pts[ $i ][0] * $s ), (int) ( $oy + $pts[ $i ][1] * $s ), (int) ( $ox + $pts[ $i + 1 ][0] * $s ), (int) ( $oy + $pts[ $i + 1 ][1] * $s ), $col );
		}
	};
	$arc( array( array( 19, 35 ), array( 14, 31 ), array( 11, 23 ) ) );
	$arc( array( array( 45, 35 ), array( 50, 31 ), array( 53, 23 ) ) );
	imagesetthickness( $im, (int) round( 3.5 * $s ) );
	foreach ( array( array( 17, 41, 8, 43 ), array( 18, 45, 10, 50 ), array( 22, 48, 16, 55 ), array( 47, 41, 56, 43 ), array( 46, 45, 54, 50 ), array( 42, 48, 48, 55 ), array( 27, 29, 25, 21 ), array( 37, 29, 39, 21 ) ) as $l ) {
		imageline( $im, (int) ( $ox + $l[0] * $s ), (int) ( $oy + $l[1] * $s ), (int) ( $ox + $l[2] * $s ), (int) ( $oy + $l[3] * $s ), $col );
	}
	imagefilledellipse( $im, (int) ( $ox + 32 * $s ), (int) ( $oy + 39 * $s ), (int) ( 33 * $s ), (int) ( 23 * $s ), $col );
	foreach ( array( array( 25, 19.5 ), array( 39, 19.5 ) ) as $e ) {
		imagefilledellipse( $im, (int) ( $ox + $e[0] * $s ), (int) ( $oy + $e[1] * $s ), (int) ( 7.2 * $s ), (int) ( 7.2 * $s ), $col );
		imagefilledellipse( $im, (int) ( $ox + ( $e[0] + 0.8 ) * $s ), (int) ( $oy + 19 * $s ), (int) ( 2.8 * $s ), (int) ( 2.8 * $s ), $detail );
	}
	// Garras
	foreach ( array( array( 9, 15, -1 ), array( 55, 15, 1 ) ) as $c ) {
		imagefilledellipse( $im, (int) ( $ox + $c[0] * $s ), (int) ( $oy + $c[1] * $s ), (int) ( 12 * $s ), (int) ( 16 * $s ), $col );
		imagefilledpolygon( $im, array( (int) ( $ox + ( $c[0] - 1 ) * $s ), (int) ( $oy + 6 * $s ), (int) ( $ox + ( $c[0] + 2.5 * $c[2] ) * $s ), (int) ( $oy + 14 * $s ), (int) ( $ox + ( $c[0] + 4 ) * $s ), (int) ( $oy + 6 * $s ) ), $detail );
	}
	imagefilledrectangle( $im, (int) ( $ox + 21 * $s ), (int) ( $oy + 36 * $s ), (int) ( $ox + 43 * $s ), (int) ( $oy + 39.2 * $s ), $detail );
}

// ---------- OG 1200x630 ----------
$w  = 1200;
$h  = 630;
$im = imagecreatetruecolor( $w, $h );
imageantialias( $im, true );
$bg = imagecolorallocate( $im, 8, 8, 11 );
imagefilledrectangle( $im, 0, 0, $w, $h, $bg );

// Grade sutil
$grid = imagecolorallocatealpha( $im, 255, 255, 255, 123 );
for ( $x = 0; $x < $w; $x += 64 ) {
	imageline( $im, $x, 0, $x, $h, $grid );
}
for ( $y = 0; $y < $h; $y += 64 ) {
	imageline( $im, 0, $y, $w, $y, $grid );
}

// Disco iridescente (lado direito)
$cx = 930;
$cy = 315;
$r  = 250;
for ( $a = 0; $a < 360; $a += 0.5 ) {
	$t   = deg2rad( $a );
	$hue = ( $a / 360 );
	$rr  = (int) ( 40 + 40 * sin( 2 * M_PI * $hue ) );
	$gg  = (int) ( 40 + 30 * sin( 2 * M_PI * ( $hue + 0.33 ) ) );
	$bb  = (int) ( 55 + 40 * sin( 2 * M_PI * ( $hue + 0.66 ) ) );
	$col = imagecolorallocate( $im, max( 0, $rr ), max( 0, $gg ), max( 0, $bb ) );
	imagefilledarc( $im, $cx, $cy, $r * 2, $r * 2, (int) $a, (int) $a + 1, $col, IMG_ARC_PIE );
}
$label = imagecolorallocate( $im, 11, 11, 15 );
imagefilledellipse( $im, $cx, $cy, (int) ( $r * 1.24 ), (int) ( $r * 1.24 ), $label );
$accent = imagecolorallocate( $im, 255, 77, 46 );
gc_draw_crab( $im, $cx - 96, $cy - 140, 3.0, $accent, $label );

// Textos
$fg    = imagecolorallocate( $im, 243, 241, 236 );
$muted = imagecolorallocate( $im, 167, 165, 176 );
$lime  = imagecolorallocate( $im, 184, 242, 60 );
imagettftext( $im, 20, 0, 72, 120, $accent, $mono, 'GUIA DE CHEATS PÚBLICOS' );
imagettftext( $im, 96, 0, 66, 270, $fg, $light, 'game' );
$box = imagettfbbox( 96, 0, $light, 'game' );
imagettftext( $im, 96, 0, 66 + ( $box[2] - $box[0] ) + 6, 270, $accent, $black, 'CRAB' );
imagettftext( $im, 30, 0, 72, 360, $fg, $black, 'Cheats, códigos e manhas' );
imagettftext( $im, 30, 0, 72, 410, $muted, $black, 'verificados e explicados.' );
imagettftext( $im, 18, 0, 72, 540, $lime, $mono, 'GTA · THE SIMS · ROBLOX' );
imagepng( $im, $root . '/assets/og-default.png', 9 );
imagedestroy( $im );

// ---------- Logo 512 ----------
$im  = imagecreatetruecolor( 512, 512 );
$bg  = imagecolorallocate( $im, 8, 8, 11 );
imagefilledrectangle( $im, 0, 0, 512, 512, $bg );
$acc = imagecolorallocate( $im, 255, 77, 46 );
gc_draw_crab( $im, 0, 10, 8.0, $acc, $bg );
imagepng( $im, $root . '/assets/logo-512.png', 9 );
imagedestroy( $im );

echo "ok\n";
