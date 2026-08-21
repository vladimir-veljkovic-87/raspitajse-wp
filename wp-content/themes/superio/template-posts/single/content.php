<?php
global $post;
$post_format = get_post_format();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="container">
        <?php get_template_part( 'template-posts/single/header' ); ?>
    </div>
    <?php
        $thumb = superio_post_thumbnail();
        if ( $thumb ) { ?>
            <div class="entry-thumb-detail">
                <?php echo trim($thumb); ?>
            </div>
    <?php } ?>

    <div class="top-content-detail-blog">
        <div class="container">

            <div class="entry-content-detail <?php echo esc_attr((!has_post_thumbnail())?'not-img-featured':'' ); ?>">
                
                <div class="single-info info-bottom">
                    <div class="entry-description">
                        <?php the_content(); ?>
                    </div><!-- /entry-content -->
                    
                    <?php
                    wp_link_pages( array(
                        'before'      => '<div class="page-links"><span class="page-links-title">' . esc_html__( 'Pages:', 'superio' ) . '</span>',
                        'after'       => '</div>',
                        'link_before' => '<span>',
                        'link_after'  => '</span>',
                        'pagelink'    => '<span class="screen-reader-text">' . esc_html__( 'Page', 'superio' ) . ' </span>%',
                        'separator'   => '',
                    ) );
                    ?>

                    <?php  
                        $posttags = get_the_tags();
                    ?>
                    <?php if( !empty($posttags) || superio_get_config('show_blog_social_share', false) ){ ?>
                        <div class="tag-social clearfix <?php echo ( superio_get_config('show_blog_social_share', false) && !empty($posttags))?'has-tag':''; ?>">
                            
                            <?php if ( superio_get_config('show_blog_social_share', false) ) {
                                get_template_part( 'template-parts/sharebox' );
                            } ?>

                            <?php superio_post_tags(); ?>
                        </div>
                    <?php } ?>
                    

                    <?php 
                    //Previous/next post navigation.
                    the_post_navigation( array(
                        'next_text' => 
                            '<div class="inner">'.
                            '<div class="navi">'. esc_html__( 'Next Post', 'superio' ) . '<i class="ti-angle-right"></i></div>'.
                            '<span class="title-direct">%title</span></div>',
                        'prev_text' => 
                            '<div class="inner">'.
                            '<div class="navi"><i class="ti-angle-left"></i>' . esc_html__( 'Previous Post', 'superio' ) . '</div>'.
                            '<span class="title-direct">%title</span></div>',
                    ) );
                    ?>

                </div>
            </div>

            <?php
            get_template_part( 'template-posts/single/author-bio' );

            // If comments are open or we have at least one comment, load up the comment template.
            if ( comments_open() || get_comments_number() ) {
                comments_template();
            }

            ?>

        </div>
    </div>
    <?php 
        if ( superio_get_config('show_blog_releated', false) ) {
            get_template_part( 'template-posts/single/posts-releated' );
        }
    ?>
</article>