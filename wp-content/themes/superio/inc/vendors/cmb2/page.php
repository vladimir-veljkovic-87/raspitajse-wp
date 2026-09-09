<?php

if ( !function_exists( 'superio_page_metaboxes' ) ) {
	function superio_page_metaboxes(array $metaboxes) {
		global $wp_registered_sidebars;
        $sidebars = array();

        if ( !empty($wp_registered_sidebars) ) {
            foreach ($wp_registered_sidebars as $sidebar) {
                $sidebars[$sidebar['id']] = $sidebar['name'];
            }
        }
        $headers = array_merge( array('global' => esc_html__( 'Global Setting', 'superio' )), superio_get_header_layouts() );
        $footers = array_merge( array('global' => esc_html__( 'Global Setting', 'superio' )), superio_get_footer_layouts() );

		$prefix = 'apus_page_';

        $columns = array(
            '' => esc_html__( 'Global Setting', 'superio' ),
            '1' => esc_html__('1 Column', 'superio'),
            '2' => esc_html__('2 Columns', 'superio'),
            '3' => esc_html__('3 Columns', 'superio'),
            '4' => esc_html__('4 Columns', 'superio'),
            '6' => esc_html__('6 Columns', 'superio')
        );
        // Jobs Page
        $fields = array(
            array(
                'name' => esc_html__( 'Jobs Layout', 'superio' ),
                'id'   => $prefix.'layout_type',
                'type' => 'select',
                'options' => array(
                    '' => esc_html__( 'Global Setting', 'superio' ),
                    'default' => esc_html__('Default', 'superio'),
                    'fullwidth' => esc_html__('Full Width', 'superio'),
                    'half-map' => esc_html__('Half Map', 'superio'),
                    'top-map' => esc_html__('Top Map', 'superio'),
                    'half-job-details' => esc_html__('Half Job Details', 'superio'),
                )
            ),
            array(
                'id' => $prefix.'color',
                'type' => 'colorpicker',
                'name' => esc_html__('Background Color', 'superio'),
            ),
            array(
                'id' => $prefix.'display_mode',
                'type' => 'select',
                'name' => esc_html__('Default Display Mode', 'superio'),
                'options' => array(
                    '' => esc_html__( 'Global Setting', 'superio' ),
                    'grid' => esc_html__('Grid', 'superio'),
                    'list' => esc_html__('List', 'superio'),
                )
            ),
            array(
                'id' => $prefix.'inner_list_style',
                'type' => 'select',
                'name' => esc_html__('Jobs list item style', 'superio'),
                'options' => array(
                    '' => esc_html__( 'Global Setting', 'superio' ),
                    'list' => esc_html__('List Default', 'superio'),
                    'list-v1' => esc_html__('List V1', 'superio'),
                    'list-v2' => esc_html__('List V2', 'superio'),
                    'list-v3' => esc_html__('List V3', 'superio'),
                    'list-v4' => esc_html__('List V4', 'superio'),
                    'list-v5' => esc_html__('List V5', 'superio'),
                    'list-v6' => esc_html__('List V6', 'superio'),
                    'list-v7' => esc_html__('List V7', 'superio'),
                    'list-v8' => esc_html__('List V8', 'superio'),
                ),
            ),
            array(
                'id' => $prefix.'inner_grid_style',
                'type' => 'select',
                'name' => esc_html__('Jobs grid item style', 'superio'),
                'options' => array(
                    '' => esc_html__( 'Global Setting', 'superio' ),
                    'grid' => esc_html__('Grid Default', 'superio'),
                    'grid-v1' => esc_html__('Grid V1', 'superio'),
                    'grid-v2' => esc_html__('Grid V2', 'superio'),
                    'grid-v3' => esc_html__('Grid V3', 'superio'),
                    'grid-v4' => esc_html__('Grid V4', 'superio'),
                ),
            ),
            array(
                'id' => $prefix.'jobs_columns',
                'type' => 'select',
                'name' => esc_html__('Grid Listing Columns', 'superio'),
                'options' => $columns,
            ),
            array(
                'id' => $prefix.'jobs_pagination',
                'type' => 'select',
                'name' => esc_html__('Pagination Type', 'superio'),
                'options' => array(
                    '' => esc_html__( 'Global Setting', 'superio' ),
                    'default' => esc_html__('Default', 'superio'),
                    'loadmore' => esc_html__('Load More Button', 'superio'),
                    'infinite' => esc_html__('Infinite Scrolling', 'superio'),
                ),
            ),

            array(
                'id' => $prefix.'jobs_show_filter_top',
                'type' => 'select',
                'name' => esc_html__('Show Filter Top', 'superio'),
                'options' => array(
                    '' => esc_html__( 'Global Setting', 'superio' ),
                    'no' => esc_html__('No', 'superio'),
                    'yes' => esc_html__('Yes', 'superio')
                ),
            ),
            array(
                'id' => $prefix.'jobs_filter_top_sidebar',
                'type' => 'select',
                'name' => esc_html__('Jobs Filter Top Sidebar', 'superio'),
                'description' => esc_html__('Choose a filter top sidebar for your website.', 'superio'),
                'options' => array(
                    '' => esc_html__('Global Setting', 'superio'),
                    'jobs-filter-top-sidebar' => esc_html__('Jobs Filter Top Sidebar', 'superio'),
                    'jobs-filter-top2-sidebar' => esc_html__('Jobs Filter Top 2 Sidebar', 'superio'),
                    'jobs-filter-top3-sidebar' => esc_html__('Jobs Filter Top 3 Sidebar', 'superio'),
                    'jobs-filter-top4-sidebar' => esc_html__('Jobs Filter Top 4 Sidebar', 'superio'),
                ),
                'default' => ''
            ),

            array(
                'id' => $prefix.'jobs_show_offcanvas_filter',
                'type' => 'select',
                'name' => esc_html__('Show Offcanvas Filter', 'superio'),
                'options' => array(
                    '' => esc_html__( 'Global Setting', 'superio' ),
                    'no' => esc_html__('No', 'superio'),
                    'yes' => esc_html__('Yes', 'superio')
                ),
            ),

            array(
                'id' => $prefix.'jobs_filter_sidebar',
                'type' => 'select',
                'name' => esc_html__('Jobs Filter Sidebar', 'superio'),
                'description' => esc_html__('Choose a filter sidebar for your website.', 'superio'),
                'options' => array(
                    '' => esc_html__('Global Setting', 'superio'),
                    'jobs-filter-sidebar' => esc_html__('Jobs Filter Sidebar', 'superio'),
                    'jobs-filter2-sidebar' => esc_html__('Jobs Filter 2 Sidebar', 'superio'),
                    'jobs-filter3-sidebar' => esc_html__('Jobs Filter 3 Sidebar', 'superio'),
                ),
                'default' => ''
            ),

        );
        
        $metaboxes[$prefix . 'jobs_setting'] = array(
            'id'                        => $prefix . 'jobs_setting',
            'title'                     => esc_html__( 'Jobs Settings', 'superio' ),
            'object_types'              => array( 'page' ),
            'context'                   => 'normal',
            'priority'                  => 'high',
            'show_names'                => true,
            'fields'                    => $fields
        );


        // Employers Page
        $fields = array(
            array(
                'name' => esc_html__( 'Employers Layout', 'superio' ),
                'id'   => $prefix.'employers_layout_type',
                'type' => 'select',
                'options' => array(
                    '' => esc_html__( 'Global Setting', 'superio' ),
                    'default' => esc_html__('Default', 'superio'),
                    'half-map' => esc_html__('Half Map', 'superio'),
                    'top-map' => esc_html__('Top Map', 'superio'),
                )
            ),
            array(
                'id' => $prefix.'employers_display_mode',
                'type' => 'select',
                'name' => esc_html__('Employers display mode', 'superio'),
                'options' => array(
                    '' => esc_html__( 'Global Setting', 'superio' ),
                    'grid' => esc_html__('Grid', 'superio'),
                    'list' => esc_html__('List', 'superio'),
                )
            ),
            array(
                'id' => $prefix.'employers_inner_list_style',
                'type' => 'select',
                'name' => esc_html__('Employers list item style', 'superio'),
                'options' => array(
                    '' => esc_html__( 'Global Setting', 'superio' ),
                    'list' => esc_html__('List Default', 'superio'),
                    'list-v1' => esc_html__('List V1', 'superio'),
                    'list-v2' => esc_html__('List V2', 'superio'),
                ),
            ),
            array(
                'id' => $prefix.'employers_inner_grid_style',
                'type' => 'select',
                'name' => esc_html__('Employers grid item style', 'superio'),
                'options' => array(
                    '' => esc_html__( 'Global Setting', 'superio' ),
                    'grid' => esc_html__('Grid Default', 'superio'),
                    'grid-v1' => esc_html__('Grid V1', 'superio'),
                    'grid-v2' => esc_html__('Grid V2', 'superio'),
                    'grid-v3' => esc_html__('Grid V3', 'superio'),
                ),
            ),
            array(
                'id' => $prefix.'employers_columns',
                'type' => 'select',
                'name' => esc_html__('Employer Columns', 'superio'),
                'options' => $columns,
                'description' => esc_html__('Apply for display mode is grid and simple.', 'superio'),
            ),
            array(
                'id' => $prefix.'employers_pagination',
                'type' => 'select',
                'name' => esc_html__('Pagination Type', 'superio'),
                'options' => array(
                    '' => esc_html__( 'Global Setting', 'superio' ),
                    'default' => esc_html__('Default', 'superio'),
                    'loadmore' => esc_html__('Load More Button', 'superio'),
                    'infinite' => esc_html__('Infinite Scrolling', 'superio'),
                ),
            ),


        );
        $metaboxes[$prefix . 'employers_setting'] = array(
            'id'                        => $prefix . 'employers_setting',
            'title'                     => esc_html__( 'Employers Settings', 'superio' ),
            'object_types'              => array( 'page' ),
            'context'                   => 'normal',
            'priority'                  => 'high',
            'show_names'                => true,
            'fields'                    => $fields
        );

        // Candidates Page
        $fields = array(
            array(
                'name' => esc_html__( 'Candidates Layout', 'superio' ),
                'id'   => $prefix.'candidates_layout_type',
                'type' => 'select',
                'options' => array(
                    '' => esc_html__( 'Global Setting', 'superio' ),
                    'default' => esc_html__('Default', 'superio'),
                    'half-map' => esc_html__('Half Map', 'superio'),
                    'top-map' => esc_html__('Top Map', 'superio'),
                )
            ),

            array(
                'id' => $prefix.'color',
                'type' => 'colorpicker',
                'name' => esc_html__('Background Color', 'superio'),
            ),
            
            array(
                'id' => $prefix.'candidates_display_mode',
                'type' => 'select',
                'name' => esc_html__('Candidates display mode', 'superio'),
                'options' => array(
                    '' => esc_html__( 'Global Setting', 'superio' ),
                    'grid' => esc_html__('Grid', 'superio'),
                    'list' => esc_html__('List', 'superio'),
                )
            ),
            array(
                'id' => $prefix.'candidates_inner_list_style',
                'type' => 'select',
                'name' => esc_html__('Candidates list item style', 'superio'),
                'options' => array(
                    '' => esc_html__( 'Global Setting', 'superio' ),
                    'list' => esc_html__('List Default', 'superio'),
                    'list-v1' => esc_html__('List V1', 'superio'),
                    'list-v2' => esc_html__('List V2', 'superio'),
                    'list-v3' => esc_html__('List V3', 'superio'),
                ),
            ),
            array(
                'id' => $prefix.'candidates_inner_grid_style',
                'type' => 'select',
                'name' => esc_html__('Candidates grid item style', 'superio'),
                'options' => array(
                    '' => esc_html__( 'Global Setting', 'superio' ),
                    'grid' => esc_html__('Grid Default', 'superio'),
                    'grid-v1' => esc_html__('Grid V1', 'superio'),
                    'grid-v2' => esc_html__('Grid V2', 'superio'),
                ),
            ),
            array(
                'id' => $prefix.'candidates_columns',
                'type' => 'select',
                'name' => esc_html__('Candidate Columns', 'superio'),
                'options' => $columns,
                'description' => esc_html__('Apply for display mode is grid.', 'superio'),
            ),
            array(
                'id' => $prefix.'candidates_pagination',
                'type' => 'select',
                'name' => esc_html__('Pagination Type', 'superio'),
                'options' => array(
                    '' => esc_html__( 'Global Setting', 'superio' ),
                    'default' => esc_html__('Default', 'superio'),
                    'loadmore' => esc_html__('Load More Button', 'superio'),
                    'infinite' => esc_html__('Infinite Scrolling', 'superio'),
                ),
            ),

            array(
                'id' => $prefix.'candidates_show_filter_top',
                'type' => 'select',
                'name' => esc_html__('Show Filter Top', 'superio'),
                'options' => array(
                    '' => esc_html__( 'Global Setting', 'superio' ),
                    'no' => esc_html__('No', 'superio'),
                    'yes' => esc_html__('Yes', 'superio')
                ),
            ),
            array(
                'id' => $prefix.'candidates_filter_top_sidebar',
                'type' => 'select',
                'name' => esc_html__('Candidates Filter Top Sidebar', 'superio'),
                'description' => esc_html__('Choose a filter top sidebar for your website.', 'superio'),
                'options' => array(
                    '' => esc_html__('Global Setting', 'superio'),
                    'candidates-filter-top-sidebar' => esc_html__('Candidates Filter Top Sidebar', 'superio'),
                    'candidates-filter-top2-sidebar' => esc_html__('Candidates Filter Top 2 Sidebar', 'superio'),
                    'candidates-filter-top3-sidebar' => esc_html__('Candidates Filter Top 3 Sidebar', 'superio'),
                ),
                'default' => ''
            ),

            array(
                'id' => $prefix.'candidates_filter_sidebar',
                'type' => 'select',
                'name' => esc_html__('Candidates Filter Sidebar', 'superio'),
                'description' => esc_html__('Choose a filter top sidebar for your website.', 'superio'),
                'options' => array(
                    '' => esc_html__('Global Setting', 'superio'),
                    'candidates-filter-sidebar' => esc_html__('Candidates Filter Sidebar', 'superio'),
                    'candidates-filter2-sidebar' => esc_html__('Candidates Filter 2 Sidebar', 'superio'),
                ),
                'default' => ''
            ),
        );
        $metaboxes[$prefix . 'candidates_setting'] = array(
            'id'                        => $prefix . 'candidates_setting',
            'title'                     => esc_html__( 'Candidates Settings', 'superio' ),
            'object_types'              => array( 'page' ),
            'context'                   => 'normal',
            'priority'                  => 'high',
            'show_names'                => true,
            'fields'                    => $fields
        );

        // General
	    $fields = array(
			array(
				'name' => esc_html__( 'Select Layout', 'superio' ),
				'id'   => $prefix.'layout',
				'type' => 'select',
				'options' => array(
					'main' => esc_html__('Main Content Only', 'superio'),
					'left-main' => esc_html__('Left Sidebar - Main Content', 'superio'),
					'main-right' => esc_html__('Main Content - Right Sidebar', 'superio')
				)
			),
			array(
                'id' => $prefix.'fullwidth',
                'type' => 'select',
                'name' => esc_html__('Is Full Width?', 'superio'),
                'default' => 'no',
                'options' => array(
                    'no' => esc_html__('No', 'superio'),
                    'yes' => esc_html__('Yes', 'superio')
                )
            ),
            array(
                'id' => $prefix.'left_sidebar',
                'type' => 'select',
                'name' => esc_html__('Left Sidebar', 'superio'),
                'options' => $sidebars
            ),
            array(
                'id' => $prefix.'right_sidebar',
                'type' => 'select',
                'name' => esc_html__('Right Sidebar', 'superio'),
                'options' => $sidebars
            ),
            array(
                'id' => $prefix.'show_breadcrumb',
                'type' => 'select',
                'name' => esc_html__('Show Breadcrumb?', 'superio'),
                'options' => array(
                    'no' => esc_html__('No', 'superio'),
                    'yes' => esc_html__('Yes', 'superio')
                ),
                'default' => 'yes',
            ),
            array(
                'id' => $prefix.'breadcrumb_color',
                'type' => 'colorpicker',
                'name' => esc_html__('Breadcrumb Background Color', 'superio')
            ),
            array(
                'id' => $prefix.'breadcrumb_image',
                'type' => 'file',
                'name' => esc_html__('Breadcrumb Background Image', 'superio')
            ),
            array(
                'id' => $prefix.'header_type',
                'type' => 'select',
                'name' => esc_html__('Header Layout Type', 'superio'),
                'description' => esc_html__('Choose a header for your website.', 'superio'),
                'options' => $headers,
                'default' => 'global'
            ),
            array(
                'id' => $prefix.'header_transparent',
                'type' => 'select',
                'name' => esc_html__('Header Transparent', 'superio'),
                'description' => esc_html__('Choose a header for your website.', 'superio'),
                'options' => array(
                    'no' => esc_html__('No', 'superio'),
                    'yes' => esc_html__('Yes', 'superio')
                ),
                'default' => 'global'
            ),
            array(
                'id' => $prefix.'footer_type',
                'type' => 'select',
                'name' => esc_html__('Footer Layout Type', 'superio'),
                'description' => esc_html__('Choose a footer for your website.', 'superio'),
                'options' => $footers,
                'default' => 'global'
            ),
            array(
                'id' => $prefix.'extra_class',
                'type' => 'text',
                'name' => esc_html__('Extra Class', 'superio'),
                'description' => esc_html__('If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'superio')
            )
    	);
		
	    $metaboxes[$prefix . 'display_setting'] = array(
			'id'                        => $prefix . 'display_setting',
			'title'                     => esc_html__( 'Display Settings', 'superio' ),
			'object_types'              => array( 'page' ),
			'context'                   => 'normal',
			'priority'                  => 'high',
			'show_names'                => true,
			'fields'                    => $fields
		);

	    return $metaboxes;
	}
}
add_filter( 'cmb2_meta_boxes', 'superio_page_metaboxes' );

if ( !function_exists( 'superio_cmb2_style' ) ) {
	function superio_cmb2_style() {
        wp_enqueue_style( 'superio-cmb2-style', get_template_directory_uri() . '/inc/vendors/cmb2/assets/style.css', array(), '1.0' );
		wp_enqueue_script( 'superio-admin', get_template_directory_uri() . '/js/admin.js', array( 'jquery' ), '20150330', true );
	}
}
add_action( 'admin_enqueue_scripts', 'superio_cmb2_style' );


