
var count = 1;

jQuery(function() {
    jQuery(window).bottom({proximity: 0.05});
    jQuery(window).on('bottom', function() {
    var obj = jQuery(this);
    var sort = $("input[name='sort']:checked").val();
    var prcid = $('[name="prcid"]:checked').map(function(){
        return $(this).val();
    }).get();
    var keyword = $('#search [name=keyword]').val();
    var cname = $('attr').attr('cname');
    var tag = $('attr').attr('tag');
    var data = {
        'count': count,
        'prcid': prcid,
        'sort' : sort,
        'keyword' : keyword,
        'cname':cname,
        'tag':tag
    };
    if (!obj.data('loading')) {
        obj.data('loading', true);
        jQuery('#loading').html('Loading…');
        jQuery.ajax({
            type:"POST",
            url: "?route=pages/loadrelease",
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
