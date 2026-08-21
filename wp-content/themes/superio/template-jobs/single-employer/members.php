<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

$meta_obj = WP_Job_Board_Pro_Employer_Meta::get_instance($post->ID);

if ( $meta_obj->check_post_meta_exist('team_members') && ($team_members = $meta_obj->get_post_meta( 'team_members' )) ) {
?>
    <div id="job-employer-portfolio" class="employer-detail-portfolio">
    	<h4 class="title"><?php esc_html_e('Team Member', 'superio'); ?></h4>
    	<div class="row">
	        <?php foreach ($team_members as $member) { ?>
	        	<div class="col-sm-4 col-xs-6">
		            <div class="member-item">
		            	<div class="profile-image">
			            	<?php if ( !empty($member['profile_image']) ) { ?>
			            		<div class="image">
				                	<img src="<?php echo esc_url($member['profile_image']); ?>" alt="<?php esc_attr_e('Image', 'superio'); ?>">
				                </div>
				            <?php } ?>

				            <div class="social">
				            	<?php if ( !empty($member['facebook']) ) { ?>
				            		<a href="<?php echo esc_url($member['facebook']); ?>"><i class="fab fa-facebook-f"></i></a>
					            <?php } ?>
					            <?php if ( !empty($member['twitter']) ) { ?>
				            		<a href="<?php echo esc_url($member['twitter']); ?>"><i class="fab fa-twitter"></i></a>
					            <?php } ?>
					            <?php if ( !empty($member['google_plus']) ) { ?>
				            		<a href="<?php echo esc_url($member['google_plus']); ?>"><i class="fab fa-google-plus-g"></i></a>
					            <?php } ?>
					            <?php if ( !empty($member['linkedin']) ) { ?>
				            		<a href="<?php echo esc_url($member['linkedin']); ?>"><i class="fab fa-linkedin-in"></i></a>
					            <?php } ?>
					            <?php if ( !empty($member['dribbble']) ) { ?>
				            		<a href="<?php echo esc_url($member['dribbble']); ?>"><i class="fab fa-dribbble"></i></a>
					            <?php } ?>
				            </div>
				        </div>
				        <div class="content">
				            <?php if ( !empty($member['name']) ) { ?>
			            		<h3 class="title"><?php echo esc_html($member['name']); ?></h3>
				            <?php } ?>
				            <?php if ( !empty($member['designation']) ) { ?>
			            		<div class="designation"><?php echo esc_html($member['designation']); ?></div>
				            <?php } ?>
				            <?php if ( !empty($member['experience']) ) { ?>
			            		<div class="experience text-theme"><?php esc_html_e('Experience: ', 'superio'); ?><?php echo esc_html($member['experience']); ?></div>
				            <?php } ?>
			            </div>
		            </div>
	            </div>
	        <?php } ?>
	    </div>
    </div>
<?php }