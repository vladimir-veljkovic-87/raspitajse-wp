<?php
/**
 * Related Products
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$show_product_releated = superio_get_config('show_product_releated', true);
if ( !$show_product_releated  ) {
    return;
}

$columns = superio_get_config('releated_product_columns', 4);

if ( $related_products ) : ?>

	<div class="related products">
		<div class="woocommerce">
			<h3 class="widget-title"><?php esc_html_e( 'Related Products', 'superio' ); ?></h3>
			<div class="slick-carousel products" data-carousel="slick"
			    data-items="<?php echo esc_attr($columns); ?>"
			    data-smallmedium="2"
			    data-extrasmall="1"

			    data-slidestoscroll="<?php echo esc_attr($columns); ?>"
			    data-slidestoscroll_smallmedium="2"
			    data-slidestoscroll_extrasmall="1"

			    data-pagination="false" data-nav="true">

					<?php wc_set_loop_prop( 'loop', 0 ); ?>

					<?php foreach ( $related_products as $related_product ) : ?>

							<?php
							$post_object = get_post( $related_product->get_id() );

							setup_postdata( $GLOBALS['post'] =& $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found
							?> 
							<div class="inner">
								<?php wc_get_template_part( 'item-product/inner' ); ?>
							</div>
					<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php
endif;

wp_reset_postdata();