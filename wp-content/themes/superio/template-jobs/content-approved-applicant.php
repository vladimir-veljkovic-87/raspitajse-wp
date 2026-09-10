<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $post;

$job_id = get_post_meta($post->ID, WP_JOB_BOARD_PRO_APPLICANT_PREFIX.'job_id', true);

$candidate_id = get_post_meta( $post->ID, WP_JOB_BOARD_PRO_APPLICANT_PREFIX.'candidate_id', true );
$candidate = get_post($candidate_id);

if ( empty($candidate) ) {
    return;
}

$candidate_url = get_permalink($candidate_id);
$candidate_url = add_query_arg( 'applicant_id', $post->ID, $candidate_url );
$candidate_url = add_query_arg( 'candidate_id', $candidate_id, $candidate_url );
$candidate_url = add_query_arg( 'action', 'view-profile', $candidate_url );

$cv_file_id = get_post_meta( $post->ID, WP_JOB_BOARD_PRO_APPLICANT_PREFIX.'cv_file_id', true );
$download_base_url = WP_Job_Board_Pro_Ajax::get_endpoint('wp_job_board_pro_ajax_download_cv');

$download_urls = [];
if ( $cv_file_id ) {
    if ( is_array($cv_file_id) ) {
        foreach ($cv_file_id as $id) {
            $download_urls[] = add_query_arg(array('file_id' => $id), $download_base_url);
        }
    } else {
        $download_urls[] = add_query_arg(array('file_id' => $cv_file_id), $download_base_url);
    }
    
} else {
    $meta_obj = WP_Job_Board_Pro_Candidate_Meta::get_instance($candidate_id);
    $cv_attachments = $meta_obj->get_post_meta('cv_attachment');
    if ( !empty($cv_attachments) ) {
        foreach ($cv_attachments as $id => $cv_url) {
            $download_urls[] = add_query_arg(array('file_id' => $id), $download_base_url);
        }
    }
}
if ( empty($download_urls) ) {
    $download_base_url = WP_Job_Board_Pro_Ajax::get_endpoint('wp_job_board_pro_ajax_download_resume_cv');
    $download_urls[] = add_query_arg(array('post_id' => $candidate_id), $download_base_url);
}

$rating_avg = WP_Job_Board_Pro_Review::get_ratings_average($candidate_id);

$viewed = get_post_meta( $post->ID, WP_JOB_BOARD_PRO_APPLICANT_PREFIX.'viewed', true );
$classes = $viewed ? 'viewed' : '';
?>

<?php do_action( 'wp_job_board_pro_before_applicant_content', $post->ID ); ?>

<article <?php post_class('applicants-job job-applicant-wrapper clearfix '.$classes); ?>>

    <div class="candidate-list candidate-archive-layout flex-middle-sm">
        <div class="candidate-info">
            <div class="flex-middle">
                <?php if ( has_post_thumbnail($candidate_id) ) { ?>
                    <div class="candidate-logo">
                        <a href="<?php echo esc_url( $candidate_url ); ?>" rel="bookmark">
                            <?php echo get_the_post_thumbnail( $candidate_id, 'thumbnail' ); ?>
                        </a>
                    </div>
                <?php } ?>
                <div class="candidate-info-content">
                    <div class="title-wrapper flex-middle-sm">
                        <h2 class="candidate-title">
                            <a href="<?php echo esc_url( $candidate_url ); ?>" rel="bookmark"><?php the_title(); ?></a>
                        </h2>

                        <?php
                            $app_status = WP_Job_Board_Pro_Applicant::get_post_meta($post->ID, 'app_status', true);
                            if ( $app_status == 'approved' ) {
                                echo '<span class="label label-success approved">'.esc_html__('Approved', 'superio').'</span>';
                            } elseif ( $app_status == 'rejected' ) {
                                echo '<span class="label label-default rejected">'.esc_html__('Rejected', 'superio').'</span>';
                            } else {
                                echo '<span class="label label-info pending">'.esc_html__('Pending', 'superio').'</span>';
                            }
                        ?>
                    </div>
                    <div class="job-metas">
                        <h4 class="job-title">
                            <a href="<?php echo get_permalink($job_id); ?>" class="text-theme"><?php echo get_the_title($job_id); ?></a>
                        </h4>
                        <?php superio_candidate_display_short_location($candidate, 'icon'); ?>
                        <?php superio_candidate_display_salary($candidate, 'icon'); ?>
                        <div class="date">
                            <?php esc_html_e('Applied date : ', 'superio'); ?>
                            <?php the_time( get_option('date_format', 'd M, Y') ); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="ali-right">
            <div class="applicant-action-button action-button">
                
                <?php if ( version_compare(WP_JOB_BOARD_PRO_PLUGIN_VERSION, '1.2.0', '>=') ) { ?>
                    <a data-toggle="tooltip" href="#job-apply-create-meeting-form-wrapper-<?php echo esc_attr($post->ID); ?>-<?php echo esc_attr($candidate_id); ?>" class="btn-create-meeting-job-applied btn-action-icon" title="<?php echo esc_attr_e('Create Meeting', 'superio'); ?>"><i class="ti-plus"></i></a>
                    <?php echo WP_Job_Board_Pro_Template_Loader::get_template_part('misc/meeting-form'); ?>
                <?php } ?>
                
                <a data-toggle="tooltip" href="javascript:void(0);" class="btn-undo-approve-job-applied btn-action-icon approve" data-applicant_id="<?php echo esc_attr($post->ID); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce( 'wp-job-board-pro-undo-approve-applied-nonce' )); ?>" title="<?php esc_html_e('Undo Approved', 'superio'); ?>"><i class="fa fa-undo"></i></a>

                <?php
                if ( $download_urls ) {
                    foreach ($download_urls as $url) {
                        ?>
                        <a data-toggle="tooltip" href="<?php echo esc_url($url); ?>" title="<?php esc_attr_e('Download CV', 'superio'); ?>" class="btn-action-icon download"><i class="ti-download"></i></a>
                        <?php
                    }
                } ?>

                <a data-toggle="tooltip" title="<?php esc_attr_e('Remove', 'superio'); ?>" href="javascript:void(0);" class="btn-action-icon btn-remove-job-applied remove" data-applicant_id="<?php echo esc_attr($post->ID); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce( 'wp-job-board-pro-remove-applied-nonce' )); ?>"><i class="ti-close"></i></a>
            </div>
        </div> 
    </div>
    
</article><!-- #post-## -->

<?php do_action( 'wp_job_board_pro_after_applicant_content', $post->ID );