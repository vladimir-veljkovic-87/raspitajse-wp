<?php
/**
 * CMB2 File
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_CMB2_Field_Datepicker {

	public static function init() {
		add_filter( 'cmb2_render_wjbp_datepicker', array( __CLASS__, 'render_map' ), 10, 5 );
		add_filter( 'cmb2_sanitize_wjbp_datepicker', array( __CLASS__, 'sanitize_map' ), 10, 4 );
	}

	/**
	 * Render field
	 */
	public static function render_map( $field, $field_escaped_value, $field_object_id, $field_object_type, $field_type_object ) {
		
		$value = '';
		if ( !empty($field_escaped_value) ) {
			$value = strtotime($field_escaped_value);
			$value = date(get_option('date_format'), $value);
		}
		$datepicker = $field->args( 'attributes' )['data-datepicker'];

		echo $field_type_object->input( array(
			'type'       => 'text',
			'name'       => $field->args( '_name' ).'_display',
			'id'       => $field->args( '_name' ).'_display',
			'value'      => $value,
			'class'      => 'wjbp-datepicker cmb2-datepicker',
			// 'desc'       => '',
			// 'placeholder' => date(get_option('date_format')),
			'js_dependencies' => array( 'jquery-ui-core', 'jquery-ui-datepicker' ),
			'data-datepicker' => $field->args( 'attributes' )['data-datepicker']
		) );

		echo $field_type_object->input( array(
			'type'       => 'hidden',
			'name'       => $field->args( '_name' ),
			'value'      => isset( $field_escaped_value ) ? $field_escaped_value : '',
			'desc'       => '',
		) );
	}

	public static function sanitize_map( $override_value, $value, $object_id, $field_args ) {
		return $value;
	}

}

WP_Job_Board_Pro_CMB2_Field_Datepicker::init();