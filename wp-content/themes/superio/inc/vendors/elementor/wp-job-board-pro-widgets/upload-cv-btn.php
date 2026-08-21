<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Superio_Elementor_Upload_CV_Btn extends Elementor\Widget_Base {

	public function get_name() {
        return 'apus_element_upload_cv_btn';
    }

	public function get_title() {
        return esc_html__( 'Apus Header Upload CV Button', 'superio' );
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
            'button_text',
            [
                'label' => esc_html__( 'Button Text', 'superio' ),
                'type' => Elementor\Controls_Manager::TEXT,
                'input_type' => 'text',
                'placeholder' => esc_html__( 'Enter your title here', 'superio' ),
                'default' => 'Upload Your CV',
            ]
        );

        $this->add_control(
            'show_add_listing',
            [
                'label' => esc_html__( 'Show Upload CV Button', 'superio' ),
                'type' => Elementor\Controls_Manager::SELECT,
                'options' => [
                    '' => esc_html__('No', 'superio'),
                    'always' => esc_html__('Always', 'superio'),
                    'show_logedin' => esc_html__('Candidate Loged in', 'superio'),
                    'none-register-candidate' => esc_html__('None Register and Candidate', 'superio'),
                ],
                'default' => 'always',
            ]
        );

        $this->add_control(
            'style',
            [
                'label' => esc_html__( 'Style', 'superio' ),
                'type' => Elementor\Controls_Manager::SELECT,
                'options' => array(
                    '' => esc_html__('Default', 'superio'),
                    ' st_1' => esc_html__('Style 1', 'superio'),
                ),
                'default' => ''
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

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style',
            [
                'label' => esc_html__( 'Button', 'superio' ),
                'tab' => Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs( 'tabs_button_style' );

        $this->start_controls_tab(
            'tab_button_normal',
            [
                'label' => esc_html__( 'Normal', 'superio' ),
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => esc_html__( 'Text Color', 'superio' ),
                'type' => Elementor\Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .btn-readmore' => 'fill: {{VALUE}}; color: {{VALUE}};',
                ],
            ]
        );


        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_button_hover',
            [
                'label' => esc_html__( 'Hover', 'superio' ),
            ]
        );

        $this->add_control(
            'hover_color',
            [
                'label' => esc_html__( 'Text Color', 'superio' ),
                'type' => Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .btn-readmore:hover, {{WRAPPER}} .btn-readmore:focus' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .btn-readmore:after' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

	protected function render() {
        $settings = $this->get_settings();

        extract( $settings );

        if ( Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            ?>
            <div class="widget-submit-btn <?php echo esc_attr($el_class.$style); ?>">
                <a class="btn-readmore" href="javascript:void(0);">
                    <span class="flaticon-file"></span> <?php echo trim($button_text); ?>
                </a>
            </div>
            <?php
        } elseif ( $show_add_listing == 'always' || ($show_add_listing == 'show_logedin' && is_user_logged_in() && WP_Job_Board_Pro_User::is_candidate(get_current_user_id()) ) || ($show_add_listing == 'none-register-candidate' && (!is_user_logged_in() || WP_Job_Board_Pro_User::is_candidate(get_current_user_id())) )  ) {
            $page_id = wp_job_board_pro_get_option('my_resume_page_id');
            $page_id = WP_Job_Board_Pro_Mixes::get_lang_post_id($page_id);
            $classes = '';
            if ( ($show_add_listing == 'always' || $show_add_listing == 'none-register-candidate') && !is_user_logged_in() ) {
                $classes = 'user-login-form';
            }
            ?>
            <div class="widget-submit-btn <?php echo esc_attr($el_class.$style); ?>">
                <a class="btn-readmore <?php echo esc_attr($classes); ?>" href="<?php echo esc_url( get_permalink( $page_id ) ); ?>">
                    <span class="flaticon-file"></span> <?php echo trim($button_text); ?>
                </a>
            </div>
            <?php
        }
    }
}

if ( version_compare(ELEMENTOR_VERSION, '3.5.0', '<') ) {
    Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Superio_Elementor_Upload_CV_Btn );
} else {
    Elementor\Plugin::instance()->widgets_manager->register( new Superio_Elementor_Upload_CV_Btn );
}