<?php
get_header(); ?>

<div id="main-content" class="main-content">

<?php
	if ( is_front_page() && twentyfourteen_has_featured_posts() ) {
		// Include the featured content template.
		get_template_part( 'featured-content' );
	}
?>
	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">

			<?php
				// Start the Loop.
				while ( have_posts() ) : the_post();

					// Include the page content template.
					get_template_part( 'content', 'page' );

					// stuff, which makes the resolutions apear
					global $wpdb;
					$results = $wpdb->get_results("SELECT title,author,date,link FROM wp_reso_db WHERE author_is_bufata = 1 ORDER BY date DESC");
					if(!empty($results)) {
					?>
					<table border="1">
						<tr>
							<th>Titel</th>
							<th>Verfasser</th>
							<th>Datum</th>
							<th>Link</th>
						</tr>
					<?php
						foreach($results as $r) {
							echo '<tr>';
							echo "<td>".$r->title."</td>";
							echo '<td>'.$r->author.'</td>';
							echo '<td>'.$r->date.'</td>';
							echo '<td><a href="'.$r->link.'" target="_blank">Dokument</td>';
							echo '</tr>';
						}
					?>
					</table>
					<?php
					} else {
						echo "<p>Es wurden leider keine Resolutionen in der Datenbank gefunden.</p>";
					}

					// If comments are open or we have at least one comment, load up the comment template.
					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}
				endwhile;
			?>

		</div><!-- #content -->
	</div><!-- #primary -->
	<?php get_sidebar( 'content' ); ?>
</div><!-- #main-content -->

<?php
get_sidebar();
get_footer();
