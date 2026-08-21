<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

$relate_count = superio_get_config('candidate_releated_number', 3);

$tax_query = array();

$terms = WP_Job_Board_Pro_Job_Listing::get_job_taxs( $post->ID, 'candidate_category' );
$relate_columns = superio_get_config('candidate_releated_columns', 3);
if ($terms) {
    $termids = array();
    foreach($terms as $term) {
        $termids[] = $term->term_id;
    }
    $tax_query[] = array(
        'taxonomy' => 'candidate_category',
        'field' => 'id',
        'terms' => $termids,
        'operator' => 'IN'
    );
}
if ( empty($tax_query) ) {
    return;
}

$meta_query = array();
$args = array(
    'post_type' => 'candidate',
    'posts_per_page' => $relate_count,
    'post__not_in' => array( get_the_ID() ),
    'tax_query' => $tax_query,
    'meta_query' => array(
        array(
            'relation' => 'OR',
            array(
                'key'       => WP_JOB_BOARD_PRO_CANDIDATE_PREFIX.'show_profile',
                'value'     => 'show',
                'compare'   => '==',
            ),
            array(
                'key'       => WP_JOB_BOARD_PRO_CANDIDATE_PREFIX.'show_profile',
                'compare' => 'NOT EXISTS',
            ),
        ),
    )
);
$relates = new WP_Query( $args );
if( $relates->have_posts() ):
?>
    <div class="widget releated-candidates">
        <h4 class="widget-title">
            <?php esc_html_e( 'Related Candidates', 'superio' ); ?>
        </h4>
        <div class="widget-content">
            <div class="row">
                <?php $i=1;
                    while ( $relates->have_posts() ) : $relates->the_post();
                        ?>
                        <div class="col-xs-12 <?php echo esc_attr( ($i == $relates->post_count )?'item-last':''); ?>">
                            <?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'candidates-styles/inner-list' ); ?>
                        </div>
                        <?php
                    $i++; endwhile;
                ?>
            </div>
            <?php wp_reset_postdata(); ?>
        </div>
    </div>
<?php endif; ?>