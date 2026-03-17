jQuery(document).ready(function ($) {

    setTimeout(function() {
        jQuery('#style-editor').hide();
    }, 100); //waiting Select2

    let style_add = jQuery('#block_styles_meta_box .attr_add');
    style_add.on('click', function () {
        jQuery('#style-editor').toggle();
        jQuery(this).hide();
    });

    let styles = jQuery('#_block_styles');

    if (styles.length) {
        //console.log('init');
        
        var editorSettings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};
            editorSettings.codemirror = _.extend(
                {},
                editorSettings.codemirror,
                {
                    indentUnit: 2,
                    tabSize: 2,
                    mode: 'application/ld+json',
                    autoRefresh: true
                }
        );
        jQuery('._block_styles').each(function() {
            // Initialize them one by one
            wp.codeEditor.initialize($(this), editorSettings);
        });

        styles.on('click', '.attr_edit', function () {
            //console.log('edit');
            let row = jQuery(this).closest('.repeat_attr');

            jQuery('#style-editor').show();
            style_add.hide();

            let style_json = row.find('textarea._block_styles').text();
            //console.log(style_json);

            let style = JSON.parse(style_json);
            console.log(style);
            
            jQuery('#style-name').val(style.name);
            if (style.title) jQuery('#style-title').val(style.title);
            if (style.isDefault) jQuery('#style-isDefault').prop('checked', true);
            
            if (style.inlineStyle) jQuery('#style-inlineStyle').val(style.inlineStyle);
            if (style.styleHandle) jQuery('#style-styleHandle').val(style.styleHandle);
            if (style.styleData) jQuery('#style-styleData').val(JSON.stringify(style.styleData));
            
            return false;
        });
        
        styles.on('click', '.attr_remove', function () {
            //console.log('remove');
            if (confirm(wp.i18n.__("Are you sure you want to remove this style?", "wizard-blocks"))) {
                let row = jQuery(this).closest('.repeat_attr');
                row.find('.style-delete').prop('checked', true);
                row.fadeOut();
            }
            return false;
        });

    }

});