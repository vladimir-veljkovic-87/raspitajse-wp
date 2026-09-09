<?php

function superio_candidate_display_logo($post, $link = true, $thumbnail = 'thumbnail', $attr = '') {
	?>
    <div class="candidate-logo">
        <?php if ( $link ) { ?>
            <a href="<?php echo esc_url( get_permalink($post) ); ?>">
        <?php } ?>
                <?php if ( has_post_thumbnail($post->ID) ) { ?>
                    <?php
                    $image_id = get_post_thumbnail_id($post);
                    echo superio_get_attachment_thumbnail($image_id, $thumbnail, false, $attr);
                    ?>
                <?php } else { ?>
                    <img src="<?php echo esc_url(superio_candidate_placeholder_img_src()); ?>" alt="<?php echo esc_attr(get_the_title($post->ID)); ?>">
                <?php } ?>
        <?php if ( $link ) { ?>
            </a>
        <?php } ?>
    </div>
    <?php
}

function superio_candidate_name($post) {
    $title = get_the_title($post);
    if ( !WP_Job_Board_Pro_Candidate::check_restrict_view_contact_info($post) && wp_job_board_pro_get_option('restrict_contact_candidate_name', 'on') == 'on' ) {
        $title = superio_hide_string($title);
    }

    return $title;
}

function superio_candidate_display_categories($post, $display_type = 'no-icon') {
	$categories = get_the_terms( $post->ID, 'candidate_category' );
	if ( $categories ) {
		?>
		<div class="candidate-category">
			<?php if ($display_type == 'icon') { ?>
				<i class="flaticon-briefcase-1"></i>
			<?php } ?>
            <?php $i=1; foreach ($categories as $term) { ?>
                <a href="<?php echo get_term_link($term); ?>"><?php echo trim($term->name); ?></a><?php echo esc_html( $i < count($categories) ? ', ' : '' ); ?>
            <?php $i++; } ?>
        </div>
		<?php
    }
}

function superio_candidate_display_short_location($post, $display_type = 'no-icon-title', $echo = true) {
    $locations = get_the_terms( $post->ID, 'candidate_location' );
    ob_start();
    if ( $locations ) {
        $terms = array();
        superio_locations_walk($locations, 0, $terms);
        if ( empty($terms) ) {
            $terms = $locations;
        } else {
            $terms = array_reverse($terms, true);
        }
        if ( $display_type == 'icon' ) {
            ?>
            <div class="candidate-location with-icon"><i class="flaticon-location"></i>
            <?php
        } elseif ( $display_type == 'title' ) {
            ?>
            <div class="candidate-location with-title"><strong><?php esc_html_e('Location:', 'superio'); ?></strong>
            <?php
        } else {
            ?>
            <div class="candidate-location">
            <?php
        }

            $i=1; foreach ($terms as $term) { ?>
                <a href="<?php echo get_term_link($term); ?>"><?php echo trim($term->name); ?></a><?php echo esc_html( $i < count($terms) ? ', ' : '' ); ?>
            <?php $i++; } ?>

        </div>
        <?php
    }
    $output = ob_get_clean();
    if ( $echo ) {
        echo trim($output);
    } else {
        return $output;
    }
}

function superio_candidate_display_full_location($post, $display_type = 'no-icon-title', $echo = true) {
	$obj_candidate_meta = WP_Job_Board_Pro_Candidate_Meta::get_instance($post->ID);

    $location = $obj_candidate_meta->get_post_meta( 'address' );
    if ( empty($location) ) {
        $location = $obj_candidate_meta->get_post_meta( 'map_location_address' );
    }
	ob_start();
	if ( $location ) {
		
		if ( $display_type == 'icon' ) {
			?>
			<div class="candidate-location with-icon"><i class="flaticon-location"></i> <?php echo trim($location); ?></div>
			<?php
		} elseif ( $display_type == 'title' ) {
			?>
			<div class="candidate-location with-title">
				<strong><?php esc_html_e('Location:', 'superio'); ?></strong> <span><?php echo trim($location); ?></span>
			</div>
			<?php
		} else {
			?>
			<div class="candidate-location"><?php echo trim($location); ?></div>
			<?php
		}
    }
    $output = ob_get_clean();
    if ( $echo ) {
    	echo trim($output);
    } else {
    	return $output;
    }
}

function superio_candidate_display_job_title($post) {
    $obj_candidate_meta = WP_Job_Board_Pro_Candidate_Meta::get_instance($post->ID);

    $job_title = $obj_candidate_meta->get_post_meta( 'job_title' );

	if ( $job_title ) { ?>
        <div class="candidate-job">
            <?php echo trim($job_title); ?>
        </div>
    <?php }
}

function superio_candidate_display_featured_icon($post,$display_type = 'text') {
    $obj_candidate_meta = WP_Job_Board_Pro_Candidate_Meta::get_instance($post->ID);

    $featured = $obj_candidate_meta->get_post_meta( 'featured' );
	if ( $featured ) { ?>
        
        <?php if($display_type == 'icon') { ?>
            <span class="featured" data-toggle="tooltip" title="<?php esc_attr_e('featured', 'superio'); ?>"><i class="ti-crown"></i></span>
        <?php }else{ ?>
            <span class="featured-text"><?php esc_html_e('Featured', 'superio'); ?></span>
        <?php } ?>

    <?php }
}

function superio_candidate_display_urgent_icon($post) {
    $obj_candidate_meta = WP_Job_Board_Pro_Candidate_Meta::get_instance($post->ID);

    $urgent = $obj_candidate_meta->get_post_meta( 'urgent' );
	if ( $urgent ) { ?>
        <span class="urgent"><?php esc_html_e('Urgent', 'superio'); ?></span>
    <?php }
}

function superio_candidate_display_phone($post, $echo = true, $force_show_phone = false) {
	$phone = WP_Job_Board_Pro_Candidate::get_display_phone( $post->ID );
	ob_start();
	if ( $phone ) { ?>
        <div class="candidate-phone">
            <?php superio_display_phone($phone, '', $force_show_phone); ?>
        </div>
    <?php }
    $output = ob_get_clean();
    if ( $echo ) {
    	echo trim($output);
    } else {
    	return $output;
    }
}

function superio_candidate_display_email($post, $show_icon = true, $echo = true) {
	$email = WP_Job_Board_Pro_Candidate::get_display_email( $post->ID );
	ob_start();
	if ( $email ) { ?>
        <div class="candidate-email">
            <?php if ( $show_icon ) { ?>
                <i class="flaticon-envelope"></i>
            <?php } ?>
            <?php echo trim($email); ?>
        </div>
    <?php }
    $output = ob_get_clean();
    if ( $echo ) {
    	echo trim($output);
    } else {
    	return $output;
    }
}

function superio_candidate_display_salary($post, $display_type = 'no-icon-title', $echo = true) {
	$salary = WP_Job_Board_Pro_Candidate::get_salary_html($post->ID);
	ob_start();
	if ( $salary ) {
		if ( $display_type == 'icon' ) {
			?>
			<div class="candidate-salary with-icon"><i class="flaticon-money-1"></i> <?php echo trim($salary); ?></div>
			<?php
		} elseif ( $display_type == 'title' ) {
			?>
			<div class="candidate-salary with-title">
				<strong><?php esc_html_e('Salary:', 'superio'); ?></strong> <span><?php echo trim($salary); ?></span>
			</div>
			<?php
		} else {
			?>
			<div class="candidate-salary"><?php echo trim($salary); ?></div>
			<?php
		}
    }
    $output = ob_get_clean();
    if ( $echo ) {
    	echo trim($output);
    } else {
    	return $output;
    }
}

function superio_candidate_display_birthday($post, $display_type = 'no-icon-title', $echo = true) {
    $obj_candidate_meta = WP_Job_Board_Pro_Candidate_Meta::get_instance($post->ID);

    $birthday = $obj_candidate_meta->get_post_meta( 'founded_date' );
	ob_start();
	if ( $birthday ) {
		$birthday = strtotime($birthday);
		$birthday = date_i18n(get_option('date_format'), $birthday);
		if ( $display_type == 'icon' ) {
			?>
			<div class="candidate-birthday with-icon"><i class="flaticon-wall-clock"></i> <?php echo trim($birthday); ?></div>
			<?php
		} elseif ( $display_type == 'title' ) {
			?>
			<div class="candidate-birthday with-title">
				<strong><?php esc_html_e('Birthday:', 'superio'); ?></strong> <span><?php echo trim($birthday); ?></span>
			</div>
			<?php
		} else {
			?>
			<div class="candidate-birthday"><?php echo trim($birthday); ?></div>
			<?php
		}
    }
    $output = ob_get_clean();
    if ( $echo ) {
    	echo trim($output);
    } else {
    	return $output;
    }
}

function superio_candidate_display_shortlist_btn($html, $post_id) {
	if ( WP_Job_Board_Pro_Employer::check_added_shortlist($post_id) ) {
        $classes = 'btn-action-job added btn-added-candidate-shortlist btn-follow';
        $nonce = wp_create_nonce( 'wp-job-board-pro-remove-candidate-shortlist-nonce' );
        $text = esc_html__('Shortlisted', 'superio');
    } else {
        $classes = 'btn-action-job btn-add-candidate-shortlist btn-follow';
        $nonce = wp_create_nonce( 'wp-job-board-pro-add-candidate-shortlist-nonce' );
        $text = esc_html__('Shortlist', 'superio');
    }
    ob_start();
    ?>
    <a title="<?php echo esc_attr($text); ?>" href="javascript:void(0);" class="<?php echo esc_attr($classes); ?>" data-candidate_id="<?php echo esc_attr($post_id); ?>" data-nonce="<?php echo esc_attr($nonce); ?>"><span class="text hidden"><?php echo esc_html($text); ?></span><i class="flaticon-bookmark"></i></a>
    <?php
    $html = ob_get_clean();
    return $html;
}
add_filter('wp-job-board-pro-candidate-display-shortlist-btn', 'superio_candidate_display_shortlist_btn', 10, 2);

function superio_candidate_display_tags($post, $display_type = 'no-title', $echo = true) {
    $tags = get_the_terms( $post->ID, 'candidate_tag' );
    ob_start();
    if ( $tags ) {
        ?>
            <?php
            if ( $display_type == 'title' ) {
                ?>
                <div class="candidate-tags">
                <strong><?php esc_html_e('Tagged as:', 'superio'); ?></strong>
                <?php
            } else {
                ?>
                <div class="candidate-tags">
                <?php
            }
                foreach ($tags as $term) {
                    ?>
                        <a class="tag-candidate" href="<?php echo get_term_link($term); ?>"><?php echo esc_html($term->name); ?></a>
                    <?php
                }
            ?>
            </div>
        <?php
    }

    $output = ob_get_clean();
    if ( $echo ) {
        echo trim($output);
    } else {
        return $output;
    }
}

function superio_candidate_display_tags_version2($post, $display_type = 'no-title', $echo = true, $limit = 3) {
    $tags = get_the_terms( $post->ID, 'candidate_tag' );
    ob_start();
    $i = 1;
    if ( $tags ) {
        ?>
            <?php
            if ( $display_type == 'title' ) {
                ?>
                <div class="candidate-tags">
                <strong><?php esc_html_e('Tagged as:', 'superio'); ?></strong>
                <?php
            } else {
                ?>
                <div class="candidate-tags">
                <?php
            }
                foreach ($tags as $term) {
                    if ( $limit > 0 && $i > $limit ) {
                        break;
                    }
                    ?>
                        <a class="tag-candidate" href="<?php echo get_term_link($term); ?>"><?php echo esc_html($term->name); ?></a>
                    <?php $i++;
                }
            ?>

            <?php if ( $limit > 0 && count($tags) > $limit ) { ?>
                <span class="count-more-tags"><?php echo sprintf(esc_html__('+%d', 'superio'), (count($tags) - $limit) ); ?></span>
            <?php } ?>
            
            </div>
        <?php
    }

    $output = ob_get_clean();
    if ( $echo ) {
        echo trim($output);
    } else {
        return $output;
    }
}

function superio_candidate_item_map_meta($post) {
    $obj_candidate_meta = WP_Job_Board_Pro_Candidate_Meta::get_instance($post->ID);

    $latitude = $obj_candidate_meta->get_post_meta( 'map_location_latitude' );
    $longitude = $obj_candidate_meta->get_post_meta( 'map_location_longitude' );

    $url = '';
    if ( has_post_thumbnail($post->ID) ) {
        $url = get_the_post_thumbnail_url($post->ID, 'thumbnail');
    }
    
    echo 'data-latitude="'.esc_attr($latitude).'" data-longitude="'.esc_attr($longitude).'" data-img="'.esc_url($url).'"';
}


function superio_candidate_display_meta($post, $meta_key, $icon = '', $show_title = false, $suffix = '', $echo = false) {
    $obj_job_meta = WP_Job_Board_Pro_Candidate_Meta::get_instance($post->ID);

    $value = $obj_job_meta->get_post_meta( $meta_key );
    
    ob_start();
    if ( $obj_job_meta->check_post_meta_exist($meta_key) && ($value = $obj_job_meta->get_post_meta( $meta_key )) ) {
        ?>
        <div class="candidate-meta with-<?php echo esc_attr($show_title ? 'icon-title' : 'icon'); ?>">

            <div class="candidate-meta">
                <?php if ( !empty($show_title) ) {
                    $title = $obj_job_meta->get_post_meta_title($meta_key);
                ?>
                    <span class="title-meta">
                        <?php echo esc_html($title); ?>
                    </span>
                <?php } ?>

                <?php if ( !empty($icon) ) { ?>
                    <i class="<?php echo esc_attr($icon); ?>"></i>
                <?php } ?>
                <?php
                    if ( is_array($value) ) {
                        echo implode(', ', $value);
                    } else {
                        echo esc_html($value);
                    }
                ?>
                <?php echo trim($suffix); ?>
            </div>

        </div>
        <?php
    }
    $output = ob_get_clean();
    if ( $echo ) {
        echo trim($output);
    } else {
        return $output;
    }
}

function superio_candidate_display_custom_field_meta($post, $meta_key, $icon = '', $show_title = false, $suffix = '', $echo = false) {
    $obj_job_meta = WP_Job_Board_Pro_Candidate_Meta::get_instance($post->ID);

    ob_start();
    if ( $obj_job_meta->check_custom_post_meta_exist($meta_key) && ($value = $obj_job_meta->get_custom_post_meta( $meta_key )) ) {
        ?>
        <div class="candidate-meta with-<?php echo esc_attr($show_title ? 'icon-title' : 'icon'); ?>">

            <div class="candidate-meta">
                <?php if ( !empty($show_title) ) {
                    $title = $obj_job_meta->get_custom_post_meta_title($meta_key);
                ?>
                    <span class="title-meta">
                        <?php echo esc_html($title); ?>
                    </span>
                <?php } ?>

                <?php if ( !empty($icon) ) { ?>
                    <i class="<?php echo esc_attr($icon); ?>"></i>
                <?php } ?>
                <?php
                    if ( is_array($value) ) {
                        echo implode(', ', $value);
                    } else {
                        echo esc_html($value);
                    }
                ?>
                <?php echo trim($suffix); ?>
            </div>

        </div>
        <?php
    }
    $output = ob_get_clean();
    if ( $echo ) {
        echo trim($output);
    } else {
        return $output;
    }
}


// Candidate Archive hooks
function superio_candidate_display_filter_btn() {
    $layout_type = superio_get_candidates_layout_type();
    $sidebar = superio_get_candidates_filter_sidebar();
    if ( $layout_type == 'half-map' && is_active_sidebar($sidebar) ) {
        ?>
        <div class="filter-in-sidebar-wrapper">
            <span class="filter-in-sidebar btn-theme-light btn"><i class="ti-filter"></i><span class="text"><?php esc_html_e( 'Filter', 'superio' ); ?></span></span>
        </div>
        <?php
    }
}

function superio_candidate_display_per_page_form($wp_query) {
    $total              = $wp_query->found_posts;
    $per_page           = $wp_query->get( 'posts_per_page' );
    $_per_page          = wp_job_board_pro_get_option('number_candidates_per_page', 12);

    // Generate per page options
    $products_per_page_options = array();
    while ( $_per_page < $total ) {
        $products_per_page_options[] = $_per_page;
        $_per_page = $_per_page * 2;
    }

    if ( empty( $products_per_page_options ) ) {
        return;
    }

    $products_per_page_options[] = -1;

    ?>
    <form method="get" action="<?php echo esc_url(WP_Job_Board_Pro_Mixes::get_candidates_page_url()); ?>" class="form-superio-ppp">
        
    	<select name="candidates_ppp" onchange="this.form.submit()">
            <?php foreach( $products_per_page_options as $key => $value ) { ?>
                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $per_page ); ?>>
                	<?php
                		if ( $value == -1 ) {
                			esc_html_e( 'All', 'superio' );
                		} else {
                			echo sprintf( esc_html__( '%s Per Page', 'superio' ), $value );
                		}
                	?>
                </option>
            <?php } ?>
        </select>

        <input type="hidden" name="paged" value="1" />
		<?php WP_Job_Board_Pro_Mixes::query_string_form_fields( null, array( 'candidates_ppp', 'submit', 'paged' ) ); ?>
    </form>
    <?php
}

function superio_candidate_show_invite($post_id) {
    if ( version_compare(WP_JOB_BOARD_PRO_PLUGIN_VERSION, '1.2.17', '>=') ) {
        $show = superio_get_config('show_candidate_invite_apply_job', 'always');

        if ( $show == 'always' || ($show == 'show_loggedin' && is_user_logged_in() && WP_Job_Board_Pro_User::is_employer(get_current_user_id()) ) || ($show == 'none-register-employer' && (!is_user_logged_in() || WP_Job_Board_Pro_User::is_employer(get_current_user_id())) ) ) {
            WP_Job_Board_Pro_Candidate::display_invite_btn($post_id);
        }
    }
}