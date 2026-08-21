<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Superio_Elementor_Job_Board_Pro_Candidate_Category_Banner extends Elementor\Widget_Base {

	public function get_name() {
        return 'apus_element_job_board_pro_candidate_category_banner';
    }

	public function get_title() {
        return esc_html__( 'Apus Candidate Category Banner', 'superio' );
    }
    
	public function get_categories() {
        return [ 'superio-elements' ];
    }

	protected function register_controls() {

        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__( 'Category Banner', 'superio' ),
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
                'label' => esc_html__( 'Category Slug', 'superio' ),
                'type' => Elementor\Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Enter your Category Slug here', 'superio' ),
            ]
        );

        $this->add_control(
            'icon',
            [
                'label' => esc_html__( 'Category Icon', 'superio' ),
                'type' => Elementor\Controls_Manager::ICON,
            ]
        );

        $this->add_control(
            'show_nb_candidates',
            [
                'label' => esc_html__( 'Show Number Candidates', 'superio' ),
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
                    'v1' => esc_html__('Style 1', 'superio'),
                    'v2' => esc_html__('Style 2', 'superio'),
                ),
                'default' => 'v1'
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


            $this->start_controls_tabs('style_tabs');

                $this->start_controls_tab(
                    'style_normal_tab',
                    [
                        'label' => esc_html__( 'Normal', 'superio' ),
                    ]
                );

                $this->add_control(
                    'heading_color',
                    [
                        'label' => esc_html__( 'Heading Color', 'superio' ),
                        'type' => Elementor\Controls_Manager::COLOR,
                        'selectors' => [
                            '{{WRAPPER}} .title' => 'color: {{VALUE}};',
                        ],
                    ]
                );

                $this->add_control(
                    'icon_color',
                    [
                        'label' => esc_html__( 'Icon Color', 'superio' ),
                        'type' => Elementor\Controls_Manager::COLOR,
                        'selectors' => [
                            '{{WRAPPER}} .category-icon' => 'color: {{VALUE}};',
                        ],
                    ]
                );

                $this->add_control(
                    'icon_bg',
                    [
                        'label' => esc_html__( 'Icon Bg Color', 'superio' ),
                        'type' => Elementor\Controls_Manager::COLOR,
                        'selectors' => [
                            '{{WRAPPER}} .category-icon' => 'background-color: {{VALUE}};',
                        ],
                    ]
                );


                $this->end_controls_tab();

                $this->start_controls_tab(
                    'style_hover_tab',
                    [
                        'label' => esc_html__( 'Hover', 'superio' ),
                    ]
                );


                $this->add_control(
                    'heading_hv_color',
                    [
                        'label' => esc_html__( 'Heading Color', 'superio' ),
                        'type' => Elementor\Controls_Manager::COLOR,
                        'selectors' => [
                            '{{WRAPPER}} .category-banner-inner:hover .title' => 'color: {{VALUE}};',
                        ],
                    ]
                );

                $this->add_control(
                    'icon_hv_color',
                    [
                        'label' => esc_html__( 'Icon Color', 'superio' ),
                        'type' => Elementor\Controls_Manager::COLOR,
                        'selectors' => [
                            '{{WRAPPER}} .category-banner-inner:hover .category-icon' => 'color: {{VALUE}};',
                        ],
                    ]
                );

                $this->add_control(
                    'icon_hv_bg',
                    [
                        'label' => esc_html__( 'Icon Bg Color', 'superio' ),
                        'type' => Elementor\Controls_Manager::COLOR,
                        'selectors' => [
                            '{{WRAPPER}} .category-banner-inner:hover .category-icon' => 'background-color: {{VALUE}};',
                        ],
                    ]
                );

                $this->end_controls_tab();

            $this->end_controls_tabs();

            $this->add_control(
                'border-radius',
                [
                    'label' => esc_html__( 'Border Radius Icon', 'superio' ),
                    'type' => Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors' => [
                        '{{WRAPPER}} .category-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

        $this->end_controls_section();

    }

	protected function render() {
        $settings = $this->get_settings();

        extract( $settings );

        ?>
        <div class="widget-job-category-banner <?php echo esc_attr($el_class); ?>">
            
            <?php
            $term = get_term_by( 'slug', $slug, 'candidate_category' );
            $link = $custom_url;
            if ($term) {
                if ( empty($link) ) {
                    $link = get_term_link( $term, 'candidate_category' );
                }
                if ( empty($title) ) {
                    $title = $term->name;
                }
            }
            ?>

            <a href="<?php echo esc_url($link); ?>">
                <div class="category-banner-inner <?php echo esc_attr($style); ?>">
                    <?php if ( !empty($icon) ) { ?>
                        <div class="category-icon"><i class="<?php echo esc_attr($icon); ?>"></i></div>
                    <?php } ?>
                    <div class="inner">
                        <?php if ( !empty($title) ) { ?>
                            <h4 class="title">
                                <?php echo trim($title); ?>
                            </h4>
                        <?php } ?>

                        <?php if ( $show_nb_candidates ) {
                                $args = array(
                                    'fields' => 'ids',
                                    'categories' => array($slug),
                                    'limit' => 1
                                );
                                $query = superio_get_candidates($args);
                                $number_candidates = $count = $query->found_posts;
                                $number_candidates = $number_candidates ? WP_Job_Board_Pro_Mixes::format_number($number_candidates) : 0;
                        ?>
                            <div class="number"><?php echo sprintf(_n('<span>%d</span> Candidate', '<span>%d</span> Candidates', $count, 'superio'), $number_candidates); ?></div>
                        <?php } ?>
                    </div>
                </div>
            </a>

        </div>
        <?php
    }
}

if ( version_compare(ELEMENTOR_VERSION, '3.5.0', '<') ) {
    Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Superio_Elementor_Job_Board_Pro_Candidate_Category_Banner );
} else {
    Elementor\Plugin::instance()->widgets_manager->register( new Superio_Elementor_Job_Board_Pro_Candidate_Category_Banner );
}