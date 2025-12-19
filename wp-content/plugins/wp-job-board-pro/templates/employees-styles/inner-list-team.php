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
        <div class="employee-information">
        	<h2 class="entry-title employee-title">
                <?php echo $userdata->display_name; ?>
            </h2>
    	</div>

        <a href="javascript:void(0);" class="btn btn-employer-remove-employee" data-employee_id="<?php echo esc_attr($userdata->ID); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce( 'wp-job-board-pro-employer-remove-employee-nonce' )); ?>"><?php esc_html_e('Remove', 'wp-job-board-pro'); ?></a>
    </div>
</article><!-- #post-## -->

<?php do_action( 'wp_job_board_pro_after_employee_content', $userdata ); ?>