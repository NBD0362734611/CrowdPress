var count = 1;
jQuery(function() {
    jQuery(window).bottom({proximity: 0.05});
    jQuery(window).on('bottom', function() {
    var obj = jQuery(this);
    var data = {
        'count': count
    };
    if (!obj.data('loading')) {
        obj.data('loading', true);
        jQuery('#loading').html('Loading…');
        jQuery.ajax({
            type:"POST",
            url: "?route=users/loadmyfeed",
            // dataType: "json",
            data: data,
            cache: false,
            success: function(html){
                if ( html ) {
                    count++;
                    jQuery("#release_body").append(html);
                    jQuery('#loading').html('');
                    obj.data('loading', false);
                }else{
                    jQuery(window).unbind('bottom');
                    jQuery(window).unbind('scroll');
                    jQuery('#loading').html('');
                }
            }
        });
    }
    });
});
