(function ($) {
    "use strict";
    $(document).ready(function() {
        $('.profile-url-edit-wrapper').hide();
        
        $('.edit-profile-slug').on('click', function() {
        	$(this).hide();
        	$('.profile-url-edit-wrapper').show();
        });

        $('.save-profile-slug').on( 'click', function(e) {
        	var $this = $(this),
        		$con = $this.closest('.profile-url-wrapper');
    		if ( $this.hasClass('loading') ) {
    			return false;
    		}
        	$con.find('.alert').remove();
            $this.addClass('loading');
        	$.ajax({
                url: wp_job_board_pro_profile_url_opts.ajaxurl,
                type:'POST',
                dataType: 'json',
                data: {
                	action: 'wp_job_board_pro_ajax_change_slug',
                	profile_url_slug: $con.find('input[name=profile_url_slug]').val(),
                	nonce: $this.data('nonce'),
                }
            }).done(function(data) {
                $this.removeClass('loading');
                if ( data.status ) {
                    $con.find('.post-slug').text(data.url);
        			$('.profile-url-edit-wrapper').hide();
        			$('.edit-profile-slug').show();
                } else {
                    $con.prepend( '<div class="alert alert-warning">' + data.msg + '</div>' );
                }
            });
            return false;
        });
    });

})(jQuery);
