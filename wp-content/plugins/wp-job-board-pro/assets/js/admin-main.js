(function ($) {
    "use strict";

    if (!$.wjbpAdminExtensions)
        $.wjbpAdminExtensions = {};
    
    function WJBPAdminMainCore() {
        var self = this;
        self.init();
    };

    WJBPAdminMainCore.prototype = {
        /**
         *  Initialize
         */
        init: function() {
            var self = this;

            self.taxInit();

            self.emailSettings();

            self.mixes();
        },
        taxInit: function() {
            $('.tax_color_input').wpColorPicker();
        },
        emailSettings: function() {
            var show_hiden_action = function(key, checked) {
                if ( checked ) {
                    $('.cmb2-id-' + key + '-subject').show();
                    $('.cmb2-id-' + key + '-content').show();
                } else {
                    $('.cmb2-id-' + key + '-subject').hide();
                    $('.cmb2-id-' + key + '-content').hide();
                }
            }
            $('#admin_notice_add_new_listing').on('change', function(){
                var key = 'admin-notice-add-new-listing';
                var checked = $(this).is(":checked");
                show_hiden_action(key, checked);
            });
            var checked = $('#admin_notice_add_new_listing').is(":checked");
            var key = 'admin-notice-add-new-listing';
            show_hiden_action(key, checked);

            // updated
            $('#admin_notice_updated_listing').on('change', function(){
                var key = 'admin-notice-updated-listing';
                var checked = $(this).is(":checked");
                show_hiden_action(key, checked);
            });
            var checked = $('#admin_notice_updated_listing').is(":checked");
            var key = 'admin-notice-updated-listing';
            show_hiden_action(key, checked);

            // admin expiring
            $('#admin_notice_expiring_listing').on('change', function(){
                var key = 'admin-notice-expiring-listing';
                var checked = $(this).is(":checked");
                show_hiden_action(key, checked);
                if ( checked ) {
                    $('.cmb2-id-admin-notice-expiring-listing-days').show();
                } else {
                    $('.cmb2-id-admin-notice-expiring-listing-days').hide();
                }
            });
            var checked = $('#admin_notice_expiring_listing').is(":checked");
            var key = 'admin-notice-expiring-listing';
            show_hiden_action(key, checked);
            if ( checked ) {
                $('.cmb2-id-admin-notice-expiring-listing-days').show();
            } else {
                $('.cmb2-id-admin-notice-expiring-listing-days').hide();
            }

            // employer expiring
            $('#employer_notice_expiring_listing').on('change', function(){
                var key = 'employer-notice-expiring-listing';
                var checked = $(this).is(":checked");
                show_hiden_action(key, checked);

                if ( checked ) {
                    $('.cmb2-id-employer-notice-expiring-listing-days').show();
                } else {
                    $('.cmb2-id-employer-notice-expiring-listing-days').hide();
                }
            });
            var checked = $('#employer_notice_expiring_listing').is(":checked");
            var key = 'employer-notice-expiring-listing';
            show_hiden_action(key, checked);
            if ( checked ) {
                $('.cmb2-id-employer-notice-expiring-listing-days').show();
            } else {
                $('.cmb2-id-employer-notice-expiring-listing-days').hide();
            }
        },
        mixes: function() {
            var map_service = $('.cmb2-id-map-service select').val();
            if ( map_service == 'mapbox' ) {
                $('.cmb2-id-google-map-api-keys').hide();
                $('.cmb2-id-google-map-style').hide();
                $('.cmb2-id-here-map-api-key').hide();
                $('.cmb2-id-here-map-style').hide();
                $('.cmb2-id-mapbox-token').show();
                $('.cmb2-id-mapbox-style').show();

            } else if ( map_service == 'here' ) {
                $('.cmb2-id-google-map-api-keys').hide();
                $('.cmb2-id-google-map-style').hide();
                $('.cmb2-id-mapbox-token').hide();
                $('.cmb2-id-mapbox-style').hide();

                $('.cmb2-id-here-map-api-key').show();
                $('.cmb2-id-here-map-style').show();
            } else if ( map_service == 'openstreetmap' ) {
                $('.cmb2-id-google-map-api-keys').hide();
                $('.cmb2-id-google-map-style').hide();
                $('.cmb2-id-mapbox-token').hide();
                $('.cmb2-id-mapbox-style').hide();

                $('.cmb2-id-here-map-api-key').hide();
                $('.cmb2-id-here-map-style').hide();
            } else {
                $('.cmb2-id-google-map-api-keys').show();
                $('.cmb2-id-google-map-style').show();
                $('.cmb2-id-mapbox-token').hide();
                $('.cmb2-id-mapbox-style').hide();
                $('.cmb2-id-here-map-style').hide();
                $('.cmb2-id-here-map-api-key').hide();
            }

            $('.cmb2-id-map-service select').on('change', function() {
                var map_service = $(this).val();
                if ( map_service == 'mapbox' ) {
                    $('.cmb2-id-google-map-api-keys').hide();
                    $('.cmb2-id-google-map-style').hide();
                    $('.cmb2-id-here-map-api-key').hide();
                    $('.cmb2-id-here-map-style').hide();
                    $('.cmb2-id-mapbox-token').show();
                    $('.cmb2-id-mapbox-style').show();

                } else if ( map_service == 'here' ) {
                    $('.cmb2-id-google-map-api-keys').hide();
                    $('.cmb2-id-google-map-style').hide();
                    $('.cmb2-id-mapbox-token').hide();
                    $('.cmb2-id-mapbox-style').hide();

                    $('.cmb2-id-here-map-api-key').show();
                    $('.cmb2-id-here-map-style').show();
                } else {
                    $('.cmb2-id-google-map-api-keys').show();
                    $('.cmb2-id-google-map-style').show();
                    $('.cmb2-id-mapbox-token').hide();
                    $('.cmb2-id-mapbox-style').hide();
                    $('.cmb2-id-here-map-style').hide();
                    $('.cmb2-id-here-map-api-key').hide();
                }
            });

            //
            var location_type = $('.cmb2-id-location-multiple-fields select').val();
            if ( location_type == 'yes' ) {
                $('.cmb2-id-location-nb-fields').show();
                $('.cmb2-id-location-1-field-label').show();
                $('.cmb2-id-location-2-field-label').show();
                $('.cmb2-id-location-3-field-label').show();
                $('.cmb2-id-location-4-field-label').show();
            } else {
                $('.cmb2-id-location-nb-fields').hide();
                $('.cmb2-id-location-1-field-label').hide();
                $('.cmb2-id-location-2-field-label').hide();
                $('.cmb2-id-location-3-field-label').hide();
                $('.cmb2-id-location-4-field-label').hide();
            }

            $('.cmb2-id-location-multiple-fields select').on('change', function() {
                var location_type = $(this).val();
                if ( location_type == 'yes' ) {
                    $('.cmb2-id-location-nb-fields').show();
                    $('.cmb2-id-location-1-field-label').show();
                    $('.cmb2-id-location-2-field-label').show();
                    $('.cmb2-id-location-3-field-label').show();
                    $('.cmb2-id-location-4-field-label').show();
                } else {
                    $('.cmb2-id-location-nb-fields').hide();
                    $('.cmb2-id-location-1-field-label').hide();
                    $('.cmb2-id-location-2-field-label').hide();
                    $('.cmb2-id-location-3-field-label').hide();
                    $('.cmb2-id-location-4-field-label').hide();
                }
            });
            
            // free
            var free_apply = $('#candidate_free_job_apply').val();
            if ( free_apply == 'off' ) {
                $('.cmb2-id-candidate-package-page-id').css({'display': 'block'});
            } else {
                $('.cmb2-id-candidate-package-page-id').css({'display': 'none'});
            }
            $('#candidate_free_job_apply').on('change', function() {
                var free_apply = $(this).val();
                if ( free_apply == 'off' ) {
                    $('.cmb2-id-candidate-package-page-id').css({'display': 'block'});
                } else {
                    $('.cmb2-id-candidate-package-page-id').css({'display': 'none'});
                }
            });

            // restrict candidate
            var restrict_type = $('#candidate_restrict_type').val();
            if ( restrict_type == 'view' ) {
                $('.cmb2-id-candidate-restrict-detail').css({'display': 'block'});
                $('.cmb2-id-candidate-restrict-listing').css({'display': 'block'});
                $('.cmb2-id-candidate-restrict-contact-info').css({'display': 'none'});

                $('.cmb2-id-restrict-contact-candidate-name').css({'display': 'none'});
                $('.cmb2-id-restrict-contact-candidate-email').css({'display': 'none'});
                $('.cmb2-id-restrict-contact-candidate-phone').css({'display': 'none'});
                $('.cmb2-id-restrict-contact-candidate-social').css({'display': 'none'});
                $('.cmb2-id-restrict-contact-candidate-download-cv').css({'display': 'none'});
            } else if ( restrict_type == 'view_contact_info' ) {
                $('.cmb2-id-candidate-restrict-detail').css({'display': 'none'});
                $('.cmb2-id-candidate-restrict-listing').css({'display': 'none'});
                $('.cmb2-id-candidate-restrict-contact-info').css({'display': 'block'});

                $('.cmb2-id-restrict-contact-candidate-name').css({'display': 'block'});
                $('.cmb2-id-restrict-contact-candidate-email').css({'display': 'block'});
                $('.cmb2-id-restrict-contact-candidate-phone').css({'display': 'block'});
                $('.cmb2-id-restrict-contact-candidate-social').css({'display': 'block'});
                $('.cmb2-id-restrict-contact-candidate-download-cv').css({'display': 'block'});
            } else {
                $('.cmb2-id-candidate-restrict-detail').css({'display': 'none'});
                $('.cmb2-id-candidate-restrict-listing').css({'display': 'none'});
                $('.cmb2-id-candidate-restrict-contact-info').css({'display': 'none'});

                $('.cmb2-id-restrict-contact-candidate-name').css({'display': 'none'});
                $('.cmb2-id-restrict-contact-candidate-email').css({'display': 'none'});
                $('.cmb2-id-restrict-contact-candidate-phone').css({'display': 'none'});
                $('.cmb2-id-restrict-contact-candidate-social').css({'display': 'none'});
                $('.cmb2-id-restrict-contact-candidate-download-cv').css({'display': 'none'});
            }
            $('#candidate_restrict_type').on('change', function() {
                var restrict_type = $(this).val();
                if ( restrict_type == 'view' ) {
                    $('.cmb2-id-candidate-restrict-detail').css({'display': 'block'});
                    $('.cmb2-id-candidate-restrict-listing').css({'display': 'block'});
                    $('.cmb2-id-candidate-restrict-contact-info').css({'display': 'none'});

                    $('.cmb2-id-restrict-contact-candidate-name').css({'display': 'none'});
                    $('.cmb2-id-restrict-contact-candidate-email').css({'display': 'none'});
                    $('.cmb2-id-restrict-contact-candidate-phone').css({'display': 'none'});
                    $('.cmb2-id-restrict-contact-candidate-social').css({'display': 'none'});
                    $('.cmb2-id-restrict-contact-candidate-download-cv').css({'display': 'none'});

                } else if ( restrict_type == 'view_contact_info' ) {
                    $('.cmb2-id-candidate-restrict-detail').css({'display': 'none'});
                    $('.cmb2-id-candidate-restrict-listing').css({'display': 'none'});
                    $('.cmb2-id-candidate-restrict-contact-info').css({'display': 'block'});

                    $('.cmb2-id-restrict-contact-candidate-name').css({'display': 'block'});
                    $('.cmb2-id-restrict-contact-candidate-email').css({'display': 'block'});
                    $('.cmb2-id-restrict-contact-candidate-phone').css({'display': 'block'});
                    $('.cmb2-id-restrict-contact-candidate-social').css({'display': 'block'});
                    $('.cmb2-id-restrict-contact-candidate-download-cv').css({'display': 'block'});
                } else {
                    $('.cmb2-id-candidate-restrict-detail').css({'display': 'none'});
                    $('.cmb2-id-candidate-restrict-listing').css({'display': 'none'});
                    $('.cmb2-id-candidate-restrict-contact-info').css({'display': 'none'});

                    $('.cmb2-id-restrict-contact-candidate-name').css({'display': 'none'});
                    $('.cmb2-id-restrict-contact-candidate-email').css({'display': 'none'});
                    $('.cmb2-id-restrict-contact-candidate-phone').css({'display': 'none'});
                    $('.cmb2-id-restrict-contact-candidate-social').css({'display': 'none'});
                    $('.cmb2-id-restrict-contact-candidate-download-cv').css({'display': 'none'});
                }
            });

            // restrict employer
            var restrict_type = $('#employer_restrict_type').val();
            if ( restrict_type == 'view' ) {
                $('.cmb2-id-employer-restrict-detail').css({'display': 'block'});
                $('.cmb2-id-employer-restrict-listing').css({'display': 'block'});
                $('.cmb2-id-employer-restrict-contact-info').css({'display': 'none'});

                $('.cmb2-id-restrict-contact-employer-name').css({'display': 'none'});
                $('.cmb2-id-restrict-contact-employer-email').css({'display': 'none'});
                $('.cmb2-id-restrict-contact-employer-phone').css({'display': 'none'});
                $('.cmb2-id-restrict-contact-employer-social').css({'display': 'none'});
            } else if ( restrict_type == 'view_contact_info' ) {
                $('.cmb2-id-employer-restrict-detail').css({'display': 'none'});
                $('.cmb2-id-employer-restrict-listing').css({'display': 'none'});
                $('.cmb2-id-employer-restrict-contact-info').css({'display': 'block'});

                $('.cmb2-id-restrict-contact-employer-name').css({'display': 'block'});
                $('.cmb2-id-restrict-contact-employer-email').css({'display': 'block'});
                $('.cmb2-id-restrict-contact-employer-phone').css({'display': 'block'});
                $('.cmb2-id-restrict-contact-employer-social').css({'display': 'block'});
            } else {
                $('.cmb2-id-employer-restrict-detail').css({'display': 'none'});
                $('.cmb2-id-employer-restrict-listing').css({'display': 'none'});
                $('.cmb2-id-employer-restrict-contact-info').css({'display': 'none'});

                $('.cmb2-id-restrict-contact-employer-name').css({'display': 'none'});
                $('.cmb2-id-restrict-contact-employer-email').css({'display': 'none'});
                $('.cmb2-id-restrict-contact-employer-phone').css({'display': 'none'});
                $('.cmb2-id-restrict-contact-employer-social').css({'display': 'none'});
            }
            $('#employer_restrict_type').on('change', function() {
                var restrict_type = $(this).val();
                if ( restrict_type == 'view' ) {
                    $('.cmb2-id-employer-restrict-detail').css({'display': 'block'});
                    $('.cmb2-id-employer-restrict-listing').css({'display': 'block'});
                    $('.cmb2-id-employer-restrict-contact-info').css({'display': 'none'});

                    $('.cmb2-id-restrict-contact-employer-name').css({'display': 'none'});
                    $('.cmb2-id-restrict-contact-employer-email').css({'display': 'none'});
                    $('.cmb2-id-restrict-contact-employer-phone').css({'display': 'none'});
                    $('.cmb2-id-restrict-contact-employer-social').css({'display': 'none'});
                } else if ( restrict_type == 'view_contact_info' ) {
                    $('.cmb2-id-employer-restrict-detail').css({'display': 'none'});
                    $('.cmb2-id-employer-restrict-listing').css({'display': 'none'});
                    $('.cmb2-id-employer-restrict-contact-info').css({'display': 'block'});

                    $('.cmb2-id-restrict-contact-employer-name').css({'display': 'block'});
                    $('.cmb2-id-restrict-contact-employer-email').css({'display': 'block'});
                    $('.cmb2-id-restrict-contact-employer-phone').css({'display': 'block'});
                    $('.cmb2-id-restrict-contact-employer-social').css({'display': 'block'});
                } else {
                    $('.cmb2-id-employer-restrict-detail').css({'display': 'none'});
                    $('.cmb2-id-employer-restrict-listing').css({'display': 'none'});
                    $('.cmb2-id-employer-restrict-contact-info').css({'display': 'none'});

                    $('.cmb2-id-restrict-contact-employer-name').css({'display': 'none'});
                    $('.cmb2-id-restrict-contact-employer-email').css({'display': 'none'});
                    $('.cmb2-id-restrict-contact-employer-phone').css({'display': 'none'});
                    $('.cmb2-id-restrict-contact-employer-social').css({'display': 'none'});
                }
            });

            //
            var apply_type = $('#_job_apply_type').val();
            if ( apply_type == 'internal' ) {
                $('.cmb2-id--job-apply-url').hide();
                $('.cmb2-id--job-apply-email').hide();
                $('.cmb2-id--job-phone').hide();
            } else if ( apply_type == 'external' ) {
                $('.cmb2-id--job-apply-url').show();
                $('.cmb2-id--job-apply-email').hide();
                $('.cmb2-id--job-phone').hide();
            } else if ( apply_type == 'with_email' ) {
                $('.cmb2-id--job-apply-url').hide();
                $('.cmb2-id--job-phone').hide();
                $('.cmb2-id--job-apply-email').show();
            } else if ( apply_type == 'call' ) {
                $('.cmb2-id--job-apply-url').hide();
                $('.cmb2-id--job-phone').show();
                $('.cmb2-id--job-apply-email').hide();
            }
            $('#_job_apply_type').change(function(){
                var apply_type = $('#_job_apply_type').val();
                if ( apply_type == 'internal' ) {
                    $('.cmb2-id--job-apply-url').hide();
                    $('.cmb2-id--job-apply-email').hide();
                    $('.cmb2-id--job-phone').hide();
                } else if ( apply_type == 'external' ) {
                    $('.cmb2-id--job-apply-url').show();
                    $('.cmb2-id--job-apply-email').hide();
                    $('.cmb2-id--job-phone').hide();
                } else if ( apply_type == 'with_email' ) {
                    $('.cmb2-id--job-apply-url').hide();
                    $('.cmb2-id--job-apply-email').show();
                    $('.cmb2-id--job-phone').hide();
                } else if ( apply_type == 'call' ) {
                    $('.cmb2-id--job-apply-url').hide();
                    $('.cmb2-id--job-phone').show();
                    $('.cmb2-id--job-apply-email').hide();
                }
            });


            // restrict job
            var restrict_type = $('#job_restrict_type').val();
            if ( restrict_type == 'view' ) {
                $('.cmb2-id-job-restrict-detail').css({'display': 'block'});
                $('.cmb2-id-job-restrict-listing').css({'display': 'block'});
            } else {
                $('.cmb2-id-job-restrict-detail').css({'display': 'none'});
                $('.cmb2-id-job-restrict-listing').css({'display': 'none'});
            }
            $('#job_restrict_type').on('change', function() {
                var restrict_type = $(this).val();
                if ( restrict_type == 'view' ) {
                    $('.cmb2-id-job-restrict-detail').css({'display': 'block'});
                    $('.cmb2-id-job-restrict-listing').css({'display': 'block'});
                } else {
                    $('.cmb2-id-job-restrict-detail').css({'display': 'none'});
                    $('.cmb2-id-job-restrict-listing').css({'display': 'none'});
                }
            });

            // currency
            var enable_mutil_currencies = $('#enable_multi_currencies').val();
            if ( enable_mutil_currencies == 'yes' ) {
                $('.cmb2-id-multi-currencies').show();
                $('.cmb2-id-exchangerate-api-key').show();
            } else {
                $('.cmb2-id-multi-currencies').hide();
                $('.cmb2-id-exchangerate-api-key').hide();
            }

            $('#enable_multi_currencies').on('change', function() {
                var enable_mutil_currencies = $(this).val();
                if ( enable_mutil_currencies == 'yes' ) {
                    $('.cmb2-id-multi-currencies').show();
                    $('.cmb2-id-exchangerate-api-key').show();
                } else {
                    $('.cmb2-id-multi-currencies').hide();
                    $('.cmb2-id-exchangerate-api-key').hide();
                }
            });
        }
    }

    $.wjbpAdminMainCore = WJBPAdminMainCore.prototype;
    
    $(document).ready(function() {
        // Initialize script
        new WJBPAdminMainCore();
    });
    
})(jQuery);

