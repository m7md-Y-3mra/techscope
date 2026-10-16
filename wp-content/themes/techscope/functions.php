<?php
/**
 * TechScope theme functions.
 *
 * @package TechScope
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TECHSCOPE_VERSION', '1.0.0' );

/**
 * Theme supports that block themes do not switch on for us.
 */
function techscope_setup() {
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'style.css' );
	load_theme_textdomain( 'techscope', get_template_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'techscope_setup' );

/**
 * Fonts and stylesheet.
 *
 * enqueue_block_assets runs on both the front end and inside the editor, so the
 * editor preview matches the published page.
 */
function techscope_enqueue_assets() {
	wp_enqueue_style(
		'techscope-fonts',
		'https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;1,8..60,400&family=JetBrains+Mono:wght@400;500&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'techscope-style',
		get_stylesheet_uri(),
		array( 'techscope-fonts' ),
		TECHSCOPE_VERSION
	);
}
add_action( 'enqueue_block_assets', 'techscope_enqueue_assets' );

/**
 * Preconnect to the font hosts so the first paint is not held up.
 */
function techscope_resource_hints( $hints, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$hints[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
		$hints[] = 'https://fonts.googleapis.com';
	}

	return $hints;
}
add_filter( 'wp_resource_hints', 'techscope_resource_hints', 10, 2 );

/**
 * Pattern category used by the theme's own patterns.
 */
function techscope_register_pattern_category() {
	register_block_pattern_category(
		'techscope',
		array( 'label' => __( 'TechScope', 'techscope' ) )
	);
}
add_action( 'init', 'techscope_register_pattern_category' );

/**
 * Image sizes used by the magazine grids.
 */
function techscope_image_sizes() {
	add_image_size( 'techscope-lead', 1600, 900, true );
	add_image_size( 'techscope-card', 800, 500, true );
}
add_action( 'after_setup_theme', 'techscope_image_sizes' );

/**
 * Reading time, exposed as a block binding so templates can print it without
 * a custom block or a shortcode.
 *
 * @param array $source_args Arguments passed from the block's metadata.
 * @return string|null
 */
function techscope_reading_time_binding( $source_args = array() ) {
	$post = get_post();

	if ( ! $post ) {
		return null;
	}

	$words   = str_word_count( wp_strip_all_tags( $post->post_content ) );
	$minutes = max( 1, (int) ceil( $words / 220 ) );

	return sprintf(
		/* translators: %d: estimated reading time in minutes. */
		_n( '%d min read', '%d min read', $minutes, 'techscope' ),
		$minutes
	);
}

function techscope_register_bindings() {
	if ( ! function_exists( 'register_block_bindings_source' ) ) {
		return;
	}

	register_block_bindings_source(
		'techscope/reading-time',
		array(
			'label'              => __( 'Reading time', 'techscope' ),
			'get_value_callback' => 'techscope_reading_time_binding',
			'uses_context'       => array( 'postId' ),
		)
	);
}
add_action( 'init', 'techscope_register_bindings' );

/**
 * Keep excerpts to a magazine-deck length.
 *
 * @param int $length Default word count.
 * @return int
 */
function techscope_excerpt_length( $length ) {
	return 28;
}
add_filter( 'excerpt_length', 'techscope_excerpt_length' );

/**
 * Search should return articles, not pages or attachments.
 *
 * @param WP_Query $query The query being run.
 */
function techscope_search_only_posts( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}

	$query->set( 'post_type', array( 'post' ) );
}
add_action( 'pre_get_posts', 'techscope_search_only_posts' );
