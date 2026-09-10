<?php

//namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Superio_Elementor_User_Info2 extends Elementor\Widget_Base {

	public function get_name() {
        return 'apus_element_user_info2';
    }

	public function get_title() {
        return esc_html__( 'Apus Header User Info 2', 'superio' );
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
            'layout_type',
            [
                'label' => esc_html__( 'Layout Type', 'superio' ),
                'type' => Elementor\Controls_Manager::SELECT,
                'options' => array(
                    'popup' => esc_html__('Popup', 'superio'),
                    'page' => esc_html__('Page', 'superio'),
                ),
                'default' => 'popup'
            ]
        );

        $this->add_control(
            'login_register_text',
            [
                'label'         => esc_html__( 'Login Register Text', 'superio' ),
                'type'          => Elementor\Controls_Manager::TEXT,
                'default'       => 'Login / Register',
                'placeholder'   => esc_html__( 'Login / Register', 'superio' ),
                'condition' => [
                    'layout_type' => 'page',
                ],
            ]
        );

        $this->add_control(
            'login_text',
            [
                'label'         => esc_html__( 'Login Text', 'superio' ),
                'type'          => Elementor\Controls_Manager::TEXT,
                'default'       => 'Login',
                'placeholder'   => esc_html__( 'Login', 'superio' ),
                'condition' => [
                    'layout_type' => 'popup',
                ],
            ]
        );

        $this->add_control(
            'register_text',
            [
                'label'         => esc_html__( 'Register Text', 'superio' ),
                'type'          => Elementor\Controls_Manager::TEXT,
                'default'       => 'Register',
                'placeholder'   => esc_html__( 'Register', 'superio' ),
                'condition' => [
                    'layout_type' => 'popup',
                ],
            ]
        );

        $this->add_control(
            'login_title',
            [
                'label'         => esc_html__( 'Login Popup Title', 'superio' ),
                'type'          => Elementor\Controls_Manager::TEXT,
                'default'       => 'Login to superio',
                'placeholder'   => esc_html__( 'Login to superio', 'superio' ),
                'condition' => [
                    'layout_type' => 'popup',
                ],
            ]
        );

        $this->add_control(
            'register_title',
            [
                'label'         => esc_html__( 'Register Popup Title', 'superio' ),
                'type'          => Elementor\Controls_Manager::TEXT,
                'default'       => 'Create a free superio account',
                'placeholder'   => esc_html__( 'Create a free superio account', 'superio' ),
                'condition' => [
                    'layout_type' => 'popup',
                ],
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

        $this->add_responsive_control(
            'align',
            [
                'label' => esc_html__( 'Alignment', 'superio' ),
                'type' => Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__( 'Left', 'superio' ),
                        'icon' => 'fa fa-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'superio' ),
                        'icon' => 'fa fa-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__( 'Right', 'superio' ),
                        'icon' => 'fa fa-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_title_style',
            [
                'label' => esc_html__( 'Name Account', 'superio' ),
                'tab' => Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'icon_color',
            [
                'label' => esc_html__( 'Color', 'superio' ),
                'type' => Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    // Stronger selector to avoid section style from overwriting
                    '{{WRAPPER}} .drop-dow' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->end_controls_section();


        $this->start_controls_section(
            'section_button_style',
            [
                'label' => esc_html__( 'Button', 'superio' ),
                'tab' => Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'button_padding',
            [
                'label' => esc_html__( 'Padding Button', 'superio' ),
                'type' => Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_control(
            'border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'superio' ),
                'type' =>Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_control(
            'button_color',
            [
                'label' => esc_html__( 'Color Button', 'superio' ),
                'type' => Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    // Stronger selector to avoid section style from overwriting
                    '{{WRAPPER}} .btn-login' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .btn-login a' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'button_bg_color',
            [
                'label' => esc_html__( 'Background Color Button', 'superio' ),
                'type' => Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    // Stronger selector to avoid section style from overwriting
                    '{{WRAPPER}} .btn-login' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'button_border_color',
            [
                'label' => esc_html__( 'Border Color Button', 'superio' ),
                'type' => Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    // Stronger selector to avoid section style from overwriting
                    '{{WRAPPER}} .btn-login' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_color',
            [
                'label' => esc_html__( 'Hover Color Button', 'superio' ),
                'type' => Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    // Stronger selector to avoid section style from overwriting
                    '{{WRAPPER}} .btn-login:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .btn-login:focus' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .btn-login:hover a' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .btn-login:focus a' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'button_hover_bg_color',
            [
                'label' => esc_html__( 'Hover Background Color Button', 'superio' ),
                'type' => Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    // Stronger selector to avoid section style from overwriting
                    '{{WRAPPER}} .btn-login:hover' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .btn-login:focus' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'button_hover_border_color',
            [
                'label' => esc_html__( 'Hover Border Color Button', 'superio' ),
                'type' => Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    // Stronger selector to avoid section style from overwriting
                    '{{WRAPPER}} .btn-login:hover' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .btn-login:focus' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

	protected function render() {
        $settings = $this->get_settings();

        extract( $settings );

        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
            $userdata = get_userdata($user_id);
            $user_name = $userdata->display_name;
            if ( WP_Job_Board_Pro_User::is_employer($user_id) || WP_Job_Board_Pro_User::is_candidate($user_id) || ( method_exists('WP_Job_Board_Pro_User', 'is_employee') && WP_Job_Board_Pro_User::is_employee($user_id) ) ) {
                if ( WP_Job_Board_Pro_User::is_employer($user_id) ) {
                    $menu_nav = 'employer-menu';
                    $employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);
                    $user_name = get_post_field('post_title', $employer_id);
                    $avatar = get_the_post_thumbnail( $employer_id, 'thumbnail' );
                } elseif ( method_exists('WP_Job_Board_Pro_User', 'is_employee') && WP_Job_Board_Pro_User::is_employee($user_id) ) {
                    $user_id = WP_Job_Board_Pro_User::get_user_id();
                    
                    $menu_nav = 'employee-menu';
                    $employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);
                    $user_name = get_post_field('post_title', $employer_id);
                    $avatar = get_the_post_thumbnail( $employer_id, 'thumbnail' );
                } else {
                    $menu_nav = 'candidate-menu';
                    $candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);
                    $user_name = get_post_field('post_title', $candidate_id);
                    $avatar = get_the_post_thumbnail( $candidate_id, 'thumbnail' );
                }
            }
            ?>
            <div class="top-wrapper-menu <?php echo esc_attr($el_class); ?> pull-right">
                <a class="drop-dow" href="javascript:void(0);">
                    <div class="infor-account flex-middle">
                        <div class="avatar-wrapper">
                            <?php if ( !empty($avatar)) {
                                echo trim($avatar);
                            } else {
                                echo get_avatar($user_id, 54);
                            } ?>
                        </div>
                        <div class="name-acount"><?php echo esc_html($user_name); ?> 
                            <?php if ( !empty($menu_nav) && has_nav_menu( $menu_nav ) ) { ?>
                                <i class="ti-angle-down" aria-hidden="true"></i>
                            <?php } ?>
                        </div>
                    </div>
                </a>
                <?php
                    if ( !empty($menu_nav) && has_nav_menu( $menu_nav ) ) {
                        $args = array(
                            'theme_location' => $menu_nav,
                            'container_class' => 'inner-top-menu',
                            'menu_class' => 'nav navbar-nav topmenu-menu',
                            'fallback_cb' => '',
                            'menu_id' => '',
                            'walker' => new Superio_Nav_Menu()
                        );
                        wp_nav_menu($args);
                    }
                ?>
            </div>
        <?php } else {
            $show_candidate = superio_get_config('register_form_enable_candidate', true);
            $show_employer = superio_get_config('register_form_enable_employer', true);
            if ( $show_candidate || $show_employer ) {

                $login_register_page_id = wp_job_board_pro_get_option('login_register_page_id');
        ?>
                <div class="top-wrapper-menu <?php echo esc_attr($el_class); ?>">

                    <?php if ( $layout_type == 'page' ) { ?>
                        <a class="btn btn-login login" href="<?php echo esc_url( get_permalink( $login_register_page_id ) ); ?>" title="<?php esc_attr_e('Sign in','superio'); ?>"><?php echo trim($login_register_text); ?>
                        </a>
                    <?php } else { ?>
                        <div class="login-register-btn">
                            <a class="apus-user-login btn btn-login" href="#apus_login_forgot_form">
                                <span><?php echo trim($login_text); ?></span>
                                <?php
                                if ( $show_candidate || $show_employer ) {
                                    ?>
                                    <span class="separate">/</span>
                                    <span><?php echo trim($register_text); ?></span>
                                <?php } ?>
                            </a>
                        </div>

                        <div id="apus_login_forgot_form" class="apus_login_register_form mfp-hide" data-effect="fadeIn">
                            <div class="form-login-register-inner">
                                <div class="title-wrapper flex-middle">
                                    <?php if ( !empty($login_title) ) { ?>
                                        <h3 class="title"><?php echo trim($login_title); ?></h3>
                                    <?php } ?>

                                    <a href="javascript:void(0);" class="close-magnific-popup ali-right"><i class="ti-close"></i></a>
                                </div>
                                <?php echo do_shortcode( '[wp_job_board_pro_login popup="true"]' ); ?>
                            </div>
                        </div>

                        <div id="apus_register_form" class="apus_login_register_form mfp-hide" data-effect="fadeIn">
                            <div class="form-login-register-inner">
                                <div class="title-wrapper flex-middle">
                                    <?php if ( !empty($register_title) ) { ?>
                                        <h3 class="title"><?php echo trim($register_title); ?></h3>
                                    <?php } ?>

                                    <a href="javascript:void(0);" class="close-magnific-popup ali-right"><i class="ti-close"></i></a>
                                </div>
                                
                                <?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'template-jobs/misc/register2', array('popup' => true) ); ?>
                            </div>
                        </div>
                    <?php } ?>

                </div>
            <?php }
        }
    }
}

if ( version_compare(ELEMENTOR_VERSION, '3.5.0', '<') ) {
    Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Superio_Elementor_User_Info2 );
} else {
    Elementor\Plugin::instance()->widgets_manager->register( new Superio_Elementor_User_Info2 );
}