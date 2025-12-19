<?php
/**
 * Review
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_Review {
	
	public static function init() {
		add_filter( 'comments_template', array( __CLASS__, 'comments_template_loader') );

		add_action( 'comment_post', array( __CLASS__, 'save_rating_comment'), 10, 3 );
		add_action( 'comment_unapproved_to_approved', array( __CLASS__,'save_ratings_average'), 10 );
		add_action( 'comment_approved_to_unapproved', array( __CLASS__,'save_ratings_average'), 10 );

		add_action( 'comment_form_top', array( __CLASS__, 'comment_rating_fields' ) );
	}

	public static function comments_template_loader($template) {
	    if ( get_post_type() === 'employer') {
	        return WP_Job_Board_Pro_Template_Loader::locate('single-employer/reviews');
	    } elseif ( get_post_type() === 'candidate' ) {
	    	return WP_Job_Board_Pro_Template_Loader::locate('single-candidate/reviews');
	    }
	    return $template;
	}
	
	// comment list
	public static function job_employer_comments( $comment, $args, $depth ) {
	    echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-employer/review', array('comment' => $comment, 'args' => $args, 'depth' => $depth) );
	}

	public static function job_candidate_comments( $comment, $args, $depth ) {
	    echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/review', array('comment' => $comment, 'args' => $args, 'depth' => $depth) );
	}

	// add comment meta
	public static function save_rating_comment( $comment_id, $comment_approved, $commentdata ) {
	    $post_type = get_post_type($commentdata['comment_post_ID']);
	    if ( $post_type == 'employer' || $post_type == 'candidate' ) {
	    	if ( isset($_POST['rating']) ) {
		        add_comment_meta( $comment_id, '_rating', $_POST['rating'] );
		        
		        if ( $commentdata['comment_approved'] ) {
			        $average_rating = self::get_total_rating( $commentdata['comment_post_ID'] );
		        	update_post_meta( $commentdata['comment_post_ID'], '_average_rating', $average_rating );
		        }
		    }
	    }
	}

	public static function save_ratings_average($comment) {
		$post_id = $comment->comment_post_ID;
	    $post_type = get_post_type($post_id);

	    if ( $post_type == 'employer' || $post_type == 'candidate' ) {
	        $average_rating = self::get_total_rating( $post_id );
	        update_post_meta( $post_id, '_average_rating', $average_rating );
	    }
	}

	public static function get_ratings_average($post_id) {
	    return get_post_meta( $post_id, '_average_rating', true );
	}

	public static function get_review_comments( $args = array() ) {
	    $args = wp_parse_args( $args, array(
	        'status' => 'approve',
	        'post_id' => '',
	        'user_id' => '',
	        'post_type' => 'employer',
	        'number' => 0
	    ));
	    extract($args);

	    $cargs = array(
	        'status' => 'approve',
	        'post_type' => $post_type,
	        'number' => $number,
	        'meta_query' => array(
	            array(
	               'key' => '_rating',
	               'value' => 0,
	               'compare' => '>',
	            )
	        )
	    );
	    if ( !empty($post_id) ) {
	        $cargs['post_id'] = $post_id;
	    }
	    if ( !empty($user_id) ) {
	        $cargs['user_id'] = $user_id;
	    }

	    $comments = get_comments( $cargs );
	    
	    return $comments;
	}

	public static function get_total_reviews( $post_id ) {
		$post_type = get_post_type($post_id);
	    $args = array( 'post_id' => $post_id, 'post_type' => $post_type );
	    $comments = self::get_review_comments($args);

	    if (empty($comments)) {
	        return 0;
	    }
	    
	    return count($comments);
	}

	public static function get_total_rating( $post_id ) {
		$post_type = get_post_type($post_id);
	    $args = array( 'post_id' => $post_id, 'post_type' => $post_type );
	    $comments = self::get_review_comments($args);
	    if (empty($comments)) {
	        return 0;
	    }
	    $total_review = 0;
	    foreach ($comments as $comment) {
	        $rating = intval( get_comment_meta( $comment->comment_ID, '_rating', true ) );
	        if ($rating) {
	            $total_review += (int)$rating;
	        }
	    }
	    return round($total_review/count($comments),2);
	}

	public static function get_total_rating_by_user( $user_id, $post_type ) {
	    $args = array( 'user_id' => $user_id, 'post_type' => $post_type );
	    $comments = self::get_review_comments($args);

	    if (empty($comments)) {
	        return 0;
	    }
	    $total_review = 0;
	    foreach ($comments as $comment) {
	        $rating = intval( get_comment_meta( $comment->comment_ID, '_rating', true ) );
	        if ($rating) {
	            $total_review += (int)$rating;
	        }
	    }
	    return $total_review/count($comments);
	}

	public static function print_review( $rate, $type = '', $nb = 0 ) {
	    ?>
	    <div class="review-stars-rated-wrapper">
	        <div class="review-stars-rated">
	            <ul class="review-stars">
	                <li><span class="fa fa-star"></span></li>
	                <li><span class="fa fa-star"></span></li>
	                <li><span class="fa fa-star"></span></li>
	                <li><span class="fa fa-star"></span></li>
	                <li><span class="fa fa-star"></span></li>
	            </ul>
	            
	            <ul class="review-stars filled"  style="<?php echo esc_attr( 'width: ' . ( (float)$rate * 20 ) . '%' ) ?>" >
	                <li><span class="fa fa-star"></span></li>
	                <li><span class="fa fa-star"></span></li>
	                <li><span class="fa fa-star"></span></li>
	                <li><span class="fa fa-star"></span></li>
	                <li><span class="fa fa-star"></span></li>
	            </ul>
	        </div>
	        <?php if ($type == 'detail') { ?>
	            <span class="nb-review"><?php echo sprintf(_n('%d Review', '%d Reviews', $nb, 'wp-job-board-pro'), $nb); ?></span>
	        <?php } elseif ($type == 'list') { ?>
	            <span class="nb-review"><?php echo sprintf(_n('(%d Review)', '(%d Reviews)', $nb, 'wp-job-board-pro'), $nb); ?></span>
	        <?php } ?>
	    </div>
	    <?php
	}
	
	public static function get_detail_ratings( $post_id ) {
	    global $wpdb;
	    $comment_ratings = $wpdb->get_results( $wpdb->prepare(
	        "
	            SELECT cm2.meta_value AS rating, COUNT(*) AS quantity FROM $wpdb->posts AS p
	            INNER JOIN $wpdb->comments AS c ON (p.ID = c.comment_post_ID AND c.comment_approved=1)
	            INNER JOIN $wpdb->commentmeta AS cm2 ON cm2.comment_id = c.comment_ID AND cm2.meta_key=%s
	            WHERE p.ID=%d
	            GROUP BY cm2.meta_value",
	            '_rating',
	            $post_id
	        ), OBJECT_K
	    );
	    return $comment_ratings;
	}

	public static function comment_rating_fields () {
		global $post;
		if ( $post->post_type !== 'candidate' && $post->post_type !== 'employer' ) {
			return;
		}
		echo '<div class="choose-rating clearfix"><div class="choose-rating-inner row">
			<div class="col-sm-12 col-xs-12"><div class="form-group yourview"><div class="your-rating">' . esc_html__( 'Your Rating for this listing', 'wp-job-board-pro' ) . '</div><div class="wrapper-rating-form"><div class="comment-form-rating">
				<ul class="review-stars">
					<li><span class="fa fa-star"></span></li>
					<li><span class="fa fa-star"></span></li>
					<li><span class="fa fa-star"></span></li>
					<li><span class="fa fa-star"></span></li>
					<li><span class="fa fa-star"></span></li>
				</ul>
				<ul class="review-stars filled">
					<li><span class="fa fa-star"></span></li>
					<li><span class="fa fa-star"></span></li>
					<li><span class="fa fa-star"></span></li>
					<li><span class="fa fa-star"></span></li>
					<li><span class="fa fa-star"></span></li>
				</ul>
				<input type="hidden" value="5" name="rating" id="_input_rating"></div><label for="rating">' . esc_html__( 'Your Rating', 'wp-job-board-pro' ) .'</label></div></div></div></div></div><div class="group-upload-preview clearfix"></div>
				';
	}

}

WP_Job_Board_Pro_Review::init();