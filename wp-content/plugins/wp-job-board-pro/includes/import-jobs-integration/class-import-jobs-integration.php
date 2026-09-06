<?php
/**
 * Import Jobs Integration
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Job_Board_Pro_Import_Jobs_Integration {
    
    public static function init() {
        // careerbuilder
        require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/import-jobs-integration/careerbuilder/class-careerbuilder-jobs-api.php';
        require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/import-jobs-integration/careerbuilder/class-careerbuilder-jobs.php';

        // careerjet
        require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/import-jobs-integration/careerjet/class-careerjet-jobs-api.php';
        require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/import-jobs-integration/careerjet/class-careerjet-jobs.php';

        // indeed
        require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/import-jobs-integration/indeed/class-indeed-jobs-api.php';
        require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/import-jobs-integration/indeed/class-indeed-jobs.php';

        // ziprecruiter
        require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/import-jobs-integration/ziprecruiter/class-ziprecruiter-jobs-api.php';
        require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/import-jobs-integration/ziprecruiter/class-ziprecruiter-jobs.php';

        add_action( 'admin_enqueue_scripts', array(__CLASS__, 'scripts') );
    }

    public static function scripts() {
        if ( !empty($_GET['page']) && ($_GET['page'] == 'import-careerbuilder-jobs' || $_GET['page'] == 'import-careerjet-jobs' || $_GET['page'] == 'import-indeed-jobs' || $_GET['page'] == 'import-ziprecruiter-jobs') ) {
            wp_enqueue_script( 'wp-job-board-pro-import-jobs-integration', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/admin-impport-job-integration.js', array( 'jquery' ), '1.0.0', true );

            $args = array(
                'ajaxurl' => admin_url('admin-ajax.php')
            );

            wp_localize_script('wp-job-board-pro-import-jobs-integration', 'wp_job_board_pro_iji_opts', $args);
        }
    }

}

WP_Job_Board_Pro_Import_Jobs_Integration::init();