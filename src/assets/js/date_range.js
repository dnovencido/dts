$(function() {
    $('.date-range').daterangepicker({
        autoUpdateInput: false,   // 🔥 prevents default date
        opens: 'right',
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [
               moment().subtract(1, 'month').startOf('month'),
               moment().subtract(1, 'month').endOf('month')
           ]
        },

        locale: {
            cancelLabel: 'Clear'
        }
    });

    // When user selects a range
    $('.date-range').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(
            picker.startDate.format('MM/DD/YYYY') +
            ' - ' +
            picker.endDate.format('MM/DD/YYYY')
        );
    });

    // When user clicks Clear
    $('.date-range').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });

});