<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


$applicants = WP_Job_Board_Pro_Query::get_posts(array(
    'post_type' => 'job_applicant',
    'post_status' => 'publish',
    'fields' => 'ids',
    'meta_query' => array(
    	array(
	    	'key' => WP_JOB_BOARD_PRO_APPLICANT_PREFIX . 'candidate_id',
	    	'value' => $candidate_id,
	    	'compare' => '=',
	    )
    )
));
$count_applicants = $applicants->post_count;

$shortlist_job_ids = get_post_meta($candidate_id, WP_JOB_BOARD_PRO_CANDIDATE_PREFIX.'shortlist', true);
$shortlist = 0;
if ( !empty($shortlist_job_ids) && is_array($shortlist_job_ids) ) {
	$query_vars = array(
		'post_type'         => 'job_listing',
		'posts_per_page'    => 1,
		'post_status'       => array('publish', 'expired'),
		'post__in'       	=> $shortlist_job_ids,
		'fields'       	=> 'ids',
	);

	$shortlist_jobs = new WP_Query($query_vars);

	$shortlist = $shortlist_jobs->found_posts;
}

$total_reviews = WP_Job_Board_Pro_Review::get_total_reviews($candidate_id);
$views = get_post_meta($candidate_id, WP_JOB_BOARD_PRO_CANDIDATE_PREFIX.'views_count', true);
?>

<div class="box-dashboard-wrapper">
	<h3 class="title"><?php esc_html_e('Applications statistics', 'superio'); ?></h3>
	<div class="inner-list bg-transparent no-padding">
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
						<div class="number-count"><?php echo esc_html( $count_applicants ? WP_Job_Board_Pro_Mixes::format_number($count_applicants) : 0); ?></div>
						<span><?php esc_html_e('Applied Jobs', 'superio'); ?></span>
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
				<div class="views-count-wrapper list-item flex-middle justify-content-between text-right">
					<div class="icon-wrapper">
					<div class="icon">
						<i class="flaticon-view"></i>
					</div>
					</div>
					<div class="inner">
						<div class="number-count"><?php echo esc_html( $views ? WP_Job_Board_Pro_Mixes::format_number($views) : 0 ); ?></div>
						<span><?php esc_html_e('Views', 'superio'); ?></span>
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
						<div class="number-count"><?php echo esc_html( $shortlist ? WP_Job_Board_Pro_Mixes::format_number($shortlist) : 0 ); ?></div>
						<span><?php esc_html_e('Shortlisted', 'superio'); ?></span>
					</div>
				</div>
				</div>
			</div>
		</div>
	</div>
	<?php if ( version_compare(WP_JOB_BOARD_PRO_PLUGIN_VERSION, '1.1.6', '>') ) {
		wp_enqueue_script( 'chart', get_template_directory_uri() . '/js/chart.min.js', array( 'jquery' ), '1.0.0', true );
	?>
		<div class="row">
			<div class="col-sm-8">
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
					    $views_by_date = get_post_meta( $candidate_id, '_views_by_date', true );
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
			</div>
			<div class="col-sm-4">
				<div class="inner-list dashboard-notifications">
				<h3 class="title-small"><?php echo esc_html__( 'Notifications', 'superio' ); ?></h3>
				<?php
				$notifications = WP_Job_Board_Pro_User_Notification::get_notifications($candidate_id, 'candidate');
				if ( !empty($notifications) ) { ?>
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
		<h3 class="title-small"><?php esc_html_e('Jobs Applied Recently', 'superio'); ?></h3>
		<div class="applicants">
			<?php
				$job_ids = array();
				$job_applications = array();
				if ( !empty($applicants) && !empty($applicants->posts) ) {
					foreach ($applicants->posts as $applicant_id) {
						$job_ids[] = intval(get_post_meta($applicant_id, WP_JOB_BOARD_PRO_APPLICANT_PREFIX.'job_id', true));
						$job_applications[intval(get_post_meta($applicant_id, WP_JOB_BOARD_PRO_APPLICANT_PREFIX.'job_id', true))] = $applicant_id;
					}
				}
				if ( !empty($job_ids) ) {
					$query_args = array(
						'post_type'         => 'job_listing',
						'posts_per_page'    => 5,
						'post_status'       => 'publish',
						'post__in'       => $job_ids,
					);

					$job_loop = new WP_Query($query_args);
					
					if ( $job_loop->have_posts() ) {
						while ( $job_loop->have_posts() ) : $job_loop->the_post();
							global $post;
							$applicant_id = !empty($job_applications[$post->ID]) ? $job_applications[$post->ID] : 0;
							
	                        $status_label = '';
	                        $app_status = WP_Job_Board_Pro_Applicant::get_post_meta($applicant_id, 'app_status', true);
	                        if ( $app_status == 'rejected' ) {
								$status_label = '<span class="label label-default rejected">'.esc_html__('Rejected', 'superio').'</span>';
							} elseif ( $app_status == 'approved' ) {
								$status_label = '<span class="label label-success approved">'.esc_html__('Approved', 'superio').'</span>';
							} else {
								$status_label = '<span class="label label-info pending">'.esc_html__('Pending', 'superio').'</span>';
							}
							echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'jobs-styles/inner-list', array('status_label' => $status_label) );
						endwhile;
						wp_reset_postdata();
					} else {
						?>
						<div class=""><?php esc_html_e('No Applicants found.', 'superio'); ?></div>
						<?php
					}
				} else {
					?>
					<div class=""><?php esc_html_e('No Applicants found.', 'superio'); ?></div>
					<?php
				}
			?>
		</div>
	</div>
</div>