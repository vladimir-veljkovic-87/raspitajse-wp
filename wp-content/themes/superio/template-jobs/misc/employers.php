<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<?php
	echo WP_Job_Board_Pro_Template_Loader::get_template_part('loop/employer/archive-inner', array('employers' => $employers));

	echo WP_Job_Board_Pro_Template_Loader::get_template_part('loop/employer/pagination', array('employers' => $employers));
?>