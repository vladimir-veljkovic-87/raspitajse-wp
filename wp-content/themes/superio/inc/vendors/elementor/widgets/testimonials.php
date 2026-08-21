<?php

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Superio_Elementor_Testimonials extends Widget_Base {

    public function get_name() {
        return 'apus_element_testimonials';
    }

    public function get_title() {
        return esc_html__( 'Apus Testimonials', 'superio' );
    }

    public function get_icon() {
        return 'eicon-testimonial';
    }

    public function get_categories() {
        return [ 'superio-elements' ];
    }

    protected function register_controls() {

        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__( 'Content', 'superio' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'title',
            [
                'label' => esc_html__( 'Title', 'superio' ),
                'type' => Controls_Manager::TEXT,
                'input_type' => 'text',
                'placeholder' => esc_html__( 'Enter your title here', 'superio' ),
            ]
        );

        $this->add_control(
            'des',
            [
                'label' => esc_html__( 'Description', 'superio' ),
                'type' => Controls_Manager::TEXTAREA,
                'input_type' => 'text',
                'placeholder' => esc_html__( 'Enter your description here', 'superio' ),
            ]
        );


        $repeater = new Repeater();

        $repeater->add_control(
            'title',
            [
                'label' => esc_html__( 'Title', 'superio' ),
                'type' => Controls_Manager::TEXT,
                'default' => '',
            ]
        );

        $repeater->add_control(
            'content', [
                'label' => esc_html__( 'Content', 'superio' ),
                'type' => Controls_Manager::TEXTAREA
            ]
        );

        $repeater->add_control(
            'img_src',
            [
                'name' => 'image',
                'label' => esc_html__( 'Choose Image', 'superio' ),
                'type' => Controls_Manager::MEDIA,
                'placeholder'   => esc_html__( 'Upload Brand Image', 'superio' ),
            ]
        );
        $repeater->add_control(
            'name',
            [
                'label' => esc_html__( 'Name', 'superio' ),
                'type' => Controls_Manager::TEXT,
                'default' => '',
            ]
        );

        $repeater->add_control(
            'job',
            [
                'label' => esc_html__( 'Job', 'superio' ),
                'type' => Controls_Manager::TEXT,
                'default' => '',
            ]
        );

        $repeater->add_control(
            'link',
            [
                'label' => esc_html__( 'Link To', 'superio' ),
                'type' => Controls_Manager::URL,
                'placeholder' => esc_html__( 'Enter your social link here', 'superio' ),
                'placeholder' => esc_html__( 'https://your-link.com', 'superio' ),
            ]
        );

        $this->add_control(
            'testimonials',
            [
                'label' => esc_html__( 'Testimonials', 'superio' ),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
            ]
        );
        
        $this->add_control(
            'columns',
            [
                'label' => esc_html__( 'Columns', 'superio' ),
                'type' => Controls_Manager::TEXT,
                'input_type' => 'number',
                'default' => '1'
            ]
        );
        $this->add_control(
            'style',
            [
                'label' => esc_html__( 'Style', 'superio' ),
                'type' => Controls_Manager::SELECT,
                'options' => array(
                    'style1' => esc_html__('Style 1', 'superio'),
                    'style2' => esc_html__('Style 2', 'superio'),
                    'style3' => esc_html__('Style 3', 'superio'),
                    'style4' => esc_html__('Style 4', 'superio'),
                    'style5' => esc_html__('Style 5', 'superio'),
                    'style6' => esc_html__('Style 6', 'superio'),
                    'style7' => esc_html__('Style 7', 'superio'),
                    'style8' => esc_html__('Style 8', 'superio'),
                    'style9' => esc_html__('Style 9', 'superio'),
                    'style10' => esc_html__('Style 10', 'superio'),
                    'style11' => esc_html__('Style 11', 'superio'),
                    'style12' => esc_html__('Style 12', 'superio'),
                ),
                'default' => 'style1'
            ]
        );
        $this->add_control(
            'show_nav',
            [
                'label' => esc_html__( 'Show Nav', 'superio' ),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'label_on' => esc_html__( 'Hide', 'superio' ),
                'label_off' => esc_html__( 'Show', 'superio' ),
            ]
        );

        $this->add_control(
            'show_pagination',
            [
                'label' => esc_html__( 'Show Pagination', 'superio' ),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'label_on' => esc_html__( 'Hide', 'superio' ),
                'label_off' => esc_html__( 'Show', 'superio' ),
            ]
        );

        $this->add_control(
            'infinite_loop',
            [
                'label'         => esc_html__( 'Infinite Loop', 'superio' ),
                'type'          => Controls_Manager::SWITCHER,
                'label_on'      => esc_html__( 'Yes', 'superio' ),
                'label_off'     => esc_html__( 'No', 'superio' ),
                'return_value'  => true,
                'default'       => true,
            ]
        );

        $this->add_control(
            'action',
            [
                'label' => esc_html__( 'Button Action', 'superio' ),
                'type' => Controls_Manager::SELECT,
                'options' => array(
                    '' => esc_html__('Default', 'superio'),
                    'st_light' => esc_html__('Light', 'superio'),
                ),
                'default' => ''
            ]
        );

        $this->add_control(
            'el_class',
            [
                'label'         => esc_html__( 'Extra class name', 'superio' ),
                'type'          => Controls_Manager::TEXT,
                'placeholder'   => esc_html__( 'If you wish to style particular content element differently, please add a class name to this field and refer to it in your custom CSS file.', 'superio' ),
            ]
        );

        $this->end_controls_section();


        $this->start_controls_section(
            'section_title_style',
            [
                'label' => esc_html__( 'Styles', 'superio' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => esc_html__( 'Title Color', 'superio' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .title' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'label' => esc_html__( 'Title Typography', 'superio' ),
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .title',
            ]
        );

        $this->add_control(
            'test_title_color',
            [
                'label' => esc_html__( 'Testimonial Name Color', 'superio' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .name-client a' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .name-client' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'label' => esc_html__( 'Testimonial Name Typography', 'superio' ),
                'name' => 'test_title_typography',
                'selector' => '{{WRAPPER}} .name-client',
            ]
        );

        $this->add_control(
            'content_color',
            [
                'label' => esc_html__( 'Content Color', 'superio' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .description' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'label' => esc_html__( 'Content Typography', 'superio' ),
                'name' => 'content_typography',
                'selector' => '{{WRAPPER}} .description',
            ]
        );

        $this->add_control(
            'job_color',
            [
                'label' => esc_html__( 'Job Color', 'superio' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .job' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'label' => esc_html__( 'Job Typography', 'superio' ),
                'name' => 'job_typography',
                'selector' => '{{WRAPPER}} .job',
            ]
        );

        $this->add_responsive_control(
            'align_pagination',
            [
                'label' => esc_html__( 'Alignment Pagination', 'superio' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__( 'Left', 'superio' ),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'superio' ),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__( 'Right', 'superio' ),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'justify' => [
                        'title' => esc_html__( 'Justified', 'superio' ),
                        'icon' => 'eicon-text-align-justify',
                    ],
                ],
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .slick-carousel .slick-dots' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {

        $settings = $this->get_settings();

        extract( $settings );

        if ( !empty($testimonials) ) {
            ?>
            <div class="widget-testimonials <?php echo esc_attr($el_class.' '.$style); ?>">
                <?php if ( $title || $des) {  ?>
                    <div class="top-information">
                        <?php if ( $title ) { ?>
                            <h2 class="widget-title"><?php echo trim($title); ?></h2>
                        <?php } ?>
                        <?php if ( $des ) { ?>
                            <div class="des"><?php echo trim($des); ?></div>
                        <?php } ?> 
                    </div>    
                <?php } ?>

                <?php if($style == 'style7') { ?>

                    <div class="slick-carousel v1 testimonial-main" data-items="1" data-smallmedium="1" data-extrasmall="1" data-pagination="false" data-nav="false" data-asnavfor=".testimonial-thumbnail" data-slickparent="true">
                        <?php foreach ($testimonials as $item) { ?>
                            <?php $img_src = ( isset( $item['img_src']['id'] ) && $item['img_src']['id'] != 0 ) ? wp_get_attachment_url( $item['img_src']['id'] ) : ''; ?>
                            
                            <div class="testimonials-item style7">
                                <?php if ( !empty($item['content']) ) { ?>
                                    <div class="description"><?php echo trim($item['content']); ?></div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="wrapper-testimonial-thumbnail">
                        <div class="slick-carousel testimonial-thumbnail" data-centermode="true" data-items="3" data-smallmedium="3" data-extrasmall="1" data-pagination="false" data-nav="false" data-asnavfor=".testimonial-main" data-slidestoscroll="1" data-focusonselect="true" data-infinite="true">
                            <?php foreach ($testimonials as $item) { ?>
                                <?php $img_src = ( isset( $item['img_src']['id'] ) && $item['img_src']['id'] != 0 ) ? wp_get_attachment_url( $item['img_src']['id'] ) : ''; ?>
                                    <div class="item">
                                        <div class="bottom-info flex-middle">
                                            <?php if ( $img_src ) { ?>
                                                <div class="avarta">
                                                    <img src="<?php echo esc_url($img_src); ?>" alt="<?php echo esc_attr(!empty($item['name']) ? $item['name'] : ''); ?>">
                                                </div>
                                            <?php } ?>
                                            <div class="info-testimonials">

                                                <?php if ( !empty($item['name']) ) {

                                                    $title = '<h3 class="name-client">'.$item['name'].'</h3>';
                                                    if ( ! empty( $item['link']['url'] ) ) {
                                                        $title = sprintf( '<h3 class="name-client"><a href="'.esc_url($item['link']['url']).'" target="'.esc_attr($item['link']['is_external'] ? '_blank' : '_self').'" '.($item['link']['nofollow'] ? 'rel="nofollow"' : '').'>%1$s</a></h3>', $item['name'] );
                                                    }
                                                    echo trim($title);
                                                ?>
                                                <?php } ?>

                                                <?php if ( !empty($item['job']) ) { ?>
                                                    <div class="job"><?php echo trim($item['job']); ?></div>
                                                <?php } ?>

                                            </div>
                                        </div>
                                    </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } elseif ($style == 'style10') { ?>

                    <div class="slick-carousel v1 testimonial-main" data-items="1" data-smallmedium="1" data-extrasmall="1" data-pagination="false" data-nav="false" data-asnavfor=".testimonial-thumbnail" data-padding="0px" data-slickparent="true">
                        <?php foreach ($testimonials as $item) { ?>
                            <?php $img_src = ( isset( $item['img_src']['id'] ) && $item['img_src']['id'] != 0 ) ? wp_get_attachment_url( $item['img_src']['id'] ) : ''; ?>
                            
                            <div class="testimonials-item style10">
                                <?php if ( !empty($item['content']) ) { ?>
                                    <div class="description"><?php echo trim($item['content']); ?></div>
                                <?php } ?>
                                <div class="info-testimonials">

                                    <?php if ( !empty($item['name']) ) {

                                        $title = '<h3 class="name-client">'.$item['name'].'</h3>';
                                        if ( ! empty( $item['link']['url'] ) ) {
                                            $title = sprintf( '<h3 class="name-client"><a href="'.esc_url($item['link']['url']).'" target="'.esc_attr($item['link']['is_external'] ? '_blank' : '_self').'" '.($item['link']['nofollow'] ? 'rel="nofollow"' : '').'>%1$s</a></h3>', $item['name'] );
                                        }
                                        echo trim($title);
                                    ?>
                                    <?php } ?>

                                    <?php if ( !empty($item['job']) ) { ?>
                                        <div class="job"><?php echo trim($item['job']); ?></div>
                                    <?php } ?>

                                </div>
                            </div>

                        <?php } ?>
                    </div>

                    <div class="wrapper-testimonial-thumbnail style10">
                        <div class="slick-carousel testimonial-thumbnail" data-centermode="true" data-centerpadding="0px" data-items="5" data-smallmedium="5" data-extrasmall="4" data-pagination="false" data-nav="false" data-asnavfor=".testimonial-main" data-slidestoscroll="1" data-focusonselect="true" data-infinite="true">
                            <?php foreach ($testimonials as $item) { ?>
                                <?php $img_src = ( isset( $item['img_src']['id'] ) && $item['img_src']['id'] != 0 ) ? wp_get_attachment_url( $item['img_src']['id'] ) : ''; ?>
                                    <div class="item">
                                        <div class="bottom-info flex-middle">
                                            <?php if ( $img_src ) { ?>
                                                <div class="avarta">
                                                    <img src="<?php echo esc_url($img_src); ?>" alt="<?php echo esc_attr(!empty($item['name']) ? $item['name'] : ''); ?>">
                                                </div>
                                            <?php } ?>
                                            
                                        </div>
                                    </div>
                            <?php } ?>
                        </div>
                    </div>
                    
                <?php }else{ ?>

                    <div class="slick-carousel testimonial-main <?php echo esc_attr($action); ?>" data-items="<?php echo esc_attr($columns); ?>" data-smallmedium="1" data-extrasmall="1" data-pagination="<?php echo esc_attr($show_pagination ? 'true' : 'false'); ?>" data-nav="<?php echo esc_attr($show_nav ? 'true' : 'false'); ?>" data-infinite="<?php echo esc_attr( $infinite_loop ? 'true' : 'false' ); ?>">
                        <?php foreach ($testimonials as $item) { ?>
                        <?php $img_src = ( isset( $item['img_src']['id'] ) && $item['img_src']['id'] != 0 ) ? wp_get_attachment_url( $item['img_src']['id'] ) : ''; ?>
                        <div class="item">
                                <div class="testimonials-item <?php echo esc_attr($style); ?>">
                                    <?php if(($style == 'style1') || ($style == 'style5') || ($style == 'style11')) { ?>
                                        <?php if($style == 'style11'){ ?>
                                            <svg width="37" height="26" viewBox="0 0 37 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M9.1 25.8C6.76667 25.8 4.66667 24.8667 2.8 23C0.933334 21.0667 0 18.2333 0 14.5C0 10.2333 1.13333 6.76666 3.4 4.1C5.73333 1.36667 9.03333 0 13.3 0C14.8333 0 16.0333 0.099998 16.9 0.299995V4.9C15.9667 4.76666 14.7667 4.7 13.3 4.7C11.0333 4.7 9.2 5.46666 7.8 7C6.46667 8.33333 5.7 10.1 5.5 12.3C6.36667 11.2333 7.76667 10.7 9.7 10.7C11.7 10.7 13.4 11.4 14.8 12.8C16.2 14.1333 16.9 15.9 16.9 18.1C16.9 20.3667 16.1667 22.2333 14.7 23.7C13.2333 25.1 11.3667 25.8 9.1 25.8ZM29 25.8C26.6667 25.8 24.5667 24.8667 22.7 23C20.8333 21.0667 19.9 18.2333 19.9 14.5C19.9 10.2333 21.0333 6.76666 23.3 4.1C25.6333 1.36667 28.9333 0 33.2 0C34.7333 0 35.9333 0.099998 36.8 0.299995V4.9C35.8667 4.76666 34.6667 4.7 33.2 4.7C30.9333 4.7 29.1 5.46666 27.7 7C26.3667 8.33333 25.6 10.1 25.4 12.3C26.2667 11.2333 27.6667 10.7 29.6 10.7C31.6 10.7 33.3 11.4 34.7 12.8C36.1 14.1333 36.8 15.9 36.8 18.1C36.8 20.3667 36.0667 22.2333 34.6 23.7C33.1333 25.1 31.2667 25.8 29 25.8Z" fill="currentColor"/>
                                            </svg>
                                        <?php } ?>
                                        <?php if ( !empty($item['title']) ) { ?>
                                            <h3 class="title text-theme"><?php echo trim($item['title']); ?></h3>
                                        <?php } ?>

                                        <?php if ( !empty($item['content']) ) { ?>
                                            <div class="description"><?php echo trim($item['content']); ?></div>
                                        <?php } ?>

                                        <div class="bottom-info flex-middle">
                                            <?php if ( $img_src ) { ?>
                                                <div class="avarta">
                                                    <img src="<?php echo esc_url($img_src); ?>" alt="<?php echo esc_attr(!empty($item['name']) ? $item['name'] : ''); ?>">
                                                </div>
                                            <?php } ?>
                                            <div class="info-testimonials">

                                                <?php if ( !empty($item['name']) ) {

                                                    $title = '<h3 class="name-client">'.$item['name'].'</h3>';
                                                    if ( ! empty( $item['link']['url'] ) ) {
                                                        $title = sprintf( '<h3 class="name-client"><a href="'.esc_url($item['link']['url']).'" target="'.esc_attr($item['link']['is_external'] ? '_blank' : '_self').'" '.($item['link']['nofollow'] ? 'rel="nofollow"' : '').'>%1$s</a></h3>', $item['name'] );
                                                    }
                                                    echo trim($title);
                                                ?>
                                                <?php } ?>

                                                <?php if ( !empty($item['job']) ) { ?>
                                                    <div class="job"><?php echo trim($item['job']); ?></div>
                                                <?php } ?>

                                            </div>
                                        </div>
                                    <?php } elseif($style == 'style4') { ?>

                                        <?php if ( !empty($item['title']) ) { ?>
                                            <h3 class="title"><?php echo trim($item['title']); ?></h3>
                                        <?php } ?>

                                        <?php if ( !empty($item['content']) ) { ?>
                                            <div class="description"><?php echo trim($item['content']); ?></div>
                                        <?php } ?>

                                        <?php if ( $img_src ) { ?>
                                            <div class="avarta">
                                                <img src="<?php echo esc_url($img_src); ?>" alt="<?php echo esc_attr(!empty($item['name']) ? $item['name'] : ''); ?>">
                                            </div>
                                        <?php } ?>
                                        
                                        <div class="info-testimonials">

                                            <?php if ( !empty($item['name']) ) {

                                                $title = '<h3 class="name-client">'.$item['name'].'</h3>';
                                                if ( ! empty( $item['link']['url'] ) ) {
                                                    $title = sprintf( '<h3 class="name-client"><a href="'.esc_url($item['link']['url']).'" target="'.esc_attr($item['link']['is_external'] ? '_blank' : '_self').'" '.($item['link']['nofollow'] ? 'rel="nofollow"' : '').'>%1$s</a></h3>', $item['name'] );
                                                }
                                                echo trim($title);
                                            ?>
                                            <?php } ?>

                                            <?php if ( !empty($item['job']) ) { ?>
                                                <div class="job"><?php echo trim($item['job']); ?></div>
                                            <?php } ?>

                                        </div>
                                    <?php } elseif($style == 'style6') { ?>
                                        
                                        <?php if ( $img_src ) { ?>
                                            <div class="avarta">
                                                <img src="<?php echo esc_url($img_src); ?>" alt="<?php echo esc_attr(!empty($item['name']) ? $item['name'] : ''); ?>">
                                            </div>
                                        <?php } ?>

                                        <?php if ( !empty($item['title']) ) { ?>
                                            <h3 class="title"><?php echo trim($item['title']); ?></h3>
                                        <?php } ?>

                                        <?php if ( !empty($item['content']) ) { ?>
                                            <div class="description"><?php echo trim($item['content']); ?></div>
                                        <?php } ?>

                                        <div class="info-testimonials">

                                            <?php if ( !empty($item['name']) ) {

                                                $title = '<h3 class="name-client">'.$item['name'].'</h3>';
                                                if ( ! empty( $item['link']['url'] ) ) {
                                                    $title = sprintf( '<h3 class="name-client"><a href="'.esc_url($item['link']['url']).'" target="'.esc_attr($item['link']['is_external'] ? '_blank' : '_self').'" '.($item['link']['nofollow'] ? 'rel="nofollow"' : '').'>%1$s</a></h3>', $item['name'] );
                                                }
                                                echo trim($title);
                                            ?>
                                            <?php } ?>

                                            <?php if ( !empty($item['job']) ) { ?>
                                                <div class="job"><?php echo trim($item['job']); ?></div>
                                            <?php } ?>

                                        </div>
                                    <?php } elseif($style == 'style8' || $style == 'style9' || $style == 'style12') { ?>
                                        
                                        <div class="bottom-info flex-middle">
                                            <?php if($style == 'style12'){ ?>
                                                <div class="avarta">
                                                    <svg width="25" height="18" viewBox="0 0 25 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M0.136612 17.8279C0.0455375 17.2359 0 16.6439 0 16.0519C0 15.4599 0 14.9818 0 14.6175C0.0910747 11.8852 0.614754 9.26685 1.57104 6.76229C2.57286 4.25774 3.7796 2.00364 5.19126 0L11.2705 1.63934C10.3597 3.64299 9.65392 5.76047 9.15301 7.9918C8.6521 10.2231 8.40164 12.3406 8.40164 14.3443C8.40164 14.7086 8.40164 15.2322 8.40164 15.9153C8.40164 16.5984 8.44718 17.2359 8.53825 17.8279H0.136612ZM13.8661 17.8279C13.775 17.2359 13.7295 16.6439 13.7295 16.0519C13.7295 15.4599 13.7295 14.9818 13.7295 14.6175C13.8206 11.8852 14.3443 9.26685 15.3005 6.76229C16.3024 4.25774 17.5091 2.00364 18.9208 0L25 1.63934C24.0893 3.64299 23.3834 5.76047 22.8825 7.9918C22.3816 10.2231 22.1311 12.3406 22.1311 14.3443C22.1311 14.7086 22.1311 15.2322 22.1311 15.9153C22.1311 16.5984 22.1767 17.2359 22.2678 17.8279H13.8661Z" fill="currentColor"/>
                                                    </svg>
                                                </div>
                                            <?php } elseif ( $img_src ) { ?>
                                                <div class="avarta">
                                                    <img src="<?php echo esc_url($img_src); ?>" alt="<?php echo esc_attr(!empty($item['name']) ? $item['name'] : ''); ?>">
                                                </div>
                                            <?php } ?>

                                            <div class="info-testimonials">

                                                <?php if ( !empty($item['name']) ) {

                                                    $title = '<h3 class="name-client">'.$item['name'].'</h3>';
                                                    if ( ! empty( $item['link']['url'] ) ) {
                                                        $title = sprintf( '<h3 class="name-client"><a href="'.esc_url($item['link']['url']).'" target="'.esc_attr($item['link']['is_external'] ? '_blank' : '_self').'" '.($item['link']['nofollow'] ? 'rel="nofollow"' : '').'>%1$s</a></h3>', $item['name'] );
                                                    }
                                                    echo trim($title);
                                                ?>
                                                <?php } ?>

                                                <?php if ( !empty($item['job']) ) { ?>
                                                    <div class="job"><?php echo trim($item['job']); ?></div>
                                                <?php } ?>

                                            </div>
                                        </div>

                                        <?php if ( !empty($item['title']) ) { ?>
                                            <h3 class="title text-theme"><?php echo trim($item['title']); ?></h3>
                                        <?php } ?>

                                        <?php if ( !empty($item['content']) ) { ?>
                                            <div class="description"><?php echo trim($item['content']); ?></div>
                                        <?php } ?>

                                    <?php } else { ?>
                                        <?php if ( $img_src ) { ?>
                                            <div class="avarta">
                                                <img src="<?php echo esc_url($img_src); ?>" alt="<?php echo esc_attr(!empty($item['name']) ? $item['name'] : ''); ?>">
                                            </div>
                                        <?php } ?>
                                        <?php if ( !empty($item['title']) ) { ?>
                                            <h3 class="title text-theme"><?php echo trim($item['title']); ?></h3>
                                        <?php } ?>
                                        <?php if ( !empty($item['content']) ) { ?>
                                            <div class="description"><?php echo trim($item['content']); ?></div>
                                        <?php } ?>
                                        <div class="info-testimonials">

                                            <?php if ( !empty($item['name']) ) {

                                                $title = '<h3 class="name-client">'.$item['name'].'</h3>';
                                                if ( ! empty( $item['link']['url'] ) ) {
                                                    $title = sprintf( '<h3 class="name-client"><a href="'.esc_url($item['link']['url']).'" target="'.esc_attr($item['link']['is_external'] ? '_blank' : '_self').'" '.($item['link']['nofollow'] ? 'rel="nofollow"' : '').'>%1$s</a></h3>', $item['name'] );
                                                }
                                                echo trim($title);
                                            ?>
                                            <?php } ?>

                                            <?php if ( !empty($item['job']) ) { ?>
                                                <div class="job"><?php echo trim($item['job']); ?></div>
                                            <?php } ?>

                                        </div>
                                    <?php } ?>
                                </div>

                        </div>
                        <?php } ?>
                    </div>

                <?php } ?>
            </div>
            <?php
        }
    }
}
if ( version_compare(ELEMENTOR_VERSION, '3.5.0', '<') ) {
    Plugin::instance()->widgets_manager->register_widget_type( new Superio_Elementor_Testimonials );
} else {
    Plugin::instance()->widgets_manager->register( new Superio_Elementor_Testimonials );
}