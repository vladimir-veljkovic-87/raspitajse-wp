<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $post;
if ( ! comments_open() ) {
	return;
}

?>
<div id="reviews">
	<?php if ( have_comments() ) : ?>
		<h3 class="title"><?php comments_number( esc_html__('0 Comments', 'superio'), esc_html__('1 Comment', 'superio'), esc_html__('% Comments', 'superio') ); ?></h3>
	<?php endif; ?>
	<div id="comments">
		<?php if ( have_comments() ) : ?>

			<ol class="comment-list">
				<?php wp_list_comments( array( 'callback' => array( 'WP_Job_Board_Pro_Review', 'job_employer_comments' ) ) ); ?>
			</ol>

			<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
				echo '<nav class="apus-pagination">';
				paginate_comments_links( apply_filters( 'apus_comment_pagination_args', array(
					'prev_text' => '&larr;',
					'next_text' => '&rarr;',
					'type'      => 'list',
				) ) );
				echo '</nav>';
			endif; ?>

		<?php endif; ?>
	</div>

	<?php if ( get_current_user_id() != $post->post_author ) { ?>
		<?php $commenter = wp_get_current_commenter(); ?>
		<div id="review_form_wrapper" class="commentform">
			<div id="review_form">
				<?php
					$comment_form = array(
						'title_reply'          => have_comments() ? esc_html__( 'Add a review', 'superio' ) : sprintf( esc_html__( 'Be the first to review &ldquo;%s&rdquo;', 'superio' ), get_the_title() ),
						'title_reply_to'       => esc_html__( 'Leave a Reply to %s', 'superio' ),
						'comment_notes_before' => '',
						'comment_notes_after'  => '',
						'fields'               => array(
							'author' => '<div class="row"><div class="col-xs-12 col-sm-12"><div class="form-group"><label>'.esc_html__( 'Name', 'superio' ).'</label>'.
							            '<input id="author" placeholder="'.esc_attr__( 'Your Name', 'superio' ).'" class="form-control" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" aria-required="true" /></div></div>',
							'email'  => '<div class="col-xs-12 col-sm-12"><div class="form-group"><label>'.esc_html__( 'Email', 'superio' ).'</label>' .
							            '<input id="email" placeholder="'.esc_attr__( 'your@mail.com', 'superio' ).'" class="form-control" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30" aria-required="true" /></div></div></div>',
						),
						'label_submit'  => esc_html__( 'Submit Review', 'superio' ),
						'logged_in_as'  => '',
						'comment_field' => '',
						'title_reply_before' => '<h4 class="title comment-reply-title">',
						'title_reply_after'  => '</h4>',
						'class_submit' => 'btn btn-theme'
					);

					$comment_form['must_log_in'] = '<div class="must-log-in">' .__( 'You must be <a href="">logged in</a> to post a review.', 'superio' ) . '</div>';

					$comment_form['comment_field'] .= '<div class="form-group space-30"><label>'.esc_html__( 'Review', 'superio' ).'</label><textarea id="comment" class="form-control" placeholder="'.esc_attr__( 'Write Comment', 'superio' ).'" name="comment" cols="45" rows="5" aria-required="true" required></textarea></div>';
					
					superio_comment_form($comment_form);
				?>
			</div>
		</div>
	<?php } ?>
</div>