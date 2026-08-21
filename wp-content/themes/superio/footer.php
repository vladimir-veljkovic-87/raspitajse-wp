<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the "site-content" div and all content after.
 *
 * @package WordPress
 * @subpackage Superio
 * @since Superio 1.0
 */
$footer = apply_filters( 'superio_get_footer_layout', 'default' );

?>
	</div><!-- .site-content -->
	<?php if ( !empty($footer) ): ?>
		<?php superio_display_footer_builder($footer); ?>
	<?php else: ?>
		<footer id="apus-footer" class="apus-footer " role="contentinfo">
			<div class="footer-default">
				<div class="apus-footer-inner">
					<div class="apus-copyright">
						<div class="container">
							<div class="copyright-content text-center">
								<?php
									
									$allowed_html_array = array( 'a' => array('href' => array()) );
									echo sprintf(wp_kses(__('&copy; %s - Superio. All Rights Reserved. <br/> Powered by <a href="//themeforest.net/user/apustheme">ApusTheme</a>', 'superio'), $allowed_html_array), date("Y"));
								?>

							</div>
						</div>
					</div>
				</div>
			</div>
		</footer><!-- .site-footer -->
	<?php endif; ?>
	<?php
	if ( superio_get_config('back_to_top') ) { ?>
		<a href="#" id="back-to-top" class="add-fix-top">
			<i class="ti-angle-up"></i>
		</a>
	<?php
	}
	?>
</div><!-- .site -->
<?php wp_footer(); ?>
</body>
</html>