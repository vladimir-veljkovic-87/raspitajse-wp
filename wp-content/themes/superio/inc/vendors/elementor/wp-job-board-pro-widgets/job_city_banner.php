<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Superio_Elementor_Job_Board_Pro_Job_City_Banner extends Elementor\Widget_Base {

	public function get_name() {
        return 'apus_element_job_board_pro_job_city_banner';
    }

	public function get_title() {
        return esc_html__( 'Apus Job City Banner', 'superio' );
    }
    
	public function get_categories() {
        return [ 'superio-elements' ];
    }

	protected function register_controls() {

        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__( 'City Banner', 'superio' ),
                'tab' => Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'title',
            [
                'label' => esc_html__( 'Title', 'superio' ),
                'type' => Elementor\Controls_Manager::TEXT,
                'input_type' => 'text',
                'placeholder' => esc_html__( 'Enter your title here', 'superio' ),
            ]
        );

        $this->add_control(
            'slug',
            [
                'label' => esc_html__( 'Location Slug', 'superio' ),
                'type' => Elementor\Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Enter your Location Slug here', 'superio' ),
            ]
        );

        $this->add_control(
            'img_src',
            [
                'name' => 'image',
                'label' => esc_html__( 'Image', 'superio' ),
                'type' => Elementor\Controls_Manager::MEDIA,
                'placeholder'   => esc_html__( 'Upload Image Here', 'superio' ),
            ]
        );

        $this->add_control(
            'show_nb_jobs',
            [
                'label' => esc_html__( 'Show Number Jobs', 'superio' ),
                'type' => Elementor\Controls_Manager::SWITCHER,
                'default' => '',
                'label_on' => esc_html__( 'Hide', 'superio' ),
                'label_off' => esc_html__( 'Show', 'superio' ),
            ]
        );

        $this->add_control(
            'custom_url',
            [
                'label' => esc_html__( 'Custom URL', 'superio' ),
                'type' => Elementor\Controls_Manager::TEXT,
                'input_type' => 'url',
                'placeholder' => esc_html__( 'Enter your custom url here', 'superio' ),
            ]
        );

        $this->add_control(
            'style',
            [
                'label' => esc_html__( 'Style', 'superio' ),
                'type' => Elementor\Controls_Manager::SELECT,
                'options' => array(
                    '' => esc_html__('Default', 'superio'),
                    'v1' => esc_html__('Style 1', 'superio'),
                    'v2' => esc_html__('Style 2', 'superio'),
                    'v3' => esc_html__('Style 3', 'superio'),
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
            'section_icon_style',
            [
                'label' => esc_html__( 'Style', 'superio' ),
                'tab' => Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'box_height',
            [
                'label' => esc_html__( 'Height', 'superio' ),
                'type' => Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .city-banner-inner' => 'height: {{SIZE}}{{UNIT}}; max-height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'heading_color',
            [
                'label' => esc_html__( 'Heading Color', 'superio' ),
                'type' => Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .title' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .title:before' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'number_color',
            [
                'label' => esc_html__( 'Number Color', 'superio' ),
                'type' => Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .number' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

    }

	protected function render() {
        $settings = $this->get_settings();

        extract( $settings );

        ?>
        <div class="widget-job-city-banner <?php echo esc_attr($el_class); ?>">
            
            <?php
            $term = get_term_by( 'slug', $slug, 'job_listing_location' );
            $link = $custom_url;
            if ($term) {
                if ( empty($link) ) {
                    $link = get_term_link( $term, 'job_listing_location' );
                }
                if ( empty($title) ) {
                    $title = $term->name;
                }
            }
            ?>

            <?php
            
                $img_src = ( isset( $img_src['id'] ) && $img_src['id'] != 0 ) ? wp_get_attachment_url( $img_src['id'] ) : '';
                $style_bg = '';
                if ( !empty($img_src) && ($style !== 'v1' ) && ( $style !== 'v2') && ( $style !== 'v3') ) {
                    $style_bg = 'style="background-image:url('.esc_url($img_src).')"';
                }
            ?>
                <a href="<?php echo esc_url($link); ?>">
                    <div class="city-banner-inner <?php echo esc_attr($style); ?>">
                        <?php if(!empty($style_bg)){ ?>
                            <div class="bg-banner" <?php echo trim($style_bg); ?>></div>
                        <?php } ?>
                        <?php if( ($style == 'v1' || $style == 'v2' || $style == 'v3') && !empty($img_src)) { ?>
                            <div class="img-banner flex-middle">
                                <img src="<?php echo esc_url($img_src); ?>" alt="">
                            </div>
                        <?php } ?>
                        <div class="inner">
                            <?php if ( !empty($title) ) { ?>
                                <h4 class="title">
                                    <?php echo trim($title); ?>
                                </h4>
                            <?php } ?>

                            <?php if ( $show_nb_jobs ) {
                                    $args = array(
                                        'fields' => 'ids',
                                        'locations' => array($slug),
                                        'limit' => 1
                                    );
                                    $query = superio_get_jobs($args);
                                    $number_jobs = $count = $query->found_posts;
                                    $number_jobs = $number_jobs ? WP_Job_Board_Pro_Mixes::format_number($number_jobs) : 0;
                            ?>
                                <div class="number"><?php echo sprintf(_n('<span>%d</span> job', '<span>%d</span> jobs', $count, 'superio'), $number_jobs); ?></div>
                            <?php } ?>
                        </div>
                    </div>
                </a>

        </div>
        <?php
    }
}

if ( version_compare(ELEMENTOR_VERSION, '3.5.0', '<') ) {
    Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Superio_Elementor_Job_Board_Pro_Job_City_Banner );
} else {
    Elementor\Plugin::instance()->widgets_manager->register( new Superio_Elementor_Job_Board_Pro_Job_City_Banner );
}