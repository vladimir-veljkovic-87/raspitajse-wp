<?php

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}
global $post;


$user_id = WP_Job_Board_Pro_User::get_user_id();
$candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);
$meta_obj = WP_Job_Board_Pro_Candidate_Meta::get_instance($candidate_id);

$cv_attachments = $meta_obj->get_post_meta('cv_attachment');
$rand = !empty($rand) ? $rand : $post->ID;

$cv_required = wp_job_board_pro_get_option('candidate_apply_job_cv_required', 'on');
?>


<div id="job-apply-internal-form-wrapper-<?php echo esc_attr($rand); ?>" class="job-apply-internal-form-wrapper mfp-hide">
	<div class="inner">
		<h2 class="widget-title">
			<span><?php esc_html_e('Apply for this job', 'superio'); ?></span>
		</h2>

	    <form id="job-apply-internal-form-<?php echo esc_attr($post->ID); ?>" class="job-apply-internal-form" method="post" action="" enctype="multipart/form-data">
	    	<div class="row">
	    		<?php if ( $cv_required == 'on' ) { ?>
		    		<?php if ( is_array($cv_attachments) ) { ?>
				        <div class="col-sm-12">
				        	<div class="file-or-upload"><?php esc_html_e('Select a your CV', 'superio'); ?></div>
					        <div class="wrapper-file-action <?php echo trim( (count($cv_attachments) > 1)?'has-multiply':'' ); ?>">
					            <?php
					            foreach ($cv_attachments as $id => $cv_url) {
							        $file_info = pathinfo($cv_url); 
							        if ( $file_info ) {
							        	?>
							        	<label for="apply-internal-cv-<?php echo esc_attr($id); ?>" class="list-file-cv">
							        		<input id="apply-internal-cv-<?php echo esc_attr($id); ?>" type="radio" class="apply_cv_id" name="apply_cv_id" value="<?php echo esc_attr($id); ?>">
							        		<div class="candidate-detail-cv">
								                <span class="icon_type">
								                    <i class="flaticon-file"></i>
								                </span>
								                <?php if ( !empty($file_info['filename']) ) { ?>
								                    <span class="filename"><?php echo esc_html($file_info['filename']); ?></span>
								                <?php } ?>
								                <?php if ( !empty($file_info['extension']) ) { ?>
								                    <span class="extension"><?php echo esc_html($file_info['extension']); ?></span>
								                <?php } ?>
							                </div>
							            </label>
							        	<?php
							        }
						        }
							    ?>

							    <div class="file-or-upload"><?php esc_html_e('or upload your CV', 'superio'); ?></div>
					        </div><!-- /.form-group -->
					    </div>
					<?php } ?>

					<?php
					$cv_types = wp_job_board_pro_get_option('cv_file_types');
					$cv_types_str = !empty($cv_types) ? implode(', ', $cv_types) : '';
					?>

			        <div class="col-sm-12">
				     	<div class="form-group upload-file-btn-wrapper">
				            <input type="file" class="cv_file" name="cv_file" data-file_types="<?php echo esc_attr(!empty($cv_types) ? implode('|', $cv_types) : ''); ?>">

				            <div class="label-can-drag">
								<div class="form-group group-upload">
							        <div class="upload-file-btn" data-text="<?php echo esc_attr(sprintf(esc_html__('Upload CV (%s)', 'superio'), $cv_types_str)); ?>">
						            	<span class="text"><?php echo sprintf(esc_html__('Upload CV (%s)', 'superio'), $cv_types_str); ?></span>
							        </div>
							    </div>
							</div>
				        </div>
			        </div><!-- /.form-group -->
			    <?php } ?>
			    
		        <div class="col-sm-12">
			     	<div class="form-group space-30">
			            <textarea class="form-control style2" name="message" placeholder="<?php esc_attr_e( 'Message', 'superio' ); ?>" required="required"></textarea>
			        </div>
		        </div><!-- /.form-group -->

		        <?php
				$page_id = wp_job_board_pro_get_option('terms_conditions_page_id');
				$page_id = WP_Job_Board_Pro_Mixes::get_lang_post_id( $page_id, 'page');
				if ( !empty($page_id) ) {
					$page_url = $page_id ? get_permalink($page_id) : home_url('/');
				?>
					<div style="clear: both;"></div>
					<div class="col-sm-12">
						<div class="form-group">
							<label for="register-terms-and-conditions">
								<input type="checkbox" name="terms_and_conditions" value="on" id="register-terms-and-conditions" required>
								<?php
									$allowed_html_array = array( 'a' => array('href' => array(), 'target' => array()) );
									echo sprintf(wp_kses(__('You accept our <a href="%s" target="_blank">Terms and Conditions and Privacy Policy</a>', 'superio'), $allowed_html_array), esc_url($page_url));
								?>
							</label>
						</div>
					</div>
				<?php } ?>
	        </div>
	       	

	        <?php if ( WP_Job_Board_Pro_Recaptcha::is_recaptcha_enabled() ) { ?>
	            <div id="recaptcha-contact-form" class="ga-recaptcha" data-sitekey="<?php echo esc_attr(wp_job_board_pro_get_option( 'recaptcha_site_key' )); ?>"></div>
	      	<?php } ?>

	      	<?php wp_nonce_field( 'wp-job-board-pro-apply-internal-nonce', 'nonce' ); ?>
	      	<input type="hidden" name="action" value="wp_job_board_pro_ajax_apply_internal">
	      	<input type="hidden" name="job_id" value="<?php echo esc_attr($post->ID); ?>">
	        <button class="button btn btn-theme btn-block" name="apply-email"><?php echo esc_html__( 'Apply Job', 'superio' ); ?></button>
	    </form>
	</div>
</div>