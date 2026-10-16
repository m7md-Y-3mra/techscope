<?php
/**
 * Generates abstract cover art for TechScope demo articles.
 *
 * Runs inside the wordpress container (host PHP has no gd):
 *   docker compose exec wordpress php /var/www/html/wp-content/techscope-tools/make-covers.php
 *
 * Usage: make-covers.php <out-dir> <slug>:<accent-hex>:<motif> ...
 * Motifs: grid, arcs, bars, scatter, waves, blocks
 */

if ( PHP_SAPI !== 'cli' ) {
	exit( 1 );
}

const W = 1600;
const H = 900;

function hex_to_rgb( string $hex ): array {
	$hex = ltrim( $hex, '#' );
	return array(
		hexdec( substr( $hex, 0, 2 ) ),
		hexdec( substr( $hex, 2, 2 ) ),
		hexdec( substr( $hex, 4, 2 ) ),
	);
}

/** Linear-interpolate two rgb triples. */
function mix( array $a, array $b, float $t ): array {
	return array(
		(int) round( $a[0] + ( $b[0] - $a[0] ) * $t ),
		(int) round( $a[1] + ( $b[1] - $a[1] ) * $t ),
		(int) round( $a[2] + ( $b[2] - $a[2] ) * $t ),
	);
}

function alloc( $img, array $rgb, int $alpha = 0 ) {
	return imagecolorallocatealpha( $img, $rgb[0], $rgb[1], $rgb[2], $alpha );
}

/** Vertical-ish gradient background, drawn as a diagonal sweep. */
function paint_background( $img, array $dark, array $accent ): void {
	for ( $y = 0; $y < H; $y++ ) {
		$t   = $y / H;
		$rgb = mix( $dark, mix( $dark, $accent, 0.38 ), $t );
		$c   = alloc( $img, $rgb );
		imagefilledrectangle( $img, 0, $y, W, $y, $c );
		imagecolordeallocate( $img, $c );
	}
}

function motif_grid( $img, array $accent, array $light ): void {
	$line = alloc( $img, $light, 100 );
	imagesetthickness( $img, 1 );
	for ( $x = 0; $x <= W; $x += 50 ) {
		imageline( $img, $x, 0, $x, H, $line );
	}
	for ( $y = 0; $y <= H; $y += 50 ) {
		imageline( $img, 0, $y, W, $y, $line );
	}
	$hot = alloc( $img, $accent, 25 );
	imagesetthickness( $img, 6 );
	imageline( $img, 0, (int) ( H * 0.68 ), W, (int) ( H * 0.32 ), $hot );
	$dot = alloc( $img, $accent, 0 );
	for ( $i = 0; $i < 7; $i++ ) {
		$x = (int) ( W * ( 0.12 + $i * 0.13 ) );
		$y = (int) ( H * 0.68 + ( ( H * 0.32 - H * 0.68 ) * ( $i / 6 ) ) );
		imagefilledellipse( $img, $x, $y, 18, 18, $dot );
	}
}

function motif_arcs( $img, array $accent, array $light ): void {
	$cx = (int) ( W * 0.72 );
	$cy = (int) ( H * 0.5 );
	imagesetthickness( $img, 3 );
	for ( $r = 120; $r < 1500; $r += 90 ) {
		$a = (int) max( 10, 110 - $r / 16 );
		$c = alloc( $img, $accent, $a );
		imagearc( $img, $cx, $cy, $r, $r, 0, 360, $c );
	}
	$c = alloc( $img, $light, 60 );
	imagesetthickness( $img, 2 );
	for ( $i = 0; $i < 5; $i++ ) {
		$y = (int) ( H * ( 0.2 + $i * 0.15 ) );
		imageline( $img, 0, $y, (int) ( W * 0.45 ), $y, $c );
	}
	imagefilledellipse( $img, $cx, $cy, 46, 46, alloc( $img, $accent, 0 ) );
}

function motif_bars( $img, array $accent, array $light ): void {
	$n    = 26;
	$step = (int) ( W / $n );
	for ( $i = 0; $i < $n; $i++ ) {
		$h  = (int) ( H * ( 0.12 + 0.72 * abs( sin( $i * 0.7 ) ) * ( 0.5 + 0.5 * cos( $i * 0.21 ) ) ) );
		$t  = $i / $n;
		$c  = alloc( $img, mix( $light, $accent, $t ), 45 );
		$x0 = $i * $step + 6;
		imagefilledrectangle( $img, $x0, H - $h, $x0 + $step - 12, H, $c );
	}
	imagesetthickness( $img, 4 );
	imageline( $img, 0, (int) ( H * 0.28 ), W, (int) ( H * 0.28 ), alloc( $img, $accent, 30 ) );
}

function motif_scatter( $img, array $accent, array $light ): void {
	mt_srand( 20261016 );
	$line = alloc( $img, $light, 110 );
	imagesetthickness( $img, 1 );
	$pts = array();
	for ( $i = 0; $i < 44; $i++ ) {
		$pts[] = array( mt_rand( 60, W - 60 ), mt_rand( 60, H - 60 ) );
	}
	foreach ( $pts as $i => $p ) {
		foreach ( $pts as $j => $q ) {
			if ( $j <= $i ) {
				continue;
			}
			$d = sqrt( ( $p[0] - $q[0] ) ** 2 + ( $p[1] - $q[1] ) ** 2 );
			if ( $d < 230 ) {
				imageline( $img, $p[0], $p[1], $q[0], $q[1], $line );
			}
		}
	}
	foreach ( $pts as $i => $p ) {
		$size = 0 === $i % 7 ? 26 : 12;
		$c    = 0 === $i % 7 ? alloc( $img, $accent, 0 ) : alloc( $img, $light, 20 );
		imagefilledellipse( $img, $p[0], $p[1], $size, $size, $c );
	}
}

function motif_waves( $img, array $accent, array $light ): void {
	imagesetthickness( $img, 3 );
	for ( $k = 0; $k < 16; $k++ ) {
		$c    = alloc( $img, mix( $light, $accent, $k / 15 ), 55 );
		$base = (int) ( H * ( 0.12 + $k * 0.052 ) );
		$prev = null;
		for ( $x = 0; $x <= W; $x += 8 ) {
			$y = (int) ( $base + 46 * sin( ( $x / W ) * 6.2 + $k * 0.42 ) );
			if ( null !== $prev ) {
				imageline( $img, $prev[0], $prev[1], $x, $y, $c );
			}
			$prev = array( $x, $y );
		}
	}
}

function motif_blocks( $img, array $accent, array $light ): void {
	mt_srand( 777 );
	$cols = 10;
	$rows = 6;
	$cw   = (int) ( W / $cols );
	$ch   = (int) ( H / $rows );
	for ( $r = 0; $r < $rows; $r++ ) {
		for ( $c = 0; $c < $cols; $c++ ) {
			$roll = mt_rand( 0, 100 );
			if ( $roll < 52 ) {
				continue;
			}
			$col = $roll > 92
				? alloc( $img, $accent, 20 )
				: alloc( $img, $light, mt_rand( 70, 115 ) );
			$pad = 8;
			imagefilledrectangle( $img, $c * $cw + $pad, $r * $ch + $pad, ( $c + 1 ) * $cw - $pad, ( $r + 1 ) * $ch - $pad, $col );
		}
	}
	imagesetthickness( $img, 8 );
	imagerectangle( $img, 40, 40, W - 40, H - 40, alloc( $img, $accent, 40 ) );
}

$argv_items = array_slice( $argv, 1 );
if ( count( $argv_items ) < 2 ) {
	fwrite( STDERR, "usage: make-covers.php <out-dir> <slug>:<hex>:<motif> ...\n" );
	exit( 1 );
}

$out_dir = array_shift( $argv_items );
if ( ! is_dir( $out_dir ) && ! mkdir( $out_dir, 0775, true ) ) {
	fwrite( STDERR, "cannot create $out_dir\n" );
	exit( 1 );
}

$dark  = hex_to_rgb( '#0B0E14' );
$light = hex_to_rgb( '#FBFAF7' );

$motifs = array(
	'grid'    => 'motif_grid',
	'arcs'    => 'motif_arcs',
	'bars'    => 'motif_bars',
	'scatter' => 'motif_scatter',
	'waves'   => 'motif_waves',
	'blocks'  => 'motif_blocks',
);

foreach ( $argv_items as $spec ) {
	list( $slug, $hex, $motif ) = array_pad( explode( ':', $spec ), 3, 'grid' );

	$accent = hex_to_rgb( $hex );
	$img    = imagecreatetruecolor( W, H );
	imagealphablending( $img, true );

	paint_background( $img, $dark, $accent );

	$fn = $motifs[ $motif ] ?? 'motif_grid';
	$fn( $img, $accent, $light );

	// Vignette so overlaid text stays readable.
	for ( $y = 0; $y < H; $y++ ) {
		$a = (int) ( 118 - 118 * ( $y / H ) );
		if ( $a <= 0 ) {
			continue;
		}
		$c = imagecolorallocatealpha( $img, 11, 14, 20, 127 - (int) ( $a / 2 ) );
		imagefilledrectangle( $img, 0, $y, W, $y, $c );
	}

	$path = rtrim( $out_dir, '/' ) . "/$slug.jpg";
	imagejpeg( $img, $path, 86 );
	imagedestroy( $img );
	echo "wrote $path\n";
}
