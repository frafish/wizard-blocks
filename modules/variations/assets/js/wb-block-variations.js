jQuery(document).ready(function ($) {

    setTimeout(function() {
        jQuery('#variation-editor').hide();
    }, 100); //waiting Select2

    let variation_add = jQuery('#block_variations_meta_box .attr_add');
    variation_add.on('click', function () {
        jQuery('#variation-editor').toggle();
        jQuery(this).hide();
    });

    let variations = jQuery('#_block_variations');

    if (variations.length) {
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
        jQuery('._block_variations').each(function() {
            // Initialize them one by one
            wp.codeEditor.initialize($(this), editorSettings);
        });

        variations.on('click', '.attr_edit', function () {
            //console.log('edit');
            let row = jQuery(this).closest('.repeat_attr');

            jQuery('#variation-editor').show();
            variation_add.hide();

            let variation_json = row.find('textarea._block_variations').text();
            //console.log(variation_json);

            let variation = JSON.parse(variation_json);
            console.log(variation);
            
            jQuery('#variation-name').val(variation.name);
            if (variation.title) jQuery('#variation-title').val(variation.title);
            if (variation.description) jQuery('#variation-description').val(variation.description);
            if (variation.category) jQuery('#variation-category').val(variation.category);
            if (variation.keywords) jQuery('#variation-keywords').val(variation.keywords.join(', '));
            if (variation.isDefault) jQuery('#variation-isDefault').prop('checked', true);
            if (variation.innerBlocks) jQuery('#variation-innerBlocks').val(JSON.stringify(variation.innerBlocks));
            if (variation.isActive) jQuery('#variation-isActive').val(variation.isActive.join(', '));
            if (variation.icon) { 
                if (variation.icon.substr(0,5) == '<svg ') {
                    jQuery('#variation_icon').val('').trigger('change');
                    jQuery('#variation_icon_src').val(variation.icon);
                } else {
                    jQuery('#variation_icon').val(variation.icon).trigger('change');
                }
            }
            //console.log(variation.scope);
            if (variation.scope) { 
                if (variation.scope.includes("block")) jQuery('#variation-scope-block').prop('checked', true);
                if (variation.scope.includes("inserter")) jQuery('#variation-scope-inserter').prop('checked', true);
                if (variation.scope.includes("transform")) jQuery('#variation-scope-transform').prop('checked', true);
            }
            
            function set_variation_attributes($values, $name) {
                jQuery.each($values, function(ekey, evalue){
                    //console.log(ekey, evalue);
                    let id = '#variation-'+$name+'-'+ekey;
                    let val = Array.isArray(evalue) || Object.is(evalue) ? JSON.stringify(evalue) : evalue;
                    if (jQuery(id).attr('type') == 'checkbox' && val == true) {
                        // for boolean
                        jQuery(id).prop('checked', true);
                    } else {
                        // for string, number/integer, array/object
                        jQuery(id).val(val);
                    }
                });
            }
            if (variation.example) { 
                set_variation_attributes(variation.example, 'example');
            }
            if (variation.attributes) { 
                set_variation_attributes(variation.attributes, 'attributes');
            }

            return false;
        });
        
        variations.on('click', '.attr_remove', function () {
            //console.log('remove');
            if (confirm(wp.i18n.__("Are you sure you want to remove this variation?", "wizard-blocks"))) {
                let row = jQuery(this).closest('.repeat_attr');
                row.find('.variation-delete').prop('checked', true);
                row.fadeOut();
            }
            return false;
        });

    }

});