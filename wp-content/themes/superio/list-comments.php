<?php

$GLOBALS['comment'] = $comment;
$add_below = '';

?>
<li <?php comment_class(); ?> id="comment-<?php comment_ID() ?>">

	<div class="the-comment">
		<?php if(get_avatar($comment, 80)){ ?>
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
		<?php } ?>
		<div class="comment-box">
			<div class="clearfix flex-top">
				<div class="comment-author meta">
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
					<div class="date"><?php printf(esc_html__('%1$s', 'superio'), get_comment_date()) ?></div>
				</div>
				<div class="ali-right">
					<?php edit_comment_link(esc_html__('Edit', 'superio'),'  ','') ?>
					<?php comment_reply_link(array_merge( $args, array( 'reply_text' => esc_html__(' Reply', 'superio'), 'add_below' => 'comment', 'depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
				</div>
			</div>
			<div class="comment-text">
				<?php if ($comment->comment_approved == '0') : ?>
				<em><?php esc_html_e('Your comment is awaiting moderation.', 'superio') ?></em>
				<br />
				<?php endif; ?>
				<?php comment_text() ?>
			</div>
		</div>
	</div>
