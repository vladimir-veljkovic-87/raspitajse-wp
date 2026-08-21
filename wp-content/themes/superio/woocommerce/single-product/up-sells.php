<?php
/**
 * Single Product Up-Sells
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$columns = superio_get_config('upsells_product_columns', true);

if ( $upsells ) : ?>

	<div class="related products">
		<div class="woocommerce">
			<h2 class="widget-title"><?php esc_html_e( 'Up-Sells Products', 'superio' ) ?></h2>

			<div class="slick-carousel products" data-carousel="slick"
			    data-items="<?php echo esc_attr($columns); ?>"
			    data-smallmedium="2"
			    data-extrasmall="1"

			    data-slidestoscroll="<?php echo esc_attr($columns); ?>"
			    data-slidestoscroll_smallmedium="2"
			    data-slidestoscroll_extrasmall="1"

			    data-pagination="false" data-nav="true">

					<?php wc_set_loop_prop( 'loop', 0 ); ?>

					<?php foreach ( $upsells as $upsell ) : ?>

						<?php
						$post_object = get_post( $upsell->get_id() );

						setup_postdata( $GLOBALS['post'] =& $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found

						wc_get_template_part( 'item-product/inner' );

						?>

					<?php endforeach; ?>
			</div>

		</div>
	</div>

	<?php
endif;

wp_reset_postdata();