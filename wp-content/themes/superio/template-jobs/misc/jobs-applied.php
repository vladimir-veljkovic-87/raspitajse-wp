<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
superio_load_select2();
?>
<div class="box-dashboard-wrapper">
	<h3 class="title"><?php echo esc_html__('Applied Jobs','superio') ?></h3>
	<div class="inner-list">
		<div class="search-orderby-wrapper flex-middle-sm">
			<div class="search-jobs-applied-form search-applicants-form">
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
			<div class="sort-jobs-applied-form sortby-form">
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
		<?php if ( !empty($applicants) && !empty($applicants->posts) ) { ?>
			<div class="table-responsive">
			<table class="job-table">
				<thead>
					<tr>
						<th class="job-title"><?php esc_html_e('Job Title', 'superio'); ?></th>
						<th class="job-date"><?php esc_html_e('Date Applied', 'superio'); ?></th>
						<th class="job-status"><?php esc_html_e('Status', 'superio'); ?></th>
						<th class="job-actions"><?php esc_html_e('Actions', 'superio'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($applicants->posts as $applicant_id) {
						$job_id = get_post_meta($applicant_id, WP_JOB_BOARD_PRO_APPLICANT_PREFIX.'job_id', true);
						$post = get_post($job_id);

						if ( empty($post) ) {
							continue;
						}

						$author_id = superio_get_post_author($post->ID);
						$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($author_id);
						$types = get_the_terms( $post->ID, 'job_listing_type' );
						$category = get_the_terms( $post->ID, 'job_listing_category' );
						$location = get_the_terms( $post->ID, 'job_listing_location' );
						$salary = WP_Job_Board_Pro_Job_Listing::get_salary_html($post->ID);

						?>

						<tr class="job-applied-wrapper">
							<td>
								<div class="job-applied">
									<?php if ( has_post_thumbnail($employer_id) ) { ?>
								        <div class="employer-logo">
								            <?php echo get_the_post_thumbnail( $employer_id, 'thumbnail' ); ?>
								        </div>
								    <?php } ?>
								    <div class="job-content">
								    	<div class="title-wrapper">
									        <h2 class="job-title">
									        	<a href="<?php echo esc_url(get_permalink($job_id)); ?>" rel="bookmark"><?php echo get_the_title($job_id); ?></a>
									        </h2>
					                    </div>
					                     <div class="job-metas">
								        	<?php if ( $category ) { ?>
								        		<div class="category-job">
								        			<i class="flaticon-briefcase-1"></i>
										            <?php foreach ($category as $term) { ?>
										                <a href="<?php echo get_term_link($term); ?>"><?php echo trim($term->name); ?></a>
										                <?php break; ?>
										            <?php } ?>
									            </div>
									        <?php } ?>
								            <?php if ( $location ) { ?>
								        		<div class="location-job">
								        			<i class="flaticon-location"></i>
										            <?php foreach ($location as $term) { ?>
										                <a href="<?php echo get_term_link($term); ?>"><?php echo trim($term->name); ?></a>
										                <?php break; ?>
										            <?php } ?>
									            </div>
									        <?php } ?>
								        </div>
								    </div>
							    </div>
							</td>
							<td>
								<?php echo get_the_time(get_option('date_format'), $applicant_id); ?>
							</td>
							<td>
								<?php
			                        $app_status = WP_Job_Board_Pro_Applicant::get_post_meta($applicant_id, 'app_status', true);

			                        if ( $app_status == 'rejected' ) {
										echo '<span class="application-status-label rejected">'.esc_html__('Rejected', 'superio').'</span>';
									} elseif ( $app_status == 'approved' ) {
										echo '<span class="application-status-label approved">'.esc_html__('Approved', 'superio').'</span>';
									} else {
										echo '<span class="application-status-label pending">'.esc_html__('Pending', 'superio').'</span>';
									}
			                    ?>
							</td>
							<td>
								<div class="action-button">
									<a href="javascript:void(0)" class="btn-remove-job-applied btn-action-icon deleted" data-applicant_id="<?php echo esc_attr($applicant_id); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce( 'wp-job-board-pro-remove-applied-nonce' )); ?>" data-toggle="tooltip" title="<?php esc_attr_e('Remove', 'superio'); ?>"><i class="ti-close"></i></a>
									<a class="btn-action-icon" href="<?php echo esc_url(get_permalink($job_id)); ?>" data-toggle="tooltip" title="<?php esc_attr_e('View Job', 'superio'); ?>"><i class="ti-eye"></i></a>
								</div>
							</td>
						</tr>

						<?php
					} ?>
				</tbody>
			</table>
			</div>

			<?php WP_Job_Board_Pro_Mixes::custom_pagination( array(
				'wp_query' => $applicants,
				'max_num_pages' => $applicants->max_num_pages,
				'prev_text'     => '<i class="ti-arrow-left"></i>',
				'next_text'     => '<i class="ti-arrow-right"></i>',
			));
		?>

		<?php } else { ?>
			<div class="not-found"><?php esc_html_e('No application found.', 'superio'); ?></div>
		<?php } ?>
	</div>
</div>