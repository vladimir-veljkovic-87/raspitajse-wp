<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( $packages ) : ?>
	<div class="widget widget-packages widget-subwoo">
		<h2 id="available-packages class="widget-title"><?php esc_html_e( 'Dostupni paketi', 'wp-job-board-pro-wc-paid-listings' ); ?></h2>
		<div class="row" id="packages-accordion">
			<?php foreach ( $packages as $key => $package ) :
				$product = wc_get_product( $package );
				if ( ! $product->is_type( array( 'job_package', 'job_package_subscription' ) ) || ! $product->is_purchasable() ) {
					continue;
				}

				// ---------------------------------------
				// BUTTON TEXT BY PACKAGE SLUG
				// ---------------------------------------
				$slug = $product->get_slug();

				$button_text = __('Get Started', 'wp-job-board-pro-wc-paid-listings');

				if ($slug === 'start') {
					$button_text = __('Počni besplatno', 'wp-job-board-pro-wc-paid-listings');
				}

				if ($slug === 'pro') {
					$button_text = __('Ubrzaj zapošljavanje', 'wp-job-board-pro-wc-paid-listings');
				}

				if ($slug === 'partner') {
					$button_text = __('Reši zapošljavanje', 'wp-job-board-pro-wc-paid-listings');
				}

				// ---------------------------------------
				// SHORT DESCRIPTION (for "Saznaj više")
				// ---------------------------------------
				$short_description = apply_filters(
					'woocommerce_short_description',
					$product->get_short_description()
				);
				?>

				<div class="col-sm-4 col-xs-12 package-<?php echo esc_attr( $slug ); ?>">
					<div class="subwoo-inner <?php echo ($product->is_featured()) ? 'highlight' : ''; ?>">
						<div class="header-sub">
							<div class="inner-sub">
								<h3 class="title"><?php echo trim( $product->get_title() ); ?></h3>
								<div class="price">
									<?php echo ( ! empty( $product->get_price() ) )
										? $product->get_price_html()
										: esc_html__( 'Free', 'wp-job-board-pro-wc-paid-listings' ); ?>
								</div>
							</div>
						</div>

						<div class="bottom-sub">

							<!-- MAIN DESCRIPTION -->
							<div class="content">
								<?php
								echo apply_filters(
									'the_content',
									get_post_field( 'post_content', $product->get_id() )
								);
								?>
							</div>

							<?php if ( ! empty( trim( $short_description ) ) ) : ?>
								<!-- SAZNAJ VIŠE BUTTON (Bootstrap Collapse) -->
								<button
									class="btn btn-link package-more-toggle"
									type="button"
									data-toggle="collapse"
									data-target="#package-more-<?php echo esc_attr( $product->get_id() ); ?>"
									aria-expanded="false"
									aria-controls="package-more-<?php echo esc_attr( $product->get_id() ); ?>">
									<?php esc_html_e( 'Saznaj više', 'wp-job-board-pro-wc-paid-listings' ); ?>
									<i class="fas fa-chevron-down ml-1"></i>
								</button>

								<!-- COLLAPSE CONTENT -->
								<div
									class="collapse package-more-content"
									id="package-more-<?php echo esc_attr( $product->get_id() ); ?>"
									data-parent="#packages-accordion">
									<?php echo $short_description; ?>
								</div>
							<?php endif; ?>

							<div class="button-action">
								<button
									class="button btn"
									type="submit"
									name="wjbpwpl_job_package"
									value="<?php echo esc_attr( $product->get_id() ); ?>"
									id="package-<?php echo esc_attr( $product->get_id() ); ?>">
									<?php echo esc_html( $button_text ); ?>
								</button>
							</div>

						</div>
					</div>
				</div>

			<?php endforeach; ?>
		</div>
	</div>
<?php endif; ?>
