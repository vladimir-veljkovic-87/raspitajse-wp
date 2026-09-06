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

class WP_Job_Board_Pro_Careerbuilder_Jobs_Hooks {

    private static $key = 'wp_job_board_pro_careerbuilder_import';

    public static function init() {
        
        // Job Fields
        add_filter( 'cmb2_meta_boxes', array( __CLASS__, 'job_meta_fields' ), 100 );

        add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );

        add_action( 'wp_ajax_wp_job_board_pro_ajax_careerbuilder_job_import', array( __CLASS__, 'process_import_jobs' ) );

        add_filter( 'wp-job-board-pro-get-company-name', array( __CLASS__, 'get_company_name' ) );

        add_filter( 'wp-job-board-pro-get-company-name-html', array( __CLASS__, 'get_company_name_html' ) );
    }

    public static function admin_menu() {
        if ( wp_job_board_pro_get_option('careerbuilder_job_import_enable') || (isset($_POST['careerbuilder_job_import_enable']) && $_POST['careerbuilder_job_import_enable'] == 'on') ) {
            add_submenu_page( 'edit.php?post_type=job_listing', esc_html__('Import Careerbuilder Jobs', 'wp-job-board-pro'), esc_html__('Import Careerbuilder Jobs', 'wp-job-board-pro'), 'manage_options', 'import-careerbuilder-jobs', array( __CLASS__, 'jobs_settings' ) );
        }
    }

    public static function jobs_settings() {
        ?>
        <div class="wrap wp_job_board_pro_settings_page cmb2_options_page">
            <h2><?php esc_html_e('Import Careerbuilder Jobs', 'wp-job-board-pro'); ?></h2>
            
            <?php cmb2_metabox_form( self::import_careerbuilder_fields(), self::$key ); ?>

        </div>

        <?php
    }

    public static function import_careerbuilder_fields() {
        $fields = array(
            'id'         => 'options_page',
            'wp_job_board_pro_title' => __( 'Careerbuilder Jobs Import', 'wp-job-board-pro' ),
            'show_on'    => array( 'key' => 'options-page', 'value' => array( self::$key, ), ),
            'fields'     => apply_filters( 'wp_job_board_pro_careerbuilder_job_import_fields', array(
                    
                    array(
                        'name'    => __( 'Keywords', 'wp-job-board-pro' ),
                        'desc'    => __( 'Enter job title, keywords or company name. Default keyword is all.', 'wp-job-board-pro' ),
                        'id'      => 'careerbuilder_job_import_keywords',
                        'type'    => 'text',
                    ),
                    array(
                        'name'    => __( 'Location', 'wp-job-board-pro' ),
                        'desc'    => __( 'Enter a location for search.', 'wp-job-board-pro' ),
                        'id'      => 'careerbuilder_job_import_location',
                        'type'    => 'text',
                    ),
                    array(
                        'name'    => __( 'Number of jobs', 'wp-job-board-pro' ),
                        'desc'    => __( 'Enter number of jobs.', 'wp-job-board-pro' ),
                        'id'      => 'careerbuilder_job_import_number_jobs',
                        'type'    => 'text',
                        'default' => '10',
                    ),
                    array(
                        'name'    => __( 'Expired on', 'wp-job-board-pro' ),
                        'desc'    => __( 'Enter number of days (numeric format) for expiray date after job posted date.', 'wp-job-board-pro' ),
                        'id'      => 'careerbuilder_job_import_expired_on',
                        'type'    => 'text',
                        'default' => '0',
                    ),
                    array(
                        'name'    => __( 'Posted By Type', 'wp-job-board-pro' ),
                        'id'      => 'careerbuilder_job_import_posted_by_type',
                        'type'    => 'select',
                        'options' => array(
                            'auto'  => __( 'Auto Generate', 'wp-job-board-pro' ),
                            'employer' => __( 'Choose A Employer', 'wp-job-board-pro' ),
                        ),
                        'default' => 'auto',
                    ),
                    array(
                        'name'          => __( 'Posted By', 'wp-job-board-pro' ),
                        'id'            => 'careerbuilder_job_import_posted_by',
                        'type'          => 'user_ajax_search',
                        'query_args'    => array(
                            'role'              => array( 'wp_job_board_pro_employer' ),
                            'search_columns'    => array( 'user_login', 'user_email' )
                        ),
                        'desc'    => __( 'Choose an employer for job author.', 'wp-job-board-pro' ),
                    )
                )
            )        
        );
        return $fields;
    }

    public static function job_meta_fields( $metaboxes ) {
        if ( wp_job_board_pro_get_option('careerbuilder_job_import_enable') ) {
            $prefix = WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX;

            $metaboxes[ $prefix . 'careerbuilder_job_fields' ] = array(
                'id'                        => $prefix . 'careerbuilder_job_fields',
                'title'                     => __( 'Careerbuilder Job Fields', 'wp-job-board-pro' ),
                'object_types'              => array( 'job_listing' ),
                'context'                   => 'normal',
                'priority'                  => 'high',
                'show_names'                => true,
                'show_in_rest'              => true,
                'fields'                    => array(
                    array(
                        'name'              => __( 'Job Detail Url', 'wp-job-board-pro' ),
                        'id'                => WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'careerbuilder_detail_url',
                        'type'              => 'text',
                    ),
                    array(
                        'name'              => __( 'Company Name', 'wp-job-board-pro' ),
                        'id'                => WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'careerbuilder_company_name',
                        'type'              => 'text',
                    ),
                ),
            );
        }
        return $metaboxes;
    }

    public static function process_import_jobs() {
        $search_keywords = !empty($_POST['careerbuilder_job_import_keywords']) ? sanitize_text_field(stripslashes($_POST['careerbuilder_job_import_keywords'])) : '';
        $search_location = !empty($_POST['careerbuilder_job_import_location']) ? sanitize_text_field(stripslashes($_POST['careerbuilder_job_import_location'])) : '';
        $number_jobs = !empty($_POST['careerbuilder_job_import_number_jobs']) ? sanitize_text_field($_POST['careerbuilder_job_import_number_jobs']) : '';
        $expired_on = !empty($_POST['careerbuilder_job_import_expired_on']) ? sanitize_text_field($_POST['careerbuilder_job_import_expired_on']) : '';

        $posted_by_type = !empty($_POST['careerbuilder_job_import_posted_by_type']) ? sanitize_text_field($_POST['careerbuilder_job_import_posted_by_type']) : '';
        $posted_by = !empty($_POST['careerbuilder_job_import_posted_by']) ? sanitize_text_field($_POST['careerbuilder_job_import_posted_by']) : '';


        if ($per_page < 0) {
            $per_page = 10;
        }

        $api_args = array(
            'search' => $search_keywords,
            'location' => $search_location,
            'jobs_per_page' => $number_jobs,
        );

        $careerbuilder_jobs = WP_Job_Board_Pro_Careerbuilder_API::get_jobs($api_args);
        
        if (isset($careerbuilder_jobs['error']) && $careerbuilder_jobs['error'] != '') {
            $json = array(
                'status' => false,
                'msg' => $careerbuilder_jobs['error'],
            );
            echo json_encode($json);
            die();
        } elseif (empty($careerbuilder_jobs)) {
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
            foreach ($careerbuilder_jobs as $careerbuilder_job) {
                
                $job_url = isset($careerbuilder_job['url']) ? $careerbuilder_job['url'] : '';

                $existing_id = WP_Job_Board_Pro_Mixes::get_post_id_by_meta_value( WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX.'careerbuilder_detail_url', $job_url);

                if ( !empty($existing_id) ) {
                    continue;
                }

                $post_data = array(
                    'post_type' => 'job_listing',
                    'post_title' => isset($careerbuilder_job['title']) ? $careerbuilder_job['title'] : '',
                    'post_content' => isset($careerbuilder_job['tagline']) ? $careerbuilder_job['tagline'] : '',
                    'post_status' => 'publish'
                );
                if ( $post_author ) {
                    $post_data['post_author'] = $post_author;
                } elseif ( $posted_by_type == 'auto' && !empty($careerbuilder_job['company']) ) {
                    $user_id = WP_Job_Board_Pro_User::generate_user_by_post_name($careerbuilder_job['company']);
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
                if (!empty($careerbuilder_job['location'])) {
                    $location_addrs['address'] = $careerbuilder_job['location'];
                    add_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'address', $careerbuilder_job['location'], true);
                } else {
                    $location_addrs['address'] = '';
                }

                // Insert job latitude meta key
                if (!empty($careerbuilder_job['latitude'])) {
                    add_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'map_location_latitude', esc_attr($careerbuilder_job['latitude']), true);
                    $location_addrs['latitude'] = $careerbuilder_job['latitude'];
                }

                // Insert job longitude meta key
                if (!empty($careerbuilder_job['longitude'])) {
                    add_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'map_location_longitude', esc_attr($careerbuilder_job['longitude']), true);
                    $location_addrs['longitude'] = $careerbuilder_job['longitude'];
                }

                add_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'map_location', $location_addrs, true);

                // Insert job referral meta key
                add_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'job_referral', 'careerbuilder', true);

                // Insert job detail url meta key
                if ( $job_url ) {
                    add_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'careerbuilder_detail_url', $job_url, true);

                    update_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'apply_type', 'external', true);
                    update_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'apply_url', $job_url, true);
                }

                // Insert job comapny name meta key
                if ( !empty($careerbuilder_job['company']) ) {
                    add_post_meta($post_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'careerbuilder_company_name', $careerbuilder_job['company'], true);
                }

                // Create and assign taxonomy to post
                if ( !empty($careerbuilder_job['type']) ) {
                    wp_insert_term($careerbuilder_job['type'], 'job_listing_type');
                    $term = get_term_by('name', $careerbuilder_job['type'], 'job_listing_type');
                    wp_set_post_terms($post_id, $term->term_id, 'job_listing_type');
                }
                
            }
            $json = array(
                'status' => false,
                'msg' => sprintf(__('%s careerbuilder jobs are imported successfully.', 'wp-job-board-pro'), count($careerbuilder_jobs))
            );
            echo json_encode($json);
            die();
        }
        die();
    }

    public static function get_company_name($ouput, $post) {
        $job_referral = get_post_meta($post->ID, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'job_referral', true);
        $careerbuilder_company_name = get_post_meta($post->ID, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'careerbuilder_company_name', true);
        if ($job_referral == 'careerbuilder' && $careerbuilder_company_name != '') {
            $ouput = $careerbuilder_company_name;
        }
        return $ouput;
    }

    public static function get_company_name_html($ouput, $post) {
        $job_referral = get_post_meta($post->ID, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'job_referral', true);
        $careerbuilder_company_name = get_post_meta($post->ID, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'careerbuilder_company_name', true);
        if ($job_referral == 'careerbuilder' && $careerbuilder_company_name != '') {
            $ouput = sprintf(wp_kses(__('<span class="employer text-theme">%s</span>', 'wp-job-board-pro'), array( 'span' => array('class' => array()) ) ), $careerbuilder_company_name );
        }
        return $ouput;
    }

    public static function view_more_btn($post) {
        $ouput = '';
        $job_referral = get_post_meta($post->ID, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'job_referral', true);
        $careerbuilder_detail_url = get_post_meta($post->ID, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'careerbuilder_detail_url', true);
        if ($job_referral == 'careerbuilder' && $careerbuilder_detail_url != '') {
            $ouput = '<div class="view-more-link"><a class="btn btn-theme" href="' . $careerbuilder_detail_url . '">' . esc_html__('view more', 'wp-job-board-pro') . '</a></div>';
        }
        return $ouput;
    }
}

add_filter( 'cmb2_get_metabox_form_format', 'wp_job_board_pro_careerbuilder_modify_cmb2_form_output', 10, 3 );

function wp_job_board_pro_careerbuilder_modify_cmb2_form_output( $form_format, $object_id, $cmb ) {
    if ( 'wp_job_board_pro_careerbuilder_import' == $object_id && 'options_page' == $cmb->cmb_id ) {

        return '<form class="cmb-form" method="post" id="%1$s" enctype="multipart/form-data" encoding="multipart/form-data"><input type="hidden" name="object_id" value="%2$s">%3$s<div class="wp_job_board_pro-submit-wrap"><input type="button" name="submit-cmb-careerbuilder-job-import" value="' . __( 'Import Careerbuilder Jobs', 'wp-job-board-pro' ) . '" class="button-primary"></div></form>';
    }

    return $form_format;
}

WP_Job_Board_Pro_Careerbuilder_Jobs_Hooks::init();