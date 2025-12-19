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
            'minute' => array(
                'label' => __('Every Minute', 'wp-job-board-pro'),
                'days' => '1', // This can remain as '1' since we only check if it's within the last minute.
            ),
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
				'label' => __('Nedeljno', 'wp-job-board-pro'),
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
		// Flag to ensure that job IDs are logged only once during the processing of email frequencies
		// seting flag for only one pass per job alert 
		// find job_alerts inside posts and make one pass foreach job_alert
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
                    if ($key === 'minute') {
                        $last_sent_time = strtotime(get_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_ALERT_PREFIX . 'send_email_time', true));
                        if ($last_sent_time && ($current_time - $last_sent_time < 60)) {
                            // If the last sent time is less than a minute ago, skip sending
                            continue;
                        }
                        // Set the current time for minute frequency
                        $meta_query[] = array(
                            'key' => WP_JOB_BOARD_PRO_JOB_ALERT_PREFIX . 'send_email_time',
                            'value' => date('Y-m-d H:i:s', $current_time - 60), // One minute ago
                            'compare' => '>=',
                        );
                    } else {
					
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
                    }
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
							
       						// error_log("Post data: " . print_r($post, true)); // Log post data [ID] [post_author] [post_date] [post_date_gmt] etc.
							// error_log("Posts array: " . print_r($job_alerts->posts, true));  result => // [0] => 8907 [1] => 8906 [2] => 1811 [3] => 1810
							

							$author_id = get_post_field('post_author', $post_id);
							$alert_query = get_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_ALERT_PREFIX . 'alert_query', true);
							
							
							$params = $alert_query;
							if ( !empty($alert_query) && !is_array($alert_query) ) {
								$params = json_decode($alert_query, true);
								// error_log("Alert Query: " . $params); // Log the alert query
							}

							// Initialize $current_time before using it
							$current_time = '';

							// error_log("Number of job alerts found: " . count($job_alerts->posts));

							// Check if job alerts are found
							// if (!empty($job_alerts->posts)) {
							// 	foreach ($job_alerts->posts as $alert_id) {
							// 		// Fetch the job alert post object using the ID
							// 		$alert = get_post($alert_id);
							// 		if ($alert) {
							// 			// Log the job alert name
							// 			error_log("Job Alert Name: " . $alert->post_title);
							// 		} else {
							// 			error_log("Job Alert ID: $alert_id not found.");
							// 		}
							// 	}
							// } else {
							// 	error_log("No job alerts found.");
							// }

							$query_args = array(
								'post_type' => 'job_listing',
							    'post_status' => 'publish',
							    'post_per_page' => 1,
							    'fields' => 'ids',
							    'view_user_id' => $author_id
							);
							// error_log("Query Arguments: " . print_r($query_args, true));

							// magia WP_Job_Board_Pro_Query get_posts, using $query_args and $params. 
							// This query retrieves job listings that match the specified criteria from the job alert
							$jobs = WP_Job_Board_Pro_Query::get_posts($query_args, $params);

							// Log the jobs variable
							// error_log("Jobs: " . print_r($jobs, true));
							$count_jobs = $jobs->found_posts;
							$job_alert_title = get_the_title($post_id);
							// send email action
							// admin email address from the WordPress settings - SENDER
							// $email_from = get_option( 'admin_email', false );
							// This line creates the email headers needed for sending an HTML email
							// SENDER (for candidates)
							$headers  = "From: Raspitajse.com - Vaš pouzdan AI model <noreply-candidates@raspitajse.com>\r\n";
							$headers .= "Reply-To: no-reply@raspitajse.com\r\n";
							$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
							
							// creating RECEIVER
							$author_id = get_post_field('post_author', $post_id);
							$email_to = get_the_author_meta('user_email', $author_id);
							// generates the subject of the email

                            // this line retrieves the email frequency setting for a specific job alert post using its $post_id
							$email_frequency = get_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_ALERT_PREFIX.'email_frequency', true);
							if ( !empty($email_frequency_default[$email_frequency]['label']) ) {
								$email_frequency = $email_frequency_default[$email_frequency]['label'];
							}
							// WP_Job_Board_Pro_Mixes class to retrieve the URL of the jobs alert page
							$jobs_alert_url = WP_Job_Board_Pro_Mixes::get_jobs_page_url();
							// check where users can view job alerts
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


							// Log Job IDs only once
							if (!$only_one_pass) {
								foreach ($job_ids as $job_id) {
								// 	error_log("Working with Job ID: " . $job_id);
								// 	error_log("Working with Alert Title: " . $job_alert_title);
									// Additional processing for each job ID
									$job_title = get_the_title($job_id);
									$job_url = get_permalink($job_id);
									$job_publish_date = get_the_date('Y-m-d', $job_id);
									$job_expiry_date = get_post_meta($job_id, '_job_expiry_date', true);
									$job_apply_email = get_post_meta($job_id, '_job_apply_email', true);
									$location = get_post_meta($job_id, '_job_address', true);
									$salary = get_post_meta($job_id, '_job_salary', true);
	
									// error_log("Job Title: " . $job_title);
									// error_log("Job URL: " . $job_url);
									// error_log("Job Publish Date: " . $job_publish_date);
									// error_log("Job Expiry Date: " . $job_expiry_date);
									// error_log("Job Apply Email: " . $job_apply_email);
									// error_log("Location: " . $location);
									// error_log("Salary: " . $salary);
						
									// Get employer name
									// error_log("All Meta Data for Candidate ID $job_id: " . print_r(get_post_meta($job_id), true));
									$employer_id = get_post_meta($job_id, '_job_employer_posted_by', true);
									$employer_name = get_the_title($employer_id);
	
									// error_log("Employer Name: " . $employer_name);	
									
									// error_log("Alert Title: " . $job_alert_title);
									// error_log("Jobs alert url: " . $jobs_alert_url);

									// Check if the job_id and alert_title combination is already in the job_listings
									foreach ($job_listings as $job) {
										if ($job['job_id'] === $job_id && $job['alert_title'] === $job_alert_title) {
											$duplicate = true;
											break;
										}
									}

									// If the combination is not found, add the job entry
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

								error_log("Job Listings Filtered: " . print_r($job_listings, true));
								
								// Retrieve the job entry template from options
								$job_entry_template = get_option('job_entry_template');
								// Log the template to the error log
								// error_log("Job Entry Template: " . print_r($job_entry_template, true));

								// Initialize $job_content to store the processed job listings
								$job_content = '';

								if (!empty($job_listings) && is_array($job_listings)) {
									foreach ($job_listings as $job) {
										// Create a copy of the job entry template to replace placeholders
										$job_entry = $job_entry_template;
										
										// Replace placeholders with actual job data
										$job_entry = str_replace('{{job_title}}', esc_html($job['job_title']), $job_entry);
										$job_entry = str_replace('{{job_url}}', esc_url($job['job_url']), $job_entry);
										$job_entry = str_replace('{{employer_name}}', esc_html($job['employer_name']), $job_entry);
										$job_entry = str_replace('{{location}}', esc_html($job['location']), $job_entry);
										$job_entry = str_replace('{{job_publish_date}}', esc_html($job['job_publish_date']), $job_entry);
										$job_entry = str_replace('{{job_expiry_date}}', esc_html($job['job_expiry_date']), $job_entry);
										$job_entry = str_replace('{{job_apply_email}}', esc_html($job['job_apply_email']), $job_entry);
										$job_entry = str_replace('{{salary}}', esc_html($job['salary']), $job_entry);
										
										// Append the processed job entry to $job_content
										$job_content .= $job_entry . "\n";
									}
								}
									
								// Log the $job_content to check if it's populated correctly
								// error_log("Job Content: " . print_r($job_content, true));

								// error_log("Job Listings: " . print_r($job_listings, true));
								// After finding the newest job in your foreach loop
								// error_log("Newest Job Details: " . print_r($newest_job, true));

								// foreach ($job_listings as $job) {
								// 	error_log("Job ID iz listinga: " . $job['job_id']);
								// 	error_log("Alert title iz listinga: " . $job['alert_title']);
								
								// 	$only_one_ever = true; // Set flag to true to avoid repeating
								// }
								
								$only_one_ever = true; // Set flag to true to avoid repeating emails sending twice per job_alert
								
                                // Log the contents of job_listings for debugging
                                // error_log(print_r($job_listings, true));

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
								
								// error_log("Content Args: " . print_r($content_args, true));
								
								$only_one_pass = true; // Set flag to true to avoid repeating
							}
				
					        // generates the subject of the email
							$subject = WP_Job_Board_Pro_Email::render_email_vars(
								array(
									'alert_title' => $job_alert_title,
									'location' => $location, // Add the location data here
									'job_title' => $job_title    // Add the job title data here
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
					} // else { Handle the case where the job ID is not found | log an error or send a default message }
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