<?php
/**
 * Fields Manager
 *
 * @package    wp-job-board-pro
 * @author     Habq
 * @license    GNU General Public License, version 3
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
 
class WP_Job_Board_Pro_Fields_Manager {

	public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'register_page' ), 1 );
        add_action( 'init', array(__CLASS__, 'init_hook'), 10 );
	}

    public static function register_page() {
        add_submenu_page( 'edit.php?post_type=job_listing', __( 'Fields Manager', 'wp-job-board-pro' ), __( 'Fields Manager', 'wp-job-board-pro' ), 'manage_options', 'job_listing-manager-fields-manager', array( __CLASS__, 'output_job_fields' ), 9 );

        add_submenu_page( 'edit.php?post_type=employer', __( 'Fields Manager', 'wp-job-board-pro' ), __( 'Fields Manager', 'wp-job-board-pro' ), 'manage_options', 'employer-manager-fields-manager', array( __CLASS__, 'output_employer_fields' ), 9 );

        add_submenu_page( 'edit.php?post_type=candidate', __( 'Fields Manager', 'wp-job-board-pro' ), __( 'Fields Manager', 'wp-job-board-pro' ), 'manage_options', 'candidate-manager-fields-manager', array( __CLASS__, 'output_candidate_fields' ), 9 );
    }

    public static function init_hook() {
        // Ajax endpoints.
        add_action( 'wjbp_ajax_wp_job_board_pro_custom_field_html', array( __CLASS__, 'custom_field_html' ) );

        add_action( 'wjbp_ajax_wp_job_board_pro_custom_field_available_html', array( __CLASS__, 'custom_field_available_html' ) );

        // compatible handlers.
        // custom fields
        add_action( 'wp_ajax_wp_job_board_pro_custom_field_html', array( __CLASS__, 'custom_field_html' ) );
        add_action( 'wp_ajax_nopriv_wp_job_board_pro_custom_field_html', array( __CLASS__, 'custom_field_html' ) );

        add_action( 'wp_ajax_wp_job_board_pro_custom_field_available_html', array( __CLASS__, 'custom_field_available_html' ) );
        add_action( 'wp_ajax_nopriv_wp_job_board_pro_custom_field_available_html', array( __CLASS__, 'custom_field_available_html' ) );

        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'scripts' ), 1 );
    }

    public static function scripts() {
        wp_enqueue_style('wp-job-board-pro-custom-field-css', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/admin/style.css');
        
        // icon
        if ( !empty($_GET['page']) && ($_GET['page'] == 'job_listing-manager-fields-manager' || $_GET['page'] == 'employer-manager-fields-manager' || $_GET['page'] == 'candidate-manager-fields-manager') ) {
            wp_enqueue_style('jquery-fonticonpicker', WP_JOB_BOARD_PRO_PLUGIN_URL. 'assets/admin/jquery.fonticonpicker.min.css', array(), '1.0');
            wp_enqueue_style('jquery-fonticonpicker-bootstrap', WP_JOB_BOARD_PRO_PLUGIN_URL. 'assets/admin/jquery.fonticonpicker.bootstrap.min.css', array(), '1.0');
            wp_enqueue_script('jquery-fonticonpicker', WP_JOB_BOARD_PRO_PLUGIN_URL. 'assets/admin/jquery.fonticonpicker.min.js', array(), '1.0', true);

            
            wp_register_script('wp-job-board-pro-custom-field', WP_JOB_BOARD_PRO_PLUGIN_URL.'assets/admin/functions.js', array('jquery', 'wp-color-picker'), '', true);

            $args = array(
                'plugin_url' => WP_JOB_BOARD_PRO_PLUGIN_URL,
                'ajax_url' => admin_url('admin-ajax.php'),
            );
            wp_localize_script('wp-job-board-pro-custom-field', 'wp_job_board_pro_customfield_common_vars', $args);
            wp_enqueue_script('wp-job-board-pro-custom-field');

            wp_enqueue_script('jquery-ui-sortable');
        }
    }

    public static function output_html($prefix = WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX) {
        
        self::save($prefix);
        $rand_id = rand(123, 9878787);
        $default_fields = self::get_all_field_types();
        $post_type = '';
        if ( $prefix == WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX ) {
            $available_fields = self::get_all_types_job_listing_fields_available();
            $required_types = self::get_all_types_job_listing_fields_required();
            $post_type = 'job_listing';
        } elseif ( $prefix == WP_JOB_BOARD_PRO_EMPLOYER_PREFIX ) {
            $available_fields = self::get_all_types_employer_fields_available();
            $required_types = self::get_all_types_employer_fields_required();
            $post_type = 'employer';
        } elseif ( $prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX ) {
            $available_fields = self::get_all_types_candidate_fields_available();
            $required_types = self::get_all_types_candidate_fields_required();
            $post_type = 'candidate';
        }

        $custom_all_fields_saved_data = self::get_custom_fields_data($prefix);

        ?>
        <h1><?php echo esc_html__('Fields manager', 'wp-job-board-pro'); ?></h1>

        <form class="job_listing-manager-options" method="post" action="" data-prefix="<?php echo esc_attr($prefix); ?>">
            
            <button type="submit" class="button button-primary" name="updateWPJBFieldManager"><?php esc_html_e('Update', 'wp-job-board-pro'); ?></button>
            
            <div class="custom-fields-wrapper clearfix">
                            
                <div class="wp-job-board-pro-custom-field-form" id="wp-job-board-pro-custom-field-form-<?php echo esc_attr($rand_id); ?>">
                    <div class="box-wrapper">
                        <h3 class="title"><?php echo esc_html('List of Fields', 'wp-job-board-pro'); ?></h3>
                        <ul id="foo<?php echo esc_attr($rand_id); ?>" class="block__list block__list_words"> 
                            <?php

                            $count_node = 1000;
                            $output = '';
                            $all_fields_name_count = 0;
                            $disabled_fields = array();

                            if (is_array($custom_all_fields_saved_data) && sizeof($custom_all_fields_saved_data) > 0) {
                                $field_names_counter = 0;
                                $types = self::get_all_field_type_keys();
                                foreach ($custom_all_fields_saved_data as $key => $custom_field_saved_data) {
                                    $all_fields_name_count++;
                                    
                                    $li_rand_id = rand(454, 999999);

                                    $output .= '<li class="custom-field-class-' . $li_rand_id . '">';

                                    $fieldtype = $custom_field_saved_data['type'];

                                    $delete = true;
                                    $drfield_values = self::get_field_id($fieldtype, $required_types);
                                    $dvfield_values = self::get_field_id($fieldtype, $available_fields);

                                    if ( !empty($drfield_values) ) {
                                        $count_node ++;
                                        
                                        $delete = false;
                                        $field_values = wp_parse_args( $custom_field_saved_data, $drfield_values);
                                        if ( in_array( $fieldtype, array( $prefix.'title', $prefix.'expiry_date', $prefix.'featured', $prefix.'urgent', $prefix.'filled', $prefix.'posted_by', $prefix.'attached_user' ) ) ) {
                                            $output .= apply_filters('wp_job_board_pro_custom_field_available_simple_html', $fieldtype, $count_node, $field_values, $prefix);
                                        } elseif ( in_array( $fieldtype, array( $prefix.'description' ) ) ) {
                                            $output .= apply_filters('wp_job_board_pro_custom_field_available_description_html', $fieldtype, $count_node, $field_values, $prefix);
                                        } else {
                                            $output .= apply_filters('wp_job_board_pro_custom_field_available_'.$fieldtype.'_html', $fieldtype, $count_node, $field_values, $prefix);
                                        }
                                    } elseif ( !empty($dvfield_values) ) {
                                        $count_node ++;
                                        $field_values = wp_parse_args( $custom_field_saved_data, $dvfield_values);

                                        $dtypes = apply_filters( 'wp_job_board_pro_list_simple_type', array( $prefix.'featured', $prefix.'urgent', $prefix.'address', $prefix.'salary', $prefix.'max_salary', $prefix.'application_deadline_date', $prefix.'apply_url', $prefix.'apply_email', $prefix.'video', $prefix.'profile_url', $prefix.'email', $prefix.'founded_date', $prefix.'website', $prefix.'phone', $prefix.'video_url', $prefix.'socials', $prefix.'team_members', $prefix.'employees', $prefix.'show_profile', $prefix.'job_title', WP_JOB_BOARD_PRO_CANDIDATE_PREFIX.'experience', $prefix.'education', $prefix.'award', $prefix.'skill', $prefix.'tag', $prefix.'company_size' ) );

                                        if ( in_array( $fieldtype, $dtypes) ) {
                                            $output .= apply_filters('wp_job_board_pro_custom_field_available_simple_html', $fieldtype, $count_node, $field_values, $prefix);
                                        } elseif ( in_array( $fieldtype, array( $prefix.'category', $prefix.'type' ) ) ) {
                                            $output .= apply_filters('wp_job_board_pro_custom_field_available_tax_html', $fieldtype, $count_node, $field_values, $prefix);
                                        } elseif ( in_array($fieldtype, array( $prefix.'featured_image', $prefix.'logo', $prefix.'gallery', $prefix.'attachments', $prefix.'cover_photo', $prefix.'profile_photos', $prefix.'portfolio_photos', $prefix.'cv_attachment', $prefix.'photos' ) )) {
                                            $output .= apply_filters('wp_job_board_pro_custom_field_available_files_html', $fieldtype, $count_node, $field_values, $prefix);
                                        }  elseif ( in_array($fieldtype, array( $prefix.'experience_time', $prefix.'experience', $prefix.'gender', $prefix.'industry', $prefix.'qualification', $prefix.'career_level', $prefix.'age', $prefix.'languages') )) {
                                            $output .= apply_filters( 'wp_job_board_pro_custom_field_available_select_option_html', $fieldtype, $count_node, $field_values, $prefix );
                                        } elseif ( in_array( $fieldtype, array( $prefix.'salary_type') ) ) {
                                            $output .= apply_filters('wp_job_board_pro_custom_field_available_salary_type_html', $fieldtype, $count_node, $field_values, $prefix);
                                        } elseif ( in_array( $fieldtype, array( $prefix.'apply_type') ) ) {
                                            $output .= apply_filters('wp_job_board_pro_custom_field_available_apply_type_html', $fieldtype, $count_node, $field_values, $prefix);
                                        } elseif ( in_array($fieldtype, array( $prefix.'location' ) )) {
                                            $output .= apply_filters( 'wp_job_board_pro_custom_field_available_location_html', $fieldtype, $count_node, $field_values, $prefix );
                                        } else {
                                            $output .= apply_filters('wp_job_board_pro_custom_field_available_'.$fieldtype.'_html', $fieldtype, $count_node, $field_values, $prefix);
                                        }
                                        $disabled_fields[] = $fieldtype;
                                    } elseif ( in_array($fieldtype, $types) ) {
                                        $count_node ++;
                                        if ( in_array( $fieldtype, array('text', 'textarea', 'wysiwyg', 'number', 'url', 'email', 'checkbox') ) ) {
                                            $output .= apply_filters('wp_job_board_pro_custom_field_text_html', $fieldtype, $count_node, $custom_field_saved_data, $prefix);
                                        } elseif ( in_array( $fieldtype, array('select', 'multiselect', 'radio') ) ) {
                                            $output .= apply_filters('wp_job_board_pro_custom_field_opts_html', $fieldtype, $count_node, $custom_field_saved_data, $prefix);
                                        } else {
                                            $output .= apply_filters('wp_job_board_pro_custom_field_'.$fieldtype.'_html', $fieldtype, $count_node, $custom_field_saved_data, $prefix);
                                        }
                                    }

                                    $output .= apply_filters('wp_job_board_pro_custom_field_actions_html', $li_rand_id, $count_node, $fieldtype, $delete);
                                    $output .= '</li>';
                                }
                            } else {
                                foreach ($required_types as $field_values) {
                                    $count_node ++;
                                    $li_rand_id = rand(454, 999999);
                                    $output .= '<li class="custom-field-class-' . $li_rand_id . '">';
                                    $output .= apply_filters('wp_job_board_pro_custom_field_available_simple_html', $field_values['id'], $count_node, $field_values, $prefix);

                                    $output .= apply_filters('wp_job_board_pro_custom_field_actions_html', $li_rand_id, $count_node, $field_values['id'], false);
                                    $output .= '</li>';
                                }
                            }
                            echo force_balance_tags($output);
                            ?>
                        </ul>

                        <button type="submit" class="button button-primary" name="updateWPJBFieldManager"><?php esc_html_e('Update', 'wp-job-board-pro'); ?></button>

                        <div class="input-field-types">
                            <h3><?php esc_html_e('Create a custom field', 'wp-job-board-pro'); ?></h3>
                            <div class="input-field-types-wrapper">
                                <select name="field-types" class="wp-job-board-pro-field-types">
                                    <?php foreach ($default_fields as $group) { ?>
                                        <optgroup label="<?php echo esc_attr($group['title']); ?>">
                                            <?php foreach ($group['fields'] as $value => $label) { ?>
                                                <option value="<?php echo esc_attr($value); ?>"><?php echo $label; ?></option>
                                            <?php } ?>
                                        </optgroup>
                                    <?php } ?>
                                </select>
                                <button type="button" class="button btn-add-field" data-randid="<?php echo esc_attr($rand_id); ?>"><?php esc_html_e('Create', 'wp-job-board-pro'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wp-job-board-pro-form-field-list wp-job-board-pro-list">
                    <h3 class="title"><?php esc_html_e('Available Fields', 'wp-job-board-pro'); ?></h3>
                    <?php if ( !empty($available_fields) ) { ?>
                        <ul>
                            <?php foreach ($available_fields as $field) { ?>
                                <li class="<?php echo esc_attr($field['id']); ?> <?php echo esc_attr(in_array($field['id'], $disabled_fields) ? 'disabled' : ''); ?>">
                                    <a class="wp-job-board-pro-custom-field-add-available-field" data-fieldtype="<?php echo esc_attr($field['id']); ?>" data-randid="<?php echo esc_attr($rand_id); ?>" href="javascript:void(0);" data-fieldlabel="<?php echo esc_attr($field['name']); ?>">
                                        <span class="icon-wrapper">
                                            <i class="dashicons dashicons-plus"></i>
                                        </span>
                                        <?php echo esc_html($field['name']); ?>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    <?php } ?>
                </div>
                <div class="clearfix" style="clear: both;"></div>
            </div>

            <script>
                var global_custom_field_counter = <?php echo intval($all_fields_name_count); ?>;
                jQuery(document).ready(function () {
                    
                    jQuery('#foo<?php echo esc_attr($rand_id); ?>').sortable({
                        group: "words",
                        animation: 150,
                        handle: ".field-intro",
                        cancel: ".form-group-wrapper"
                    });
                });
            </script>
        </form>
        <?php
    }

    public static function output_job_fields() {
        self::output_html(WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX);
    }

    public static function output_employer_fields() {
        self::output_html(WP_JOB_BOARD_PRO_EMPLOYER_PREFIX);
    }

    public static function output_candidate_fields() {
        self::output_html(WP_JOB_BOARD_PRO_CANDIDATE_PREFIX);
    }

    public static function save($prefix) {
        if ( isset( $_POST['updateWPJBFieldManager'] ) ) {

            $custom_field_final_array = $counts = array();
            $field_index = 0;
            if ( !empty($_POST['wp-job-board-pro-custom-fields-type']) ) {
                foreach ($_POST['wp-job-board-pro-custom-fields-type'] as $field_type) {
                    $custom_fields_id = isset($_POST['wp-job-board-pro-custom-fields-id'][$field_index]) ? $_POST['wp-job-board-pro-custom-fields-id'][$field_index] : '';
                    $counter = 0;
                    if ( isset($counts[$field_type]) ) {
                        $counter = $counts[$field_type];
                    }
                    $custom_field_final_array[] = self::custom_field_ready_array($counter, $field_type, $custom_fields_id);
                    $counter++;
                    $counts[$field_type] = $counter;
                    $field_index++;
                }
            }
            $option_key = self::get_custom_fields_key($prefix);

            update_option($option_key, $custom_field_final_array);
            
        }
    }

    public static function custom_field_ready_array($array_counter = 0, $field_type = '', $custom_fields_id = '') {
        $custom_field_element_array = array();
        $custom_field_element_array['type'] = $field_type;
        if ( !empty($_POST["wp-job-board-pro-custom-fields-{$field_type}"]) ) {
            foreach ($_POST["wp-job-board-pro-custom-fields-{$field_type}"] as $field => $value) {
                if ( isset($value[$custom_fields_id]) ) {
                    $custom_field_element_array[$field] = $value[$custom_fields_id];
                } elseif ( isset($value[$array_counter]) ) {
                    $custom_field_element_array[$field] = $value[$array_counter];
                }
            }
        }
        return $custom_field_element_array;
    }

    public static function get_custom_fields_data($prefix) {
        $option_key = self::get_custom_fields_key($prefix);
        return apply_filters( 'wp-job-board-pro-get-custom-fields-data', get_option($option_key, array()), $prefix );
    }

    public static function get_custom_fields_key($prefix) {
        return apply_filters( 'wp-job-board-pro-get-custom-fields-key', 'wp_job_board_pro_'.$prefix.'_fields_data', $prefix );
    }

    public static function get_field_id($id, $fields) {
        if ( !empty($fields) && is_array($fields) ) {
            foreach ($fields as $field) {
                if ( $field['id'] == $id ) {
                    return $field;
                }
            }
        }
        return array();
    }

    public static function get_all_field_types() {
        $fields = apply_filters( 'wp_job_board_pro_get_default_field_types', array(
            array(
                'title' => esc_html__('Direct Input', 'wp-job-board-pro'),
                'fields' => array(
                    'text' => esc_html__('Text', 'wp-job-board-pro'),
                    'textarea' => esc_html__('Textarea', 'wp-job-board-pro'),
                    'wysiwyg' => esc_html__('WP Editor', 'wp-job-board-pro'),
                    'date' => esc_html__('Date', 'wp-job-board-pro'),
                    'number' => esc_html__('Number', 'wp-job-board-pro'),
                    'url' => esc_html__('Url', 'wp-job-board-pro'),
                    'email' => esc_html__('Email', 'wp-job-board-pro'),
                )
            ),
            array(
                'title' => esc_html__('Choices', 'wp-job-board-pro'),
                'fields' => array(
                    'select' => esc_html__('Select', 'wp-job-board-pro'),
                    'multiselect' => esc_html__('Multiselect', 'wp-job-board-pro'),
                    'checkbox' => esc_html__('Checkbox', 'wp-job-board-pro'),
                    'radio' => esc_html__('Radio Buttons', 'wp-job-board-pro'),
                )
            ),
            array(
                'title' => esc_html__('Form UI', 'wp-job-board-pro'),
                'fields' => array(
                    'heading' => esc_html__('Heading', 'wp-job-board-pro')
                )
            ),
            array(
                'title' => esc_html__('Others', 'wp-job-board-pro'),
                'fields' => array(
                    'file' => esc_html__('File', 'wp-job-board-pro')
                )
            ),
        ));
        
        return $fields;
    }

    public static function get_all_field_type_keys() {
        $fields = self::get_all_field_types();
        $return = array();
        foreach ($fields as $group) {
            foreach ($group['fields'] as $key => $value) {
                $return[] = $key;
            }
        }

        return apply_filters( 'wp_job_board_pro_get_all_field_types', $return );
    }

    public static function get_all_types_job_listing_fields_required() {
        $datepicker_date_format = str_replace(
            array( 'd', 'j', 'l', 'z', /* Day. */ 'F', 'M', 'n', 'm', /* Month. */ 'Y', 'y', /* Year. */ ),
            array( 'dd', 'd', 'DD', 'o', 'MM', 'M', 'm', 'mm', 'yy', 'y', ),
            get_option( 'date_format' )
        );

        $prefix = WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX;
        $fields = array(
            array(
                'name'              => __( 'Job Title', 'wp-job-board-pro' ),
                'id'                => $prefix . 'title',
                'type'              => 'text',
                'disable_check' => true,
                'required' => true,
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_input'),
                'show_compare'      => true
            ),
            array(
                'name'              => __( 'Job Description', 'wp-job-board-pro' ),
                'id'                => $prefix . 'description',
                'type'              => 'textarea',
                'options'           => array(
                    'media_buttons' => false,
                    'textarea_rows' => 8,
                    'wpautop' => true,
                    'tinymce'       => array(
                        'plugins'                       => 'lists,paste,tabfocus,wplink,wordpress',
                        'paste_as_text'                 => true,
                        'paste_auto_cleanup_on_paste'   => true,
                        'paste_remove_spans'            => true,
                        'paste_remove_styles'           => true,
                        'paste_remove_styles_if_webkit' => true,
                        'paste_strip_class_attributes'  => true,
                    ),
                ),
                'disable_check' => true,
                'required' => true,
                'show_compare'      => true
            ),
            array(
                'name'              => __( 'Expiry Date', 'wp-job-board-pro' ),
                'id'                => $prefix . 'expiry_date',
                'type'              => 'wjbp_datepicker',
                'date_format'       => 'Y-m-d',
                'disable_check' => true,
                'show_in_submit_form' => '',
                'show_in_admin_edit' => 'yes',
                'attributes' => array(
                    'data-datepicker' => json_encode(array(
                        'dateFormat' => $datepicker_date_format,
                        'altField' => '#'.$prefix . 'expiry_date',
                        'altFormat' => 'yy-mm-dd',
                    ))
                )
            ),
            array(
                'name'              => __( 'Featured Job', 'wp-job-board-pro' ),
                'id'                => $prefix . 'featured',
                'type'              => 'checkbox',
                'description'       => __( 'Featured properties will be sticky during searches, and can be styled differently.', 'wp-job-board-pro' ),
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_checkbox'),
                'disable_check' => true,
                'show_in_submit_form' => '',
                'show_in_admin_edit' => 'yes',
            ),
            array(
                'name'              => __( 'Urgent Job', 'wp-job-board-pro' ),
                'id'                => $prefix . 'urgent',
                'type'              => 'checkbox',
                'description'       => __( 'Urgent jobs will be sticky during searches, and can be styled differently.', 'wp-job-board-pro' ),
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_checkbox'),
                'disable_check' => true,
                'show_in_submit_form' => '',
                'show_in_admin_edit' => 'yes',
            ),
            array(
                'name'              => __( 'Filled', 'wp-job-board-pro' ),
                'id'                => $prefix . 'filled',
                'type'              => 'checkbox',
                'description'       => __( 'Filled listings will no longer accept applications.', 'wp-job-board-pro' ),
                'disable_check' => true,
                'show_in_submit_form' => '',
                'show_in_admin_edit' => 'yes',
            ),
        );
        return apply_filters( 'wp-job-board-pro-job_listing-type-required-fields', $fields );
    }

    public static function get_all_types_job_listing_fields_available() {
        $currency_symbol = wp_job_board_pro_get_option('currency_symbol', '$');
        $area_unit = wp_job_board_pro_get_option('measurement_unit_area', 'sqft');

        $datepicker_date_format = str_replace(
            array( 'd', 'j', 'l', 'z', /* Day. */ 'F', 'M', 'n', 'm', /* Month. */ 'Y', 'y', /* Year. */ ),
            array( 'dd', 'd', 'DD', 'o', 'MM', 'M', 'm', 'mm', 'yy', 'y', ),
            get_option( 'date_format' )
        );

        $prefix = WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX;
        $fields = array(
            array(
                'name'              => __( 'Banner Image', 'wp-job-board-pro' ),
                'id'                => $prefix . 'featured_image',
                'type'              => 'wp_job_board_pro_file',
                'ajax'              => true,
                'multiple_files'    => false,
                'mime_types'        => array( 'gif', 'jpeg', 'jpg', 'jpg|jpeg|jpe', 'png' ),
            ),
            array(
                'name'              => __( 'Logo Image', 'wp-job-board-pro' ),
                'id'                => $prefix . 'logo',
                'type'              => 'wp_job_board_pro_file',
                'file_multiple'     => false,
                'ajax'              => true,
                'mime_types'        => array( 'gif', 'jpeg', 'jpg', 'png' ),
            ),
            array(
                'name'              => __( 'Application Deadline Date', 'wp-job-board-pro' ),
                'id'                => $prefix . 'application_deadline_date',
                'type'              => 'wjbp_datepicker',
                'attributes' => array(
                    'data-datepicker' => json_encode(array(
                        'dateFormat' => $datepicker_date_format,
                        'altField' => '#'.$prefix . 'application_deadline_date',
                        'altFormat' => 'yy-mm-dd',
                    ))
                )
            ),
            array(
                'name'              => __( 'Job Apply Type', 'wp-job-board-pro' ),
                'id'                => $prefix . 'apply_type',
                'type'              => 'select',
                'options'           => WP_Job_Board_Pro_Mixes::get_default_apply_types()
            ),
            array(
                'name'              => __( 'External URL for Apply Job', 'wp-job-board-pro' ),
                'id'                => $prefix . 'apply_url',
                'type'              => 'text',
            ),
            array(
                'name'              => __( 'Job Apply Email', 'wp-job-board-pro' ),
                'id'                => $prefix . 'apply_email',
                'type'              => 'text',
            ),
            array(
                'name'              => __( 'Phone Number', 'wp-job-board-pro' ),
                'id'                => $prefix . 'phone',
                'type'              => 'text',
            ),
            array(
                'name'              => __( 'Min. Salary', 'wp-job-board-pro' ),
                'id'                => $prefix . 'salary',
                'type'              => 'text',
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_job_salary'),
            ),
            array(
                'name'              => __( 'Max. Salary', 'wp-job-board-pro' ),
                'id'                => $prefix . 'max_salary',
                'type'              => 'text',
            ),
            array(
                'name'              => __( 'Salary Type', 'wp-job-board-pro' ),
                'id'                => $prefix . 'salary_type',
                'type'              => 'select',
                'options'           => WP_Job_Board_Pro_Mixes::get_default_salary_types(),
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_radio_list'),
            ),
            array(
                'name'              => __( 'Location', 'wp-job-board-pro' ),
                'id'                => $prefix . 'location',
                'type'              => 'wpjb_taxonomy_location',
                'taxonomy'          => 'job_listing_location',
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_location_select'),
                'show_compare'      => true
            ),
            array(
                'name'              => __( 'Friendly Address', 'wp-job-board-pro' ),
                'id'                => $prefix . 'address',
                'type'              => 'text',
            ),
            array(
                'id'                => $prefix . 'map_location',
                'name'              => __( 'Maps Location', 'wp-job-board-pro' ),
                'type'              => 'pw_map',
                'sanitization_cb'   => 'pw_map_sanitise',
                'split_values'      => true,
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_input_location'),
            ),
            

            // Taxonomies
            array(
                'name'              => __( 'Type', 'wp-job-board-pro' ),
                'id'                => $prefix . 'type',
                'type'              => 'pw_taxonomy_multiselect',
                'taxonomy'          => 'job_listing_type',
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_taxonomy_hierarchical_select'),
            ),
            array(
                'name'              => __( 'Category', 'wp-job-board-pro' ),
                'id'                => $prefix . 'category',
                'type'              => 'pw_taxonomy_multiselect',
                'taxonomy'          => 'job_listing_category',
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_taxonomy_hierarchical_select'),
            ),
            array(
                'name'              => __( 'Tag', 'wp-job-board-pro' ),
                'id'                => $prefix . 'tag',
                'type'              => 'pw_taxonomy_multiselect',
                'taxonomy'          => 'job_listing_tag',
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_taxonomy_hierarchical_check_list'),
            ),
            
            // custom
            array(
                'name'              => __( 'Experience', 'wp-job-board-pro' ),
                'id'                => $prefix . 'experience',
                'type'              => 'select',
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_select'),
                'options' => 'Fresh
1 Year
2 Year
3 Year
4 Year
5 Year'
            ),
            array(
                'name'              => __( 'Gender', 'wp-job-board-pro' ),
                'id'                => $prefix . 'gender',
                'type'              => 'select',
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_select'),
                'options' => 'Both
Female
Male'
            ),
            array(
                'name'              => __( 'Industry', 'wp-job-board-pro' ),
                'id'                => $prefix . 'industry',
                'type'              => 'select',
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_select'),
                'options' => 'Development
Management
Finance
Html & Css
Seo
Banking
Designer Graphics'
            ),
            array(
                'name'              => __( 'Qualification', 'wp-job-board-pro' ),
                'id'                => $prefix . 'qualification',
                'type'              => 'select',
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_select'),
                'options' => 'Certificate
Associate Degree
Bachelor Degree
Master’s Degree
Doctorate Degree'
            ),
            array(
                'name'              => __( 'Career Level', 'wp-job-board-pro' ),
                'id'                => $prefix . 'career_level',
                'type'              => 'select',
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_select'),
                'options' => 'Manager
Officer
Student
Executive
Others'
            ),
            array(
                'name'              => __( 'Photos', 'wp-job-board-pro' ),
                'id'                => $prefix . 'photos',
                'type'              => 'file_list',
                'file_multiple'     => true,
                'ajax'              => true,
                'multiple_files'    => true,
                'mime_types'        => array( 'gif', 'jpeg', 'jpg', 'png' ),
                'query_args' => array( 'type' => 'image' ), // Only images attachment
                'text' => array(
                    'add_upload_files_text' => __( 'Add or Upload Images', 'wp-job-board-pro' ),
                ),
            ),
            array(
                'name'              => __( 'Introduction Video URL', 'wp-job-board-pro' ),
                'id'                => $prefix . 'video_url',
                'type'              => 'text',
            ),
        );
        return apply_filters( 'wp-job-board-pro-job_listing-type-available-fields', $fields );
    }

    public static function get_all_types_employer_fields_required() {
        $prefix = WP_JOB_BOARD_PRO_EMPLOYER_PREFIX;
        $fields = array(
            array(
                'name'              => __( 'Employer name', 'wp-job-board-pro' ),
                'id'                => $prefix . 'title',
                'type'              => 'text',
                'default'           => '',
                'attributes'        => array(
                    'required'          => 'required'
                ),
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_input'),
            ),
            array(
                'name'              => __( 'Description', 'wp-job-board-pro' ),
                'id'                => $prefix . 'description',
                'type'              => 'wysiwyg',
                'options' => array(
                    'media_buttons' => false,
                    'textarea_rows' => 8,
                    'wpautop' => true,
                    'tinymce'       => array(
                        'plugins'                       => 'lists,paste,tabfocus,wplink,wordpress',
                        'paste_as_text'                 => true,
                        'paste_auto_cleanup_on_paste'   => true,
                        'paste_remove_spans'            => true,
                        'paste_remove_styles'           => true,
                        'paste_remove_styles_if_webkit' => true,
                        'paste_strip_class_attributes'  => true,
                    ),
                ),
            ),
            array(
                'name'              => __( 'Featured Employer', 'wp-job-board-pro' ),
                'id'                => $prefix . 'featured',
                'type'              => 'checkbox',
                'description'       => __( 'Featured employer will be sticky during searches, and can be styled differently.', 'wp-job-board-pro' ),
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_checkbox'),
                'disable_check' => true,
                'show_in_submit_form' => '',
                'show_in_admin_edit' => 'yes',
            ),
            array(
                'name'              => __( 'Attached User', 'wp-job-board-pro' ),
                'id'                => $prefix . 'attached_user',
                'type'              => 'wp_job_board_pro_attached_user',
                'disable_check' => true,
                'show_in_submit_form' => '',
                'show_in_admin_edit' => 'yes',
                'disable_check_register' => true,
            ),
        );
        return apply_filters( 'wp-job-board-pro-employer-type-required-fields', $fields );
    }

    public static function get_all_types_employer_fields_available() {
        $socials = WP_Job_Board_Pro_Mixes::get_socials_network();
        $opt_socials = [];
        foreach ($socials as $key => $value) {
            $opt_socials[$key] = $value['title'];
        }
        $prefix = WP_JOB_BOARD_PRO_EMPLOYER_PREFIX;
        $fields = array(
            array(
                'name'              => __( 'Profile url', 'wp-job-board-pro' ),
                'id'                => $prefix . 'profile_url',
                'type'              => 'wp_job_board_pro_profile_url',
                'disable_check' => true,
                'show_in_submit_form' => 'yes',
                'show_in_admin_edit' => '',
                'disable_check_register' => true,
            ),

            array(
                'name'              => __( 'Email', 'wp-job-board-pro' ),
                'id'                => $prefix . 'email',
                'type'              => 'text',
                'disable_check_register' => true,
            ),
            array(
                'name'              => __( 'Founded Date', 'wp-job-board-pro' ),
                'id'                => $prefix . 'founded_date',
                'type'              => 'text_small',
                'attributes'        => array(
                    'type'              => 'number',
                    'min'               => 0,
                    'pattern'           => '\d*',
                ),
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_found_date_range_slider'),
            ),
            array(
                'name'              => __( 'Website', 'wp-job-board-pro' ),
                'id'                => $prefix . 'website',
                'type'              => 'text',
            ),
            array(
                'name'              => __( 'Phone Number', 'wp-job-board-pro' ),
                'id'                => $prefix . 'phone',
                'type'              => 'text',
            ),
            array(
                'name'              => __( 'Logo Image', 'wp-job-board-pro' ),
                'id'                => $prefix . 'featured_image',
                'type'              => 'wp_job_board_pro_file',
                'file_multiple'         => false,
                'ajax'              => true,
                'mime_types'        => array( 'gif', 'jpeg', 'jpg', 'png' ),
            ),
            array(
                'name'              => __( 'Cover Photo', 'wp-job-board-pro' ),
                'id'                => $prefix . 'cover_photo',
                'type'              => 'file',
                'query_args' => array( 'type' => 'image' ),
                'file_multiple'         => false,
                'ajax'              => true,
                'mime_types'        => array( 'gif', 'jpeg', 'jpg', 'png' ),
            ),
            array(
                'name'              => __( 'Profile Photos', 'wp-job-board-pro' ),
                'id'                => $prefix . 'profile_photos',
                'type'              => 'file_list',
                'file_multiple'     => true,
                'ajax'              => true,
                'multiple_files'    => true,
                'mime_types'        => array( 'gif', 'jpeg', 'jpg', 'png' ),
                'query_args' => array( 'type' => 'image' ), // Only images attachment
                'text' => array(
                    'add_upload_files_text' => __( 'Add or Upload Images', 'wp-job-board-pro' ),
                ),
            ),
            array(
                'name'              => __( 'Introduction Video URL', 'wp-job-board-pro' ),
                'id'                => $prefix . 'video_url',
                'type'              => 'text',
            ),
            array(
                'name'              => __( 'Company Size', 'wp-job-board-pro' ),
                'id'                => $prefix . 'company_size',
                'type'              => 'text',
            ),
            array(
                'name'              => __( 'Socials', 'wp-job-board-pro' ),
                'id'                => $prefix . 'socials',
                'type'              => 'group',
                'options'           => array(
                    'group_title'       => __( 'Mreža {#}', 'wp-job-board-pro' ),
                    'add_button'        => __( 'Dodaj drugu mrežu', 'wp-job-board-pro' ),
                    'remove_button'     => __( 'Ukloni mrežu', 'wp-job-board-pro' ),
                    'sortable'          => false,
                    'closed'         => true,
                ),
                'fields'            => array(
                    array(
                        'name'      => __( 'Network', 'wp-job-board-pro' ),
                        'id'        => 'network',
                        'type'      => 'select',
                        'options'   => $opt_socials
                    ),
                    array(
                        'name'      => __( 'Url', 'wp-job-board-pro' ),
                        'id'        => 'url',
                        'type'      => 'text',
                    ),
                ),
            ),
            array(
                'name'              => __( 'Friendly Address', 'wp-job-board-pro' ),
                'id'                => $prefix . 'address',
                'type'              => 'text',
            ),
            array(
                'id'                => $prefix . 'map_location',
                'name'              => __( 'Maps Location', 'wp-job-board-pro' ),
                'type'              => 'pw_map',
                'sanitization_cb'   => 'pw_map_sanitise',
                'split_values'      => true,
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_input_location'),
            ),
            array(
                'name'              => __( 'Istaknuti članovi tima', 'wp-job-board-pro' ),
                'id'                => $prefix . 'team_members',
                'type'              => 'group',
                'options'           => array(
                    'group_title'       => __( 'Član tima {#}', 'wp-job-board-pro' ),
                    'add_button'        => __( 'Dodaj člana tima', 'wp-job-board-pro' ),
                    'remove_button'     => __( 'Ukloni člana tima', 'wp-job-board-pro' ),
                    'sortable'          => true,
                    'closed'         => true,
                ),
                'fields'            => array(
                    array(
                        'name'      => __( 'Ime', 'wp-job-board-pro' ),
                        'id'        => 'name',
                        'type'      => 'text',
                    ),
                    array(
                        'name'      => __( 'Pozicija u timu', 'wp-job-board-pro' ),
                        'id'        => 'designation',
                        'type'      => 'text',
                    ),
                    array(
                        'name'      => __( 'Iskustvo', 'wp-job-board-pro' ),
                        'id'        => 'experience',
                        'type'      => 'text',
                    ),
                    array(
                        'name'      => __( 'Profilna slika', 'wp-job-board-pro' ),
                        'id'        => 'profile_image',
                        'type'      => 'file',
                        'options' => array(
                            'url' => false, // Hide the text input for the url
                        ),
                        'text'    => array(
                            'add_upload_file_text' => __( 'Add Image', 'wp-job-board-pro' ),
                        ),
                        'query_args' => array(
                            'type' => array(
                                'image/gif',
                                'image/jpeg',
                                'image/png',
                            ),
                        ),
                        'file_multiple'         => false,
                        'ajax'              => true,
                        'mime_types'        => array( 'gif', 'jpeg', 'jpg', 'png' ),
                    ),
                    array(
                        'name'              => __( 'Facebook URL', 'wp-job-board-pro' ),
                        'id'                => 'facebook',
                        'type'              => 'text',
                    ),
                    array(
                        'name'              => __( 'Twitter URL', 'wp-job-board-pro' ),
                        'id'                => 'twitter',
                        'type'              => 'text',
                    ),
                    array(
                        'name'              => __( 'Google Plus URL', 'wp-job-board-pro' ),
                        'id'                => 'google_plus',
                        'type'              => 'text',
                    ),
                    array(
                        'name'              => __( 'Linkedin URL', 'wp-job-board-pro' ),
                        'id'                => 'linkedin',
                        'type'              => 'text',
                    ),
                    array(
                        'name'              => __( 'Dribbble URL', 'wp-job-board-pro' ),
                        'id'                => 'dribbble',
                        'type'              => 'text',
                    ),
                    array(
                        'name'              => __( 'Opis', 'wp-job-board-pro' ),
                        'id'                => 'description',
                        'type'              => 'textarea',
                    ),
                )
            ),
            array(
                'name'          => __( 'Employees', 'wp-job-board-pro' ),
                'id'            => $prefix . 'employees',
                'type'          => 'user_ajax_search',
                'multiple'      => true,
                'query_args'    => array(
                    'role'              => array( 'wp_job_board_pro_employee' ),
                    'search_columns'    => array( 'user_login', 'user_email' ),
                    'meta_query'        => array(
                        'relation' => 'OR',
                        array(
                            'key'       => 'employee_employer_id',
                            'value'     => '',
                        ),
                        array(
                            'key'       => 'employee_employer_id',
                            'compare' => 'NOT EXISTS',
                        )
                    )
                ),
                'disable_check_register' => true,
            ),

            // taxonimies
            array(
                'name'              => __( 'Categories', 'wp-job-board-pro' ),
                'id'                => $prefix . 'category',
                'type'              => 'pw_taxonomy_multiselect',
                'taxonomy'          => 'employer_category',
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_taxonomy_hierarchical_check_list'),
            ),
            array(
                'name'              => __( 'Location', 'wp-job-board-pro' ),
                'id'                => $prefix . 'location',
                'type'              => 'wpjb_taxonomy_location',
                'taxonomy'          => 'employer_location',
                'attributes'        => array(
                    'placeholder'   => __( 'Select %s', 'wp-job-board-pro' ),
                ),
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_taxonomy_hierarchical_check_list'),
            ),
            array(
                'name'              => __( 'Show my profile', 'wp-job-board-pro' ),
                'id'                => $prefix . 'show_profile',
                'type'              => 'select',
                'options'           => array(
                    'show'  => __( 'Prikaži', 'wp-job-board-pro' ),
                    'hide'  => __( 'Sakrij', 'wp-job-board-pro' ),
                ),
                'disable_check_register' => true,
            ),
        );
        return apply_filters( 'wp-job-board-pro-employer-type-available-fields', $fields );
    }

    public static function get_all_types_candidate_fields_required() {
        $datepicker_date_format = str_replace(
            array( 'd', 'j', 'l', 'z', /* Day. */ 'F', 'M', 'n', 'm', /* Month. */ 'Y', 'y', /* Year. */ ),
            array( 'dd', 'd', 'DD', 'o', 'MM', 'M', 'm', 'mm', 'yy', 'y', ),
            get_option( 'date_format' )
        );
        $prefix = WP_JOB_BOARD_PRO_CANDIDATE_PREFIX;
        $fields = array(
            array(
                'name'              => __( 'Full Name', 'wp-job-board-pro' ),
                'id'                => $prefix . 'title',
                'type'              => 'text',
                'default'           => '',
                'attributes'        => array(
                    'required'          => 'required'
                ),
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_input'),
            ),
            array(
                'name'              => __( 'Description', 'wp-job-board-pro' ),
                'id'                => $prefix . 'description',
                'type'              => 'wysiwyg',
                'options' => array(
                    'media_buttons' => false,
                    'textarea_rows' => 8,
                    'wpautop' => true,
                    'tinymce'       => array(
                        'plugins'                       => 'lists,paste,tabfocus,wplink,wordpress',
                        'paste_as_text'                 => true,
                        'paste_auto_cleanup_on_paste'   => true,
                        'paste_remove_spans'            => true,
                        'paste_remove_styles'           => true,
                        'paste_remove_styles_if_webkit' => true,
                        'paste_strip_class_attributes'  => true,
                    ),
                ),
            ),
            array(
                'name'              => __( 'Featured Candidate', 'wp-job-board-pro' ),
                'id'                => $prefix . 'featured',
                'type'              => 'checkbox',
                'description'       => __( 'Featured employer will be sticky during searches, and can be styled differently.', 'wp-job-board-pro' ),
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_checkbox'),
                'disable_check' => true,
                'show_in_submit_form' => '',
                'show_in_admin_edit' => 'yes',
            ),
            array(
                'name'              => __( 'Urgent Candidate', 'wp-job-board-pro' ),
                'id'                => $prefix . 'urgent',
                'type'              => 'checkbox',
                'description'       => __( 'Urgent candidate will be sticky during searches, and can be styled differently.', 'wp-job-board-pro' ),
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_checkbox'),
                'disable_check' => true,
                'show_in_submit_form' => '',
                'show_in_admin_edit' => 'yes',
            ),
            array(
                'name'              => __( 'Attached User', 'wp-job-board-pro' ),
                'id'                => $prefix . 'attached_user',
                'type'              => 'wp_job_board_pro_attached_user',
                'disable_check' => true,
                'show_in_submit_form' => '',
                'show_in_admin_edit' => 'yes',
                'disable_check_register' => true,
            ),
            array(
                'name'              => __( 'Expiry Date', 'wp-job-board-pro' ),
                'id'                => $prefix . 'expiry_date',
                'type'              => 'wjbp_datepicker',
                'disable_check' => true,
                'show_in_submit_form' => '',
                'show_in_admin_edit' => 'yes',
                'disable_check_register' => true,
                'attributes' => array(
                    'data-datepicker' => json_encode(array(
                        'dateFormat' => $datepicker_date_format,
                        'altField' => '#'.$prefix . 'expiry_date',
                        'altFormat' => 'yy-mm-dd',
                    ))
                )
            ),
        );
        return apply_filters( 'wp-job-board-pro-candidate-type-required-fields', $fields );
    }

    public static function get_all_types_candidate_fields_available() {
        $socials = WP_Job_Board_Pro_Mixes::get_socials_network();
        $opt_socials = [];
        foreach ($socials as $key => $value) {
            $opt_socials[$key] = $value['title'];
        }
        
        $currency_symbol = wp_job_board_pro_get_option('currency_symbol', '$');
        $area_unit = wp_job_board_pro_get_option('measurement_unit_area', 'sqft');

        $datepicker_date_format = str_replace(
            array( 'd', 'j', 'l', 'z', /* Day. */ 'F', 'M', 'n', 'm', /* Month. */ 'Y', 'y', /* Year. */ ),
            array( 'dd', 'd', 'DD', 'o', 'MM', 'M', 'm', 'mm', 'yy', 'y', ),
            get_option( 'date_format' )
        );

        $prefix = WP_JOB_BOARD_PRO_CANDIDATE_PREFIX;
        $fields = array(
            array(
                'name'              => __( 'Profile url', 'wp-job-board-pro' ),
                'id'                => $prefix . 'profile_url',
                'type'              => 'wp_job_board_pro_profile_url',
                'disable_check' => true,
                'show_in_submit_form' => 'yes',
                'show_in_admin_edit' => '',
                'disable_check_register' => true,
            ),
            array(
                'name'              => __( 'Featured Image', 'wp-job-board-pro' ),
                'id'                => $prefix . 'featured_image',
                'type'              => 'wp_job_board_pro_file',
                'multiple'          => false,
                'default'           => ! empty( $featured_image ) ? $featured_image : '',
                'ajax'              => true,
                'mime_types' => array( 'gif', 'jpeg', 'jpg', 'png' ),
            ),
            array(
                'name'              => __( 'Email', 'wp-job-board-pro' ),
                'id'                => $prefix . 'email',
                'type'              => 'text',
                'disable_check_register' => true,
            ),
            array(
                'name'              => __( 'Show my profile', 'wp-job-board-pro' ),
                'id'                => $prefix . 'show_profile',
                'type'              => 'select',
                'options'           => array(
                    'show'  => __( 'Prikaži', 'wp-job-board-pro' ),
                    'hide'  => __( 'Sakrij', 'wp-job-board-pro' ),
                ),
                'disable_check_register' => true,
            ),
            array(
                'name'              => __( 'Date of Birth', 'wp-job-board-pro' ),
                'id'                => $prefix . 'founded_date',
                'type'              => 'wjbp_datepicker2',
                // 'date_format' => get_option( 'date_format' ),
                'attributes'        => array(
                    'data-datepicker' => json_encode(array(
                        'yearRange' => '-100:+5',
                        'dateFormat' => $datepicker_date_format,
                        'altField' => '#'.$prefix . 'founded_date',
                        'altFormat' => 'yy-mm-dd',
                    ))
                ),
            ),
			array(
				'name'              => __( 'Phone Number', 'wp-job-board-pro' ),
				'id'                => $prefix . 'phone',
				'type'              => 'text',
				'attributes'        => array(
					'class' => 'phone-with-flags', // Custom class for JS targeting
					'placeholder' => __( 'Enter your phone number', 'wp-job-board-pro' ),
				),
				'description'       => __( 'Please include your country code.', 'wp-job-board-pro' ),
			),
            array(
                'name'              => __( 'Job Title', 'wp-job-board-pro' ),
                'id'                => $prefix . 'job_title',
                'type'              => 'text',
            ),
            array(
                'name'              => sprintf(__( 'Salary (%s)', 'wp-job-board-pro' ), $currency_symbol),
                'id'                => $prefix . 'salary',
                'type'              => 'text',
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_candidate_salary'),
            ),
            array(
                'name'              => __( 'Salary Type', 'wp-job-board-pro' ),
                'id'                => $prefix . 'salary_type',
                'type'              => 'select',
                'options'           => WP_Job_Board_Pro_Mixes::get_default_salary_types(),
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_radio_list'),
            ),
            array(
                'name'              => __( 'Portfolio Photos', 'wp-job-board-pro' ),
                'id'                => $prefix . 'portfolio_photos',
                'type'              => 'file_list',
                'file_multiple'     => true,
                'ajax'              => true,
                'multiple_files'    => true,
                'mime_types'        => array( 'gif', 'jpeg', 'jpg', 'png' ),
                'query_args' => array( 'type' => 'image' ), // Only images attachment
                'text' => array(
                    'add_upload_files_text' => __( 'Add or Upload Images', 'wp-job-board-pro' ),
                ),
            ),
            array(
                'name'              => __( 'CV Attachment', 'wp-job-board-pro' ),
                'id'                => $prefix . 'cv_attachment',
                'type'              => 'file_list',
                'file_multiple'     => true,
                'ajax'              => true,
                'multiple_files'    => true,
                'mime_types'        => array( 'pdf', 'doc', 'docx' ),
                'description'       => __('Upload file .pdf, .doc, .docx', 'wp-job-board-pro')
            ),
            array(
                'name'              => __( 'Introduction Video URL', 'wp-job-board-pro' ),
                'id'                => $prefix . 'video_url',
                'type'              => 'text',
            ),
            array(
                'name'              => __( 'Socials', 'wp-job-board-pro' ),
                'id'                => $prefix . 'socials',
                'type'              => 'group',
                'options'           => array(
                    'group_title'       => __( 'Mreža {#}', 'wp-job-board-pro' ),
                    'add_button'        => __( 'Dodaj drugu mrežu', 'wp-job-board-pro' ),
                    'remove_button'     => __( 'Ukloni mrežu', 'wp-job-board-pro' ),
                    'sortable'          => false,
                    'closed'         => true,
                    'closed'         => true,
                ),
                'fields'            => array(
                    array(
                        'name'      => __( 'Network', 'wp-job-board-pro' ),
                        'id'        => 'network',
                        'type'      => 'select',
                        'options'   => $opt_socials
                    ),
                    array(
                        'name'      => __( 'Url', 'wp-job-board-pro' ),
                        'id'        => 'url',
                        'type'      => 'text',
                    ),
                ),
            ),
            array(
                'name'              => __( 'Friendly Address', 'wp-job-board-pro' ),
                'id'                => $prefix . 'address',
                'type'              => 'text',
            ),
            array(
                'id'                => $prefix . 'map_location',
                'name'              => __( 'Maps Location', 'wp-job-board-pro' ),
                'type'              => 'pw_map',
                'sanitization_cb'   => 'pw_map_sanitise',
                'split_values'      => true,
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_input_location'),
            ),
            array(
                'name'              => __( 'Obrazovanje', 'wp-job-board-pro' ),
                'id'                => $prefix . 'education',
                'type'              => 'group',
                'options'           => array(
                    'group_title'       => __( 'Obrazovanje {#}', 'wp-job-board-pro' ),
                    'add_button'        => __( 'Dodaj novo obrazovanje', 'wp-job-board-pro' ),
                    'remove_button'     => __( 'Ukloni obrazovanje', 'wp-job-board-pro' ),
                    'sortable'          => false,
                    'closed'         => true,
                ),
                'fields'            => array(
                    array(
                        'name'      => __( 'Naziv', 'wp-job-board-pro' ),
                        'id'        => 'title',
                        'type'      => 'text',
                    ),
                    array(
                        'name'      => __( 'Škola', 'wp-job-board-pro' ),
                        'id'        => 'academy',
                        'type'      => 'text',
                    ),
                    array(
                        'name'      => __( 'Godina', 'wp-job-board-pro' ),
                        'id'        => 'year',
                        'type'      => 'text',
                    ),
                    array(
                        'name'      => __( 'Opis', 'wp-job-board-pro' ),
                        'id'        => 'description',
                        'type'      => 'textarea',
                    ),
                )
            ),
            array(
                'name'              => __( 'Iskustvo', 'wp-job-board-pro' ),
                'id'                => $prefix . 'experience',
                'type'              => 'group',
                'options'           => array(
                    'group_title'       => __( 'Iskustvo {#}', 'wp-job-board-pro' ),
                    'add_button'        => __( 'Dodaj radno iskustvo', 'wp-job-board-pro' ),
                    'remove_button'     => __( 'Ukloni radno iskustvo', 'wp-job-board-pro' ),
                    'sortable'          => false,
                    'closed'         => true,
                ),
                'fields'            => array(
                    array(
                        'name'      => __( 'Naziv', 'wp-job-board-pro' ),
                        'id'        => 'title',
                        'type'      => 'text',
                    ),
                    array(
                        'name'      => __( 'Datum početka', 'wp-job-board-pro' ),
                        'id'        => 'start_date',
                        'type'      => 'text',
                        'date_format' => get_option('date_format'),
                        'attributes'        => array(
                            'data-datepicker' => json_encode(array(
                                'yearRange' => '-100:+5',
                            ))
                        ),
                    ),
                    array(
                        'name'      => __( 'Datum završetka', 'wp-job-board-pro' ),
                        'id'        => 'end_date',
                        'type'      => 'text',
                        'date_format' => get_option('date_format'),
                        'attributes'        => array(
                            'data-datepicker' => json_encode(array(
                                'yearRange' => '-100:+5',
                            ))
                        ),
                    ),
                    array(
                        'name'      => __( 'Kompanija', 'wp-job-board-pro' ),
                        'id'        => 'company',
                        'type'      => 'text',
                    ),
                    array(
                        'name'      => __( 'Opis', 'wp-job-board-pro' ),
                        'id'        => 'description',
                        'type'      => 'textarea',
                    ),
                )
            ),
            array(
                'name'              => __( 'Nagrada', 'wp-job-board-pro' ),
                'id'                => $prefix . 'award',
                'type'              => 'group',
                'options'           => array(
                    'group_title'       => __( 'Nagrada {#}', 'wp-job-board-pro' ),
                    'add_button'        => __( 'Dodaj još jednu nagradu', 'wp-job-board-pro' ),
                    'remove_button'     => __( 'Ukloni nagradu', 'wp-job-board-pro' ),
                    'sortable'          => false,
                    'closed'         => true,
                ),
                'fields'            => array(
                    array(
                        'name'      => __( 'Naziv', 'wp-job-board-pro' ),
                        'id'        => 'title',
                        'type'      => 'text',
                    ),
                    array(
                        'name'      => __( 'Godina', 'wp-job-board-pro' ),
                        'id'        => 'year',
                        'type'      => 'text',
                    ),
                    array(
                        'name'      => __( 'Opis', 'wp-job-board-pro' ),
                        'id'        => 'description',
                        'type'      => 'textarea',
                    ),
                )
            ),
            array(
                'name'              => __( 'Veština', 'wp-job-board-pro' ),
                'id'                => $prefix . 'skill',
                'type'              => 'group',
                'options'           => array(
                    'group_title'       => __( 'Veština {#}', 'wp-job-board-pro' ),
                    'add_button'        => __( 'Dodaj još jednu veštinu', 'wp-job-board-pro' ),
                    'remove_button'     => __( 'Ukloni veštinu', 'wp-job-board-pro' ),
                    'sortable'          => false,
                    'closed'         => true,
                ),
                'fields'            => array(
                    array(
                        'name'      => __( 'Naziv', 'wp-job-board-pro' ),
                        'id'        => 'title',
                        'type'      => 'text',
                    ),
                    array(
                        'name'      => __( 'Procenat', 'wp-job-board-pro' ),
                        'id'        => 'percentage',
                        'type'      => 'text',
                        'attributes'        => array(
                            'type'              => 'number',
                            'min'               => 0,
                            'pattern'           => '\d*',
                        )
                    ),
                )
            ),

            // taxonomies
            array(
                'name'              => __( 'Categories', 'wp-job-board-pro' ),
                'id'                => $prefix . 'category',
                'type'              => 'pw_taxonomy_multiselect',
                'taxonomy'          => 'candidate_category',
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_taxonomy_hierarchical_check_list'),
            ),
            array(
                'name'              => __( 'Location', 'wp-job-board-pro' ),
                'id'                => $prefix . 'location',
                'type'              => 'wpjb_taxonomy_location',
                'taxonomy'          => 'candidate_location',
                'attributes'        => array(
                    'placeholder'   => __( 'Select %s', 'wp-job-board-pro' ),
                ),
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_taxonomy_hierarchical_check_list'),
            ),

            // custom
            array(
                'name'              => __( 'Experience Time', 'wp-job-board-pro' ),
                'id'                => $prefix . 'experience_time',
                'type'              => 'select',
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_select'),
                'options' => 'Fresh
1 Year
2 Year
3 Year
4 Year
5 Year'
            ),
            array(
                'name'              => __( 'Gender', 'wp-job-board-pro' ),
                'id'                => $prefix . 'gender',
                'type'              => 'select',
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_select'),
                'options' => 'Both
Female
Male'
            ),
            array(
                'name'              => __( 'Age', 'wp-job-board-pro' ),
                'id'                => $prefix . 'age',
                'type'              => 'select',
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_select'),
                'options' => '18-20
20-25
25-30
30-35
33-40'
            ),
            array(
                'name'              => __( 'Qualification', 'wp-job-board-pro' ),
                'id'                => $prefix . 'qualification',
                'type'              => 'select',
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_select'),
                'options' => 'Certificate
Associate Degree
Bachelor Degree
Master’s Degree
Doctorate Degree'
            ),
            array(
                'name'              => __( 'Languages', 'wp-job-board-pro' ),
                'id'                => $prefix . 'languages',
                'type'              => 'select',
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_select'),
                'options' => 'English
Turkish
Japanese
French'
            ),
            array(
                'name'              => __( 'Tag', 'wp-job-board-pro' ),
                'id'                => $prefix . 'tag',
                'type'              => 'pw_taxonomy_multiselect',
                'taxonomy'          => 'candidate_tag',
                'field_call_back' => array( 'WP_Job_Board_Pro_Abstract_Filter', 'filter_field_taxonomy_hierarchical_check_list'),
            ),
        );
        return apply_filters( 'wp-job-board-pro-candidate-type-available-fields', $fields );
    }

    public static function get_display_hooks($prefix = WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX) {
        $hooks = [];
        switch ($prefix) {
            case WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX:
                $hooks = array(
                    '' => esc_html__('Choose a position', 'wp-job-board-pro'),
                    'wp-job-board-pro-single-job-description' => esc_html__('Single Job - Description', 'wp-job-board-pro'),
                    'wp-job-board-pro-single-job-details' => esc_html__('Single Job - Details', 'wp-job-board-pro'),
                );
                break;
            case WP_JOB_BOARD_PRO_EMPLOYER_PREFIX:
                $hooks = array(
                    '' => esc_html__('Choose a position', 'wp-job-board-pro'),
                    'wp-job-board-pro-single-employer-description' => esc_html__('Single Employer - Description', 'wp-job-board-pro'),
                    'wp-job-board-pro-single-employer-details' => esc_html__('Single Employer - Details', 'wp-job-board-pro'),
                );
                break;
            case WP_JOB_BOARD_PRO_CANDIDATE_PREFIX:
                $hooks = array(
                    '' => esc_html__('Choose a position', 'wp-job-board-pro'),
                    'wp-job-board-pro-single-candidate-description' => esc_html__('Single Candidate - Description', 'wp-job-board-pro'),
                    'wp-job-board-pro-single-candidate-details' => esc_html__('Single Candidate - Details', 'wp-job-board-pro'),
                );
                break;
        }
        return apply_filters( 'wp-job-board-pro-get-custom-fields-display-hooks', $hooks, $prefix );
    }

    public static function custom_field_html() {
        $fieldtype = $_POST['fieldtype'];
        $prefix = $_POST['prefix'];
        $global_custom_field_counter = $_REQUEST['global_custom_field_counter'];
        $li_rand_id = rand(454, 999999);
        $global_custom_field_counter = $global_custom_field_counter.$li_rand_id;
        
        $html = '<li class="custom-field-class-' . $li_rand_id . '">';
        $types = self::get_all_field_type_keys();
        if ( in_array($fieldtype, $types) ) {
            if ( in_array( $fieldtype, array('text', 'textarea', 'wysiwyg', 'number', 'url', 'email', 'checkbox') ) ) {
                $html .= apply_filters( 'wp_job_board_pro_custom_field_text_html', $fieldtype, $global_custom_field_counter, '', $prefix );
            } elseif ( in_array( $fieldtype, array('select', 'multiselect', 'radio') ) ) {
                $html .= apply_filters( 'wp_job_board_pro_custom_field_opts_html', $fieldtype, $global_custom_field_counter, '', $prefix );
            } else {
                $html .= apply_filters('wp_job_board_pro_custom_field_'.$fieldtype.'_html', $fieldtype, $global_custom_field_counter, '', $prefix);
            }
        }
        // action btns
        $html .= apply_filters('wp_job_board_pro_custom_field_actions_html', $li_rand_id, $global_custom_field_counter, $fieldtype);
        $html .= '</li>';
        echo json_encode( array('html' => $html) );
        wp_die();
    }

    public static function custom_field_available_html() {
        $prefix = $_REQUEST['prefix'];

        $fieldtype = $_POST['fieldtype'];
        $global_custom_field_counter = $_REQUEST['global_custom_field_counter'];
        $li_rand_id = rand(454, 999999);
        $html = '<li class="custom-field-class-' . $li_rand_id . '">';
        if ( $prefix == WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX ) {
            $types = self::get_all_types_job_listing_fields_available();
        } elseif ( $prefix == WP_JOB_BOARD_PRO_EMPLOYER_PREFIX ) {
            $types = self::get_all_types_employer_fields_available();
        } elseif ( $prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX ) {
            $types = self::get_all_types_candidate_fields_available();
        }

        $dfield_values = self::get_field_id($fieldtype, $types);
        if ( !empty($dfield_values) ) {

            $dtypes = apply_filters( 'wp_job_board_pro_list_simple_type', array( $prefix.'featured', $prefix.'urgent', $prefix.'address', $prefix.'salary', $prefix.'max_salary', $prefix.'application_deadline_date', $prefix.'apply_url', $prefix.'apply_email', $prefix.'video', $prefix.'profile_url', $prefix.'email', $prefix.'founded_date', $prefix.'website', $prefix.'phone', $prefix.'video_url', $prefix.'socials', $prefix.'team_members', $prefix.'employees', $prefix.'attached_user', $prefix.'show_profile', $prefix.'job_title', WP_JOB_BOARD_PRO_CANDIDATE_PREFIX.'experience', $prefix.'education', $prefix.'award', $prefix.'skill', $prefix.'tag', $prefix.'company_size' ) );

            if ( in_array( $fieldtype, $dtypes ) ) {
                $html .= apply_filters( 'wp_job_board_pro_custom_field_available_simple_html', $fieldtype, $global_custom_field_counter, $dfield_values, $prefix );
            } elseif ( in_array( $fieldtype, array($prefix.'category', $prefix.'type') ) ) {
                $html .= apply_filters( 'wp_job_board_pro_custom_field_available_tax_html', $fieldtype, $global_custom_field_counter, $dfield_values, $prefix );
            } elseif ( in_array( $fieldtype, array($prefix.'featured_image', $prefix.'logo', $prefix.'gallery', $prefix.'attachments', $prefix.'cover_photo', $prefix.'profile_photos', $prefix.'portfolio_photos', $prefix.'cv_attachment', $prefix.'photos') ) ) {
                $html .= apply_filters( 'wp_job_board_pro_custom_field_available_file_html', $fieldtype, $global_custom_field_counter, $dfield_values, $prefix );
            } elseif ( in_array($fieldtype, array( $prefix.'experience_time', $prefix.'experience', $prefix.'gender', $prefix.'industry', $prefix.'qualification', $prefix.'career_level', $prefix.'age', $prefix.'languages') )) {
                $html .= apply_filters( 'wp_job_board_pro_custom_field_available_select_option_html', $fieldtype, $global_custom_field_counter, $dfield_values, $prefix );
            } elseif ( in_array($fieldtype, array( $prefix.'salary_type' ) )) {
                $html .= apply_filters( 'wp_job_board_pro_custom_field_available_salary_type_html', $fieldtype, $global_custom_field_counter, $dfield_values, $prefix );
            } elseif ( in_array($fieldtype, array( $prefix.'apply_type' ) )) {
                $html .= apply_filters( 'wp_job_board_pro_custom_field_available_apply_type_html', $fieldtype, $global_custom_field_counter, $dfield_values, $prefix );
            } elseif ( in_array($fieldtype, array( $prefix.'location' ) )) {
                $html .= apply_filters( 'wp_job_board_pro_custom_field_available_location_html', $fieldtype, $global_custom_field_counter, $dfield_values, $prefix );
            } else {
                $html .= apply_filters( 'wp_job_board_pro_custom_field_available_'.$fieldtype.'_html', $fieldtype, $global_custom_field_counter, $dfield_values, $prefix );
            }
        }

        // action btns
        $html .= apply_filters('wp_job_board_pro_custom_field_actions_html', $li_rand_id, $global_custom_field_counter, $fieldtype);
        $html .= '</li>';
        echo json_encode(array('html' => $html));
        wp_die();
    }

}

WP_Job_Board_Pro_Fields_Manager::init();


