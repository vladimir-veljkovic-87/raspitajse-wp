<?php
    $thumbsize = !isset($thumbsize) ? superio_get_config( 'blog_item_thumbsize', 'full' ) : $thumbsize;
    $thumb = superio_display_post_thumb($thumbsize);
?>
<article <?php post_class('post post-grid-v5'); ?>>
    <?php if($thumb) {?>
        <div class="top-image">
            <?php
                echo trim($thumb);
            ?>
         </div>
    <?php } ?>
    <div class="inner-bottom flex-column flex">
        <div class="date">
            <div class="day"><?php the_time('d'); ?></div>
            <?php the_time('M'); ?>
        </div>
        <div class="bottom-inner">
            <?php if (get_the_title()) { ?>
                <h4 class="entry-title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h4>
            <?php } ?>
            <div class="flex-middle">
                <div class="comments">
                    <?php comments_number( esc_html__('0 Comments', 'superio'), esc_html__('1 Comment', 'superio'), esc_html__('% Comments', 'superio') ); ?>
                </div>
                <div class="ali-right visible-lg">
                    <a class="btn-readmore" href="<?php the_permalink(); ?>"><?php esc_html_e('Read More', 'superio'); ?><i class="ti-angle-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</article>