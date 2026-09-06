<?php
/**
 * Types
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class WP_Job_Board_Pro_Taxonomy_Job_Type{

	/**
	 *
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'definition' ), 1 );

		add_filter( "manage_edit-job_listing_type_columns", array( __CLASS__, 'tax_columns' ) );
		add_filter( "manage_job_listing_type_custom_column", array( __CLASS__, 'tax_column' ), 10, 3 );
		add_action( "job_listing_type_add_form_fields", array( __CLASS__, 'add_fields_form' ) );
		add_action( "job_listing_type_edit_form_fields", array( __CLASS__, 'edit_fields_form' ), 10, 2 );

		add_action( 'create_term', array( __CLASS__, 'save' )  );
		add_action( 'edit_term', array( __CLASS__, 'save' ) );
	}

	/**
	 *
	 */
	public static function definition() {
		$singular = __( 'Type', 'wp-job-board-pro' );
		$plural   = __( 'Types', 'wp-job-board-pro' );

		$labels = array(
			'name'              => sprintf(__( 'Job %s', 'wp-job-board-pro' ), $plural),
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

		$rewrite_slug = get_option('wp_job_board_pro_job_type_slug');
		if ( empty($rewrite_slug) ) {
			$rewrite_slug = _x( 'job-type', 'Job type slug - resave permalinks after changing this', 'wp-job-board-pro' );
		}
		$rewrite = array(
			'slug'         => $rewrite_slug,
			'with_front'   => false,
			'hierarchical' => false,
		);
		register_taxonomy( 'job_listing_type', 'job_listing', array(
			'labels'            => apply_filters( 'wp_job_board_pro_taxomony_job_type_labels', $labels ),
			'hierarchical'      => true,
			'rewrite'           => $rewrite,
			'public'            => true,
			'show_ui'           => true,
			'show_in_rest'		=> true
		) );
	}

	public static function get_employment_types() {
		$employment_types = array(
			'FULL_TIME' => __( 'Full Time', 'wp-job-board-pro' ),
			'PART_TIME' => __( 'Part Time', 'wp-job-board-pro' ),
			'CONTRACTOR' => __( 'Contractor', 'wp-job-board-pro' ),
			'TEMPORARY' => __( 'Temporary', 'wp-job-board-pro' ),
			'INTERN' => __( 'Intern', 'wp-job-board-pro' ),
			'VOLUNTEER' => __( 'Volunteer', 'wp-job-board-pro' ),
			'PER_DIEM' => __( 'Per Diem', 'wp-job-board-pro' ),
			'OTHER' => __( 'Other', 'wp-job-board-pro' ),
		);
		return apply_filters('wp-job-board-pro-get-employment-types', $employment_types);
	}

	public static function add_fields_form($taxonomy) {
		?>
		<div class="form-field">
			<label><?php esc_html_e( 'Background Color', 'wp-job-board-pro' ); ?></label>
			<?php self::color_field('bg_color'); ?>
		</div>
		<div class="form-field">
			<label><?php esc_html_e( 'Text Color', 'wp-job-board-pro' ); ?></label>
			<?php self::color_field('text_color'); ?>
		</div>

		<div class="form-field">
			<label><?php esc_html_e( 'Employment Type', 'wp-job-board-pro' ); ?></label>
			<?php self::employment_type_field(); ?>
		</div>
		<?php
	}

	public static function edit_fields_form( $term, $taxonomy ) {
			$text_color_value = get_term_meta( $term->term_id, 'text_color', true );
			$bg_color_value = get_term_meta( $term->term_id, 'bg_color', true );
			$employment_type_value = get_term_meta( $term->term_id, '_employment_type', true );

		?>
			<tr class="form-field">
				<th scope="row" valign="top"><label><?php esc_html_e( 'Background Color', 'wp-job-board-pro' ); ?></label></th>
				<td>
					<?php self::color_field('bg_color', $bg_color_value); ?>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label><?php esc_html_e( 'Text Color', 'wp-job-board-pro' ); ?></label></th>
				<td>
					<?php self::color_field('text_color', $text_color_value); ?>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label><?php esc_html_e( 'Employment Type', 'wp-job-board-pro' ); ?></label></th>
				<td>
					<?php self::employment_type_field($employment_type_value); ?>
				</td>
			</tr>
		<?php
	}

	public static function color_field( $name, $val = '' ) {
		?>
		<input class="tax_color_input" name="<?php echo esc_attr($name); ?>" type="text" value="<?php echo esc_attr($val); ?>">
		<?php
	}

	public static function employment_type_field( $val = '' ) {
		$employment_types = self::get_employment_types();
		?>
		<select class="employment_type" name="wp_job_board_pro_employment_type">
			<option value=""><?php esc_attr_e('Choose a employment type', 'wp-job-board-pro'); ?></option>
			<?php foreach ($employment_types as $key => $title) { ?>
				<option value="<?php echo esc_attr($key); ?>" <?php selected($val, $key); ?>><?php echo esc_attr($title); ?></option>
			<?php } ?>
		</select>
		<?php
	}

	public static function save( $term_id ) {
	    if ( isset( $_POST['text_color'] ) ) {
	    	update_term_meta( $term_id, 'text_color', $_POST['text_color'] );
	    }
	    if ( isset( $_POST['bg_color'] ) ) {
	    	update_term_meta( $term_id, 'bg_color', $_POST['bg_color'] );
	    }
	    if ( isset( $_POST['wp_job_board_pro_employment_type'] ) ) {
	    	update_term_meta( $term_id, '_employment_type', $_POST['wp_job_board_pro_employment_type'] );
	    }
	}

	public static function tax_columns( $columns ) {
		$new_columns = array();
		foreach ($columns as $key => $value) {
			if ( $key == 'name' ) {
				$new_columns['color'] = esc_html__( 'Color', 'wp-job-board-pro' );
			}
			$new_columns[$key] = $value;
			if ( $key == 'slug' ) {
				$new_columns['employment_type'] = esc_html__( 'Employment Type', 'wp-job-board-pro' );
			}
		}
		return $new_columns;
	}

	public static function tax_column( $columns, $column, $id ) {
		if ( $column == 'color' ) {
			$term = get_term($id);
			$bg_color = get_term_meta( $id, 'bg_color', true );
			$text_color = get_term_meta( $id, 'text_color', true );
			$styles = '';
			if ( !empty($bg_color) ) {
				$styles .= 'background-color: '.$bg_color.';';
			}
			if ( !empty($text_color) ) {
				$styles .= 'color: '.$text_color.';';
			}
			?>
			<div href="javascript:void(0);" style="padding: 10px 30px; border-radius: 5px; <?php echo esc_attr($styles); ?>">
				<?php echo $term->name; ?>
			</div>
			<?php
		}
		if ( $column == 'employment_type' ) {
			$employment_type = get_term_meta( $id, '_employment_type', true );
			$employment_types = self::get_employment_types();
			if ( $employment_type && !empty($employment_types[$employment_type])) {
				echo $employment_types[$employment_type];
			}
		}
		return $columns;
	}
}

WP_Job_Board_Pro_Taxonomy_Job_Type::init();