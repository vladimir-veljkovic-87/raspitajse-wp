(function ($) {
    "use strict";
    var map, CustomHtmlIcon, geocoder, marker;
	var listingLocation = {
		init: function() {
			var self = this;
			setTimeout(function(){
				$( '.cmb-type-pw-map' ).each( function() {
					self.initializeMap( $( this ) );
				});
			}, 100);

			$("body").on("click", '.ui-tabs-nav li a.ui-tabs-anchor, .apus-user-register', function(){
				setTimeout(function(){
					map._onResize();
				}, 100);
			});

			$('.apus-user-register').on('click', function(){
                setTimeout(function(){
                    map._onResize();
                }, 1000);
            });
		},
		makeid: function(length) {
		    var result           = '';
		    var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		    var charactersLength = characters.length;
		    for ( var i = 0; i < length; i++ ) {
		      	result += characters.charAt(Math.floor(Math.random() * charactersLength));
		   	}
		   	return result;
		},
		initializeMap: function(mapInstance) {
			var self = this;
			var maps = [];

			var randid = self.makeid(5);
			var mapCanvas = mapInstance.find( '.pw-map' ).attr('id') + '-' + randid;
			
			mapInstance.find( '.pw-map' ).attr('id', mapCanvas);

			// var mapCanvas = mapInstance.find( '.pw-map' ).attr('id');
			var location_address = mapInstance.find( '.pw-map-search' );
			var latitude = mapInstance.find( '.pw-map-latitude' );
			var longitude = mapInstance.find( '.pw-map-longitude' );
			var properties = mapInstance.find( '.pw-map-properties' );

			var latLng = [wp_job_board_pro_maps_opts.default_latitude, wp_job_board_pro_maps_opts.default_longitude];
			var zoom = 8;

			// If we have saved values, let's set the position and zoom level
			if ( latitude.val() && longitude.val() ) {
				latLng = [latitude.val(), longitude.val()];
				zoom = 13;
			}

			var $window = $(window);

			if ( wp_job_board_pro_maps_opts.geocoder_country ) {
				geocoder = new L.Control.Geocoder.Nominatim({
					geocodingQueryParams: {countrycodes: wp_job_board_pro_maps_opts.geocoder_country}
				});
			} else {
				geocoder = new L.Control.Geocoder.Nominatim();
			}
			map = L.map(mapCanvas, {
                scrollWheelZoom: false,
                center: latLng,
	            zoom: zoom,
	            zoomControl: true,
            });

            CustomHtmlIcon = L.HtmlIcon.extend({
                options: {
                    html: "<div class='map-popup'></div>",
                    iconSize: [42, 42],
                    iconAnchor: [22, 42],
                    popupAnchor: [0, -42]
                }
            });

            $window.on('pxg:refreshmap', function() {
            	map._onResize();
            });

            $window.on('pxg:simplerefreshmap', function() {
                map._onResize();
            });

            if ( wp_job_board_pro_maps_opts.map_service == 'mapbox' ) {
                var tileLayer = L.tileLayer('https://api.mapbox.com/styles/v1/mapbox/'+wp_job_board_pro_maps_opts.mapbox_style+'/tiles/{z}/{x}/{y}?access_token='+ wp_job_board_pro_maps_opts.mapbox_token, {
                    attribution: " &copy;  <a href='https://www.mapbox.com/about/maps/'>Mapbox</a> &copy;  <a href='http://www.openstreetmap.org/copyright'>OpenStreetMap</a> <strong><a href='https://www.mapbox.com/map-feedback/' target='_blank'>Improve this map</a></strong>",
                    maxZoom: 18,
                });
            } else if ( wp_job_board_pro_maps_opts.map_service == 'here' ) {
                
                var hereTileUrl = 'https://2.base.maps.ls.hereapi.com/maptile/2.1/maptile/newest/'+wp_job_board_pro_maps_opts.here_style+'/{z}/{x}/{y}/512/png8?apiKey='+ wp_job_board_pro_maps_opts.here_map_api_key +'&ppi=320';
                var tileLayer = L.tileLayer(hereTileUrl, {
                    attribution: " &copy;  <a href='https://www.mapbox.com/about/maps/'>Here</a> &copy; <strong><a href='https://www.mapbox.com/map-feedback/' target='_blank'>Improve this map</a></strong>",
                    maxZoom: 18,
                });

            } else {
                if ( wp_job_board_pro_maps_opts.custom_style != '' ) {
                    try {
                        var custom_style = $.parseJSON(wp_job_board_pro_maps_opts.custom_style);
                        var tileLayer = L.gridLayer.googleMutant({
                            type: 'roadmap',
                            styles: custom_style
                        });

                    } catch(err) {
                        var tileLayer = L.gridLayer.googleMutant({
                            type: 'roadmap'
                        });
                    }
                } else {
                    var tileLayer = L.gridLayer.googleMutant({
                        type: 'roadmap'
                    });
                }
                $('#' + mapCanvas).addClass('map--google');
            }

            map.addLayer(tileLayer);

            if(wp_job_board_pro_maps_opts.default_pin){
                var pin_icon = "<img src='" + wp_job_board_pro_maps_opts.default_pin + "'>";
                var mapPinHTML = "<div class='map-popup map-popup-image'><div class='icon-wrapper'>"+pin_icon+"</div></div>";
            }else{
                var mapPinHTML = "<div class='map-popup map-popup-empty'><div class='icon-wrapper'><div class='icon-cat'></div></div></div>";
            }


            marker = new L.marker(latLng, {
	            draggable: 'true',
	            icon: new CustomHtmlIcon({ html: mapPinHTML })
	        });

            marker.on('dragend', function(event) {
	            var position = marker.getLatLng();
	            marker.setLatLng(position, {
	              	draggable: 'true'
	            }).bindPopup(position).update();

	            geocoder.reverse(position, map.options.crs.scale(map.getZoom()), function(results) {
	              	location_address.val(results[0].name);

	              	properties.val( JSON.stringify(results[0].properties.address) );
		            if ( results[0].properties.address ) {
		            	var address_obj = results[0].properties.address;
		            	var address_html = '';
		            	Object.keys(address_obj).forEach(function(key) {
						  	address_html += '<input name="'+properties.data('name')+'['+key+']" value="'+address_obj[key]+'">';
						});
						properties.html(address_html);
		            }

	            });
	            latitude.val( position.lat );
				longitude.val( position.lng );



	        });

            if ( wp_job_board_pro_maps_opts.map_service === 'google-map' ) {
            	$('.pw-map-search').each(function(){
            		var $id = $(this).attr('id');

	            	var searchInput = document.getElementById( $id );
	            	// Search
					var autocomplete = new google.maps.places.Autocomplete( searchInput );
					if ( wp_job_board_pro_maps_opts.geocoder_country ) {
	                    autocomplete.setComponentRestrictions({
	                        country: [wp_job_board_pro_maps_opts.geocoder_country],
	                    });
	                }
					autocomplete.addListener( 'place_changed', function () {
	                    var place = autocomplete.getPlace();
						if ( ! place.geometry ) {
							return;
						}

						var newLatLng = new L.LatLng(place.geometry.location.lat(), place.geometry.location.lng());
			            marker.setLatLng(newLatLng).update(); 
			            map.panTo(newLatLng);
			            
	                    latitude.val( place.geometry.location.lat() );
						longitude.val( place.geometry.location.lng() );
	                    
	                    var address_obj = place.address_components;
		            	var address_html = '';

	            		jQuery.each(address_obj, function(key, item) {
	            			switch ( item.types[0] ) {
								case 'street_number':
									address_html += '<input name="'+properties.data('name')+'[street_number]" value="'+item.long_name+'">';
									break;
								case 'route':
									address_html += '<input name="'+properties.data('name')+'[street]" value="'+item.long_name+'">';
									break;
								case 'sublocality_level_1':
								case 'locality':
								case 'postal_town':
									address_html += '<input name="'+properties.data('name')+'[city]" value="'+item.long_name+'">';
									break;
								case 'administrative_area_level_1':
								case 'administrative_area_level_2':
									address_html += '<input name="'+properties.data('name')+'[state_short]" value="'+item.short_name+'">';
									address_html += '<input name="'+properties.data('name')+'[state]" value="'+item.long_name+'">';
									break;
								case 'postal_code':
									address_html += '<input name="'+properties.data('name')+'[postcode]" value="'+item.long_name+'">';
									break;
								case 'country':
									address_html += '<input name="'+properties.data('name')+'[country_short]" value="'+item.short_name+'">';
									address_html += '<input name="'+properties.data('name')+'[country]" value="'+item.country_long+'">';
									break;
							}
						});
						properties.html(address_html);

	                });
					
					$( '#' + $id ).keypress( function( event ) {
						if ( 13 === event.keyCode ) {
							event.preventDefault();
						}
					});
				});
            } else {
	            // search location
	            location_address.attr('autocomplete', 'off').after('<div class="leaflet-geocode-container"></div>');

	            function delay(fn, ms) {
	                let timer = 0
	                return function(...args) {
	                    clearTimeout(timer)
	                    timer = setTimeout(fn.bind(this, ...args), ms || 0)
	                }
	            }
	            $(document).on('keyup', '.pw-map-search', delay(function (e) {
	            	var s = $(this).val(), $this = $(this), container = $(this).closest('.pw-map-search-wrapper');
	              	if ( s && s.length >= 3 ) {
	              		search_location_fc($this, s, container);
		            } else {
		            	container.find('.leaflet-geocode-container').html('').removeClass('active');
		            }
		        }, 500));

	            var search_results = [];
	            var search_location_fc = function($this, s, container) {
	            	$this.parent().addClass('loading');
	                geocoder.geocode(s, function(results) {
	                	search_results = results;
	                	var output_html = '';
	                    for (var i = 0; i < results.length; i++) {
	                        output_html += '<li class="result-item" data-latitude="'+results[i].center.lat+'" data-longitude="'+results[i].center.lng+'" data-id="'+i+'" ><i class="fa fa-map-marker" aria-hidden="true"></i> '+results[i].name+'</li>';
	                    }
	                    if ( output_html ) {
	                        output_html = '<ul>'+ output_html +'</ul>';
	                    }

	                    container.find('.leaflet-geocode-container').html(output_html).addClass('active');

	                    var highlight_texts = s.split(' ');

	                    highlight_texts.forEach(function (item) {
	                        container.find('.leaflet-geocode-container').highlight(item);
	                    });

	                  	$this.parent().removeClass('loading');

	                  	
	                });
	            }

	            //
              	$(document).on( "click", ".leaflet-geocode-container ul li", function(e) {
		        	var container = $(this).closest('.pw-map-search-wrapper');
		            var newLatLng = new L.LatLng($(this).data('latitude'), $(this).data('longitude'));
		            location_address.val($(this).text());
		            marker.setLatLng(newLatLng).update(); 
		            map.panTo(newLatLng);

		            latitude.val($(this).data('latitude'));
		            longitude.val($(this).data('longitude'));
		            properties.val( JSON.stringify(search_results[$(this).data('id')].properties.address) );
		            if ( search_results[$(this).data('id')].properties.address ) {
		            	var address_obj = search_results[$(this).data('id')].properties.address;
		            	var address_html = '';
		            	Object.keys(address_obj).forEach(function(key) {
						  	address_html += '<input name="'+properties.data('name')+'['+key+']" value="'+address_obj[key]+'">';
						});
						properties.html(address_html);
		            }
		            container.find('.leaflet-geocode-container').html('').removeClass('active');
		        });
            }

	        // change latitude
	        latitude.change(function() {
	            var position = [latitude.val(), longitude.val()];
	            marker.setLatLng(position, {
	              draggable: 'true'
	            }).bindPopup(position).update();
	            map.panTo(position);

	            var geocodeService = L.esri.Geocoding.geocodeService();
		        geocodeService.reverse().latlng(position).run(function(error, result) {
			      	$('.pw-map-search').val(result.address.Match_addr);
			    });
	            
	            var position = marker.getLatLng();
	            geocoder.reverse(position, map.options.crs.scale(map.getZoom()), function(results) {
	              	properties.val( JSON.stringify(results[0].properties.address) );
		            if ( results[0].properties.address ) {
		            	var address_obj = results[0].properties.address;
		            	var address_html = '';
		            	Object.keys(address_obj).forEach(function(key) {
						  	address_html += '<input name="'+properties.data('name')+'['+key+']" value="'+address_obj[key]+'">';
						});
						properties.html(address_html);
		            }

	            });
	        });

	        // change longitude
			longitude.change(function() {
	            var position = [latitude.val(), longitude.val()];
	            marker.setLatLng(position, {
	              draggable: 'true'
	            }).bindPopup(position).update();
	            map.panTo(position);

	            var geocodeService = L.esri.Geocoding.geocodeService();
		        geocodeService.reverse().latlng(position).run(function(error, result) {
			      	$('.pw-map-search').val(result.address.Match_addr);
			    });

		        var position = marker.getLatLng();
		        geocoder.reverse(position, map.options.crs.scale(map.getZoom()), function(results) {
	              		
	              	properties.val( JSON.stringify(results[0].properties.address) );
		            if ( results[0].properties.address ) {
		            	var address_obj = results[0].properties.address;
		            	var address_html = '';
		            	Object.keys(address_obj).forEach(function(key) {
						  	address_html += '<input name="'+properties.data('name')+'['+key+']" value="'+address_obj[key]+'">';
						});
						properties.html(address_html);
		            }

	            });
	            
	        });

			// find me
			$(document).on('click', '.find-me-location', function() {
		        $(this).addClass('loading');
		        navigator.geolocation.getCurrentPosition(self.getLocation, self.getErrorLocation);
		    });

	        map.addLayer(marker).setView(marker.getLatLng(), zoom);
	        map._onResize();
		},
		getLocation: function(position) {
			
			$('.pw-map-latitude').val(position.coords.latitude);
	        $('.pw-map-longitude').val(position.coords.longitude);
	        $('.pw-map-search').val('Location');
	        $('.pw-map-search').find('.leaflet-geocode-container').html('').removeClass('active');
	        
	        var position = [position.coords.latitude, position.coords.longitude];

	        marker.setLatLng(position, {
              	draggable: 'true'
            }).bindPopup(position).update();
	        map.panTo(position);

	        var geocodeService = L.esri.Geocoding.geocodeService();
	        geocodeService.reverse().latlng(position).run(function(error, result) {
		      	$('.pw-map-search').val(result.address.Match_addr);
		    });

	        var properties = $('.pw-map-properties');
	        if ( properties.length ) {
		        var position = marker.getLatLng();
		        geocoder.reverse(position, map.options.crs.scale(map.getZoom()), function(results) {
	              	properties.val( JSON.stringify(results[0].properties.address) );
		            if ( results[0].properties.address ) {
		            	var address_obj = results[0].properties.address;
		            	var address_html = '';
		            	Object.keys(address_obj).forEach(function(key) {
						  	address_html += '<input name="'+properties.data('name')+'['+key+']" value="'+address_obj[key]+'">';
						});
						properties.html(address_html);
		            }

	            });
            }
	        return $('.find-me-location').removeClass('loading');
		},
		getErrorLocation: function(position) {
	        return $('.find-me-location').removeClass('loading');
	    }
	}
	listingLocation.init();

    

})(jQuery);


