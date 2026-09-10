<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
extract( $args );

global $post;
if ( empty($post->post_type) || $post->post_type != 'candidate' ) {
    return;
}

$user_id = WP_Job_Board_Pro_User::get_user_by_candidate_id($post->ID);
$author_email = WP_Job_Board_Pro_Candidate::get_display_email( $post );

extract( $args );
extract( $instance );

echo trim($before_widget);
$title = apply_filters('widget_title', $instance['title']);

$style = (!empty($style)) ? $style : '';
$meta_obj = WP_Job_Board_Pro_Candidate_Meta::get_instance($post->ID);

$salary = WP_Job_Board_Pro_Candidate::get_salary_html($post->ID);

$experience_time = superio_candidate_display_meta($post, 'experience_time');
$gender = superio_candidate_display_meta($post, 'gender');
$age = superio_candidate_display_meta($post, 'age');
$qualification = superio_candidate_display_meta($post, 'qualification');
$languages = superio_candidate_display_meta($post, 'languages');


$email = superio_candidate_display_email($post, false, false);

if($style == 'style1' || $style == 'style2'){
    $phone = superio_candidate_display_phone($post, false,true);
} else {
    $phone = superio_candidate_display_phone($post, false);
}
$website = superio_candidate_display_meta($post, 'website');

if ( method_exists('WP_Job_Board_Pro_User', 'is_employee') && WP_Job_Board_Pro_User::is_employee($user_id) ) {
    $user_id = WP_Job_Board_Pro_User::get_user_id();
}

?>
<div class="job-detail-detail in-sidebar <?php echo esc_attr($style); ?>">
    <?php 
        if ( $title ) {
            echo trim($before_title)  . trim( $title ) . $after_title;
        }
    ?>
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

    <?php if($style == 'style1' || $style == 'style2'){ ?>
        <?php superio_private_message_form($post, $user_id); ?>
        <?php
        if ($meta_obj->check_post_meta_exist('socials') ) {
            $socials = $meta_obj->get_post_meta( 'socials' );
            $all_socials = WP_Job_Board_Pro_Mixes::get_socials_network();
        ?>
            <?php if ( $socials ) { ?>
                <div class="apus-social-share">
                    <?php foreach ($socials as $social) { ?>
                        <?php if ( !empty($social['url']) && !empty($social['network']) ) {
                            $icon_class = !empty( $all_socials[$social['network']]['icon'] ) ? $all_socials[$social['network']]['icon'] : '';
                        ?>
                            <a href="<?php echo esc_html($social['url']); ?>">
                                <i class="<?php echo esc_attr($icon_class); ?>"></i>
                            </a>
                        <?php } ?>
                    <?php } ?>
                </div>
            <?php } ?>
        <?php } ?>
    <?php } ?>
</div>
<?php echo trim($after_widget);