<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package WordPress
 * @subpackage Superio
 * @since Superio 1.0
 */
/*
*Template Name: 404 Page
*/
get_header();
$icon = superio_get_config('icon-img');

$img_url = get_template_directory_uri().'/images/error.jpg';
if ( !empty($icon['id']) ) {
	$img = wp_get_attachment_image_src($icon['id'], 'full');
	if ( !empty($img[0]) ) {
		$img_url = $img[0];
	}
}
?>
<section class="page-404">
	<div id="main-container" class="inner">
		<div id="main-content" class="main-page">
			<section class="error-404 not-found clearfix">
				<div class="container">
					<div class="clearfix text-center">
						<div class="top-image">
							<img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
						</div>
						<div class="slogan">
							<h4 class="title-big">
								<?php
								$title = superio_get_config('404_title');
								if ( !empty($title) ) {
									echo esc_html($title);
								} else {
									esc_html_e('Oops! That page can&rsquo;t be found.', 'superio');
								}
								?>
							</h4>
						</div>
						<div class="page-content">
							<div class="description">
								<?php
								$description = superio_get_config('404_description');
								if ( !empty($description) ) {
									echo esc_html($description);
								} else {
									esc_html_e('It looks like nothing was found at this location. Maybe try a search?', 'superio');
								}
								?>
							</div>
							<div class="return">
								<a class="btn-theme btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html__('Go To Home Page', 'superio') ?></a>
							</div>
						</div><!-- .page-content -->
					</div>
				</div>
			</section><!-- .error-404 -->
		</div><!-- .content-area -->
	</div>
</section>
<?php get_footer(); ?>