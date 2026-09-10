<?php


if ( ! function_exists( 'superio_post_tags' ) ) {
	function superio_post_tags() {
		$posttags = get_the_tags();
		if ( $posttags ) {
			echo '<div class="entry-tags-list">';
			$size = count( $posttags );
			foreach ( $posttags as $tag ) {
				echo '<a href="' . get_tag_link( $tag->term_id ) . '">';
				echo esc_attr($tag->name);
				echo '</a>';
			}
			echo '</div>';
		}
	}
}

if ( !function_exists('superio_get_page_title') ) {
	function superio_get_page_title() {
		$title = '';
		if ( !is_front_page() || is_paged() ) {
			global $post;
			$homeLink = esc_url( home_url() );

			if ( is_home() ) {
				$posts_page_id = get_option( 'page_for_posts');
				if ( $posts_page_id ) {
					$title = get_the_title( $posts_page_id );
				} else {
					$title = esc_html__( 'Blog', 'superio' );
				}
			} elseif (is_category()) {
				global $wp_query;
				$cat_obj = $wp_query->get_queried_object();
				$title = $cat_obj->name;
			} elseif (is_day()) {
				$title = get_the_time('d');
			} elseif (is_month()) {
				$title = get_the_time('F');
			} elseif (is_year()) {
				$title = get_the_time('Y');
			} elseif (is_single() && !is_attachment()) {
				$title = get_the_title();
			} elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() && !is_author() && !is_search() ) {
				global $wp_query;

				$post_type = get_post_type();
				if ( is_tax('job_listing_category') || is_tax('job_listing_location') || is_tax('job_listing_tag') || is_tax('job_listing_type') ) {
					$cat_obj = $wp_query->get_queried_object();
					$title = $cat_obj->name;
				} elseif ( is_tax('candidate_category') || is_tax('candidate_location') || is_tax('candidate_tag') ) {
					$cat_obj = $wp_query->get_queried_object();
					$title = $cat_obj->name;
				} elseif ( is_tax('employer_category') || is_tax('employer_location') ) {
					$cat_obj = $wp_query->get_queried_object();
					$title = $cat_obj->name;
				} elseif ( $post_type == 'job_listing' ) {
					$title = esc_html__('Jobs', 'superio');
				} elseif ( $post_type == 'employer' ) {
					$title = esc_html__('Employers', 'superio');
				} elseif ( $post_type == 'candidate' ) {
					$title = esc_html__('Candidates', 'superio');
				} else {
					$post_type = get_post_type_object(get_post_type());
					if (is_object($post_type)) {
						$title = $post_type->labels->singular_name;
					}
				}
			} elseif (is_attachment()) {
				$title = get_the_title();
			} elseif ( is_page() && !$post->post_parent ) {
				$title = get_the_title();
			} elseif ( is_page() && $post->post_parent ) {
				$title = get_the_title();
			} elseif ( is_search() ) {
				$title = sprintf(esc_html__('Search results for "%s"', 'superio'), get_search_query());
			} elseif ( is_tag() ) {
				$title = sprintf(esc_html__('Posts tagged "%s"', 'superio'), single_tag_title('', false));
			} elseif ( is_author() ) {
				global $author;
				$userdata = get_userdata($author);
				$title = $userdata->display_name;
			} elseif ( is_404() ) {
				$title = esc_html__('Error 404', 'superio');
			}
		}else{
			$title = get_the_title();
		}
		return $title;
	}
}

if ( ! function_exists( 'superio_breadcrumbs' ) ) {
	function superio_breadcrumbs() {

		$delimiter = ' ';
		$home = esc_html__('Home', 'superio');
		$before = '<li><span class="active">';
		$after = '</span></li>';
		$title = superio_get_page_title();
		if ( !is_front_page() || is_paged()) {
			global $post;
			$homeLink = esc_url( home_url() );
			
			echo '<div class="breadscrumb-inner clearfix">';
			echo '<h2 class="bread-title">'.$title.'</h2>';
			echo '<div class="clearfix"><ol class="breadcrumb">';

			echo '<li><a href="' . $homeLink . '">' . $home . '</a> ' . $delimiter . '</li> ';

			if (is_category()) {
				global $wp_query;
				$cat_obj = $wp_query->get_queried_object();
				
				if ( !empty($cat_obj->parent) ) {
					$args = array(
						'separator' => '',
						'link'      => true,
						'format'    => 'name',
					);

					echo '<li>'.get_term_parents_list( $cat_obj->parent, 'category', $args ).'</li>';
				}
				echo trim($before . single_cat_title('', false) . $after);

			} elseif (is_day()) {
				echo '<li><a href="' . esc_url( get_year_link(get_the_time('Y')) ) . '">' . get_the_time('Y') . '</a></li> ' . $delimiter . ' ';
				echo '<li><a href="' . esc_url( get_month_link(get_the_time('Y'),get_the_time('m')) ) . '">' . get_the_time('F') . '</a></li> ' . $delimiter . ' ';
				echo trim($before) . get_the_time('d') . $after;
			} elseif (is_month()) {
				echo '<a href="' . esc_url( get_year_link(get_the_time('Y')) ) . '">' . get_the_time('Y') . '</a></li> ' . $delimiter . ' ';
				echo trim($before) . get_the_time('F') . $after;
			} elseif (is_year()) {
				echo trim($before) . get_the_time('Y') . $after;
			} elseif (is_single() && !is_attachment()) {
				if ( get_post_type() == 'job_listing' ) {
					if ( class_exists('WP_Job_Board_Pro_Mixes') ) {
						$url = WP_Job_Board_Pro_Mixes::get_jobs_page_url();
						echo '<li><a href="' . esc_url($url) . '">' . esc_html__('Jobs', 'superio') . '</a></li> ' . $delimiter . ' ';
					}
					echo trim($before) . get_the_title() . $after;
				} elseif ( get_post_type() == 'employer' ) {
					if ( class_exists('WP_Job_Board_Pro_Mixes') ) {
						$url = WP_Job_Board_Pro_Mixes::get_employers_page_url();
						echo '<li><a href="' . esc_url($url) . '">' . esc_html__('Employers', 'superio') . '</a></li> ' . $delimiter . ' ';
					}
					echo trim($before) . get_the_title() . $after;
				} elseif ( get_post_type() == 'candidate' ) {
					if ( class_exists('WP_Job_Board_Pro_Mixes') ) {
						$url = WP_Job_Board_Pro_Mixes::get_candidates_page_url();
						echo '<li><a href="' . esc_url($url) . '">' . esc_html__('Candidates', 'superio') . '</a></li> ' . $delimiter . ' ';
					}
					echo trim($before) . get_the_title() . $after;
				} elseif ( get_post_type() != 'post' ) {
					$post_type = get_post_type_object(get_post_type());
					$slug = $post_type->rewrite;
					
					echo '<li><a href="' . $homeLink . '/' . $slug['slug'] . '/">' . $post_type->labels->singular_name . '</a></li> ' . $delimiter . ' ';
					echo trim($before) . get_the_title() . $after;
				} else {
					$cat = get_the_category(); $cat = $cat[0];
					echo '<li>'.get_category_parents($cat, TRUE, '</li><li>');
					echo '<span class="active">'.get_the_title() . $after;
				}
			} elseif (!is_single() && !is_page() && get_post_type() != 'post' && !is_404() && !is_author() && !is_search()) {

				if ( is_tax('job_listing_category') || is_tax('job_listing_location') || is_tax('job_listing_tag') || is_tax('job_listing_type') ) {
					if ( class_exists('WP_Job_Board_Pro_Mixes') ) {
						$jobs_page_id = wp_job_board_pro_get_option('jobs_page_id');
						$jobs_page_id = WP_Job_Board_Pro_Mixes::get_lang_post_id( $jobs_page_id, 'page');
						if ( $jobs_page_id ) {
							$url = get_permalink($jobs_page_id);
						} else {
							$url = get_post_type_archive_link( 'job_listing' );
						}

						echo '<li><a href="' . esc_url($url) . '">' . esc_html__('Jobs', 'superio') . '</a></li> ' . $delimiter . ' ';
					}
					global $wp_query;
					$cat_obj = $wp_query->get_queried_object();
					
					if ( !empty($cat_obj->parent) ) {
						$args = array(
							'separator' => '',
							'link'      => true,
							'format'    => 'name',
						);

						echo '<li>'.get_term_parents_list( $cat_obj->parent, $cat_obj->taxonomy, $args ).'</li>';
					}
					echo trim($before . single_cat_title('', false) . $after);

				} elseif ( is_tax('candidate_category') || is_tax('candidate_location') || is_tax('candidate_tag') ) {
					if ( class_exists('WP_Job_Board_Pro_Mixes') ) {
						$candidates_page_id = wp_job_board_pro_get_option('candidates_page_id');
						$candidates_page_id = WP_Job_Board_Pro_Mixes::get_lang_post_id( $candidates_page_id, 'page');
						if ( $candidates_page_id ) {
							$url = get_permalink($candidates_page_id);
						} else {
							$url = get_post_type_archive_link( 'candidate' );
						}
						echo '<li><a href="' . esc_url($url) . '">' . esc_html__('Candidates', 'superio') . '</a></li> ' . $delimiter . ' ';
					}

					global $wp_query;
					$cat_obj = $wp_query->get_queried_object();
					
					if ( !empty($cat_obj->parent) ) {
						$args = array(
							'separator' => '',
							'link'      => true,
							'format'    => 'name',
						);

						echo '<li>'.get_term_parents_list( $cat_obj->parent, $cat_obj->taxonomy, $args ).'</li>';
					}
					echo trim($before . single_cat_title('', false) . $after);

				} elseif ( is_tax('employer_category') || is_tax('employer_location') ) {
					if ( class_exists('WP_Job_Board_Pro_Mixes') ) {
						$employers_page_id = wp_job_board_pro_get_option('employers_page_id');
						$employers_page_id = WP_Job_Board_Pro_Mixes::get_lang_post_id( $employers_page_id, 'page');
						if ( $employers_page_id ) {
							$url = get_permalink($employers_page_id);
						} else {
							$url = get_post_type_archive_link( 'employer' );
						}
						
						echo '<li><a href="' . esc_url($url) . '">' . esc_html__('Employers', 'superio') . '</a></li> ' . $delimiter . ' ';
					}

					global $wp_query;
					$cat_obj = $wp_query->get_queried_object();
					
					if ( !empty($cat_obj->parent) ) {
						$args = array(
							'separator' => '',
							'link'      => true,
							'format'    => 'name',
						);

						echo '<li>'.get_term_parents_list( $cat_obj->parent, $cat_obj->taxonomy, $args ).'</li>';
					}
					echo trim($before . single_cat_title('', false) . $after);

				} elseif ( get_post_type() == 'job_listing' ) {
					echo trim($before) . esc_html__('Jobs', 'superio') . $after;
				} elseif ( get_post_type() == 'employer' ) {
					echo trim($before) . esc_html__('Employers', 'superio') . $after;
				} elseif ( get_post_type() == 'candidate' ) {
					echo trim($before) . esc_html__('Candidates', 'superio') . $after;
				} else {
					$post_type = get_post_type_object(get_post_type());
					if (is_object($post_type)) {
						echo trim($before) . $post_type->labels->singular_name . $after;
					}
				}

			} elseif (is_attachment()) {
				$parent = get_post($post->post_parent);
				$cat = get_the_category($parent->ID);
				echo '<li>';
				if ( !empty($cat) ) {
					$cat = $cat[0];
					echo get_category_parents($cat, TRUE, '</li><li>');
				}
				if ( !empty($parent) ) {
					echo '<a href="' . esc_url( get_permalink($parent) ) . '">' . $parent->post_title . '</a></li><li>';
				}
				echo '<span class="active">'.get_the_title() . $after;
			} elseif ( is_page() && !$post->post_parent ) {
				echo trim($before) . get_the_title() . $after;
			} elseif ( is_page() && $post->post_parent ) {
				$parent_id  = $post->post_parent;
				$breadcrumbs = array();
				while ($parent_id) {
					$page = get_page($parent_id);
					$breadcrumbs[] = '<li><a href="' . esc_url( get_permalink($page->ID) ) . '">' . get_the_title($page->ID) . '</a></li>';
					$parent_id  = $page->post_parent;
				}
				$breadcrumbs = array_reverse($breadcrumbs);
				foreach ($breadcrumbs as $crumb) {
					echo trim($crumb) . ' ' . $delimiter . ' ';
				}
				echo trim($before) . get_the_title() . $after;
			} elseif ( is_search() ) {
				echo trim($before) . sprintf(esc_html__('Search results for "%s"','superio'), get_search_query()) . $after;
			} elseif ( is_tag() ) {
				echo trim($before) . sprintf(esc_html__('Posts tagged "%s"', 'superio'), single_tag_title('', false)) . $after;
			} elseif ( is_author() ) {
				global $author;
				$userdata = get_userdata($author);
				echo trim($before) . esc_html__('Articles posted by ', 'superio') . $userdata->display_name . $after;
			} elseif ( is_404() ) {
				echo trim($before) . esc_html__('Error 404', 'superio') . $after;
			} elseif ( is_home() ) {
				$posts_page_id = get_option( 'page_for_posts');
				if ( $posts_page_id ) {
					$title = get_the_title( $posts_page_id );
				} else {
					$title = esc_html__( 'Blog', 'superio' );
				}
				echo trim($before) . $title . $after;
			}

			echo '</ol></div>';
			echo '</div>';
			
		}
	}
}

if ( ! function_exists( 'superio_render_breadcrumbs' ) ) {
	function superio_render_breadcrumbs() {
		global $post;
		$has_bg = '';
		$show = true;
		$style = $classes = array();
		$full_width = 'container';
		if ( is_page() && is_object($post) ) {
			$show = get_post_meta( $post->ID, 'apus_page_show_breadcrumb', true );
			if ( $show == 'no' ) {
				return ''; 
			}
			$bgimage = get_post_meta( $post->ID, 'apus_page_breadcrumb_image', true );
			$bgcolor = get_post_meta( $post->ID, 'apus_page_breadcrumb_color', true );
			$style = array();
			if ( $bgcolor ) {
				$style[] = 'background-color:'.$bgcolor;
			}
			if ( $bgimage ) { 
				$style[] = 'background-image:url(\''.esc_url($bgimage).'\')';
				$has_bg = 1;
			}
			$full_width = apply_filters('superio_page_content_class', 'container');
		} elseif ( is_singular('post') || is_category() || is_home() || is_search() ) {
			$show = superio_get_config('show_blog_breadcrumbs', true);
			if ( !$show || is_front_page() ) {
				return ''; 
			}
			$breadcrumb_img = superio_get_config('blog_breadcrumb_image');
	        $breadcrumb_color = superio_get_config('blog_breadcrumb_color');
	        $style = array();
	        if ( $breadcrumb_color ) {
	            $style[] = 'background-color:'.$breadcrumb_color;
	        }
	        if ( isset($breadcrumb_img['url']) && !empty($breadcrumb_img['url']) ) {
	            $style[] = 'background-image:url(\''.esc_url($breadcrumb_img['url']).'\')';
	            $has_bg = 1;
	        }
	        $full_width = apply_filters('superio_blog_content_class', 'container');
		} elseif ( is_singular('job_listing') || is_post_type_archive('job_listing') || is_tax('job_listing_type') || is_tax('job_listing_category') || is_tax('job_listing_location') || is_tax('job_listing_tag') ) {
			$show = superio_get_config('show_job_breadcrumbs', true);
			if ( !$show || is_front_page() ) {
				return ''; 
			}
			$breadcrumb_img = superio_get_config('job_breadcrumb_image');
	        $breadcrumb_color = superio_get_config('job_breadcrumb_color');
	        $style = array();
	        if ( $breadcrumb_color ) {
	            $style[] = 'background-color:'.$breadcrumb_color;
	        }
	        if ( isset($breadcrumb_img['url']) && !empty($breadcrumb_img['url']) ) {
	            $style[] = 'background-image:url(\''.esc_url($breadcrumb_img['url']).'\')';
	            $has_bg = 1;
	        }
	        $full_width = apply_filters('superio_blog_content_class', 'container');
		} elseif ( is_singular('employer') || is_post_type_archive('employer') || is_tax('employer_category') || is_tax('employer_location') ) {
			$show = superio_get_config('show_employer_breadcrumbs', true);
			if ( !$show || is_front_page() ) {
				return ''; 
			}
			$breadcrumb_img = superio_get_config('employer_breadcrumb_image');
	        $breadcrumb_color = superio_get_config('employer_breadcrumb_color');
	        $style = array();
	        if ( $breadcrumb_color ) {
	            $style[] = 'background-color:'.$breadcrumb_color;
	        }
	        if ( isset($breadcrumb_img['url']) && !empty($breadcrumb_img['url']) ) {
	            $style[] = 'background-image:url(\''.esc_url($breadcrumb_img['url']).'\')';
	            $has_bg = 1;
	        }
	        $full_width = apply_filters('superio_blog_content_class', 'container');
		} elseif ( is_singular('candidate') || is_post_type_archive('candidate') || is_tax('candidate_category') || is_tax('candidate_location') ) {
			$show = superio_get_config('show_candidate_breadcrumbs', true);
			if ( !$show || is_front_page() ) {
				return ''; 
			}
			$breadcrumb_img = superio_get_config('candidate_breadcrumb_image');
	        $breadcrumb_color = superio_get_config('candidate_breadcrumb_color');
	        $style = array();
	        if ( $breadcrumb_color ) {
	            $style[] = 'background-color:'.$breadcrumb_color;
	        }
	        if ( isset($breadcrumb_img['url']) && !empty($breadcrumb_img['url']) ) {
	            $style[] = 'background-image:url(\''.esc_url($breadcrumb_img['url']).'\')';
	            $has_bg = 1;
	        }
	        $full_width = apply_filters('superio_blog_content_class', 'container');
		}
		$estyle = !empty($style)? ' style="'.implode(";", $style).'"':"";
		$classes[] = $has_bg ? 'has_bg' :'';

		echo '<section id="apus-breadscrumb" class="breadcrumb-page apus-breadscrumb '.implode(' ', $classes).'"'.$estyle.'><div class="'.$full_width.'"><div class="wrapper-breads"><div class="wrapper-breads-inner">';
			superio_breadcrumbs();
		echo '</div></div></div></section>';
	}
}

if ( ! function_exists( 'superio_paging_nav' ) ) {
	function superio_paging_nav() {
		global $wp_query, $wp_rewrite;

		if ( $wp_query->max_num_pages < 2 ) {
			return;
		}

		$paged        = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;
		$pagenum_link = html_entity_decode( get_pagenum_link() );
		$query_args   = array();
		$url_parts    = explode( '?', $pagenum_link );

		if ( isset( $url_parts[1] ) ) {
			wp_parse_str( $url_parts[1], $query_args );
		}

		$pagenum_link = remove_query_arg( array_keys( $query_args ), $pagenum_link );
		$pagenum_link = trailingslashit( $pagenum_link ) . '%_%';

		$format  = $wp_rewrite->using_index_permalinks() && ! strpos( $pagenum_link, 'index.php' ) ? 'index.php/' : '';
		$format .= $wp_rewrite->using_permalinks() ? user_trailingslashit( $wp_rewrite->pagination_base . '/%#%', 'paged' ) : '?paged=%#%';

		// Set up paginated links.
		$links = paginate_links( array(
			'base'     => $pagenum_link,
			'format'   => $format,
			'total'    => $wp_query->max_num_pages,
			'current'  => $paged,
			'mid_size' => 1,
			'add_args' => array_map( 'urlencode', $query_args ),
			'prev_text' => '<i class="ti-arrow-left"></i>',
			'next_text' => '<i class="ti-arrow-right"></i>',
		) );

		if ( $links ) :

		?>
		<nav class="navigation paging-navigation" role="navigation">
			<h1 class="screen-reader-text hidden"><?php esc_html_e( 'Posts navigation', 'superio' ); ?></h1>
			<div class="apus-pagination">
				<?php echo trim($links); ?>
			</div><!-- .pagination -->
		</nav><!-- .navigation -->
		<?php
		endif;
	}
}

if ( !function_exists('superio_comment_form') ) {
	function superio_comment_form($arg, $class = 'btn-theme ') {
		global $post;
		if ('open' == $post->comment_status) {
			ob_start();
	      	comment_form($arg);
	      	$form = ob_get_clean();
	      	?>
	      	<div class="commentform reset-button-default">
		    	<div class="clearfix">
			    	<?php
			      	echo trim($form);
			      	?>
		      	</div>
	      	</div>
	      	<?php
	      }
	}
}
if (!function_exists('superio_list_comment') ) {
	function superio_list_comment($comment, $args, $depth) {
		if ( is_file(get_template_directory().'/list-comments.php') ) {
	        require get_template_directory().'/list-comments.php';
      	}
	}
}

function superio_comment_field_to_bottom( $fields ) {
	$comment_field = $fields['comment'];
	unset( $fields['comment'] );
	$fields['comment'] = $comment_field;
	return $fields;
}
add_filter( 'comment_form_fields', 'superio_comment_field_to_bottom' );


/*
 * create placeholder
 * var size: array( width, height )
 */
function superio_create_placeholder($size) {
	return "data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg'%20viewBox%3D'0%200%20".$size[0]."%20".$size[1]."'%2F%3E";
}


function superio_display_sidebar_left( $sidebar_configs ) {
	if ( isset($sidebar_configs['left']) ) : ?>
		<div class="sidebar-wrapper <?php echo esc_attr($sidebar_configs['left']['class']) ;?>">
		  	<aside class="sidebar sidebar-left" itemscope="itemscope" itemtype="http://schema.org/WPSideBar">
		  		<div class="close-sidebar-btn hidden-lg hidden-md"> <i class="ti-close"></i> <span><?php esc_html_e('Close', 'superio'); ?></span></div>
		   		<?php if ( is_active_sidebar( $sidebar_configs['left']['sidebar'] ) ): ?>
		   			<?php dynamic_sidebar( $sidebar_configs['left']['sidebar'] ); ?>
		   		<?php endif; ?>
		  	</aside>
		</div>
	<?php endif;
}

function superio_display_sidebar_right( $sidebar_configs ) {
	if ( isset($sidebar_configs['right']) ) : ?>
		<div class="sidebar-wrapper <?php echo esc_attr($sidebar_configs['right']['class']) ;?>">
		  	<aside class="sidebar sidebar-right" itemscope="itemscope" itemtype="http://schema.org/WPSideBar">
		  		<div class="close-sidebar-btn hidden-lg hidden-md"><i class="ti-close"></i> <span><?php esc_html_e('Close', 'superio'); ?></span></div>
		   		<?php if ( is_active_sidebar( $sidebar_configs['right']['sidebar'] ) ): ?>
			   		<?php dynamic_sidebar( $sidebar_configs['right']['sidebar'] ); ?>
			   	<?php endif; ?>
		  	</aside>
		</div>
	<?php endif;
}

function superio_before_content( $sidebar_configs ) {
	if ( isset($sidebar_configs['left']) || isset($sidebar_configs['right']) ) : ?>
		<a href="javascript:void(0)" class="mobile-sidebar-btn hidden-lg hidden-md"> <i class="fa fa-bars"></i> <?php echo esc_html__('Show Sidebar', 'superio'); ?></a>
		<div class="mobile-sidebar-panel-overlay"></div>
	<?php endif;
}
