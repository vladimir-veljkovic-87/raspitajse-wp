<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;


$categories = get_the_terms( $post->ID, 'candidate_category' );
$location_html = superio_candidate_display_full_location($post, 'icon', false);

$phone_html = superio_candidate_display_phone($post, false, true);
$email_html = superio_candidate_display_email($post, false, false);

$urgent = WP_Job_Board_Pro_Candidate::get_post_meta( $post->ID, 'urgent', true );
?>
<div class="candidate-detail-header">
    <div class="flex-sm row">
        <div class="col-xs-12"> 
            <div class="flex">
                <?php if ( has_post_thumbnail() ) { ?>
                    <div class="candidate-thumbnail">
                        <div class="inner-image">
                            <a href="<?php the_permalink(); ?>">
                                <?php echo get_the_post_thumbnail( $post->ID, 'thumbnail' ); ?>
                            </a>
                        </div>
                    </div>
                <?php } ?>
                <div class="candidate-information">
                    <div class="title-wrapper">
                        <h1 class="candidate-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
                        <?php if ( $urgent ) { ?>
                            <span class="urgent"><?php esc_html_e('Urgentno', 'superio'); ?></span>
                        <?php } ?>
                    </div>
                    <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) { ?>
                        <?php foreach ($categories as $term) { ?>
                            <span><?php echo trim($term->name); ?></span>
                        <?php } ?>
                    <?php } ?>

                    <div class="job-metas-cadidate">
                        <?php if ( $phone_html ) { ?>
                            <?php echo trim($phone_html); ?>
                        <?php } ?>
                        <?php if ( $email_html ) { ?>
                            <?php echo trim($email_html); ?>
                        <?php } ?>
                    </div>
                    <div class="job-metas-cadidate">
                        <?php if ( $location_html ) { ?>
                            <?php echo trim($location_html); ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>  
        
    </div>
</div>