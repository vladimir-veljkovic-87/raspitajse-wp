<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $post;

$icon = superio_get_config('candidate_expired_icon_img');

?>

<article id="post-<?php the_ID(); ?>" <?php post_class('candidate-single-expired'); ?>>


	<div class="candidate-content-area container">
		<!-- Main content -->
		<div class="content-single-candidate text-center">
			<?php if ( !empty($icon['id']) ) { ?>
				<div class="top-image">
					<?php echo superio_get_attachment_thumbnail($icon['id'], 'full'); ?>
				</div>
			<?php } ?>
			<h1>
				<?php
				$title = superio_get_config('candidate_expired_title');
				if ( !empty($title) ) {
					echo esc_html($title);
				} else {
					esc_html_e('We\'re Sorry Opps! Candidate Expired', 'superio');
				}
				?>
			</h1>
		   	<div class="content">
		   		<?php
				$description = superio_get_config('candidate_expired_description');
				if ( !empty($description) ) {
					echo esc_html($description);
				} else {
					esc_html_e('Unable to access the link. Candidate has been expired. Please contact the admin or who shared the link with you.', 'superio');
				}
				?>
		   	</div>

		   	<div class="return margin-top-30">
				<a class="btn-theme btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html__('Go To Home Page', 'superio') ?></a>
			</div>
		</div>
	</div>

</article><!-- #post-## -->
