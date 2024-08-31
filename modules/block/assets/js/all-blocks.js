window.addEventListener("load", (event) => {
    jQuery('#blocks-search').on('change keyup', function () {
        jQuery('.blocks .hentry').show();
        let value = jQuery(this).val();
        //console.log(value);
        if (value) {
            jQuery('.blocks .hentry').each(function () {
                if (!jQuery(this).find('.title').text().toLowerCase().includes(value.toLowerCase())) {
                    jQuery(this).hide();
                }
            });
        }
    });

    jQuery('.blocks-filter a').on('click', function () {
        jQuery('.blocks-filter a.current').removeClass('current');
        jQuery(this).addClass('current');
        jQuery('.blocks .hentry').show();
        let filter = jQuery(this).attr('href').replace('#', '');
        if (filter) {
            //console.log(filter);
            jQuery('.blocks .hentry').hide();
            jQuery('.blocks .hentry.block-' + filter).show();
        }
        return false;
    });

});         