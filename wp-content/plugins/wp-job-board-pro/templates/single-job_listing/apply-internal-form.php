<?php

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}
global $post;


$user_id = WP_Job_Board_Pro_User::get_user_id();
$candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);
$meta_obj = WP_Job_Board_Pro_Candidate_Meta::get_instance($candidate_id);

$cv_attachments = $meta_obj->get_post_meta('cv_attachment');
?>


<div id="job-apply-internal-form-wrapper-<?php echo esc_attr($post->ID); ?>" class="job-apply-internal-form-wrapper mfp-hide">
	<div class="inner">
		<h2 class="widget-title">
			<span><?php echo __('Apply for this job', 'wp-job-board-pro'); ?></span>
		</h2>

	    <form id="job-apply-internal-form-<?php echo esc_attr($post->ID); ?>" class="job-apply-internal-form" method="post" action="" enctype="multipart/form-data">
	    	<div class="row">
	    		<?php if ( is_array($cv_attachments) ) { ?>
			        <div class="col-sm-12">
				        <div class="form-group">
				            <?php
				            foreach ($cv_attachments as $id => $cv_url) {
						        $file_info = pathinfo($cv_url);
						        if ( $file_info ) {
						        	?>
						        	<label for="apply-internal-cv-<?php echo esc_attr($id); ?>">
						        		<input id="apply-internal-cv-<?php echo esc_attr($id); ?>" type="radio" name="apply_cv_id" value="<?php echo esc_attr($id); ?>">
						                <span class="icon_type">
						                    <?php if ( !empty($file_info['extension']) ) {
						                        switch ($file_info['extension']) {
						                            case 'doc':
						                            case 'docx':
						                                ?>
						                                <i class="flaticon-doc"></i>
						                                <?php
						                                break;
						                            
						                            case 'pdf':
						                                ?>
						                                <i class="flaticon-pdf"></i>
						                                <?php
						                                break;
						                            default:
						                                ?>
						                                <i class="flaticon-doc"></i>
						                                <?php
						                                break;
						                        }
						                    } ?>
						                </span>
						                <?php if ( !empty($file_info['filename']) ) { ?>
						                    <span class="filename"><?php echo esc_html($file_info['filename']); ?></span>
						                <?php } ?>
						                <?php if ( !empty($file_info['extension']) ) { ?>
						                    <span class="extension"><?php echo esc_html($file_info['extension']); ?></span>
						                <?php } ?>
						            </label>
						        	<?php
						        }
					        }
						    ?>

						    <div class="file-or-upload"><?php esc_html_e('or upload your CV', 'wp-job-board-pro'); ?></div>
				        </div><!-- /.form-group -->
				    </div>
				<?php } ?>

				<?php
				$cv_types = wp_job_board_pro_get_option('cv_file_types');
				$cv_types_str = !empty($cv_types) ? implode(', ', $cv_types) : '';
				?>

		        <div class="col-sm-12">
			     	<div class="form-group upload-file-btn-wrapper">
			            <input type="file" name="cv_file" data-file_types="<?php echo esc_attr(!empty($cv_types) ? implode('|', $cv_types) : ''); ?>">

			            <div class="label-can-drag">
							<div class="form-group group-upload">
						        <div class="upload-file-btn" data-text="<?php echo esc_attr(sprintf(esc_html__('Upload CV (%s)', 'wp-job-board-pro'), $cv_types_str)); ?>">
					            	<span class="text"><?php echo sprintf(esc_html__('Dodaj CV (%s)', 'wp-job-board-pro'), $cv_types_str); ?></span>
						        </div>
						    </div>
						</div>
			        </div>
		        </div><!-- /.form-group -->
		        <div class="col-sm-12">
			     	<div class="form-group space-30">
			            <textarea class="form-control style2" name="message" placeholder="<?php esc_attr_e( 'Message', 'wp-job-board-pro' ); ?>" required="required"></textarea>
			        </div>
		        </div><!-- /.form-group -->

		        
		        <?php
				$page_id = wp_job_board_pro_get_option('terms_conditions_page_id');
				$page_id = WP_Job_Board_Pro_Mixes::get_lang_post_id( $page_id, 'page');
				if ( !empty($page_id) ) {
					$page_url = $page_id ? get_permalink($page_id) : home_url('/');
				?>
					<div class="col-sm-12">
						<div class="form-group">
							<label for="register-terms-and-conditions">
								<input type="checkbox" name="terms_and_conditions" value="on" id="register-terms-and-conditions" required>
								<?php
									echo sprintf(__('Prihvatate naše <a href="%s" target="_blank">Uslove korišćenja i Politiku privatnosti</a>', 'wp-job-board-pro'), esc_url($page_url));
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
	        <button class="button btn btn-theme btn-block" name="apply-email"><?php echo esc_html__( 'Prijavi se na oglas', 'wp-job-board-pro' ); ?></button>
	    </form>
	</div>
</div>