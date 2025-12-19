<?php
/**
 * Widget: Job Alert Form
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_Widget_Job_Alert_Form extends WP_Widget {
	/**
	 * Initialize widget
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {
		parent::__construct(
			'job_alert_form_widget',
			__( 'Job Alert Form', 'wp-job-board-pro' ),
			array(
				'description' => __( 'Job alert form for add email alert.', 'wp-job-board-pro' ),
			)
		);
	}

	/**
	 * Frontend
	 *
	 * @access public
	 * @param array $args
	 * @param array $instance
	 * @return void
	 */
	function widget( $args, $instance ) {
		include WP_Job_Board_Pro_Template_Loader::locate( 'widgets/job-alert-form' );
	}

	/**
	 * Update
	 *
	 * @access public
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title']       = sanitize_text_field( $new_instance['title'] );
		$instance['subtitle']    = sanitize_text_field( $new_instance['subtitle'] );
		$instance['button_text'] = sanitize_text_field( $new_instance['button_text'] );

		return $instance;
	}


	/**
	 * Backend
	 *
	 * @access public
	 * @param array $instance
	 * @return void
	 */
	function form( $instance ) {

		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$button_text = ! empty( $instance['button_text'] ) ? $instance['button_text'] : 'Save Job Alert';
		?>

		<!-- TITLE -->
		<p>
		    <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
		        <?php echo __( 'Title', 'wp-job-board-pro' ); ?>
		    </label>

		    <input  class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<!-- SUBTITLE -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'subtitle' ) ); ?>">
				<?php echo __( 'Subtitle', 'wp-job-board-pro' ); ?>
			</label>

			<input  class="widefat"
					id="<?php echo esc_attr( $this->get_field_id( 'subtitle' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'subtitle' ) ); ?>"
					type="text"
					value="<?php echo esc_attr( ! empty( $instance['subtitle'] ) ? $instance['subtitle'] : '' ); ?>">
		</p>


		<!-- BUTTON TEXT -->
		<p>
		    <label for="<?php echo esc_attr( $this->get_field_id( 'button_text' ) ); ?>">
		        <?php echo __( 'Button text', 'wp-job-board-pro' ); ?>
		    </label>

		    <input  class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'button_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'button_text' ) ); ?>" type="text" value="<?php echo esc_attr( $button_text ); ?>">
		</p>

		
		<?php
	}
}
register_widget('WP_Job_Board_Pro_Widget_Job_Alert_Form');