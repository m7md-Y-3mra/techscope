<?php
/**
 * Seeds the standing editorial pages. Idempotent.
 *
 * @package TechScope
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

$pages = array(
	array(
		'slug'    => 'about',
		'title'   => 'About TechScope',
		'content' => <<<'HTML'
<!-- wp:paragraph -->
<p>TechScope covers the systems, silicon and software that the rest of the industry depends on and rarely explains. We write for people who build things, and we assume you would rather have the mechanism than the metaphor.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">What we cover</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list">
<!-- wp:list-item -->
<li><strong>AI</strong> — serving economics, model architecture and the infrastructure underneath both.</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li><strong>Hardware</strong> — packaging, interconnect, power and the physical limits everything runs into.</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li><strong>Software</strong> — languages, build systems and the maintenance nobody budgets for.</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li><strong>Security</strong> — supply chains, cryptography and the economics of defence.</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li><strong>Science &amp; policy</strong> — research heading for production, and the rules it will meet there.</li>
<!-- /wp:list-item -->
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading">How we work</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Stories are reported by the desk that covers the beat. Corrections are appended to the article rather than issued quietly, and significant ones are noted at the top. We do not publish vendor copy, and we label anything written in partnership.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Reading us elsewhere</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Every section has a feed, and the full archive is available via the <a href="/wp-sitemap.xml">sitemap</a>. Subscribe to the main <a href="/feed/">RSS feed</a> to get everything.</p>
<!-- /wp:paragraph -->
HTML
	),
	array(
		'slug'    => 'contact',
		'title'   => 'Contact the desk',
		'content' => <<<'HTML'
<!-- wp:paragraph -->
<p>Tips, corrections and complaints all go to the same place, and all of them get read.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Tips</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>If you work somewhere interesting and something is going wrong, we would like to hear about it. Tell us what you can verify and what you cannot — we will not publish either without checking, and we will not identify you without agreeing how first.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Corrections</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Point at the sentence. If we got it wrong we will say so in the article, with the date and what changed.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Pitches</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>We commission from people who do the work. A short note describing the argument and why you are the one to make it is more useful than a full draft.</p>
<!-- /wp:paragraph -->
HTML
	),
);

$done = 0;

foreach ( $pages as $page ) {
	$existing = get_page_by_path( $page['slug'], OBJECT, 'page' );

	$postarr = array(
		'post_title'   => $page['title'],
		'post_name'    => $page['slug'],
		'post_content' => $page['content'],
		'post_status'  => 'publish',
		'post_type'    => 'page',
	);

	if ( $existing ) {
		$postarr['ID'] = $existing->ID;
		wp_update_post( $postarr );
	} else {
		wp_insert_post( $postarr );
	}

	$done++;
}

WP_CLI::success( "pages seeded: {$done}" );
