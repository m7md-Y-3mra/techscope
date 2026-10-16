<?php
/**
 * Title: No results
 * Slug: techscope/no-results
 * Categories: techscope
 * Inserter: no
 */
?>
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)">
	<!-- wp:paragraph {"className":"techscope-eyebrow techscope-eyebrow--signal"} -->
	<p class="techscope-eyebrow techscope-eyebrow--signal">Nothing found</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":2} -->
	<h2 class="wp-block-heading"><?php echo esc_html__( 'No articles match that query', 'techscope' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"textColor":"muted"} -->
	<p class="has-muted-color has-text-color"><?php echo esc_html__( 'Try a broader term, or browse a desk from the navigation above.', 'techscope' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:search {"label":"Search","showLabel":false,"placeholder":"Search TechScope","buttonText":"Search","buttonPosition":"button-outside"} /-->
</div>
<!-- /wp:group -->
