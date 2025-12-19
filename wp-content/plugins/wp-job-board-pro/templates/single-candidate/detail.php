<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

$categories = get_the_terms( $post->ID, 'employer_category' );

?>
<div class="candidate-detail-detail">
    <ul class="list">
        
        <?php if ( $categories ) { ?>
            <li>
                <div class="icon">
                    <i class="flaticon-2-squares"></i>
                </div>
                <div class="details">
                    <div class="text"><?php esc_html_e('Kategorije', 'wp-job-board-pro'); ?></div>
                    <div class="value">
                        <?php foreach ($categories as $term) { ?>
                            <a href="<?php echo get_term_link($term); ?>"><?php echo esc_html($term->name); ?></a>
                        <?php } ?>
                    </div>
                </div>
            </li>
        <?php } ?>

    </ul>
</div>