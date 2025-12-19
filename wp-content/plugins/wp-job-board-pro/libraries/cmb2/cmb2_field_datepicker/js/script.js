/* global wp_job_board_pro_datepicker */
jQuery(document).ready(function($) {
    var datePickerOptions = {
        altFormat  : 'yy-mm-dd',
    };
    if ( typeof wp_job_board_pro_datepicker !== 'undefined' ) {
        datePickerOptions.dateFormat = wp_job_board_pro_datepicker.date_format;
    }

    $( 'input.wjbp-datepicker' ).each( function(){
        var $hidden_input = $( '<input />', { type: 'hidden', name: $(this).attr( 'name' ) } ).insertAfter( $( this ) );
        $(this).attr( 'name', $(this).attr( 'name' ) + '-datepicker' );
        $(this).keyup( function() {
            if ( '' === $(this).val() ) {
                $hidden_input.val( '' );
            }
        } );
        var fieldOpts = $(this).data( 'datepicker' ) || {};
        
        $(this).datepicker( $.extend( {}, datePickerOptions, fieldOpts, {
            altField: $hidden_input,
            beforeShow: function(input, inst) {
               $('#ui-datepicker-div').addClass( 'cmb2-element' );
            }
        } ) );
        if ( $(this).val() ) {
            var dateParts = $(this).val().split("-");
            if ( 3 === dateParts.length ) {
                var selectedDate = new Date(parseInt(dateParts[0], 10), (parseInt(dateParts[1], 10) - 1), parseInt(dateParts[2], 10));
                $(this).datepicker('setDate', selectedDate);
            }
        }
    });
});
