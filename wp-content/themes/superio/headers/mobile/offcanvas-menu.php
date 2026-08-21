<div id="apus-mobile-menu" class="apus-offcanvas hidden-lg"> 
    <div class="apus-offcanvas-body">

        <div class="header-offcanvas">
            <div class="container">
                <div class="flex-middle">
                    <?php
                        $logo = superio_get_config('media-mobile-logo');
                        $logo_url = '';
                        if ( !empty($logo['id']) ) {
                            $img = wp_get_attachment_image_src($logo['id'], 'full');
                            if ( !empty($img[0]) ) {
                                $logo_url = $img[0];
                            }
                        }
                    ?>
                    <?php if( isset($logo['url']) && !empty($logo['url']) ): ?>
                        <div class="logo">
                            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" >
                                <img src="<?php echo esc_url( $logo['url'] ); ?>" alt="<?php echo esc_attr(get_bloginfo( 'name' )); ?>">
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="logo logo-theme">
                            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" >
                                <img src="<?php echo esc_url_raw( get_template_directory_uri().'/images/logo.svg'); ?>" alt="<?php echo esc_attr(get_bloginfo( 'name' )); ?>">
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="ali-right flex-middle">
                        <?php if ( superio_is_wp_job_board_pro_activated() ) { ?>
                            <?php if ( superio_get_config('show_login_register', true) ) {
                                if ( is_user_logged_in() ) {
                                    $page_id = wp_job_board_pro_get_option('user_dashboard_page_id');
                                } else {
                                    $page_id = wp_job_board_pro_get_option('login_register_page_id');
                                }
                            ?>
                                <a class="btn-menu-account" href="<?php echo esc_url( get_permalink( $page_id ) ); ?>">
                                    <i class="flaticon-user"></i>
                                </a>

                            <?php } ?>
                        <?php } ?>
                        <a class="btn-toggle-canvas" data-toggle="offcanvas">
                            <i class="ti-close"></i>
                        </a>
                    </div>
                </div>

            </div>
        </div>
        <div class="offcanvas-content flex-column flex">
            <div class="middle-offcanvas">

                <nav id="menu-main-menu-navbar" class="navbar navbar-offcanvas" role="navigation">
                    <?php
                        $mobile_menu = 'primary';
                        $menus = get_nav_menu_locations();
                        if( !empty($menus['mobile-primary']) && wp_get_nav_menu_items($menus['mobile-primary'])) {
                            $mobile_menu = 'mobile-primary';
                        }
                        $args = array(
                            'theme_location' => $mobile_menu,
                            'container_class' => '',
                            'menu_class' => '',
                            'fallback_cb' => '',
                            'menu_id' => '',
                            'container' => 'div',
                            'container_id' => 'mobile-menu-container',
                            'walker' => new Superio_Mobile_Menu()
                        );
                        wp_nav_menu($args);

                    ?>
                </nav>
            </div>
        
            <?php if ( is_active_sidebar( 'header-mobile-bottom' ) || superio_is_wp_job_board_pro_activated() ) { ?>
                
                <div class="header-mobile-bottom">

                    <?php if ( superio_is_wp_job_board_pro_activated() && superio_get_config('show_add_job_btn', true) ) { ?>
                        <?php $submit_job_form_page_id = wp_job_board_pro_get_option('submit_job_form_page_id'); ?>
                        <div class="submit-job">
                            <a class="btn btn-theme btn-block" href="<?php echo esc_url( get_permalink( $submit_job_form_page_id ) ); ?>"><?php esc_html_e('Post Job','superio') ?></a>
                        </div>
                    <?php } ?>

                    <?php if ( is_active_sidebar( 'header-mobile-bottom' ) ){ ?>
                        <?php dynamic_sidebar( 'header-mobile-bottom' ); ?>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<div class="over-dark"></div>