<?php
/**
 * Title: Newsletter band
 * Slug: techscope/newsletter
 * Categories: techscope
 */
?>
<!-- wp:group {"align":"full","style":{"spacing":{"margin":{"top":"var:preset|spacing|60"},"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|30","right":"var:preset|spacing|30"}}},"backgroundColor":"ink","textColor":"paper","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-paper-color has-ink-background-color has-text-color has-background" style="margin-top:var(--wp--preset--spacing--60);padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--30)">
	<!-- wp:columns {"align":"wide","verticalAlignment":"center"} -->
	<div class="wp-block-columns alignwide are-vertically-aligned-center">
		<!-- wp:column {"verticalAlignment":"center","width":"60%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:60%">
			<!-- wp:paragraph {"className":"techscope-eyebrow techscope-eyebrow--signal","style":{"spacing":{"margin":{"top":"0"}}}} -->
			<p class="techscope-eyebrow techscope-eyebrow--signal" style="margin-top:0"><?php echo esc_html__( 'The Dispatch', 'techscope' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:heading {"level":2,"style":{"spacing":{"margin":{"top":"var:preset|spacing|10","bottom":"var:preset|spacing|20"}}}} -->
			<h2 class="wp-block-heading" style="margin-top:var(--wp--preset--spacing--10);margin-bottom:var(--wp--preset--spacing--20)"><?php echo esc_html__( 'One careful read, every Thursday', 'techscope' ); ?></h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"style":{"typography":{"fontSize":"1.0625rem"},"spacing":{"margin":{"bottom":"0"}}}} -->
			<p style="font-size:1.0625rem;margin-bottom:0"><?php echo esc_html__( 'What shipped, what broke, and what it means — written by the desk that covered it. No churn, no hot takes.', 'techscope' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"center","width":"40%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:40%">
			<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"right"}} -->
			<div class="wp-block-buttons">
				<!-- wp:button {"backgroundColor":"signal","textColor":"paper"} -->
				<div class="wp-block-button"><a class="wp-block-button__link has-paper-color has-signal-background-color has-text-color has-background wp-element-button" href="/feed/"><?php echo esc_html__( 'Subscribe via RSS', 'techscope' ); ?></a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
