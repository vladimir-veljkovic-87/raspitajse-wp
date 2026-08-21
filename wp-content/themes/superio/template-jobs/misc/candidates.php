<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<?php
	echo WP_Job_Board_Pro_Template_Loader::get_template_part('loop/candidate/archive-inner', array('candidates' => $candidates));

	echo WP_Job_Board_Pro_Template_Loader::get_template_part('loop/candidate/pagination', array('candidates' => $candidates));
?>