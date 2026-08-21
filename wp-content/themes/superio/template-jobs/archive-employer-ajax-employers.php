<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$inner_style = superio_get_employers_inner_style();


$total = $employers->found_posts;
$per_page = $employers->query_vars['posts_per_page'];
$current = max( 1, $employers->get( 'paged', 1 ) );
$last  = min( $total, $per_page * $current );

$pre_page  = max( 0, ($employers->get( 'paged', 1 ) - 1 ) );
$i =  $per_page * $pre_page;
?>
<div class="results-count">
	<span class="last"><?php echo esc_html($last); ?></span>
</div>

<div class="items-wrapper">
	<?php
	$columns = superio_get_employers_columns();
	$bcol = $columns ? 12/$columns : 4;
	?>
	<?php while ( $employers->have_posts() ) : $employers->the_post(); ?>
		<div class="<?php echo esc_attr($columns > 1 ? 'col-sm-6' : 'col-sm-12'); ?> col-md-<?php echo esc_attr($bcol); ?> col-xs-12 <?php echo esc_attr($i%$columns == 0 ? 'md-clearfix lg-clearfix':''); ?> <?php echo esc_attr(($columns > 1 && $i%2 == 0) ? 'sm-clearfix':''); ?>">
			<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'employers-styles/inner-'.$inner_style ); ?>
		</div>
	<?php $i++; endwhile; ?>

</div>

<div class="apus-pagination-next-link"><?php next_posts_link( '&nbsp;', $employers->max_num_pages ); ?></div>