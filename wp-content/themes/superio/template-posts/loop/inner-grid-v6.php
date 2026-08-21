<?php
    global $post;
    $thumbsize = !isset($thumbsize) ? superio_get_config( 'blog_item_thumbsize', 'full' ) : $thumbsize;
    $thumb = superio_display_post_thumb($thumbsize);
?>
<article <?php post_class('post post-layout post-grid-v6'); ?>>
    <?php if($thumb) {?>
        <div class="top-image p-relative">
            <?php
                echo trim($thumb);
            ?>
            <?php superio_post_categories_first($post); ?>
         </div>
    <?php } ?>
    <div class="inner-bottom">
        <div class="top-info2">
            <div class="date">
                <?php the_time( get_option('date_format', 'd M, Y') ); ?>
            </div>
            <a href="<?php the_permalink(); ?>" class="author-post">
                <?php echo esc_html__( 'By', 'superio' ); ?> <span class="name-author"><?php echo get_the_author(); ?></span>
            </a>
        </div>
        <?php if (get_the_title()) { ?>
            <h4 class="entry-title">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h4>
        <?php } ?>
    </div>
</article>