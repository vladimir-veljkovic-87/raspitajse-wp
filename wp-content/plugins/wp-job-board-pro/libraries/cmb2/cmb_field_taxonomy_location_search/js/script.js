(function ($) {
	'use strict';

	var __cache = [];
	$('select.wpjb_taxonomy_location_search').each(function () {
		taxonomy_select2_init($(this));
	});

	// Location Change
	$('body').on('change', 'select.wpjb_taxonomy_location_search', function(){
        var val = $(this).val();
        var next = $(this).data('next');
        var main_select = 'select.wpjb_taxonomy_location_search' + next;
        if ( $(main_select).length > 0 ) {
            $(main_select).val(null).trigger("change");
        }
    });

	function taxonomy_select2_init($element) {
		var allowclear = $element.data('allowclear');
		var width = $element.data('width') ? $element.data('width') : '100%';

		$element.select2({
            allowClear: allowclear,
            width: width,
            dir: wp_job_board_pro_select2_opts['dir'],
            language: {
                noResults: function (params) {
                    return wp_job_board_pro_select2_opts['language_result'];
                },
                inputTooShort: function () {
                    return wp_job_board_pro_select2_opts['formatInputTooShort_text'];
                }
            },
            minimumInputLength: 2,
            ajax: {
                url: location_opts.ajaxurl_endpoint.toString().replace( '%%endpoint%%', 'wpjb_search_terms' ),
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    
                    var parent_id = 0;
                    var prev = $element.data('prev');
                    var prev_select = $('.wpjb_taxonomy_location_search' + prev);
                    if ( prev_select.length ) {
                        parent_id = prev_select.val();
                        if ( !parent_id ) {
                            parent_id = 'lost-parent';
                        }

                    }
                    var query = {
                        search: params.term,
                        page: params.page || 1,
                        taxonomy: $element.data('taxonomy'),
                        parent: parent_id,
                        prev: prev
                    }

                    // Query parameters will be ?search=[term]&type=public
                    return query;
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    return {
                        results: $.map(data.results, function (item) {
                            return {
                                text: item.name,
                                id: item.id
                            }
                        }),
                        pagination: {
                            more: params.page < data.pages
                        }
                    };
                },
                transport: function(params, success, failure) {
                    //retrieve the cached key or default to _ALL_
                    var __cachekey = params.data.search + '-' + params.data.taxonomy + '-' + params.data.page + '-' + params.data.parent + params.data.prev;

                    if ('undefined' !== typeof __cache[__cachekey]) {
                        //display the cached results
                        success(__cache[__cachekey]);
                        return; /* noop */
                    }
                    var $request = $.ajax(params);
                    $request.then(function(data) {
                        //store data in cache
                        __cache[__cachekey] = data;
                        //display the results
                        success(__cache[__cachekey]);
                    });
                    $request.fail(failure);
                    return $request;
                },
                cache: true
            }
            
        });
	}
	
})(jQuery);