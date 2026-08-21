(function ($) {
    "use strict";

    if (!$.apusThemeExtensions)
        $.apusThemeExtensions = {};
    
    function ApusThemeCore() {
        var self = this;
        // self.init();
    };

    ApusThemeCore.prototype = {
        /**
         *  Initialize
         */
        init: function() {
            var self = this;
            
            self.preloadSite();

            // slick init
            self.initSlick($("[data-carousel=slick]"));

            // Unveil init
            setTimeout(function(){
                self.layzyLoadImage();
            }, 200);
            
            // isoto
            self.initIsotope();

            // Sticky Header
            
            self.initHeaderSticky('main-sticky-header');
            if ( $('#main-container .layout-type-half-map').length <= 0 ) {
                self.initHeaderSticky('header-mobile');
            }

            // back to top
            self.backToTop();

            // popup image
            self.popupImage();

            $('[data-toggle="tooltip"]').tooltip();

            self.initMobileMenu();

            self.mainMenuInit();
            
            self.dashboardMenu();

            $(window).resize(function(){
                setTimeout(function(){
                    self.dashboardMenu();
                }, 50);
            });
            
            self.advanceFilter();
            
            $('.more').on('click', function(){
                $('.wrapper-morelink').toggleClass('active');
            });
            

            $(document.body).on('click', '.nav [data-toggle="dropdown"]' ,function(){
                if ( this.href && this.href != '#'){
                    window.location.href = this.href;
                }
            });

            self.loadExtension();
        },
        /**
         *  Extensions: Load scripts
         */
        loadExtension: function() {
            var self = this;
            
            if ($.apusThemeExtensions.shop) {
                $.apusThemeExtensions.shop.call(self);
            }

            if ($.apusThemeExtensions.job_map) {
                $.apusThemeExtensions.job_map.call(self);
            }

            if ($.apusThemeExtensions.job) {
                $.apusThemeExtensions.job.call(self);
            }
        },
        initSlick: function(element) {
            var self = this;
            element.each( function(){
                var config = {
                    infinite: false,
                    arrows: $(this).data( 'nav' ),
                    dots: $(this).data( 'pagination' ),
                    slidesToShow: 4,
                    slidesToScroll: 4,
                    prevArrow:"<button type='button' class='slick-arrow slick-prev'><i class='ti-angle-left' aria-hidden='true'></i></span><span class='textnav'>"+ superio_ajax.previous +"</span></button>",
                    nextArrow:"<button type='button' class='slick-arrow slick-next'><span class='textnav'>"+ superio_ajax.next +"</span><i class='ti-angle-right' aria-hidden='true'></i></button>",
                };
            
                var slick = $(this);
                if( $(this).data('items') ){
                    config.slidesToShow = $(this).data( 'items' );
                    config.slidesToScroll = $(this).data( 'items' );
                }
                if( $(this).data('infinite') ){
                    config.infinite = true;
                }
                if( $(this).data('autoplay') ){
                    config.autoplay = true;
                    config.autoplaySpeed = 1500;
                    config.pauseOnHover = true;
                }
                if( $(this).data('disable_draggable') ){
                    config.touchMove = false;
                    config.draggable = false;
                    config.swipe = false;
                    config.swipeToSlide = false;
                }
                if( $(this).data('centermode') ){
                    config.centerMode = true;
                }
                if( $(this).data('centerpadding') ){
                    config.centerPadding = $(this).data('centerpadding');;
                }
                if( $(this).data('vertical') ){
                    config.vertical = true;
                }
                if( $(this).data('rows') ){
                    config.rows = $(this).data( 'rows' );
                }
                if( $(this).data('asnavfor') ){
                    config.asNavFor = $(this).data( 'asnavfor' );
                }
                if( $(this).data('slidestoscroll') ){
                    config.slidesToScroll = $(this).data( 'slidestoscroll' );
                }
                if( $(this).data('focusonselect') ){
                    config.focusOnSelect = $(this).data( 'focusonselect' );
                }
                if ($(this).data('large')) {
                    var desktop = $(this).data('large');
                } else {
                    var desktop = config.items;
                }
                if ($(this).data('smalldesktop')) {
                    var smalldesktop = $(this).data('smalldesktop');
                } else {
                    if ($(this).data('large')) {
                        var smalldesktop = $(this).data('large');
                    } else{
                        var smalldesktop = config.items;
                    }
                }
                if ($(this).data('medium')) {
                    var medium = $(this).data('medium');
                } else {
                    var medium = config.items;
                }
                if ($(this).data('smallmedium')) {
                    var smallmedium = $(this).data('smallmedium');
                } else {
                    var smallmedium = 2;
                }
                if ($(this).data('extrasmall')) {
                    var extrasmall = $(this).data('extrasmall');
                } else {
                    var extrasmall = 2;
                }
                if ($(this).data('smallest')) {
                    var smallest = $(this).data('smallest');
                } else {
                    var smallest = 1;
                }

                if ($(this).data('slidestoscroll_large')) {
                    var slidestoscroll_desktop = $(this).data('slidestoscroll_large');
                } else {
                    var slidestoscroll_desktop = config.slidesToScroll;
                }
                if ($(this).data('slidestoscroll_smalldesktop')) {
                    var slidestoscroll_smalldesktop = $(this).data('slidestoscroll_smalldesktop');
                } else {
                    if ($(this).data('slidestoscroll_large')) {
                        var slidestoscroll_smalldesktop = $(this).data('slidestoscroll_large');
                    } else{
                        var slidestoscroll_smalldesktop = config.items;
                    }
                }
                if ($(this).data('slidestoscroll_medium')) {
                    var slidestoscroll_medium = $(this).data('slidestoscroll_medium');
                } else {
                    var slidestoscroll_medium = config.items;
                }
                if ($(this).data('slidestoscroll_smallmedium')) {
                    var slidestoscroll_smallmedium = $(this).data('slidestoscroll_smallmedium');
                } else {
                    var slidestoscroll_smallmedium = smallmedium;
                }
                if ($(this).data('slidestoscroll_extrasmall')) {
                    var slidestoscroll_extrasmall = $(this).data('slidestoscroll_extrasmall');
                } else {
                    var slidestoscroll_extrasmall = extrasmall;
                }
                if ($(this).data('slidestoscroll_smallest')) {
                    var slidestoscroll_smallest = $(this).data('slidestoscroll_smallest');
                } else {
                    var slidestoscroll_smallest = smallest;
                }
                config.responsive = [
                    {
                        breakpoint: 321,
                        settings: {
                            slidesToShow: smallest,
                            slidesToScroll: slidestoscroll_smallest,
                        }
                    },
                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: extrasmall,
                            slidesToScroll: slidestoscroll_extrasmall,
                        }
                    },
                    {
                        breakpoint: 769,
                        settings: {
                            slidesToShow: smallmedium,
                            slidesToScroll: slidestoscroll_smallmedium
                        }
                    },
                    {
                        breakpoint: 981,
                        settings: {
                            slidesToShow: medium,
                            slidesToScroll: slidestoscroll_medium
                        }
                    },
                    {
                        breakpoint: 1200,
                        settings: {
                            slidesToShow: smalldesktop,
                            slidesToScroll: slidestoscroll_smalldesktop
                        }
                    },
                    {
                        breakpoint: 1501,
                        settings: {
                            slidesToShow: desktop,
                            slidesToScroll: slidestoscroll_desktop
                        }
                    }
                ];
                if ( $('html').attr('dir') == 'rtl' ) {
                    config.rtl = true;
                }

                $(this).slick( config );

            } );

            // Fix slick in bootstrap tabs
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var target = $(e.target).attr("href");
                var $slick = $("[data-carousel=slick]", target);

                if ($slick.length > 0 && $slick.hasClass('slick-initialized')) {
                    $slick.slick('refresh');
                }
                self.layzyLoadImage();
            });
        },
        layzyLoadImage: function() {
            $(window).off('scroll.unveil resize.unveil lookup.unveil');
            var $images = $('.image-wrapper:not(.image-loaded) .unveil-image'); // Get un-loaded images only
            if ($images.length) {
                $images.unveil(1, function() {
                    $(this).load(function() {
                        $(this).parents('.image-wrapper').first().addClass('image-loaded');
                        $(this).removeAttr('data-src');
                        $(this).removeAttr('data-srcset');
                        $(this).removeAttr('data-sizes');
                    });
                });
            }

            var $images = $('.product-image:not(.image-loaded) .unveil-image'); // Get un-loaded images only
            if ($images.length) {
                $images.unveil(1, function() {
                    $(this).load(function() {
                        $(this).parents('.product-image').first().addClass('image-loaded');
                    });
                });
            }
        },
        initIsotope: function() {
            $('.isotope-items').each(function(){  
                var $container = $(this);
                
                $container.imagesLoaded( function(){
                    $container.isotope({
                        itemSelector : '.isotope-item',
                        transformsEnabled: true,         // Important for videos
                        masonry: {
                            columnWidth: $container.data('columnwidth')
                        }
                    }); 
                });
            });

            /*---------------------------------------------- 
             *    Apply Filter        
             *----------------------------------------------*/
            $('.isotope-filter li a').on('click', function(){
               
                var parentul = $(this).parents('ul.isotope-filter').data('related-grid');
                $(this).parents('ul.isotope-filter').find('li a').removeClass('active');
                $(this).addClass('active');
                var selector = $(this).attr('data-filter'); 
                $('#'+parentul).isotope({ filter: selector }, function(){ });
                
                return(false);
            });
        },

        initHeaderSticky: function(main_sticky_class) {
            if ( $('.' + main_sticky_class).length ) {

                if ( typeof Waypoint !== 'undefined' ) {
                    
                    if ( $('.' + main_sticky_class) && typeof Waypoint.Sticky !== 'undefined' ) {
                        
                        var sticky = new Waypoint.Sticky({
                            element: $('.' + main_sticky_class)[0],
                            wrapper: '<div class="main-sticky-header-wrapper">',
                            offset: '-10px',
                            stuckClass: 'sticky-header'
                        });
                    }
                }
            }
        },
        backToTop: function () {
            $(window).scroll(function () {
                if ($(this).scrollTop() > 400) {
                    $('#back-to-top').addClass('active');
                } else {
                    $('#back-to-top').removeClass('active');
                }
            });
            $('#back-to-top').on('click', function () {
                $('html, body').animate({scrollTop: '0px'}, 800);
                return false;
            });
        },
        
        popupImage: function() {
            // popup
            $(".popup-image").magnificPopup({type:'image'});
            $('.popup-video').magnificPopup({
                disableOn: 700,
                type: 'iframe',
                mainClass: 'mfp-fade',
                removalDelay: 160,
                preloader: false,
                fixedContentPos: false
            });

            $('.candidate-detail-portfolio11').each(function(){
                var tagID = $(this).attr('id');
                $('#' + tagID).magnificPopup({
                    delegate: '.popup-image-gallery',
                    type: 'image',
                    tLoading: 'Loading image #%curr%...',
                    mainClass: 'mfp-img-mobile',
                    gallery: {
                        enabled: true,
                        navigateByImgClick: true,
                        preload: [0,1] // Will preload 0 - before current, and 1 after the current image
                    }
                });
            });

            $('body').on('click', '.close-magnific-popup', function() {
                $.magnificPopup.close();
            });
        },
        preloadSite: function() {
            // preload page
            if ( $('body').hasClass('apus-body-loading') ) {
                setTimeout(function(){
                    $('body').removeClass('apus-body-loading');
                    $('.apus-page-loading').fadeOut(100);
                }, 100);
            }
        },

        initMobileMenu: function() {

            // stick mobile
            var self = this;

            // mobile menu
            $('.btn-toggle-canvas,.btn-showmenu').on('click', function (e) {
                e.stopPropagation();
                $('.apus-offcanvas').toggleClass('active');           
                $('.over-dark').toggleClass('active');

                $("#mobile-menu-container").slidingMenu({
                    backLabel: superio_ajax.menu_back_text
                });
            });
            $('body').on('click', function() {
                if ($('.apus-offcanvas').hasClass('active')) {
                    $('.apus-offcanvas').toggleClass('active');
                    $('.over-dark').toggleClass('active');
                }
            });
            $('.apus-offcanvas').on('click', function(e) {
                e.stopPropagation();
            });
            
            // $('.apus-offcanvas-body').perfectScrollbar();
            
            // sidebar mobile
            if ($(window).width() < 992) {
                $('.sidebar-wrapper > .sidebar-right, .sidebar-wrapper > .sidebar-left').perfectScrollbar();
            }
            $('body').on('click', '.mobile-sidebar-btn', function(){
                if ( $('.sidebar-left').length > 0 ) {
                    $('.sidebar-left').toggleClass('active');
                } else if ( $('.sidebar-right').length > 0 ) {
                    $('.sidebar-right').toggleClass('active');
                }
                $('.mobile-sidebar-panel-overlay').toggleClass('active');
            });
            $('body').on('click', '.mobile-sidebar-panel-overlay, .close-sidebar-btn', function(){
                if ( $('.sidebar-left').length > 0 ) {
                    $('.sidebar-left').removeClass('active');
                } else if ( $('.sidebar-right').length > 0 ) {
                    $('.sidebar-right').removeClass('active');
                }
                $('.mobile-sidebar-panel-overlay').removeClass('active');
            });

            $('#apus-header-mobile .btn-menu-account').on('click', function (e) {
                e.stopPropagation();
                $(this).closest('.top-wrapper-menu').toggleClass('active'); 
            });
            $('body').on('click', function() {
                $('#apus-header-mobile .top-wrapper-menu').removeClass('active');
            });
            $('#apus-header-mobile .top-wrapper-menu .inner-top-menu').on('click', function(e) {
                e.stopPropagation();
            });
            
        },
        advanceFilter: function() {
            $('body').on('click', '.filter-job-top .toggle-adv', function(e){
                e.preventDefault();
                $('.filter-job-top .advance-fields').toggle('slow');
            });
        },
        mainMenuInit: function() {
            $('.apus-megamenu .megamenu .has-mega-menu.aligned-fullwidth').each(function(e){
                var $this = $(this),
                    i = $this.closest(".elementor-section"),
                    a = $this.closest('.apus-megamenu');

                if ( !$this.find('.elementor-element').hasClass('elementor-section-stretched') ) {
                    $this.on('mouseenter', function(){
                        var m = $(this).find('> .dropdown-menu .dropdown-menu-inner'),
                            w = i.outerWidth();

                        m.css({
                            width: w,
                            marginLeft: i.offset().left - $this.offset().left
                        });
                    });
                }

                
            });

            $('.apus-megamenu .megamenu').each(function(e){
                var $this = $(this);
                $this.find('.elementor-element').addClass('no-transparent');
            });
        },
        dashboardMenu: function() {
            $('body').on('click', '.together-sidebar-account', function(e){
                e.preventDefault();
                $('.inner-dashboard.container-fluid').toggleClass('active');
            });

            if ($(window).width() > 991) {
                if ( $('#apus-header').length > 0 ) {
                    var header_height = $('#apus-header').outerHeight();
                    $('.inner-dashboard.container-fluid .sidebar-wrapper .sidebar').css({ 'top': header_height, 'height' : 'calc(100vh - ' + header_height + 'px)' });
                }
            } else {
                if ( $('#apus-header-mobile').length > 0 ) {
                    $('.inner-dashboard.container-fluid .sidebar-wrapper .sidebar').css({ 'top': 0, 'height' : 'calc(100vh)' });
                }
            }
            $('.inner-dashboard.container-fluid aside.sidebar').perfectScrollbar();
        },

        setCookie: function(cname, cvalue, exdays) {
            var d = new Date();
            d.setTime(d.getTime() + (exdays*24*60*60*1000));
            var expires = "expires="+d.toUTCString();
            document.cookie = cname + "=" + cvalue + "; " + expires+";path=/";
        },
        getCookie: function(cname) {
            var name = cname + "=";
            var ca = document.cookie.split(';');
            for(var i=0; i<ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0)==' ') c = c.substring(1);
                if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
            }
            return "";
        }
    }

    $.apusThemeCore = ApusThemeCore.prototype;
    
    
    $.fn.wrapStart = function(numWords){
        return this.each(function(){
            var $this = $(this);
            var node = $this.contents().filter(function(){
                return this.nodeType == 3;
            }).first(),
            text = node.text().trim(),
            first = text.split(' ', 1).join(" ");
            if (!node.length) return;
            node[0].nodeValue = text.slice(first.length);
            node.before('<b>' + first + '</b>');
        });
    };

    $(document).ready(function() {
        // Initialize script
        var apusthemecore_init = new ApusThemeCore();
        apusthemecore_init.init();

        $('.mod-heading .widget-title > span').wrapStart(1);
    });

    jQuery(window).on("elementor/frontend/init", function() {
        
        var apusthemecore_init = new ApusThemeCore();

        // General element
        elementorFrontend.hooks.addAction( "frontend/element_ready/apus_element_brands.default",
            function($scope) {
                apusthemecore_init.initSlick($scope.find('.slick-carousel'));
            }
        );

        elementorFrontend.hooks.addAction( "frontend/element_ready/apus_element_features_box.default",
            function($scope) {
                apusthemecore_init.initSlick($scope.find('.slick-carousel'));
            }
        );

        elementorFrontend.hooks.addAction( "frontend/element_ready/apus_element_instagram.default",
            function($scope) {
                apusthemecore_init.initSlick($scope.find('.slick-carousel'));
            }
        );

        elementorFrontend.hooks.addAction( "frontend/element_ready/apus_element_posts.default",
            function($scope) {
                apusthemecore_init.initSlick($scope.find('.slick-carousel'));
            }
        );

        elementorFrontend.hooks.addAction( "frontend/element_ready/apus_element_testimonials.default",
            function($scope) {
                apusthemecore_init.initSlick($scope.find('.slick-carousel'));
            }
        );

        // jobs elements
        elementorFrontend.hooks.addAction( "frontend/element_ready/apus_element_job_board_pro_jobs.default",
            function($scope) {
                apusthemecore_init.initSlick($scope.find('.slick-carousel'));
            }
        );

        elementorFrontend.hooks.addAction( "frontend/element_ready/apus_element_job_board_pro_jobs_tabs.default",
            function($scope) {
                apusthemecore_init.initSlick($scope.find('.slick-carousel'));
            }
        );

        elementorFrontend.hooks.addAction( "frontend/element_ready/apus_element_job_board_pro_candidates.default",
            function($scope) {
                apusthemecore_init.initSlick($scope.find('.slick-carousel'));
            }
        );
        
        elementorFrontend.hooks.addAction( "frontend/element_ready/apus_element_job_board_pro_employers.default",
            function($scope) {
                apusthemecore_init.initSlick($scope.find('.slick-carousel'));
            }
        );

        elementorFrontend.hooks.addAction( "frontend/element_ready/apus_element_job_board_pro_jobs_vertical_carousel.default",
            function($scope) {
                apusthemecore_init.initSlick($scope.find('.slick-carousel'));

                $scope.find('.slick-carousel').on('wheel', (function(e) {
                    e.preventDefault();

                    if (e.originalEvent.deltaY < 0) {
                        $(this).slick('slickPrev');
                    } else {
                        $(this).slick('slickNext');
                    }
                }));
            }
        );

    });

})(jQuery);

