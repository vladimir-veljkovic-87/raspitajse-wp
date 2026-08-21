<?php
global $post;
?>

<div class="entry-blog-header text-center">
    <?php if (get_the_title()) { ?>
        <h1 class="entry-title">
            <?php the_title(); ?>
        </h1>
    <?php } ?>

    <ul class="top-detail-blog-info">
        <li class="author">
            <a href="<?php the_permalink(); ?>" class="author-post">
                <?php echo get_avatar(get_the_author_meta( 'ID' ), 30); ?>
                <span class="name-author"><?php echo get_the_author(); ?></span>
            </a>
        </li>
        <li>
            <a href="<?php the_permalink(); ?>"><?php the_time( get_option('date_format', 'd M, Y') ); ?></a>
        </li>
        <li>
            <?php superio_post_categories($post); ?>
        </li>
        <li>
            <span class="comments"><?php comments_number( esc_html__('0 Comments', 'superio'), esc_html__('1 Comment', 'superio'), esc_html__('% Comments', 'superio') ); ?></span>
        </li>
    </ul>
        
</div>