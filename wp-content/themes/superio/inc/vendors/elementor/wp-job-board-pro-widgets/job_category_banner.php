<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Superio_Elementor_Job_Board_Pro_Job_Category_Banner extends Elementor\Widget_Base {

	public function get_name() {
        return 'apus_element_job_board_pro_job_category_banner';
    }

	public function get_title() {
        return esc_html__( 'Apus Job Category Banner', 'superio' );
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
            'des',
            [
                'label' => esc_html__( 'Content', 'superio' ),
                'type' => Elementor\Controls_Manager::TEXTAREA,
                'placeholder' => esc_html__( 'Enter your content here', 'superio' ),
                'condition' => [
                    'style' => 'v5',
                ],
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
                    'v3' => esc_html__('Style 3', 'superio'),
                    'v4' => esc_html__('Style 4', 'superio'),
                    'v5' => esc_html__('Style 5', 'superio'),
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
                'label' => esc_html__( 'Content', 'superio' ),
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

                $this->add_group_control(
                    Elementor\Group_Control_Border::get_type(),
                    [
                        'name' => 'border_icon',
                        'label' => esc_html__( 'Border', 'superio' ),
                        'selector' => '{{WRAPPER}} .category-icon',
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

                $this->add_control(
                    'hover_border_icon',
                    [
                        'label' => esc_html__( 'Border Color', 'superio' ),
                        'type' => Elementor\Controls_Manager::COLOR,
                        'default' => '',
                        'condition' => [
                            'border_icon_border!' => '',
                        ],
                        'selectors' => [
                            '{{WRAPPER}} .category-banner-inner:hover .category-icon' => 'border-color: {{VALUE}};',
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

            $this->add_responsive_control(
                'width',
                [
                    'label' => esc_html__( 'Width Icon', 'superio' ),
                    'type' => Elementor\Controls_Manager::SLIDER,
                    'size_units' => [ 'px' ],
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 1000,
                            'step' => 5,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .category-icon' => 'width: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_responsive_control(
                'height',
                [
                    'label' => esc_html__( 'Height Icon', 'superio' ),
                    'type' => Elementor\Controls_Manager::SLIDER,
                    'size_units' => [ 'px' ],
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 1000,
                            'step' => 5,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .category-icon' => 'height: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_group_control(
                Elementor\Group_Control_Typography::get_type(),
                [
                    'label' => esc_html__( 'Icon Typography', 'superio' ),
                    'name' => 'typography_icon',
                    'selector' => '{{WRAPPER}} .category-icon',
                ]
            );

            $this->add_responsive_control(
                'padding_inner',
                [
                    'label' => esc_html__( 'Padding Information', 'superio' ),
                    'type' => Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors' => [
                        '{{WRAPPER}} .inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                    ],
                ]
            );

            $this->add_group_control(
                Elementor\Group_Control_Typography::get_type(),
                [
                    'label' => esc_html__( 'Title Typography', 'superio' ),
                    'name' => 'typography_title',
                    'selector' => '{{WRAPPER}} .category-banner-inner .title',
                ]
            );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_box_style',
            [
                'label' => esc_html__( 'Box', 'superio' ),
                'tab' => Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

            $this->start_controls_tabs('style_tabs_box');

                    $this->start_controls_tab(
                        'style_normal_tab_box',
                        [
                            'label' => esc_html__( 'Normal', 'superio' ),
                        ]
                    );

                    $this->add_control(
                        'box_color',
                        [
                            'label' => esc_html__( 'Color', 'superio' ),
                            'type' => Elementor\Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .category-banner-inner' => 'color: {{VALUE}};',
                            ],
                        ]
                    );

                    $this->add_control(
                        'box_bg',
                        [
                            'label' => esc_html__( 'Bg Color', 'superio' ),
                            'type' => Elementor\Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .category-banner-inner' => 'background-color: {{VALUE}};',
                            ],
                        ]
                    );

                    $this->add_group_control(
                        Elementor\Group_Control_Box_Shadow::get_type(),
                        [
                            'name' => 'inner_box_shadow',
                            'selector' => '{{WRAPPER}} .category-banner-inner',
                        ]
                    );

                    $this->end_controls_tab();

                    $this->start_controls_tab(
                        'style_hover_tab_box',
                        [
                            'label' => esc_html__( 'Hover', 'superio' ),
                        ]
                    );

                    $this->add_control(
                        'box_hv_color',
                        [
                            'label' => esc_html__( 'Color', 'superio' ),
                            'type' => Elementor\Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .category-banner-inner:hover' => 'color: {{VALUE}};',
                                '{{WRAPPER}} .category-banner-inner:hover .number' => 'color: {{VALUE}};',
                            ],
                        ]
                    );

                    $this->add_control(
                        'box_hv_bg',
                        [
                            'label' => esc_html__( 'Bg Color', 'superio' ),
                            'type' => Elementor\Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .category-banner-inner:hover' => 'background-color: {{VALUE}};',
                            ],
                        ]
                    );

                    $this->add_control(
                        'button_hover_border_color',
                        [
                            'label' => esc_html__( 'Border Color', 'superio' ),
                            'type' => Elementor\Controls_Manager::COLOR,
                            'condition' => [
                                'border_box_border!' => '',
                            ],
                            'selectors' => [
                                '{{WRAPPER}} .category-banner-inner:hover' => 'border-color: {{VALUE}};',
                            ],
                        ]
                    );

                    $this->add_group_control(
                        Elementor\Group_Control_Box_Shadow::get_type(),
                        [
                            'name' => 'inner_hv_box_shadow',
                            'selector' => '{{WRAPPER}} .category-banner-inner:hover',
                        ]
                    );

                    $this->end_controls_tab();

            $this->end_controls_tabs();

            $this->add_group_control(
                Elementor\Group_Control_Border::get_type(),
                [
                    'name' => 'border_box',
                    'label' => esc_html__( 'Border', 'superio' ),
                    'selector' => '{{WRAPPER}} .category-banner-inner',
                ]
            );

            $this->add_responsive_control(
                'padding',
                [
                    'label' => esc_html__( 'Padding', 'superio' ),
                    'type' => Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors' => [
                        '{{WRAPPER}} .category-banner-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_responsive_control(
                'border-radius-box',
                [
                    'label' => esc_html__( 'Border Radius', 'superio' ),
                    'type' => Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors' => [
                        '{{WRAPPER}} .category-banner-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
            $term = get_term_by( 'slug', $slug, 'job_listing_category' );
            $link = $custom_url;
            if ($term) {
                if ( empty($link) ) {
                    $link = get_term_link( $term, 'job_listing_category' );
                }
                if ( empty($title) ) {
                    $title = $term->name;
                }
            }
            ?>

            <a href="<?php echo esc_url($link); ?>">
                <div class="category-banner-inner <?php echo esc_attr($style); ?>">
                    <?php if($style == 'v5'){ ?>
                        <?php if ( !empty($icon) ) { ?>
                            <div class="category-icon"><i class="<?php echo esc_attr($icon); ?>"></i></div>
                        <?php } ?>
                        <div class="inner">
                            <?php if ( $show_nb_jobs ) {
                                    $args = array(
                                        'fields' => 'ids',
                                        'categories' => array($slug),
                                        'limit' => 1
                                    );
                                    $query = superio_get_jobs($args);
                                    $number_jobs = $count = $query->found_posts;
                                    $number_jobs = $number_jobs ? WP_Job_Board_Pro_Mixes::format_number($number_jobs) : 0;
                            ?>
                                <div class="number"><?php echo sprintf(_n('(<span>%d</span> open position)', '(<span>%d</span> open positions)', $count, 'superio'), $number_jobs); ?></div>
                            <?php } ?>
                            <?php if ( !empty($title) ) { ?>
                                <h4 class="title">
                                    <?php echo trim($title); ?>
                                </h4>
                            <?php } ?>
                            <?php if ( $des ) { ?>
                                <div class="des"><?php echo trim($des); ?></div>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <?php if ( !empty($icon) ) { ?>
                            <div class="category-icon"><i class="<?php echo esc_attr($icon); ?>"></i></div>
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
                                        'categories' => array($slug),
                                        'limit' => 1
                                    );
                                    $query = superio_get_jobs($args);
                                    $number_jobs = $count = $query->found_posts;
                                    $number_jobs = $number_jobs ? WP_Job_Board_Pro_Mixes::format_number($number_jobs) : 0;
                            ?>
                                <div class="number"><?php echo sprintf(_n('(<span>%d</span> open position)', '(<span>%d</span> open positions)', $count, 'superio'), $number_jobs); ?></div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </a>

        </div>
        <?php
    }
}

if ( version_compare(ELEMENTOR_VERSION, '3.5.0', '<') ) {
    Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Superio_Elementor_Job_Board_Pro_Job_Category_Banner );
} else {
    Elementor\Plugin::instance()->widgets_manager->register( new Superio_Elementor_Job_Board_Pro_Job_Category_Banner );
}