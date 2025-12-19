<?php
/**
 * Custom Field HTML
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if (!defined('ABSPATH')) {
    die;
}

// main plugin class
class WP_Job_Board_Pro_CustomFieldHTML {
    
    public static $packages;
    

    public static function init() {
        add_filter('wp_job_board_pro_custom_field_text_html', array(__CLASS__, 'field_text_html_callback'), 1, 4);
        add_filter('wp_job_board_pro_custom_field_date_html', array(__CLASS__, 'field_text_html_callback'), 1, 4);
        add_filter('wp_job_board_pro_custom_field_opts_html', array(__CLASS__, 'field_opts_html_callback'), 1, 4);
        add_filter('wp_job_board_pro_custom_field_file_html', array(__CLASS__, 'field_file_html_callback'), 1, 4);
        add_filter('wp_job_board_pro_custom_field_heading_html', array(__CLASS__, 'field_heading_html_callback'), 1, 4);

        // available fields
        add_filter('wp_job_board_pro_custom_field_available_simple_html', array(__CLASS__, 'field_available_simple_callback'), 1, 4);
        add_filter('wp_job_board_pro_custom_field_available_tax_html', array(__CLASS__, 'field_available_tax_callback'), 1, 4);
        add_filter('wp_job_board_pro_custom_field_available_file_html', array(__CLASS__, 'field_available_file_callback'), 1, 4);
        add_filter('wp_job_board_pro_custom_field_available_files_html', array(__CLASS__, 'field_available_files_callback'), 1, 4);
        add_filter('wp_job_board_pro_custom_field_available_description_html', array(__CLASS__, 'field_available_description_callback'), 1, 4);
        add_filter('wp_job_board_pro_custom_field_available_select_option_html', array(__CLASS__, 'field_available_select_option_callback'), 1, 4);
        add_filter('wp_job_board_pro_custom_field_available_location_html', array(__CLASS__, 'field_available_location_callback'), 1, 4);

         add_filter('wp_job_board_pro_custom_field_available_salary_type_html', array(__CLASS__, 'field_available_salary_type_callback'), 1, 4);
         add_filter('wp_job_board_pro_custom_field_available_apply_type_html', array(__CLASS__, 'field_available_apply_type_callback'), 1, 4);

        add_filter('wp_job_board_pro_custom_field_available_'.WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX.'map_location_html', array(__CLASS__, 'field_available_simple_without_placeholder_callback'), 1, 4);
        add_filter('wp_job_board_pro_custom_field_available_'.WP_JOB_BOARD_PRO_EMPLOYER_PREFIX.'map_location_html', array(__CLASS__, 'field_available_simple_without_placeholder_callback'), 1, 4);
        add_filter('wp_job_board_pro_custom_field_available_'.WP_JOB_BOARD_PRO_CANDIDATE_PREFIX.'map_location_html', array(__CLASS__, 'field_available_simple_without_placeholder_callback'), 1, 4);
        // actions
        add_filter('wp_job_board_pro_custom_field_actions_html', array(__CLASS__, 'field_actions_html_callback'), 1, 4);
    }
    
    public static function yes_no_opts(){
        return array(
            '' => __('No', 'wp-job-board-pro'),
            'yes' => __('Yes', 'wp-job-board-pro'),
        );
    }

    public static function candidate_form_opts(){
        return array(
            'profile' => __('Profile Form', 'wp-job-board-pro'),
            'resume' => __('Resume Form', 'wp-job-board-pro'),
        );
    }

    public static function get_packages() {
        if ( empty(self::$packages) ) {
            $query_args = array(
                'post_type' => 'product',
                'post_status' => 'publish',
                'posts_per_page'   => -1,
                'order'            => 'asc',
                'orderby'          => 'menu_order',
                'tax_query' => array(
                    array(
                        'taxonomy' => 'product_type',
                        'field'    => 'slug',
                        'terms'    => apply_filters( 'wp-job-board-pro-packages-type', array('job_package') ),
                    ),
                ),
            );
            $packages = get_posts( $query_args );
            $return = array();
            foreach ($packages as $package) {
                $return[$package->ID] = $package->post_title;
            }
            self::$packages = $return;
        }
        return self::$packages;
    }
    
    public static function field_text_html_callback($type, $field_counter, $field_data, $p_prefix) {
        ob_start();
        $rand = $field_counter;

        $name_val = stripslashes(isset($field_data['name']) ? $field_data['name'] : 'Custom Field');
        $id_val = isset($field_data['id']) ? $field_data['id'] : 'custom-'.$type.'-'.$field_counter;
        $placeholder_val = stripslashes(isset($field_data['placeholder']) ? $field_data['placeholder'] : '');
        $description_val = stripslashes(isset($field_data['description']) ? $field_data['description'] : '');

        $required_val = isset($field_data['required']) ? $field_data['required'] : '';
        $show_in_submit_form_val = isset($field_data['show_in_submit_form']) ? $field_data['show_in_submit_form'] : 'yes';
        $show_in_admin_edit_val = isset($field_data['show_in_admin_edit']) ? $field_data['show_in_admin_edit'] : 'yes';
        
        $disable_check_val = isset($field_data['disable_check']) ? $field_data['disable_check'] : false;

        $prefix = 'wp-job-board-pro-custom-fields-'.$type;
        ?>
        <div class="wp-job-board-pro-custom-field-container wp-job-board-pro-custom-field-<?php echo esc_attr($type); ?>-container">
            <?php self::header_html($type, $rand, $name_val); ?>

            <div class="field-data form-group-wrapper" id="<?php echo esc_attr($type); ?>-field-wraper<?php echo esc_html($rand); ?>" style="display:none;">
                <input type="hidden" name="wp-job-board-pro-custom-fields-type[]" value="<?php echo esc_attr($type); ?>" />
                <input type="hidden" name="wp-job-board-pro-custom-fields-id[]" value="<?php echo esc_html($field_counter); ?>" />

                <?php
                    self::text( $prefix.'[name][]', esc_html__('Label', 'wp-job-board-pro'), $name_val, '', 'wp-job-board-pro-custom-field-label');
                    self::text( $prefix.'[id][]', esc_html__('Key', 'wp-job-board-pro'), $id_val, '', 'wp-job-board-pro-custom-field-key');
                    self::text( $prefix.'[placeholder][]', esc_html__('Placeholder', 'wp-job-board-pro'), $placeholder_val);
                    self::text( $prefix.'[description][]', esc_html__('Description', 'wp-job-board-pro'), $description_val);
                    

                    $text_field_icon = isset($field_data['icon']) ? $field_data['icon'] : '';
                    ?>
                    <div class="form-group">
                        <label>
                            <?php echo esc_html__('Icon', 'wp-job-board-pro'); ?>:
                        </label>
                        <div class="input-field">
                            <?php
                            $icon_id = rand(1000000, 99999999);

                            echo self::icon_picker($text_field_icon, $icon_id, $prefix.'[icon][]');
                            ?>
                        </div>
                    </div>
                    <?php
                    // self::checkbox( $prefix.'[show_in_submit_form][]', esc_html__('Show in frontend form', 'wp-job-board-pro'), $show_in_submit_form_val);
                    // self::checkbox( $prefix.'[show_in_admin_edit][]', esc_html__('Show in admin edit page', 'wp-job-board-pro'), $show_in_admin_edit_val);

                    self::select( $prefix.'[show_in_submit_form][]', self::yes_no_opts(), esc_html__('Show in frontend form', 'wp-job-board-pro'), $show_in_submit_form_val, '', false, false, 'show_in_submit_form', $disable_check_val );
                    self::select( $prefix.'[show_in_admin_edit][]', self::yes_no_opts(), esc_html__('Show in admin form', 'wp-job-board-pro'), $show_in_admin_edit_val, '', false, false, 'show_in_admin_edit', $disable_check_val );

                    if ( $p_prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX ) {
                        $show_in_submit_form_candidate_val = isset($field_data['show_in_submit_form_candidate']) ? $field_data['show_in_submit_form_candidate'] : 'yes';
                        self::select( $prefix.'[show_in_submit_form_candidate][]', self::candidate_form_opts(), esc_html__('Show in Candidate Form', 'wp-job-board-pro'), $show_in_submit_form_candidate_val, '', false, false, 'show_if_show_in_submit_form');
                    }

                    if ( $p_prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX || $p_prefix == WP_JOB_BOARD_PRO_EMPLOYER_PREFIX ) {
                        $show_in_register_form_val = isset($field_data['show_in_register_form']) ? $field_data['show_in_register_form'] : '';
                        $disable_check_register_val = isset($field_data['disable_check_register']) ? $field_data['disable_check_register'] : false;
                        // self::select( $prefix.'[show_in_register_form][]', self::yes_no_opts(), esc_html__('Show in register form', 'wp-job-board-pro'), $show_in_register_form_val, '', false, false, '', $disable_check_register_val );

                        self::select( $prefix.'[show_in_register_form][]', self::yes_no_opts(), esc_html__('Show in register form', 'wp-job-board-pro'), $show_in_register_form_val, '', false, false, '', $disable_check_register_val );
                    }

                    // self::checkbox( $prefix.'[required][]', esc_html__('Required', 'wp-job-board-pro'), $required_val);
                    self::select( $prefix.'[required][]', self::yes_no_opts(), esc_html__('Required', 'wp-job-board-pro'), $required_val, '', false, false );

                    // packages
                    if ( $p_prefix == WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX ) {
                        $packages = self::get_packages();
                        if ( $packages ) {
                            $show_in_package = isset($field_data['show_in_package']) ? $field_data['show_in_package'] : '';
                            $package_display = isset($field_data['package_display']) ? $field_data['package_display'] : '';
                            // self::checkbox( $prefix.'[show_in_package][]', esc_html__('Enable package visibility', 'wp-job-board-pro'), $show_in_package, false, true, 'show_in_package');

                            self::select( $prefix.'[show_in_package][]', self::yes_no_opts(), esc_html__('Enable package visibility', 'wp-job-board-pro'), $show_in_package, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), false, true, 'show_in_package');

                            self::select( $prefix.'[package_display]['.$field_counter.'][]', $packages, esc_html__('Packages', 'wp-job-board-pro'), $package_display, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), true, true, 'show_if_show_in_package');
                        }
                    }
                    // hook
                    $hook_display = isset($field_data['hook_display']) ? $field_data['hook_display'] : '';
                    $opts = WP_Job_Board_Pro_Fields_Manager::get_display_hooks($p_prefix);
                    self::select( $prefix.'[hook_display][]', $opts, esc_html__('Position Display', 'wp-job-board-pro'), $hook_display, '', false, true);

                    do_action('wp_job_board_pro_custom_field_text_html_callback', $type, $field_counter, $field_data, $p_prefix);
                ?>

            </div>
        </div>
        <?php
        $html = ob_get_clean();

        return $html;
    }

    public static function field_opts_html_callback($type, $field_counter, $field_data, $p_prefix) {
        ob_start();
        $rand = $field_counter;

        $name_val = stripslashes(isset($field_data['name']) ? $field_data['name'] : 'Custom Field');
        $id_val = isset($field_data['id']) ? $field_data['id'] : 'custom-'.$type.'-'.$field_counter;
        $placeholder_val = stripslashes(isset($field_data['placeholder']) ? $field_data['placeholder'] : '');
        $description_val = stripslashes(isset($field_data['description']) ? $field_data['description'] : '');
        $text_field_options = stripslashes(isset($field_data['options']) ? $field_data['options'] : '');

        $required_val = isset($field_data['required']) ? $field_data['required'] : '';
        $show_in_submit_form_val = isset($field_data['show_in_submit_form']) ? $field_data['show_in_submit_form'] : 'yes';
        $show_in_admin_edit_val = isset($field_data['show_in_admin_edit']) ? $field_data['show_in_admin_edit'] : 'yes';

        $disable_check_val = isset($field_data['disable_check']) ? $field_data['disable_check'] : false;

        $prefix = 'wp-job-board-pro-custom-fields-'.$type;
        ?>
        <div class="wp-job-board-pro-custom-field-container wp-job-board-pro-custom-field-<?php echo esc_attr($type); ?>-container">
            <?php self::header_html($type, $rand, $name_val); ?>

            <div class="field-data form-group-wrapper" id="<?php echo esc_attr($type); ?>-field-wraper<?php echo esc_html($rand); ?>" style="display:none;">
                <input type="hidden" name="wp-job-board-pro-custom-fields-type[]" value="<?php echo esc_attr($type); ?>" />
                <input type="hidden" name="wp-job-board-pro-custom-fields-id[]" value="<?php echo esc_html($field_counter); ?>" />

                <?php
                self::text( $prefix.'[name][]', esc_html__('Label', 'wp-job-board-pro'), $name_val, '', 'wp-job-board-pro-custom-field-label');
                self::text( $prefix.'[id][]', esc_html__('Key', 'wp-job-board-pro'), $id_val, '', 'wp-job-board-pro-custom-field-key');
                self::text( $prefix.'[placeholder][]', esc_html__('Placeholder', 'wp-job-board-pro'), $placeholder_val);
                self::text( $prefix.'[description][]', esc_html__('Description', 'wp-job-board-pro'), $description_val);
                self::textarea( $prefix.'[options][]', esc_html__('Options', 'wp-job-board-pro'), $text_field_options, esc_html__('Add each option in a new line.', 'wp-job-board-pro'));

                $text_field_icon = isset($field_data['icon']) ? $field_data['icon'] : '';
                ?>
                <div class="form-group">
                    <label>
                        <?php echo esc_html__('Icon', 'wp-job-board-pro'); ?>:
                    </label>
                    <div class="input-field">
                        <?php
                        $icon_id = rand(1000000, 99999999);

                        echo self::icon_picker($text_field_icon, $icon_id, $prefix.'[icon][]');
                        ?>
                    </div>
                </div>
                <?php

                self::select( $prefix.'[show_in_submit_form][]', self::yes_no_opts(), esc_html__('Show in frontend form', 'wp-job-board-pro'), $show_in_submit_form_val, '', false, false, 'show_in_submit_form', $disable_check_val);
                self::select( $prefix.'[show_in_admin_edit][]', self::yes_no_opts(), esc_html__('Show in admin form', 'wp-job-board-pro'), $show_in_admin_edit_val, '', false, false, 'show_in_admin_edit', $disable_check_val );

                if ( $p_prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX ) {
                    $show_in_submit_form_candidate_val = isset($field_data['show_in_submit_form_candidate']) ? $field_data['show_in_submit_form_candidate'] : 'yes';
                    self::select( $prefix.'[show_in_submit_form_candidate][]', self::candidate_form_opts(), esc_html__('Show in Candidate Form', 'wp-job-board-pro'), $show_in_submit_form_candidate_val, '', false, false, 'show_if_show_in_submit_form' );
                }

                if ( $p_prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX || $p_prefix == WP_JOB_BOARD_PRO_EMPLOYER_PREFIX ) {
                    $show_in_register_form_val = isset($field_data['show_in_register_form']) ? $field_data['show_in_register_form'] : '';
                    $disable_check_register_val = isset($field_data['disable_check_register']) ? $field_data['disable_check_register'] : false;
                    self::select( $prefix.'[show_in_register_form][]', self::yes_no_opts(), esc_html__('Show in register form', 'wp-job-board-pro'), $show_in_register_form_val, '', false, false, '', $disable_check_register_val );
                }

                self::select( $prefix.'[required][]', self::yes_no_opts(), esc_html__('Required', 'wp-job-board-pro'), $required_val, '', false, false );

                // self::checkbox( $prefix.'[show_in_submit_form][]', esc_html__('Show in frontend form', 'wp-job-board-pro'), $show_in_submit_form_val);
                // self::checkbox( $prefix.'[show_in_admin_edit][]', esc_html__('Show in admin edit page', 'wp-job-board-pro'), $show_in_admin_edit_val);

                // packages
                if ( $p_prefix == WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX ) {
                    $packages = self::get_packages();
                    if ( $packages ) {
                        $show_in_package = isset($field_data['show_in_package']) ? $field_data['show_in_package'] : '';
                        $package_display = isset($field_data['package_display']) ? $field_data['package_display'] : '';
                        
                        self::select( $prefix.'[show_in_package][]', self::yes_no_opts(), esc_html__('Enable package visibility', 'wp-job-board-pro'), $show_in_package, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), false, true, 'show_in_package');

                        self::select( $prefix.'[package_display]['.$field_counter.'][]', $packages, esc_html__('Packages', 'wp-job-board-pro'), $package_display, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), true, true, 'show_if_show_in_package');
                    }
                }

                // hook
                $hook_display = isset($field_data['hook_display']) ? $field_data['hook_display'] : '';
                $opts = WP_Job_Board_Pro_Fields_Manager::get_display_hooks($p_prefix);
                self::select( $prefix.'[hook_display][]', $opts, esc_html__('Position Display', 'wp-job-board-pro'), $hook_display, '', false, true);

                do_action('wp_job_board_pro_custom_field_opts_html_callback', $type, $field_counter, $field_data, $p_prefix);
                ?>

            </div>
        </div>
        <?php
        $html = ob_get_clean();

        return $html;
    }

    public static function field_heading_html_callback($type, $field_counter, $field_data, $p_prefix) {
        ob_start();
        $rand = $field_counter;

        $label_val = stripslashes(isset($field_data['name']) ? $field_data['name'] : 'Custom Field');
        $key_val = isset($field_data['id']) ? $field_data['id'] : 'custom-'.$type.'-'.$field_counter;

        $text_field_icon = isset($field_data['icon']) ? $field_data['icon'] : '';

        $prefix = 'wp-job-board-pro-custom-fields-'.$type;
        ?>
        <div class="wp-job-board-pro-custom-field-container wp-job-board-pro-custom-field-heading-container">
            <?php self::header_html($type, $rand, $label_val); ?>
            
            <div class="field-data form-group-wrapper" id="heading-field-wraper<?php echo esc_html($rand); ?>" style="display:none;">
                <input type="hidden" name="wp-job-board-pro-custom-fields-type[]" value="heading" />
                <input type="hidden" name="wp-job-board-pro-custom-fields-id[]" value="<?php echo esc_html($field_counter); ?>" />
                
                <?php
                self::text( $prefix.'[name][]', esc_html__('Label', 'wp-job-board-pro'), $label_val, '', 'wp-job-board-pro-custom-field-label');
                self::text( $prefix.'[id][]', esc_html__('Key', 'wp-job-board-pro'), $key_val, '', 'wp-job-board-pro-custom-field-key');
                ?>

                <div class="form-group">
                    <label>
                        <?php echo esc_html__('Icon', 'wp-job-board-pro'); ?>:
                    </label>
                    <div class="input-field">
                        <?php
                        $icon_id = rand(1000000, 99999999);

                        echo self::icon_picker($text_field_icon, $icon_id, $prefix.'[icon][]');
                        ?>
                    </div>
                </div>


                <?php
                    if ( $p_prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX ) {
                        $show_in_submit_form_candidate_val = isset($field_data['show_in_submit_form_candidate']) ? $field_data['show_in_submit_form_candidate'] : 'yes';
                        self::select( $prefix.'[show_in_submit_form_candidate][]', self::candidate_form_opts(), esc_html__('Show in Candidate Form', 'wp-job-board-pro'), $show_in_submit_form_candidate_val );
                    }
                    $number_column_val = isset($field_data['number_columns']) ? $field_data['number_columns'] : '1';
                    $columns = array(
                        '1' => __('1 Column', 'wp-job-board-pro'),
                        '2' => __('2 Column', 'wp-job-board-pro'),
                        '3' => __('3 Column', 'wp-job-board-pro'),
                        '4' => __('4 Column', 'wp-job-board-pro'),
                    );
                    self::select( $prefix.'[number_columns][]', $columns, esc_html__('Columns Inner', 'wp-job-board-pro'), $number_column_val, '', false);

                    // packages
                    if ( $p_prefix == WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX ) {
                        $packages = self::get_packages();
                        if ( $packages ) {
                            $show_in_package = isset($field_data['show_in_package']) ? $field_data['show_in_package'] : '';
                            $package_display = isset($field_data['package_display']) ? $field_data['package_display'] : '';
                            self::select( $prefix.'[show_in_package][]', self::yes_no_opts(), esc_html__('Enable package visibility', 'wp-job-board-pro'), $show_in_package, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), false, true, 'show_in_package');
                            self::select( $prefix.'[package_display]['.$field_counter.'][]', $packages, esc_html__('Packages', 'wp-job-board-pro'), $package_display, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), true, true, 'show_if_show_in_package');
                        }
                    }

                    do_action('wp_job_board_pro_custom_field_heading_html_callback', $type, $field_counter, $field_data, $p_prefix);
                ?>

            </div>
        </div>
        <?php
        $html = ob_get_clean();

        return $html;
    }

    public static function field_file_html_callback($type, $field_counter, $field_data, $p_prefix) {
        ob_start();
        $rand = $field_counter;

        $name_val = stripslashes(isset($field_data['name']) ? $field_data['name'] : 'Custom Field');
        $id_val = isset($field_data['id']) ? $field_data['id'] : 'custom-'.$type.'-'.$field_counter;
        $placeholder_val = stripslashes(isset($field_data['placeholder']) ? $field_data['placeholder'] : '');
        $description_val = stripslashes(isset($field_data['description']) ? $field_data['description'] : '');
        $file_limit = isset($field_data['file_limit']) ? $field_data['file_limit'] : 5;
        $file_size = isset($field_data['file_size']) ? $field_data['file_size'] : 5;

        $text_field_multiple_files = isset($field_data['multiple_files']) ? $field_data['multiple_files'] : '';
        $required_val = isset($field_data['required']) ? $field_data['required'] : '';
        $show_in_submit_form_val = isset($field_data['show_in_submit_form']) ? $field_data['show_in_submit_form'] : 'yes';
        $show_in_admin_edit_val = isset($field_data['show_in_admin_edit']) ? $field_data['show_in_admin_edit'] : 'yes';

        $text_field_allow_types = isset($field_data['allow_types']) ? $field_data['allow_types'] : (isset($field_data['mime_types']) ? $field_data['mime_types'] : '');

        $disable_check_val = isset($field_data['disable_check']) ? $field_data['disable_check'] : false;

        $prefix = 'wp-job-board-pro-custom-fields-'.$type;
        ?>
        <div class="wp-job-board-pro-custom-field-container wp-job-board-pro-custom-field-<?php echo esc_attr($type); ?>-container">
            <?php self::header_html($type, $rand, $name_val); ?>

            <div class="field-data form-group-wrapper" id="<?php echo esc_attr($type); ?>-field-wraper<?php echo esc_html($rand); ?>" style="display:none;">
                <input type="hidden" name="wp-job-board-pro-custom-fields-type[]" value="<?php echo esc_attr($type); ?>" />
                <input type="hidden" name="wp-job-board-pro-custom-fields-id[]" value="<?php echo esc_html($field_counter); ?>" />

                <?php
                self::text( $prefix.'[name][]', esc_html__('Label', 'wp-job-board-pro'), $name_val, '', 'wp-job-board-pro-custom-field-label');
                self::text( $prefix.'[id][]', esc_html__('Key', 'wp-job-board-pro'), $id_val, '', 'wp-job-board-pro-custom-field-key');
                self::text( $prefix.'[placeholder][]', esc_html__('Placeholder', 'wp-job-board-pro'), $placeholder_val);
                self::text( $prefix.'[description][]', esc_html__('Description', 'wp-job-board-pro'), $description_val);

                self::number( $prefix.'[file_limit][]', esc_html__('File limit', 'wp-job-board-pro'), $file_limit);
                self::number( $prefix.'[file_size][]', esc_html__('Limit Size', 'wp-job-board-pro'), $file_size, esc_html__('Enter limit size by megabyte', 'wp-job-board-pro'));

                $mime_types = get_allowed_mime_types();
                self::select( $prefix.'[allow_types]['.$field_counter.'][]', $mime_types, esc_html__('Allowed file types', 'wp-job-board-pro'), $text_field_allow_types, '', true);

                $text_field_icon = isset($field_data['icon']) ? $field_data['icon'] : '';
                ?>
                <div class="form-group">
                    <label>
                        <?php echo esc_html__('Icon', 'wp-job-board-pro'); ?>:
                    </label>
                    <div class="input-field">
                        <?php
                        $icon_id = rand(1000000, 99999999);

                        echo self::icon_picker($text_field_icon, $icon_id, $prefix.'[icon][]');
                        ?>
                    </div>
                </div>
                <?php
                
                // self::checkbox( $prefix.'[multiple_files][]', esc_html__('Multiple files', 'wp-job-board-pro'), $text_field_multiple_files);
                self::select( $prefix.'[multiple_files][]', self::yes_no_opts(), esc_html__('Multiple files', 'wp-job-board-pro'), $text_field_multiple_files, '', false, false );

                self::select( $prefix.'[show_in_submit_form][]', self::yes_no_opts(), esc_html__('Show in frontend form', 'wp-job-board-pro'), $show_in_submit_form_val, '', false, false, 'show_in_submit_form', $disable_check_val );
                self::select( $prefix.'[show_in_admin_edit][]', self::yes_no_opts(), esc_html__('Show in admin form', 'wp-job-board-pro'), $show_in_admin_edit_val, '', false, false, 'show_in_admin_edit', $disable_check_val );

                if ( $p_prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX ) {
                    $show_in_submit_form_candidate_val = isset($field_data['show_in_submit_form_candidate']) ? $field_data['show_in_submit_form_candidate'] : 'yes';
                    self::select( $prefix.'[show_in_submit_form_candidate][]', self::candidate_form_opts(), esc_html__('Show in Candidate Form', 'wp-job-board-pro'), $show_in_submit_form_candidate_val, '', false, false, 'show_if_show_in_submit_form' );
                }

                if ( $p_prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX || $p_prefix == WP_JOB_BOARD_PRO_EMPLOYER_PREFIX ) {
                    $show_in_register_form_val = isset($field_data['show_in_register_form']) ? $field_data['show_in_register_form'] : '';
                    $disable_check_register_val = isset($field_data['disable_check_register']) ? $field_data['disable_check_register'] : false;
                    self::select( $prefix.'[show_in_register_form][]', self::yes_no_opts(), esc_html__('Show in register form', 'wp-job-board-pro'), $show_in_register_form_val, '', false, false, '', $disable_check_register_val );
                }
                
                self::select( $prefix.'[required][]', self::yes_no_opts(), esc_html__('Required', 'wp-job-board-pro'), $required_val, '', false, false );

                // packages
                if ( $p_prefix == WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX ) {
                    $packages = self::get_packages();
                    if ( $packages ) {
                        $show_in_package = isset($field_data['show_in_package']) ? $field_data['show_in_package'] : '';
                        $package_display = isset($field_data['package_display']) ? $field_data['package_display'] : '';
                        self::select( $prefix.'[show_in_package][]', self::yes_no_opts(), esc_html__('Enable package visibility', 'wp-job-board-pro'), $show_in_package, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), false, true, 'show_in_package');
                        self::select( $prefix.'[package_display]['.$field_counter.'][]', $packages, esc_html__('Packages', 'wp-job-board-pro'), $package_display, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), true, true, 'show_if_show_in_package');
                    }
                }

                // hook
                $hook_display = isset($field_data['hook_display']) ? $field_data['hook_display'] : '';
                $opts = WP_Job_Board_Pro_Fields_Manager::get_display_hooks($p_prefix);
                self::select( $prefix.'[hook_display][]', $opts, esc_html__('Position Display', 'wp-job-board-pro'), $hook_display, '', false, true);

                do_action('wp_job_board_pro_custom_field_file_html_callback', $type, $field_counter, $field_data, $p_prefix);
                ?>
                
            </div>
        </div>
        <?php
        $html = ob_get_clean();

        return $html;
    }

    public static function field_available_simple_callback($type, $field_counter, $field_data, $p_prefix) {
        ob_start();
        $rand = $field_counter;

        $name_val = stripslashes(isset($field_data['name']) ? $field_data['name'] : 'Available Field');
        $id_val = $type;
        $placeholder_val = stripslashes(isset($field_data['placeholder']) ? $field_data['placeholder'] : '');
        $description_val = stripslashes(isset($field_data['description']) ? $field_data['description'] : '');

        $required_val = isset($field_data['required']) ? $field_data['required'] : '';
        $show_in_submit_form_val = isset($field_data['show_in_submit_form']) ? $field_data['show_in_submit_form'] : 'yes';
        $show_in_admin_edit_val = isset($field_data['show_in_admin_edit']) ? $field_data['show_in_admin_edit'] : 'yes';

        $disable_check_val = isset($field_data['disable_check']) ? $field_data['disable_check'] : false;

        $prefix = 'wp-job-board-pro-custom-fields-'.$type;
        ?>
        <div class="wp-job-board-pro-custom-field-container wp-job-board-pro-custom-field-<?php echo esc_attr($type); ?>-container">
            <?php self::header_html($type, $rand, $name_val); ?>
            <?php self::hidden( $prefix.'[id][]', $id_val, 'wp-job-board-pro-custom-field-key'); ?>
            <div class="field-data form-group-wrapper" id="<?php echo esc_attr($type); ?>-field-wraper<?php echo esc_html($rand); ?>" style="display:none;">
                <input type="hidden" name="wp-job-board-pro-custom-fields-type[]" value="<?php echo esc_attr($type); ?>" />
                <input type="hidden" name="wp-job-board-pro-custom-fields-id[]" value="<?php echo esc_html($field_counter); ?>" />
                
                <?php
                self::text( $prefix.'[name][]', esc_html__('Label', 'wp-job-board-pro'), $name_val, '', 'wp-job-board-pro-custom-field-label');
                self::text( $prefix.'[placeholder][]', esc_html__('Placeholder', 'wp-job-board-pro'), $placeholder_val);
                self::text( $prefix.'[description][]', esc_html__('Description', 'wp-job-board-pro'), $description_val);
                self::select( $prefix.'[show_in_submit_form][]', self::yes_no_opts(), esc_html__('Show in frontend form', 'wp-job-board-pro'), $show_in_submit_form_val, '', false, false, 'show_in_submit_form', $disable_check_val );
                self::select( $prefix.'[show_in_admin_edit][]', self::yes_no_opts(), esc_html__('Show in admin form', 'wp-job-board-pro'), $show_in_admin_edit_val, '', false, false, 'show_in_admin_edit', $disable_check_val );

                if ( $p_prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX ) {
                    $show_in_submit_form_candidate_val = isset($field_data['show_in_submit_form_candidate']) ? $field_data['show_in_submit_form_candidate'] : 'yes';
                    self::select( $prefix.'[show_in_submit_form_candidate][]', self::candidate_form_opts(), esc_html__('Show in Candidate Form', 'wp-job-board-pro'), $show_in_submit_form_candidate_val, '', false, false, 'show_if_show_in_submit_form' );
                }

                if ( $p_prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX || $p_prefix == WP_JOB_BOARD_PRO_EMPLOYER_PREFIX ) {
                    $show_in_register_form_val = isset($field_data['show_in_register_form']) ? $field_data['show_in_register_form'] : '';
                    $disable_check_register_val = isset($field_data['disable_check_register']) ? $field_data['disable_check_register'] : false;
                    self::select( $prefix.'[show_in_register_form][]', self::yes_no_opts(), esc_html__('Show in register form', 'wp-job-board-pro'), $show_in_register_form_val, '', false, false, '', $disable_check_register_val );
                }

                self::select( $prefix.'[required][]', self::yes_no_opts(), esc_html__('Required', 'wp-job-board-pro'), $required_val, '', false, false );

                // packages
                if ( $p_prefix == WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX ) {
                    $packages = self::get_packages();
                    if ( $packages ) {
                        $show_in_package = isset($field_data['show_in_package']) ? $field_data['show_in_package'] : '';
                        $package_display = isset($field_data['package_display']) ? $field_data['package_display'] : '';
                        self::select( $prefix.'[show_in_package][]', self::yes_no_opts(), esc_html__('Enable package visibility', 'wp-job-board-pro'), $show_in_package, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), false, true, 'show_in_package');
                        self::select( $prefix.'[package_display]['.$field_counter.'][]', $packages, esc_html__('Packages', 'wp-job-board-pro'), $package_display, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), true, true, 'show_if_show_in_package');
                    }
                }

                do_action('wp_job_board_pro_custom_field_available_simple_callback', $type, $field_counter, $field_data, $p_prefix);
                ?>

            </div>
        </div>
        <?php
        $html = ob_get_clean();

        return $html;
    }

    public static function field_available_simple_without_placeholder_callback($type, $field_counter, $field_data, $p_prefix) {
        ob_start();
        $rand = $field_counter;

        $name_val = stripslashes(isset($field_data['name']) ? $field_data['name'] : 'Available Field');
        $id_val = $type;
        $description_val = stripslashes(isset($field_data['description']) ? $field_data['description'] : '');

        $required_val = isset($field_data['required']) ? $field_data['required'] : '';
        $show_in_submit_form_val = isset($field_data['show_in_submit_form']) ? $field_data['show_in_submit_form'] : 'yes';
        $show_in_admin_edit_val = isset($field_data['show_in_admin_edit']) ? $field_data['show_in_admin_edit'] : 'yes';

        $disable_check_val = isset($field_data['disable_check']) ? $field_data['disable_check'] : false;

        $prefix = 'wp-job-board-pro-custom-fields-'.$type;
        ?>
        <div class="wp-job-board-pro-custom-field-container wp-job-board-pro-custom-field-<?php echo esc_attr($type); ?>-container">
            <?php self::header_html($type, $rand, $name_val); ?>
            <?php self::hidden( $prefix.'[id][]', $id_val, 'wp-job-board-pro-custom-field-key'); ?>
            <div class="field-data form-group-wrapper" id="<?php echo esc_attr($type); ?>-field-wraper<?php echo esc_html($rand); ?>" style="display:none;">
                <input type="hidden" name="wp-job-board-pro-custom-fields-type[]" value="<?php echo esc_attr($type); ?>" />
                <input type="hidden" name="wp-job-board-pro-custom-fields-id[]" value="<?php echo esc_html($field_counter); ?>" />
                
                <?php
                self::text( $prefix.'[name][]', esc_html__('Label', 'wp-job-board-pro'), $name_val, '', 'wp-job-board-pro-custom-field-label');
                // self::text( $prefix.'[placeholder][]', esc_html__('Placeholder', 'wp-job-board-pro'), $placeholder_val);
                self::text( $prefix.'[description][]', esc_html__('Description', 'wp-job-board-pro'), $description_val);
                self::select( $prefix.'[show_in_submit_form][]', self::yes_no_opts(), esc_html__('Show in frontend form', 'wp-job-board-pro'), $show_in_submit_form_val, '', false, false, 'show_in_submit_form', $disable_check_val );
                self::select( $prefix.'[show_in_admin_edit][]', self::yes_no_opts(), esc_html__('Show in admin form', 'wp-job-board-pro'), $show_in_admin_edit_val, '', false, false, 'show_in_admin_edit', $disable_check_val );

                if ( $p_prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX ) {
                    $show_in_submit_form_candidate_val = isset($field_data['show_in_submit_form_candidate']) ? $field_data['show_in_submit_form_candidate'] : 'yes';
                    self::select( $prefix.'[show_in_submit_form_candidate][]', self::candidate_form_opts(), esc_html__('Show in Candidate Form', 'wp-job-board-pro'), $show_in_submit_form_candidate_val, '', false, false, 'show_if_show_in_submit_form' );
                }

                if ( $p_prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX || $p_prefix == WP_JOB_BOARD_PRO_EMPLOYER_PREFIX ) {
                    $show_in_register_form_val = isset($field_data['show_in_register_form']) ? $field_data['show_in_register_form'] : '';
                    $disable_check_register_val = isset($field_data['disable_check_register']) ? $field_data['disable_check_register'] : false;
                    self::select( $prefix.'[show_in_register_form][]', self::yes_no_opts(), esc_html__('Show in register form', 'wp-job-board-pro'), $show_in_register_form_val, '', false, false, '', $disable_check_register_val );
                }

                self::select( $prefix.'[required][]', self::yes_no_opts(), esc_html__('Required', 'wp-job-board-pro'), $required_val, '', false, false );

                // packages
                if ( $p_prefix == WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX ) {
                    $packages = self::get_packages();
                    if ( $packages ) {
                        $show_in_package = isset($field_data['show_in_package']) ? $field_data['show_in_package'] : '';
                        $package_display = isset($field_data['package_display']) ? $field_data['package_display'] : '';
                        self::select( $prefix.'[show_in_package][]', self::yes_no_opts(), esc_html__('Enable package visibility', 'wp-job-board-pro'), $show_in_package, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), false, true, 'show_in_package');
                        self::select( $prefix.'[package_display]['.$field_counter.'][]', $packages, esc_html__('Packages', 'wp-job-board-pro'), $package_display, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), true, true, 'show_if_show_in_package');
                    }
                }

                do_action('wp_job_board_pro_custom_field_available_simple_without_placeholder_callback', $type, $field_counter, $field_data, $p_prefix);
                ?>

            </div>
        </div>
        <?php
        $html = ob_get_clean();

        return $html;
    }

    public static function field_available_tax_callback($type, $field_counter, $field_data, $p_prefix) {
        ob_start();
        $rand = $field_counter;

        $name_val = stripslashes(isset($field_data['name']) ? $field_data['name'] : 'Custom Field');
        $id_val = $type;
        $placeholder_val = stripslashes(isset($field_data['placeholder']) ? $field_data['placeholder'] : '');
        $description_val = stripslashes(isset($field_data['description']) ? $field_data['description'] : '');
        $text_field_select_type = isset($field_data['select_type']) ? $field_data['select_type'] : 'pw_taxonomy_select';

        $required_val = isset($field_data['required']) ? $field_data['required'] : '';
        $show_in_submit_form_val = isset($field_data['show_in_submit_form']) ? $field_data['show_in_submit_form'] : 'yes';
        $show_in_admin_edit_val = isset($field_data['show_in_admin_edit']) ? $field_data['show_in_admin_edit'] : 'yes';

        $disable_check_val = isset($field_data['disable_check']) ? $field_data['disable_check'] : false;

        $prefix = 'wp-job-board-pro-custom-fields-'.$type;
        ?>
        <div class="wp-job-board-pro-custom-field-container wp-job-board-pro-custom-field-<?php echo esc_attr($type); ?>-container">
            <?php self::header_html($type, $rand, $name_val); ?>
            <?php self::hidden( $prefix.'[id][]', $id_val, 'wp-job-board-pro-custom-field-key'); ?>
            <div class="field-data form-group-wrapper" id="<?php echo esc_attr($type); ?>-field-wraper<?php echo esc_html($rand); ?>" style="display:none;">
                <input type="hidden" name="wp-job-board-pro-custom-fields-type[]" value="<?php echo esc_attr($type); ?>" />
                <input type="hidden" name="wp-job-board-pro-custom-fields-id[]" value="<?php echo esc_html($field_counter); ?>" />
                
                <?php
                self::text( $prefix.'[name][]', esc_html__('Label', 'wp-job-board-pro'), $name_val, '', 'wp-job-board-pro-custom-field-label');
                self::text( $prefix.'[placeholder][]', esc_html__('Placeholder', 'wp-job-board-pro'), $placeholder_val);
                self::text( $prefix.'[description][]', esc_html__('Description', 'wp-job-board-pro'), $description_val);

                $opts = array(
                    'pw_taxonomy_select' => esc_html__('Term Select', 'wp-job-board-pro'),
                    'pw_taxonomy_select_search' => esc_html__('Term Select Search', 'wp-job-board-pro'),
                    'pw_taxonomy_multiselect' => esc_html__('Term Multiselect', 'wp-job-board-pro'),
                    'pw_taxonomy_multiselect_search' => esc_html__('Term Multiselect Search', 'wp-job-board-pro'),
                    'taxonomy_multicheck' => esc_html__('Term Checklist', 'wp-job-board-pro'),
                    'taxonomy_radio' => esc_html__('Term Radio Button', 'wp-job-board-pro'),
                );
                self::select( $prefix.'[select_type][]', $opts, esc_html__('Template', 'wp-job-board-pro'), $text_field_select_type);
                self::select( $prefix.'[show_in_submit_form][]', self::yes_no_opts(), esc_html__('Show in frontend form', 'wp-job-board-pro'), $show_in_submit_form_val, '', false, false, 'show_in_submit_form', $disable_check_val );
                self::select( $prefix.'[show_in_admin_edit][]', self::yes_no_opts(), esc_html__('Show in admin form', 'wp-job-board-pro'), $show_in_admin_edit_val, '', false, false, 'show_in_admin_edit', $disable_check_val );

                if ( $p_prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX ) {
                    $show_in_submit_form_candidate_val = isset($field_data['show_in_submit_form_candidate']) ? $field_data['show_in_submit_form_candidate'] : 'yes';
                    self::select( $prefix.'[show_in_submit_form_candidate][]', self::candidate_form_opts(), esc_html__('Show in Candidate Form', 'wp-job-board-pro'), $show_in_submit_form_candidate_val, '', false, false, 'show_if_show_in_submit_form' );
                }

                if ( $p_prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX || $p_prefix == WP_JOB_BOARD_PRO_EMPLOYER_PREFIX ) {
                    $show_in_register_form_val = isset($field_data['show_in_register_form']) ? $field_data['show_in_register_form'] : '';
                    $disable_check_register_val = isset($field_data['disable_check_register']) ? $field_data['disable_check_register'] : false;
                    self::select( $prefix.'[show_in_register_form][]', self::yes_no_opts(), esc_html__('Show in register form', 'wp-job-board-pro'), $show_in_register_form_val, '', false, false, '', $disable_check_register_val );
                }

                self::select( $prefix.'[required][]', self::yes_no_opts(), esc_html__('Required', 'wp-job-board-pro'), $required_val, '', false, false );

                // packages
                if ( $p_prefix == WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX ) {
                    $packages = self::get_packages();
                    if ( $packages ) {
                        $show_in_package = isset($field_data['show_in_package']) ? $field_data['show_in_package'] : '';
                        $package_display = isset($field_data['package_display']) ? $field_data['package_display'] : '';
                        self::select( $prefix.'[show_in_package][]', self::yes_no_opts(), esc_html__('Enable package visibility', 'wp-job-board-pro'), $show_in_package, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), false, true, 'show_in_package');
                        self::select( $prefix.'[package_display]['.$field_counter.'][]', $packages, esc_html__('Packages', 'wp-job-board-pro'), $package_display, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), true, true, 'show_if_show_in_package');
                    }
                }

                do_action('wp_job_board_pro_custom_field_available_tax_callback', $type, $field_counter, $field_data, $p_prefix);
                ?>

            </div>
        </div>
        <?php
        $html = ob_get_clean();

        return $html;
    }

    public static function field_available_file_callback($type, $field_counter, $field_data, $p_prefix) {
        ob_start();
        $rand = $field_counter;

        $name_val = stripslashes(isset($field_data['name']) ? $field_data['name'] : 'Available Field');
        $id_val = $type;
        $placeholder_val = stripslashes(isset($field_data['placeholder']) ? $field_data['placeholder'] : '');
        $description_val = stripslashes(isset($field_data['description']) ? $field_data['description'] : '');
        $file_limit = isset($field_data['file_limit']) ? $field_data['file_limit'] : 5;
        $file_size = isset($field_data['file_size']) ? $field_data['file_size'] : 5;

        $required_val = isset($field_data['required']) ? $field_data['required'] : '';
        $show_in_submit_form_val = isset($field_data['show_in_submit_form']) ? $field_data['show_in_submit_form'] : 'yes';
        $show_in_admin_edit_val = isset($field_data['show_in_admin_edit']) ? $field_data['show_in_admin_edit'] : 'yes';

        $disable_check_val = isset($field_data['disable_check']) ? $field_data['disable_check'] : false;

        $text_field_allow_types = isset($field_data['allow_types']) ? $field_data['allow_types'] : (isset($field_data['mime_types']) ? $field_data['mime_types'] : '');

        $prefix = 'wp-job-board-pro-custom-fields-'.$type;
        ?>
        <div class="wp-job-board-pro-custom-field-container wp-job-board-pro-custom-field-<?php echo esc_attr($type); ?>-container">
            <?php self::header_html($type, $rand, $name_val); ?>
            <?php self::hidden( $prefix.'[id][]', $id_val, 'wp-job-board-pro-custom-field-key'); ?>
            <div class="field-data form-group-wrapper" id="<?php echo esc_attr($type); ?>-field-wraper<?php echo esc_html($rand); ?>" style="display:none;">
                <input type="hidden" name="wp-job-board-pro-custom-fields-type[]" value="<?php echo esc_attr($type); ?>" />
                <input type="hidden" name="wp-job-board-pro-custom-fields-id[]" value="<?php echo esc_html($field_counter); ?>" />
                
                <?php
                self::text( $prefix.'[name][]', esc_html__('Label', 'wp-job-board-pro'), $name_val, '', 'wp-job-board-pro-custom-field-label');
                self::text( $prefix.'[placeholder][]', esc_html__('Placeholder', 'wp-job-board-pro'), $placeholder_val);
                self::text( $prefix.'[description][]', esc_html__('Description', 'wp-job-board-pro'), $description_val);
                
                self::number( $prefix.'[limit_file][]', esc_html__('Limit Files', 'wp-job-board-pro'), $file_limit);
                self::number( $prefix.'[file_size][]', esc_html__('Limit Size', 'wp-job-board-pro'), $file_size, esc_html__('Enter limit size by megabyte', 'wp-job-board-pro'));

                $mime_types = get_allowed_mime_types();
                
                self::select( $prefix.'[allow_types]['.$field_counter.'][]', $mime_types, esc_html__('Allowed file types', 'wp-job-board-pro'), $text_field_allow_types, '', true);

                self::select( $prefix.'[show_in_submit_form][]', self::yes_no_opts(), esc_html__('Show in frontend form', 'wp-job-board-pro'), $show_in_submit_form_val, '', false, false, 'show_in_submit_form', $disable_check_val );
                self::select( $prefix.'[show_in_admin_edit][]', self::yes_no_opts(), esc_html__('Show in admin form', 'wp-job-board-pro'), $show_in_admin_edit_val, '', false, false, 'show_in_admin_edit', $disable_check_val );

                if ( $p_prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX ) {
                    $show_in_submit_form_candidate_val = isset($field_data['show_in_submit_form_candidate']) ? $field_data['show_in_submit_form_candidate'] : 'yes';
                    self::select( $prefix.'[show_in_submit_form_candidate][]', self::candidate_form_opts(), esc_html__('Show in Candidate Form', 'wp-job-board-pro'), $show_in_submit_form_candidate_val, '', false, false, 'show_if_show_in_submit_form' );
                }

                if ( $p_prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX || $p_prefix == WP_JOB_BOARD_PRO_EMPLOYER_PREFIX ) {
                    $show_in_register_form_val = isset($field_data['show_in_register_form']) ? $field_data['show_in_register_form'] : '';
                    $disable_check_register_val = isset($field_data['disable_check_register']) ? $field_data['disable_check_register'] : false;
                    self::select( $prefix.'[show_in_register_form][]', self::yes_no_opts(), esc_html__('Show in register form', 'wp-job-board-pro'), $show_in_register_form_val, '', false, false, '', $disable_check_register_val );
                }

                self::select( $prefix.'[required][]', self::yes_no_opts(), esc_html__('Required', 'wp-job-board-pro'), $required_val, '', false, false );

                // packages
                if ( $p_prefix == WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX ) {
                    $packages = self::get_packages();
                    if ( $packages ) {
                        $show_in_package = isset($field_data['show_in_package']) ? $field_data['show_in_package'] : '';
                        $package_display = isset($field_data['package_display']) ? $field_data['package_display'] : '';
                        self::select( $prefix.'[show_in_package][]', self::yes_no_opts(), esc_html__('Enable package visibility', 'wp-job-board-pro'), $show_in_package, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), false, true, 'show_in_package');
                        self::select( $prefix.'[package_display]['.$field_counter.'][]', $packages, esc_html__('Packages', 'wp-job-board-pro'), $package_display, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), true, true, 'show_if_show_in_package');
                    }
                }

                do_action('wp_job_board_pro_custom_field_available_file_callback', $type, $field_counter, $field_data, $p_prefix);
                ?>

            </div>
        </div>
        <?php
        $html = ob_get_clean();

        return $html;
    }

    public static function field_available_files_callback($type, $field_counter, $field_data, $p_prefix) {
        ob_start();
        $rand = $field_counter;

        $name_val = stripslashes(isset($field_data['name']) ? $field_data['name'] : 'Available Field');
        $id_val = $type;
        $placeholder_val = stripslashes(isset($field_data['placeholder']) ? $field_data['placeholder'] : '');
        $description_val = stripslashes(isset($field_data['description']) ? $field_data['description'] : '');
        $file_limit = isset($field_data['file_limit']) ? $field_data['file_limit'] : 5;
        $file_size = isset($field_data['file_size']) ? $field_data['file_size'] : 5;

        $required_val = isset($field_data['required']) ? $field_data['required'] : '';
        $show_in_submit_form_val = isset($field_data['show_in_submit_form']) ? $field_data['show_in_submit_form'] : 'yes';
        $show_in_admin_edit_val = isset($field_data['show_in_admin_edit']) ? $field_data['show_in_admin_edit'] : 'yes';

        $disable_check_val = isset($field_data['disable_check']) ? $field_data['disable_check'] : false;

        $text_field_allow_types = isset($field_data['allow_types']) ? $field_data['allow_types'] : (isset($field_data['mime_types']) ? $field_data['mime_types'] : '');

        $prefix = 'wp-job-board-pro-custom-fields-'.$type;
        ?>
        <div class="wp-job-board-pro-custom-field-container wp-job-board-pro-custom-field-<?php echo esc_attr($type); ?>-container">
            <?php self::header_html($type, $rand, $name_val); ?>
            <?php self::hidden( $prefix.'[id][]', $id_val, 'wp-job-board-pro-custom-field-key'); ?>
            <div class="field-data form-group-wrapper" id="<?php echo esc_attr($type); ?>-field-wraper<?php echo esc_html($rand); ?>" style="display:none;">
                <input type="hidden" name="wp-job-board-pro-custom-fields-type[]" value="<?php echo esc_attr($type); ?>" />
                <input type="hidden" name="wp-job-board-pro-custom-fields-id[]" value="<?php echo esc_html($field_counter); ?>" />
                
                <?php
                self::text( $prefix.'[name][]', esc_html__('Label', 'wp-job-board-pro'), $name_val, '', 'wp-job-board-pro-custom-field-label');
                self::text( $prefix.'[placeholder][]', esc_html__('Placeholder', 'wp-job-board-pro'), $placeholder_val);
                self::text( $prefix.'[description][]', esc_html__('Description', 'wp-job-board-pro'), $description_val);
                
                self::number( $prefix.'[file_limit][]', esc_html__('File limit', 'wp-job-board-pro'), $file_limit);
                self::number( $prefix.'[file_size][]', esc_html__('Limit Size', 'wp-job-board-pro'), $file_size, esc_html__('Enter limit size by megabyte', 'wp-job-board-pro'));

                $mime_types = get_allowed_mime_types();
                self::select( $prefix.'[allow_types]['.$field_counter.'][]', $mime_types, esc_html__('Allowed file types', 'wp-job-board-pro'), $text_field_allow_types, '', true);

                self::select( $prefix.'[show_in_submit_form][]', self::yes_no_opts(), esc_html__('Show in frontend form', 'wp-job-board-pro'), $show_in_submit_form_val, '', false, false, 'show_in_submit_form', $disable_check_val );
                self::select( $prefix.'[show_in_admin_edit][]', self::yes_no_opts(), esc_html__('Show in admin form', 'wp-job-board-pro'), $show_in_admin_edit_val, '', false, false, 'show_in_admin_edit', $disable_check_val );

                if ( $p_prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX ) {
                    $show_in_submit_form_candidate_val = isset($field_data['show_in_submit_form_candidate']) ? $field_data['show_in_submit_form_candidate'] : 'yes';
                    self::select( $prefix.'[show_in_submit_form_candidate][]', self::candidate_form_opts(), esc_html__('Show in Candidate Form', 'wp-job-board-pro'), $show_in_submit_form_candidate_val, '', false, false, 'show_if_show_in_submit_form' );
                }

                if ( $p_prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX || $p_prefix == WP_JOB_BOARD_PRO_EMPLOYER_PREFIX ) {
                    $show_in_register_form_val = isset($field_data['show_in_register_form']) ? $field_data['show_in_register_form'] : '';
                    $disable_check_register_val = isset($field_data['disable_check_register']) ? $field_data['disable_check_register'] : false;
                    self::select( $prefix.'[show_in_register_form][]', self::yes_no_opts(), esc_html__('Show in register form', 'wp-job-board-pro'), $show_in_register_form_val, '', false, false, '', $disable_check_register_val );
                }

                self::select( $prefix.'[required][]', self::yes_no_opts(), esc_html__('Required', 'wp-job-board-pro'), $required_val, '', false, false );

                // packages
                if ( $p_prefix == WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX ) {
                    $packages = self::get_packages();
                    if ( $packages ) {
                        $show_in_package = isset($field_data['show_in_package']) ? $field_data['show_in_package'] : '';
                        $package_display = isset($field_data['package_display']) ? $field_data['package_display'] : '';
                        self::select( $prefix.'[show_in_package][]', self::yes_no_opts(), esc_html__('Enable package visibility', 'wp-job-board-pro'), $show_in_package, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), false, true, 'show_in_package');
                        self::select( $prefix.'[package_display]['.$field_counter.'][]', $packages, esc_html__('Packages', 'wp-job-board-pro'), $package_display, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), true, true, 'show_if_show_in_package');
                    }
                }

                do_action('wp_job_board_pro_custom_field_available_files_callback', $type, $field_counter, $field_data, $p_prefix);
                ?>

            </div>
        </div>
        <?php
        $html = ob_get_clean();

        return $html;
    }

    public static function field_available_description_callback($type, $field_counter, $field_data, $p_prefix) {
        ob_start();
        $rand = $field_counter;

        $name_val = stripslashes(isset($field_data['name']) ? $field_data['name'] : 'Custom Field');
        $id_val = $type;
        $placeholder_val = stripslashes(isset($field_data['placeholder']) ? $field_data['placeholder'] : '');
        $description_val = stripslashes(isset($field_data['description']) ? $field_data['description'] : '');
        $text_field_select_type = isset($field_data['select_type']) ? $field_data['select_type'] : 'textarea';

        $required_val = isset($field_data['required']) ? $field_data['required'] : '';
        $show_in_submit_form_val = isset($field_data['show_in_submit_form']) ? $field_data['show_in_submit_form'] : 'yes';
        $show_in_admin_edit_val = isset($field_data['show_in_admin_edit']) ? $field_data['show_in_admin_edit'] : 'yes';

        $disable_check_val = isset($field_data['disable_check']) ? $field_data['disable_check'] : false;
        $prefix = 'wp-job-board-pro-custom-fields-'.$type;
        ?>
        <div class="wp-job-board-pro-custom-field-container wp-job-board-pro-custom-field-<?php echo esc_attr($type); ?>-container">
            <?php self::header_html($type, $rand, $name_val); ?>
            <?php self::hidden( $prefix.'[id][]', $id_val, 'wp-job-board-pro-custom-field-key'); ?>
            <div class="field-data form-group-wrapper" id="<?php echo esc_attr($type); ?>-field-wraper<?php echo esc_html($rand); ?>" style="display:none;">
                <input type="hidden" name="wp-job-board-pro-custom-fields-type[]" value="<?php echo esc_attr($type); ?>" />
                <input type="hidden" name="wp-job-board-pro-custom-fields-id[]" value="<?php echo esc_html($field_counter); ?>" />

                <?php

                self::text( $prefix.'[name][]', esc_html__('Label', 'wp-job-board-pro'), $name_val, '', 'wp-job-board-pro-custom-field-label');
                self::text( $prefix.'[placeholder][]', esc_html__('Placeholder', 'wp-job-board-pro'), $placeholder_val);
                self::text( $prefix.'[description][]', esc_html__('Description', 'wp-job-board-pro'), $description_val);

                $opts = array(
                    'textarea' => esc_html__('Textarea', 'wp-job-board-pro'),
                    'wysiwyg' => esc_html__('WP Editor', 'wp-job-board-pro'),
                );
                self::select( $prefix.'[select_type][]', $opts, esc_html__('Template', 'wp-job-board-pro'), $text_field_select_type);
                self::select( $prefix.'[show_in_submit_form][]', self::yes_no_opts(), esc_html__('Show in frontend form', 'wp-job-board-pro'), $show_in_submit_form_val, '', false, false, 'show_in_submit_form', $disable_check_val );
                self::select( $prefix.'[show_in_admin_edit][]', self::yes_no_opts(), esc_html__('Show in admin form', 'wp-job-board-pro'), $show_in_admin_edit_val, '', false, false, 'show_in_admin_edit', $disable_check_val );

                if ( $p_prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX ) {
                    $show_in_submit_form_candidate_val = isset($field_data['show_in_submit_form_candidate']) ? $field_data['show_in_submit_form_candidate'] : 'yes';
                    self::select( $prefix.'[show_in_submit_form_candidate][]', self::candidate_form_opts(), esc_html__('Show in Candidate Form', 'wp-job-board-pro'), $show_in_submit_form_candidate_val, '', false, false, 'show_if_show_in_submit_form' );
                }

                if ( $p_prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX || $p_prefix == WP_JOB_BOARD_PRO_EMPLOYER_PREFIX ) {
                    $show_in_register_form_val = isset($field_data['show_in_register_form']) ? $field_data['show_in_register_form'] : '';
                    $disable_check_register_val = isset($field_data['disable_check_register']) ? $field_data['disable_check_register'] : false;
                    self::select( $prefix.'[show_in_register_form][]', self::yes_no_opts(), esc_html__('Show in register form', 'wp-job-board-pro'), $show_in_register_form_val, '', false, false, '', $disable_check_register_val );
                }

                self::select( $prefix.'[required][]', self::yes_no_opts(), esc_html__('Required', 'wp-job-board-pro'), $required_val, '', false, false );

                // packages
                if ( $p_prefix == WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX ) {
                    $packages = self::get_packages();
                    if ( $packages ) {
                        $show_in_package = isset($field_data['show_in_package']) ? $field_data['show_in_package'] : '';
                        $package_display = isset($field_data['package_display']) ? $field_data['package_display'] : '';
                        self::select( $prefix.'[show_in_package][]', self::yes_no_opts(), esc_html__('Enable package visibility', 'wp-job-board-pro'), $show_in_package, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), false, true, 'show_in_package');
                        self::select( $prefix.'[package_display]['.$field_counter.'][]', $packages, esc_html__('Packages', 'wp-job-board-pro'), $package_display, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), true, true, 'show_if_show_in_package');
                    }
                }

                do_action('wp_job_board_pro_custom_field_available_description_callback', $type, $field_counter, $field_data, $p_prefix);
                ?>

            </div>
        </div>
        <?php
        $html = ob_get_clean();

        return $html;
    }

    public static function field_available_select_option_callback($type, $field_counter, $field_data, $p_prefix) {
        ob_start();
        $rand = $field_counter;

        $name_val = stripslashes(isset($field_data['name']) ? $field_data['name'] : 'Available Field');
        $id_val = $type;
        $placeholder_val = stripslashes(isset($field_data['placeholder']) ? $field_data['placeholder'] : '');
        $description_val = stripslashes(isset($field_data['description']) ? $field_data['description'] : '');
        $text_field_select_type = isset($field_data['select_type']) ? $field_data['select_type'] : '';
        $text_field_options = stripslashes(isset($field_data['options']) ? $field_data['options'] : '');

        $required_val = isset($field_data['required']) ? $field_data['required'] : '';
        $show_in_submit_form_val = isset($field_data['show_in_submit_form']) ? $field_data['show_in_submit_form'] : 'yes';
        $show_in_admin_edit_val = isset($field_data['show_in_admin_edit']) ? $field_data['show_in_admin_edit'] : 'yes';

        $disable_check_val = isset($field_data['disable_check']) ? $field_data['disable_check'] : false;

        $prefix = 'wp-job-board-pro-custom-fields-'.$type;
        ?>
        <div class="wp-job-board-pro-custom-field-container wp-job-board-pro-custom-field-<?php echo esc_attr($type); ?>-container">
            <?php self::header_html($type, $rand, $name_val); ?>
            <?php self::hidden( $prefix.'[id][]', $id_val, 'wp-job-board-pro-custom-field-key'); ?>
            <div class="field-data form-group-wrapper" id="<?php echo esc_attr($type); ?>-field-wraper<?php echo esc_html($rand); ?>" style="display:none;">
                <input type="hidden" name="wp-job-board-pro-custom-fields-type[]" value="<?php echo esc_attr($type); ?>" />
                <input type="hidden" name="wp-job-board-pro-custom-fields-id[]" value="<?php echo esc_html($field_counter); ?>" />
                
                <?php
                self::text( $prefix.'[name][]', esc_html__('Label', 'wp-job-board-pro'), $name_val, '', 'wp-job-board-pro-custom-field-label');
                self::text( $prefix.'[placeholder][]', esc_html__('Placeholder', 'wp-job-board-pro'), $placeholder_val);
                self::text( $prefix.'[description][]', esc_html__('Description', 'wp-job-board-pro'), $description_val);

                $opts = array(
                    'pw_select' => esc_html__('Select', 'wp-job-board-pro'),
                    'pw_multiselect' => esc_html__('Multi Select', 'wp-job-board-pro'),
                    'multicheck' => esc_html__('Multi Check', 'wp-job-board-pro'),
                    'radio' => esc_html__('Radio Button', 'wp-job-board-pro'),
                );
                self::select( $prefix.'[select_type][]', $opts, esc_html__('Template', 'wp-job-board-pro'), $text_field_select_type);

                self::textarea( $prefix.'[options][]', esc_html__('Options', 'wp-job-board-pro'), $text_field_options, esc_html__('Add each option in a new line.', 'wp-job-board-pro'), true);
                self::select( $prefix.'[show_in_submit_form][]', self::yes_no_opts(), esc_html__('Show in frontend form', 'wp-job-board-pro'), $show_in_submit_form_val, '', false, false, 'show_in_submit_form', $disable_check_val );
                self::select( $prefix.'[show_in_admin_edit][]', self::yes_no_opts(), esc_html__('Show in admin form', 'wp-job-board-pro'), $show_in_admin_edit_val, '', false, false, 'show_in_admin_edit', $disable_check_val );

                if ( $p_prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX ) {
                    $show_in_submit_form_candidate_val = isset($field_data['show_in_submit_form_candidate']) ? $field_data['show_in_submit_form_candidate'] : 'yes';
                    self::select( $prefix.'[show_in_submit_form_candidate][]', self::candidate_form_opts(), esc_html__('Show in Candidate Form', 'wp-job-board-pro'), $show_in_submit_form_candidate_val, '', false, false, 'show_if_show_in_submit_form' );
                }

                if ( $p_prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX || $p_prefix == WP_JOB_BOARD_PRO_EMPLOYER_PREFIX ) {
                    $show_in_register_form_val = isset($field_data['show_in_register_form']) ? $field_data['show_in_register_form'] : '';
                    $disable_check_register_val = isset($field_data['disable_check_register']) ? $field_data['disable_check_register'] : false;
                    self::select( $prefix.'[show_in_register_form][]', self::yes_no_opts(), esc_html__('Show in register form', 'wp-job-board-pro'), $show_in_register_form_val, '', false, false, '', $disable_check_register_val );
                }

                self::select( $prefix.'[required][]', self::yes_no_opts(), esc_html__('Required', 'wp-job-board-pro'), $required_val, '', false, false );

                // packages
                if ( $p_prefix == WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX ) {
                    $packages = self::get_packages();
                    if ( $packages ) {
                        $show_in_package = isset($field_data['show_in_package']) ? $field_data['show_in_package'] : '';
                        $package_display = isset($field_data['package_display']) ? $field_data['package_display'] : '';
                        self::select( $prefix.'[show_in_package][]', self::yes_no_opts(), esc_html__('Enable package visibility', 'wp-job-board-pro'), $show_in_package, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), false, true, 'show_in_package');
                        self::select( $prefix.'[package_display]['.$field_counter.'][]', $packages, esc_html__('Packages', 'wp-job-board-pro'), $package_display, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), true, true, 'show_if_show_in_package');
                    }
                }

                do_action('wp_job_board_pro_custom_field_available_select_callback', $type, $field_counter, $field_data, $p_prefix);
                ?>

            </div>
        </div>
        <?php
        $html = ob_get_clean();

        return $html;
    }

    public static function field_available_location_callback($type, $field_counter, $field_data, $p_prefix) {
        ob_start();
        $rand = $field_counter;

        $name_val = stripslashes(isset($field_data['name']) ? $field_data['name'] : 'Available Field');
        $id_val = $type;
        $placeholder_val = stripslashes(isset($field_data['placeholder']) ? $field_data['placeholder'] : '');
        $description_val = stripslashes(isset($field_data['description']) ? $field_data['description'] : '');
        

        $required_val = isset($field_data['required']) ? $field_data['required'] : '';
        $show_in_submit_form_val = isset($field_data['show_in_submit_form']) ? $field_data['show_in_submit_form'] : 'yes';
        $show_in_admin_edit_val = isset($field_data['show_in_admin_edit']) ? $field_data['show_in_admin_edit'] : 'yes';

        $disable_check_val = isset($field_data['disable_check']) ? $field_data['disable_check'] : false;

        $prefix = 'wp-job-board-pro-custom-fields-'.$type;
        ?>
        <div class="wp-job-board-pro-custom-field-container wp-job-board-pro-custom-field-<?php echo esc_attr($type); ?>-container">
            <?php self::header_html($type, $rand, $name_val); ?>
            <?php self::hidden( $prefix.'[id][]', $id_val, 'wp-job-board-pro-custom-field-key'); ?>
            <div class="field-data form-group-wrapper" id="<?php echo esc_attr($type); ?>-field-wraper<?php echo esc_html($rand); ?>" style="display:none;">
                <input type="hidden" name="wp-job-board-pro-custom-fields-type[]" value="<?php echo esc_attr($type); ?>" />
                <input type="hidden" name="wp-job-board-pro-custom-fields-id[]" value="<?php echo esc_html($field_counter); ?>" />
                
                <?php
                self::text( $prefix.'[name][]', esc_html__('Label', 'wp-job-board-pro'), $name_val, '', 'wp-job-board-pro-custom-field-label');
                self::text( $prefix.'[placeholder][]', esc_html__('Placeholder', 'wp-job-board-pro'), $placeholder_val);
                self::text( $prefix.'[description][]', esc_html__('Description', 'wp-job-board-pro'), $description_val);

                $location_type = wp_job_board_pro_get_option('location_multiple_fields', 'yes');
                if ( $location_type === 'no' ) {
                    $text_field_select_type = isset($field_data['select_type']) ? $field_data['select_type'] : '';
                    $opts = array(
                        'pw_taxonomy_select' => esc_html__('Term Select', 'wp-job-board-pro'),
                        'pw_taxonomy_select_search' => esc_html__('Term Select Search', 'wp-job-board-pro'),
                        'pw_taxonomy_multiselect' => esc_html__('Term Multiselect', 'wp-job-board-pro'),
                        'pw_taxonomy_multiselect_search' => esc_html__('Term Multiselect Search', 'wp-job-board-pro'),
                        'taxonomy_multicheck' => esc_html__('Term Checklist', 'wp-job-board-pro'),
                        'taxonomy_radio' => esc_html__('Term Radio Button', 'wp-job-board-pro'),
                    );
                    self::select( $prefix.'[select_type][]', $opts, esc_html__('Template', 'wp-job-board-pro'), $text_field_select_type);
                } else {
                    $text_field_select_type = isset($field_data['select_type_search']) ? $field_data['select_type_search'] : '';
                    $opts = array(
                        'wpjb_taxonomy_location' => esc_html__('Term Select', 'wp-job-board-pro'),
                        'wpjb_taxonomy_location_search' => esc_html__('Term Select Search', 'wp-job-board-pro'),
                    );
                    self::select( $prefix.'[select_type_search][]', $opts, esc_html__('Template', 'wp-job-board-pro'), $text_field_select_type);
                }

                self::select( $prefix.'[show_in_submit_form][]', self::yes_no_opts(), esc_html__('Show in frontend form', 'wp-job-board-pro'), $show_in_submit_form_val, '', false, false, 'show_in_submit_form', $disable_check_val );
                self::select( $prefix.'[show_in_admin_edit][]', self::yes_no_opts(), esc_html__('Show in admin form', 'wp-job-board-pro'), $show_in_admin_edit_val, '', false, false, 'show_in_admin_edit', $disable_check_val );

                if ( $p_prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX ) {
                    $show_in_submit_form_candidate_val = isset($field_data['show_in_submit_form_candidate']) ? $field_data['show_in_submit_form_candidate'] : 'yes';
                    self::select( $prefix.'[show_in_submit_form_candidate][]', self::candidate_form_opts(), esc_html__('Show in Candidate Form', 'wp-job-board-pro'), $show_in_submit_form_candidate_val, '', false, false, 'show_if_show_in_submit_form' );
                }

                if ( $p_prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX || $p_prefix == WP_JOB_BOARD_PRO_EMPLOYER_PREFIX ) {
                    $show_in_register_form_val = isset($field_data['show_in_register_form']) ? $field_data['show_in_register_form'] : '';
                    $disable_check_register_val = isset($field_data['disable_check_register']) ? $field_data['disable_check_register'] : false;
                    self::select( $prefix.'[show_in_register_form][]', self::yes_no_opts(), esc_html__('Show in register form', 'wp-job-board-pro'), $show_in_register_form_val, '', false, false, '', $disable_check_register_val );
                }

                self::select( $prefix.'[required][]', self::yes_no_opts(), esc_html__('Required', 'wp-job-board-pro'), $required_val, '', false, false );

                // packages
                if ( $p_prefix == WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX ) {
                    $packages = self::get_packages();
                    if ( $packages ) {
                        $show_in_package = isset($field_data['show_in_package']) ? $field_data['show_in_package'] : '';
                        $package_display = isset($field_data['package_display']) ? $field_data['package_display'] : '';
                        self::select( $prefix.'[show_in_package][]', self::yes_no_opts(), esc_html__('Enable package visibility', 'wp-job-board-pro'), $show_in_package, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), false, true, 'show_in_package');
                        self::select( $prefix.'[package_display]['.$field_counter.'][]', $packages, esc_html__('Packages', 'wp-job-board-pro'), $package_display, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), true, true, 'show_if_show_in_package');
                    }
                }

                do_action('wp_job_board_pro_custom_field_available_location_callback', $type, $field_counter, $field_data, $p_prefix);
                ?>

            </div>
        </div>
        <?php
        $html = ob_get_clean();

        return $html;
    }

    public static function field_available_salary_type_callback($type, $field_counter, $field_data, $p_prefix) {
        ob_start();
        $rand = $field_counter;

        $name_val = stripslashes(isset($field_data['name']) ? $field_data['name'] : 'Available Field');
        $id_val = $type;
        $placeholder_val = stripslashes(isset($field_data['placeholder']) ? $field_data['placeholder'] : '');
        $description_val = stripslashes(isset($field_data['description']) ? $field_data['description'] : '');

        $required_val = isset($field_data['required']) ? $field_data['required'] : '';
        $show_in_submit_form_val = isset($field_data['show_in_submit_form']) ? $field_data['show_in_submit_form'] : 'yes';
        $show_in_admin_edit_val = isset($field_data['show_in_admin_edit']) ? $field_data['show_in_admin_edit'] : 'yes';

        $disable_check_val = isset($field_data['disable_check']) ? $field_data['disable_check'] : false;

        $prefix = 'wp-job-board-pro-custom-fields-'.$type;
        ?>
        <div class="wp-job-board-pro-custom-field-container wp-job-board-pro-custom-field-<?php echo esc_attr($type); ?>-container">
            <?php self::header_html($type, $rand, $name_val); ?>
            <?php self::hidden( $prefix.'[id][]', $id_val, 'wp-job-board-pro-custom-field-key'); ?>
            <div class="field-data form-group-wrapper" id="<?php echo esc_attr($type); ?>-field-wraper<?php echo esc_html($rand); ?>" style="display:none;">
                <input type="hidden" name="wp-job-board-pro-custom-fields-type[]" value="<?php echo esc_attr($type); ?>" />
                <input type="hidden" name="wp-job-board-pro-custom-fields-id[]" value="<?php echo esc_html($field_counter); ?>" />
                
                <?php
                self::text( $prefix.'[name][]', esc_html__('Label', 'wp-job-board-pro'), $name_val, '', 'wp-job-board-pro-custom-field-label');
                self::text( $prefix.'[placeholder][]', esc_html__('Placeholder', 'wp-job-board-pro'), $placeholder_val);
                self::text( $prefix.'[description][]', esc_html__('Description', 'wp-job-board-pro'), $description_val);
                
                $salary_types = WP_Job_Board_Pro_Mixes::get_default_salary_types();
                $d_types = array();
                if ( $salary_types ) {
                    foreach ($salary_types as $key => $value) {
                        $d_types[] = $key;
                    }
                }
                $text_field_allow_types = isset($field_data['allow_salary_types']) ? $field_data['allow_salary_types'] : $d_types;
                
                self::select( $prefix.'[allow_salary_types]['.$field_counter.'][]', $salary_types, esc_html__('Allowed salary types', 'wp-job-board-pro'), $text_field_allow_types, '', true, true);

                self::select( $prefix.'[show_in_submit_form][]', self::yes_no_opts(), esc_html__('Show in frontend form', 'wp-job-board-pro'), $show_in_submit_form_val, '', false, false, 'show_in_submit_form', $disable_check_val );
                self::select( $prefix.'[show_in_admin_edit][]', self::yes_no_opts(), esc_html__('Show in admin form', 'wp-job-board-pro'), $show_in_admin_edit_val, '', false, false, 'show_in_admin_edit', $disable_check_val );

                if ( $p_prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX ) {
                    $show_in_submit_form_candidate_val = isset($field_data['show_in_submit_form_candidate']) ? $field_data['show_in_submit_form_candidate'] : 'yes';
                    self::select( $prefix.'[show_in_submit_form_candidate][]', self::candidate_form_opts(), esc_html__('Show in Candidate Form', 'wp-job-board-pro'), $show_in_submit_form_candidate_val, '', false, false, 'show_if_show_in_submit_form' );
                }

                if ( $p_prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX || $p_prefix == WP_JOB_BOARD_PRO_EMPLOYER_PREFIX ) {
                    $show_in_register_form_val = isset($field_data['show_in_register_form']) ? $field_data['show_in_register_form'] : '';
                    $disable_check_register_val = isset($field_data['disable_check_register']) ? $field_data['disable_check_register'] : false;
                    self::select( $prefix.'[show_in_register_form][]', self::yes_no_opts(), esc_html__('Show in register form', 'wp-job-board-pro'), $show_in_register_form_val, '', false, false, '', $disable_check_register_val );
                }

                self::select( $prefix.'[required][]', self::yes_no_opts(), esc_html__('Required', 'wp-job-board-pro'), $required_val, '', false, false );

                // packages
                if ( $p_prefix == WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX ) {
                    $packages = self::get_packages();
                    if ( $packages ) {
                        $show_in_package = isset($field_data['show_in_package']) ? $field_data['show_in_package'] : '';
                        $package_display = isset($field_data['package_display']) ? $field_data['package_display'] : '';
                        self::select( $prefix.'[show_in_package][]', self::yes_no_opts(), esc_html__('Enable package visibility', 'wp-job-board-pro'), $show_in_package, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), false, true, 'show_in_package');
                        self::select( $prefix.'[package_display]['.$field_counter.'][]', $packages, esc_html__('Packages', 'wp-job-board-pro'), $package_display, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), true, true, 'show_if_show_in_package');
                    }
                }

                do_action('wp_job_board_pro_custom_field_available_salary_type_callback', $type, $field_counter, $field_data, $p_prefix);
                ?>

            </div>
        </div>
        <?php
        $html = ob_get_clean();

        return $html;
    }
    
    public static function field_available_apply_type_callback($type, $field_counter, $field_data, $p_prefix) {
        ob_start();
        $rand = $field_counter;

        $name_val = stripslashes(isset($field_data['name']) ? $field_data['name'] : 'Available Field');
        $id_val = $type;
        $placeholder_val = stripslashes(isset($field_data['placeholder']) ? $field_data['placeholder'] : '');
        $description_val = stripslashes(isset($field_data['description']) ? $field_data['description'] : '');

        $required_val = isset($field_data['required']) ? $field_data['required'] : '';
        $show_in_submit_form_val = isset($field_data['show_in_submit_form']) ? $field_data['show_in_submit_form'] : 'yes';
        $show_in_admin_edit_val = isset($field_data['show_in_admin_edit']) ? $field_data['show_in_admin_edit'] : 'yes';

        $disable_check_val = isset($field_data['disable_check']) ? $field_data['disable_check'] : false;

        $prefix = 'wp-job-board-pro-custom-fields-'.$type;
        ?>
        <div class="wp-job-board-pro-custom-field-container wp-job-board-pro-custom-field-<?php echo esc_attr($type); ?>-container">
            <?php self::header_html($type, $rand, $name_val); ?>
            <?php self::hidden( $prefix.'[id][]', $id_val, 'wp-job-board-pro-custom-field-key'); ?>
            <div class="field-data form-group-wrapper" id="<?php echo esc_attr($type); ?>-field-wraper<?php echo esc_html($rand); ?>" style="display:none;">
                <input type="hidden" name="wp-job-board-pro-custom-fields-type[]" value="<?php echo esc_attr($type); ?>" />
                <input type="hidden" name="wp-job-board-pro-custom-fields-id[]" value="<?php echo esc_html($field_counter); ?>" />
                
                <?php
                self::text( $prefix.'[name][]', esc_html__('Label', 'wp-job-board-pro'), $name_val, '', 'wp-job-board-pro-custom-field-label');
                self::text( $prefix.'[placeholder][]', esc_html__('Placeholder', 'wp-job-board-pro'), $placeholder_val);
                self::text( $prefix.'[description][]', esc_html__('Description', 'wp-job-board-pro'), $description_val);
                
                $apply_types = WP_Job_Board_Pro_Mixes::get_default_apply_types();
                $d_types = array();
                if ( $apply_types ) {
                    foreach ($apply_types as $key => $value) {
                        $d_types[] = $key;
                    }
                }
                $text_field_allow_types = isset($field_data['allow_apply_types']) ? $field_data['allow_apply_types'] : $d_types;
                
                self::select( $prefix.'[allow_apply_types]['.$field_counter.'][]', $apply_types, esc_html__('Allowed apply types', 'wp-job-board-pro'), $text_field_allow_types, '', true, true);

                self::select( $prefix.'[show_in_submit_form][]', self::yes_no_opts(), esc_html__('Show in frontend form', 'wp-job-board-pro'), $show_in_submit_form_val, '', false, false, 'show_in_submit_form', $disable_check_val );
                self::select( $prefix.'[show_in_admin_edit][]', self::yes_no_opts(), esc_html__('Show in admin form', 'wp-job-board-pro'), $show_in_admin_edit_val, '', false, false, 'show_in_admin_edit', $disable_check_val );

                if ( $p_prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX ) {
                    $show_in_submit_form_candidate_val = isset($field_data['show_in_submit_form_candidate']) ? $field_data['show_in_submit_form_candidate'] : 'yes';
                    self::select( $prefix.'[show_in_submit_form_candidate][]', self::candidate_form_opts(), esc_html__('Show in Candidate Form', 'wp-job-board-pro'), $show_in_submit_form_candidate_val, '', false, false, 'show_if_show_in_submit_form' );
                }

                if ( $p_prefix == WP_JOB_BOARD_PRO_CANDIDATE_PREFIX || $p_prefix == WP_JOB_BOARD_PRO_EMPLOYER_PREFIX ) {
                    $show_in_register_form_val = isset($field_data['show_in_register_form']) ? $field_data['show_in_register_form'] : '';
                    $disable_check_register_val = isset($field_data['disable_check_register']) ? $field_data['disable_check_register'] : false;
                    self::select( $prefix.'[show_in_register_form][]', self::yes_no_opts(), esc_html__('Show in register form', 'wp-job-board-pro'), $show_in_register_form_val, '', false, false, '', $disable_check_register_val );
                }

                self::select( $prefix.'[required][]', self::yes_no_opts(), esc_html__('Required', 'wp-job-board-pro'), $required_val, '', false, false );

                // packages
                if ( $p_prefix == WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX ) {
                    $packages = self::get_packages();
                    if ( $packages ) {
                        $show_in_package = isset($field_data['show_in_package']) ? $field_data['show_in_package'] : '';
                        $package_display = isset($field_data['package_display']) ? $field_data['package_display'] : '';
                        self::select( $prefix.'[show_in_package][]', self::yes_no_opts(), esc_html__('Enable package visibility', 'wp-job-board-pro'), $show_in_package, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), false, true, 'show_in_package');
                        self::select( $prefix.'[package_display]['.$field_counter.'][]', $packages, esc_html__('Packages', 'wp-job-board-pro'), $package_display, esc_html__('Choose Packages to show this field insubmit form.', 'wp-job-board-pro'), true, true, 'show_if_show_in_package');
                    }
                }

                do_action('wp_job_board_pro_custom_field_available_apply_type_callback', $type, $field_counter, $field_data, $p_prefix);
                ?>

            </div>
        </div>
        <?php
        $html = ob_get_clean();

        return $html;
    }

    public static function field_actions_html_callback($li_rand, $rand, $field_type, $delete = true) {
        ob_start();
        ?>
        <div class="actions">
            <a href="javascript:void(0);" class="custom-fields-edit <?php echo esc_attr($field_type); ?>-field<?php echo esc_attr($rand); ?>" ><i  class="dashicons dashicons-edit" aria-hidden="true"></i></a>
            <?php if ($delete) { ?>
                <a href="javascript:void(0);" class="custom-fields-remove" data-randid="<?php echo esc_attr($li_rand) ?>" data-fieldtype="<?php echo esc_attr($field_type); ?>"><i  class="dashicons dashicons-trash" aria-hidden="true"></i></a>
            <?php } ?>
        </div>
        <?php
        $html = ob_get_clean();

        return $html;
    }

    public static function header_html($type, $rand, $label_val) {
        ?>
        <div class="field-intro">
            <?php $field_dyn_name = $label_val != '' ? '<b>(' . $label_val . ')</b>' : '' ?>
            <a href="javascript:void(0);" class="<?php echo esc_attr($type); ?>-field<?php echo esc_attr($rand); ?>" >
                <?php echo wp_kses(sprintf(__('%s Field %s', 'wp-job-board-pro'), $type, $field_dyn_name), array('b' => array())); ?>
            </a>
        </div>
        <?php
    }

    public static function text($name, $title = '', $value = '', $desc = '', $inputclass = '', $fullwidth = false) {
        ?>
        <div class="form-group <?php echo esc_attr($fullwidth ? 'fullwidth' : ''); ?>">
            <label><?php echo $title; ?></label>
            <div class="input-field">
                <input type="text" name="<?php echo $name ;?>" value="<?php echo esc_attr($value); ?>" <?php echo trim($inputclass ? 'class="'.$inputclass.'"' : ''); ?>/>
                <?php if ( !empty($desc) ) { ?>
                    <span class="desc"><?php echo $desc; ?></span>
                <?php } ?>
            </div>
        </div>
        <?php
    }

    public static function number($name, $title = '', $value = '', $desc = '', $inputclass = '', $fullwidth = false) {
        ?>
        <div class="form-group <?php echo esc_attr($fullwidth ? 'fullwidth' : ''); ?>">
            <label><?php echo $title; ?></label>
            <div class="input-field">
                <input type="number" name="<?php echo $name ;?>" value="<?php echo esc_attr($value); ?>" <?php echo trim($inputclass ? 'class="'.$inputclass.'"' : ''); ?>/>
                <?php if ( !empty($desc) ) { ?>
                    <span class="desc"><?php echo $desc; ?></span>
                <?php } ?>
            </div>
        </div>
        <?php
    }

    public static function hidden($name, $value = '', $inputclass = '') {
        ?>
        <input type="hidden" name="<?php echo $name ;?>" value="<?php echo esc_attr($value); ?>" <?php echo trim($inputclass ? 'class="'.$inputclass.'"' : ''); ?>/>
        <?php
    }

    public static function textarea($name, $title = '', $value = '', $desc = '', $fullwidth = false) {
        ?>
        <div class="form-group <?php echo esc_attr($fullwidth ? 'fullwidth' : ''); ?>">
            <label><?php echo $title; ?></label>
            <div class="input-field">
                <textarea name="<?php echo $name ;?>"><?php echo esc_html($value); ?></textarea>
                <?php if ( !empty($desc) ) { ?>
                    <span class="desc"><?php echo $desc; ?></span>
                <?php } ?>
            </div>
        </div>
        <?php
    }

    public static function checkbox($name, $title = '', $value = '', $disabled = false, $fullwidth = true, $inputclass = '') {
        ?>
        <div class="form-group <?php echo esc_attr($fullwidth ? 'fullwidth' : ''); ?>">
            <label>
                <input <?php echo trim($inputclass ? 'class="'.$inputclass.'"' : ''); ?> type="checkbox" name="<?php echo $name ;?>" value="yes" <?php echo ($value == 'yes' ? 'checked="checked"' : ''); ?> <?php echo ($disabled ? 'readonly="readonly" onclick="return false;"' : ''); ?>/>
                <?php echo $title; ?>
            </label>
        </div>
        <?php
    }

    public static function select($name, $opts, $title = '', $values = '', $desc = '', $multiple = false, $fullwidth = false, $wrapperclass = '', $disabled = false) {
        if ( !is_array($values) ) {
            $values = array($values);
        }
        ?>
        <div class="form-group <?php echo esc_attr($fullwidth ? 'fullwidth' : ''); ?> <?php echo trim($wrapperclass ? $wrapperclass : ''); ?>">
            <label><?php echo $title; ?></label>
            <div class="input-field">
                <select name="<?php echo $name ;?>" <?php echo ($multiple ? 'multiple="multiple"' : ''); ?>>
                    <?php
                    if ( !empty($opts) && is_array($opts) ) {
                        $opts = self::sort_array_by_array( $opts, $values );
                        foreach ($opts as $key => $text) { ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php self::selected($key, $values, $disabled); ?>><?php echo $text; ?></option>
                        <?php }
                    } ?>
                </select>
                <?php if ( !empty($desc) ) { ?>
                    <span class="desc"><?php echo $desc; ?></span>
                <?php } ?>
            </div>
        </div>
        <?php
    }

    public static function selected($val, $defaults, $disabled = false) {
        if ( is_array($defaults) ) {
            if ( in_array($val, $defaults) ) {
                echo 'selected="selected"';
            } else {
                if ( $disabled ) {
                    echo 'disabled';
                }
            }
        } else {
            if ( $val == $defaults ) {
                echo 'selected="selected"';
            } else {
                if ( $disabled ) {
                    echo 'disabled';
                }
            }
        }
    }

    public static function icon_picker($value = '', $id = '', $name = '', $class = 'wp-job-board-pro-icon-pickerr') {
        $html = "
        <script>
        jQuery(document).ready(function ($) {
            setTimeout(function(){
                var e9_element = $('#icon_picker_".$id."').fontIconPicker({
                    theme: 'fip-bootstrap',
                    source: wp_job_board_pro_all_loaded_icons
                });
            }, 1000);
        });
        </script>";

        $html .= '<input type="text" id="icon_picker_' . $id . '" class="' . $class . '" name="' . $name . '" value="' . $value . '">';

        return $html;
    }

    public static function sort_array_by_array( array $array, array $orderArray ) {
        $ordered = array();

        foreach ( $orderArray as $key ) {
            if ( array_key_exists( $key, $array ) ) {
                $ordered[ $key ] = $array[ $key ];
                unset( $array[ $key ] );
            }
        }

        return $ordered + $array;
    }
}

WP_Job_Board_Pro_CustomFieldHTML::init();