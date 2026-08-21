<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( $packages ) : ?>
	<div class="widget woocommerce widget-subwoo">
		<h2 class="widget-title"><?php esc_html_e( 'Resume Packages', 'superio' ); ?></h2>
		<div class="row">
			<?php foreach ( $packages as $key => $package ) :
				$post_object = get_post($package);
				setup_postdata( $GLOBALS['post'] =& $post_object );
				$product = wc_get_product( $package );
				if ( ! $product->is_type( array( 'resume_package', 'resume_package_subscription' ) ) || ! $product->is_purchasable() ) {
					continue;
				}
				?>

				<div class="col-sm-4 col-xs-12">
					<div class="subwoo-inner <?php echo esc_attr($product->is_featured()?'highlight':''); ?>">
						<div class="item">
							<div class="header-sub">
								<div class="inner-sub">
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
									<div class="top-info clearfix flex-middle">
                                        <h3 class="title"><?php the_title(); ?></h3>
                                        <?php if($product->is_featured()){ ?>
                                            <div class="recommended ali-right">
                                                <?php echo esc_html__('Recommended','superio'); ?>
                                            </div>
                                        <?php } ?>
                                    </div>
								</div>
							</div>
							<div class="bottom-sub">
								<div class="price">
									<?php echo (!empty($product->get_price())) ? $product->get_price_html() : esc_html__('Free', 'superio'); ?>
								</div>
								<div class="short-des"><?php echo apply_filters( 'the_excerpt', get_post_field('post_excerpt', $product->get_id()) ) ?></div>
								<div class="button-action">
									<div class="button-action"><?php do_action( 'woocommerce_after_shop_loop_item' ); ?></div>
								</div>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach;
				wp_reset_postdata();
			?>
		</div>
	</div>
<?php endif; ?>