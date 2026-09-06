<?php
/**
 * Employer Categories
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class WP_Job_Board_Pro_Taxonomy_Employer_Category{

	/**
	 *
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'definition' ), 1 );
	}

	/**
	 *
	 */
	public static function definition($args) {

		$singular = __( 'Category', 'wp-job-board-pro' );
		$plural   = __( 'Categories', 'wp-job-board-pro' );

		$labels = array(
			'name'              => sprintf(__( 'Employer %s', 'wp-job-board-pro' ), $plural),
			'singular_name'     => $singular,
			'search_items'      => sprintf(__( 'Search %s', 'wp-job-board-pro' ), $plural),
			'all_items'         => sprintf(__( 'All %s', 'wp-job-board-pro' ), $plural),
			'parent_item'       => sprintf(__( 'Parent %s', 'wp-job-board-pro' ), $singular),
			'parent_item_colon' => sprintf(__( 'Parent %s:', 'wp-job-board-pro' ), $singular),
			'edit_item'         => __( 'Edit', 'wp-job-board-pro' ),
			'update_item'       => __( 'Update', 'wp-job-board-pro' ),
			'add_new_item'      => __( 'Add New', 'wp-job-board-pro' ),
			'new_item_name'     => sprintf(__( 'New %s', 'wp-job-board-pro' ), $singular),
			'menu_name'         => $plural,
		);
		
		$rewrite_slug = get_option('wp_job_board_pro_employer_category_slug');
		if ( empty($rewrite_slug) ) {
			$rewrite_slug = _x( 'employer-category', 'Employer category slug - resave permalinks after changing this', 'wp-job-board-pro' );
		}
		$rewrite = array(
			'slug'         => $rewrite_slug,
			'with_front'   => false,
			'hierarchical' => false,
		);
		register_taxonomy( 'employer_category', 'employer', array(
			'labels'            => apply_filters( 'wp_job_board_pro_taxomony_employer_category_labels', $labels ),
			'hierarchical'      => true,
			'rewrite'           => $rewrite,
			'public'            => true,
			'show_ui'           => true,
			'show_in_rest'		=> true
		) );
	}

}

WP_Job_Board_Pro_Taxonomy_Employer_Category::init();