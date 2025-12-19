<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

$categories = get_the_terms( $post->ID, 'employer_category' );
$address = get_post_meta( $post->ID, WP_JOB_BOARD_PRO_EMPLOYER_PREFIX . 'address', true );


?>

<?php do_action( 'wp_job_board_pro_before_employer_content', $post->ID ); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php if ( has_post_thumbnail() ) { ?>
        <div class="employer-thumbnail">
            <?php echo get_the_post_thumbnail( $post->ID, 'thumbnail' ); ?>
        </div>
    <?php } ?>
    <div class="employer-information">
    	
		<?php the_title( sprintf( '<h2 class="entry-title employer-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>

        <?php if ( $categories ) { ?>
            <?php foreach ($categories as $term) { ?>
                <a href="<?php echo get_term_link($term); ?>"><?php echo $term->name; ?></a>
            <?php } ?>
        <?php } ?>

        <?php if ( $address ) { ?>
            <div class="employer-location"><?php echo $address; ?></div>
        <?php } ?>

	</div>
</article><!-- #post-## -->

<?php do_action( 'wp_job_board_pro_after_employer_content', $post->ID ); ?>