<?php
/**
 * Plugin Name: TechScope Core
 * Description: Social metadata and structured data for the TechScope magazine. Theme-independent, so the markup survives a theme change.
 * Version: 1.0.0
 * Requires at least: 6.7
 * Requires PHP: 8.0
 * Author: TechScope Editorial
 * License: GPL-2.0-or-later
 * Text Domain: techscope-core
 *
 * @package TechScope
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Best available description for the current request.
 *
 * @return string
 */
function techscope_core_description(): string {
	if ( is_singular() ) {
		$post = get_queried_object();

		if ( $post instanceof WP_Post ) {
			$excerpt = has_excerpt( $post ) ? $post->post_excerpt : wp_trim_words( wp_strip_all_tags( $post->post_content ), 32, '…' );

			return trim( $excerpt );
		}
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();

		if ( $term instanceof WP_Term && $term->description ) {
			return trim( wp_strip_all_tags( $term->description ) );
		}
	}

	return trim( (string) get_bloginfo( 'description' ) );
}

/**
 * Canonical-ish URL for the current request.
 *
 * @return string
 */
function techscope_core_url(): string {
	if ( is_singular() ) {
		return (string) get_permalink();
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$link = get_term_link( get_queried_object() );

		return is_wp_error( $link ) ? home_url( '/' ) : (string) $link;
	}

	return home_url( '/' );
}

/**
 * Open Graph, Twitter card and a meta description.
 *
 * WordPress emits the title tag, canonical link, robots directives, feeds and
 * the sitemap on its own, so this only fills the gaps core leaves.
 *
 * @return void
 */
function techscope_core_meta_tags(): void {
	if ( is_404() || is_search() ) {
		return;
	}

	$title       = wp_get_document_title();
	$description = techscope_core_description();
	$url         = techscope_core_url();
	$type        = is_singular( 'post' ) ? 'article' : 'website';

	printf(
		"\n<meta name=\"description\" content=\"%s\" />\n",
		esc_attr( $description )
	);

	printf( "<meta property=\"og:site_name\" content=\"%s\" />\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( "<meta property=\"og:type\" content=\"%s\" />\n", esc_attr( $type ) );
	printf( "<meta property=\"og:title\" content=\"%s\" />\n", esc_attr( $title ) );
	printf( "<meta property=\"og:description\" content=\"%s\" />\n", esc_attr( $description ) );
	printf( "<meta property=\"og:url\" content=\"%s\" />\n", esc_url( $url ) );

	$image = '';

	if ( is_singular() && has_post_thumbnail() ) {
		$image = (string) get_the_post_thumbnail_url( null, 'techscope-lead' );
	}

	if ( $image ) {
		printf( "<meta property=\"og:image\" content=\"%s\" />\n", esc_url( $image ) );
		printf( "<meta name=\"twitter:card\" content=\"summary_large_image\" />\n" );
	} else {
		printf( "<meta name=\"twitter:card\" content=\"summary\" />\n" );
	}

	if ( is_singular( 'post' ) ) {
		printf( "<meta property=\"article:published_time\" content=\"%s\" />\n", esc_attr( (string) get_the_date( 'c' ) ) );
		printf( "<meta property=\"article:modified_time\" content=\"%s\" />\n", esc_attr( (string) get_the_modified_date( 'c' ) ) );

		$author = get_the_author_meta( 'display_name' );
		if ( $author ) {
			printf( "<meta property=\"article:author\" content=\"%s\" />\n", esc_attr( $author ) );
		}

		foreach ( (array) get_the_category() as $category ) {
			printf( "<meta property=\"article:section\" content=\"%s\" />\n", esc_attr( $category->name ) );
			break;
		}

		foreach ( (array) get_the_tags() as $tag ) {
			printf( "<meta property=\"article:tag\" content=\"%s\" />\n", esc_attr( $tag->name ) );
		}
	}

	printf( "<meta name=\"twitter:title\" content=\"%s\" />\n", esc_attr( $title ) );
	printf( "<meta name=\"twitter:description\" content=\"%s\" />\n\n", esc_attr( $description ) );
}
add_action( 'wp_head', 'techscope_core_meta_tags', 5 );

/**
 * JSON-LD: WebSite on the front page, Article on single posts.
 *
 * @return void
 */
function techscope_core_schema(): void {
	$graph = array();

	if ( is_front_page() || is_home() ) {
		$graph[] = array(
			'@type'           => 'WebSite',
			'@id'             => home_url( '/#website' ),
			'name'            => get_bloginfo( 'name' ),
			'description'     => get_bloginfo( 'description' ),
			'url'             => home_url( '/' ),
			'inLanguage'      => get_bloginfo( 'language' ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => array(
					'@type'       => 'EntryPoint',
					'urlTemplate' => home_url( '/?s={search_term_string}' ),
				),
				'query-input' => 'required name=search_term_string',
			),
		);
	}

	if ( is_singular( 'post' ) ) {
		$post = get_queried_object();

		$article = array(
			'@type'            => 'NewsArticle',
			'@id'              => get_permalink() . '#article',
			'headline'         => get_the_title(),
			'description'      => techscope_core_description(),
			'datePublished'    => get_the_date( 'c' ),
			'dateModified'     => get_the_modified_date( 'c' ),
			'url'              => get_permalink(),
			'inLanguage'       => get_bloginfo( 'language' ),
			'mainEntityOfPage' => get_permalink(),
			'author'           => array(
				'@type' => 'Person',
				'name'  => get_the_author_meta( 'display_name', $post->post_author ),
				'url'   => get_author_posts_url( (int) $post->post_author ),
			),
			'publisher'        => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
				'url'   => home_url( '/' ),
			),
		);

		if ( has_post_thumbnail() ) {
			$article['image'] = get_the_post_thumbnail_url( null, 'techscope-lead' );
		}

		$sections = get_the_category();
		if ( $sections ) {
			$article['articleSection'] = $sections[0]->name;
		}

		$tags = get_the_tags();
		if ( $tags ) {
			$article['keywords'] = implode( ', ', wp_list_pluck( $tags, 'name' ) );
		}

		$graph[] = $article;
	}

	if ( ! $graph ) {
		return;
	}

	$payload = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);

	printf(
		"<script type=\"application/ld+json\">%s</script>\n",
		wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
	);
}
add_action( 'wp_head', 'techscope_core_schema', 6 );

/**
 * Keep search-result and paged archive pages out of the index without
 * suppressing the article pages that should rank.
 *
 * @param array $robots Robots directives.
 * @return array
 */
function techscope_core_robots( $robots ): array {
	if ( is_search() || is_paged() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}

	return $robots;
}
add_filter( 'wp_robots', 'techscope_core_robots' );
