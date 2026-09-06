(function ($) {
    "use strict";

    function WJBPAjaxFileUpload() {
        var self = this;
        self.init();
    };

    WJBPAjaxFileUpload.prototype = {
        init: function() {
            var self = this;

            $('.wp-job-board-pro-file-upload').each(function(){
                self.upload_file($(this));
            });
            $(document).on('click', '.cmb-add-group-row', function(e) {
                setTimeout(function(){
                    $('.wp-job-board-pro-file-upload').each(function(){
                        self.upload_file($(this));
                    });
                }, 50);
                
            });
            $(document).on('click', '.wp-job-board-pro-uploaded-files .wp-job-board-pro-remove-uploaded-file', function(e) {
                e.preventDefault();
                var $file_field = $(this).closest('.cmb-type-wp-job-board-pro-file').find('input.wp-job-board-pro-file-upload');
                if ( typeof $file_field.data( 'file_limit_left' ) !== 'undefined' ) {
                    var fileLimitLeft = parseInt( $file_field.data( 'file_limit_left' ), 10 );
                    $file_field.data( 'file_limit_left', fileLimitLeft + 1 );
                }

                $(this).closest('.wp-job-board-pro-uploaded-file').remove();
            });
        },
        upload_file: function($element) {
            $element.fileupload({
                dataType: 'json',
                dropZone: $(this),
                url: wp_job_board_pro_file_upload.ajax_url_endpoint.toString().replace( '%%endpoint%%', 'wp_job_board_pro_ajax_upload_file' ),
                maxNumberOfFiles: 1,
                formData: {
                    script: true,
                    action: 'wp_job_board_pro_ajax_upload_file'
                },
                add: function (e, data) {
                    var $file_field     = $( this );
                    var $form           = $file_field.closest( 'form' );
                    var $uploaded_files = $file_field.parent().find('.wp-job-board-pro-uploaded-files');
                    var uploadErrors    = [];
                    var fileLimitLeft    = false;
                    var fileLimit        = parseInt( $file_field.data( 'file_limit' ), 10 );

                    if ( typeof $file_field.data( 'file_limit_left' ) !== 'undefined' ) {
                        fileLimitLeft = parseInt( $file_field.data( 'file_limit_left' ), 10 );
                    } else if ( typeof fileLimit !== 'undefined' ) {
                        var currentFiles = parseInt( $uploaded_files.children( '.wp-job-board-pro-uploaded-file' ).length, 10);
                        fileLimitLeft = fileLimit - currentFiles;
                        $file_field.data( 'file_limit_left', fileLimitLeft );
                    }

                    if ( false !== fileLimitLeft && fileLimitLeft <= 0 ) {
                        var message = 'Exceeded upload limit';
                        if( $file_field.data( 'file_limit_message' ) ) {
                            message = $file_field.data( 'file_limit_message' );
                        } else if ( typeof wp_realestate_file_upload !== 'undefined' ) {
                            message = wp_realestate_file_upload.i18n_over_upload_limit;
                        }
                        message = message.replace( '%d', fileLimit );

                        uploadErrors.push( message );
                    }

                    // Validate type
                    var allowed_types = $(this).data('file_types');

                    if ( allowed_types ) {
                        var acceptFileTypes = new RegExp( '(\.|\/)(' + allowed_types + ')$', 'i' );

                        if ( data.originalFiles[0].name.length && ! acceptFileTypes.test( data.originalFiles[0].name ) ) {
                            uploadErrors.push( wp_job_board_pro_file_upload.i18n_invalid_file_type + ' ' + allowed_types );
                        }
                    }

                    var allowed_size = $(this).data('file_size');
                    if ( allowed_size ) {
                        if ( data.originalFiles[0].size > (allowed_size * 1000000) ) {
                            var text_limit = wp_job_board_pro_file_upload.i18n_invalid_file_size;
                            text_limit = text_limit.replace(/%s/g, allowed_size);
                            console.log(allowed_size);
                            uploadErrors.push( text_limit );
                        }
                    }

                    if ( uploadErrors.length > 0 ) {
                        window.alert( uploadErrors.join( '\n' ) );
                    } else {
                        if ( false !== fileLimitLeft ) {
                            $file_field.data( 'file_limit_left', fileLimitLeft - 1 );
                        }
                        $form.find(':input[type="submit"]').attr( 'disabled', 'disabled' );
                        data.context = $('<progress value="" max="100"></progress>').appendTo( $uploaded_files );
                        data.submit();
                    }
                },
                progress: function (e, data) {
                    var progress        = parseInt(data.loaded / data.total * 100, 10);
                    data.context.val( progress );
                },
                fail: function (e, data) {
                    var $file_field     = $( this );
                    var $form           = $file_field.closest( 'form' );

                    if ( data.errorThrown ) {
                        window.alert( data.errorThrown );
                    }

                    data.context.remove();

                    $form.find(':input[type="submit"]').removeAttr( 'disabled' );
                },
                done: function (e, data) {
                    var $file_field     = $( this );
                    var $form           = $file_field.closest( 'form' );
                    var $uploaded_files = $file_field.parent().find('.wp-job-board-pro-uploaded-files');
                    var multiple        = $file_field.attr( 'multiple' ) ? 1 : 0;
                    var image_types     = [ 'jpg', 'gif', 'png', 'jpeg', 'jpe' ];

                    $file_field.val("");

                    data.context.remove();

                    $.each(data.result.files, function(index, file) {
                        if ( file.error ) {
                            window.alert( file.error );
                        } else {
                            var html;
                            if ( $.inArray( file.extension, image_types ) >= 0 ) {
                                html = $.parseHTML( wp_job_board_pro_file_upload.js_field_html_img );
                                $( html ).find('.wp-job-board-pro-uploaded-file-preview img').attr( 'src', file.url );
                                $( html ).find('.wp-job-board-pro-uploaded-file-name').remove();
                            } else {
                                html = $.parseHTML( wp_job_board_pro_file_upload.js_field_html );

                                var new_html = '<span class="icon_type"><i class="flaticon-file"></i></span>';
                                new_html += '<div class="filename">' + file.name + '</div>';
                                new_html += '<div class="extension">' + file.extension + '</div>';

                                $( html ).find('.wp-job-board-pro-uploaded-file-name .candidate-detail-cv').html( new_html );

                                $( html ).find('.wp-job-board-pro-uploaded-file-preview').remove();
                            }

                            $( html ).find('.input-text').val( file.url );
                            $( html ).find('.input-text').attr( 'name', 'current_' + $file_field.attr( 'name' ) );

                            if ( multiple ) {
                                $uploaded_files.append( html );
                            } else {
                                $uploaded_files.html( html );
                            }
                        }
                    });

                    $form.find(':input[type="submit"]').removeAttr( 'disabled' );
                }
            });
        }
    }

    $.wjbpAjaxFileUpload = WJBPAjaxFileUpload.prototype;
    
    $(document).ready(function() {
        // Initialize script
        new WJBPAjaxFileUpload();

    });

})(jQuery);

