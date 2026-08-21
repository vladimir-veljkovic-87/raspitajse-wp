<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( $packages ) : ?>
	<div class="widget widget-packages widget-subwoo">
		<h2 class="widget-title"><?php esc_html_e( 'Packages', 'wp-job-board-pro-wc-paid-listings' ); ?></h2>
		<div class="row">
			<?php foreach ( $packages as $key => $package ) :
				$product = wc_get_product( $package );
				if ( ! $product->is_type( array( 'job_package', 'job_package_subscription' ) ) || ! $product->is_purchasable() ) {
					continue;
				}

				// ---------------------------------------
				// BUTTON BUSINESS LOGIC (by product slug)
				// ---------------------------------------
				$slug = $product->get_slug();

				$button_text  = __('Get Started', 'wp-job-board-pro-wc-paid-listings');
				$button_class = 'btn-danger';
				$button_type  = 'submit';
				$button_name  = 'wjbpwpl_job_package';
				$button_value = $product->get_id();

				if ($slug === 'start') {
					$button_text = __('Počni besplatno', 'wp-job-board-pro-wc-paid-listings');
				}

				if ($slug === 'pro') {
					$button_text = __('Ubrzaj zapošljavanje', 'wp-job-board-pro-wc-paid-listings');
				}

				if ($slug === 'partner') {
					$button_text  = __('Reši zapošljavanje', 'wp-job-board-pro-wc-paid-listings');
					$button_type  = 'button'; // NE submit
					$button_name  = '';
					$button_value = '';
				}
				?>

				<div class="col-sm-4 col-xs-12">
					<div class="subwoo-inner <?php echo ($product->is_featured())?'highlight':''; ?>">
						<div class="header-sub">
							<div class="inner-sub">
								<h3 class="title"><?php echo trim($product->get_title()); ?></h3>
								<div class="price">
									<?php echo (!empty($product->get_price())) ? $product->get_price_html() : esc_html__('Free', 'wp-job-board-pro-wc-paid-listings'); ?>
								</div>
							</div>
						</div>
						<div class="bottom-sub">
							<div class="content"><?php echo apply_filters( 'the_content', get_post_field('post_content', $product->get_id()) ) ?></div>
							<div class="button-action">
								<button
									class="button btn <?php echo esc_attr($button_class); ?>"
									type="<?php echo esc_attr($button_type); ?>"
									<?php if ( !empty($button_name) ) : ?>
										name="<?php echo esc_attr($button_name); ?>"
									<?php endif; ?>
									<?php if ( !empty($button_value) ) : ?>
										value="<?php echo esc_attr($button_value); ?>"
									<?php endif; ?>
									id="package-<?php echo esc_attr($product->get_id()); ?>">
									<?php echo esc_html($button_text); ?>
								</button>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
<?php endif; ?>
