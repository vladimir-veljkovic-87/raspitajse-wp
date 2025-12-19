(function ($) {
	'use strict';

	$('.wpjb_taxonomy_location').each(function () {
		var allowclear = $(this).data('allowclear');
		var width = $(this).data('width') ? $(this).data('width') : '100%';
		$(this).select2({
			allowClear: allowclear,
			width: width
		});
	});

	// Location Change
    $('body').on('change', 'select.wpjb_taxonomy_location', function(){
        var val = $(this).val();
        var next = $(this).data('next');
        var main_select = 'select.wpjb_taxonomy_location' + next;
        if ( $(main_select).length > 0 ) {
            
            $(main_select).prop('disabled', true);
            $(main_select).val('').trigger('change');

            if ( val ) {
            	$(main_select).parent().addClass('loading');
                $.ajax({
                    url: location_opts.ajaxurl,
                    type:'POST',
                    dataType: 'json',
                    data:{
                        'action': 'wpjb_process_change_location',
                        'parent': val,
                        'taxonomy': $(main_select).data('taxonomy'),
                        'security': location_opts.ajax_nonce,
                    }
                }).done(function(data) {
                    $(main_select).parent().removeClass('loading');
                    
                    $(main_select).find('option').remove();
                    if ( data ) {
                    	$.each(data, function(i, item) {
                    		var option = new Option(item.name, item.id, true, true);
    						$(main_select).append(option);
						});
                    }
                    $(main_select).prop("disabled", false);
                    $(main_select).val(null).select2("destroy").select2();
                });
            } else {
                $(main_select).find('option').remove();
                $(main_select).prop("disabled", false);
                $(main_select).val(null).select2("destroy").select2();
            }
        }
    });


})(jQuery);