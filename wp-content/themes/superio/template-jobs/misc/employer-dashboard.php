<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// $user_id = WP_Job_Board_Pro_User::get_user_id();
if ( empty($user_id) ) {
	return;
}
$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);

$jobs = new WP_Query(array(
    'post_type' => 'job_listing',
    'post_status' => 'publish',
    'author' => $user_id,
    'fields' => 'ids',
    'posts_per_page'    => -1,
));
$count_jobs = $jobs->found_posts;
$shortlist = get_post_meta($employer_id, WP_JOB_BOARD_PRO_EMPLOYER_PREFIX.'shortlist', true);
$shortlist = is_array($shortlist) ? count($shortlist) : 0;
$total_reviews = WP_Job_Board_Pro_Review::get_total_reviews($employer_id);
$views = get_post_meta($employer_id, WP_JOB_BOARD_PRO_EMPLOYER_PREFIX.'views_count', true);

$ids = !empty($jobs->posts) ? $jobs->posts : array();
$job_ids = array(0);
if ( $ids ) {
	foreach ($ids as $id) {
		$job_ida = apply_filters( 'wp-job-board-pro-translations-post-ids', $id );
		if ( !empty($job_ida) && is_array($job_ida) ) {
			$job_ids = array_merge($job_ids, $job_ida );
		} else {
			$job_ids = array_merge($job_ids, array($id) );
		}
	}
}
$query_vars = array(
	'post_type'         => 'job_applicant',
	'posts_per_page'    => 1,
	'paged'    			=> 1,
	'post_status'       => 'publish',
	'meta_query'       => array(
		array(
			'key' => WP_JOB_BOARD_PRO_APPLICANT_PREFIX.'job_id',
			'value'     => $job_ids,
			'compare'   => 'IN',
		)
	)
);
$applicants = new WP_Query($query_vars);
$applicants_count = $applicants->found_posts;


?>
<div class="box-dashboard-wrapper employer-dashboard-wrapper">
	<h3 class="title"><?php esc_html_e('Applications statistics', 'superio'); ?></h3>
	<div class="space-30">
		<div class="statistics row">
			<div class="col-xs-12 col-lg-3 col-sm-6">
				<div class="inner-header">
					<div class="posted-jobs list-item flex-middle justify-content-between text-right">
						<div class="icon-wrapper">
							<div class="icon">
								<i class="flaticon-briefcase-1"></i>
							</div>
						</div>
						<div class="inner">
							<div class="number-count"><?php echo esc_html( $count_jobs ? WP_Job_Board_Pro_Mixes::format_number($count_jobs) : 0); ?></div>
							<span><?php esc_html_e('Posted Jobs', 'superio'); ?></span>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-lg-3 col-sm-6">
				<div class="inner-header">
				<div class="views-count-wrapper list-item flex-middle justify-content-between text-right">
					<div class="icon-wrapper">
					<div class="icon">
						<i class="flaticon-resume"></i>
					</div>
					</div>
					<div class="inner">
						<div class="number-count"><?php echo esc_html( $applicants_count ? WP_Job_Board_Pro_Mixes::format_number($applicants_count) : 0 ); ?></div>
						<span><?php esc_html_e('Application', 'superio'); ?></span>
					</div>
				</div>
				</div>
			</div>
			<div class="col-xs-12 col-lg-3 col-sm-6">
				<div class="inner-header">
				<div class="review-count-wrapper list-item flex-middle justify-content-between text-right">
					<div class="icon-wrapper">
					<div class="icon">
						<i class="flaticon-chat"></i>
					</div>
					</div>
					<div class="inner">
						<div class="number-count"><?php echo esc_html( $total_reviews ? WP_Job_Board_Pro_Mixes::format_number($total_reviews) : 0 ); ?></div>
						<span><?php esc_html_e('Review', 'superio'); ?></span>
					</div>
				</div>
				</div>
			</div>
			<div class="col-xs-12 col-lg-3 col-sm-6">
				<div class="inner-header">
				<div class="shortlist list-item flex-middle justify-content-between text-right">
					<div class="icon-wrapper">
					<div class="icon">
						<i class="flaticon-bookmark"></i>
					</div>
					</div>
					<div class="inner">
						<div class="number-count"><?php echo esc_html($shortlist ? WP_Job_Board_Pro_Mixes::format_number($shortlist) : 0); ?></div>
						<span><?php esc_html_e('Shortlisted', 'superio'); ?></span>
					</div>
				</div>
				</div>
			</div>
		</div>
	</div>
	<?php if ( version_compare(WP_JOB_BOARD_PRO_PLUGIN_VERSION, '1.1.6', '>') ) {
		wp_enqueue_script( 'chart', get_template_directory_uri() . '/js/chart.min.js', array( 'jquery' ), '1.0.0', true );

		$class_second_column = '';

	?>
		<div class="row">
			<div class="col-sm-8">
			<?php
				$query_vars = array(
					'post_type'     => 'job_listing',
					'post_status'   => apply_filters('wp-job-board-pro-my-jobs-stats-post-statuses', array( 'publish', 'expired' )),
					'paged'         => 1,
					'author'        => $user_id,
					'orderby'		=> 'date',
					'order'			=> 'DESC',
					'fields'		=> 'ids'
				);

				$jobs = new WP_Query($query_vars);
				if ( !empty($jobs->posts) ) {
					superio_load_select2();
					$class_second_column = 'with-employer';
			?>
				<div class="inner-list">
					<h3 class="title-small"><?php echo esc_html__( 'Page Views', 'superio' ); ?></h3>
					<div class="page_views-wrapper">
						
						<div class="page_views-wrapper">
							<canvas id="dashboard_job_chart_wrapper" data-job_id="<?php echo esc_attr($jobs->posts[0]); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce( 'superio-job-chart-nonce' )); ?>"></canvas>
						</div>

						<div class="search-form-wrapper">
							<form class="stats-graph-search-form" method="post">
								<div class="row">
									<div class="col-xs-6">
										<div class="form-group">
											<label><?php esc_html_e('Jobs', 'superio'); ?></label>
											<select class="form-control" name="job_id">
												<?php foreach ($jobs->posts as $post_id) { ?>
													<option value="<?php echo esc_attr($post_id); ?>"><?php echo esc_html(get_the_title($post_id)); ?></option>
												<?php } ?>
											</select>
										</div>
									</div>

									<div class="col-xs-6">
										<div class="form-group">
											<label><?php esc_html_e('Number Days', 'superio'); ?></label>
											<select class="form-control" name="nb_days">
												<option value="30"><?php esc_html_e('30 days', 'superio'); ?></option>
												<option value="15" selected><?php esc_html_e('15 days', 'superio'); ?></option>
												<option value="7"><?php esc_html_e('7 days', 'superio'); ?></option>
											</select>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			<?php } else { ?>
				<div class="inner-list">
					<h3 class="title-small"><?php echo esc_html__( 'Your Profile Views', 'superio' ); ?></h3>
					<div class="page_views-wrapper">
						<?php
						$number_days = 14;

						// label
					    $array_labels = array();
						for ($i=$number_days; $i >= 0; $i--) { 
							$date = strtotime(date("Y-m-d", strtotime("-".$i." day")));
							$array_labels[] = date_i18n(get_option('date_format'), $date);
						}

					    // values
					    $views_by_date = get_post_meta( $employer_id, '_views_by_date', true );
					    if ( !is_array( $views_by_date ) ) {
					        $views_by_date = array();
					    }
					    $array_values = array();
						for ($i=$number_days; $i >= 0; $i--) { 
							$date = date("Y-m-d", strtotime("-".$i." day"));
							if ( isset($views_by_date[$date]) ) {
								$array_values[] = $views_by_date[$date];
							} else {
								$array_values[] = 0;
							}
						}

						if ( superio_get_config('main_color') != "" ) {
							$main_color = superio_get_config('main_color');
						} else {
							$main_color = '#1967D2';
						}
						
						?>

						<canvas id="dashboard_chart_wrapper" data-labels="<?php echo esc_attr(json_encode($array_labels)); ?>" data-values="<?php echo esc_attr(json_encode($array_values)); ?>" data-label="<?php esc_attr_e('Views', 'superio'); ?>" data-chart_type="line" data-bg_color="<?php echo esc_attr($main_color); ?>" data-border_color="<?php echo esc_attr($main_color); ?>"></canvas>
					</div>
				</div>
			<?php } ?>
			</div>
			<div class="col-sm-4">
				<div class="inner-list dashboard-notifications <?php echo esc_attr($class_second_column); ?>">
				<h3 class="title-small"><?php echo esc_html__( 'Notifications', 'superio' ); ?></h3>
				<?php
				$notifications = WP_Job_Board_Pro_User_Notification::get_notifications($employer_id, 'employer');
				if ( !empty($notifications) ) {
					// echo "<pre>".print_r($notifications,1); die;
				?>
		            <div class="dashboard-notifications-wrapper">
		                <ul>
		                    <?php foreach ($notifications as $key => $notify) {
		                        $type = !empty($notify['type']) ? $notify['type'] : '';
		                        if ( $type ) {
		                    ?>
		                            <li>
		                            	<span class="icons">
			                            	<?php
			                            	switch ($type) {
												case 'email_apply':
												case 'internal_apply':
												case 'remove_apply':
													?>
													<i class="flaticon-briefcase"></i>
													<?php
													break;
												case 'create_meeting':
												case 'reschedule_meeting':
												case 'remove_meeting':
												case 'cancel_meeting':
													?>
													<i class="flaticon-user"></i>
													<?php
													break;
												case 'reject_applied':
												case 'undo_reject_applied':
												case 'approve_applied':
												case 'undo_approve_applied':
													?>
													<i class="flaticon-briefcase"></i>
													<?php
													break;
												case 'new_private_message':
													?>
													<i class="flaticon-envelope"></i>
													<?php
													break;
												default:
													?>
													<i class="flaticon-envelope"></i>
													<?php
													break;
											}
			                            	?>
		                            	</span>
		                            	<span class="text">
		                            		<div>
				                                <?php echo trim(WP_Job_Board_Pro_User_Notification::display_notify($notify)); ?>
				                            </div>
				                            <small class="time">
			                            		<?php
			                            			$time = $notify['time'];
			                            			echo human_time_diff( $time, current_time( 'timestamp' ) ).' '.esc_html__( 'ago', 'superio' );
			                            		?>
		                            		</small>
			                            </span>
		                            </li>
		                        <?php } ?>
		                    <?php } ?>
		                </ul>      
		            </div>
		        <?php } ?>
			    </div>
		    </div>
		</div>
	<?php } ?>
	<div class="inner-list">
		<h3 class="title-small"><?php esc_html_e('Recent Applicants', 'superio'); ?></h3>
		<div class="applicants">
			<?php
				if ( !empty($job_ids) ) {
					$query_args = array(
						'post_type'         => 'job_applicant',
						'posts_per_page'    => 5,
						'post_status'       => 'publish',
						'meta_query'       => array(
							array(
								'key' => WP_JOB_BOARD_PRO_APPLICANT_PREFIX.'job_id',
								'value'     => $job_ids,
								'compare'   => 'IN',
							),
						)
					);

					$applicants = new WP_Query($query_args);
					
					if ( $applicants->have_posts() ) {
						while ( $applicants->have_posts() ) : $applicants->the_post();
							global $post;
							
		                    $app_status = WP_Job_Board_Pro_Applicant::get_post_meta($post->ID, 'app_status', true);
		                    if ( $app_status == 'rejected' ) {
								echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'content-rejected-applicant' );
							} elseif ( $app_status == 'approved' ) {
								echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'content-approved-applicant' );
							} else {
								echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'content-applicant' );
							}

						endwhile;
						wp_reset_postdata();
					} else {
						?>
						<div class="no-found"><?php esc_html_e('No applicants found.', 'superio'); ?></div>
						<?php
					}
				} else {
					?>
					<div class="no-found"><?php esc_html_e('No applicants found.', 'superio'); ?></div>
					<?php
				}
			?>
		</div>
	</div>
</div>