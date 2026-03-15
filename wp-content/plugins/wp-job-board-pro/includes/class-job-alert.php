<?php
/**
 * Job Alert
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_Job_Alert {
	public static function init() {
		add_action( 'wp_job_board_pro_email_daily_notices', array( __CLASS__, 'send_job_alert_notice' ) );
		// Ajax endpoints.
		add_action( 'wjbp_ajax_wp_job_board_pro_ajax_add_job_alert',  array(__CLASS__,'process_add_job_alert') );

		add_action( 'wjbp_ajax_wp_job_board_pro_ajax_remove_job_alert',  array(__CLASS__,'process_remove_job_alert') );


		// compatible handlers.
		add_action( 'wp_ajax_wp_job_board_pro_ajax_add_job_alert',  array(__CLASS__,'process_add_job_alert') );
		add_action( 'wp_ajax_nopriv_wp_job_board_pro_ajax_add_job_alert',  array(__CLASS__,'process_add_job_alert') );

		add_action( 'wp_ajax_wp_job_board_pro_ajax_remove_job_alert',  array(__CLASS__,'process_remove_job_alert') );
		add_action( 'wp_ajax_nopriv_wp_job_board_pro_ajax_remove_job_alert',  array(__CLASS__,'process_remove_job_alert') );
	}

	public static function get_email_frequency() {
		$email_frequency = apply_filters( 'wp-job-board-pro-job-alert-email-frequency', array(
			'daily' => array(
				'label' => __('Dnevno', 'wp-job-board-pro'),
				'days' => '1',
			),
			'weekly' => array(
				'label' => __('Nedeljno', 'wp-job-board-pro'),
				'days' => '7',
			),
			'fortnightly' => array(
				'label' => __('Dvonedeljno', 'wp-job-board-pro'),
				'days' => '15',
			),
			'monthly' => array(
				'label' => __('Mesečno', 'wp-job-board-pro'),
				'days' => '30',
			),
			'biannually' => array(
				'label' => __('Polugodišnje', 'wp-job-board-pro'),
				'days' => '182',
			),
			'annually' => array(
				'label' => __('Godišnje', 'wp-job-board-pro'),
				'days' => '365',
			),
		));
		return $email_frequency;
	}

	public static function send_job_alert_notice() {
		
		$email_frequency_default = self::get_email_frequency();
		$only_one_ever = false;

		if ( $email_frequency_default ) {
			foreach ($email_frequency_default as $key => $value) {
				if ( !empty($value['days']) ) {
					$meta_query = array(
						'relation' => 'OR',
						array(
							'key' => WP_JOB_BOARD_PRO_JOB_ALERT_PREFIX.'send_email_time',
							'compare' => 'NOT EXISTS',
						)
					);
				
					$current_time = apply_filters( 'wp-job-board-pro-job-alert-current-'.$key.'-time', date( 'Y-m-d', strtotime( '-'.intval($value['days']).' days', current_time( 'timestamp' ) ) ) );
					$meta_query[] = array(
						'relation' => 'AND',
						array(
							'key' => WP_JOB_BOARD_PRO_JOB_ALERT_PREFIX.'send_email_time',
							'value' => $current_time,
							'compare' => '<=',
						),
						array(
							'key' => WP_JOB_BOARD_PRO_JOB_ALERT_PREFIX.'email_frequency',
							'value' => $key,
							'compare' => '=',
						),
					);
                    
					$query_args = apply_filters( 'wp-job-board-pro-job-alert-query-args', array(
						'post_type' => 'job_alert',
						'post_per_page' => -1,
						'post_status' => 'publish',
						'fields' => 'ids',
						'meta_query' => $meta_query
					));

					$job_alerts = new WP_Query($query_args);
					$duplicate = false;
					// doing it all once foreach $job_alerts in posts - $job_alerts->posts as $post_id) {  
					$only_one_pass = false;
							
					if ( !empty($job_alerts->posts) ) {
						foreach ($job_alerts->posts as $post_id) {
							$post = get_post($post_id);					

							$author_id = get_post_field('post_author', $post_id);
							$alert_query = get_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_ALERT_PREFIX . 'alert_query', true);					
							
							$params = $alert_query;
							if ( !empty($alert_query) && !is_array($alert_query) ) {
								$params = json_decode($alert_query, true);
							}

							$query_args = array(
								'post_type' => 'job_listing',
							    'post_status' => 'publish',
							    'post_per_page' => 1,
							    'fields' => 'ids',
							    'view_user_id' => $author_id
							);

							$jobs = WP_Job_Board_Pro_Query::get_posts($query_args, $params);

							$count_jobs = $jobs->found_posts;
							$job_alert_title = get_the_title($post_id);

							$headers  = "From: Raspitajse.com - Vaš pouzdan AI model <noreply-candidates@raspitajse.com>\r\n";
							$headers .= "Reply-To: no-reply@raspitajse.com\r\n";
							$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

							$author_id = get_post_field('post_author', $post_id);
							$email_to = get_the_author_meta('user_email', $author_id);

							$email_frequency = get_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_ALERT_PREFIX.'email_frequency', true);
							if ( !empty($email_frequency_default[$email_frequency]['label']) ) {
								$email_frequency = $email_frequency_default[$email_frequency]['label'];
							}

							$jobs_alert_url = WP_Job_Board_Pro_Mixes::get_jobs_page_url();
							if ( !empty($params) ) {
								foreach ($params as $key => $value) {
									// error_log("Key: $key; Value: " . print_r($value, true));
									if ( is_array($value) ) {
										$jobs_alert_url = remove_query_arg( $key.'[]', $jobs_alert_url );
										foreach ($value as $val) {
											$jobs_alert_url = add_query_arg( $key.'[]', $val, $jobs_alert_url );
										}
									} else {
										$jobs_alert_url = add_query_arg( $key, $value, remove_query_arg( $key, $jobs_alert_url ) );
									}
								}
							}

							// Initialize $job_id here
							$job_id = ''; 

							// geting posts from $jobs object
							$job_ids = $jobs->posts;

							// Initialize job data placeholders
							$job_title = $job_url = $job_publish_date = $job_expiry_date = $location = $salary = $employer_name = $job_apply_email = '';
							// Initialize the job_listings array
							$job_listings = [];

							if (!$only_one_pass) {
								foreach ($job_ids as $job_id) {
									$job_title = get_the_title($job_id);
									$job_url = get_permalink($job_id);
									$job_publish_date = get_the_date('Y-m-d', $job_id);
									$job_expiry_date = get_post_meta($job_id, '_job_expiry_date', true);
									$job_apply_email = get_post_meta($job_id, '_job_apply_email', true);
									$location = get_post_meta($job_id, '_job_address', true);
									$salary = get_post_meta($job_id, '_job_salary', true);
	
									$employer_id = get_post_meta($job_id, '_job_employer_posted_by', true);
									$employer_name = get_the_title($employer_id);

									foreach ($job_listings as $job) {
										if ($job['job_id'] === $job_id && $job['alert_title'] === $job_alert_title) {
											$duplicate = true;
											break;
										}
									}

									if (!$duplicate) {
										$job_listings[] = [
											'job_id' => $job_id,
											'job_title' => $job_title,
											'job_url' => $job_url,
											'job_publish_date' => $job_publish_date,
											'job_expiry_date' => $job_expiry_date,
											'job_apply_email' => $job_apply_email,
											'location' => $location,
											'salary' => $salary,
											'employer_name' => $employer_name,
											'alert_title' => $job_alert_title,
											'jobs_alert_url' => $jobs_alert_url,
										];
									}		
								}
								
								// Assuming $job_listings is your full array of job listings
                                $job_listings = array_slice($job_listings, 0, 5);
								
								// Log the $job_content to check if it's populated correctly
								// error_log("Job Listings: " . print_r($job_listings, true));

								$newest_job = []; // initialize $newest_job
								$newest_date = null; // Initialize the date for comparison
								$newest_job_key = null;

								// Iterate through each job listing
								foreach ($job_listings as $key => $job) {
									$publish_date = $job['job_publish_date'];
									
									// Check if the current job's publish date is newer
									if ($newest_date === null || $publish_date > $newest_date) {
										$newest_date = $publish_date;
										$newest_job = $job;
										$newest_job_key = $key; // Store the key of the newest job
									}
								}
								
								// Exclude the newest job from the job listings using array_diff_key
								if ($newest_job_key !== null) {
									$job_listings = array_diff_key($job_listings, [$newest_job_key => $job_listings[$newest_job_key]]);
								}

								$job_entry_template = get_option('job_entry_template');

								$job_content = '';

								if (!empty($job_listings) && is_array($job_listings)) {
									foreach ($job_listings as $job) {
										$job_entry = $job_entry_template;
										$job_entry = str_replace('{{job_title}}', esc_html($job['job_title']), $job_entry);
										$job_entry = str_replace('{{job_url}}', esc_url($job['job_url']), $job_entry);
										$job_entry = str_replace('{{employer_name}}', esc_html($job['employer_name']), $job_entry);
										$job_entry = str_replace('{{location}}', esc_html($job['location']), $job_entry);
										$job_entry = str_replace('{{job_publish_date}}', esc_html($job['job_publish_date']), $job_entry);
										$job_entry = str_replace('{{job_expiry_date}}', esc_html($job['job_expiry_date']), $job_entry);
										$job_entry = str_replace('{{job_apply_email}}', esc_html($job['job_apply_email']), $job_entry);
										$job_entry = str_replace('{{salary}}', esc_html($job['salary']), $job_entry);
										$job_content .= $job_entry . "\n";
									}
								}

								$only_one_ever = true; 

								// Prepare content arguments
								$content_args = apply_filters('wp-job-board-pro-job-alert-email-content-args', array(
									'job_data' => $job_content, // Add all job listings data here
									// newest job data
									'newest_employer_name' => $newest_job['employer_name'],
									'newest_job_title' => $newest_job['job_title'],
									'newest_job_url' => $newest_job['job_url'],
									'newest_job_publish_date' => $newest_job['job_publish_date'],
									'newest_job_expiry_date' => $newest_job['job_expiry_date'],
									'newest_job_apply_email' => $newest_job['job_apply_email'],
									'newest_location' => $newest_job['location'],
									'newest_salary' => $newest_job['salary'],
									'newest_alert_title' => $newest_job['alert_title'],
									'newest_jobs_alert_url' => $newest_job['jobs_alert_url']
								));
								
								$only_one_pass = true;
							}
				
							$subject = WP_Job_Board_Pro_Email::render_email_vars(
								array(
									'alert_title' => $job_alert_title,
									'location' => $location, 
									'job_title' => $job_title  
								), 
								'job_alert_notice', 
								'subject'
							);

							// Now you can use $content_args to generate your email content
							$content = WP_Job_Board_Pro_Email::render_email_vars($content_args, 'job_alert_notice', 'content');

							// calls a static method, wp_mail, from the WP_Job_Board_Pro_Email class. 
							// It sends an email to $email_to with the specified $subject, $content, and $headers			
							WP_Job_Board_Pro_Email::wp_mail( $email_to, $subject, $content, $headers );
							// this line removes any existing metadata
							$current_time = date( 'Y-m-d', current_time( 'timestamp' ) );
							// this line removes any existing metadata
							delete_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_ALERT_PREFIX.'send_email_time');
							// this line adds the new send_email_time
							add_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_ALERT_PREFIX.'send_email_time', $current_time);
						}
					} 
				}
			}
		}
		
	}

	public static function process_add_job_alert() {
		$return = array();
		
		if ( !is_user_logged_in() || !WP_Job_Board_Pro_User::is_candidate() ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Prijavite se kao „Kandidat“ da biste dodali obaveštenje o poslovima.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		$user_id = WP_Job_Board_Pro_User::get_user_id();
		$candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);
		
		 // Added check if the user already has two job alerts 
		$existing_alerts = new WP_Query(array(
			'post_type' => 'job_alert',
			'meta_query' => array(
				array(
					'key' => WP_JOB_BOARD_PRO_JOB_ALERT_PREFIX . 'candidate_id',
					'value' => $candidate_id,
					'compare' => '='
				)
			),
			'posts_per_page' => 1
		));

		if ( $existing_alerts->found_posts >= 1 ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Možete imati najviše jedno obaveštenje o poslovima.', 'wp-job-board-pro') );
			echo wp_json_encode($return);
			exit;
		}

		$errors = self::validate_add_job_alert();
		if ( !empty($errors) && sizeof($errors) > 0 ) {
			$return = array( 'status' => false, 'msg' => implode(', ', $errors) );
		   	echo wp_json_encode($return);
		   	exit;
		}
		$name = !empty($_POST['name']) ? $_POST['name'] : '';
		
		$post_args = array(
            'post_title' => $name,
            'post_type' => 'job_alert',
            'post_content' => '',
            'post_status' => 'publish',
            'user_id' => $user_id
        );
		$post_args = apply_filters('wp-job-board-pro-add-job-alert-data', $post_args);
		
		do_action('wp-job-board-pro-before-add-job-alert');

        // Insert the post into the database
        $alert_id = wp_insert_post($post_args);
        if ( $alert_id ) {
	        update_post_meta($alert_id, WP_JOB_BOARD_PRO_JOB_ALERT_PREFIX . 'candidate_id', $candidate_id);
	        $email_frequency = !empty($_POST['email_frequency']) ? $_POST['email_frequency'] : '';
	        update_post_meta($alert_id, WP_JOB_BOARD_PRO_JOB_ALERT_PREFIX . 'email_frequency', $email_frequency);

	        $alert_query = array();
			if ( ! empty( $_POST ) && is_array( $_POST ) ) {
				foreach ( $_POST as $key => $value ) {
					if ( strrpos( $key, 'filter-', -strlen( $key ) ) !== false ) {
						$alert_query[$key] = $value;
					}
				}
			}
	        if ( !empty($alert_query) ) {
	        	// $alert_query = json_encode($alert_query);
	        	update_post_meta($alert_id, WP_JOB_BOARD_PRO_JOB_ALERT_PREFIX . 'alert_query', $alert_query);	
	        }
	        
	        do_action('wp-job-board-pro-after-add-job-alert', $alert_id);

	        $return = array( 'status' => true, 'msg' => esc_html__('Obaveštenje o poslovima je uspešno dodato.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
	    } else {
			$return = array( 'status' => false, 'msg' => esc_html__('Greška pri dodavanju obaveštenja o poslovima.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
	}

	public static function validate_add_job_alert() {
		$name = !empty($_POST['name']) ? $_POST['name'] : '';
		if ( empty($name) ) {
			$return[] = esc_html__('Naziv je obavezan.', 'wp-job-board-pro');
		}
		$email_frequency = !empty($_POST['email_frequency']) ? $_POST['email_frequency'] : '';
		if ( empty($email_frequency) ) {
			$return[] = esc_html__('Učestalost e-pošte je obavezna.', 'wp-job-board-pro');
		}
		return $return;
	}

	public static function process_remove_job_alert() {
		$return = array();
		
		if ( !is_user_logged_in() ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Prijavite se da biste uklonili obaveštenje o poslovima.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		$alert_id = !empty($_POST['alert_id']) ? $_POST['alert_id'] : '';

		if ( empty($alert_id) ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Odgovarajući poslovi ne postoje prema kriterijumima koje ste zadali.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		$user_id = WP_Job_Board_Pro_User::get_user_id();
		$is_allowed = WP_Job_Board_Pro_Mixes::is_allowed_to_remove( $user_id, $alert_id );

		if ( ! $is_allowed ) {
	        $return = array( 'status' => false, 'msg' => esc_html__('Ne možete ukloniti ovo obaveštenje o poslovima.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}

		do_action('wp-job-board-pro-before-remove-job-alert', $alert_id);

		if ( wp_delete_post( $alert_id ) ) {
	        $return = array( 'status' => true, 'msg' => esc_html__('Obaveštenje o poslovima je uspešno uklonjeno.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
	    } else {
			$return = array( 'status' => false, 'msg' => esc_html__('Greška pri uklanjanju obaveštenja o poslovima.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
	}
}

WP_Job_Board_Pro_Job_Alert::init();