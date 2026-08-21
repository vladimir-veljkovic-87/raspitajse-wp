<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
extract( $args );

global $post, $job_preview;
if ( $job_preview ) {
    $post = $job_preview;
}
if ( empty($post->post_type) || $post->post_type != 'job_listing' ) {
    return;
}
extract( $args );
extract( $instance );

echo trim($before_widget);
$title = apply_filters('widget_title', $instance['title']);

$job_ids = WP_Job_Board_Pro_Job_Listing::customer_also_viewed($post->ID);
if ( empty($job_ids) || sizeof( $job_ids ) == 0 || !is_array($job_ids) ) {
	return;
}

$args = array(
	'post_type'            => 'job_listing',
	'ignore_sticky_posts'  => 1,
	'no_found_rows'        => 1,
	'posts_per_page'       => $limit,
	'post__in'             => $job_ids,
	'orderby' => 'ID(ID, explode('.implode(',', $job_ids).'))'
);

$jobs = new WP_Query( $args );

if ( $jobs->have_posts() ) : ?>

	<div class="job-detail-also-viewed">
		<?php
		if ( $title ) {
		    echo trim($before_title)  . trim( $title ) . $after_title;
		}
		?>
		<div class="content-inner">
			<?php
	            while ( $jobs->have_posts() ) : $jobs->the_post();
	            	global $post;
	            	
	            	$author_id = superio_get_post_author($post->ID);
					$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($author_id);
					$address = get_post_meta( $post->ID, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'address', true );
	            ?>
	                <div class="item">
					    <div class="job-information">
							<?php the_title( sprintf( '<h2 class="job-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
							<?php if ( $employer_id ) { ?>
						        <div class="job-date-author">
						        	<a class="text-theme" href="<?php echo esc_url(get_permalink($employer_id)); ?>"><?php echo trim(get_the_title($employer_id)); ?></a>
						        </div>
						    <?php } ?>

				            <?php if ( $address ) { ?>
				                <div class="job-location"><i class="flaticon-location-pin"></i><?php echo trim($address); ?></div>
				            <?php } ?>
						</div>
					</div><!-- #post-## -->
	                <?php
	            endwhile;
	        ?>
        </div>
	</div>
<?php
	wp_reset_postdata();
endif;

echo trim($after_widget);