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

$cv_file_id = get_post_meta( $post->ID, WP_JOB_BOARD_PRO_APPLICANT_PREFIX.'cv_file_id', true );

$download_base_url = WP_Job_Board_Pro_Ajax::get_endpoint('wp_job_board_pro_ajax_download_cv');
if ( $cv_file_id ) {
    $download_url = add_query_arg(array('file_id' => $cv_file_id), $download_base_url);
} else {
    $cv_attachments = $meta_obj->get_post_meta('cv_attachment');
    if ( !empty($cv_attachments) ) {
        foreach ($cv_attachments as $id => $cv_url) {
            $download_url = add_query_arg(array('file_id' => $id), $download_base_url);
            break;
        }
    }
}
if ( !empty($download_url) ) {
    $download_base_url = WP_Job_Board_Pro_Ajax::get_endpoint('wp_job_board_pro_ajax_download_resume_cv');
    $download_url = add_query_arg(array('post_id' => $candidate_id), $download_base_url);
}




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

        <a href="<?php echo esc_url( $candidate_url ); ?>" rel="bookmark"><?php esc_html_e('Pregledaj Profil', 'wp-job-board-pro'); ?></a>

        <div class="applicant-action-button">
            <a href="javascript:void(0);" class="btn-actions"><?php esc_html_e('Akcije', 'wp-job-board-pro'); ?></a>
            <div class="all-actions-wrapper">
                <?php
                if ( $download_url ) {
                ?>
                    <a href="<?php echo esc_url($download_url); ?>"><?php esc_html_e('Preuzimanje CV-a', 'wp-job-board-pro'); ?></a>
                <?php } ?>

                <?php WP_Job_Board_Pro_Candidate::display_shortlist_link($candidate_id); ?>

                <?php
                    $app_status = WP_Job_Board_Pro_Applicant::get_post_meta($post->ID, 'app_status', true);
                    if ( $app_status == 'rejected' ) {
                        $text = esc_html__('Odbijen', 'wp-job-board-pro');
                    } else {
                        $text = esc_html__('Odbij', 'wp-job-board-pro');
                    }
                ?>
                <a href="javascript:void(0);" class="btn-reject-job-applied" data-applicant_id="<?php echo esc_attr($post->ID); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce( 'wp-job-board-pro-reject-applied-nonce' )); ?>"><?php echo wp_kses_post($text); ?></a>

                <a href="javascript:void(0);" class="btn-remove-job-applied" data-applicant_id="<?php echo esc_attr($post->ID); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce( 'wp-job-board-pro-remove-applied-nonce' )); ?>"><?php esc_html_e('Ukloni', 'wp-job-board-pro'); ?></a>
            </div>
        </div>
    </div>
</article><!-- #post-## -->

<?php do_action( 'wp_job_board_pro_after_applicant_content', $post->ID );