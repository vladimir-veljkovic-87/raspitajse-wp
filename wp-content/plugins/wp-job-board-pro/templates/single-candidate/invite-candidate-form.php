<?php

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}


?>

<div id="invite-candidate-form-wrapper-<?php echo esc_attr($candidate_id); ?>" class="invite-candidate-form-wrapper mfp-hide">
	<div class="inner">
		<div class="title-wrapper flex-middle">
			<h2 class="widget-title">
				<span><?php echo __('Invite to apply job', 'wp-job-board-pro'); ?></span>
			</h2>
			<a href="javascript:void(0);" class="close-magnific-popup ali-right"><i class="ti-close"></i></a>
		</div>
		<div class="widget-content">
			<div class="des">
				<?php esc_html_e('Select job to invite this user', 'wp-job-board-pro'); ?>
			</div>
			<div class="jobs">
				<form id="invite-candidate-form-<?php echo esc_attr($candidate_id); ?>" class="invite-candidate-form" method="post" action="post">
					<?php
						$user_id = WP_Job_Board_Pro_User::get_user_id();
						$query_vars = array(
							'post_type'     => 'job_listing',
							'post_status'   => 'publish',
							'posts_per_page' => -1,
							'author'        => $user_id,
							'orderby'		=> 'date',
							'order'			=> 'DESC',
							'fields'		=> 'ids',
							'meta_query' => array(
								array(
									'relation' => 'OR',
									array(
										'key'       => WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX.'filled',
										'value'     => 'on',
										'compare'   => '!=',
									),
									array(
										'key'       => WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX.'filled',
										'compare' => 'NOT EXISTS',
									),
								)
							)
						);

						$jobs = new WP_Query($query_vars);
						if ( !empty($jobs->posts) ) {
							?>
							<div class="form-group">
								<ul class="checklist">
								<?php
								foreach ($jobs->posts as $job_id) { ?>
									<li>
										<label>
											<input type="checkbox" name="job_ids[]" value="<?php echo esc_attr($job_id); ?>">
											<?php echo get_the_title($job_id); ?>
										</label>
									</li>
									<?php
								}
								?>
								</ul>
							</div>

							<input type="hidden" name="candidate_id" value="<?php echo esc_attr($candidate_id); ?>">

							<button class="btn btn-theme" name="invite-candidate"><?php echo esc_html__( 'Invite', 'wp-job-board-pro' ); ?></button>
							<?php
						} else {
							?>
							<div class="alert alert-warning not-allow-wrapper">
								<?php echo __( 'You need to have jobs to invite the candidates.', 'wp-job-board-pro' ); ?>
							</div>
							<?php
						}
					?>

					
				</form>
			</div>
		</div>
	</div>
</div>
