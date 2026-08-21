<?php

get_header();

$sidebar_configs = superio_get_blog_layout_configs();

?>
<section id="main-container" class="wrapper-single-post main-content container-fluid p-lr-0 inner">
	
	<div class="row m-lr-0">
		<div id="main-content" class="col-xs-12 p-lr-0">
			<div id="primary" class="content-area">
				<div id="content" class="site-content detail-post" role="main">
					<?php
						// Start the Loop.
						while ( have_posts() ) : the_post();

							/*
							 * Include the post format-specific template for the content. If you want to
							 * use this in a child theme, then include a file called called content-___.php
							 * (where ___ is the post format) and that will be used instead.
							 */
							get_template_part( 'template-posts/single/content' );
							
			                
						// End the loop.
						endwhile;
					?>
				</div><!-- #content -->
			</div><!-- #primary -->
		</div>	
		
	</div>	
</section>
<?php get_footer(); ?>