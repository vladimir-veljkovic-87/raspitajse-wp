<?php

remove_action( 'widgets_init', array( WP_Private_Message::getInstance(), 'register_widgets' ) );

add_action( 'superio_after_contact_form', 'superio_private_message_form', 10, 2 );
function superio_private_message_form($post, $user_id) {
	?>
	<div class="send-private-wrapper">
		<a href="#send-private-message-wrapper-<?php echo esc_attr($post->ID); ?>" class="send-private-message-btn btn"><?php esc_html_e('Private Message', 'superio'); ?></a>
	</div>
	<div id="send-private-message-wrapper-<?php echo esc_attr($post->ID); ?>" class="send-private-message-wrapper mfp-hide" data-effect="fadeIn">
		<h3 class="title"><?php echo esc_html__('Send message', 'superio'); ?></h3>
		<?php
		if ( is_user_logged_in() ) {
			?>
			<form id="send-message-form" class="send-message-form" action="?" method="post">
                <div class="form-group">
                    <input type="text" class="form-control style2" name="subject" placeholder="<?php esc_attr_e( 'Subject', 'superio' ); ?>" required="required">
                </div><!-- /.form-group -->
                <div class="form-group">
                    <textarea class="form-control message style2" name="message" placeholder="<?php esc_attr_e( 'Enter text here...', 'superio' ); ?>" required="required"></textarea>
                </div><!-- /.form-group -->

                <?php wp_nonce_field( 'wp-private-message-send-message', 'wp-private-message-send-message-nonce' ); ?>
              	<input type="hidden" name="recipient" value="<?php echo esc_attr($user_id); ?>">
              	<input type="hidden" name="action" value="wp_private_message_send_message">
                <button class="button btn btn-theme btn-block send-message-btn"><?php echo esc_html__( 'Send Message', 'superio' ); ?></button>
        	</form>
			<?php
		} else {
			$login_url = '';
			if ( function_exists('wp_job_board_pro_get_option') ) {
				$page_id = wp_job_board_pro_get_option('login_register_page_id');
				$page_id = WP_Job_Board_Pro_Mixes::get_lang_post_id($page_id);
				$login_url = get_permalink( $page_id );
			}
			?>
			<a href="<?php echo esc_url($login_url); ?>" class="login"><?php esc_html_e('Please login to send a private message', 'superio'); ?></a>
			<?php
		}
		?>
	</div>
	<?php
}

function superio_private_message_form_btn($post, $user_id) {
	?>

	<a data-toggle="tooltip" class="btn-action-icon send-private-message-btn" href="#send-private-message-wrapper-<?php echo esc_attr($post->ID); ?>" data-candidate_id="<?php echo esc_attr($post->ID); ?>" title="<?php esc_attr_e('Send message', 'superio'); ?>"><i class="ti-email"></i></a>

	<div id="send-private-message-wrapper-<?php echo esc_attr($post->ID); ?>" class="send-private-message-wrapper mfp-hide" data-effect="fadeIn">
		<h3 class="title"><?php echo esc_html__('Send message', 'superio'); ?></h3>
		<?php
		if ( is_user_logged_in() ) {
			?>
			<form id="send-message-form" class="send-message-form" action="?" method="post">
                <div class="form-group">
                    <input type="text" class="form-control style2" name="subject" placeholder="<?php esc_attr_e( 'Subject', 'superio' ); ?>" required="required">
                </div><!-- /.form-group -->
                <div class="form-group">
                    <textarea class="form-control message style2" name="message" placeholder="<?php esc_attr_e( 'Enter text here...', 'superio' ); ?>" required="required"></textarea>
                </div><!-- /.form-group -->

                <?php wp_nonce_field( 'wp-private-message-send-message', 'wp-private-message-send-message-nonce' ); ?>
              	<input type="hidden" name="recipient" value="<?php echo esc_attr($user_id); ?>">
              	<input type="hidden" name="action" value="wp_private_message_send_message">
                <button class="button btn btn-theme btn-block send-message-btn"><?php echo esc_html__( 'Send Message', 'superio' ); ?></button>
        	</form>
			<?php
		} else {
			$login_url = '';
			if ( function_exists('wp_job_board_pro_get_option') ) {
				$page_id = wp_job_board_pro_get_option('login_register_page_id');
				$page_id = WP_Job_Board_Pro_Mixes::get_lang_post_id($page_id);
				$login_url = get_permalink( $page_id );
			}
			?>
			<a href="<?php echo esc_url($login_url); ?>" class="login"><?php esc_html_e('Please login to send a private message', 'superio'); ?></a>
			<?php
		}
		?>
	</div>
	<?php
}

function superio_private_message_user_avarta($user_id) {
	if ( class_exists('WP_Job_Board_Pro_User') && (WP_Job_Board_Pro_User::is_employer($user_id) || WP_Job_Board_Pro_User::is_candidate($user_id)) ) {
	    if ( WP_Job_Board_Pro_User::is_employer($user_id) ) {
	        $employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);
	        $avatar = get_the_post_thumbnail( $employer_id, 'thumbnail' );
	    } else {
	        $candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);
	        $avatar = get_the_post_thumbnail( $candidate_id, 'thumbnail' );
	    }
	}

	if ( !empty($avatar)) {
        echo trim($avatar);
    } else {
        echo get_avatar($user_id, 54);
    }
}

function superio_private_message_user_id($user_id = 0) {
	if ( method_exists('WP_Job_Board_Pro_User', 'get_user_id') ) {
        $user_id = WP_Job_Board_Pro_User::get_user_id();
    } else {
    	$user_id = get_current_user_id();
    }
    return $user_id;
}
add_filter('wp-private-message-get-current-user-id', 'superio_private_message_user_id');

function superio_private_message_user_display_name($display_name, $user_id) {
	if ( class_exists('WP_Job_Board_Pro_User') && (WP_Job_Board_Pro_User::is_employer($user_id) || WP_Job_Board_Pro_User::is_candidate($user_id)) ) {
	    if ( WP_Job_Board_Pro_User::is_employer($user_id) ) {
	        $employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);
	        $display_name = get_the_title($employer_id);
	    } else {
	        $candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);
	        $display_name = get_the_title($candidate_id);
	    }
	}
    return $display_name;
}
add_filter('superio-private-message-user-display-name', 'superio_private_message_user_display_name', 10, 2);