jQuery(document).ready(function ($) {

// Set all variables to be used in scope

// ADD IMAGE LINK
    jQuery('.upload-medias').on('click', function (event) {
        event.preventDefault();
        let frame;
        let btn = jQuery(this);
        // If the media frame already exists, reopen it.
        if (frame) {
            frame.open();
            return;
        }
        // Create a new media frame
        frame = wp.media({
            title: wp.i18n.__('Select or Upload Media', 'wizard-blocks'),
            button: {
                text: wp.i18n.__('Add this Media', 'wizard-blocks')
            },
            multiple: true  // Set to true to allow multiple files to be selected
        });
        // When an image is selected in the media frame...
        frame.on('select', () => {
            // Get media attachment details from the frame state
            //console.log(frame.state().get('selection'));
            frame.state().get('selection').each((attachment, index) => {
                //console.log(index);
                //console.log(attachment);
                let asset = attachment.toJSON();
                //console.log(asset);
                let input = btn.siblings('textarea');
                //console.log(input);
                // Send the attachment URL to our custom image input field.
                input.val((input.val() ? input.val() + '\n' : '') + asset.url);
                // Send the attachment id to our hidden input
                //input.val(asset.id);
                jQuery('.block-medias').append('<figure class="media-preview"><span class="media-delete dashicons dashicons-trash"></span><a href="'+asset.url+'" target="_blank"><img class="media media-new" src="'+asset.url+'"></a></figure>');
            });
        });
        // Finally, open the modal on click
        frame.open();
    });

    jQuery('.block-medias').on('click', '.media-delete', function () {
        if (confirm(wp.i18n.__('Are you sure to remove this Media?', 'wizard-blocks'))) {
            let input = jQuery('#_block_media');
            jQuery(this).parent().remove();
            input.val('');
            jQuery('.block-medias img').each(function(index, media){
                //console.log(media);
                let imgsrc = jQuery(media).attr('src');
                if (!jQuery(media).hasClass('media-new')) {
                    imgsrc = imgsrc.split('/').reverse()[0];
                }
                input.val(input.val()+(input.val() ? '\n' : '')+imgsrc);
            });
        }
        return false;
    });
    
    
    jQuery('.block-medias').on('click', 'a', function () {
        let imgurl = jQuery(this).attr('href');
        jQuery('#block-media-modal .details-image').attr('src', imgurl);
        jQuery('#block-media-modal').show();
        return false;
    });
    
    jQuery('#block-media-modal .media-modal-close').on('click', function () {
        jQuery('#block-media-modal').hide();
    });
    
    jQuery('#block-media-modal .left').on('click', function () {
        let url = jQuery('#block-media-modal .details-image').attr('src');
        let current = jQuery('.block-medias .media[src="'+url+'"]').closest('figure'); 
        if (current.prev()) {
            current.prev().find('a').trigger('click');
        } else {
            current.siblings().last().find('a').trigger('click');
        }
        return false;
    });
    jQuery('#block-media-modal .right').on('click', function () {
        let url = jQuery('#block-media-modal .details-image').attr('src');
        let current = jQuery('.block-medias .media[src="'+url+'"]').closest('figure'); 
        if (current.next()) {
            current.next().find('a').trigger('click');
        } else {
            current.siblings().first().find('a').trigger('click');
        }
        return false;
    });
    
    jQuery('#block-media-modal .delete-attachment').on('click', function () {
        let url = jQuery('#block-media-modal .details-image').attr('src');
        let current = jQuery('.block-medias .media[src="'+url+'"]').closest('figure'); 
        current.find('.media-delete').trigger('click');
        jQuery('#block-media-modal .media-modal-close').trigger('click');
        return false;
    });
    
    jQuery('#block-media-modal .details-image').on('load', function() {
        //console.log('update image data'); 
        //console.log(this);
        let url = jQuery(this).attr('src');
        let basename = url.split('/').reverse()[0];
        let ext = basename.toLowerCase().split('.').reverse()[0];
        let size = window.performance.getEntriesByName(url)[0];
        let current = jQuery('.block-medias .media[src="'+url+'"]');
        jQuery('#block-media-modal .filename > span').text(basename);
        jQuery('#block-media-modal .dimensions > span').text(this.naturalWidth+' x '+this.naturalHeight);
        
        if (size) {
            jQuery('#block-media-modal .file-type > span').text(size.contentType);
            jQuery('#block-media-modal .file-size > span').text(Math.round(size.transferSize/1024)); //KB
            //console.log(size); // or decodedBodySize might differ if compression is used on server side
        } else {
            jQuery('#block-media-modal .file-type > span').text(current.data('type') ? current.data('type')  : '?');
            jQuery('#block-media-modal .file-size > span').text(current.data('size') ? Math.round(current.data('size')/1024) : '?'); //KB
        }
        
        jQuery('#block-media-modal .view-attachment').attr('href', url);
        jQuery('#block-media-modal .download-attachment').attr('href', url);
        jQuery('#block-media-modal .attachment-details-copy-link').attr('value', url);
        //console.log(current.data('date'));
        let date = current.data('date') ? new Date(current.data('date') * 1000) : new Date();
        jQuery('#block-media-modal .uploaded > span').text(date.toString());
        
    });
    
    jQuery('#block-media-modal .edit-attachment').on('click', function () {
        let url = jQuery('#block-media-modal .details-image').attr('src');
        let basename = url.split('/').reverse()[0];
        let base = jQuery(this).data('url').replace('\\?', '?');
        // Copy the text inside the text field
        navigator.clipboard.writeText(base+basename);
        jQuery(this).next('.success').removeClass('hidden');
        setTimeout(()=>{
            jQuery(this).next('.success').addClass('hidden');
        }, 3000);
        return false;
    });
    jQuery('#block-media-modal .copy-attachment-url').on('click', function () {
        let url = jQuery('#block-media-modal .attachment-details-copy-link').attr('value');
        navigator.clipboard.writeText(url);
        jQuery(this).next('.success').removeClass('hidden');
        setTimeout(()=>{
            jQuery(this).next('.success').addClass('hidden');
        }, 3000);
        return false;
    });
    

});