<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
extract( $args );

if ( !is_user_logged_in() || !class_exists('WP_Job_Board_Pro_User') ) {
    return;
}

extract( $args );
extract( $instance );

echo trim($before_widget);
$title = apply_filters('widget_title', $instance['title']);
if ( $title ) {
    echo trim($before_title)  . trim( $title ) . $after_title;
}
$user_id = get_current_user_id();
if ( WP_Job_Board_Pro_User::is_employer($user_id) ) {
    $employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);
    
    $title = get_the_title($employer_id);
    $post = get_post($employer_id);
    ob_start();
    superio_employer_display_logo($post);
    $logo = ob_get_clean();

    $location = superio_employer_display_short_location($post, 'no-title', false);

    if ($nav_menu_employer) {
        $term = get_term_by( 'slug', $nav_menu_employer, 'nav_menu' );
        if ( !empty($term) ) {
            $nav_menu_id = $term->term_id;
        }
    }
} elseif ( method_exists('WP_Job_Board_Pro_User', 'is_employee') && WP_Job_Board_Pro_User::is_employee($user_id) ) {
    $user_id = WP_Job_Board_Pro_User::get_user_id();
    if ( empty($user_id) ) {
        return;
    }
    $employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);
    $title = get_the_title($employer_id);
    $post = get_post($employer_id);
    ob_start();
    superio_employer_display_logo($post);
    $logo = ob_get_clean();

    $location = superio_employer_display_short_location($post, 'no-title', false);

    if ($nav_menu_employee) {
        $term = get_term_by( 'slug', $nav_menu_employee, 'nav_menu' );
        if ( !empty($term) ) {
            $nav_menu_id = $term->term_id;
        }
    }
} elseif ( WP_Job_Board_Pro_User::is_candidate() ) {
    $candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);
    $title = get_the_title($candidate_id);
    $post = get_post($candidate_id);
    ob_start();
    superio_candidate_display_logo($post);
    $logo = ob_get_clean();

    $location = superio_candidate_display_short_location($post, 'no-title', false);

    if ($nav_menu_candidate) {
        $term = get_term_by( 'slug', $nav_menu_candidate, 'nav_menu' );
        if ( !empty($term) ) {
            $nav_menu_id = $term->term_id;
        }
    }
    $profile_percents = WP_Job_Board_Pro_User::compute_profile_percent($candidate_id);
} else {
    return;
}
?>

<div class="user-short-profile-top clearfix flex-middle <?php echo esc_attr( (WP_Job_Board_Pro_User::is_candidate($user_id))? 'is_candidate': ''); ?>">
    <?php
        if ( !empty($logo) ) {
            ?>
            <div class="user-logo"><?php echo trim($logo); ?></div>
            <?php
        }
    ?>
    <div class="inner">
        <?php if ( $title ) { ?>
            <h3 class="title">
                <a href="<?php echo esc_url(get_permalink($post)); ?>">
                    <?php echo trim($title); ?>
                </a>
            </h3>
        <?php } ?>
        <?php echo trim($location); ?>

        <?php if ( $post->post_status == 'publish' ) { ?>
            <div class="view-profile">
                <a href="<?php echo esc_url(get_permalink($post)); ?>" class="btn btn-theme btn-xs"><?php esc_html_e('View Profile', 'superio'); ?></a>
            </div>
        <?php } ?>
    </div>
</div>

<?php if ( !empty($nav_menu_id) ) { ?>
    <div class="user_short_profile">
        <?php
            $args = array(
                'menu'        => $nav_menu_id,
                'container_class' => 'navbar-collapse no-padding',
                'menu_class' => 'menu_short_profile',
                'fallback_cb' => '',
                'walker' => new Superio_Nav_Menu()
            );
            wp_nav_menu($args);
        ?>
    </div>
<?php } ?>

<?php if ( !empty($profile_percents) ) { ?>
    <div class="skill-percents">
        <h4><?php esc_html_e('Skills Percentage:', 'superio'); ?> <span><?php echo esc_html($profile_percents['percent']*100).'%'; ?></span></h4>
        <div class="skill-process">
            <span style="width:<?php echo esc_html($profile_percents['percent']*100); ?>%;"></span>
        </div>
        <?php if ( !empty($profile_percents['empty_fields']) ) { ?>
            <div class="value-percents">
                <?php
                    $allowed_html_array = array( 'strong' => array('class' => array()) );

                    if ( count($profile_percents['empty_fields']) < 4 ) {
                        echo sprintf(wp_kses(__('Put value for %s field to increase your skill up to <strong class="text-info">"%s"</strong>', 'superio'), $allowed_html_array), '<span class="text-theme">"'.implode('"</span>, <span class="text-theme">"', $profile_percents['empty_fields']).'"</span>', ((1 - $profile_percents['percent'])*100).'%' );
                    } else {
                        echo sprintf(wp_kses(__('Put value for resume, profile fields to increase your skill up to <strong class="text-info">"%s"</strong>', 'superio'), $allowed_html_array), ((1 - $profile_percents['percent'])*100).'%' );
                    }
                ?>
            </div>
        <?php } else { ?>
        <?php } ?>
    </div>
<?php } ?>
<?php echo trim($after_widget);