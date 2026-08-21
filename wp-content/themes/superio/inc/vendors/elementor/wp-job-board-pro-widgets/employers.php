<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Superio_Elementor_Job_Board_Pro_Employers extends Elementor\Widget_Base {

	public function get_name() {
        return 'apus_element_job_board_pro_employers';
    }

	public function get_title() {
        return esc_html__( 'Apus Employers', 'superio' );
    }
    
	public function get_categories() {
        return [ 'superio-elements' ];
    }

	protected function register_controls() {

        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__( 'Employers', 'superio' ),
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
            'category_slugs',
            [
                'label' => esc_html__( 'Categories Slug', 'superio' ),
                'type' => Elementor\Controls_Manager::TEXTAREA,
                'rows' => 2,
                'default' => '',
                'placeholder' => esc_html__( 'Enter slugs spearate by comma(,)', 'superio' ),
            ]
        );

        $this->add_control(
            'location_slugs',
            [
                'label' => esc_html__( 'Location Slug', 'superio' ),
                'type' => Elementor\Controls_Manager::TEXTAREA,
                'rows' => 2,
                'default' => '',
                'placeholder' => esc_html__( 'Enter slugs spearate by comma(,)', 'superio' ),
            ]
        );

        $this->add_control(
            'limit',
            [
                'label' => esc_html__( 'Limit', 'superio' ),
                'type' => Elementor\Controls_Manager::NUMBER,
                'input_type' => 'number',
                'description' => esc_html__( 'Limit jobs to display', 'superio' ),
                'default' => 4
            ]
        );
        
        $this->add_control(
            'orderby',
            [
                'label' => esc_html__( 'Order by', 'superio' ),
                'type' => Elementor\Controls_Manager::SELECT,
                'options' => array(
                    '' => esc_html__('Default', 'superio'),
                    'date' => esc_html__('Date', 'superio'),
                    'ID' => esc_html__('ID', 'superio'),
                    'author' => esc_html__('Author', 'superio'),
                    'title' => esc_html__('Title', 'superio'),
                    'modified' => esc_html__('Modified', 'superio'),
                    'rand' => esc_html__('Random', 'superio'),
                    'comment_count' => esc_html__('Comment count', 'superio'),
                    'menu_order' => esc_html__('Menu order', 'superio'),
                ),
                'default' => ''
            ]
        );

        $this->add_control(
            'order',
            [
                'label' => esc_html__( 'Sort order', 'superio' ),
                'type' => Elementor\Controls_Manager::SELECT,
                'options' => array(
                    '' => esc_html__('Default', 'superio'),
                    'ASC' => esc_html__('Ascending', 'superio'),
                    'DESC' => esc_html__('Descending', 'superio'),
                ),
                'default' => ''
            ]
        );

        $this->add_control(
            'get_employers_by',
            [
                'label' => esc_html__( 'Get Employers By', 'superio' ),
                'type' => Elementor\Controls_Manager::SELECT,
                'options' => array(
                    'featured' => esc_html__('Featured Employers', 'superio'),
                    'recent' => esc_html__('Recent Employers', 'superio'),
                ),
                'default' => 'recent'
            ]
        );

        $this->add_control(
            'layout_type',
            [
                'label' => esc_html__( 'Layout', 'superio' ),
                'type' => Elementor\Controls_Manager::SELECT,
                'options' => array(
                    'grid' => esc_html__('Grid', 'superio'),
                    'carousel' => esc_html__('Carousel', 'superio'),
                ),
                'default' => 'carousel'
            ]
        );
        $this->add_control(
            'item_type',
            [
                'label' => esc_html__( 'Item Style', 'superio' ),
                'type' => Elementor\Controls_Manager::SELECT,
                'options' => array(
                    'grid' => esc_html__('Grid Default', 'superio'),
                    'grid-v1' => esc_html__('Grid 1', 'superio'),
                    'grid-v2' => esc_html__('Grid 2', 'superio'),
                    'grid-v3' => esc_html__('Grid 3', 'superio'),
                    'grid-v4' => esc_html__('Grid 4', 'superio'),
                    'grid-v5' => esc_html__('Grid 5', 'superio'),
                    'list' => esc_html__('List', 'superio'),
                    'list-v1' => esc_html__('List 1', 'superio'),
                ),
                'default' => 'grid'
            ]
        );

        $columns = range( 1, 12 );
        $columns = array_combine( $columns, $columns );

        $this->add_responsive_control(
            'columns',
            [
                'label' => esc_html__( 'Columns', 'superio' ),
                'type' => Elementor\Controls_Manager::SELECT,
                'options' => $columns,
                'frontend_available' => true,
                'default' => 3,
            ]
        );

        $this->add_responsive_control(
            'slides_to_scroll',
            [
                'label' => esc_html__( 'Slides to Scroll', 'superio' ),
                'type' => Elementor\Controls_Manager::SELECT,
                'description' => esc_html__( 'Set how many slides are scrolled per swipe.', 'superio' ),
                'options' => $columns,
                'condition' => [
                    'columns!' => '1',
                    'layout_type' => 'carousel',
                ],
                'frontend_available' => true,
                'default' => 1,
            ]
        );

        $this->add_control(
            'rows',
            [
                'label' => esc_html__( 'Rows', 'superio' ),
                'type' => Elementor\Controls_Manager::TEXT,
                'input_type' => 'number',
                'placeholder' => esc_html__( 'Enter your rows number here', 'superio' ),
                'default' => 1,
                'condition' => [
                    'layout_type' => 'carousel',
                ],
            ]
        );

        $this->add_control(
            'show_nav',
            [
                'label'         => esc_html__( 'Show Navigation', 'superio' ),
                'type'          => Elementor\Controls_Manager::SWITCHER,
                'label_on'      => esc_html__( 'Show', 'superio' ),
                'label_off'     => esc_html__( 'Hide', 'superio' ),
                'return_value'  => true,
                'default'       => true,
                'condition' => [
                    'layout_type' => 'carousel',
                ],
            ]
        );

        $this->add_control(
            'show_pagination',
            [
                'label'         => esc_html__( 'Show Pagination', 'superio' ),
                'type'          => Elementor\Controls_Manager::SWITCHER,
                'label_on'      => esc_html__( 'Show', 'superio' ),
                'label_off'     => esc_html__( 'Hide', 'superio' ),
                'return_value'  => true,
                'default'       => true,
                'condition' => [
                    'layout_type' => 'carousel',
                ],
            ]
        );

        $this->add_control(
            'autoplay',
            [
                'label'         => esc_html__( 'Autoplay', 'superio' ),
                'type'          => Elementor\Controls_Manager::SWITCHER,
                'label_on'      => esc_html__( 'Yes', 'superio' ),
                'label_off'     => esc_html__( 'No', 'superio' ),
                'return_value'  => true,
                'default'       => true,
                'condition' => [
                    'layout_type' => 'carousel',
                ],
            ]
        );

        $this->add_control(
            'infinite_loop',
            [
                'label'         => esc_html__( 'Infinite Loop', 'superio' ),
                'type'          => Elementor\Controls_Manager::SWITCHER,
                'label_on'      => esc_html__( 'Yes', 'superio' ),
                'label_off'     => esc_html__( 'No', 'superio' ),
                'return_value'  => true,
                'default'       => true,
                'condition' => [
                    'layout_type' => 'carousel',
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

        $this->end_controls_section();

        $this->start_controls_section(
            'section_box_style',
            [
                'label' => esc_html__( 'Box', 'superio' ),
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
                    'box_bg',
                    [
                        'label' => esc_html__( 'Bg Color', 'superio' ),
                        'type' => Elementor\Controls_Manager::COLOR,
                        'selectors' => [
                            '{{WRAPPER}} .layout-employer' => 'background-color: {{VALUE}};',
                        ],
                    ]
                );

                $this->add_group_control(
                    Elementor\Group_Control_Box_Shadow::get_type(),
                    [
                        'name' => 'inner_box_shadow',
                        'selector' => '{{WRAPPER}} .layout-employer',
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
                    'box_hv_bg',
                    [
                        'label' => esc_html__( 'Bg Color', 'superio' ),
                        'type' => Elementor\Controls_Manager::COLOR,
                        'selectors' => [
                            '{{WRAPPER}} .layout-employer:hover' => 'background-color: {{VALUE}};',
                        ],
                    ]
                );

                $this->add_group_control(
                    Elementor\Group_Control_Box_Shadow::get_type(),
                    [
                        'name' => 'inner_hv_box_shadow',
                        'selector' => '{{WRAPPER}} .layout-employer:hover',
                    ]
                );

                $this->add_control(
                    'box_hover_border_color',
                    [
                        'label' => esc_html__( 'Border Color', 'superio' ),
                        'type' => Elementor\Controls_Manager::COLOR,
                        'condition' => [
                            'border_box_border!' => '',
                        ],
                        'selectors' => [
                            '{{WRAPPER}} .layout-employer:hover' => 'border-color: {{VALUE}};',
                        ],
                    ]
                );

                $this->end_controls_tab();

            $this->end_controls_tabs();

            $this->add_group_control(
                Elementor\Group_Control_Border::get_type(),
                [
                    'name' => 'border_box',
                    'label' => esc_html__( 'Border', 'superio' ),
                    'selector' => '{{WRAPPER}} .layout-employer',
                ]
            );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_info_style',
            [
                'label' => esc_html__( 'Info', 'superio' ),
                'tab' => Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

            $this->start_controls_tabs('style_tabs_info');

                    $this->start_controls_tab(
                        'style_normal_tab_info',
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
                                '{{WRAPPER}} .employer-title a' => 'color: {{VALUE}};',
                            ],
                        ]
                    );
                    $this->add_control(
                        'inner_color',
                        [
                            'label' => esc_html__( 'Content and Link Color', 'superio' ),
                            'type' => Elementor\Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .layout-employer' => 'color: {{VALUE}};',
                                '{{WRAPPER}} .layout-employer a' => 'color: {{VALUE}};',
                            ],
                        ]
                    );

                    $this->end_controls_tab();

                    $this->start_controls_tab(
                        'style_hover_tab_info',
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
                                '{{WRAPPER}} .layout-employer:hover .employer-title a' => 'color: {{VALUE}};',
                            ],
                        ]
                    );

                    $this->add_control(
                        'inner_hv_color',
                        [
                            'label' => esc_html__( 'Content and Link Color', 'superio' ),
                            'type' => Elementor\Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .layout-employer:hover' => 'color: {{VALUE}};',
                                '{{WRAPPER}} .layout-employer:hover a' => 'color: {{VALUE}};',
                            ],
                        ]
                    );

                    $this->end_controls_tab();

            $this->end_controls_tabs();

        $this->end_controls_section();


        $this->start_controls_section(
            'section_button_style',
            [
                'label' => esc_html__( 'Button', 'superio' ),
                'tab' => Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

            $this->start_controls_tabs('style_tabs_button');

                    $this->start_controls_tab(
                        'style_normal_tab_button',
                        [
                            'label' => esc_html__( 'Normal', 'superio' ),
                        ]
                    );

                    $this->add_control(
                        'button_color',
                        [
                            'label' => esc_html__( 'Color', 'superio' ),
                            'type' => Elementor\Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .open-job' => 'color: {{VALUE}};',
                            ],
                        ]
                    );

                    $this->add_control(
                        'button_bg_color',
                        [
                            'label' => esc_html__( 'Background Color', 'superio' ),
                            'type' => Elementor\Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .open-job' => 'background-color: {{VALUE}};',
                            ],
                        ]
                    );

                    $this->end_controls_tab();

                    $this->start_controls_tab(
                        'style_hover_tab_button',
                        [
                            'label' => esc_html__( 'Hover', 'superio' ),
                        ]
                    );

                    $this->add_control(
                        'button_hv_color',
                        [
                            'label' => esc_html__( 'Color', 'superio' ),
                            'type' => Elementor\Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .layout-employer:hover .open-job' => 'color: {{VALUE}};',
                            ],
                        ]
                    );

                    $this->add_control(
                        'button_hv_bg_color',
                        [
                            'label' => esc_html__( 'Background Color', 'superio' ),
                            'type' => Elementor\Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .layout-employer:hover .open-job' => 'background-color: {{VALUE}};',
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

        $category_slugs = !empty($category_slugs) ? array_map('trim', explode(',', $category_slugs)) : array();
        $location_slugs = !empty($location_slugs) ? array_map('trim', explode(',', $location_slugs)) : array();

        $args = array(
            'limit' => $limit,
            'get_employers_by' => $get_employers_by,
            'orderby' => $orderby,
            'order' => $order,
            'categories' => $category_slugs,
            'locations' => $location_slugs,
        );
        $loop = superio_get_employers($args);
        if ( $loop->have_posts() ) {
            $columns = !empty($columns) ? $columns : 3;
            $columns_tablet = !empty($columns_tablet) ? $columns_tablet : 2;
            $columns_mobile = !empty($columns_mobile) ? $columns_mobile : 1;
            
            $slides_to_scroll = !empty($slides_to_scroll) ? $slides_to_scroll : $columns;
            $slides_to_scroll_tablet = !empty($slides_to_scroll_tablet) ? $slides_to_scroll_tablet : $slides_to_scroll;
            $slides_to_scroll_mobile = !empty($slides_to_scroll_mobile) ? $slides_to_scroll_mobile : 1;

            ?>
            <div class="widget-employers <?php echo esc_attr($layout_type); ?> <?php echo esc_attr($el_class.' '.$item_type); ?>">
                <?php if ( $title ) { ?>
                    <h2 class="widget-title"><?php echo trim($title); ?></h2>
                <?php } ?>
                <div class="widget-content">
                    <?php if ( $layout_type == 'carousel' ): ?>
                        <div class="slick-carousel"
                            data-items="<?php echo esc_attr($columns); ?>"
                            data-smallmedium="<?php echo esc_attr( $columns_tablet ); ?>"
                            data-extrasmall="<?php echo esc_attr($columns_mobile); ?>"

                            data-slidestoscroll="<?php echo esc_attr($slides_to_scroll); ?>"
                            data-slidestoscroll_smallmedium="<?php echo esc_attr( $slides_to_scroll_tablet ); ?>"
                            data-slidestoscroll_extrasmall="<?php echo esc_attr($slides_to_scroll_mobile); ?>"

                            data-pagination="<?php echo esc_attr( $show_pagination ? 'true' : 'false' ); ?>"
                            data-nav="<?php echo esc_attr( $show_nav ? 'true' : 'false' ); ?>"
                            data-rows="<?php echo esc_attr( $rows ); ?>"
                            data-infinite="<?php echo esc_attr( $infinite_loop ? 'true' : 'false' ); ?>"
                            data-autoplay="<?php echo esc_attr( $autoplay ? 'true' : 'false' ); ?>">
                            <?php while ( $loop->have_posts() ): $loop->the_post(); ?>
                                <div class="item">
                                    <?php get_template_part( 'template-jobs/employers-styles/inner-'.$item_type); ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <?php
                            $mdcol = 12/$columns;
                            $smcol = 12/$columns_tablet;
                            $xscol = 12/$columns_mobile;
                        ?>
                        <div class="row">
                            <?php $i=1; while ( $loop->have_posts() ) : $loop->the_post();
                                $classes = '';
                                if ( $i%$columns == 1 ) {
                                    $classes .= ' md-clearfix lg-clearfix';
                                }
                                if ( $i%$columns_tablet == 1 ) {
                                    $classes .= ' sm-clearfix';
                                }
                                if ( $i%$columns_mobile == 1 ) {
                                    $classes .= ' xs-clearfix';
                                }
                            ?>
                                <div class="col-md-<?php echo esc_attr($mdcol); ?> col-sm-<?php echo esc_attr($smcol); ?> col-xs-<?php echo esc_attr( $xscol ); ?> <?php echo esc_attr( $classes ); ?>">
                                    <?php get_template_part( 'template-jobs/employers-styles/inner-'.$item_type ); ?>
                                </div>
                            <?php $i++; endwhile; ?>
                        </div>
                    <?php endif; ?>
                    <?php wp_reset_postdata(); ?>
                </div>
            </div>
            <?php
        }

    }

}

if ( version_compare(ELEMENTOR_VERSION, '3.5.0', '<') ) {
    Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Superio_Elementor_Job_Board_Pro_Employers );
} else {
    Elementor\Plugin::instance()->widgets_manager->register( new Superio_Elementor_Job_Board_Pro_Employers );
}