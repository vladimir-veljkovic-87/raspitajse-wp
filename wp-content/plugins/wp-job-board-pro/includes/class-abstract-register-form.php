<?php
/**
 * Abstract Form
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_Abstract_Register_Form {
	public $form_name = '';
	public $post_type = '';
	public $prefix = '';
	public $errors = array();
	public $success_msg = array();

	public function __construct() {
		add_filter( 'cmb2_meta_boxes', array( $this, 'fields_front' ) );
	}
	
	public function get_form_action() {
		return '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

	public function get_form_name() {
		return $this->form_name;
	}

	public function add_error( $error ) {
		$this->errors[] = $error;
	}
	
	public function fields_front($metaboxes) {

		$fields = apply_filters( 'wp-job-board-pro-register-'.$this->post_type.'-fields', array(
			array(
                'id'                => 'post_type',
                'type'              => 'hidden',
                'default'           => $this->post_type,
                'priority'          => 0,
            ),
            array(
                'name'              => __( 'Email', 'wp-job-board-pro' ),
                'id'                => $this->prefix . 'email',
                'type'              => 'text',
                'priority'          => 0,
                'label_cb'			=> array( 'WP_Job_Board_Pro_Mixes', 'required_add_label' ),
                'attributes' => array(
                	'placeholder' => esc_html__('Email', 'wp-job-board-pro'),
                	'required' => true
                )
            ),
            array(
                'name'              => __( 'Lozinka', 'wp-job-board-pro' ),
                'id'                => $this->prefix . 'password',
                'type'              => 'hide_show_password',
                'priority'          => 0,
                'label_cb'			=> array( 'WP_Job_Board_Pro_Mixes', 'required_add_label' ),
                'attributes' => array(
                	'placeholder' => esc_html__('Lozinka', 'wp-job-board-pro'),
                	'required' => true
                )
            ),
            array(
                'name'              => __( 'Potvrdite Lozinku', 'wp-job-board-pro' ),
                'id'                => $this->prefix . 'confirmpassword',
                'type'              => 'hide_show_password',
                'priority'          => 0,
                'label_cb'			=> array( 'WP_Job_Board_Pro_Mixes', 'required_add_label' ),
                'attributes' => array(
                	'placeholder' => esc_html__('Potvrdite Lozinku', 'wp-job-board-pro'),
                	'required' => true
                )
            ),
		), $this->post_type, $this->prefix );


		$metaboxes[ $this->prefix . 'register_fields' ] = array(
			'id'                        => $this->prefix . 'register_fields',
			'title'                     => __( 'General Options', 'wp-job-board-pro' ),
			'object_types'              => array( $this->post_type ),
			'context'                   => 'normal',
			'priority'                  => 'high',
			'show_names'                => true,
			'fields'                    => $fields
		);

		return $metaboxes;
	}

	public function form_output() {
		$metaboxes = apply_filters( 'cmb2_meta_boxes', array() );
		if ( ! isset( $metaboxes[ $this->prefix . 'register_fields' ] ) ) {
			return __( 'A metabox with the specified \'metabox_id\' doesn\'t exist.', 'wp-job-board-pro' );
		}
		$metaboxes_form = $metaboxes[ $this->prefix . 'register_fields' ];

		wp_enqueue_script('wpjbp-select2');
		wp_enqueue_style('wpjbp-select2');

		return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/register-'.$this->post_type, array(
			'post_id' => WP_Job_Board_Pro_Mixes::random_key(),
			'metaboxes_form' => $metaboxes_form,
			'form_obj'       => $this,
			'submit_button_text' => apply_filters( 'wp_job_board_pro_register_'.$this->post_type.'_form_submit_button_text', __( 'Registruj se sada', 'wp-job-board-pro' ) ),
		) );
	}
}
