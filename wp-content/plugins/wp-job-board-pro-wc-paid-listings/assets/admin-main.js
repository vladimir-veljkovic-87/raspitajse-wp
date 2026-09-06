(function ($) {
    "use strict";

    if (!$.wjbpWcAdminExtensions)
        $.wjbpWcAdminExtensions = {};
    
    function WJBPWCAdminMainCore() {
        var self = this;
        self.init();
    };

    WJBPWCAdminMainCore.prototype = {
        /**
         *  Initialize
         */
        init: function() {
            var self = this;

            self.mixes();
        },
        mixes: function() {
            var self = this;

            var val_package_type = $('#_job_package_package_type').val();
            self.changePackageTypeFn(val_package_type);
            $('#_job_package_package_type').on('change', function() {
                var val_package_type = $(this).val();
                self.changePackageTypeFn(val_package_type);
            });

            self.productPackageTypeFn();


            var val_detail = $('input[name=candidate_restrict_contact_info]:checked').val();
            var restrict_type = $('#candidate_restrict_type').val();
            self.changeRestrictCandidateFn(val_detail, restrict_type);
            $('input[name=candidate_restrict_contact_info]').on('change', function() {
                var val_detail = $('input[name=candidate_restrict_contact_info]:checked').val();
                var restrict_type = $('#candidate_restrict_type').val();
                self.changeRestrictCandidateFn(val_detail, restrict_type);
            });
            
            $('#candidate_restrict_type').on('change', function() {
                var restrict_type = $(this).val();
                var val_detail = $('input[name=candidate_restrict_contact_info]:checked').val();
                self.changeRestrictCandidateFn(val_detail, restrict_type);
            });
        },
        changePackageTypeFn: function(val_package_type) {
            if ( val_package_type == 'job_package' ) {
                $('#_job_package_job_package').css({'display': 'block'});
                //
                $('#_job_package_cv_package').css({'display': 'none'});
                $('#_job_package_contact_package').css({'display': 'none'});
                $('#_job_package_candidate_package').css({'display': 'none'});
                $('#_job_package_resume_package').css({'display': 'none'});
            } else if ( val_package_type == 'cv_package' ) {
                $('#_job_package_cv_package').css({'display': 'block'});
                //
                $('#_job_package_job_package').css({'display': 'none'});
                $('#_job_package_contact_package').css({'display': 'none'});
                $('#_job_package_candidate_package').css({'display': 'none'});
                $('#_job_package_resume_package').css({'display': 'none'});
            } else if ( val_package_type == 'contact_package' ) {
                $('#_job_package_contact_package').css({'display': 'block'});
                //
                $('#_job_package_job_package').css({'display': 'none'});
                $('#_job_package_cv_package').css({'display': 'none'});
                $('#_job_package_candidate_package').css({'display': 'none'});
                $('#_job_package_resume_package').css({'display': 'none'});
            } else if ( val_package_type == 'candidate_package' ) {
                $('#_job_package_candidate_package').css({'display': 'block'});
                //
                $('#_job_package_job_package').css({'display': 'none'});
                $('#_job_package_cv_package').css({'display': 'none'});
                $('#_job_package_contact_package').css({'display': 'none'});
                $('#_job_package_resume_package').css({'display': 'none'});
            } else if ( val_package_type == 'resume_package' ) {
                $('#_job_package_resume_package').css({'display': 'block'});
                //
                $('#_job_package_job_package').css({'display': 'none'});
                $('#_job_package_cv_package').css({'display': 'none'});
                $('#_job_package_contact_package').css({'display': 'none'});
                $('#_job_package_candidate_package').css({'display': 'none'});
            } else {
                $('#_job_package_resume_package').css({'display': 'none'});
                $('#_job_package_job_package').css({'display': 'none'});
                $('#_job_package_cv_package').css({'display': 'none'});
                $('#_job_package_contact_package').css({'display': 'none'});
                $('#_job_package_candidate_package').css({'display': 'none'});
            }
        },
        productPackageTypeFn: function() {
            // $('.pricing').addClass( 'show_if_job_package show_if_cv_package show_if_contact_package show_if_candidate_package show_if_resume_package' );
            $('._tax_status_field').closest('div').addClass( 'show_if_job_package show_if_job_package_subscription show_if_cv_package show_if_cv_package_subscription show_if_contact_package show_if_contact_package_subscription' );
            $('._tax_status_field').closest('div').addClass( 'show_if_candidate_package show_if_candidate_package_subscription show_if_resume_package show_if_resume_package_subscription' );
            $('.show_if_subscription, .grouping').addClass( 'show_if_job_package_subscription show_if_cv_package_subscription show_if_contact_package_subscription show_if_candidate_package_subscription show_if_resume_package_subscription' );
            $('#product-type').change();

            $('#_job_package_subscription_type').change(function(){
                if ( $(this).val() === 'listing' ) {
                    $('#_jobs_duration').closest('.form-field').hide().val('');
                } else {
                    $('#_jobs_duration').closest('.form-field').show();
                }
            }).change();

            $('#_resume_package_subscription_type').change(function(){
                if ( $(this).val() === 'listing' ) {
                    $('#_resumes_duration').closest('.form-field').hide().val('');
                } else {
                    $('#_resumes_duration').closest('.form-field').show();
                }
            }).change();

            $('#_cv_package_subscription_type').change(function(){
                if ( $(this).val() === 'listing' ) {
                    $('#_cv_package_expiry_time').closest('.form-field').hide().val('');
                } else {
                    $('#_cv_package_expiry_time').closest('.form-field').show();
                }
            }).change();

            $('#_contact_package_subscription_type').change(function(){
                if ( $(this).val() === 'listing' ) {
                    $('#_contact_package_expiry_time').closest('.form-field').hide().val('');
                } else {
                    $('#_contact_package_expiry_time').closest('.form-field').show();
                }
            }).change();

            $('#_candidate_package_subscription_type').change(function(){
                if ( $(this).val() === 'listing' ) {
                    $('#_candidate_package_expiry_time').closest('.form-field').hide().val('');
                } else {
                    $('#_candidate_package_expiry_time').closest('.form-field').show();
                }
            }).change();
        },
        changeRestrictCandidateFn: function(val_detail, restrict_type) {
            if ( restrict_type == 'view_contact_info' && val_detail == 'register_employer_contact_with_package' ) {
                $('.cmb2-id-contact-package-page-id').css({'display': 'block'});
            } else {
                $('.cmb2-id-contact-package-page-id').css({'display': 'none'});
            }
        }
    }

    $.wjbpWcAdminMainCore = WJBPWCAdminMainCore.prototype;
    
    $(document).ready(function() {
        // Initialize script
        new WJBPWCAdminMainCore();
    });
    
})(jQuery);

