<?php
/**
 * Seeds TechScope demo content.
 *
 * Run with:
 *   ./tools/wp eval-file wp-content/techscope-tools/seed-content.php
 *
 * Idempotent: re-running updates existing posts rather than duplicating them.
 *
 * @package TechScope
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

define( 'TS_COVERS', WP_CONTENT_DIR . '/uploads/_seed-covers' );
define( 'TS_TOOLS', WP_CONTENT_DIR . '/techscope-tools' );

/**
 * Turns a compact article spec into WordPress block markup.
 *
 * @param array $blocks List of single-key arrays: p, h2, list, quote, code.
 * @return string
 */
function ts_build_blocks( array $blocks ): string {
	$out = array();

	foreach ( $blocks as $block ) {
		$type  = array_key_first( $block );
		$value = $block[ $type ];

		switch ( $type ) {
			case 'p':
				$out[] = "<!-- wp:paragraph -->\n<p>{$value}</p>\n<!-- /wp:paragraph -->";
				break;

			case 'h2':
				$out[] = "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">{$value}</h2>\n<!-- /wp:heading -->";
				break;

			case 'list':
				$items = '';
				foreach ( $value as $item ) {
					$items .= "<!-- wp:list-item -->\n<li>{$item}</li>\n<!-- /wp:list-item -->\n";
				}
				$out[] = "<!-- wp:list -->\n<ul class=\"wp-block-list\">\n{$items}</ul>\n<!-- /wp:list -->";
				break;

			case 'quote':
				$out[] = "<!-- wp:pullquote {\"textColor\":\"ink\"} -->\n"
					. "<figure class=\"wp-block-pullquote has-ink-color has-text-color\"><blockquote><p>{$value}</p></blockquote></figure>\n"
					. '<!-- /wp:pullquote -->';
				break;

			case 'code':
				$out[] = "<!-- wp:code -->\n<pre class=\"wp-block-code\"><code>"
					. esc_html( $value )
					. "</code></pre>\n<!-- /wp:code -->";
				break;
		}
	}

	return implode( "\n\n", $out );
}

/**
 * Attaches a generated cover as the post's featured image, once.
 *
 * @param int    $post_id Target post.
 * @param string $slug    Cover file basename.
 * @param string $title   Used for alt text.
 * @return void
 */
function ts_attach_cover( int $post_id, string $slug, string $title ): void {
	if ( get_post_thumbnail_id( $post_id ) ) {
		return;
	}

	$source = TS_COVERS . "/{$slug}.jpg";

	if ( ! file_exists( $source ) ) {
		WP_CLI::warning( "no cover for {$slug}" );
		return;
	}

	// wp_handle_sideload() moves the file, so hand it a copy.
	$tmp = wp_tempnam( $slug );
	copy( $source, $tmp );

	$file = array(
		'name'     => "{$slug}.jpg",
		'type'     => 'image/jpeg',
		'tmp_name' => $tmp,
		'error'    => 0,
		'size'     => filesize( $tmp ),
	);

	$attachment_id = media_handle_sideload( $file, $post_id, $title );

	if ( is_wp_error( $attachment_id ) ) {
		@unlink( $tmp );
		WP_CLI::warning( "sideload failed for {$slug}: " . $attachment_id->get_error_message() );
		return;
	}

	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $title );
	set_post_thumbnail( $post_id, $attachment_id );
}

/**
 * Creates the editorial bylines.
 *
 * @return array Map of role key to user ID.
 */
function ts_ensure_authors(): array {
	$people = array(
		'r.okonkwo'  => array( 'Rhoda Okonkwo', 'Senior correspondent covering silicon, packaging and the physical limits of compute.' ),
		'd.lindqvist' => array( 'Dag Lindqvist', 'Writes about software supply chains, build systems and the infrastructure nobody funds.' ),
		'm.saito'    => array( 'Mei Saito', 'Covers machine learning systems and the economics of running them in production.' ),
	);

	$ids = array();

	foreach ( $people as $login => $meta ) {
		list( $display, $bio ) = $meta;

		$user = get_user_by( 'login', $login );

		if ( ! $user ) {
			$user_id = wp_insert_user(
				array(
					'user_login'   => $login,
					'user_pass'    => wp_generate_password( 24, true, true ),
					'user_email'   => $login . '@techscope.local',
					'display_name' => $display,
					'first_name'   => strtok( $display, ' ' ),
					'last_name'    => trim( strstr( $display, ' ' ) ),
					'description'  => $bio,
					'role'         => 'author',
				)
			);

			if ( is_wp_error( $user_id ) ) {
				WP_CLI::warning( "author {$login}: " . $user_id->get_error_message() );
				continue;
			}
		} else {
			$user_id = $user->ID;
			wp_update_user(
				array(
					'ID'          => $user_id,
					'description' => $bio,
				)
			);
		}

		$ids[ $login ] = (int) $user_id;
	}

	return $ids;
}

$authors    = ts_ensure_authors();
$author_ids = array_values( $authors );

$articles = array_merge(
	require TS_TOOLS . '/articles-1.php',
	require TS_TOOLS . '/articles-2.php',
	require TS_TOOLS . '/articles-3.php'
);

$created = 0;
$updated = 0;
$index   = 0;

foreach ( $articles as $article ) {
	$existing = get_page_by_path( $article['slug'], OBJECT, 'post' );

	$postarr = array(
		'post_title'   => $article['title'],
		'post_name'    => $article['slug'],
		'post_content' => ts_build_blocks( $article['blocks'] ),
		'post_excerpt' => $article['excerpt'],
		'post_status'  => 'publish',
		'post_type'    => 'post',
		'post_author'  => $author_ids[ $index % count( $author_ids ) ],
		'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( "-{$article['days_ago']} days -" . ( $index * 37 ) . ' minutes' ) ),
	);

	if ( $existing ) {
		$postarr['ID'] = $existing->ID;
		$post_id       = wp_update_post( $postarr, true );
		$updated++;
	} else {
		$post_id = wp_insert_post( $postarr, true );
		$created++;
	}

	if ( is_wp_error( $post_id ) ) {
		WP_CLI::warning( "{$article['slug']}: " . $post_id->get_error_message() );
		continue;
	}

	// category is hierarchical, so wp_set_post_terms() needs term IDs, not slugs.
	$category = get_term_by( 'slug', $article['category'], 'category' );
	if ( $category ) {
		wp_set_post_terms( $post_id, array( (int) $category->term_id ), 'category', false );
	} else {
		WP_CLI::warning( "missing category {$article['category']}" );
	}

	wp_set_post_terms( $post_id, $article['tags'], 'post_tag', false );

	ts_attach_cover( (int) $post_id, $article['slug'], $article['title'] );

	$index++;
}

// Remove the stock "Uncategorized" assignment left over from the sample post.
$sample = get_page_by_path( 'hello-world', OBJECT, 'post' );
if ( $sample ) {
	wp_delete_post( $sample->ID, true );
}

$sample_page = get_page_by_path( 'sample-page', OBJECT, 'page' );
if ( $sample_page ) {
	wp_delete_post( $sample_page->ID, true );
}

WP_CLI::success( "articles created: {$created}, updated: {$updated}" );
