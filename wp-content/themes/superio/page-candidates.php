<?php
/**
 * The template for displaying pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other "pages" on your WordPress site will use a different template.
 *
 * @package WordPress
 * @subpackage Superio
 * @since Superio 1.0
 */
/*
*Template Name: Candidates Template
*/

if ( isset( $_REQUEST['load_type'] ) && WP_Job_Board_Pro_Mixes::is_ajax_request() ) {
	if ( get_query_var( 'paged' ) ) {
	    $paged = get_query_var( 'paged' );
	} elseif ( get_query_var( 'page' ) ) {
	    $paged = get_query_var( 'page' );
	} else {
	    $paged = 1;
	}

	$query_args = array(
		'post_type' => 'candidate',
	    'post_status' => 'publish',
	    'post_per_page' => wp_job_board_pro_get_option('number_candidates_per_page', 10),
	    'paged' => $paged,
	);

	global $wp_query;
	$atts = array();
	if ( !empty($wp_query->post->post_content) ) {
		$shortcode_atts = superio_get_shortcode_atts($wp_query->post->post_content, 'wp_job_board_pro_candidates');
		if ( !empty($shortcode_atts[0]) ) {
			foreach ($shortcode_atts[0] as $key => $value) {
				$atts[$key] = trim($value, '"');
			}
			
		}
	}

	$params = array();
	if (WP_Job_Board_Pro_Abstract_Filter::has_filter($atts)) {
		$params = $atts;
	}
	if ( WP_Job_Board_Pro_Candidate_Filter::has_filter() ) {
		$params = array_merge($params, $_GET);
	}

	$candidates = WP_Job_Board_Pro_Query::get_posts($query_args, $params);
	
	if ( 'items' !== $_REQUEST['load_type'] ) {
		echo WP_Job_Board_Pro_Template_Loader::get_template_part('archive-candidate-ajax-full', array('candidates' => $candidates));
	} else {
		echo WP_Job_Board_Pro_Template_Loader::get_template_part('archive-candidate-ajax-candidates', array('candidates' => $candidates));
	}
} else {
	get_header();
	$layout_type = superio_get_candidates_layout_type();
	$filter_sidebar = superio_get_candidates_filter_sidebar();
	$filter_top_sidebar = superio_get_candidates_filter_top_sidebar();

	$bg_color = get_post_meta( $post->ID, 'apus_page_color', true );
	if(!empty($bg_color)){
		$bg_color = 'style = background-color:'.$bg_color;
	}

	if ( $layout_type == 'half-map' ) {
	?>
		<section id="main-container" class="inner" <?php echo esc_attr($bg_color); ?>>
			<div class="row no-margin layout-type-<?php echo esc_attr($layout_type); ?>">
				<div id="main-content" class="col-sm-12 col-md-7 no-padding">
					<div class="inner-left">
						<?php if ( is_active_sidebar( $filter_sidebar ) ): ?>
							<div class="filter-sidebar offcanvas-filter-sidebar">
								<div class="mobile-groups-button hidden-lg hidden-md clearfix text-center">
									<button class=" btn btn-sm btn-theme btn-view-map" type="button"><i class="fa fa-map-o" aria-hidden="true"></i> <?php esc_html_e( 'Map View', 'superio' ); ?></button>
									<button class=" btn btn-sm btn-theme  btn-view-listing hidden-sm hidden-xs" type="button"><i class="fa fa-list" aria-hidden="true"></i> <?php esc_html_e( 'Listing View', 'superio' ); ?></button>
								</div>
								<div class="filter-scroll">
						   			<?php dynamic_sidebar( $filter_sidebar ); ?>
						   		</div>
						   	</div>
						   	<div class="over-dark"></div>
					   	<?php endif; ?>
					   	<div class="content-listing">
					   		
							<?php
							// Start the loop.
							while ( have_posts() ) : the_post();
								
								// Include the page content template.
								the_content();

								// If comments are open or we have at least one comment, load up the comment template.
								if ( comments_open() || get_comments_number() ) :
									comments_template();
								endif;

							// End the loop.
							endwhile;
							?>
						</div>
					</div><!-- .site-main -->
				</div><!-- .content-area -->
				<div class="col-md-5 no-padding">
					<div id="jobs-google-maps" class="hidden-sm hidden-xs fix-map">
						<span class="filter-in-sidebar btn-theme btn hidden-lg hidden-md"><i class="ti-filter"></i><span class="text"><?php esc_html_e( 'Filter', 'superio' ); ?></span></span>
					</div>
				</div>
			</div>
		</section>
	<?php
	} else {
		$sidebar_configs = superio_get_candidates_layout_configs();

		$layout_sidebar = superio_get_candidates_layout_sidebar();
	?>
		<section id="main-container" class="page-job-board inner layout-type-<?php echo esc_attr($layout_type); ?> <?php echo ((superio_get_candidates_show_filter_top())?'has-filter-top':''); ?>" <?php echo esc_attr($bg_color); ?>>
			<?php if ( $layout_type == 'top-map' ) { ?>
				<div class="mobile-groups-button hidden-lg hidden-md clearfix text-center">
					<button class=" btn btn-xs btn-theme btn-view-map" type="button"><i class="fas fa-map" aria-hidden="true"></i> <?php esc_html_e( 'Map View', 'superio' ); ?></button>
					<button class=" btn btn-xs btn-theme  btn-view-listing hidden-sm hidden-xs" type="button"><i class="fas fa-list" aria-hidden="true"></i> <?php esc_html_e( 'Properties View', 'superio' ); ?></button>
				</div>
				
				<div class="p-relative">
					<div id="jobs-google-maps" class="hidden-sm hidden-xs top-map"></div>

					<?php if ( $layout_sidebar == 'main' && is_active_sidebar( $filter_top_sidebar ) ) { ?>
						<div class="candidates-filter-top-sidebar-wrapper">
							<div class="container">
					   			<?php dynamic_sidebar( $filter_top_sidebar ); ?>
					   		</div>
					   	</div>
					<?php } ?>
				</div>
			<?php } ?>

			<?php superio_render_breadcrumbs(); ?>

			<?php if ( superio_get_candidates_show_filter_top() ) { ?>
				<div class="candidates-filter-top-sidebar-wrapper filter-top-sidebar-wrapper">
			   		<?php dynamic_sidebar( $filter_top_sidebar ); ?>
			   	</div>
			<?php } ?>

			<div class="layout-job-sidebar-v2 main-content <?php echo apply_filters('superio_page_content_class', 'container');?> inner">

				<?php superio_before_content( $sidebar_configs ); ?>
				<div class="row">
					<?php superio_display_sidebar_left( $sidebar_configs ); ?>

					<div id="main-content" class="col-sm-12 <?php echo esc_attr($sidebar_configs['main']['class']); ?>">
						<main id="main" class="site-main layout-type-<?php echo esc_attr($layout_type); ?>" role="main">

							<?php
							// Start the loop.
							while ( have_posts() ) : the_post();
								
								// Include the page content template.
								the_content();

								// If comments are open or we have at least one comment, load up the comment template.
								if ( comments_open() || get_comments_number() ) :
									comments_template();
								endif;

							// End the loop.
							endwhile;
							?>

						</main><!-- .site-main -->
					</div><!-- .content-area -->
					
					<?php superio_display_sidebar_right( $sidebar_configs ); ?>
				</div>
			</div>
		</section>
	<?php
	}

	get_footer();
}