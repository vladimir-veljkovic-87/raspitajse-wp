<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
if ( $user_packages ) : ?>
	<div class="widget widget-your-packages">
		<h2 class="widget-title"><?php esc_html_e( 'Your Packages', 'wp-job-board-pro-wc-paid-listings' ); ?></h2>
		<div class="row">
			<?php
				$prefix = WP_JOB_BOARD_PRO_WC_PAID_LISTINGS_PREFIX;
			foreach ( $user_packages as $key => $package ) :
				$package_count = get_post_meta($package->ID, $prefix.'package_count', true);
				$job_limit = get_post_meta($package->ID, $prefix.'job_limit', true);
				$job_duration = get_post_meta($package->ID, $prefix.'job_duration', true);
				$urgent_jobs = get_post_meta($package->ID, $prefix.'urgent_jobs', true);
				$feature_jobs = get_post_meta($package->ID, $prefix.'feature_jobs', true);
			?>
				<div class="col-sm-4 col-xs-12 user-job-package">
					<h3 class="title"><?php echo trim($package->post_title); ?></h3>
					<ul class="package-information">
						<?php
						if ( $job_limit ) {
							?>
							<li>
								<?php echo sprintf( _n( '%s job posted out of %d', '%s jobs posted out of %d', $package_count, 'wp-job-board-pro-wc-paid-listings' ), $package_count, $job_limit ); ?>
							</li>
							<?php
						} else {
							?>
							<li>
								<?php echo sprintf( _n( '%s job posted', '%s jobs posted', $package_count, 'wp-job-board-pro-wc-paid-listings' ), $package_count ); ?>
							</li>
							<?php
						}

						if ( $job_duration ) {
							?>
							<li>
								<?php echo sprintf( _n( 'listed for %s day', 'listed for %s days', $job_duration, 'wp-job-board-pro-wc-paid-listings' ), $job_duration ); ?>
							</li>
							<?php
						}

						?>
						<li>
							<?php echo sprintf(__( 'Urgent Job: %s', 'wp-job-board-pro-wc-paid-listings' ), $urgent_jobs ? __( 'Yes', 'wp-job-board-pro-wc-paid-listings' ) : __( 'No', 'wp-job-board-pro-wc-paid-listings' )  ); ?>
						</li>
						<li>
							<?php echo sprintf(__( 'Featured Job: %s', 'wp-job-board-pro-wc-paid-listings' ), $feature_jobs ? __( 'Yes', 'wp-job-board-pro-wc-paid-listings' ) : __( 'No', 'wp-job-board-pro-wc-paid-listings' )  ); ?>
						</li>
					</ul>

					<button class="btn btn-danger" type="submit" name="wjbpwpl_listing_user_package" value="<?php echo esc_attr($package->ID); ?>">
						<?php esc_html_e('Add Listing', 'wp-job-board-pro-wc-paid-listings') ?>
					</button>

				</div>
			<?php endforeach; ?>
		</div>
		
	</div>
<?php endif; ?>