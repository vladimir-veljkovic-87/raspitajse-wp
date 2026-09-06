<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

$candidate_id = get_post_meta( $post->ID, WP_JOB_BOARD_PRO_APPLICANT_PREFIX.'candidate_id', true );

$candidate_url = get_permalink($candidate_id);
$candidate_url = add_query_arg( 'applicant_id', $post->ID, $candidate_url );
$candidate_url = add_query_arg( 'candidate_id', $candidate_id, $candidate_url );
$candidate_url = add_query_arg( 'action', 'view-profile', $candidate_url );

$viewed = get_post_meta( $post->ID, WP_JOB_BOARD_PRO_APPLICANT_PREFIX.'viewed', true );
$classes = $viewed ? 'viewed' : '';
?>

<?php do_action( 'wp_job_board_pro_before_applicant_content', $post->ID ); ?>

<article <?php post_class('job-applicant-wrapper '.$classes); ?>>

    <?php if ( has_post_thumbnail($candidate_id) ) { ?>
        <div class="applicant-thumbnail">
            <a href="<?php echo esc_url( $candidate_url ); ?>" rel="bookmark">
                <?php echo get_the_post_thumbnail( $candidate_id, 'thumbnail' ); ?>
            </a>
        </div>
    <?php } ?>
    <div class="applicant-information">
        <h2 class="entry-title applicant-title">
            <a href="<?php echo esc_url( $candidate_url ); ?>" rel="bookmark"><?php the_title(); ?></a>
        </h2>

        <div class="applicant-date">
            <?php the_time( get_option('date_format', 'd M, Y') ); ?>
        </div>

        <a href="<?php echo esc_url( $candidate_url ); ?>" rel="bookmark"><?php esc_html_e('View Profile', 'wp-job-board-pro'); ?></a>

        <div class="applicant-action-button">
            <a href="javascript:void(0);" class="btn-actions"><?php esc_html_e('Actions', 'wp-job-board-pro'); ?></a>
            <div class="all-actions-wrapper">
                
                <?php WP_Job_Board_Pro_Candidate::display_shortlist_link($candidate_id); ?>
                
                <a href="javascript:void(0);" class="btn-undo-reject-job-applied" data-applicant_id="<?php echo esc_attr($post->ID); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce( 'wp-job-board-pro-undo-reject-applied-nonce' )); ?>"><?php esc_html_e('Undo Rejected', 'wp-job-board-pro'); ?></a>

                <a href="javascript:void(0);" class="btn-remove-job-applied" data-applicant_id="<?php echo esc_attr($post->ID); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce( 'wp-job-board-pro-remove-applied-nonce' )); ?>"><?php esc_html_e('Remove', 'wp-job-board-pro'); ?></a>
            </div>
        </div>
    </div>
</article><!-- #post-## -->

<?php do_action( 'wp_job_board_pro_after_applicant_content', $post->ID );