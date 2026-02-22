<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Superio_Elementor_Jobs_User_Packages_Custom extends Elementor\Widget_Base {

	public function get_name() {
		// VAŽNO: isto ime kao original, da Elementor nastavi da koristi widget na stranici
		return 'apus_element_jobs_user_packages';
	}

	public function get_title() {
		return esc_html__( 'Apus User Packages', 'superio' );
	}

	public function get_categories() {
		return [ 'superio-jobs-elements' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Content', 'superio' ),
				'tab'   => Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'title',
			[
				'label'   => esc_html__( 'Title', 'superio' ),
				'type'    => Elementor\Controls_Manager::TEXT,
				'default' => '',
			]
		);

		$this->add_control(
			'el_class',
			[
				'label'       => esc_html__( 'Extra class name', 'superio' ),
				'type'        => Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Extra class for CSS.', 'superio' ),
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings();
		$title    = ! empty($settings['title']) ? $settings['title'] : '';
		$el_class = ! empty($settings['el_class']) ? $settings['el_class'] : '';

		?>
		<div class="box-dashboard-wrapper">
			<?php if ( $title !== '' ) : ?>
				<h2 class="title"><?php echo esc_html( $title ); ?></h2>
			<?php endif; ?>

			<div class="inner-list">
				<?php if ( ! is_user_logged_in() ) : ?>
					<div class="box-list-2">
						<div class="text-warning"><?php esc_html_e( 'Please login as "Employer" to see this page.', 'superio' ); ?></div>
					</div>
				<?php else :
					$user_id  = get_current_user_id();
					$packages = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::get_packages_by_user( $user_id, false, 'all' );

					if ( ! empty( $packages ) ) : ?>
						<div class="widget-user-packages <?php echo esc_attr( $el_class ); ?>">
							<div class="widget-content table-responsive">
								<table class="job-table">
									<thead>
										<tr>
											<th><?php esc_html_e('#', 'superio'); ?></th>
											<th><?php esc_html_e('ID', 'superio'); ?></th>
											<th><?php esc_html_e('Package', 'superio'); ?></th>
											<th><?php esc_html_e('Package Type', 'superio'); ?></th>
											<th><?php esc_html_e('Package Info', 'superio'); ?></th>
											<th><?php esc_html_e('Status', 'superio'); ?></th>
										</tr>
									</thead>
									<tbody>
									<?php
									$i = 1;
									foreach ( $packages as $package ) {
										$prefix = WP_JOB_BOARD_PRO_WC_PAID_LISTINGS_PREFIX;

										$package_type      = get_post_meta($package->ID, $prefix . 'package_type', true);
										$package_types     = WP_Job_Board_Pro_Wc_Paid_Listings_Post_Type_Packages::package_types();
										$subscription_type = get_post_meta($package->ID, $prefix . 'subscription_type', true);

										// EXPIRATION (isti princip kao tvoj working filter)
										$expires_raw = get_user_meta($user_id, '_wjbp_package_expiration_' . $package->ID, true);
										$expires_ts  = $expires_raw ? strtotime($expires_raw) : 0;
										$is_expired  = ( $expires_ts && $expires_ts < current_time('timestamp') );
										?>
										<tr>
											<td><?php echo (int) $i; ?></td>
											<td><?php echo (int) $package->ID; ?></td>
											<td class="name-package text-theme"><?php echo esc_html( $package->post_title ); ?></td>
											<td>
												<?php
												echo ! empty($package_types[$package_type])
													? esc_html($package_types[$package_type])
													: '--';
												?>
											</td>

											<td>
												<div class="package-info-wrapper">
													<?php
													// (zadržao sam tvoju logiku, samo sam dodao mali expiry blok na dnu)
													switch ($package_type) {
														case 'job_package':
														default:
															$urgent_jobs   = get_post_meta($package->ID, $prefix.'urgent_jobs', true);
															$feature_jobs  = get_post_meta($package->ID, $prefix.'feature_jobs', true);
															$package_count = get_post_meta($package->ID, $prefix.'package_count', true);
															$job_limit     = get_post_meta($package->ID, $prefix.'job_limit', true);
															$job_duration  = get_post_meta($package->ID, $prefix.'job_duration', true);
															?>
															<ul class="lists-info">
																<li><span class="title-inner"><?php esc_html_e('Urgent:', 'superio'); ?></span><span class="value"><?php echo ($urgent_jobs === 'on') ? esc_html__('Yes','superio') : esc_html__('No','superio'); ?></span></li>
																<li><span class="title-inner"><?php esc_html_e('Featured:', 'superio'); ?></span><span class="value"><?php echo ($feature_jobs === 'on') ? esc_html__('Yes','superio') : esc_html__('No','superio'); ?></span></li>
																<li><span class="title-inner"><?php esc_html_e('Posted:', 'superio'); ?></span><span class="value"><?php echo (int) $package_count; ?></span></li>
																<li><span class="title-inner"><?php esc_html_e('Limit Posts:', 'superio'); ?></span><span class="value"><?php echo (int) $job_limit; ?></span></li>
																<?php if ( $subscription_type !== 'listing' ) : ?>
																	<li><span class="title-inner"><?php esc_html_e('Listing Duration:', 'superio'); ?></span><span class="value"><?php echo (int) $job_duration; ?></span></li>
																<?php endif; ?>
															</ul>
															<?php
														break;
													}

													// DODATA INFORMACIJA: datum isteka / poruka
													if ( $expires_ts ) {
														if ( $is_expired ) {
															echo '<div class="package-expiration expired" style="margin-top:6px;color:#d63638;font-weight:600;">'
																. esc_html__('Paket vam je istekao', 'superio')
																. '</div>';
														} else {
															echo '<div class="package-expiration" style="margin-top:6px;">'
																. '<strong>' . esc_html__('Paket važi do:', 'superio') . '</strong> '
																. esc_html( date_i18n('d.m.Y', $expires_ts) )
																. '</div>';
														}
													}
													?>
												</div>
											</td>

											<td>
												<?php
												// ORIGINALNI VALID CHECK
												$valid = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::package_is_valid($user_id, $package->ID);

												// NOVA LOGIKA STATUSA (prioritet: ISTEKAO -> POTROŠEN -> AKTIVAN)
												if ( $is_expired ) {
													echo '<span class="action expired">'.esc_html__('Istekao', 'superio').'</span>';
												} elseif ( ! $valid ) {
													echo '<span class="action finish">'.esc_html__('Potrošen', 'superio').'</span>';
												} else {
													echo '<span class="action active">'.esc_html__('Aktivan', 'superio').'</span>';
												}
												?>
											</td>
										</tr>
										<?php
										$i++;
									}
									?>
									</tbody>
								</table>
							</div>
						</div>
					<?php else : ?>
						<div class="not-found"><?php esc_html_e('Don\'t have any packages', 'superio'); ?></div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}