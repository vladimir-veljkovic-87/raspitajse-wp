<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$user_id = WP_Job_Board_Pro_User::get_user_id();
$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);

if ( get_query_var( 'paged' ) ) {
    $paged = get_query_var( 'paged' );
} elseif ( get_query_var( 'page' ) ) {
    $paged = get_query_var( 'page' );
} else {
    $paged = 1;
}

$users = WP_Job_Board_Pro_Query::get_employer_employees($employer_id, array(
    'post_per_page' => get_option('posts_per_page'),
    'paged' => $paged
));

wp_enqueue_script('jquery-ui-autocomplete');
?>

<div class="employer-employees-member box-dashboard-wrapper">
	<div class="employer-employees-list inner-list p-bottom-0 clearfix">
		<h3 class="title"><?php esc_html_e('Employees', 'superio'); ?></h3>
		<div class="employer-employees-list-inner">
	        <?php
	        	if ( !empty($users) ) {
		        	$employee_style = apply_filters('wp-job-board-pro-employee-inner-list-team', 'inner-list-team');
		            foreach ($users as $user) {
		            	echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'employees-styles/'.$employee_style, array('userdata' => $user) );
		            }
	            }  else { ?>

				<div class="not-found"><?php esc_html_e('No employees found.', 'superio'); ?></div>

			<?php }  ?>
	    </div>
	    
	    <?php
		    if ( !empty($users) ) {
		    $all_users = WP_Job_Board_Pro_Query::get_employer_employees($employer_id, array(
			    'post_per_page' => -1,
			    'paged' => 1,
			    'fields' => 'ids'
			));
			$count_users = !empty($all_users) ? count($all_users) : 0;
			$max_num_pages = ceil($count_users/get_option('posts_per_page'));
		    WP_Job_Board_Pro_Mixes::custom_pagination2( array(
				'prev_text'     => esc_html__( 'Previous page', 'superio' ),
				'next_text'     => esc_html__( 'Next page', 'superio' ),
				'per_page' 		=> get_option('posts_per_page'),
				'current' 		=> $paged,
				'max_num_pages' => $max_num_pages,
			));
		} ?>
	</div>
	<!-- Form list -->
	<div class="employer-employee-form-wrapper inner-list no-margin">
		<h3 class="title"><?php esc_html_e('Add Employee', 'superio'); ?></h3>
		
		<form action="" method="get" class="employer-add-employees-form">

			<div class="row">
				<div class="col-xs-12 col-sm-6">
					<div class="form-group">
						<label for="register-username"><?php esc_html_e('Username', 'superio'); ?></label>
						<sup class="required-field">*</sup>
						<input type="text" class="form-control" name="username" id="register-username" placeholder="<?php esc_attr_e('Enter Username','superio'); ?>">
					</div>
				</div>
				<div class="col-xs-12 col-sm-6">
					<div class="form-group">
						<label for="register-email"><?php esc_html_e('Email', 'superio'); ?></label>
						<sup class="required-field">*</sup>
						<input type="text" class="form-control" name="email" id="register-email" placeholder="<?php esc_attr_e('Enter Email','superio'); ?>">
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-xs-12 col-sm-6">
					<div class="form-group">
						<label for="password"><?php esc_html_e('Password', 'superio'); ?></label>
						<sup class="required-field">*</sup>
						<input type="password" class="form-control" name="password" id="password" placeholder="<?php esc_attr_e('Enter Password','superio'); ?>">
					</div>
				</div>
				<div class="col-xs-12 col-sm-6">
				<div class="form-group">
						<label for="confirmpassword"><?php esc_html_e('Confirm Password', 'superio'); ?></label>
						<sup class="required-field">*</sup>
						<input type="password" class="form-control" name="confirmpassword" id="confirmpassword" placeholder="<?php esc_attr_e('Re-enter Password','superio'); ?>">
					</div>
				</div>
			</div>

			<div class="clearfix">
				<button class="search-submit btn btn-theme" name="submit"><?php echo esc_html__( 'Add Employee', 'superio' ); ?></button>
				<input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce( 'wp-job-board-pro-employer-add-employee-nonce' )); ?>">
			</div>
			
		</form>
	</div>
</div>