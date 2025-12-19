<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
wp_enqueue_script('wpjbp-select2');
wp_enqueue_style('wpjbp-select2');
?>

<div class="search-orderby-wrapper">
	<div class="search-candidates-shortlist-form">
		<form action="" method="get">
			<div class="form-group">
				<input type="text" name="search" value="<?php echo esc_attr(isset($_GET['search']) ? $_GET['search'] : ''); ?>">
			</div>
			<div class="submit-wrapper">
				<button class="search-submit" name="submit">
					<?php esc_html_e( 'Search ...', 'wp-job-board-pro' ); ?>
				</button>
			</div>
			<input type="hidden" name="paged" value="1" />
		</form>
	</div>
	<div class="sort-candidates-shortlist-form sortby-form">
		<?php
			$orderby_options = apply_filters( 'wp_job_board_pro_my_jobs_orderby', array(
				'menu_order'	=> esc_html__( 'Default', 'wp-job-board-pro' ),
				'newest' 		=> esc_html__( 'Newest', 'wp-job-board-pro' ),
				'oldest'     	=> esc_html__( 'Oldest', 'wp-job-board-pro' ),
			) );

			$orderby = isset( $_GET['orderby'] ) ? wp_unslash( $_GET['orderby'] ) : 'newest'; 
		?>

		<div class="orderby-wrapper">
			<span>
				<?php echo esc_html__('Sort by: ','wp-job-board-pro'); ?>
			</span>
			<form class="candidates-shortlist-ordering" method="get">
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
if ( !empty($candidate_ids) && is_array($candidate_ids) ) {
	if ( get_query_var( 'paged' ) ) {
	    $paged = get_query_var( 'paged' );
	} elseif ( get_query_var( 'page' ) ) {
	    $paged = get_query_var( 'page' );
	} else {
	    $paged = 1;
	}
	$query_vars = array(
		'post_type'         => 'candidate',
		'posts_per_page'    => get_option('posts_per_page'),
		'paged'    			=> $paged,
		'post_status'       => 'publish',
		'post__in'       	=> $candidate_ids,
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

	$candidates = new WP_Query($query_vars);
	if ( $candidates->have_posts() ) {
		while ( $candidates->have_posts() ) : $candidates->the_post();
			global $post;

			$categories = get_the_terms( $post->ID, 'candidate_category' );
			$address = get_post_meta( $post->ID, WP_JOB_BOARD_PRO_CANDIDATE_PREFIX . 'address', true );
			$salary = WP_Job_Board_Pro_Candidate::get_salary_html($post->ID);

			?>

			<?php do_action( 'wp_job_board_pro_before_candidate_content', $post->ID ); ?>

			<article <?php post_class('candidate-shortlist-wrapper'); ?>>

				<?php if ( has_post_thumbnail() ) { ?>
			        <div class="candidate-thumbnail">
			            <?php echo get_the_post_thumbnail( $post->ID, 'thumbnail' ); ?>
			        </div>
			    <?php } ?>
			    <div class="candidate-information">
			    	
					<?php the_title( sprintf( '<h2 class="entry-title candidate-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>

			        <?php if ( $categories ) { ?>
			            <?php foreach ($categories as $term) { ?>
			                <a href="<?php echo get_term_link($term); ?>"><?php echo $term->name; ?></a>
			            <?php } ?>
			        <?php } ?>
			        <!-- rating -->

			        <?php if ( $address ) { ?>
			            <div class="candidate-location">
			                <?php esc_html_e('Location', 'wp-job-board-pro'); ?>
			                <?php echo $address; ?>
			            </div>
			        <?php } ?>

			        <?php if ( $salary ) { ?>
			            <div class="candidate-salary">
			                <?php esc_html_e('Salary', 'wp-job-board-pro'); ?>
			                <?php echo $salary; ?>
			            </div>
			        <?php } ?>
				</div>

			    <a href="<?php the_permalink(); ?>" class="btn button"><?php esc_html_e('View Profile', 'wp-job-board-pro'); ?></a>

			    <a href="javascript:void(0)" class="btn-remove-candidate-shortlist" data-candidate_id="<?php echo esc_attr($post->ID); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce( 'wp-job-board-pro-remove-candidate-shortlist-nonce' )); ?>"><?php esc_html_e('Remove', 'wp-job-board-pro'); ?></a>
			</article><!-- #post-## -->

			<?php do_action( 'wp_job_board_pro_after_candidate_content', $post->ID );
		endwhile;
		
		WP_Job_Board_Pro_Mixes::custom_pagination( array(
			'max_num_pages' => $candidates->max_num_pages,
			'prev_text'     => esc_html__( 'Previous page', 'wp-job-board-pro' ),
			'next_text'     => esc_html__( 'Next page', 'wp-job-board-pro' ),
			'wp_query' => $candidates
		));

		wp_reset_postdata();
	}
?>

<?php } else { ?>
	<div class="not-found"><?php esc_html_e('Don\'t have any candidates in shortlist', 'wp-job-board-pro'); ?></div>
<?php } ?>