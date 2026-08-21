<?php 
global $post;
$thumbsize = !isset($thumbsize) ? superio_get_config( 'blog_item_thumbsize', 'full' ) : $thumbsize;
$thumb = superio_display_post_thumb($thumbsize);
?>
<article <?php post_class('post post-layout post-list-item woocommerce'); ?> data-product-id="<?php echo esc_attr($post->ID); ?>">
    <div class="product-block product-block-search">
        <div class="flex-middle">
            <?php
                if ( !empty($thumb) ) {
                    ?>
                    <div class="top-image">
                        <?php
                            echo trim($thumb);
                        ?>
                     </div>
                    <?php
                }
            ?>
            <div class="inner-bottom">

                <?php if (get_the_title()) { ?>
                    <h4 class="entry-title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h4>
                <?php } ?>
                
                <?php
                    /**
                    * woocommerce_after_shop_loop_item_title hook
                    *
                    * @hooked woocommerce_template_loop_rating - 5
                    * @hooked woocommerce_template_loop_price - 10
                    */
                    remove_action('woocommerce_after_shop_loop_item_title','woocommerce_template_loop_rating', 5);
                    do_action( 'woocommerce_after_shop_loop_item_title');
                ?> 

                <div class="groups-button clearfix">
                    <?php do_action( 'woocommerce_after_shop_loop_item' ); ?>
                </div>
            </div>
        </div>
    </div>
</article>