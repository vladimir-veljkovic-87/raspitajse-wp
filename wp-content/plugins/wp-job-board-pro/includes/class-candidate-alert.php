<?php
/**
 * Candidate Alert
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_Candidate_Alert {
	public static function init() {
		add_action( 'wp_job_board_pro_email_daily_notices', array( __CLASS__, 'send_candidate_alert_notice' ) );

		// Ajax endpoints.
		add_action( 'wjbp_ajax_wp_job_board_pro_ajax_add_candidate_alert',  array(__CLASS__,'process_add_candidate_alert') );
		add_action( 'wjbp_ajax_wp_job_board_pro_ajax_remove_candidate_alert',  array(__CLASS__,'process_remove_candidate_alert') );

		// compatible handlers.
		add_action( 'wp_ajax_wp_job_board_pro_ajax_add_candidate_alert',  array(__CLASS__,'process_add_candidate_alert') );
		add_action( 'wp_ajax_nopriv_wp_job_board_pro_ajax_add_candidate_alert',  array(__CLASS__,'process_add_candidate_alert') );

		add_action( 'wp_ajax_wp_job_board_pro_ajax_remove_candidate_alert',  array(__CLASS__,'process_remove_candidate_alert') );
		add_action( 'wp_ajax_nopriv_wp_job_board_pro_ajax_remove_candidate_alert',  array(__CLASS__,'process_remove_candidate_alert') );
	}

	public static function get_email_frequency() {
		$email_frequency = apply_filters( 'wp-job-board-pro-candidate-alert-email-frequency', array(
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
				'label' => __('Dva puta godišnje', 'wp-job-board-pro'),
				'days' => '182',
			),
			'annually' => array(
				'label' => __('Godišnje', 'wp-job-board-pro'),
				'days' => '365',
			),
		));
		return $email_frequency;
	}

	public static function send_candidate_alert_notice() {
		$email_frequency_default = self::get_email_frequency();
        // Flag to ensure that job IDs are logged only once during the processing of email frequencies
		// seting flag for only one pass per job alert 
		// find candidate inside posts and make one pass foreach candidate
		$only_one_ever = false;

		if ( $email_frequency_default ) {
			foreach ($email_frequency_default as $key => $value) {
				if ( !empty($value['days']) ) {
					$meta_query = array(
						'relation' => 'OR',
						array(
							'key' => WP_JOB_BOARD_PRO_CANDIDATE_ALERT_PREFIX.'send_email_time',
							'compare' => 'NOT EXISTS',
						)
					);
                    if ($key === 'minute') {
                        $last_sent_time = strtotime(get_post_meta($post_id, WP_JOB_BOARD_PRO_CANDIDATE_ALERT_PREFIX . 'send_email_time', true));
                        if ($last_sent_time && ($current_time - $last_sent_time < 60)) {
                            // If the last sent time is less than a minute ago, skip sending
                            continue;
                        }
                        // Set the current time for minute frequency
                        $meta_query[] = array(
                            'key' => WP_JOB_BOARD_PRO_CANDIDATE_ALERT_PREFIX . 'send_email_time',
                            'value' => date('Y-m-d H:i:s', $current_time - 60), // One minute ago
                            'compare' => '>=',
                        );
                    } else {
                    
                        $current_time = apply_filters( 'wp-job-board-pro-candidate-alert-current-'.$key.'-time', date( 'Y-m-d', strtotime( '-'.intval($value['days']).' days', current_time( 'timestamp' ) ) ) );
                        $meta_query[] = array(
                            'relation' => 'AND',
                            array(
                                'key' => WP_JOB_BOARD_PRO_CANDIDATE_ALERT_PREFIX.'send_email_time',
                                'value' => $current_time,
                                'compare' => '<=',
                            ),
                            array(
                                'key' => WP_JOB_BOARD_PRO_CANDIDATE_ALERT_PREFIX.'email_frequency',
                                'value' => $key,
                                'compare' => '=',
                            ),
                        );
                    }    

					$query_args = apply_filters( 'wp-job-board-pro-candidate-alert-query-args', array(
						'post_type' => 'candidate_alert',
						'post_per_page' => -1,
						'post_status' => 'publish',
						'fields' => 'ids',
						'meta_query' => $meta_query
					));

					$candidate_alerts = new WP_Query($query_args);
                    $duplicate = false;
					// doing it all once foreach $job_alerts in posts - $job_alerts->posts as $post_id) {  
					$only_one_pass = false;

					if ( !empty($candidate_alerts->posts) ) {
						foreach ($candidate_alerts->posts as $post_id) {
                            $post = get_post($post_id);

                            // error_log("Post data: " . print_r($post, true)); // Log post data [ID] [post_author] [post_date] [post_date_gmt] etc.

							$author_id = get_post_field('post_author', $post_id);
							$alert_query = get_post_meta($post_id, WP_JOB_BOARD_PRO_CANDIDATE_ALERT_PREFIX . 'alert_query', true);
							
							$params = $alert_query;
							if ( !empty($alert_query) && !is_array($alert_query) ) {
								$params = json_decode($alert_query, true);
							}

							$query_args = array(
								'post_type' => 'candidate',
							    'post_status' => 'publish',
							    'post_per_page' => 1,
							    'fields' => 'ids',
							    'view_user_id' => $author_id
							);
                            // error_log("Query Arguments: " . print_r($query_args, true));

							$candidates = WP_Job_Board_Pro_Query::get_posts($query_args, $params);

                            // Log the jobs variable
							// error_log("Candidates: " . print_r($candidates, true));

							$count_candidates = $candidates->found_posts;
							$candidate_alert_title = get_the_title($post_id);
							// send email action
							//$email_from = get_option( 'admin_email', false );
							
							// SENDER (for candidates)
							$headers  = "From: Raspitajse.com - Vaš pouzdan AI model <noreply-employers@raspitajse.com>\r\n";
							$headers .= "Reply-To: no-reply@raspitajse.com\r\n";
							$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

							
							$author_id = get_post_field( 'post_author', $post_id );
							$email_to = get_the_author_meta('user_email', $author_id);

							

							$email_frequency = get_post_meta($post_id, WP_JOB_BOARD_PRO_CANDIDATE_ALERT_PREFIX.'email_frequency', true);
							if ( !empty($email_frequency_default[$email_frequency]['label']) ) {
								$email_frequency = $email_frequency_default[$email_frequency]['label'];
							}
							$candidates_alert_url = WP_Job_Board_Pro_Mixes::get_candidates_page_url();
							if ( !empty($params) ) {
								foreach ($params as $key => $value) {
									if ( is_array($value) ) {
										$candidates_alert_url = remove_query_arg( $key.'[]', $candidates_alert_url );
										foreach ($value as $val) {
											$candidates_alert_url = add_query_arg( $key.'[]', $val, $candidates_alert_url );
										}
									} else {
										$candidates_alert_url = add_query_arg( $key, $value, remove_query_arg( $key, $candidates_alert_url ) );
									}
								}
							}

							// Initialize $candidate_id here
							$candidate_id = ''; 

							// geting posts from $jobs object
							$candidate_ids = $candidates->posts;
	                        // error_log("Candidate IDS " . print_r($candidate_ids, true));

							$candidate_ids = $candidates->posts;
							$candidate_title = $candidate_url = $candidate_publish_date = $candidate_expiry_date = $location = $salary = $employer_name = $candidate_apply_email = '';
							
							$candidate_listings = [];
							
							if (!$only_one_pass) {
								foreach ($candidate_ids as $candidate_id) {
									$candidate_title = get_the_title($candidate_id);
									$candidate_url = get_permalink($candidate_id);
									$candidate_publish_date = get_the_date('Y-m-d', $candidate_id);
									// candidate name is obsolute
									$candidate_name = get_post_meta($candidate_id, '_candidate_display_name', true);
									$candidate_job_title = get_post_meta($candidate_id, '_candidate_job_title', true);
									$candidate_qualification = get_post_meta($candidate_id, '_candidate_qualification', true);
									$candidate_experience_time = get_post_meta($candidate_id, '_candidate_experience_time', true);

									$candidate_birth_date = get_post_meta($candidate_id, '_candidate_founded_date', true);
									$candidate_email = get_post_meta($candidate_id, '_candidate_email', true);
									
									// Retrieve the serialized data
									$map_location_data = get_post_meta($candidate_id, '_candidate_map_location', true);
									// Unserialize the data to convert it into an array
									$map_location = maybe_unserialize($map_location_data);
									// Access the address field
									$location = isset($map_location['address']) ? $map_location['address'] : null;

									$salary = get_post_meta($candidate_id, '_candidate_salary', true);
									$salary_type = get_post_meta($candidate_id, '_candidate_salary_type', true);
							
                                    // error_log("All Meta Data for Candidate ID $candidate_id: " . print_r(get_post_meta($candidate_id), true));
                                    
                                    // error_log("Employer Name: " . $employer_name);	
                            
									$duplicate = false;
									foreach ($candidate_listings as $candidate) {
										if ($candidate['candidate_id'] === $candidate_id && $candidate['alert_title'] === $candidate_alert_title) {
											$duplicate = true;
											break;
										}
									}
							
									if (!$duplicate) {
										$candidate_listings[] = [
											'candidate_id' => $candidate_id,
											'candidate_title' => $candidate_title,
											'candidate_url' => $candidate_url,
											'candidate_publish_date' => $candidate_publish_date,
											'candidate_name' => $candidate_name,
											'candidate_job_title' => $candidate_job_title,
											'candidate_qualification' => $candidate_qualification,
											'candidate_experience_time' => $candidate_experience_time,
											
											'candidate_birth_date' => $candidate_birth_date,
											'candidate_email' => $candidate_email,
											'location' => $location,
											'salary' => $salary,
											'salary_type' => $salary_type,
											
											'alert_title' => $candidate_alert_title,
											'candidates_alert_url' => $candidates_alert_url,
										];
									}
									
								}
								// error_log("Candidate Listings: " . print_r($candidate_listings, true));
								$candidate_listings = array_slice($candidate_listings, 0, 5);

								$best_match_candidate = []; // Initialize $best_match_candidate
								$lowest_salary = PHP_INT_MAX; // Start with the maximum possible value
								$highest_experience = -1; // Start with the minimum possible value
								$best_match_candidate_key = null;

								// Iterate through each candidate listing to find best match
								foreach ($candidate_listings as $key => $candidate) {
									// error_log("Processing candidate with key: " . $key . " Data: " . print_r($candidate, true));
									
									// Exclude candidates without salary or experience
									if (empty($candidate['salary']) || empty($candidate['candidate_experience_time'])) {
										// error_log("Candidate excluded due to missing salary or experience: " . print_r($candidate, true));
										continue; // Skip the current candidate and move to the next one
									}

									// Normalize candidate salary based on salary type (monthly, yearly, hourly, daily)
									$candidate_salary = isset($candidate['salary']) ? (int) $candidate['salary'] : PHP_INT_MAX;
									$candidate_salary_type = isset($candidate['salary_type']) ? $candidate['salary_type'] : 'monthly'; // Default to monthly if not specified
									
									// Normalize salary based on type (convert to monthly salary)
									if ($candidate_salary_type === 'yearly') {
										$candidate_salary = $candidate_salary / 12;  // Convert yearly to monthly
									} elseif ($candidate_salary_type === 'hourly') {
										$candidate_salary = $candidate_salary * 168; // Assuming 168 working hours per month for hourly rates
									} elseif ($candidate_salary_type === 'daily') {
										$candidate_salary = $candidate_salary * 21; // Assuming 21 days in a month for daily rates
									}
									
									// Normalize experience to years if it's not already in years
									$candidate_experience = isset($candidate['candidate_experience_time']) ? $candidate['candidate_experience_time'] : '';
									$normalized_experience = 0;  // Default experience in years
									
									if (strpos($candidate_experience, 'Year') !== false) {
										$normalized_experience = (int) preg_replace('/\D/', '', $candidate_experience); // Extract experience in years
									} elseif (strpos($candidate_experience, 'Month') !== false) {
										$normalized_experience = (int) preg_replace('/\D/', '', $candidate_experience) / 12; // Convert months to years
									}

									// error_log("Candidate salary: " . $candidate_salary . ", Experience: " . $normalized_experience);
									
									// Check if the current candidate is a better match
									if (
										$candidate_salary < $lowest_salary || 
										($candidate_salary === $lowest_salary && $normalized_experience > $highest_experience)
									) {
										$lowest_salary = $candidate_salary;
										$highest_experience = $normalized_experience;
										$best_match_candidate = $candidate;
										$best_match_candidate_key = $key;
									
										// error_log("New best match found with key: " . $best_match_candidate_key);
									} else {
										// error_log("Candidate did not match: Salary: " . $candidate_salary . ", Experience: " . $normalized_experience);
									}
								}

								// if ($best_match_candidate_key === null) {
								// 	error_log("No best match candidate found.");
								// } else {
								// 	error_log("Final best match candidate key: " . $best_match_candidate_key);
								// }

								// Exclude the best match candidate
								if ($best_match_candidate_key !== null) {
									$candidate_listings = array_diff_key($candidate_listings, [$best_match_candidate_key => $candidate_listings[$best_match_candidate_key]]);
									error_log("Remaining candidates after exclusion: " . print_r($candidate_listings, true));
								}
								// At this point, $candidate_listings no longer contains the best match candidate
								// error_log("Candidate Listings Without Best Match: " . print_r($candidate_listings, true));
								
								// Retrieve the candidate entry template from options (functions)
 								$candidate_entry_template = get_option('candidate_entry_template');
								 error_log('Candidate Entry Template: ' . print_r($candidate_entry_template, true));

								// Initialize $candidate_content to store the processed candidate listings
								$candidate_content = '';

								if (!empty($candidate_listings) && is_array($candidate_listings)) {
									foreach ($candidate_listings as $candidate) {
										error_log('Processing Candidate: ' . print_r($candidate, true));
										// Create a copy of the candidate entry template to replace placeholders
										$candidate_entry = $candidate_entry_template;
										
										// Replace placeholders with actual candidate data
										$candidate_entry = str_replace('{{candidate_title}}', esc_html($candidate['candidate_title']), $candidate_entry);
										$candidate_entry = str_replace('{{candidate_url}}', esc_url($candidate['candidate_url']), $candidate_entry);
										$candidate_entry = str_replace('{{candidate_job_title}}', esc_html($candidate['candidate_job_title']), $candidate_entry);
										$candidate_entry = str_replace('{{candidate_qualification}}', esc_html($candidate['candidate_qualification']), $candidate_entry);
										$candidate_entry = str_replace('{{candidate_experience_time}}', esc_html($candidate['candidate_experience_time']), $candidate_entry);
										$candidate_entry = str_replace('{{candidate_birth_date}}', esc_html($candidate['candidate_birth_date']), $candidate_entry);

										$candidate_entry = str_replace('{{candidate_email}}', esc_html($candidate['candidate_email']), $candidate_entry);
										$candidate_entry = str_replace('{{location}}', esc_html($candidate['location']), $candidate_entry);
										$candidate_entry = str_replace('{{salary}}', esc_html($candidate['salary']), $candidate_entry);
										$candidate_entry = str_replace('{{salary_type}}', esc_html($candidate['salary_type']), $candidate_entry);
																			
										// Append the processed candidate entry to $candidate_content
										$candidate_content .= $candidate_entry . "\n";
									}
									error_log('Candidate Content: ' . $candidate_content);
								}

								$only_one_ever = true; // Set flag to true to avoid repeating emails sending twice per job_alert

								// Prepare content arguments
								$content_args = apply_filters('wp-job-board-pro-candidate-alert-email-content-args', array(
									'candidate_data' => $candidate_content, // Add all candidate listings data here
									// best match candidate data
									'best_candidate_title' => $best_match_candidate['candidate_title'],
									'best_candidate_url' => $best_match_candidate['candidate_url'],
									'best_candidate_job_title' => $best_match_candidate['candidate_job_title'],
									'best_candidate_qualification' => $best_match_candidate['candidate_qualification'],
									'best_candidate_experience_time' => $best_match_candidate['candidate_experience_time'],
									'best_candidate_birth_date' => $best_match_candidate['candidate_birth_date'],
									'best_location' => $best_match_candidate['location'],
									'best_alert_title' => $best_match_candidate['candidates_alert_url'],
									'candidates_found' => $count_candidates,
								));
								
								// Log candidate_data
								error_log('Candidate Data: ' . print_r($candidate_content, true));
								error_log("Content Args: " . print_r($content_args, true));
								
								$only_one_pass = true; // Set flag to true to avoid repeating
									

							}
							
                            $subject = WP_Job_Board_Pro_Email::render_email_vars(
                                array(
                                    'alert_title' => $candidate_alert_title,
                                    'location' => $location, // Add the location data here
									'candidate_title' => $candidate_title    // Add the candidate title data here
                                ), 
                                'candidate_alert_notice', 
                                'subject'
                            );

							$content = WP_Job_Board_Pro_Email::render_email_vars($content_args, 'candidate_alert_notice', 'content');
										
							WP_Job_Board_Pro_Email::wp_mail( $email_to, $subject, $content, $headers );
							$current_time = date( 'Y-m-d', current_time( 'timestamp' ) );
							delete_post_meta($post_id, WP_JOB_BOARD_PRO_CANDIDATE_ALERT_PREFIX.'send_email_time');
							add_post_meta($post_id, WP_JOB_BOARD_PRO_CANDIDATE_ALERT_PREFIX.'send_email_time', $current_time);
						}
					}
				}
			}
		}
		
	}

	public static function process_add_candidate_alert() {
		$return = array();
		
		if ( !is_user_logged_in() || !WP_Job_Board_Pro_User::is_employer() ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Prijavite se kao "Poslodavac" da biste dodali obaveštenje o kandidatima.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		$user_id = WP_Job_Board_Pro_User::get_user_id();
		$candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);

		// Added check if the user already has two candidate alerts
		$existing_alerts = new WP_Query(array(
			'post_type' => 'candidate_alert',
			'meta_query' => array(
				array(
					'key' => WP_JOB_BOARD_PRO_CANDIDATE_ALERT_PREFIX . 'candidate_id',
					'value' => $candidate_id,
					'compare' => '='
				)
			),
			'posts_per_page' => 1
		));

		if ( $existing_alerts->found_posts >= 1 ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Možete imati jedno sačuvano obaveštenje o kandidatima.', 'wp-job-board-pro') );
			echo wp_json_encode($return);
			exit;
		}		

		$errors = self::validate_add_candidate_alert();
		if ( !empty($errors) && sizeof($errors) > 0 ) {
			$return = array( 'status' => false, 'msg' => implode(', ', $errors) );
		   	echo wp_json_encode($return);
		   	exit;
		}
		$name = !empty($_POST['name']) ? $_POST['name'] : '';
		
		$post_args = array(
            'post_title' => $name,
            'post_type' => 'candidate_alert',
            'post_content' => '',
            'post_status' => 'publish',
            'user_id' => $user_id
        );
		$post_args = apply_filters('wp-job-board-pro-add-candidate-alert-data', $post_args);
		
		do_action('wp-job-board-pro-before-add-candidate-alert');

        // Insert the post into the database
        $alert_id = wp_insert_post($post_args);
        if ( $alert_id ) {
	        update_post_meta($alert_id, WP_JOB_BOARD_PRO_CANDIDATE_ALERT_PREFIX . 'candidate_id', $candidate_id);
	        $email_frequency = !empty($_POST['email_frequency']) ? $_POST['email_frequency'] : '';
	        update_post_meta($alert_id, WP_JOB_BOARD_PRO_CANDIDATE_ALERT_PREFIX . 'email_frequency', $email_frequency);

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
	        	update_post_meta($alert_id, WP_JOB_BOARD_PRO_CANDIDATE_ALERT_PREFIX . 'alert_query', $alert_query);	
	        }
	        
	        do_action('wp-job-board-pro-after-add-candidate-alert', $alert_id);

	        $return = array( 'status' => true, 'msg' => esc_html__('Uspešno ste dodali obaveštenje o kandidatima.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
	    } else {
			$return = array( 'status' => false, 'msg' => esc_html__('Došlo je do greške prilikom dodavanja obaveštenja o kandidatima.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
	}

	public static function validate_add_candidate_alert() {
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

	public static function process_remove_candidate_alert() {
		$return = array();

		if ( !is_user_logged_in() ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Prijavite se da biste uklonili obaveštenje o kandidatima.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		$alert_id = !empty($_POST['alert_id']) ? $_POST['alert_id'] : '';

		if ( empty($alert_id) ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Kandidati ne postoje prema kriterijumima koje ste zadali.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		$user_id = WP_Job_Board_Pro_User::get_user_id();
		$is_allowed = WP_Job_Board_Pro_Mixes::is_allowed_to_remove( $user_id, $alert_id );

		if ( ! $is_allowed ) {
	        $return = array( 'status' => false, 'msg' => esc_html__('Ne možete ukloniti ovo obaveštenje o kandidatu.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}

		if ( wp_delete_post( $alert_id ) ) {
	        $return = array( 'status' => true, 'msg' => esc_html__('Uspešno ste uklonili obaveštenje o kandidatima.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
	    } else {
			$return = array( 'status' => false, 'msg' => esc_html__('Došlo je do greške prilikom uklanjanja obaveštenja o kandidatima.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
	}
}

WP_Job_Board_Pro_Candidate_Alert::init();