<?php
/**
 * Settings
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Job_Board_Pro_Careerjet_Jobs_Hooks {

    private static $key = 'wp_job_board_pro_careerjet_import';

    public static function init() {
        
        // Job Fields
        add_filter( 'cmb2_meta_boxes', array( __CLASS__, 'careerjet_job_meta_fields' ), 100 );

        add_action( 'admin_menu', array( __CLASS__, 'careerjet_jobs_admin_menu' ) );

        add_action( 'wp_ajax_wp_job_board_pro_ajax_careerjet_job_import', array( __CLASS__, 'process_import_careerjet_jobs' ) );

        add_filter( 'wp-job-board-pro-get-company-name', array( __CLASS__, 'get_company_name' ) );

        add_filter( 'wp-job-board-pro-get-company-name-html', array( __CLASS__, 'get_company_name_html' ) );
    }

    public static function careerjet_jobs_admin_menu() {
        if ( wp_job_board_pro_get_option('careerjet_job_import_enable') || (isset($_POST['careerjet_job_import_enable']) && $_POST['careerjet_job_import_enable'] == 'on') ) {
            add_submenu_page( 'edit.php?post_type=job_listing', esc_html__('Import Careerjet Jobs', 'wp-job-board-pro'), esc_html__('Import Careerjet Jobs', 'wp-job-board-pro'), 'manage_options', 'import-careerjet-jobs', array( __CLASS__, 'import_careerjet_jobs_settings' ) );
        }
    }

    public static function import_careerjet_jobs_settings() {
        ?>
        <div class="wrap wp_job_board_pro_settings_page cmb2_options_page">
            <h2><?php esc_html_e('Import Careerjet Jobs', 'wp-job-board-pro'); ?></h2>
            
            <?php cmb2_metabox_form( self::import_careerjet_fields(), self::$key ); ?>

        </div>

        <?php
    }

    public static function import_careerjet_fields() {
        $fields = array(
            'id'         => 'options_page',
            'wp_job_board_pro_title' => __( 'Careerjet Jobs Import', 'wp-job-board-pro' ),
            'show_on'    => array( 'key' => 'options-page', 'value' => array( self::$key, ), ),
            'fields'     => apply_filters( 'wp_job_board_pro_careerjet_job_import_fields', array(
                    
                    array(
                        'name'    => __( 'Keywords', 'wp-job-board-pro' ),
                        'desc'    => __( 'Enter job title, keywords or company name. Default keyword is all.', 'wp-job-board-pro' ),
                        'id'      => 'careerjet_job_import_keywords',
                        'type'    => 'text',
                    ),
                    array(
                        'name'    => __( 'Location', 'wp-job-board-pro' ),
                        'desc'    => __( 'Enter a location for search.', 'wp-job-board-pro' ),
                        'id'      => 'careerjet_job_import_location',
                        'type'    => 'text',
                    ),
                    array(
                        'name'    => __( 'Page', 'wp-job-board-pro' ),
                        'desc'    => __( 'Enter page number to import jobs. Default page number is 1.', 'wp-job-board-pro' ),
                        'id'      => 'careerjet_job_import_page',
                        'type'    => 'text',
                        'default' => '1',
                    ),
                    array(
                        'name'    => __( 'Expired on', 'wp-job-board-pro' ),
                        'desc'    => __( 'Enter number of days (numeric format) for expiray date after job posted date.', 'wp-job-board-pro' ),
                        'id'      => 'careerjet_job_import_expired_on',
                        'type'    => 'text',
                        'default' => '0',
                    ),
                    array(
                        'name'    => __( 'Posted By Type', 'wp-job-board-pro' ),
                        'id'      => 'careerjet_job_import_posted_by_type',
                        'type'    => 'select',
                        'options' => array(
                            'auto'  => __( 'Auto Generate', 'wp-job-board-pro' ),
                            'employer' => __( 'Choose A Employer', 'wp-job-board-pro' ),
                        ),
                        'default' => 'auto',
                    ),
                    array(
                        'name'          => __( 'Posted By', 'wp-job-board-pro' ),
                        'id'            => 'careerjet_job_import_posted_by',
                        'type'          => 'user_ajax_search',
                        'query_args'    => array(
                            'role'              => array( 'wp_job_board_pro_employer' ),
                            'search_columns'    => array( 'user_login', 'user_email' )
                        ),
                        'desc'    => __( 'Choose an employer for job author', 'wp-job-board-pro' ),
                    )
                )
            )        
        );
        return $fields;
    }

    public static function careerjet_job_meta_fields( $metaboxes ) {
        if ( wp_job_board_pro_get_option('careerjet_job_import_enable') ) {
            $prefix = WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX;

            $metaboxes[ $prefix . 'careerjet_job_fields' ] = array(
                'id'                        => $prefix . 'careerjet_job_fields',
                'title'                     => __( 'Careerjet Job Fields', 'wp-job-board-pro' ),
                'object_types'              => array( 'job_listing' ),
                'context'                   => 'normal',
                'priority'                  => 'high',
                'show_names'                => true,
                'show_in_rest'              => true,
                'fields'                    => array(
                    array(
                        'name'              => __( 'Job Detail Url', 'wp-job-board-pro' ),
                        'id'                => WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'careerjet_detail_url',
                        'type'              => 'text',
                    ),
                    array(
                        'name'              => __( 'Company Name', 'wp-job-board-pro' ),
                        'id'                => WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'careerjet_company_name',
                        'type'              => 'text',
                    ),
                ),
            );
        }
        return $metaboxes;
    }

    public static function process_import_careerjet_jobs() {
        $search_keywords = !empty($_POST['careerjet_job_import_keywords']) ? sanitize_text_field(stripslashes($_POST['careerjet_job_import_keywords'])) : '';
        $search_location = !empty($_POST['careerjet_job_import_location']) ? sanitize_text_field(stripslashes($_POST['careerjet_job_import_location'])) : '';
        $page = !empty($_POST['careerjet_job_import_page']) ? sanitize_text_field($_POST['careerjet_job_import_page']) : '';
        $expired_on = !empty($_POST['careerjet_job_import_expired_on']) ? sanitize_text_field($_POST['careerjet_job_import_expired_on']) : '';
        $posted_by = !empty($_POST['careerjet_job_import_posted_by']) ? sanitize_text_field($_POST['careerjet_job_import_posted_by']) : '';
        $posted_by_type = !empty($_POST['careerjet_job_import_posted_by_type']) ? sanitize_text_field($_POST['careerjet_job_import_posted_by_type']) : '';


        $limit = $limit ? $limit : 10;
        $limit =  $limit > 25 ? 25 :  $limit;
        $start = $start ? ($start - 1) : 0;
        $api_args = array(
            'keywords' => $search_keywords,
            'location' => $search_location,
            'page' => $page,
        );

        $careerjet_jobs = WP_Job_Board_Pro_Careerjet_API::get_jobs($api_args);
        
        if (isset($careerjet_jobs['error']) && $careerjet_jobs['error'] != '') {
            $json = array(
                'status' => false,
                'msg' => $careerjet_jobs['error'],
            );
            echo json_encode($json);
            die();
        } elseif (empty($careerjet_jobs)) {
            $json = array(
                'status' => false,
                'msg' => esc_html__('Sorry! There are no jobs found for your search query.', 'wp-job-board-pro')
            );
            echo json_encode($json);
            die();
        } else {
            $post_author = '';
            if ( $posted_by_type == 'employer' ) {
                $post_author = !empty($posted_by) ? $posted_by : '';
            }
            
            foreach ($careerjet_jobs as $careerjet_job) {
                
                $job_url = isset($careerjet_job->url) ? $careerjet_job->url : '';

                $existing_id = WP_Job_Board_Pro_Mixes::get_post_id_by_meta_value( WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX.'careerjet_detail_url', $job_url);

                if ( !empty($existing_id) ) {
                    continue;
                }
                
                $post_data = array(
                    'post_type' => 'job_listing',
                    'post_title' => isset($careerjet_job->title) ? $careerjet_job->title : '',
                    'post_content' => isset($careerjet_job->description) ? $careerjet_job->description : '',
                    'post_status' => 'publish'
                );
                if ( $post_author ) {
                    $post_data['post_author'] = $post_author;
                } elseif ( $posted_by_type == 'auto' && !empty($careerjet_job->company) ) {
                    $user_id = WP_Job_Board_Pro_User::generate_user_by_post_name($careerjet_job->company);
                    if ( $user_id ) {
                        $post_data['post_author'] = $user_id;
                    }
                }
                // Insert the job into the database
                $post_id = wp_insert_post($post_data);

                if ( !empty($user_id) ) {
                    $employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);
                    update_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'employer_posted_by', $employer_id, true);
                }
                
                // Insert job expired on meta key
                if ( $expired_on > 0 ) {
                    $expired_date = date('Y-m-d', strtotime("$expired_on days", current_time('timestamp')));
                    update_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'expiry_date', $expired_date, true);
                }

                // Insert job address meta key
                $location_addrs = array();
                
                if (!empty($careerjet_job->locations)) {
                    $location_addrs['address'] = $careerjet_job->locations;
                    update_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'address', $careerjet_job->locations, true);
                } else {
                    $location_addrs['address'] = '';
                }

                update_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'map_location', $location_addrs, true);

                // Insert job referral meta key
                update_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'job_referral', 'careerjet', true);

                // Insert job detail url meta key
                update_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'careerjet_detail_url', esc_url($job_url), true);

                update_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'apply_type', 'external', true);
                update_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'apply_url', $job_url, true);

                // Insert job comapny name meta key
                $job_company = isset($careerjet_job->company) ? $careerjet_job->company : '';
                update_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'careerjet_company_name', $job_company, true);

                // salary
                if ( !empty($careerjet_job->salary_min) ) {
                    update_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'salary', $careerjet_job->salary_min, true);
                }

                if ( !empty($careerjet_job->salary_max) ) {
                    update_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'max_salary', $careerjet_job->salary_max, true);
                }
                
            }
            $json = array(
                'status' => true,
                'msg' => sprintf(__('%s careerjet jobs are imported successfully.', 'wp-job-board-pro'), count($careerjet_jobs))
            );
            echo json_encode($json);
            die();
        }
        die();
    }

    public static function get_company_name($ouput, $post) {
        $job_referral = get_post_meta($post->ID, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'job_referral', true);
        $careerjet_company_name = get_post_meta($post->ID, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'careerjet_company_name', true);
        if ($job_referral == 'careerjet' && $careerjet_company_name != '') {
            $ouput = $careerjet_company_name;
        }
        return $ouput;
    }

    public static function get_company_name_html($ouput, $post) {
        $job_referral = get_post_meta($post->ID, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'job_referral', true);
        $careerjet_company_name = get_post_meta($post->ID, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'careerjet_company_name', true);
        if ($job_referral == 'careerjet' && $careerjet_company_name != '') {
            $ouput = sprintf(wp_kses(__('<span class="employer text-theme">%s</span>', 'wp-job-board-pro'), array( 'span' => array('class' => array()) ) ), $careerjet_company_name );
        }
        return $ouput;
    }

    public static function view_more_btn($post) {
        $ouput = '';
        $job_referral = get_post_meta($post->ID, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'job_referral', true);
        $careerjet_detail_url = get_post_meta($post->ID, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'careerjet_detail_url', true);
        if ($job_referral == 'careerjet' && $careerjet_detail_url != '') {
            $ouput = '<div class="view-more-link"><a class="btn btn-theme" href="' . $careerjet_detail_url . '">' . esc_html__('view more', 'wp-job-board-pro') . '</a></div>';
        }
        return $ouput;
    }
}

add_filter( 'cmb2_get_metabox_form_format', 'wp_job_board_pro_careerjet_modify_cmb2_form_output', 10, 3 );

function wp_job_board_pro_careerjet_modify_cmb2_form_output( $form_format, $object_id, $cmb ) {
    if ( 'wp_job_board_pro_careerjet_import' == $object_id && 'options_page' == $cmb->cmb_id ) {

        return '<form class="cmb-form" method="post" id="%1$s" enctype="multipart/form-data" encoding="multipart/form-data"><input type="hidden" name="object_id" value="%2$s">%3$s<div class="wp_job_board_pro-submit-wrap"><input type="button" name="submit-cmb-careerjet-job-import" value="' . __( 'Import Careerjet Jobs', 'wp-job-board-pro' ) . '" class="button-primary"></div></form>';
    }

    return $form_format;
}

WP_Job_Board_Pro_Careerjet_Jobs_Hooks::init();