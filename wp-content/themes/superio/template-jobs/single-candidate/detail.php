<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;


$meta_obj = WP_Job_Board_Pro_Candidate_Meta::get_instance($post->ID);

$salary = WP_Job_Board_Pro_Candidate::get_salary_html($post->ID);

$experience_time = superio_candidate_display_meta($post, 'experience_time');
$gender = superio_candidate_display_meta($post, 'gender');
$age = superio_candidate_display_meta($post, 'age');
$qualification = superio_candidate_display_meta($post, 'qualification');
$languages = superio_candidate_display_meta($post, 'languages');


$email = superio_candidate_display_email($post, false, false);
$phone = superio_candidate_display_phone($post, false, true);

$website = superio_candidate_display_meta($post, 'website');
?>
<div class="job-detail-detail in-sidebar">
    <ul class="list">
        <?php if ( $salary ) { ?>
            <li>
                <div class="icon">
                    <i class="flaticon-money-1"></i>
                </div>
                <div class="details">
                    <div class="text"><?php esc_html_e('Offered Salary', 'superio'); ?></div>
                    <div class="value"><?php echo trim($salary); ?></div>
                </div>
            </li>
        <?php } ?>

        <?php if ( $experience_time ) { ?>
            <li>
                <div class="icon">
                    <i class="flaticon-calendar"></i>
                </div>
                <div class="details">
                    <div class="text"><?php echo trim($meta_obj->get_post_meta_title('experience_time')); ?></div>
                    <div class="value"><?php echo trim($experience_time); ?></div>
                </div>
            </li>
        <?php } ?>

        <?php if ( $gender ) { ?>
            <li>
                <div class="icon">
                    <i class="flaticon-user"></i>
                </div>
                <div class="details">
                    <div class="text"><?php echo trim($meta_obj->get_post_meta_title('gender')); ?></div>
                    <div class="value"><?php echo trim($gender); ?></div>
                </div>
            </li>
        <?php } ?>

        <?php if ( $age ) { ?>
            <li>
                <div class="icon">
                    <i class="flaticon-waiting"></i>
                </div>
                <div class="details">
                    <div class="text"><?php echo trim($meta_obj->get_post_meta_title('age')); ?></div>
                    <div class="value"><?php echo trim($age); ?></div>
                </div>
            </li>
        <?php } ?>

        <?php if ( $qualification ) { ?>
            <li>
                <div class="icon">
                    <i class="flaticon-vector"></i>
                </div>
                <div class="details">
                    <div class="text"><?php echo trim($meta_obj->get_post_meta_title('qualification')); ?></div>
                    <div class="value"><?php echo trim($qualification); ?></div>
                </div>
            </li>
        <?php } ?>

        <?php if ( $languages ) { ?>
            <li>
                <div class="icon">
                    <i class="flaticon-translation"></i>
                </div>
                <div class="details">
                    <div class="text"><?php echo trim($meta_obj->get_post_meta_title('languages')); ?></div>
                    <div class="value"><?php echo trim($languages); ?></div>
                </div>
            </li>
        <?php } ?>



        <?php if ( $email ) { ?>
            <li>
                <div class="icon">
                    <i class="flaticon-envelope"></i>
                </div>
                <div class="details">
                    <div class="text"><?php echo trim($meta_obj->get_post_meta_title('email')); ?></div>
                    <div class="value"><?php echo trim($email); ?></div>
                </div>
            </li>
        <?php } ?>

        <?php if ( $phone ) { ?>
            <li>
                <div class="icon">
                    <i class="flaticon-phone"></i>
                </div>
                <div class="details">
                    <div class="text"><?php echo trim($meta_obj->get_post_meta_title('phone')); ?></div>
                    <div class="value"><?php echo trim($phone); ?></div>
                </div>
            </li>
        <?php } ?>

        <?php do_action('wp-job-board-pro-single-candidate-details', $post); ?>

    </ul>

    
</div>