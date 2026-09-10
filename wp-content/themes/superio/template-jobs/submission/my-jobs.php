<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
superio_load_select2();

$my_jobs_page_id = wp_job_board_pro_get_option('my_jobs_page_id');
$my_jobs_url = get_permalink( $my_jobs_page_id );

?>

<div class="box-dashboard-wrapper my-job-employer">
	<h3 class="widget-title"><?php echo esc_html__('Manage Jobs','superio') ?></h3>
	<div class="inner-list">
		<div class="search-orderby-wrapper flex-middle-sm">
			<div class="search-my-jobs-form search-applicants-form">
				<form action="" method="get">
					<div class="flex-middle">

						<button class="search-submit btn btn-sm btn-search" name="submit">
							<i class="flaticon-magnifiying-glass"></i>
						</button>
						<input type="text" placeholder="<?php esc_attr_e( 'Search ...', 'superio' ); ?>" class="form-control" name="search" value="<?php echo esc_attr(isset($_GET['search']) ? $_GET['search'] : ''); ?>">

					</div>
					<input type="hidden" name="paged" value="1" />
				</form>
			</div>
			<div class="sort-my-jobs-form sortby-form">
				<?php
					$orderby_options = apply_filters( 'wp_job_board_pro_my_jobs_orderby', array(
						'menu_order'	=> esc_html__( 'Default', 'superio' ),
						'newest' 		=> esc_html__( 'Newest', 'superio' ),
						'oldest'     	=> esc_html__( 'Oldest', 'superio' ),
					) );

					$orderby = isset( $_GET['orderby'] ) ? wp_unslash( $_GET['orderby'] ) : 'newest'; 
				?>

				<div class="orderby-wrapper flex-middle">
					<span class="text-sort">
						<?php echo esc_html__('Sort by: ','superio'); ?>
					</span>
					<form class="my-jobs-ordering" method="get">
						<select name="orderby" class="orderby">
							<?php foreach ( $orderby_options as $id => $name ) : ?>
								<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $orderby, $id ); ?>><?php echo esc_html( $name ); ?></option>
							<?php endforeach; ?>
						</select>
						<input type="hidden" name="paged" value="1" />
						<?php WP_Job_Board_Pro_Mixes::query_string_form_fields( null, array( 'orderby', 'submit', 'paged' ) ); ?>
					</form>
				</div>
			</div>
		</div>

		<?php
			$user_id = WP_Job_Board_Pro_User::get_user_id();
			$paged = (get_query_var( 'paged' )) ? get_query_var( 'paged' ) : 1;
			$query_vars = array(
				'post_type'     => 'job_listing',
				'post_status'   => apply_filters('wp-job-board-pro-my-jobs-post-statuses', array( 'publish', 'expired', 'pending', 'pending_payment', 'pending_approve', 'draft', 'preview' )),
				'paged'         => $paged,
				'author'        => $user_id,
				'orderby'		=> 'date',
				'order'			=> 'DESC',
			);
			if ( isset($_GET['search']) ) {
				$query_vars['s'] = $_GET['search'];
			}
			if ( isset($_GET['orderby']) ) {
				switch ($_GET['orderby']) {
					case 'menu_order':
						$query_vars['orderby'] = array(
							'menu_order' => 'ASC',
							'date'       => 'DESC',
							'ID'         => 'DESC',
						);
						break;
					case 'newest':
						$query_vars['orderby'] = 'date';
						$query_vars['order'] = 'DESC';
						break;
					case 'oldest':
						$query_vars['orderby'] = 'date';
						$query_vars['order'] = 'ASC';
						break;
				}
			}
			$jobs = new WP_Query($query_vars);
			
			if ( $jobs->have_posts() ) :
			?>
			<div class="table-responsive">
				<table class="job-table">
					<thead>
						<tr>
							<th class="job-title"><?php esc_html_e('Title', 'superio'); ?></th>
							<th class="job-applicants"><?php esc_html_e('Applicants', 'superio'); ?></th>
							<th class="job-date"><?php esc_html_e('Created & Expired', 'superio'); ?></th>
							<th class="job-status"><?php esc_html_e('Status', 'superio'); ?></th>
							<th class="job-actions"><?php esc_html_e('Actions', 'superio'); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php while ( $jobs->have_posts() ) : $jobs->the_post(); global $post;
						$filled = WP_Job_Board_Pro_Job_Listing::get_post_meta($post->ID, 'filled');
					?>
						<tr>
							<td class="job-table-info">
								
								<div class="job-table-info-content">
									<div class="title-wrapper">
										<h3 class="job-table-info-content-title">
											<?php if ( $post->post_status == 'publish' ) { ?>
												<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
											<?php } else { ?>
												<?php the_title(); ?>
											<?php } ?>

										</h3>
										<?php if (!has_post_thumbnail( $post->ID ) ): ?>
											<?php superio_job_display_featured_icon($post); ?>
										<?php endif; ?>
										<?php superio_job_display_urgent_icon($post,'icon'); ?>
										<?php if ( $filled ) { ?>
											<span class="application-status-label success"><?php esc_html_e('Filled', 'superio'); ?></span>
										<?php } ?>
									</div>
									<div class="job-metas">
										<?php superio_job_display_short_location($post, 'icon'); ?>
									</div>
									
								</div>
							</td>

							<td class="job-table-applicants text-theme nowrap">
								<?php
									$count_applicants = WP_Job_Board_Pro_Job_Listing::count_applicants($post->ID);
									echo '<span class="number">'.$count_applicants.'</span> ';
									esc_html_e('Applicant(s)', 'superio');
								?>
							</td>

							<td>
								<div class="job-table-info-content-date-expiry">
									<div class="created">
										<strong><?php esc_html_e('Created: ', 'superio'); ?></strong><?php the_time( get_option('date_format') ); ?>
									</div>
									<div class="expiry-date">
										<strong><?php esc_html_e('Expiry date: ', 'superio'); ?></strong>
										<span class="text-danger">
										<?php
											$expires = get_post_meta( $post->ID, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX.'expiry_date', true);
											if ( $expires ) {
												echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $expires ) ) );
											} else {
												echo '--';
											}
										?>
										</span>
									</div>
								</div>
							</td>

							<td class="job-table-status nowrap">
								<div class="job-table-actions-inner <?php echo esc_attr($post->post_status); ?>">
									<?php
										$post_status = get_post_status_object( $post->post_status );
										if ( !empty($post_status->label) ) {
											echo esc_html($post_status->label);
										} else {
											echo esc_html($post_status->post_status);
										}
									?>
								</div>
							</td>

							<td class="job-table-actions nowrap">
								<div class="action-button">
									<?php
									$my_jobs_url = add_query_arg( 'job_id', $post->ID, remove_query_arg( 'job_id', $my_jobs_url ) );
									switch ( $post->post_status ) {
										case 'publish' :
											$edit_url = add_query_arg( 'action', 'edit', remove_query_arg( 'action', $my_jobs_url ) );
											
											
											if ( $filled ) {
												$classes = 'mark_not_filled';
												$title = esc_html__('Mark not filled', 'superio');
												$nonce = wp_create_nonce( 'wp-job-board-pro-mark-not-filled-nonce' );
												$icon_class = 'fa fa-lock';
											} else {
												$classes = 'mark_filled';
												$title = esc_html__('Mark filled', 'superio');
												$nonce = wp_create_nonce( 'wp-job-board-pro-mark-filled-nonce' );
												$icon_class = 'fa fa-unlock';
											}
											?>
											<a data-toggle="tooltip" class="fill-btn btn-action-icon <?php echo esc_attr($classes); ?>" href="javascript:void(0);" title="<?php echo esc_attr($title); ?>" data-job_id="<?php echo esc_attr($post->ID); ?>" data-nonce="<?php echo esc_attr($nonce); ?>"><i class="<?php echo esc_attr($icon_class); ?>"></i></a>

											<?php if ( wp_job_board_pro_get_option('user_edit_published_submission') !== 'no' ) { ?>
												<a data-toggle="tooltip" class="edit-btn btn-action-icon edit job-table-action" href="<?php echo esc_url($edit_url); ?>" title="<?php esc_attr_e('Edit', 'superio'); ?>">
													<i class="ti-pencil-alt"></i>
												</a>
												<?php
											}
											break;
										case 'expired' :
											$relist_url = add_query_arg( 'action', 'relist', remove_query_arg( 'action', $my_jobs_url ) );
											?>
											<a data-toggle="tooltip" href="<?php echo esc_url($relist_url); ?>" class="btn-action-icon edit job-table-action" title="<?php esc_attr_e('Relist', 'superio'); ?>">
												<i class="fa fa-registered"></i>
											</a>
											<?php
											break;
										case 'pending_payment':
											$order_id = get_post_meta($post->ID, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX.'order_id', true);
											if ( $order_id ) {
												if ( wp_job_board_pro_get_option('user_edit_published_submission') !== 'no' ) {
												$edit_url = add_query_arg( 'action', 'edit', remove_query_arg( 'action', $my_jobs_url ) );
													?>
													<a data-toggle="tooltip" class="edit-btn btn-action-icon edit job-table-action" href="<?php echo esc_url($edit_url); ?>" title="<?php esc_attr_e('Edit', 'superio'); ?>">
														<i class="ti-pencil-alt"></i>
													</a>
													<?php
												}
											} else {
												$continue_url = add_query_arg( 'action', 'continue', remove_query_arg( 'action', $my_jobs_url ) );
												?>
												<a data-toggle="tooltip" href="<?php echo esc_url($continue_url); ?>" class="edit-btn btn-action-icon edit job-table-action" title="<?php esc_attr_e('Continue', 'superio'); ?>">
													<i class="ti-arrow-right"></i>
												</a>
												<?php
											}
										break;
										case 'pending_approve':
										case 'pending' :
											if ( wp_job_board_pro_get_option('user_edit_published_submission') !== 'no' ) {
												$edit_url = add_query_arg( 'action', 'edit', remove_query_arg( 'action', $my_jobs_url ) );
												?>
												<a data-toggle="tooltip" class="edit-btn btn-action-icon edit job-table-action" href="<?php echo esc_url($edit_url); ?>" title="<?php esc_attr_e('Edit', 'superio'); ?>">
													<i class="ti-pencil-alt"></i>
												</a>
												<?php
											}
										break;
										case 'draft' :
										case 'preview' :
											$continue_url = add_query_arg( 'action', 'continue', remove_query_arg( 'action', $my_jobs_url ) );
											?>
											<a data-toggle="tooltip" href="<?php echo esc_url($continue_url); ?>" class="edit-btn btn-action-icon edit job-table-action" title="<?php esc_attr_e('Continue', 'superio'); ?>">
												<i class="ti-arrow-right"></i>
											</a>
											<?php
											break;
									}
									?>


									<a data-toggle="tooltip" class="remove-btn btn-action-icon deleted job-table-action job-button-delete" href="javascript:void(0)" data-job_id="<?php echo esc_attr($post->ID); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce( 'wp-job-board-pro-delete-job-nonce' )); ?>" title="<?php esc_attr_e('Remove', 'superio'); ?>">
										<i class="ti-close"></i>
									</a>
								</div>
							</td>
						</tr>
					<?php endwhile; ?>
					</tbody>
				</table>
			</div>
			<?php
				WP_Job_Board_Pro_Mixes::custom_pagination( array(
					'wp_query' => $jobs,
					'max_num_pages' => $jobs->max_num_pages,
					'prev_text'     => '<i class="ti-arrow-left"></i>',
					'next_text'     => '<i class="ti-arrow-right"></i>',
				));

				wp_reset_postdata();
			?>
		<?php else : ?>
			<div class="alert alert-warning">
				<p><?php esc_html_e( 'You don\'t have any jobs, yet. Start by creating new one.', 'superio' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</div>