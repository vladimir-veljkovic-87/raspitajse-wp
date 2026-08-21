<?php
    $relate_count = superio_get_config('number_blog_releated', 3);
    $relate_columns = superio_get_config('releated_blog_columns', 3);
    $terms = get_the_terms( get_the_ID(), 'category' );
    $termids =array();

    if ($terms) {
        foreach($terms as $term) {
            $termids[] = $term->term_id;
        }
    }

    $args = array(
        'post_type' => 'post',
        'posts_per_page' => $relate_count,
        'post__not_in' => array( get_the_ID() ),
        'tax_query' => array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'category',
                'field' => 'id',
                'terms' => $termids,
                'operator' => 'IN'
            )
        )
    );
    $relates = new WP_Query( $args );
    if( $relates->have_posts() ):
?>
<div class="related-posts">
    <div class="container">
        <div class="top-related text-center">
            <h4 class="title">
                <?php esc_html_e( 'Recent News Articles', 'superio' ); ?>
            </h4>
            <div class="des"><?php esc_html_e( 'Fresh job related news content posted each day.', 'superio' ); ?></div>
        </div>
        <div class="related-posts-content">
            <div class="slick-carousel" data-carousel="slick" data-smallmedium="2" data-extrasmall="1" data-items="<?php echo esc_attr($relate_columns); ?>" data-pagination="false" data-nav="true">
                <?php while ( $relates->have_posts() ) : $relates->the_post(); ?>
                    <div class="item">
                        <?php get_template_part( 'template-posts/loop/inner-grid' ); ?>
                    </div>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>