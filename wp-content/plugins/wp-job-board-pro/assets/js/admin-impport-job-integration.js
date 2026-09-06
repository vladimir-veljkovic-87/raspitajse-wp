(function ($) {
    "use strict";

    function WJBPAdminImportJobIntegrationCore() {
        var self = this;
        self.init();
    };

    WJBPAdminImportJobIntegrationCore.prototype = {
        /**
         *  Initialize
         */
        init: function() {
            $('input[name=submit-cmb-indeed-job-import]').on('click', function() {
                var form = $(this).closest('form');
                var $this = $(this);
                if (  form.hasClass('loading') ) {
                    return false;
                }
                form.find('.alert').remove();
                form.addClass('loading');
                $.ajax({
                    url: wp_job_board_pro_iji_opts.ajaxurl,
                    type:'POST',
                    dataType: 'json',
                    data:  form.serialize()+"&action=wp_job_board_pro_ajax_indeed_job_import"
                }).done(function(data) {
                    form.removeClass('loading');
                    if ( data.status ) {
                        form.prepend( '<div class="alert alert-info">' + data.msg + '</div>' );
                    } else {
                        form.prepend( '<div class="alert alert-warning">' + data.msg + '</div>' );
                    }
                });
            });

            $('input[name=submit-cmb-ziprecruiter-job-import]').on('click', function() {
                var form = $(this).closest('form');
                var $this = $(this);
                if (  form.hasClass('loading') ) {
                    return false;
                }
                form.find('.alert').remove();
                form.addClass('loading');
                $.ajax({
                    url: wp_job_board_pro_iji_opts.ajaxurl,
                    type:'POST',
                    dataType: 'json',
                    data:  form.serialize()+"&action=wp_job_board_pro_ajax_ziprecruiter_job_import"
                }).done(function(data) {
                    form.removeClass('loading');
                    if ( data.status ) {
                        form.prepend( '<div class="alert alert-info">' + data.msg + '</div>' );
                    } else {
                        form.prepend( '<div class="alert alert-warning">' + data.msg + '</div>' );
                    }
                });
            });

            $('input[name=submit-cmb-careerjet-job-import]').on('click', function() {
                var form = $(this).closest('form');
                var $this = $(this);
                if (  form.hasClass('loading') ) {
                    return false;
                }
                form.find('.alert').remove();
                form.addClass('loading');
                $.ajax({
                    url: wp_job_board_pro_iji_opts.ajaxurl,
                    type:'POST',
                    dataType: 'json',
                    data:  form.serialize()+"&action=wp_job_board_pro_ajax_careerjet_job_import"
                }).done(function(data) {
                    form.removeClass('loading');
                    if ( data.status ) {
                        form.prepend( '<div class="alert alert-info">' + data.msg + '</div>' );
                    } else {
                        form.prepend( '<div class="alert alert-warning">' + data.msg + '</div>' );
                    }
                });
            });

            $('input[name=submit-cmb-careerbuilder-job-import]').on('click', function() {
                var form = $(this).closest('form');
                var $this = $(this);
                if (  form.hasClass('loading') ) {
                    return false;
                }
                form.find('.alert').remove();
                form.addClass('loading');
                $.ajax({
                    url: wp_job_board_pro_iji_opts.ajaxurl,
                    type:'POST',
                    dataType: 'json',
                    data:  form.serialize()+"&action=wp_job_board_pro_ajax_careerbuilder_job_import"
                }).done(function(data) {
                    form.removeClass('loading');
                    if ( data.status ) {
                        form.prepend( '<div class="alert alert-info">' + data.msg + '</div>' );
                    } else {
                        form.prepend( '<div class="alert alert-warning">' + data.msg + '</div>' );
                    }
                });
            });


            var val = $('#careerbuilder_job_import_posted_by_type').val();
            if ( val == 'auto' ) {
                $('.cmb2-id-careerbuilder-job-import-posted-by').hide();
            } else {
                $('.cmb2-id-careerbuilder-job-import-posted-by').show();
            }

            $('#careerbuilder_job_import_posted_by_type').on('change', function() {
                var val = $(this).val();
                if ( val == 'auto' ) {
                    $('.cmb2-id-careerbuilder-job-import-posted-by').hide();
                } else {
                    $('.cmb2-id-careerbuilder-job-import-posted-by').show();
                }
            });


            var val = $('#careerjet_job_import_posted_by_type').val();
            if ( val == 'auto' ) {
                $('.cmb2-id-careerjet-job-import-posted-by').hide();
            } else {
                $('.cmb2-id-careerjet-job-import-posted-by').show();
            }

            $('#careerjet_job_import_posted_by_type').on('change', function() {
                var val = $(this).val();
                if ( val == 'auto' ) {
                    $('.cmb2-id-careerjet-job-import-posted-by').hide();
                } else {
                    $('.cmb2-id-careerjet-job-import-posted-by').show();
                }
            });


            var val = $('#indeed_job_import_posted_by_type').val();
            if ( val == 'auto' ) {
                $('.cmb2-id-indeed-job-import-posted-by').hide();
            } else {
                $('.cmb2-id-indeed-job-import-posted-by').show();
            }

            $('#indeed_job_import_posted_by_type').on('change', function() {
                var val = $(this).val();
                if ( val == 'auto' ) {
                    $('.cmb2-id-indeed-job-import-posted-by').hide();
                } else {
                    $('.cmb2-id-indeed-job-import-posted-by').show();
                }
            });


            var val = $('#ziprecruiter_job_import_posted_by_type').val();
            if ( val == 'auto' ) {
                $('.cmb2-id-ziprecruiter-job-import-posted-by').hide();
            } else {
                $('.cmb2-id-ziprecruiter-job-import-posted-by').show();
            }

            $('#ziprecruiter_job_import_posted_by_type').on('change', function() {
                var val = $(this).val();
                if ( val == 'auto' ) {
                    $('.cmb2-id-ziprecruiter-job-import-posted-by').hide();
                } else {
                    $('.cmb2-id-ziprecruiter-job-import-posted-by').show();
                }
            });
        }
        
    }

    $.wjbpAdminImportJobIntegrationCore = WJBPAdminImportJobIntegrationCore.prototype;
    
    $(document).ready(function() {
        // Initialize script
        new WJBPAdminImportJobIntegrationCore();
    });
    
})(jQuery);

