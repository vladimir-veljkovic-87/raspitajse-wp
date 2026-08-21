<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = array(
	'candidates' => $candidates,
);
?>

<?php
	echo WP_Job_Board_Pro_Template_Loader::get_template_part('loop/candidate/archive-inner', $args);
?>

<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part('loop/candidate/pagination', array('candidates' => $candidates) ); ?>
