<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function superio_ocdi_import_files() {
    $demos = array();
    for ($i=1; $i <= 22; $i++) {
        $redux_options_file = trailingslashit( get_template_directory() ) . 'inc/vendors/one-click-demo-import/default/redux-options.json';
        if ( $i == 5 ) {
            $redux_options_file = trailingslashit( get_template_directory() ) . 'inc/vendors/one-click-demo-import/default/redux-options5.json';
        } elseif ( $i == 10 ) {
            $redux_options_file = trailingslashit( get_template_directory() ) . 'inc/vendors/one-click-demo-import/default/redux-options10.json';
        }
        $demos[] = array(
            'import_file_name'             => 'Home '.$i,
            //'categories'                   => array( 'Job Board' ),
            'local_import_file'            => trailingslashit( get_template_directory() ) . 'inc/vendors/one-click-demo-import/default/dummy-data.xml',
            'local_import_widget_file'     => trailingslashit( get_template_directory() ) . 'inc/vendors/one-click-demo-import/default/widgets.wie',
            'local_import_redux'           => array(
                array(
                    'file_path'   => $redux_options_file,
                    'option_name' => 'superio_theme_options',
                ),
            ),
            'import_preview_image_url'     => trailingslashit( get_template_directory_uri() ) . 'inc/vendors/one-click-demo-import/default/home-'.$i.'.png',
            'import_notice'                => esc_html__( 'Import process may take 5-10 minutes. If you facing any issues please contact our support.', 'superio' ),
            'preview_url'                  => 'https://apusthemes.com/wp-demo/superio/',
        );
    }
    return apply_filters( 'superio_ocdi_files_args', $demos );
}
add_filter( 'pt-ocdi/import_files', 'superio_ocdi_import_files' );

function superio_ocdi_after_import_setup( $selected_import ) {
    // Assign menus to their locations.
    $main_menu       = get_term_by( 'name', 'Main Menu', 'nav_menu' );
    $mobile_menu       = get_term_by( 'name', 'Main Menu Mobile', 'nav_menu' );
    $employer_menu      = get_term_by( 'name', 'Employer', 'nav_menu' );
    $candidate_menu   = get_term_by( 'name', 'Candidate', 'nav_menu' );

    set_theme_mod( 'nav_menu_locations', array(
            'primary' => $main_menu->term_id,
            'mobile-primary' => $mobile_menu->term_id,
            'employer-menu' => $employer_menu->term_id,
            'candidate-menu' => $candidate_menu->term_id,
        )
    );

    // Assign front page and posts page (blog page) and other WooCommerce pages
    $blog_page_id       = get_page_by_title( 'Blog' );
    $shop_page_id       = get_page_by_title( 'Shop' );
    $cart_page_id       = get_page_by_title( 'Cart' );
    $checkout_page_id   = get_page_by_title( 'Checkout' );
    $myaccount_page_id  = get_page_by_title( 'My Account' );

    update_option( 'show_on_front', 'page' );
    
    update_option( 'page_for_posts', $blog_page_id->ID );
    update_option( 'woocommerce_shop_page_id', $shop_page_id->ID );
    update_option( 'woocommerce_cart_page_id', $cart_page_id->ID );
    update_option( 'woocommerce_checkout_page_id', $checkout_page_id->ID );
    update_option( 'woocommerce_myaccount_page_id', $myaccount_page_id->ID );
    update_option( 'woocommerce_enable_myaccount_registration', 'yes' );

    // elementor
    update_option( 'elementor_global_image_lightbox', 'yes' );
    update_option( 'elementor_disable_color_schemes', 'yes' );
    update_option( 'elementor_disable_typography_schemes', 'yes' );
    update_option( 'elementor_container_width', 1320 );

    $front_page_id = get_page_by_title( 'Default Kit', OBJECT, 'elementor_library' );
    update_option( 'elementor_active_kit', $front_page_id->ID );

    switch ($selected_import['import_file_name']) {
        case 'Home 1':
            $front_page_id = get_page_by_title( 'Home 1' );
            update_option( 'page_on_front', $front_page_id->ID );
            break;
        case 'Home 2':
            $front_page_id = get_page_by_title( 'Home 2' );
            update_option( 'page_on_front', $front_page_id->ID );
            break;
        case 'Home 3':
            $front_page_id = get_page_by_title( 'Home 3' );
            update_option( 'page_on_front', $front_page_id->ID );
            break;
        case 'Home 4':
            $front_page_id = get_page_by_title( 'Home 4' );
            update_option( 'page_on_front', $front_page_id->ID );
            break;
        case 'Home 5':
            $front_page_id = get_page_by_title( 'Home 5' );
            update_option( 'page_on_front', $front_page_id->ID );
            break;
        case 'Home 6':
            $front_page_id = get_page_by_title( 'Home 6' );
            update_option( 'page_on_front', $front_page_id->ID );
            break;
        case 'Home 7':
            $front_page_id = get_page_by_title( 'Home 7' );
            update_option( 'page_on_front', $front_page_id->ID );
            break;
        case 'Home 8':
            $front_page_id = get_page_by_title( 'Home 8' );
            update_option( 'page_on_front', $front_page_id->ID );
            break;
        case 'Home 9':
            $front_page_id = get_page_by_title( 'Home 9' );
            update_option( 'page_on_front', $front_page_id->ID );
            break;
        case 'Home 10':
            $front_page_id = get_page_by_title( 'Home 10' );
            update_option( 'page_on_front', $front_page_id->ID );
            break;
        case 'Home 11':
            $front_page_id = get_page_by_title( 'Home 11' );
            update_option( 'page_on_front', $front_page_id->ID );
            break;
        case 'Home 12':
            $front_page_id = get_page_by_title( 'Home 12' );
            update_option( 'page_on_front', $front_page_id->ID );
            break;
        case 'Home 13':
            $front_page_id = get_page_by_title( 'Home 13' );
            update_option( 'page_on_front', $front_page_id->ID );
            break;
        case 'Home 14':
            $front_page_id = get_page_by_title( 'Home 14' );
            update_option( 'page_on_front', $front_page_id->ID );
            break;
        case 'Home 15':
            $front_page_id = get_page_by_title( 'Home 15' );
            update_option( 'page_on_front', $front_page_id->ID );
            break;
        case 'Home 16':
            $front_page_id = get_page_by_title( 'Home 16' );
            update_option( 'page_on_front', $front_page_id->ID );
            break;
        case 'Home 17':
            $front_page_id = get_page_by_title( 'Home 17' );
            update_option( 'page_on_front', $front_page_id->ID );
            break;
        case 'Home 18':
            $front_page_id = get_page_by_title( 'Home 18' );
            update_option( 'page_on_front', $front_page_id->ID );
            break;
        case 'Home 19':
            $front_page_id = get_page_by_title( 'Home 19' );
            update_option( 'page_on_front', $front_page_id->ID );
            break;
        case 'Home 20':
            $front_page_id = get_page_by_title( 'Home 20' );
            update_option( 'page_on_front', $front_page_id->ID );
            break;
        case 'Home 21':
            $front_page_id = get_page_by_title( 'Home 21' );
            update_option( 'page_on_front', $front_page_id->ID );
            break;
        case 'Home 22':
            $front_page_id = get_page_by_title( 'Home 22' );
            update_option( 'page_on_front', $front_page_id->ID );
            break;
    }

    $file = trailingslashit( get_template_directory() ) . 'inc/vendors/one-click-demo-import/default/settings.json';
    if ( file_exists($file) ) {
        superio_ocdi_import_settings($file);
    }

    if ( superio_is_revslider_activated() ) {
        require_once( ABSPATH . 'wp-load.php' );
        require_once( ABSPATH . 'wp-includes/functions.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );

        $slider_array = array(
            trailingslashit( get_template_directory() ) . 'inc/vendors/one-click-demo-import/default/slider-1.zip',
            trailingslashit( get_template_directory() ) . 'inc/vendors/one-click-demo-import/default/slider-5.zip',
            trailingslashit( get_template_directory() ) . 'inc/vendors/one-click-demo-import/default/slider-6.zip',
            trailingslashit( get_template_directory() ) . 'inc/vendors/one-click-demo-import/default/slider-11.zip',
        );
        $slider = new RevSlider();

        foreach( $slider_array as $filepath ) {
            $slider->importSliderFromPost( true, true, $filepath );
        }
    }

}
add_action( 'pt-ocdi/after_import', 'superio_ocdi_after_import_setup' );

function superio_ocdi_import_settings($file) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
    require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
    $file_obj = new WP_Filesystem_Direct( array() );
    $datas = $file_obj->get_contents($file);
    $datas = json_decode( $datas, true );

    if ( count( array_filter( $datas ) ) < 1 ) {
        return;
    }

    if ( !empty($datas['page_options']) ) {
        superio_ocdi_import_page_options($datas['page_options']);
    }
    if ( !empty($datas['metadata']) ) {
        superio_ocdi_import_some_metadatas($datas['metadata']);
    }
}

function superio_ocdi_import_page_options($datas) {
    if ( $datas ) {
        foreach ($datas as $option_name => $page_id) {
            update_option( $option_name, $page_id);
        }
    }
}

function superio_ocdi_import_some_metadatas($datas) {
    if ( $datas ) {
        foreach ($datas as $slug => $post_types) {
            if ( $post_types ) {
                foreach ($post_types as $post_type => $metas) {
                    if ( $metas ) {
                        $args = array(
                            'name'        => $slug,
                            'post_type'   => $post_type,
                            'post_status' => 'publish',
                            'numberposts' => 1
                        );
                        $posts = get_posts($args);
                        if ( $posts && isset($posts[0]) ) {
                            foreach ($metas as $meta) {
                                update_post_meta( $posts[0]->ID, $meta['meta_key'], $meta['meta_value'] );
                                if ( $meta['meta_key'] == '_mc4wp_settings' ) {
                                    update_option( 'mc4wp_default_form_id', $posts[0]->ID );
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

function superio_ocdi_before_widgets_import() {

    $sidebars_widgets = get_option('sidebars_widgets');
    $all_widgets = array();

    array_walk_recursive( $sidebars_widgets, function ($item, $key) use ( &$all_widgets ) {
        if( ! isset( $all_widgets[$key] ) ) {
            $all_widgets[$key] = $item;
        } else {
            $all_widgets[] = $item;
        }
    } );

    if( isset( $all_widgets['array_version'] ) ) {
        $array_version = $all_widgets['array_version'];
        unset( $all_widgets['array_version'] );
    }

    $new_sidebars_widgets = array_fill_keys( array_keys( $sidebars_widgets ), array() );

    $new_sidebars_widgets['wp_inactive_widgets'] = $all_widgets;
    if( isset( $array_version ) ) {
        $new_sidebars_widgets['array_version'] = $array_version;
    }

    update_option( 'sidebars_widgets', $new_sidebars_widgets );
}
add_action( 'pt-ocdi/before_widgets_import', 'superio_ocdi_before_widgets_import' );