<?php 
global $product;
$product_id = $product->get_id();
?>
<div class="product-block grid" data-product-id="<?php echo esc_attr($product_id); ?>">
    <div class="grid-inner">
        <div class="block-inner">
            <figure class="image">
                <?php
                    do_action( 'woocommerce_before_shop_loop_item_title' );
                ?>
            </figure>
        </div>
        <div class="metas text-center">

            <h3 class="name"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
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
             
        </div>
        <div class="groups-button clearfix">
            <?php do_action( 'woocommerce_after_shop_loop_item' ); ?>
        </div>
    </div>
</div>