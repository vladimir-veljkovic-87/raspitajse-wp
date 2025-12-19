/* global wp_job_board_pro_datepicker */
jQuery(document).ready(function($) {
    var datePickerOptions = {
        altFormat  : 'yy-mm-dd',
        changeYear: true,
        changeMonth: true,
    };
    if ( typeof wp_job_board_pro_datepicker !== 'undefined' ) {
        datePickerOptions.dateFormat = wp_job_board_pro_datepicker.date_format;
    }

    function wjbp_datepicker(){
        $( 'input.wjbp-datepicker2' ).each( function(){
            var $id = wjbp_make_random_id(5);
            var $hidden_input = $( '<input />', { type: 'hidden', name: $(this).attr( 'name' ) } ).insertAfter( $( this ) );
            $(this).attr( 'name', $(this).attr( 'name' ) + '-datepicker' );
            $(this).attr( 'id', $(this).attr( 'id' ) + '-' +$id );
            $(this).keyup( function() {
                if ( '' === $(this).val() ) {
                    $hidden_input.val( '' );
                }
            } );
            var fieldOpts = $(this).data( 'datepicker' ) || {};
            $('#'+$(this).attr( 'id' )).datepicker( $.extend( {}, datePickerOptions, fieldOpts, {
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
    }

    function wjbp_make_random_id(length) {
        let result = '';
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        const charactersLength = characters.length;
        let counter = 0;
        while (counter < length) {
          result += characters.charAt(Math.floor(Math.random() * charactersLength));
          counter += 1;
        }
        return result;
    }

    wjbp_datepicker();
    


    // When a new group row is added
    $('.cmb-repeatable-group').on('cmb2_add_row', function (event, newRow) {
        // Reinitialise the field we previously destroyed
        $(newRow).prev().find('input.wjbp-datepicker2').each(function () {
            wjbp_datepicker();
        });

    });

    // When a group row is shifted
    $('.cmb-repeatable-group').on('cmb2_shift_rows_complete', function (event, instance) {

        var groupWrap = $(instance).closest('.cmb-repeatable-group');
        groupWrap.find('input.wjbp-datepicker2').each(function () {
            wjbp_datepicker();
        });

    });

    // When a new repeatable field row is added
    $('.cmb-repeat-table').on('cmb2_add_row', function (event, newRow) {

        // Reinitialise the field we previously destroyed
        $(newRow).prev().find('input.wjbp-datepicker2').each(function () {
            wjbp_datepicker();
        });

    });

});
