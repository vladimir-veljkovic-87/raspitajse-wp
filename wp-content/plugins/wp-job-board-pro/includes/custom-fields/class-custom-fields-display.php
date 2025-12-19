<?php
/**
 * Custom Fields Display
 *
 * @package    wp-job-board-pro
 * @author     Habq
 * @license    GNU General Public License, version 3
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WP_Job_Board_Pro_Custom_Fields_Display {
	
	public static function init() {
		add_action('init', array(__CLASS__, 'hooks'));
	}

	public static function hooks() {
        $prefixs = array(WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX, WP_JOB_BOARD_PRO_EMPLOYER_PREFIX, WP_JOB_BOARD_PRO_CANDIDATE_PREFIX);
        foreach ($prefixs as $prefix) {
            $hooks = WP_Job_Board_Pro_Fields_Manager::get_display_hooks($prefix);
        
            foreach ($hooks as $hook => $title) {
                if ( !empty($hook) ) {
                    add_action( $hook, function($post) use ( $hook, $prefix ) {
                        self::display_hook($post, $hook, $prefix);
                    }, 100 );
                }
            }
        }
	}

	public static function display_hook($post, $current_hook, $prefix) {
		$custom_fields = WP_Job_Board_Pro_Fields_Manager::get_custom_fields_data($prefix);

		if (is_array($custom_fields) && sizeof($custom_fields) > 0) {
			foreach ($custom_fields as $key => $custom_field) {
				$hook_display = !empty($custom_field['hook_display']) ? $custom_field['hook_display'] : '';
				if ( !empty($hook_display) && $hook_display == $current_hook ) {
					echo self::display_field_data($custom_field, $post, $current_hook, $prefix);
				}
			}
		}
	}

	public static function display_field_data($custom_field, $post, $current_hook, $prefix) {
		$field_type = !empty($custom_field['type']) ? $custom_field['type'] : '';
		$field_id = !empty($custom_field['id']) ? $custom_field['id'] : '';
        $field_name = !empty($custom_field['name']) ? $custom_field['name'] : '';
		
		$value = get_post_meta( $post->ID, $field_id, true );
        if ( empty($value) ) {
            return;
        }
		$output_value = '';

		switch ( $field_type ) {
            case 'text':
            case 'number':
            case 'email':
            case 'select':
            case 'radio':
                $output_value = $value;
                break;
            case 'textarea':
            case 'wp-editor':
            case 'wysiwyg':
                $output_value = wpautop($value);
                break;
            case 'url':
                $output_value = '<a href="'.esc_url($value).'">'.$value.'</a>';
                break;
            case 'date':
            	$output_value = strtotime($value);
            	$output_value = date_i18n(get_option('date_format'), $output_value);
                break;
            case 'checkbox':
            	$output_value = $value ? esc_html__('Yes', 'wp-job-board-pro') : esc_html__('No', 'wp-job-board-pro');
            	break;
            case 'multiselect':
                if ( is_array($value) ) {
                	$output_value = implode(', ', $value);
                }
                break;
            case 'file':
                $return = '';
                if ( is_array($value) ) {
                	foreach ($value as $file) {
                		if ( self::check_image_mime_type($file) ) {
                			$return .= '<img src="'.esc_url($file).'">';
                		} else {
                			$return .= '<a href="'.esc_url($file).'">'.esc_html__('Download file', 'wp-job-board-pro').'</a>';
                		}
                	}
                } elseif ( !empty($value) ) {
                	if ( self::check_image_mime_type($value) ) {
            			$return .= '<img src="'.esc_url($value).'">';
            		} else {
            			$return .= '<a href="'.esc_url($value).'">'.esc_html__('Download file', 'wp-job-board-pro').'</a>';
            		}
                }
                $output_value = $return;
            break;
        }
        ob_start();
        ?>
        <div class="custom-field-data <?php echo esc_attr($field_id); ?>">
        	<?php if ( $field_name ) { ?>
	        	<h5><?php echo trim($field_name); ?></h5>
	        <?php } ?>
	        <div class="content"><?php echo (is_array($output_value) ? implode(', ', $output_value) : $output_value); ?></div>
        </div>
        <?php
        $html = ob_get_clean();
        return apply_filters( 'wp_job_board_pro_display_field_data', $html, $custom_field, $post, $field_name, $output_value, $current_hook );
	}

	public static function check_image_mime_type($image_path) {
		$filetype = strtolower(substr(strstr($image_path, '.'), 1));
	    $mimes  = array( "gif", "jpg", "png", "ico");

	    if ( in_array($filetype, $mimes) ) {
	        return true;
	    } else {
	        return false;
	    }
	}
}

WP_Job_Board_Pro_Custom_Fields_Display::init();