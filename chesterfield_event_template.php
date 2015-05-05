<?php
/**
 * Simple template based off of twentyfifteen for chesterfield_event
 * @package chesterfield_upcoming_events
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php
		// Start the loop.
		while ( have_posts() ) : the_post();
    ?>

    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
      <header class="entry-header">
        <?php the_title('<h1 class="entry-title">', '</h1>' ); ?>
      </header>
      <div class="entry-content">
        <p><?php
          echo sprintf( __( 'The event will be held on %s', 'cf_domain' ),
            date("F j, Y", strtotime( get_post_meta( get_the_ID(), 'cf_event_date', TRUE ) ) )
          );
        ?>
        </p>
        <p><?php echo __( 'Please come back for more details.' ); ?></p>
      </div>
    <?php

		// End the loop.
		endwhile;
		?>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_footer(); ?>
