<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $post;
$rating = get_comment_meta( $comment->comment_ID, '_rating', true );

?>
<li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">

	<div id="comment-<?php comment_ID(); ?>" class="the-comment ">
		<div class="avatar">
			<?php
			if ( empty($comment->user_id) ) {
				echo get_avatar( $comment->user_id, '80', '' );
			} elseif ( WP_Job_Board_Pro_User::is_candidate($comment->user_id) ) {
				$candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($comment->user_id);
				
				$candidate_p = get_post($candidate_id);
				superio_candidate_display_logo($candidate_p, true, 'thumbnail', 'class=avatar');
			}  elseif ( WP_Job_Board_Pro_User::is_employer($comment->user_id) ) {
				$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($comment->user_id);
				
				$employer_p = get_post($employer_id);
				superio_employer_display_logo($employer_p, true, 'thumbnail', 'class=avatar');
			} else {
				echo get_avatar( $comment->user_id, '80', '' );
			}
			?>
		</div>
		<div class="comment-box">
			<div class="flex-top clearfix">
				<div class="meta comment-author">
					<div class="info-meta">
						<strong>
							<?php if ( empty($comment->user_id) ) {
								comment_author();
							} elseif ( WP_Job_Board_Pro_User::is_candidate($comment->user_id) ) {
								$candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($comment->user_id);
								echo get_the_title($candidate_id);
							}  elseif ( WP_Job_Board_Pro_User::is_employer($comment->user_id) ) {
								$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($comment->user_id);
								echo get_the_title($employer_id);
							} else {
								comment_author();
							}?>
						</strong>
						<?php if ( $comment->comment_approved == '0' ) : ?>
							<div class="date"><em><?php esc_html_e( 'Your comment is awaiting approval', 'superio' ); ?></em></div>
						<?php else : ?>
							<div class="date">
								<i class="flaticon-event"></i><?php echo get_comment_date( get_option('date_format', 'd M, Y') ); ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
				<div class="star-rating clear ali-right" title="<?php echo sprintf( esc_attr__( 'Rated %d out of 5', 'superio' ), $rating ) ?>">
					<span class="review-avg"><?php echo number_format((float)$rating, 1, '.', ''); ?></span>
					<?php echo WP_Job_Board_Pro_Review::print_review($rating); ?>
				</div>
			</div>
			<div itemprop="description" class="comment-text">
				<?php comment_text(); ?>
			</div>
		</div>
	</div>