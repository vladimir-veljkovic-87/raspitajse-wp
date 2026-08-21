<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty($userdata) ) {
    return;
}
?>

<?php do_action( 'wp_job_board_pro_before_employee_content', $userdata ); ?>

<article class="employee-team-wrapper">
    <div class="employee-team">
        <div class="employee-thumbnail">
            <?php echo get_avatar( $userdata->ID, 'thumbnail' ); ?>
        </div>
        <div class="employee-information flex-middle">
        	<h2 class="entry-title employee-title">
                <?php echo esc_html($userdata->display_name); ?>
            </h2>
            <div class="ali-right">
                <a href="javascript:void(0);" class="btn-employer-remove-employee deleted btn-action-icon" data-employee_id="<?php echo esc_attr($userdata->ID); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce( 'wp-job-board-pro-employer-remove-employee-nonce' )); ?>"><i class="ti-close"></i></a>
            </div>
        </div>
    </div>
</article><!-- #post-## -->

<?php do_action( 'wp_job_board_pro_after_employee_content', $userdata ); ?>