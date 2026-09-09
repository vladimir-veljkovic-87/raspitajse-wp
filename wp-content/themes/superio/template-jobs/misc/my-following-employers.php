<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
superio_load_select2();
?>
<div class="widget-following-employers box-dashboard-wrapper">
	<h3 class="widget-title"><?php echo esc_html__('Following Employers','superio') ?></h3>
	<div class="inner-list">
		<div class="search-orderby-wrapper flex-middle-sm">
			<div class="search-following-employer-form search-applicants-form">
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
			<div class="sort-following-employer-form sortby-form">
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
		<?php if ( !empty($employers) && !empty($employers->have_posts()) ) {
			
			while ( $employers->have_posts() ) : $employers->the_post(); global $post;?>
				<div class="following-employer-wrapper">
					<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'employers-styles/inner-list', array('unfollow' => true) ); ?>
				</div>
			<?php endwhile;
			wp_reset_postdata();

			WP_Job_Board_Pro_Mixes::custom_pagination( array(
				'wp_query' => $employers,
				'max_num_pages' => $employers->max_num_pages,
				'prev_text'     => '<i class="ti-arrow-left"></i>',
				'next_text'     => '<i class="ti-arrow-right"></i>',
			));
		?>

		<?php } else { ?>
			<div class="not-found"><?php esc_html_e('No following employer found.', 'superio'); ?></div>
		<?php } ?>
	</div>
</div>