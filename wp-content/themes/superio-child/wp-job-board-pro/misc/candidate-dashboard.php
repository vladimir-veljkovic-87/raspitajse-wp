<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$candidate_ids = apply_filters( 'wp-job-board-pro-translations-post-ids', $candidate_id );
if ( !is_array($candidate_ids) ) {
	$candidate_ids = array($candidate_ids);
}

$applicants = WP_Job_Board_Pro_Query::get_posts(array(
    'post_type' => 'job_applicant',
    'post_status' => 'publish',
    'fields' => 'ids',
    'meta_query' => array(
    	array(
	    	'key' => WP_JOB_BOARD_PRO_APPLICANT_PREFIX . 'candidate_id',
	    	'value' => $candidate_ids,
	    	'compare' => 'IN',
	    )
    )
));
$count_applicants = $applicants->post_count;

$shortlist = get_post_meta($candidate_id, WP_JOB_BOARD_PRO_CANDIDATE_PREFIX.'shortlist', true);
$shortlist = is_array($shortlist) ? count($shortlist) : 0;
$total_reviews = WP_Job_Board_Pro_Review::get_total_reviews($candidate_id);
$views = get_post_meta($candidate_id, WP_JOB_BOARD_PRO_CANDIDATE_PREFIX.'views_count', true);
?>

<div class="employer-dashboard-wrapper">
	<h3 class="title"><?php esc_html_e('Statistika prijava', 'wp-job-board-pro'); ?></h3>
	<div class="statistics">
		<div class="posted-jobs">
			<h4><?php esc_html_e('Prijavljeni poslovi', 'wp-job-board-pro'); ?></h4>
			<div class="jobs-count"><?php echo WP_Job_Board_Pro_Mixes::format_number($count_applicants); ?></div>
		</div>
		<div class="shortlist">
			<h4><?php esc_html_e('U užem izboru', 'wp-job-board-pro'); ?></h4>
			<div class="jobs-count"><?php echo WP_Job_Board_Pro_Mixes::format_number($shortlist); ?></div>
		</div>
		<div class="review-count-wrapper">
			<h4><?php esc_html_e('Recenzije', 'wp-job-board-pro'); ?></h4>
			<div class="review-count"><?php echo WP_Job_Board_Pro_Mixes::format_number($total_reviews); ?></div>
		</div>
		<div class="views-count-wrapper">
			<h4><?php esc_html_e('Pregledi', 'wp-job-board-pro'); ?></h4>
			<div class="views-count"><?php echo WP_Job_Board_Pro_Mixes::format_number($views); ?></div>
		</div>
	</div>

	<h3 class="title"><?php esc_html_e('Nedavno prijavljeni poslovi', 'wp-job-board-pro'); ?></h3>
	<div class="applicants">
		<?php
			$job_ids = array();
			if ( !empty($applicants) && !empty($applicants->posts) ) {
				foreach ($applicants->posts as $applicant_id) {
					$job_ids[] = intval(get_post_meta($applicant_id, WP_JOB_BOARD_PRO_APPLICANT_PREFIX.'job_id', true));
				}
			}
			if ( !empty($job_ids) ) {
				$query_args = array(
					'post_type'         => 'job_listing',
					'posts_per_page'    => 5,
					'post_status'       => 'publish',
					'post__in'       => $job_ids,
				);
				
				$jobs = new WP_Query($query_args);
				if ( $jobs->have_posts() ) {
					while ( $jobs->have_posts() ) : $jobs->the_post();
						echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'jobs-styles/inner-list' );
					endwhile;
					wp_reset_postdata();
				} else {
					?>
					<div class="not-found"><?php esc_html_e('Nema pronađenih prijava.', 'wp-job-board-pro'); ?></div>
					<?php
				}
			} else {
				?>
				<div class="not-found"><?php esc_html_e('Nema pronađenih prijava.', 'wp-job-board-pro'); ?></div>
				<?php
			}
		?>
	</div>
</div>
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$candidate_ids = apply_filters( 'wp-job-board-pro-translations-post-ids', $candidate_id );
if ( !is_array($candidate_ids) ) {
	$candidate_ids = array($candidate_ids);
}

$applicants = WP_Job_Board_Pro_Query::get_posts(array(
    'post_type' => 'job_applicant',
    'post_status' => 'publish',
    'fields' => 'ids',
    'meta_query' => array(
    	array(
	    	'key' => WP_JOB_BOARD_PRO_APPLICANT_PREFIX . 'candidate_id',
	    	'value' => $candidate_ids,
	    	'compare' => 'IN',
	    )
    )
));
$count_applicants = $applicants->post_count;

$shortlist = get_post_meta($candidate_id, WP_JOB_BOARD_PRO_CANDIDATE_PREFIX.'shortlist', true);
$shortlist = is_array($shortlist) ? count($shortlist) : 0;
$total_reviews = WP_Job_Board_Pro_Review::get_total_reviews($candidate_id);
$views = get_post_meta($candidate_id, WP_JOB_BOARD_PRO_CANDIDATE_PREFIX.'views_count', true);
?>

<div class="employer-dashboard-wrapper">
	<h3 class="title"><?php esc_html_e('Applications statistics', 'wp-job-board-pro'); ?></h3>
	<div class="statistics">
		<div class="posted-jobs">
			<h4><?php esc_html_e('Applied Jobs', 'wp-job-board-pro'); ?></h4>
			<div class="jobs-count"><?php echo WP_Job_Board_Pro_Mixes::format_number($count_applicants); ?></div>
		</div>
		<div class="shortlist">
			<h4><?php esc_html_e('Shortlisted', 'wp-job-board-pro'); ?></h4>
			<div class="jobs-count"><?php echo WP_Job_Board_Pro_Mixes::format_number($shortlist); ?></div>
		</div>
		<div class="review-count-wrapper">
			<h4><?php esc_html_e('Review', 'wp-job-board-pro'); ?></h4>
			<div class="review-count"><?php echo WP_Job_Board_Pro_Mixes::format_number($total_reviews); ?></div>
		</div>
		<div class="views-count-wrapper">
			<h4><?php esc_html_e('Views', 'wp-job-board-pro'); ?></h4>
			<div class="views-count"><?php echo WP_Job_Board_Pro_Mixes::format_number($views); ?></div>
		</div>
	</div>

	<h3 class="title"><?php esc_html_e('Jobs Applied Recently', 'wp-job-board-pro'); ?></h3>
	<div class="applicants">
		<?php
			$job_ids = array();
			if ( !empty($applicants) && !empty($applicants->posts) ) {
				foreach ($applicants->posts as $applicant_id) {
					$job_ids[] = intval(get_post_meta($applicant_id, WP_JOB_BOARD_PRO_APPLICANT_PREFIX.'job_id', true));
				}
			}
			if ( !empty($job_ids) ) {
				$query_args = array(
					'post_type'         => 'job_listing',
					'posts_per_page'    => 5,
					'post_status'       => 'publish',
					'post__in'       => $job_ids,
				);
				
				$jobs = new WP_Query($query_args);
				if ( $jobs->have_posts() ) {
					while ( $jobs->have_posts() ) : $jobs->the_post();
						echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'jobs-styles/inner-list' );
					endwhile;
					wp_reset_postdata();
				} else {
					?>
					<div class="not-found"><?php esc_html_e('No Applicants found.', 'wp-job-board-pro'); ?></div>
					<?php
				}
			} else {
				?>
				<div class="not-found"><?php esc_html_e('No Applicants found.', 'wp-job-board-pro'); ?></div>
				<?php
			}
		?>
	</div>
</div>
