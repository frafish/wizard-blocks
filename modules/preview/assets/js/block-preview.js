/*//console.log('preview');
jQuery(window).on('load', function ($) {
    var block_preview_reloading = false;
    setTimeout(()=>{
        //console.log(window._block_render);
        window._block_render.codemirror.on('change', function (instance, changeObj) {
            //console.log('codemirror render change');
            if (!block_preview_reloading) {
                console.log('ok, refresh');
                block_preview_reloading = true;
                var not_too_much_reload = setInterval(function () {
                    if (jQuery('#block-instant-preview').prop('checked')) {
                        // refresh iframe
                        console.log('refreshing iframe');
                        document.getElementById('block-preview').contentDocument.location.reload(true);
                    }
                    block_preview_reloading = false;
                    clearInterval(not_too_much_reload);
                }, 1000);
            } else {
                console.log('no, wait!');
            }
        });
        
    }, 1000);
    
});*/