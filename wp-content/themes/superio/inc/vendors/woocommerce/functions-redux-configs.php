<?php

// Shop Archive settings
function superio_woo_redux_config($sections, $sidebars, $columns) {
    
    $sections[] = array(
        'icon' => 'el el-shopping-cart',
        'title' => esc_html__('Shop Settings', 'superio'),
        'fields' => array(
            array(
                'id' => 'products_breadcrumb_setting',
                'icon' => true,
                'type' => 'info',
                'raw' => '<h3 style="margin: 0;"> '.esc_html__('Breadcrumbs Setting', 'superio').'</h3>',
            ),
            array(
                'id' => 'show_product_breadcrumbs',
                'type' => 'switch',
                'title' => esc_html__('Breadcrumbs', 'superio'),
                'default' => 1
            ),
            array(
                'title' => esc_html__('Breadcrumbs Background Color', 'superio'),
                'subtitle' => '<em>'.esc_html__('The breadcrumbs background color of the site.', 'superio').'</em>',
                'id' => 'woo_breadcrumb_color',
                'type' => 'color',
                'transparent' => false,
            ),
            array(
                'id' => 'woo_breadcrumb_image',
                'type' => 'media',
                'title' => esc_html__('Breadcrumbs Background', 'superio'),
                'subtitle' => esc_html__('Upload a .jpg or .png image that will be your breadcrumbs.', 'superio'),
            ),
        )
    );
    // Archive settings
    $sections[] = array(
        'title' => esc_html__('Product Archives', 'superio'),
        'subsection' => true,
        'fields' => array(
            array(
                'id' => 'products_general_setting',
                'icon' => true,
                'type' => 'info',
                'raw' => '<h3 style="margin: 0;"> '.esc_html__('General Setting', 'superio').'</h3>',
            ),
            array(
                'id' => 'show_shop_cat_title',
                'type' => 'switch',
                'title' => esc_html__('Show Shop/Category Title ?', 'superio'),
                'default' => 1
            ),
            array(
                'id' => 'product_columns',
                'type' => 'select',
                'title' => esc_html__('Product Columns', 'superio'),
                'options' => $columns,
                'default' => 4,
            ),
            array(
                'id' => 'number_products_per_page',
                'type' => 'text',
                'title' => esc_html__('Number of Products Per Page', 'superio'),
                'default' => 12,
                'min' => '1',
                'step' => '1',
                'max' => '100',
                'type' => 'slider'
            ),

            array(
                'id' => 'products_sidebar_setting',
                'icon' => true,
                'type' => 'info',
                'raw' => '<h3 style="margin: 0;"> '.esc_html__('Sidebar Setting', 'superio').'</h3>',
            ),
            array(
                'id' => 'product_archive_fullwidth',
                'type' => 'switch',
                'title' => esc_html__('Is Full Width?', 'superio'),
                'default' => false
            ),
            array(
                'id' => 'product_archive_layout',
                'type' => 'image_select',
                'compiler' => true,
                'title' => esc_html__('Archive Product Layout', 'superio'),
                'subtitle' => esc_html__('Select the layout you want to apply on your archive product page.', 'superio'),
                'options' => array(
                    'main' => array(
                        'title' => esc_html__('Main Content', 'superio'),
                        'alt' => esc_html__('Main Content', 'superio'),
                        'img' => get_template_directory_uri() . '/inc/assets/images/screen1.png'
                    ),
                    'left-main' => array(
                        'title' => esc_html__('Left Sidebar - Main Content', 'superio'),
                        'alt' => esc_html__('Left Sidebar - Main Content', 'superio'),
                        'img' => get_template_directory_uri() . '/inc/assets/images/screen2.png'
                    ),
                    'main-right' => array(
                        'title' => esc_html__('Main Content - Right Sidebar', 'superio'),
                        'alt' => esc_html__('Main Content - Right Sidebar', 'superio'),
                        'img' => get_template_directory_uri() . '/inc/assets/images/screen3.png'
                    ),
                ),
                'default' => 'main'
            ),
            array(
                'id' => 'product_archive_left_sidebar',
                'type' => 'select',
                'title' => esc_html__('Archive Left Sidebar', 'superio'),
                'subtitle' => esc_html__('Choose a sidebar for left sidebar.', 'superio'),
                'options' => $sidebars,
                'required' => array('product_archive_layout', '=', 'left-main')
            ),
            array(
                'id' => 'product_archive_right_sidebar',
                'type' => 'select',
                'title' => esc_html__('Archive Right Sidebar', 'superio'),
                'subtitle' => esc_html__('Choose a sidebar for right sidebar.', 'superio'),
                'options' => $sidebars,
                'required' => array('product_archive_layout', '=', 'main-right')
            ),
        )
    );
    
    
    // Product Page
    $sections[] = array(
        'title' => esc_html__('Single Product', 'superio'),
        'subsection' => true,
        'fields' => array(
            array(
                'id' => 'product_general_setting',
                'icon' => true,
                'type' => 'info',
                'raw' => '<h3 style="margin: 0;"> '.esc_html__('General Setting', 'superio').'</h3>',
            ),
            array(
                'id' => 'product_thumbs_position',
                'type' => 'select',
                'title' => esc_html__('Thumbnails Position', 'superio'),
                'options' => array(
                    'thumbnails-left' => esc_html__('Thumbnails Left', 'superio'),
                    'thumbnails-right' => esc_html__('Thumbnails Right', 'superio'),
                    'thumbnails-bottom' => esc_html__('Thumbnails Bottom', 'superio'),
                ),
                'default' => 'thumbnails-bottom',
            ),
            array(
                'id' => 'number_product_thumbs',
                'title' => esc_html__('Number Thumbnails Per Row', 'superio'),
                'default' => 5,
                'min' => '1',
                'step' => '1',
                'max' => '8',
                'type' => 'slider',
            ),
            array(
                'id' => 'show_product_social_share',
                'type' => 'switch',
                'title' => esc_html__('Show Social Share', 'superio'),
                'default' => 1
            ),
            array(
                'id' => 'show_product_review_tab',
                'type' => 'switch',
                'title' => esc_html__('Show Product Review Tab', 'superio'),
                'default' => 1
            ),
            array(
                'id' => 'product_sidebar_setting',
                'icon' => true,
                'type' => 'info',
                'raw' => '<h3 style="margin: 0;"> '.esc_html__('Sidebar Setting', 'superio').'</h3>',
            ),
            array(
                'id' => 'product_single_layout',
                'type' => 'image_select',
                'compiler' => true,
                'title' => esc_html__('Single Product Sidebar Layout', 'superio'),
                'subtitle' => esc_html__('Select the layout you want to apply on your Single Product Page.', 'superio'),
                'options' => array(
                    'main' => array(
                        'title' => esc_html__('Main Only', 'superio'),
                        'alt' => esc_html__('Main Only', 'superio'),
                        'img' => get_template_directory_uri() . '/inc/assets/images/screen1.png'
                    ),
                    'left-main' => array(
                        'title' => esc_html__('Left - Main Sidebar', 'superio'),
                        'alt' => esc_html__('Left - Main Sidebar', 'superio'),
                        'img' => get_template_directory_uri() . '/inc/assets/images/screen2.png'
                    ),
                    'main-right' => array(
                        'title' => esc_html__('Main - Right Sidebar', 'superio'),
                        'alt' => esc_html__('Main - Right Sidebar', 'superio'),
                        'img' => get_template_directory_uri() . '/inc/assets/images/screen3.png'
                    ),
                ),
                'default' => 'main'
            ),
            array(
                'id' => 'product_single_fullwidth',
                'type' => 'switch',
                'title' => esc_html__('Is Full Width?', 'superio'),
                'default' => false
            ),
            array(
                'id' => 'product_single_left_sidebar',
                'type' => 'select',
                'title' => esc_html__('Single Product Left Sidebar', 'superio'),
                'subtitle' => esc_html__('Choose a sidebar for left sidebar.', 'superio'),
                'options' => $sidebars,
                'required' => array('product_single_layout', '=', 'left-main')
            ),
            array(
                'id' => 'product_single_right_sidebar',
                'type' => 'select',
                'title' => esc_html__('Single Product Right Sidebar', 'superio'),
                'subtitle' => esc_html__('Choose a sidebar for right sidebar.', 'superio'),
                'options' => $sidebars,
                'required' => array('product_single_layout', '=', 'main-right')
            ),
            array(
                'id' => 'product_block_setting',
                'icon' => true,
                'type' => 'info',
                'raw' => '<h3 style="margin: 0;"> '.esc_html__('Product Block Setting', 'superio').'</h3>',
            ),
            array(
                'id' => 'show_product_releated',
                'type' => 'switch',
                'title' => esc_html__('Show Products Releated', 'superio'),
                'default' => 1
            ),
            array(
                'id' => 'releated_product_columns',
                'type' => 'select',
                'title' => esc_html__('Releated Products Columns', 'superio'),
                'options' => $columns,
                'default' => 3,
                'required' => array('show_product_releated', '=', true)
            ),

            array(
                'id' => 'show_product_upsells',
                'type' => 'switch',
                'title' => esc_html__('Show Products upsells', 'superio'),
                'default' => 1
            ),
            array(
                'id' => 'upsells_product_columns',
                'type' => 'select',
                'title' => esc_html__('Upsells Products Columns', 'superio'),
                'options' => $columns,
                'default' => 3,
                'required' => array('show_product_upsells', '=', true)
            ),
        )
    );
    
    return $sections;
}
add_filter( 'superio_redux_framwork_configs', 'superio_woo_redux_config', 10, 3 );