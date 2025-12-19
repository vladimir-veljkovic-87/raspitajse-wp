<?php defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class AMEX_Hide_Show_Password {


	const VERSION = '1.0.0';


	public function __construct() {
		add_filter( 'cmb2_render_hide_show_password', array( $this, 'render_callback_for_hide_show_password' ), 10, 5 );
		add_filter( 'cmb2_render_hide_show_password_medium', array( $this, 'render_callback_for_hide_show_password_medium' ), 10, 5 );
	}
	
	/* CMB2 Hide/Show Password Field */
	public function render_callback_for_hide_show_password( $field, $escaped_value, $object_id, $object_type, $field_type_object ) { 
		$this->css_js( $field ); ?> 
		<span class="show_hide_password_wrapper">
			<?php echo $field_type_object->input( array( 
				'type' => 'password',
				'name' => $field->args( '_name' ),
				'id'   => 'hide_show_password',
				'data-lpignore' => true,
				'autocomplete' => 'off',
				'desc' => ''
			) ); ?>
			<a class="toggle-password" title="<?php esc_attr_e('Show', 'wp-job-board-pro'); ?>"><span class="dashicons dashicons-hidden"></span></a>
		</span>
		<?php echo $field_type_object->_desc( true );
	}
	
	/* CMB2 Hide/Show Password Field Medium */
	public function render_callback_for_hide_show_password_medium( $field, $escaped_value, $object_id, $object_type, $field_type_object ) { 
		$this->css_js( $field ); ?> 
		<span class="show_hide_password_medium show_hide_password_wrapper">
			<?php echo $field_type_object->input( array( 
				'type' => 'password',
				'name' => $field->args( '_name' ),
				'id'   => 'hide_show_password_medium',
				'data-lpignore' => true,
				'autocomplete' => 'off',
				'desc' => ''
			) ); ?>
			<a title="<?php esc_attr_e('Show', 'wp-job-board-pro'); ?>"><span class="dashicons dashicons-hidden"></span></a>
		</span>
		<?php echo $field_type_object->_desc( true );
	}

	public function css_js( $field ) {
		wp_enqueue_style( 'dashicons' );
		$asset_path = apply_filters( 'wp-job-board-pro-hide-show-password-asset-path', plugins_url( '', __FILE__ ) );
		wp_enqueue_style('wp-job-board-pro-hide-show-password-css', $asset_path . '/assets/css/style.min.css', array(), self::VERSION );
		wp_register_script('wp-job-board-pro-hide-show-password-js', $asset_path . '/assets/js/hide-show.min.js', array( 'jquery' ), self::VERSION, true );
		$translation_array = array(
			'show' => __( 'Show', 'wp-job-board-pro' ),
			'hide' => __( 'Hide', 'wp-job-board-pro' )
		);
		wp_localize_script( 'wp-job-board-pro-hide-show-password-js', 'wjbp_hide_show_password', $translation_array );
		wp_enqueue_script( 'wp-job-board-pro-hide-show-password-js' );
	} 

}

new AMEX_Hide_Show_Password();

?>
