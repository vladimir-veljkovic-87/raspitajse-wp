<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

$categories = get_the_terms( $post->ID, 'candidate_category' );
$address = get_post_meta( $post->ID, WP_JOB_BOARD_PRO_CANDIDATE_PREFIX . 'address', true );
$salary = WP_Job_Board_Pro_Candidate::get_salary_html($post->ID);

?>

<?php do_action( 'wp_job_board_pro_before_candidate_content', $post->ID ); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

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
</article><!-- #post-## -->

<?php do_action( 'wp_job_board_pro_after_candidate_content', $post->ID ); ?>