<?php

function superio_employer_display_logo($post, $link = true, $thumbnail = 'thumbnail', $attr = '') {
	?>
    <div class="employer-logo">
    	<?php if ( $link ) { ?>
        	<a href="<?php echo esc_url( get_permalink($post) ); ?>">
        <?php } ?>
	        	<?php if ( has_post_thumbnail($post->ID) ) { ?>
	                <?php
                    $image_id = get_post_thumbnail_id($post);
                    echo superio_get_attachment_thumbnail($image_id, $thumbnail, false, $attr);
                    ?>
	            <?php } else { ?>
	            	<img src="<?php echo esc_url(superio_placeholder_img_src()); ?>" alt="<?php echo esc_attr(get_the_title($post->ID)); ?>">
	            <?php } ?>
        <?php if ( $link ) { ?>
        	</a>
        <?php } ?>
    </div>
    <?php
}

function superio_employer_name($post) {
    $title = get_the_title($post);
    if ( !WP_Job_Board_Pro_Employer::check_restrict_view_contact_info($post) && wp_job_board_pro_get_option('restrict_contact_employer_name', 'on') == 'on' ) {
        $title = superio_hide_string($title);
    }

    return $title;
}

function superio_employer_display_short_location($post, $display_type = 'no-icon-title', $echo = true) {
    $locations = get_the_terms( $post->ID, 'employer_location' );
    ob_start();
    if ( $locations ) {
        $terms = array();
        superio_locations_walk($locations, 0, $terms);
        if ( empty($terms) ) {
        	$terms = $locations;
        } else {
        	$terms = array_reverse($terms, true);
        }
            ?>
	        <?php if ( $display_type == 'title' ) {
	        	$meta_obj = WP_Job_Board_Pro_Employer_Meta::get_instance($post->ID);
	        	$title = $meta_obj->get_post_meta_title('location');
	            ?>
	            <div class="employer-location"><h3 class="title"><?php echo trim($title); ?>:</h3>

			<?php } else { ?>
	            <div class="employer-location">
	        <?php } ?> 
	        	<div class="value">
		        	<?php if ( $display_type == 'icon' ) { ?>
	            		<i class="flaticon-location"></i>
	            	<?php } ?>

		        	<?php $i=1; foreach ($terms as $term) { ?>
		                <a href="<?php echo get_term_link($term); ?>"><?php echo trim($term->name); ?></a><?php echo esc_html( $i < count($terms) ? ', ' : '' ); ?>
		            <?php $i++; } ?>
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

function superio_employer_display_full_location($post, $display_type = 'no-icon-title', $echo = true) {
	$meta_obj = WP_Job_Board_Pro_Employer_Meta::get_instance($post->ID);

	$location = $meta_obj->get_post_meta( 'address' );
	if ( empty($location) ) {
		$location = $meta_obj->get_post_meta( 'map_location_address' );
	}
	ob_start();
	if ( $location ) {
		
		if ( $display_type == 'icon' ) {
			?>
			<div class="employer-location with-icon"><i class="flaticon-location"></i> <?php echo trim($location); ?></div>
			<?php
		} elseif ( $display_type == 'title' ) {
			$meta_obj = WP_Job_Board_Pro_Employer_Meta::get_instance($post->ID);
        	$title = $meta_obj->get_post_meta_title('location');
			?>
			<div class="employer-location with-title">
				<strong><?php echo trim($title); ?></strong> <span><?php echo trim($location); ?></span>
			</div>
			<?php
		} else {
			?>
			<div class="employer-location"><?php echo trim($location); ?></div>
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

function superio_employer_display_open_position($post, $show_url = false) {
	$user_id = WP_Job_Board_Pro_User::get_user_by_employer_id($post->ID);
	$args = array(
	        'post_type' => 'job_listing',
	        'post_per_page' => 1,
	        'post_status' => 'publish',
	        'fields' => 'ids',
	        'author' => $user_id
	    );
	$jobs = WP_Job_Board_Pro_Query::get_posts($args);
	$count_jobs = $jobs->found_posts;
	
	?>
	<span class="wrapper-open-job">
		<?php if ($show_url) { ?>
			<a href="<?php echo esc_url(get_permalink($post)); ?>" class="open-job">
		<?php } ?>
        <?php echo sprintf(_n('Otvorena pozicija - <span>%s</span>', 'Otvorene pozicije - <span>%s</span>', intval($count_jobs), 'superio'), intval($count_jobs)); ?>
        <?php if ($show_url) { ?>
			</a>
		<?php } ?>
    </span>
    <?php
}

function superio_employer_display_nb_jobs($post, $show_url = false) {
	$user_id = WP_Job_Board_Pro_User::get_user_by_employer_id($post->ID);
	$args = array(
	        'post_type' => 'job_listing',
	        'post_per_page' => 1,
	        'post_status' => 'publish',
	        'fields' => 'ids',
	        'author' => $user_id
	    );
	$jobs = WP_Job_Board_Pro_Query::get_posts($args);
	$count_jobs = $jobs->found_posts;
	
	?>
	<div class="nb-job">
		<?php if ($show_url) { ?>
			<a href="<?php echo esc_url(get_permalink($post)); ?>">
		<?php } ?>
        <?php echo sprintf(_n('<span class="text">Otvorena pozicija</span> - <span>%d</span>', '<span class="text">Otvorene pozicije</span> - <span>%d</span>', intval($count_jobs), 'superio'), intval($count_jobs)); ?>
        <?php if ($show_url) { ?>
			</a>
		<?php } ?>
    </div>
    <?php
}

function superio_employer_display_nb_reviews($post) {
	if ( superio_check_employer_candidate_review($post) ) {
		$employer_id = $post->ID;
		$total_reviews = WP_Job_Board_Pro_Review::get_total_reviews($employer_id);
		$total_reviews_display = $total_reviews ? WP_Job_Board_Pro_Mixes::format_number($total_reviews) : 0;
		?>
		<div class="nb_reviews">
	        <?php echo sprintf(_n('<span class="text-green">%d</span> <span class="text">Recenzija</span>', '<span class="text-green">%d</span> <span class="text">Recenzije</span>', intval($total_reviews), 'superio'), $total_reviews_display); ?>
	    </div>
	    <?php
	}
}

function superio_employer_display_nb_views($post) {
	$meta_obj = WP_Job_Board_Pro_Employer_Meta::get_instance($post->ID);

	$views = $meta_obj->get_post_meta( 'views_count' );
	$views_display = $views ? WP_Job_Board_Pro_Mixes::format_number($views) : 0;
	?>
	<div class="nb_views">
        <?php echo sprintf(_n('<span class="text-blue">%d</span> <span class="text">Pregled</span>', '<span class="text-blue">%d</span> <span class="text">Pregleda</span>', intval($views), 'superio'), $views_display); ?>
    </div>
    <?php
}

function superio_employer_display_featured_icon($post, $display_type = 'icon') {
	$meta_obj = WP_Job_Board_Pro_Employer_Meta::get_instance($post->ID);

	$featured = $meta_obj->get_post_meta( 'featured' );
	if ( $featured ) {
		if ( $display_type == 'icon' ) {
			?>
	        <span class="featured" data-toggle="tooltip" title="<?php esc_attr_e('Istaknuto', 'superio'); ?>"><i class="flaticon-tick"></i></span>
		    <?php
    	} else {
    		?>
    		<span class="featured-text"><?php esc_html_e('Istaknuto', 'superio'); ?></span>
    		<?php
    	}
	}
}

function superio_employer_display_phone($employer_id, $icon = 'fa fa-phone', $title = false, $echo = true) {
	$post = get_post($employer_id);
	$phone = WP_Job_Board_Pro_Employer::get_display_phone( $post );
	ob_start();
	if ( $phone ) {
		?>
		<div class="job-phone">
			<?php if ( $title ) {
				$meta_obj = WP_Job_Board_Pro_Employer_Meta::get_instance($post->ID);
				$title = $meta_obj->get_post_meta_title('phone');
			?>
				<h3 class="title"><?php echo trim($title); ?>:</h3>
			<?php } ?>
			<div class="value">
				<?php superio_display_phone($phone, $icon); ?>
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

function superio_employer_display_email($employer_id, $display_type = 'icon', $echo = true) {
	$post = get_post($employer_id);
	$email = WP_Job_Board_Pro_Employer::get_display_email( $post );
	ob_start();
	if ( $email ) {
		?>
		<div class="job-email">
			<?php if ( $display_type == 'title' ) {
				$meta_obj = WP_Job_Board_Pro_Employer_Meta::get_instance($post->ID);
				$title = $meta_obj->get_post_meta_title('email');
			?>
				<h3 class="title"><?php echo trim($title); ?>:</h3>
			<?php } ?>
			<div class="value">
				<?php if ( $display_type == 'icon' ) { ?>
					<i class="flaticon-envelope"></i>
				<?php } ?>
				<?php echo trim($email); ?>
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
function superio_employer_display_category($employer_id, $display_type = 'no-icon') {
	$categories = get_the_terms( $employer_id, 'employer_category' );
	if ( $categories ) {
		?>
		<?php if($display_type == "title"){
			$meta_obj = WP_Job_Board_Pro_Employer_Meta::get_instance($employer_id);
			$title = $meta_obj->get_post_meta_title('category');
		?> 
			<div class="job-category"><h3 class="title"><?php echo trim($title); ?>:</h3>
		<?php } else { ?>
			<div class="job-category">
    	<?php } ?>
    		<div class="value">
    			<?php if($display_type == "icon"){ ?> 
					<i class="flaticon-briefcase-1"></i>
				<?php } ?> 
	    		<?php
	    		$i=1;
				foreach ($categories as $term) {
					?>
		            	<a class="category-employer" href="<?php echo get_term_link($term); ?>"><?php echo esc_html($term->name); ?></a><?php echo esc_html( $i < count($categories) ? ', ' : '' ); ?>
		        	<?php
		        	$i++;
		    	} ?>
	    	</div>
    	</div>
    	<?php
    }
}

function superio_employer_display_follow_btn($employer_id) {
	if ( WP_Job_Board_Pro_Candidate::check_following($employer_id) ) {
		$classes = 'btn-unfollow-employer';
		$nonce = wp_create_nonce( 'wp-job-board-pro-unfollow-employer-nonce' );
		$text = esc_html__('Following', 'superio');
	} else {
		$classes = 'btn-follow-employer';
		$nonce = wp_create_nonce( 'wp-job-board-pro-follow-employer-nonce' );
		$text = esc_html__('Follow us', 'superio');
	}
	?>
	<a href="javascript:void(0)" class="btn-action-job button btn-follow <?php echo esc_attr($classes); ?>" data-employer_id="<?php echo esc_attr($employer_id); ?>" data-nonce="<?php echo esc_attr($nonce); ?>"><i class="flaticon-bookmark"></i><span class="text"><?php echo esc_html($text); ?></span></a>
	<?php
}

function superio_employer_item_map_meta($post) {
	$meta_obj = WP_Job_Board_Pro_Employer_Meta::get_instance($post->ID);

	$latitude = $meta_obj->get_post_meta( 'map_location_latitude' );
	$longitude = $meta_obj->get_post_meta( 'map_location_longitude' );

	$url = '';
    if ( has_post_thumbnail($post->ID) ) {
        $url = get_the_post_thumbnail_url($post->ID, 'thumbnail');
    }

	echo 'data-latitude="'.esc_attr($latitude).'" data-longitude="'.esc_attr($longitude).'" data-img="'.esc_url($url).'"';
}

function superio_employer_display_meta($post, $meta_key, $icon = '', $show_title = false, $suffix = '', $echo = false) {
	$obj_job_meta = WP_Job_Board_Pro_Employer_Meta::get_instance($post->ID);

	ob_start();
	if ( $obj_job_meta->check_post_meta_exist($meta_key) && ($value = $obj_job_meta->get_post_meta( $meta_key )) ) {
		?>
		<div class="employer-meta with-<?php echo esc_attr($show_title ? 'icon-title' : 'icon'); ?>">
			<?php if ( !empty($show_title) ) {
				$title = $obj_job_meta->get_post_meta_title($meta_key);
			?>
				<h3 class="title">
					<?php echo esc_html($title); ?>:
				</h3>
			<?php } ?>
			<div class="value">
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

function superio_employer_display_custom_field_meta($post, $meta_key, $icon = '', $show_title = false, $suffix = '', $echo = false) {
	$obj_job_meta = WP_Job_Board_Pro_Employer_Meta::get_instance($post->ID);

	ob_start();
	if ( $obj_job_meta->check_custom_post_meta_exist($meta_key) && ($value = $obj_job_meta->get_custom_post_meta( $meta_key )) ) {
		?>
		<div class="employer-meta with-<?php echo esc_attr($show_title ? 'icon-title' : 'icon'); ?>">

			<div class="employer-meta">
				<?php if ( !empty($show_title) ) {
					$title = $obj_job_meta->get_custom_post_meta_title($meta_key);
				?>
					<h3 class="title">
						<?php echo esc_html($title); ?>:
					</h3>
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


// Employer Archive hooks

function superio_employer_display_filter_btn() {
	$layout_type = superio_get_employers_layout_type();
	if ( $layout_type == 'half-map' ) {
		?>
		<div class="filter-in-sidebar-wrapper">
			<span class="filter-in-sidebar btn-theme-light btn"><i class="ti-filter"></i><span class="text"><?php esc_html_e( 'Filter', 'superio' ); ?> </span></span>
		</div>
		<?php
	}
}

function superio_employer_display_per_page_form($wp_query) {
    $total              = $wp_query->found_posts;
    $per_page           = $wp_query->get( 'posts_per_page' );
    $_per_page          = wp_job_board_pro_get_option('number_employers_per_page', 12);

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
    <form method="get" action="<?php echo esc_url(WP_Job_Board_Pro_Mixes::get_employers_page_url()); ?>" class="form-superio-ppp">
        
    	<select name="employers_ppp" onchange="this.form.submit()">
            <?php foreach( $products_per_page_options as $key => $value ) { ?>
                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $per_page ); ?>>
                	<?php
                		if ( $value == -1 ) {
                			esc_html_e( 'Sve', 'superio' );
                		} else {
                			echo sprintf( esc_html__( '%s Po Stranici', 'superio' ), $value );
                		}
                	?>
                </option>
            <?php } ?>
        </select>

        <input type="hidden" name="paged" value="1" />
		<?php WP_Job_Board_Pro_Mixes::query_string_form_fields( null, array( 'employers_ppp', 'submit', 'paged' ) ); ?>
    </form>
    <?php
}