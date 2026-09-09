<?php

global $post;

$img = '';
if ( has_post_thumbnail($post->ID) ) {
	$img = get_the_post_thumbnail_url($post->ID, 'full');
}
?>
<div class="apus-social-share share-blog">
		<h3 class="title"><?php echo esc_html__('Share this post','superio'); ?> </h3>
		
		<?php if ( superio_get_config('facebook_share', 1) ): ?>
 
			<a class="facebook" data-toggle="tooltip" data-original-title="Facebook" href="http://www.facebook.com/sharer.php?s=100&u=<?php the_permalink(); ?>&i=<?php echo urlencode($img); ?>" target="_blank" title="<?php echo esc_html__('Share on facebook', 'superio'); ?>">
				<i class="fab fa-facebook-f"></i>
				<?php echo esc_html__('Facebook', 'superio'); ?>
			</a>
 
		<?php endif; ?>
		<?php if ( superio_get_config('twitter_share', 1) ): ?>
			<a class="twitter" data-toggle="tooltip" data-original-title="Twitter" href="https://twitter.com/intent/tweet?url=<?php the_permalink(); ?>" target="_blank" title="<?php echo esc_html__('Share on Twitter', 'superio'); ?>">
				<i class="fab fa-x-twitter"></i>
				<?php echo esc_html__('Twitter', 'superio'); ?>
			</a>
 
		<?php endif; ?>
		<?php if ( superio_get_config('linkedin_share', 1) ): ?>
 
			<a class="linkedin"  data-toggle="tooltip" data-original-title="LinkedIn" href="http://linkedin.com/shareArticle?mini=true&amp;url=<?php the_permalink(); ?>&amp;title=<?php the_title(); ?>" target="_blank" title="<?php echo esc_html__('Share on LinkedIn', 'superio'); ?>">
				<i class="fab fa-linkedin-in"></i>
				<?php echo esc_html__('LinkedIn', 'superio'); ?>
			</a>
 
		<?php endif; ?>

		<?php if ( superio_get_config('pinterest_share', 1) ): ?>
 
			<a class="pinterest" data-toggle="tooltip" data-original-title="Pinterest" href="http://pinterest.com/pin/create/button/?url=<?php echo urlencode(get_permalink()); ?>&amp;media=<?php echo urlencode($img); ?>" target="_blank" title="<?php echo esc_html__('Share on Pinterest', 'superio'); ?>">
				<i class="fab fa-pinterest-p"></i>
				<?php echo esc_html__('Pinterest', 'superio'); ?>
			</a>
 
		<?php endif; ?>
</div>