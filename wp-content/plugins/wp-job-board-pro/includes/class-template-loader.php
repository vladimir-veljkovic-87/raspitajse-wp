<?php
/**
 * Template Loader
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
 
class WP_Job_Board_Pro_Template_Loader {
	
	/**
	 * Initialize template loader
	 *
	 * @access public
	 * @return void
	 */
	public static function init() {
		add_filter( 'template_include', array( __CLASS__, 'templates' ) );
	}

	/**
	 * Default templates
	 *
	 * @access public
	 * @param $template
	 * @return string
	 * @throws Exception
	 */
	public static function templates( $template ) {
		global $wp_query;
		$post_type = get_post_type();
		if ( is_tax('job_listing_category') || is_tax('job_listing_location') || is_tax('job_listing_tag') || is_tax('job_listing_type') ) {
			return self::locate( 'archive-job_listing' );
		} elseif ( is_tax('candidate_category') || is_tax('candidate_location') || is_tax('candidate_tag') ) {
			return self::locate( 'archive-candidate' );
		} elseif ( is_tax('employer_category') || is_tax('employer_location') ) {
			return self::locate( 'archive-employer' );
		} elseif ( !empty($wp_query->query_vars['post_type']) || $post_type ) {
			$custom_post_types = array( 'job_listing', 'employer', 'candidate' );
			if ( in_array( $post_type, $custom_post_types ) ) {
				if ( is_archive() ) {
					return self::locate( 'archive-' . $post_type );
				}

				if ( is_single() ) {
					return self::locate( 'single-' . $post_type );
				}
			} elseif ( in_array( $wp_query->query_vars['post_type'], $custom_post_types ) ) {
				$post_type = $wp_query->query_vars['post_type'];
				if ( is_archive() ) {
					return self::locate( 'archive-' . $post_type );
				}

				if ( is_single() ) {
					return self::locate( 'single-' . $post_type );
				}
			}
		}

		return $template;
	}

	/**
	 * Gets template path
	 *
	 * @access public
	 * @param $name
	 * @param $plugin_dir
	 * @return string
	 * @throws Exception
	 */
	public static function locate( $name, $plugin_dir = WP_JOB_BOARD_PRO_PLUGIN_DIR ) {
		$template = '';

		$theme_folder_name = apply_filters( 'wp-job-board-pro-theme-folder-name', 'wp-job-board-pro' );
		// Current theme base dir
		if ( ! empty( $name ) ) {
			$template = locate_template( array("{$name}.php") );
		}

		// Child theme
		if ( ! $template && ! empty( $name ) && file_exists( get_stylesheet_directory() . "/".$theme_folder_name."/{$name}.php" ) ) {
			$template = get_stylesheet_directory() . "/".$theme_folder_name."/{$name}.php";
		}

		// Original theme
		if ( ! $template && ! empty( $name ) && file_exists( get_template_directory() . "/".$theme_folder_name."/{$name}.php" ) ) {
			$template = get_template_directory() . "/".$theme_folder_name."/{$name}.php";
		}

		// Plugin
		if ( ! $template && ! empty( $name ) && file_exists( $plugin_dir . "templates/{$name}.php" ) ) {
			$template = $plugin_dir . "templates/{$name}.php";
		}

		// Nothing found
		if ( empty( $template ) ) {
			throw new Exception( "Template /templates/{$name}.php in plugin dir {$plugin_dir} not found." );
		}

		return $template;
	}

	
	/**
	 * Loads template content
	 *
	 * @param string $name
	 * @param array  $args
	 * @param string $plugin_dir
	 * @return string
	 * @throws Exception
	 */
	public static function get_template_part( $name, $args = array(), $plugin_dir = WP_JOB_BOARD_PRO_PLUGIN_DIR ) {
		if ( is_array( $args ) && count( $args ) > 0 ) {
			extract( $args, EXTR_SKIP );
		}

		$path = self::locate( $name, $plugin_dir );
		ob_start();
		if ( $path ) {
			include $path;
		}
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}
}

WP_Job_Board_Pro_Template_Loader::init();