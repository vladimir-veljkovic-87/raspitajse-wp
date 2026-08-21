<?php

//namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Superio_Elementor_User_Notification extends Elementor\Widget_Base {

	public function get_name() {
        return 'apus_element_user_notification';
    }

	public function get_title() {
        return esc_html__( 'Apus Notification', 'superio' );
    }
    
	public function get_categories() {
        return [ 'superio-header-elements' ];
    }

	protected function register_controls() {

        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__( 'Content', 'superio' ),
                'tab' => Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

   		$this->add_control(
            'el_class',
            [
                'label'         => esc_html__( 'Extra class name', 'superio' ),
                'type'          => Elementor\Controls_Manager::TEXT,
                'placeholder'   => esc_html__( 'If you wish to style particular content element differently, please add a class name to this field and refer to it in your custom CSS file.', 'superio' ),
            ]
        );

        $this->add_control(
            'dropdown',
            [
                'label' => esc_html__( 'Dropdown', 'superio' ),
                'type' => Elementor\Controls_Manager::SELECT,
                'options' => array(
                    'right' => esc_html__('Right', 'superio'),
                    'left' => esc_html__('Left', 'superio'),
                ),
                'default' => 'right'
            ]
        );

        

        $this->end_controls_section();

        $this->start_controls_section(
            'section_title_style',
            [
                'label' => esc_html__( 'Title', 'superio' ),
                'tab' => Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'icon_color',
            [
                'label' => esc_html__( 'Color Icon', 'superio' ),
                'type' => Elementor\Controls_Manager::COLOR,
                'scheme' => [
                    'type' => Elementor\Core\Schemes\Color::get_type(),
                    'value' => Elementor\Core\Schemes\Color::COLOR_1,
                ],
                'selectors' => [
                    // Stronger selector to avoid section style from overwriting
                    '{{WRAPPER}} .message-notification i' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'icon_hover_color',
            [
                'label' => esc_html__( 'Color Hover Icon', 'superio' ),
                'type' => Elementor\Controls_Manager::COLOR,
                'scheme' => [
                    'type' => Elementor\Core\Schemes\Color::get_type(),
                    'value' => Elementor\Core\Schemes\Color::COLOR_1,
                ],
                'selectors' => [
                    // Stronger selector to avoid section style from overwriting
                    '{{WRAPPER}} .message-notification:hover i' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .message-notification:focus i' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

	protected function render() {
		$settings = $this->get_settings();

		extract( $settings );

		// Initialize variables to avoid warnings
		$notifications = [];
		$user_post_id = '';
		$post_type = '';

		// Load notifications ONLY if user is logged in
		if ( is_user_logged_in() ) {
			$user_id = WP_Job_Board_Pro_User::get_user_id();

			if ( WP_Job_Board_Pro_User::is_employer($user_id) ) {
				$user_post_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);
				$post_type = 'employer';

			} elseif ( method_exists('WP_Job_Board_Pro_User', 'is_employee') && WP_Job_Board_Pro_User::is_employee($user_id) ) {
				$user_post_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);
				$post_type = 'employer';

			} elseif ( WP_Job_Board_Pro_User::is_candidate($user_id) ) {
				$user_post_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);
				$post_type = 'candidate';
			}

			if ( !empty($user_post_id) && !empty($post_type) ) {
				$notifications = WP_Job_Board_Pro_User_Notification::get_not_seen_notifications($user_post_id, $post_type);
			}
		}

		// BEGIN: Your exact HTML block (unchanged)
		?>

		<?php if ( is_user_logged_in() ) : ?>
			<div class="message-top <?php echo esc_attr($el_class); ?>">
				<a class="message-notification" href="javascript:void(0);">
					<i class="ti-bell"></i>
					<?php if ( !empty($notifications) ) { ?>
						<span class="unread-count bg-warning"><?php echo count($notifications); ?></span>
					<?php } ?>
				</a>

				<?php if ( !empty($notifications) ) { ?>
					<div class="notifications-wrapper <?php echo trim($dropdown); ?>">
						<ul>
							<?php foreach ($notifications as $key => $notify) {
								$type = !empty($notify['type']) ? $notify['type'] : '';
								if ( $type ) {
							?>
									<li>
										<i class="time">
											<?php
												$time = $notify['time'];
												echo human_time_diff( $time, current_time( 'timestamp' ) ).' '.esc_html__( 'ago', 'superio' );
											?>
										</i>
										<p>
											<?php echo trim(WP_Job_Board_Pro_User_Notification::display_notify($notify)); ?>
											<a href="javascript:void(0);" class="remove-notify-btn" data-id="<?php echo esc_attr($notify['unique_id']); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce( 'wp-job-board-pro-remove-notify-nonce' )); ?>"><i class="ti-close"></i></a>
										</p>
									</li>
								<?php } ?>
							<?php } ?>
						</ul>
					</div>
				<?php } ?>
			</div>
		<?php endif; ?>

		<?php
	}

}

if ( version_compare(ELEMENTOR_VERSION, '3.5.0', '<') ) {
    Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Superio_Elementor_User_Notification );
} else {
    Elementor\Plugin::instance()->widgets_manager->register( new Superio_Elementor_User_Notification );
}