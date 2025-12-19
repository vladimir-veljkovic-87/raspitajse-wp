<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

$author_id = WP_Job_Board_Pro_Job_Listing::get_author_id($post->ID);
$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($author_id);
$types = get_the_terms( $post->ID, 'job_listing_type' );
$address = get_post_meta( $post->ID, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'address', true );
$salary = WP_Job_Board_Pro_Job_Listing::get_salary_html($post->ID);

?>

<?php do_action( 'wp_job_board_pro_before_job_content', $post->ID ); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php if ( has_post_thumbnail($employer_id) ) { ?>
        <div class="employer-thumbnail">
            <?php echo get_the_post_thumbnail( $employer_id, 'thumbnail' ); ?>
        </div>
    <?php } ?>
    <div class="job-information">
    	<?php if ( $types ) { ?>
            <?php foreach ($types as $term) { ?>
                <a href="<?php echo get_term_link($term); ?>"><?php echo $term->name; ?></a>
            <?php } ?>
        <?php } ?>

		<?php the_title( sprintf( '<h2 class="entry-title job-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>

        <div class="job-date-author">
            <?php echo sprintf( __(' posted %s ago', 'wp-job-board-pro'), human_time_diff(get_the_time('U'), current_time('timestamp')) ); ?> 
            <?php
            if ( $employer_id ) {
                echo sprintf( __('by %s', 'wp-job-board-pro'), get_the_title($employer_id) );
            }
            ?>
        </div>
        <div class="job-metas">
            <?php if ( $address ) { ?>
                <div class="job-location"><?php echo $address; ?></div>
            <?php } ?>
            <?php if ( $salary ) { ?>
                <div class="job-salary"><?php echo $salary; ?></div>
            <?php } ?>
        </div>

	</div>
</article><!-- #post-## -->

<?php do_action( 'wp_job_board_pro_after_job_content', $post->ID ); ?>