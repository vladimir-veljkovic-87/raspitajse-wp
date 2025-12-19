<?php
/**
 * Submit Form
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_Submit_Form extends WP_Job_Board_Pro_Abstract_Form {
	public $form_name = 'wp_job_board_pro_job_submit_form';
	

	private static $_instance = null;

	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {

		add_action( 'wp', array( $this, 'process' ) );

		$this->get_steps();

		if ( !empty( $_REQUEST['submit_step'] ) ) {
			$step = is_numeric( $_REQUEST['submit_step'] ) ? max( absint( $_REQUEST['submit_step'] ), 0 ) : array_search( intval( $_REQUEST['submit_step'] ), array_keys( $this->steps ), true );
			$this->step = $step;
		}

		$this->job_id = ! empty( $_REQUEST['job_id'] ) ? absint( $_REQUEST['job_id'] ) : 0;

		if ( ! WP_Job_Board_Pro_User::is_employer_can_edit_job( $this->job_id ) ) {
			$this->job_id = 0;
		}
		do_action('wp_job_board_pro_submit_job_construct', $this);
		add_filter( 'cmb2_meta_boxes', array( $this, 'fields_front' ) );
		
	}

	public function get_steps() {
		$this->steps = apply_filters( 'wp_job_board_pro_submit_job_steps', array(
			'submit'  => array(
				'view'     => array( $this, 'form_output' ),
				'handler'  => array( $this, 'submit_process' ),
				'priority' => 10,
			),
			'preview' => array(
				'view'     => array( $this, 'preview_output' ),
				'handler'  => array( $this, 'preview_process' ),
				'priority' => 20,
			),
			'done'    => array(
				'before_view' => array( $this, 'done_handler' ),
				'view'     => array( $this, 'done_output' ),
				'priority' => 30,
			)
		));

		uasort( $this->steps, array( 'WP_Job_Board_Pro_Mixes', 'sort_array_by_priority' ) );

		return $this->steps;
	}

	public function submit_process() {
		
		if ( ! isset( $_POST['submit-cmb-job_listing'] ) || empty( $_POST[WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX.'post_type'] ) || 'job_listing' !== $_POST[WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX.'post_type'] ) {
			return;
		}
		
		$cmb = cmb2_get_metabox( WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'front' );
		if ( ! isset( $_POST[ $cmb->nonce() ] ) || ! wp_verify_nonce( $_POST[ $cmb->nonce() ], $cmb->nonce() ) ) {
			return;
		}
		// Setup and sanitize data
		if ( isset( $_POST[ WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'title' ] ) ) {
			$post_id = $this->job_id;

			$post_status = 'preview';
			if ( ! empty( $post_id ) ) {
				$old_post = get_post( $post_id );
				$post_date = $old_post->post_date;
				$old_post_status = get_post_status( $post_id );
				if ( $old_post_status === 'draft' || $old_post_status === 'expired' ) {
					$post_status = 'preview';
				} else {
					$post_status = $old_post_status;
				}
			} else {
				$post_date = '';
			}
			
			$employer_user_id = WP_Job_Board_Pro_User::get_user_id();
			$data = array(
				'post_title'     => sanitize_text_field( $_POST[ WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'title' ] ),
				'post_author'    => $employer_user_id,
				'post_status'    => $post_status,
				'post_type'      => 'job_listing',
				'post_date'      => $post_date,
				'post_content'   => wp_kses_post( $_POST[ WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'description' ] ),
			);

			$new_post = true;
			if ( !empty( $post_id ) ) {
				$data['ID'] = $post_id;
				$new_post = false;
			} else {
				if ( apply_filters('wp-job-board-pro-update-slug-submit-job', true) ) {
					$job_slug = array();

					// Prepend with company name.
					$employer_slug = wp_job_board_pro_get_option('submission_job_slug_employer', 'on');
					if ( $employer_slug == 'on' ) {
						$employer_slug = true;
					} else {
						$employer_slug = false;
					}
					$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($employer_user_id);
					$employer_name = get_the_title($employer_id);
					if ( apply_filters( 'wp-job-board-pro-submit-job-form-prefix-post-name-with-company', $employer_slug ) && !empty( $employer_name ) ) {
						$job_slug[] = $employer_name;
					}

					// Prepend location.
					$location_slug = wp_job_board_pro_get_option('submission_job_slug_location', 'on');
					if ( $location_slug == 'on' ) {
						$location_slug = true;
					} else {
						$location_slug = false;
					}
					if ( apply_filters( 'wp-job-board-pro-submit-job-form-prefix-post-name-with-location', $location_slug ) && !empty($_POST[WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX.'location']) ) {
						$slugs = $_POST[WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX.'location'];
						if ( is_array($slugs) ) {
							foreach ($slugs as $slug) {
								if ( is_numeric($slug) ) {
									$term = get_term($slug, 'job_listing_location');
									if ( $term && $term->slug ) {
										$job_slug[] = $term->slug;
									}
								} else {
									$job_slug[] = $slug;
								}
							}
						} else {
							if ( is_numeric($slugs) ) {
								$term = get_term($slugs, 'job_listing_location');
								if ( $term && $term->slug ) {
									$job_slug[] = $term->slug;
								}
							} else {
								$job_slug[] = $slugs;
							}
						}
					}

					// Prepend with job type.
					$type_slug = wp_job_board_pro_get_option('submission_job_slug_type', 'on');
					if ( $type_slug == 'on' ) {
						$type_slug = true;
					} else {
						$type_slug = false;
					}
					if ( apply_filters( 'wp-job-board-pro-submit-job-form-prefix-post-name-with-job-type', $type_slug ) && !empty($_POST[WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX.'type']) ) {
						$slugs = $_POST[WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX.'type'];
						if ( is_array($slugs) ) {
							foreach ($slugs as $slug) {
								if ( is_numeric($slug) ) {
									$term = get_term($slug, 'job_listing_type');
									if ( $term && $term->slug ) {
										$job_slug[] = $term->slug;
									}
								} else {
									$job_slug[] = $slug;
								}
							}
						} else {
							if ( is_numeric($slugs) ) {
								$term = get_term($slugs, 'job_listing_type');
								if ( $term && $term->slug ) {
									$job_slug[] = $term->slug;
								}
							} else {
								$job_slug[] = $slugs;
							}
						}
					}

					$job_slug[] = $data['post_title'];
					$data['post_name'] = sanitize_title( implode( '-', $job_slug ) );
				}
			}

			do_action( 'wp-job-board-pro-process-submission-before-save', $post_id, $this );

			$data = apply_filters('wp-job-board-pro-process-submission-data', $data, $post_id);
			
			$this->errors = $this->submission_validate($data);
			if ( sizeof($this->errors) ) {
				return;
			}

			$post_id = wp_insert_post( $data, true );

			if ( ! empty( $post_id ) ) {
				$_POST['object_id'] = $post_id; // object_id in POST contains page ID instead of job ID

				$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($employer_user_id);
				$author_id = update_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'employer_posted_by', $employer_id);

				$cmb->save_fields( $post_id, 'post', $_POST );

				// Create featured image
				$featured_image = get_post_meta( $post_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'featured_image', true );
				if ( ! empty( $_POST[ 'current_' . WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'featured_image' ] ) ) {
					$img_id = get_post_meta( $post_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'featured_image_img', true );
					if ( !empty($featured_image) ) {
						if ( is_array($featured_image) ) {
							$img_id = $featured_image[0];
						} elseif ( is_integer($featured_image) ) {
							$img_id = $featured_image;
						} else {
							$img_id = WP_Job_Board_Pro_Image::get_attachment_id_from_url($featured_image);
						}
						set_post_thumbnail( $post_id, $img_id );
					} else {
						update_post_meta( $post_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'featured_image', null );
						delete_post_thumbnail( $post_id );
					}
				} else {
					update_post_meta( $post_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'featured_image', null );
					delete_post_thumbnail( $post_id );
				}

				do_action( 'wp-job-board-pro-process-submission-after-save', $post_id );

				if ( $new_post ) {
					setcookie( 'job_add_new_update', 'new' );
				} else {
					setcookie( 'job_add_new_update', 'update' );
				}
				$this->job_id = $post_id;
				$this->step ++;

			} else {
				if( $new_post ) {
					$this->errors[] = __( 'Can not create job', 'wp-job-board-pro' );
				} else {
					$this->errors[] = __( 'Can not update job', 'wp-job-board-pro' );
				}
			}
		}

		return;
	}

	public function submission_validate( $data ) {
		$error = array();
		if ( empty($data['post_author']) ) {
			$error[] = __( 'Please login to submit job', 'wp-job-board-pro' );
		}
		if ( empty($data['post_title']) ) {
			$error[] = __( 'Title is required.', 'wp-job-board-pro' );
		}
		if ( empty($data['post_content']) ) {
			$error[] = __( 'Description is required.', 'wp-job-board-pro' );
		}
		$error = apply_filters('wp-job-board-pro-submission-validate', $error);
		return $error;
	}

	public function preview_output() {
		global $post;

		if ( $this->job_id ) {
			$post              = get_post( $this->job_id ); // WPCS: override ok.
			$post->post_status = 'preview';

			setup_postdata( $post );

			do_action('wp-job-board-pro-before-preview-job', $post);

			echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'submission/job-submit-preview', array(
				'post_id' => $this->job_id,
				'job_id'         => $this->job_id,
				'step'           => $this->get_step(),
				'form_obj'           => $this,
			) );
			wp_reset_postdata();
		}
	}

	public function preview_process() {
		if ( ! $_POST ) {
			return;
		}

		if ( !isset( $_POST['security-job-submit-preview'] ) || ! wp_verify_nonce( $_POST['security-job-submit-preview'], 'wp-job-board-pro-job-submit-preview-nonce' )  ) {
			$this->errors[] = esc_html__('Your nonce did not verify.', 'wp-job-board-pro');
			return;
		}

		if ( isset( $_POST['continue-edit-job'] ) ) {
			$this->step --;
		} elseif ( isset( $_POST['continue-submit-job'] ) ) {
			$job = get_post( $this->job_id );

			if ( in_array( $job->post_status, array( 'preview', 'expired' ), true ) ) {
				// Reset expiry.
				delete_post_meta( $job->ID, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX.'expiry_date' );

				// Update job listing.
				$review_before = wp_job_board_pro_get_option( 'submission_requires_approval' );
				$post_status = 'publish';
				if ( $review_before == 'on' ) {
					$post_status = 'pending';
				}

				$employer_user_id = WP_Job_Board_Pro_User::get_user_id();
				$update_job                  = array();
				$update_job['ID']            = $job->ID;
				$update_job['post_status']   = apply_filters( 'wp_job_board_pro_submit_job_post_status', $post_status, $job );
				$update_job['post_date']     = current_time( 'mysql' );
				$update_job['post_date_gmt'] = current_time( 'mysql', 1 );
				$update_job['post_author']   = $employer_user_id;

				wp_update_post( $update_job );
			}

			$this->step ++;
		}
	}

	public function done_output() {
		$job = get_post( $this->job_id );
		
		echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'submission/job-submit-done', array(
			'post_id' => $this->job_id,
			'job'	  => $job,
		) );
	}

	public function done_handler() {
		do_action( 'wp_job_board_pro_job_submit_done', $this->job_id );
		
		if ( ! empty( $_COOKIE['job_add_new_update'] ) ) {
			$job_add_new_update = $_COOKIE['job_add_new_update'];

			if ( wp_job_board_pro_get_option('admin_notice_add_new_listing') ) {
				$job = get_post($this->job_id);
				$email_from = get_option( 'admin_email', false );
				
				$headers = sprintf( "From: %s <%s>\r\n Content-type: text/html", get_bloginfo('name'), $email_from );
				$email_to = get_option( 'admin_email', false );
				$subject = WP_Job_Board_Pro_Email::render_email_vars(array('job' => $job), 'admin_notice_add_new_listing', 'subject');
				$content = WP_Job_Board_Pro_Email::render_email_vars(array('job' => $job), 'admin_notice_add_new_listing', 'content');
				
				WP_Job_Board_Pro_Email::wp_mail( $email_to, $subject, $content, $headers );
			}
			
			setcookie( 'job_add_new_update', '', time() - HOUR_IN_SECONDS );
		}
	}
}

function wp_job_board_pro_submit_form() {
	if ( ! empty( $_POST['wp_job_board_pro_job_submit_form'] ) ) {
		WP_Job_Board_Pro_Submit_Form::get_instance();
	}
}

add_action( 'init', 'wp_job_board_pro_submit_form' );