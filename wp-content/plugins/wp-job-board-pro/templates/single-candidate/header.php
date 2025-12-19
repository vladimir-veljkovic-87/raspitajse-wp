<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;


$categories = get_the_terms( $post->ID, 'candidate_category' );
$address = WP_Job_Board_Pro_Candidate::get_post_meta( $post->ID, 'address', true );
?>
<div class="candidate-detail-header">
    <?php if ( has_post_thumbnail() ) { ?>
        <div class="candidate-thumbnail">
            <?php echo get_the_post_thumbnail( $post->ID, 'thumbnail' ); ?>
        </div>
    <?php } ?>
    <div class="candidate-information">
        
        <?php the_title( '<h1 class="entry-title candidate-title">', '</h1>' ); ?>
        <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) { ?>
            <?php foreach ($categories as $term) { ?>
                <a href="<?php echo get_term_link($term); ?>"><?php echo $term->name; ?></a>
            <?php } ?>
        <?php } ?>

        <?php if ( $address ) { ?>
            <div class="candidate-address"><?php echo $address; ?></div>
        <?php } ?>
        
    </div>

    <div class="candidate-detail-buttons">
        <?php WP_Job_Board_Pro_Candidate::display_shortlist_btn($post->ID); ?>
        <?php WP_Job_Board_Pro_Candidate::display_download_cv_btn($post->ID); ?>
    </div>
</div>