<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

$author_id = WP_Job_Board_Pro_Job_Listing::get_author_id($post->ID);
$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($author_id);
if ( empty($employer_id) ) {
    return;
}
$address = WP_Job_Board_Pro_Employer::get_post_meta($employer_id, 'address', true);
$phone = WP_Job_Board_Pro_Employer::get_post_meta($employer_id, 'phone', true);
$email = WP_Job_Board_Pro_Employer::get_post_meta($employer_id, 'email', true);
?>
<div class="job-detail-employer">

    <?php if ( has_post_thumbnail($employer_id) ) { ?>
        <div class="employer-thumbnail">
            <?php echo get_the_post_thumbnail( $employer_id, 'thumbnail' ); ?>
        </div>
    <?php } ?>
    <?php if ( !empty($address) ) { ?>
        <div class="employer-address">
            <?php echo $address; ?>
        </div>
    <?php } ?>
    <?php if ( !empty($phone) ) { ?>
        <div class="employer-phone">
            <?php echo $phone; ?>
        </div>
    <?php } ?>
    <?php if ( !empty($email) ) { ?>
        <div class="employer-email">
            <?php echo $email; ?>
        </div>
    <?php } ?>

</div>