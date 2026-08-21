<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Superio_Elementor_Jobs_Single_Package extends Elementor\Widget_Base {

	public function get_name() {
        return 'apus_element_jobs_single_package';
    }

	public function get_title() {
        return esc_html__( 'Apus Single Package', 'superio' );
    }
    
	public function get_categories() {
        return [ 'superio-elements' ];
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
            'product_id',
            [
                'label' => esc_html__( 'Product Package ID', 'superio' ),
                'type' => Elementor\Controls_Manager::TEXT,
            ]
        );

        $this->add_control(
            'image_icon',
            [
                'label' => esc_html__( 'Image or Icon', 'superio' ),
                'type' => Elementor\Controls_Manager::SELECT,
                'options' => array(
                    'icon' => esc_html__('Icon', 'superio'),
                    'image' => esc_html__('Image', 'superio'),
                ),
                'default' => 'image'
            ]
        );

        $this->add_control(
            'icon',
            [
                'label' => esc_html__( 'Icon', 'superio' ),
                'type' => Elementor\Controls_Manager::ICON,
                'default' => 'fa fa-star',
                'condition' => [
                    'image_icon' => 'icon',
                ],
            ]
        );

        $this->add_control(
            'image',
            [
                'label' => esc_html__( 'Choose Image', 'superio' ),
                'type' => Elementor\Controls_Manager::MEDIA,
                'dynamic' => [
                    'active' => true,
                ],
                'condition' => [
                    'image_icon' => 'image',
                ],
            ]
        );

        $this->add_control(
            'highlight',
            [
                'label' => esc_html__( 'Highlight', 'superio' ),
                'type' => Elementor\Controls_Manager::SWITCHER,
                'default' => '',
                'label_on' => esc_html__( 'No', 'superio' ),
                'label_off' => esc_html__( 'Yes', 'superio' ),
            ]
        );

        $this->add_group_control(
            Elementor\Group_Control_Image_Size::get_type(),
            [
                'name' => 'thumbnail', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `thumbnail_size` and `thumbnail_custom_dimension`.
                'default' => 'full',
                'separator' => 'none',
                'condition' => [
                    'image_icon' => 'image',
                ],
            ]
        );

        $this->add_control(
            'style',
            [
                'label' => esc_html__( 'Style', 'superio' ),
                'type' => Elementor\Controls_Manager::SELECT,
                'options' => array(
                    'style1' => esc_html__('Style 1', 'superio'),
                    'style2' => esc_html__('Style 2', 'superio'),
                ),
                'default' => 'style1'
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
                            '{{WRAPPER}} .subwoo-inner' => 'background-color: {{VALUE}};',
                        ],
                    ]
                );

                $this->add_group_control(
                    Elementor\Group_Control_Box_Shadow::get_type(),
                    [
                        'name' => 'inner_box_shadow',
                        'selector' => '{{WRAPPER}} .subwoo-inner',
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
                            '{{WRAPPER}} .subwoo-inner:hover' => 'background-color: {{VALUE}};',
                            '{{WRAPPER}} .subwoo-inner.is_featured' => 'background-color: {{VALUE}};',
                        ],
                    ]
                );

                $this->add_group_control(
                    Elementor\Group_Control_Box_Shadow::get_type(),
                    [
                        'name' => 'inner_hv_box_shadow',
                        'selector' => '{{WRAPPER}} .subwoo-inner:hover',
                        'selector' => '{{WRAPPER}} .subwoo-inner.is_featured',
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
                            '{{WRAPPER}} .subwoo-inner:hover' => 'border-color: {{VALUE}};',
                            '{{WRAPPER}} .subwoo-inner.is_featured' => 'border-color: {{VALUE}};',
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
                    'selector' => '{{WRAPPER}} .subwoo-inner',
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
                                '{{WRAPPER}} .title' => 'color: {{VALUE}};',
                            ],
                        ]
                    );
                    $this->add_control(
                        'price_color',
                        [
                            'label' => esc_html__( 'Price', 'superio' ),
                            'type' => Elementor\Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .subwoo-inner .price' => 'color: {{VALUE}};',
                            ],
                        ]
                    );

                    $this->add_control(
                        'des_color',
                        [
                            'label' => esc_html__( 'Description', 'superio' ),
                            'type' => Elementor\Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .subwoo-inner .short-des' => 'color: {{VALUE}};',
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
                                '{{WRAPPER}} .subwoo-inner:hover .title' => 'color: {{VALUE}};',
                                '{{WRAPPER}} .subwoo-inner.is_featured .title' => 'color: {{VALUE}};',
                            ],
                        ]
                    );

                    $this->add_control(
                        'price_hv_color',
                        [
                            'label' => esc_html__( 'Price', 'superio' ),
                            'type' => Elementor\Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .subwoo-inner:hover .price' => 'color: {{VALUE}};',
                                '{{WRAPPER}} .subwoo-inner.is_featured .price' => 'color: {{VALUE}};',
                            ],
                        ]
                    );

                    $this->add_control(
                        'des_hv_color',
                        [
                            'label' => esc_html__( 'Description', 'superio' ),
                            'type' => Elementor\Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .subwoo-inner:hover .short-des' => 'color: {{VALUE}};',
                                '{{WRAPPER}} .subwoo-inner.is_featured .short-des' => 'color: {{VALUE}};',
                            ],
                        ]
                    );

                    $this->end_controls_tab();

            $this->end_controls_tabs();

            $this->add_group_control(
                Elementor\Group_Control_Typography::get_type(),
                [
                    'label' => esc_html__( 'Title Typography', 'superio' ),
                    'name' => 'typography_title',
                    'selector' => '{{WRAPPER}} .title',
                ]
            );

            $this->add_group_control(
                Elementor\Group_Control_Typography::get_type(),
                [
                    'label' => esc_html__( 'Price Typography', 'superio' ),
                    'name' => 'typography_price',
                    'selector' => '{{WRAPPER}} .price',
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
                                '{{WRAPPER}} .add-cart .added_to_cart' => 'color: {{VALUE}};',
                                '{{WRAPPER}} .add-cart .button' => 'color: {{VALUE}};',
                            ],
                        ]
                    );

                    $this->add_control(
                        'button_bg_color',
                        [
                            'label' => esc_html__( 'Background Color', 'superio' ),
                            'type' => Elementor\Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .add-cart .added_to_cart' => 'background-color: {{VALUE}};',
                                '{{WRAPPER}} .add-cart .button' => 'background-color: {{VALUE}};',
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
                                '{{WRAPPER}} .subwoo-inner:hover .add-cart .added_to_cart' => 'color: {{VALUE}};',
                                '{{WRAPPER}} .subwoo-inner.is_featured .add-cart .added_to_cart' => 'color: {{VALUE}};',
                                '{{WRAPPER}} .subwoo-inner:hover .add-cart .button' => 'color: {{VALUE}};',
                                '{{WRAPPER}} .subwoo-inner.is_featured .add-cart .button' => 'color: {{VALUE}};',
                            ],
                        ]
                    );

                    $this->add_control(
                        'button_hv_bg_color',
                        [
                            'label' => esc_html__( 'Background Color', 'superio' ),
                            'type' => Elementor\Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .subwoo-inner:hover .add-cart .added_to_cart' => 'background-color: {{VALUE}};',
                                '{{WRAPPER}} .subwoo-inner.is_featured .add-cart .added_to_cart' => 'background-color: {{VALUE}};',
                                '{{WRAPPER}} .subwoo-inner:hover .add-cart .button' => 'background-color: {{VALUE}};',
                                '{{WRAPPER}} .subwoo-inner.is_featured .add-cart .button' => 'background-color: {{VALUE}};',
                            ],
                        ]
                    );

                    $this->end_controls_tab();

            $this->end_controls_tabs();
            $this->add_control(
                'border-radius',
                [
                    'label' => esc_html__( 'Border Radius', 'superio' ),
                    'type' => Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors' => [
                        '{{WRAPPER}} .add-cart .button, {{WRAPPER}} .add-cart .added_to_cart, {{WRAPPER}} .add-cart .button:before' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
        $this->end_controls_section();
    }

	protected function render() {
        $settings = $this->get_settings();

        extract( $settings );

        if ( !empty($product_id) ) {
            $product_id = trim($product_id);
            $post_object = get_post( $product_id );
            if ( $post_object ) {
                setup_postdata( $GLOBALS['post'] =& $post_object );
                $product = wc_get_product($post_object);
                if ( !$product ) {
                    return;
                }
                ?>
                <div class="woocommerce widget-subwoo <?php echo esc_attr($el_class); ?>">
                    <?php if( $style == 'style1' ) { ?>
                        <div class="subwoo-inner v2 <?php echo esc_attr( $highlight ? 'is_featured' : 'false' ); ?>">
                            <div class="item">
                                <div class="header-sub text-center">

                                    <!-- image and icon -->
                                    <?php
                                        if ( $image_icon == 'image' ) {
                                            if ( !empty($image['id']) ) {
                                                if ( $thumbnail_size == 'custom' ) {
                                                    if ( $thumbnail_custom_dimension['width'] && $thumbnail_custom_dimension['height'] ) {
                                                        $thumbsize = $thumbnail_custom_dimension['width'].'x'.$thumbnail_custom_dimension['height'];
                                                    } else {
                                                        $thumbsize = 'full';
                                                    }
                                                } else {
                                                    $thumbsize = $thumbnail_size;
                                                }
                                                ?>
                                                <div class="icon-wrapper img">
                                                    <?php echo superio_get_attachment_thumbnail($image['id'], $thumbsize); ?>
                                                </div>
                                                <?php
                                            }
                                        } elseif ( $image_icon == 'icon' && !empty($icon) ) {
                                            ?>
                                            <div class="icon-wrapper icon"><span class="<?php echo esc_attr($icon); ?>"></span></div>
                                            <?php
                                        }
                                    ?>

                                    <div class="top-info">
                                        <h3 class="title"><?php the_title(); ?></h3>
                                    </div>
                                </div>
                                <div class="bottom-sub">
                                    <div class="price text-center"><?php echo (!empty($product->get_price())) ? $product->get_price_html() : esc_html__('Free','superio'); ?></div>
                                    <?php if ( has_excerpt() ) { ?>
                                        <div class="short-des"><?php the_excerpt(); ?></div>
                                    <?php } ?>
                                    <div class="button-action"><?php do_action( 'woocommerce_after_shop_loop_item' ); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="subwoo-inner v3 <?php echo esc_attr( $highlight ? 'is_featured' : 'false' ); ?>">
                            <div class="item">
                                <div class="header-sub text-center">
                                    <h3 class="title"><?php the_title(); ?></h3>
                                    <div class="price">
                                        <?php echo (!empty($product->get_price())) ? $product->get_price_html() : esc_html__('Free','superio'); ?>
                                    </div>
                                    <!-- image and icon -->
                                    <?php
                                        if ( $image_icon == 'image' ) {
                                            if ( !empty($image['id']) ) {
                                                if ( $thumbnail_size == 'custom' ) {
                                                    if ( $thumbnail_custom_dimension['width'] && $thumbnail_custom_dimension['height'] ) {
                                                        $thumbsize = $thumbnail_custom_dimension['width'].'x'.$thumbnail_custom_dimension['height'];
                                                    } else {
                                                        $thumbsize = 'full';
                                                    }
                                                } else {
                                                    $thumbsize = $thumbnail_size;
                                                }
                                                ?>
                                                <div class="icon-wrapper img">
                                                    <?php echo superio_get_attachment_thumbnail($image['id'], $thumbsize); ?>
                                                </div>
                                                <?php
                                            }
                                        } elseif ( $image_icon == 'icon' && !empty($icon) ) {
                                            ?>
                                            <div class="icon-wrapper icon"><span class="<?php echo esc_attr($icon); ?>"></span></div>
                                            <?php
                                        }
                                    ?>
                                </div>
                                <div class="bottom-sub">
                                    <?php if ( has_excerpt() ) { ?>
                                        <div class="short-des"><?php the_excerpt(); ?></div>
                                    <?php } ?>
                                    <div class="button-action"><?php do_action( 'woocommerce_after_shop_loop_item' ); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <?php
                wp_reset_postdata();
            }
        }
    }
}

if ( version_compare(ELEMENTOR_VERSION, '3.5.0', '<') ) {
    Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Superio_Elementor_Jobs_Single_Package );
} else {
    Elementor\Plugin::instance()->widgets_manager->register( new Superio_Elementor_Jobs_Single_Package );
}