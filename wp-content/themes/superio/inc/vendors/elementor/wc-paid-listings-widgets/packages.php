<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Superio_Elementor_Jobs_Packages extends Elementor\Widget_Base {

	public function get_name() {
        return 'apus_element_jobs_packages';
    }

	public function get_title() {
        return esc_html__( 'Apus Packages', 'superio' );
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

        $options = array(
            'job_package' => esc_html__('Job Package', 'superio'),
            'cv_package' => esc_html__('CV Package', 'superio'),
            'contact_package' => esc_html__('Contact Package', 'superio'),
            'candidate_package' => esc_html__('Canidate Package', 'superio'),
            'resume_package' => esc_html__('Resume Package', 'superio'),
        );

        if ( class_exists( 'WC_Subscriptions' ) ) {
            $options['job_package_subscription'] = esc_html__( 'Job Package Subscription', 'superio' );
            $options['cv_package_subscription'] = esc_html__( 'CV Package Subscription', 'superio' );
            $options['contact_package_subscription'] = esc_html__( 'Contact Package Subscription', 'superio' );
            $options['candidate_package_subscription'] = esc_html__( 'Candidate Package Subscription', 'superio' );
            $options['resume_package_subscription'] = esc_html__( 'Resume Package Subscription', 'superio' );
        }

        $this->add_control(
            'package_type',
            [
                'label' => esc_html__( 'Packages Type', 'superio' ),
                'type' => Elementor\Controls_Manager::SELECT,
                'options' => $options,
                'default' => 'job_package'
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
            'number',
            [
                'label' => esc_html__( 'Number Product', 'superio' ),
                'type' => Elementor\Controls_Manager::NUMBER,
                'input_type' => 'number',
                'description' => esc_html__( 'Number Product to display', 'superio' ),
                'default' => 3
            ]
        );
        $this->add_control(
            'columns',
            [
                'label' => esc_html__( 'Columns', 'superio' ),
                'type' => Elementor\Controls_Manager::TEXT,
                'input_type' => 'number',
                'default' => 3,
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
    }

	protected function render() {
        $settings = $this->get_settings();

        extract( $settings );

        $loop = superio_get_products( array(
            'product_type' => $package_type,
            'post_per_page' => $number,
            'orderby' => $orderby,
            'order' => $order
        ));
        ?>
        <div class="no-margin widget woocommerce widget-subwoo <?php echo esc_attr($el_class); ?>">
            <?php if ($loop->have_posts()): ?>
                <div class="row">
                    <?php $i = 1; while ( $loop->have_posts() ) : $loop->the_post(); global $product;
                        $classes = '';
                        if ( $i%$columns == 1 ) {
                            $classes .= ' sm-clearfix md-clearfix lg-clearfix';
                        }
                    ?>
                        <div class="col-xs-12 col-sm-<?php echo esc_attr(12/$columns); ?> <?php echo esc_attr($classes); ?>">
                            <div class="subwoo-inner <?php echo esc_attr( $product->is_featured() ? ' is_featured' : '' ); ?>">
                                <div class="item">
                                    <div class="header-sub">
                                        <div class="icon-wrapper">
                                            <?php
                                            $icon_class = get_post_meta($product->get_id(), '_jobs_icon_class', true);
                                            if ( $icon_class ) {
                                                ?>
                                                <span class="<?php echo esc_attr($icon_class); ?>"></span>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="top-info clearfix flex-middle-lg">
                                            <h3 class="title"><?php the_title(); ?></h3>
                                            <?php if($product->is_featured()){ ?>
                                                <div class="recommended ali-right">
                                                    <?php echo esc_html__('Recommended','superio'); ?>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="bottom-sub">
                                        <div class="price"><?php echo (!empty($product->get_price())) ? $product->get_price_html() : esc_html__('Free','superio'); ?></div>
                                        <?php if ( has_excerpt() ) { ?>
                                            <div class="short-des"><?php the_excerpt(); ?></div>
                                        <?php } ?>
                                        <div class="button-action"><?php do_action( 'woocommerce_after_shop_loop_item' ); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>  
                    <?php $i++; endwhile; ?>
                </div>
                <?php wp_reset_postdata(); ?>
            <?php endif; ?>
        </div>
        <?php
    }
}


if ( version_compare(ELEMENTOR_VERSION, '3.5.0', '<') ) {
    Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Superio_Elementor_Jobs_Packages );
} else {
    Elementor\Plugin::instance()->widgets_manager->register( new Superio_Elementor_Jobs_Packages );
}