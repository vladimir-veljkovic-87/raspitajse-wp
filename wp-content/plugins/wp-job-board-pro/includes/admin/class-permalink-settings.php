<?php
/**
 * Permalink Settings
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Job_Board_Pro_Permalink_Settings {
	
	public static function init() {
		add_action('admin_init', array( __CLASS__, 'setup_fields') );
		add_action('admin_init', array( __CLASS__, 'settings_save') );
	}

	public static function setup_fields() {
		add_settings_field(
			'wp_job_board_pro_job_base_slug',
			__( 'Job base', 'wp-job-board-pro' ),
			array( __CLASS__, 'job_base_slug_input' ),
			'permalink',
			'optional'
		);
		add_settings_field(
			'wp_job_board_pro_job_category_slug',
			__( 'Job category base', 'wp-job-board-pro' ),
			array( __CLASS__, 'job_category_slug_input' ),
			'permalink',
			'optional'
		);
		add_settings_field(
			'wp_job_board_pro_job_type_slug',
			__( 'Job type base', 'wp-job-board-pro' ),
			array( __CLASS__, 'job_type_slug_input' ),
			'permalink',
			'optional'
		);
		add_settings_field(
			'wp_job_board_pro_job_location_slug',
			__( 'Job location base', 'wp-job-board-pro' ),
			array( __CLASS__, 'job_location_slug_input' ),
			'permalink',
			'optional'
		);
		add_settings_field(
			'wp_job_board_pro_job_tag_slug',
			__( 'Job tag base', 'wp-job-board-pro' ),
			array( __CLASS__, 'job_tag_slug_input' ),
			'permalink',
			'optional'
		);
		add_settings_field(
			'wp_job_board_pro_job_archive_slug',
			__( 'Job archive base', 'wp-job-board-pro' ),
			array( __CLASS__, 'job_archive_slug_input' ),
			'permalink',
			'optional'
		);

		// employer
		add_settings_field(
			'wp_job_board_pro_employer_base_slug',
			__( 'Employer base', 'wp-job-board-pro' ),
			array( __CLASS__, 'employer_base_slug_input' ),
			'permalink',
			'optional'
		);
		add_settings_field(
			'wp_job_board_pro_employer_category_slug',
			__( 'Employer category base', 'wp-job-board-pro' ),
			array( __CLASS__, 'employer_category_slug_input' ),
			'permalink',
			'optional'
		);
		add_settings_field(
			'wp_job_board_pro_employer_location_slug',
			__( 'Employer location base', 'wp-job-board-pro' ),
			array( __CLASS__, 'employer_location_slug_input' ),
			'permalink',
			'optional'
		);
		add_settings_field(
			'wp_job_board_pro_employer_archive_slug',
			__( 'Employer archive base', 'wp-job-board-pro' ),
			array( __CLASS__, 'employer_archive_slug_input' ),
			'permalink',
			'optional'
		);

		// candidate
		add_settings_field(
			'wp_job_board_pro_candidate_base_slug',
			__( 'Candidate base', 'wp-job-board-pro' ),
			array( __CLASS__, 'candidate_base_slug_input' ),
			'permalink',
			'optional'
		);
		add_settings_field(
			'wp_job_board_pro_candidate_category_slug',
			__( 'Candidate category base', 'wp-job-board-pro' ),
			array( __CLASS__, 'candidate_category_slug_input' ),
			'permalink',
			'optional'
		);
		add_settings_field(
			'wp_job_board_pro_candidate_location_slug',
			__( 'Candidate location base', 'wp-job-board-pro' ),
			array( __CLASS__, 'candidate_location_slug_input' ),
			'permalink',
			'optional'
		);
		add_settings_field(
			'wp_job_board_pro_candidate_tag_slug',
			__( 'Candidate tag base', 'wp-job-board-pro' ),
			array( __CLASS__, 'candidate_tag_slug_input' ),
			'permalink',
			'optional'
		);
		add_settings_field(
			'wp_job_board_pro_candidate_archive_slug',
			__( 'Candidate archive base', 'wp-job-board-pro' ),
			array( __CLASS__, 'candidate_archive_slug_input' ),
			'permalink',
			'optional'
		);
	}

	public static function job_base_slug_input() {
		$value = get_option('wp_job_board_pro_job_base_slug');
		?>
		<input name="wp_job_board_pro_job_base_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php esc_attr_e( 'job', 'wp-job-board-pro' ); ?>" />
		<?php
	}

	public static function job_category_slug_input() {
		$value = get_option('wp_job_board_pro_job_category_slug');
		?>
		<input name="wp_job_board_pro_job_category_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php esc_attr_e( 'job-category', 'wp-job-board-pro' ); ?>" />
		<?php
	}

	public static function job_type_slug_input() {
		$value = get_option('wp_job_board_pro_job_type_slug');
		?>
		<input name="wp_job_board_pro_job_type_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php esc_attr_e( 'job-type', 'wp-job-board-pro' ); ?>" />
		<?php
	}

	public static function job_location_slug_input() {
		$value = get_option('wp_job_board_pro_job_location_slug');
		?>
		<input name="wp_job_board_pro_job_location_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php esc_attr_e( 'job-location', 'wp-job-board-pro' ); ?>" />
		<?php
	}

	public static function job_tag_slug_input() {
		$value = get_option('wp_job_board_pro_job_tag_slug');
		?>
		<input name="wp_job_board_pro_job_tag_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php esc_attr_e( 'job-tag', 'wp-job-board-pro' ); ?>" />
		<?php
	}

	public static function job_archive_slug_input() {
		$value = get_option('wp_job_board_pro_job_archive_slug');
		?>
		<input name="wp_job_board_pro_job_archive_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php esc_attr_e( 'jobs', 'wp-job-board-pro' ); ?>" />
		<?php
	}

	// employer
	public static function employer_base_slug_input() {
		$value = get_option('wp_job_board_pro_employer_base_slug');
		?>
		<input name="wp_job_board_pro_employer_base_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php esc_attr_e( 'employer', 'wp-job-board-pro' ); ?>" />
		<?php
	}

	public static function employer_category_slug_input() {
		$value = get_option('wp_job_board_pro_employer_category_slug');
		?>
		<input name="wp_job_board_pro_employer_category_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php esc_attr_e( 'employer-category', 'wp-job-board-pro' ); ?>" />
		<?php
	}

	public static function employer_location_slug_input() {
		$value = get_option('wp_job_board_pro_employer_location_slug');
		?>
		<input name="wp_job_board_pro_employer_location_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php esc_attr_e( 'employer-location', 'wp-job-board-pro' ); ?>" />
		<?php
	}

	public static function employer_archive_slug_input() {
		$value = get_option('wp_job_board_pro_employer_archive_slug');
		?>
		<input name="wp_job_board_pro_employer_archive_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php esc_attr_e( 'employers', 'wp-job-board-pro' ); ?>" />
		<?php
	}

	// candidate
	public static function candidate_base_slug_input() {
		$value = get_option('wp_job_board_pro_candidate_base_slug');
		?>
		<input name="wp_job_board_pro_candidate_base_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php esc_attr_e( 'candidate', 'wp-job-board-pro' ); ?>" />
		<?php
	}

	public static function candidate_category_slug_input() {
		$value = get_option('wp_job_board_pro_candidate_category_slug');
		?>
		<input name="wp_job_board_pro_candidate_category_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php esc_attr_e( 'candidate-category', 'wp-job-board-pro' ); ?>" />
		<?php
	}

	public static function candidate_location_slug_input() {
		$value = get_option('wp_job_board_pro_candidate_location_slug');
		?>
		<input name="wp_job_board_pro_candidate_location_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php esc_attr_e( 'candidate-location', 'wp-job-board-pro' ); ?>" />
		<?php
	}

	public static function candidate_tag_slug_input() {
		$value = get_option('wp_job_board_pro_candidate_tag_slug');
		?>
		<input name="wp_job_board_pro_candidate_tag_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php esc_attr_e( 'candidate-tag', 'wp-job-board-pro' ); ?>" />
		<?php
	}

	public static function candidate_archive_slug_input() {
		$value = get_option('wp_job_board_pro_candidate_archive_slug');
		?>
		<input name="wp_job_board_pro_candidate_archive_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php esc_attr_e( 'candidates', 'wp-job-board-pro' ); ?>" />
		<?php
	}

	public static function settings_save() {
		if ( ! is_admin() ) {
			return;
		}

		if ( isset( $_POST['permalink_structure'] ) ) {
			if ( function_exists( 'switch_to_locale' ) ) {
				switch_to_locale( get_locale() );
			}
			if ( isset($_POST['wp_job_board_pro_job_base_slug']) ) {
				update_option( 'wp_job_board_pro_job_base_slug', sanitize_title_with_dashes($_POST['wp_job_board_pro_job_base_slug']) );
			}
			if ( isset($_POST['wp_job_board_pro_job_category_slug']) ) {
				update_option( 'wp_job_board_pro_job_category_slug', sanitize_title_with_dashes($_POST['wp_job_board_pro_job_category_slug']) );
			}
			if ( isset($_POST['wp_job_board_pro_job_type_slug']) ) {
				update_option( 'wp_job_board_pro_job_type_slug', sanitize_title_with_dashes($_POST['wp_job_board_pro_job_type_slug']) );
			}
			if ( isset($_POST['wp_job_board_pro_job_location_slug']) ) {
				update_option( 'wp_job_board_pro_job_location_slug', sanitize_title_with_dashes($_POST['wp_job_board_pro_job_location_slug']) );
			}
			if ( isset($_POST['wp_job_board_pro_job_tag_slug']) ) {
				update_option( 'wp_job_board_pro_job_tag_slug', sanitize_title_with_dashes($_POST['wp_job_board_pro_job_tag_slug']) );
			}
			if ( isset($_POST['wp_job_board_pro_job_archive_slug']) ) {
				update_option( 'wp_job_board_pro_job_archive_slug', sanitize_title_with_dashes($_POST['wp_job_board_pro_job_archive_slug']) );
			}

			// employer
			if ( isset($_POST['wp_job_board_pro_employer_base_slug']) ) {
				update_option( 'wp_job_board_pro_employer_base_slug', sanitize_title_with_dashes($_POST['wp_job_board_pro_employer_base_slug']) );
			}
			if ( isset($_POST['wp_job_board_pro_employer_category_slug']) ) {
				update_option( 'wp_job_board_pro_employer_category_slug', sanitize_title_with_dashes($_POST['wp_job_board_pro_employer_category_slug']) );
			}
			if ( isset($_POST['wp_job_board_pro_employer_location_slug']) ) {
				update_option( 'wp_job_board_pro_employer_location_slug', sanitize_title_with_dashes($_POST['wp_job_board_pro_employer_location_slug']) );
			}
			if ( isset($_POST['wp_job_board_pro_employer_archive_slug']) ) {
				update_option( 'wp_job_board_pro_employer_archive_slug', sanitize_title_with_dashes($_POST['wp_job_board_pro_employer_archive_slug']) );
			}

			// candidate
			if ( isset($_POST['wp_job_board_pro_candidate_base_slug']) ) {
				update_option( 'wp_job_board_pro_candidate_base_slug', sanitize_title_with_dashes($_POST['wp_job_board_pro_candidate_base_slug']) );
			}
			if ( isset($_POST['wp_job_board_pro_candidate_category_slug']) ) {
				update_option( 'wp_job_board_pro_candidate_category_slug', sanitize_title_with_dashes($_POST['wp_job_board_pro_candidate_category_slug']) );
			}
			if ( isset($_POST['wp_job_board_pro_candidate_location_slug']) ) {
				update_option( 'wp_job_board_pro_candidate_location_slug', sanitize_title_with_dashes($_POST['wp_job_board_pro_candidate_location_slug']) );
			}
			if ( isset($_POST['wp_job_board_pro_candidate_tag_slug']) ) {
				update_option( 'wp_job_board_pro_candidate_tag_slug', sanitize_title_with_dashes($_POST['wp_job_board_pro_candidate_tag_slug']) );
			}
			if ( isset($_POST['wp_job_board_pro_candidate_archive_slug']) ) {
				update_option( 'wp_job_board_pro_candidate_archive_slug', sanitize_title_with_dashes($_POST['wp_job_board_pro_candidate_archive_slug']) );
			}
		}
	}
}

WP_Job_Board_Pro_Permalink_Settings::init();
