jQuery(document).ready(function($){
    var size = $('#wordefinery-mailrucounter-size');
    var style = $('#wordefinery-mailrucounter-style');
    var color = $('#wordefinery-mailrucounter-color');
    var align = $('#wordefinery-mailrucounter-align');
    var size_sel = $('#wordefinery-mailrucounter-counter').find('div.size');
    var style_sel = $('#wordefinery-mailrucounter-counter').find('div.style');;
    var color_sel = $('#wordefinery-mailrucounter-counter').find('div.color');;
    var align_sel = $('#wordefinery-mailrucounter-preview').find('div.align');
    var preview_sel = $('#wordefinery-mailrucounter-preview').find('div.preview');

    var size_f = function() {
        size_sel.find('a').removeClass('selected');
        $(this).addClass('selected');
        size.val($(this).prop('name'));
        style_sel.hide();
        style_sel.filter('[idx="' + size.val() + '"]').show();
        color_sel.hide();
        if (style_sel.filter('[idx="' + size.val() + '"]').find('.selected').length) {
//            color_sel[size.val()].show();
        };
    }
    var style_f = function() {
        style_sel.find('a').removeClass('selected');
        $(this).addClass('selected');
        style.val($(this).prop('name'));
        color_sel.hide();
        color_sel.filter('[idx="' + size.val() + '.' + $(this).attr('colors') + '"]').find('img').each(function (i) {
            $(this).attr('src', 'http://top.mail.ru/i/counters/' + (style.val()*1 + $(this).parent().prop('name')*1) + '.gif' );
        })
        color_sel.filter('[idx="' + size.val() + '.' + $(this).attr('colors') + '"]').show();
        if (!color_sel.filter('[idx="' + size.val() + '.' + $(this).attr('colors') + '"]').find('.selected').length) {
            color.val('');
            color_sel.find('a').removeClass('selected');
        } else {
            preview_sel.find('img').attr('src', 'http://top.mail.ru/i/counters/' + (style.val()*1 + color.val()*1) + '.gif' );
        };
    }
    var color_f = function() {
        color_sel.find('a').removeClass('selected');
        $(this).addClass('selected');
        color.val($(this).prop('name'));
        preview_sel.find('img').attr('src', 'http://top.mail.ru/i/counters/' + (style.val()*1 + color.val()*1) + '.gif' );
    }
    var align_f = function() {
        align_sel.find('a').removeClass('selected');
        $(this).addClass('selected');
        align.val($(this).prop('name'));
        preview_sel.css('text-align', align.val());
    }
    size_sel.find('a').click(size_f);
    style_sel.find('a').click(style_f);
    color_sel.find('a').click(color_f);
    align_sel.find('a').click(align_f);
    if (size.val()>0) {
        size_sel.find('a[name='+size.val()+']').click();
        if (style.val()>0) {
            if (color.val()!='') color_sel.filter('[idx="' + size.val() + '.' + style_sel.find('a[name='+style.val()+']').attr('colors') + '"]').find('a[name='+color.val()+']').addClass('selected');
            style_sel.find('a[name='+style.val()+']').click();
        }
    }
    if (align.val()!='0' && align.val()!='') align_sel.find('a[name='+align.val()+']').click();
    if (style.val()>0 && size.val()>0) style_sel.filter('[idx="' + size.val() + '"]').scrollTop(style_sel.filter('[idx="' + size.val() + '"]').find('a[name='+style.val()+']').position().top - 5);
    if (color.val()!='' && size.val()>0 && style.val()>0) color_sel.filter('[idx="' + size.val() + '.' + style_sel.find('a[name='+style.val()+']').attr('colors') + '"]').scrollTop(color_sel.filter('[idx="' + size.val() + '.' + style_sel.find('a[name='+style.val()+']').attr('colors') + '"]').find('a[name='+color.val()+']').position().top - 5);


    var site_id = $('#wordefinery-mailrucounter-site_id');
    var site_id_btn = $('#wordefinery-mailrucounter-check_site_id');
    var site_id_msg = $('#wordefinery-mailrucounter-check_site_id-message');
    var check_f = function() {
        jQuery.ajax({
            type: "get",url: "admin-ajax.php", data: { action: 'check_site_id', site_id: site_id.val() },
            beforeSend: function() { site_id_btn.prop('disabled', true); site_id_msg.html('...'); },
            success: function(html){
                site_id_msg.html(html);
                site_id_btn.prop('disabled', false);
            },
            error: function(html) {
                site_id_msg.html('error');
                site_id_btn.prop('disabled', false);
            }
        });
    }
    site_id_btn.click(check_f);

    var site_url = $('#wordefinery-mailrucounter-site_url');
    var site_url_btn = $('#wordefinery-mailrucounter-get_site_id');
    var site_url_msg = $('#wordefinery-mailrucounter-get_site_id-message');
    var get_f = function() {
        jQuery.ajax({
            type: "get",url: "admin-ajax.php", data: { action: 'get_site_id', site_url: site_url.val() },
            beforeSend: function() { site_url_btn.prop('disabled', true); site_url_msg.html('...'); },
            success: function(html){
                if (html>0) {
                    site_url_msg.html('ok');
                    site_id.val(html);
                } else {
                    site_url_msg.html(html);
                }
                site_url_btn.prop('disabled', false);
            },
            error: function(html) {
                site_url_msg.html('error');
                site_url_btn.prop('disabled', false);
            }
        });
    }
    site_url_btn.click(get_f);

});
