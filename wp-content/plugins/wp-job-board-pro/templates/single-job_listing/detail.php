<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

$salary = WP_Job_Board_Pro_Job_Listing::get_salary_html($post->ID);
?>
<div class="job-detail-detail">
    <ul class="list">
        <?php if ( $salary ) { ?>
            <li>
                <div class="icon">
                    <i class="flaticon-money"></i>
                </div>
                <div class="details">
                    <div class="text"><?php esc_html_e('Ponuđena plata', 'wp-job-board-pro'); ?></div>
                    <div class="value"><?php echo wp_kses_post($salary); ?></div>
                </div>
            </li>
        <?php } ?>
    </ul>
</div>