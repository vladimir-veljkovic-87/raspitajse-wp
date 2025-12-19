<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

$meta_obj = WP_Job_Board_Pro_Employer_Meta::get_instance($post->ID);

if ( $meta_obj->check_post_meta_exist('team_members') && ($team_members = $meta_obj->get_post_meta( 'team_members' )) ) {
?>
    <div id="job-employer-team" class="employer-detail-portfolio">
    	<h4 class="title"><?php esc_html_e('Team Member', 'wp-job-board-pro'); ?></h4>
    	<div class="row row-36">
	        <?php foreach ($team_members as $member) { ?>
	        	<div class="col-xs-4">
		            <div class="member-item">
		            	<div class="profile-image">
			            	<?php if ( !empty($member['profile_image']) ) { ?>
			            		<div class="image">
				                	<img src="<?php echo esc_url($member['profile_image']); ?>" alt="">
				                </div>
				            <?php } ?>

				            <ul class="socials">
				            	<?php if ( !empty($member['facebook']) ) { ?>
				            		<li class="facebook"><a href="<?php echo esc_url($member['facebook']); ?>"><i class="fa fa-facebook"></i></a></li>
					            <?php } ?>
					            <?php if ( !empty($member['twitter']) ) { ?>
				            		<li class="twitter"><a href="<?php echo esc_url($member['twitter']); ?>"><i class="fa fa-twitter"></i></a></li>
					            <?php } ?>
					            <?php if ( !empty($member['google_plus']) ) { ?>
				            		<li class="google_plus"><a href="<?php echo esc_url($member['google_plus']); ?>"><i class="fa fa-google_plus"></i></a></li>
					            <?php } ?>
					            <?php if ( !empty($member['linkedin']) ) { ?>
				            		<li class="linkedin"><a href="<?php echo esc_url($member['linkedin']); ?>"><i class="fa fa-linkedin"></i></a></li>
					            <?php } ?>
					            <?php if ( !empty($member['dribbble']) ) { ?>
				            		<li class="dribbble"><a href="<?php echo esc_url($member['dribbble']); ?>"><i class="fa fa-dribbble"></i></a></li>
					            <?php } ?>
				            </ul>
				        </div>
			            <?php if ( !empty($member['name']) ) { ?>
		            		<h3 class="title"><?php echo esc_html($member['name']); ?></h3>
			            <?php } ?>
			            <?php if ( !empty($member['designation']) ) { ?>
		            		<div class="designation"><?php echo esc_html($member['designation']); ?></div>
			            <?php } ?>
			            <?php if ( !empty($member['experience']) ) { ?>
		            		<div class="experience"><?php esc_html_e('Experience: ', 'wp-job-board-pro'); ?><?php echo esc_html($member['experience']); ?></div>
			            <?php } ?>

			            <?php if ( !empty($member['description']) ) { ?>
		            		<div class="description"><?php echo esc_html($member['description']); ?></div>
			            <?php } ?>
		            </div>
	            </div>
	        <?php } ?>
	    </div>
    </div>
<?php }