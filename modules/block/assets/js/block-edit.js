jQuery(document).ready(function ($) {
    var editorSettings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};
    editorSettings.codemirror = _.extend(
            {},
            editorSettings.codemirror,
            {
                indentUnit: 2,
                tabSize: 2,
                mode: 'text/x-php'
            }
    );
    var _block_render = wp.codeEditor.initialize(jQuery('#_block_render_file'), editorSettings);

    editorSettings.codemirror = _.extend(
            {},
            editorSettings.codemirror,
            {
                indentUnit: 2,
                tabSize: 2,
                mode: 'css'
            }
    );
    var _block_style = wp.codeEditor.initialize(jQuery('#_block_style_file'), editorSettings);
    var _block_editorStyle = wp.codeEditor.initialize(jQuery('#_block_editorStyle_file'), editorSettings);
    var _block_viewStyle = wp.codeEditor.initialize(jQuery('#_block_viewStyle_file'), editorSettings);

    editorSettings.codemirror = _.extend(
            {},
            editorSettings.codemirror,
            {
                indentUnit: 2,
                tabSize: 2,
                mode: 'javascript',
            }
    );
    var _block_script = wp.codeEditor.initialize(jQuery('#_block_script_file'), editorSettings);
    var _block_editorScript = wp.codeEditor.initialize(jQuery('#_block_editorScript_file'), editorSettings);
    var _block_viewScriptModule = wp.codeEditor.initialize(jQuery('#_block_viewScriptModule_file'), editorSettings);
    var _block_viewScript = wp.codeEditor.initialize(jQuery('#_block_viewScript_file'), editorSettings);

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
    var _block_attributes = wp.codeEditor.initialize(jQuery('#_block_attributes'), editorSettings);
    //console.log(_block_attributes);
    //console.log(wp.codeEditor);
    //console.log(wp.CodeMirror);

    editorSettings.codemirror = _.extend(
            {},
            editorSettings.codemirror,
            {
                indentUnit: 2,
                tabSize: 2,
                autoCloseBrackets: true,
                mode: 'application/ld+json'
            }
    );
    var _block_supports_custom = wp.codeEditor.initialize(jQuery('#_block_supports_custom'), editorSettings);
    var _block_providesContext = wp.codeEditor.initialize(jQuery('#_block_providesContext'), editorSettings);
    var _block_blockHooks = wp.codeEditor.initialize(jQuery('#_block_blockHooks'), editorSettings);
    var _block_extra = wp.codeEditor.initialize(jQuery('#_block_extra'), editorSettings);

    /**************************************************************************/

    // Set all variables to be used in scope
    var frame;
    var btn;
    // ADD IMAGE LINK
    jQuery('.upload-assets').on('click', function (event) {
        event.preventDefault();
        btn = jQuery(this);
        // If the media frame already exists, reopen it.
        if (frame) {
            frame.open();
            return;
        }
        // Create a new media frame
        frame = wp.media({
            title: 'Select or Upload Assets',
            button: {
                text: 'Use this asset'
            },
            multiple: true  // Set to true to allow multiple files to be selected
        });
        // When an image is selected in the media frame...
        frame.on('select', () => {
            // Get media attachment details from the frame state
            //console.log(frame.state().get('selection'));
            frame.state().get('selection').each((attachment, index ) => {
                //console.log(index);
                //console.log(attachment);
                let asset = attachment.toJSON();
                //console.log(asset);
                let input = btn.siblings('input');
                //console.log(input);
                // Send the attachment URL to our custom image input field.
                input.val( (input.val() ? input.val()+', ' : '') + asset.url );
                // Send the attachment id to our hidden input
                //input.val(asset.id);
            });
        });
        // Finally, open the modal on click
        frame.open();
    });

    /**************************************************************************/

    jQuery('#_block_icon').select2({
        templateResult: function (state) {
            if (!state.id) {
                return state.text;
            }
            var $state = $(
                    '<span class="dashicons dashicons-' + state.element.value + '"></span> ' + state.text + '</span>'
                    );
            return $state;
        }
    });

    /**************************************************************************/

    /*
    jQuery('.tab-head').on('click', function () {
        jQuery('.tab-head').removeClass('tab-active');
        jQuery(this).addClass('tab-active');
        jQuery('.tab-body').toggle();
        return false;
    });
    setTimeout(function () {
        jQuery('.tab-js').hide();
    }, 1000);
    */
   
    /**************************************************************************/
   
    if (jQuery('#_block_icon').val()) {
        jQuery('#_block_icon_svg').hide();
    }
    jQuery('#_block_icon').on('change', function () {
        if (jQuery(this).val()) {
            jQuery('#_block_icon_svg').hide();
        } else {
            jQuery('#_block_icon_svg').show();
        }
    });
   
    /**************************************************************************/

    var attr_editor = jQuery('#_block_attributes_editor');
    var attr_attributes = jQuery('#_block_attributes');
    var _block_attributes_update = false;
    var _block_attributes_lock = false;
    
    function update_block_label(row, title) {
        let key = row.find('.key').val();
        //row.find('.attr_name').text(row.find('.attr_name').text().replace('attr_key', key).replace('attr_title', title));
        row.find('.attr_name').text('[' + key + '] ' + title);
    }
    function update_block_attributes_editor(trigger = true) {
        attr_editor.find('.repeat_attrs').html(''); // reset
        if (attr_attributes.val()) {
            var attributes = JSON.parse(attr_attributes.val());
            if (attributes) {
                //console.log(attributes);
                var index = 0;
                jQuery.each(attributes, function (key, element) {
                    //console.log(index);
                    //console.log(key);
                    //console.log(element);
                    attr_editor.find('.attr_add').trigger('click');
                    let row = attr_editor.find('.repeat_attr').eq(index);
                    //console.log(row);
                    let title = element.label ? element.label : key;
                    row.find('.key').val(key);
                    if (element.type) {
                        row.find('.type').val(element.type);
                        delete(element.type);
                    } else {
                        row.find('.type').val(''); //eval
                    }
                    if (element.component) {
                        row.find('.component').val(element.component);
                        delete(element.component);
                    }
                    if (element.label) {
                        row.find('.label').val(element.label);
                        delete(element.label);
                    }
                    if (element.help) {
                        row.find('.help').val(element.help);
                        delete(element.help);
                    }
                    if (element.hasOwnProperty('default')) {
                        if (row.find('.type').val() == 'object') {
                            element.default = JSON.stringify(element.default);
                        }
                        row.find('.default').val(element.default);
                        delete(element.default);
                    }
                    if (element.hasOwnProperty('selected')) {
                        row.find('.default').val(element.selected);
                        delete(element.selected);
                    }
                    if (element.hasOwnProperty('template')) {
                        row.find('.default').val(element.template);
                        delete(element.template);
                    }
                    if (element.enum) {
                        row.find('.options').val(element.enum.join("\r\n"));
                        delete(element.enum);
                    }
                    if (element.allowedBlocks) {
                        row.find('.options').val(element.allowedBlocks.join("\r\n"));
                        delete(element.allowedBlocks);
                    }
                    if (element.options) {
                        if (typeof element.options == "object") {
                            if (Array.isArray(element.options)) {
                                row.find('.options').val(element.options.join("\r\n"));
                            } else {
                                let options = '';
                                for (const property in element.options) {
                                    //console.log(property);
                                    //console.log(element.options[property]);
                                    let opt = element.options[property];
                                    if (property != element.options[property]) {
                                        opt = opt + "|" + property;
                                    }
                                    options += options ? "\r\n" + opt : opt;
                                }
                                row.find('.options').val(options);
                            }
                        }
                        delete(element.options);
                    }
                    if (element.source) {
                        row.find('.source').val(element.source);
                        delete(element.source);
                    }
                    if (element.attribute) {
                        row.find('.attribute').val(element.attribute);
                        delete(element.attribute);
                    }
                    if (element.selector) {
                        row.find('.selector').val(element.selector);
                        delete(element.selector);
                    }
                    if (element.multiple) {
                        row.find('.multiple').val(element.multiple ? 'true' : 'false');
                        delete(element.multiple);
                    }
                    if (Object.keys(element).length) {
                        row.find('label[for="extra"]').show();
                        row.find('.extra').val(JSON.stringify(element));
                    } else {
                        row.find('label[for="extra"]').hide();
                    }
                    if (element.position) {
                        row.find('.position').val(element.position);
                        delete(element.position);
                    }
                    if (element.className) {
                        row.find('.className').val(element.className);
                        delete(element.className);
                    }
                    update_block_label(row, title);
                    if (trigger) {
                        //console.log('input force trigger');
                        row.find('.component, .source').trigger('change');
                    }
                    index++;
                });
            }
        }
    }

    function update_block_attributes() {
        let attributes = {};
        attr_editor.find('.repeat_attr').each(function (index, row) {
            row = jQuery(row);
            let key = row.find('.key').val();
            //console.log(key);
            if (key) {
                attributes[key] = {};
                if (row.find('.type').val()) {
                    attributes[key]['type'] = row.find('.type').val();
                }
                if (row.find('.component').val()) {
                    attributes[key]['component'] = row.find('.component').val();
                }
                if (row.find('.label').val()) {
                    attributes[key]['label'] = row.find('.label').val();
                }
                if (row.find('.help').val()) {
                    attributes[key]['help'] = row.find('.help').val();
                }
                if (row.find('.default').val()) {
                    let defa = row.find('.default').val();
                    if (attributes[key]['type'] == 'number' || attributes[key]['type'] == 'integer') {
                        defa = parseFloat(defa);
                    }
                    if (attributes[key]['type'] == 'boolean') {
                        defa = defa == 'true';
                    }
                    if (attributes[key]['type'] == 'object') {
                        defa = JSON.parse(defa);
                    }
                    if (attributes[key]['component'] && ['ToggleControl', 'CheckboxControl'].includes(attributes[key]['component'])) {
                        attributes[key]['checked'] = true;
                    } else if (attributes[key]['component'] && attributes[key]['component'] == 'RadioControl') {
                        attributes[key]['selected'] = row.find('.default').val();
                    } else if (attributes[key]['component'] && attributes[key]['component'] == 'InputControl') {
                        attributes[key]['value'] = row.find('.default').val();
                    } else if (attributes[key]['component'] && attributes[key]['component'] == 'InnerBlocks') {
                        attributes[key]['template'] = row.find('.default').val();
                    } else {
                        attributes[key]['default'] = defa;
                    }
                }
                if (row.find('.className').val()) {
                    attributes[key]['className'] = row.find('.className').val();
                }
                if (row.find('.inputType').val()) {
                    if (attributes[key]['component'] && attributes[key]['component'] == 'InputControl') {
                        attributes[key]['inputType'] = row.find('.inputType').val();
                    }
                }
                if (row.find('.options').val()) {
                    let rowptions = row.find('.options').val().split('\n');
                    if ( attributes[key]['component'] && attributes[key]['component'] == 'InnerBlocks') {
                        attributes[key]['allowedBlocks'] = rowptions;
                        attributes[key]['type'] = 'null';
                    } else {
                        if (row.find('.options').val().includes('|') || attributes[key]['component']) {
                            if (!row.find('.options').val().includes('|') && (attributes[key]['type'] == 'number' || attributes[key]['type'] == 'integer')) {
                                attributes[key]['options'] = [];
                            } else {
                                attributes[key]['options'] = {};
                            }
                            let is_array = true;
                            jQuery(rowptions).each(function (id, label) {
                                if (Array.isArray(attributes[key]['options'])) {                                
                                    label = value = parseFloat(label);
                                    attributes[key]['options'].push(label);
                                } else {
                                    let tmp = label.split('|');
                                    let value = label;
                                    if (tmp.length > 1) {
                                        value = tmp.pop();
                                        label = tmp.pop();
                                        is_array = false;
                                    }
                                    attributes[key]['options'][value] = label;
                                }
                            });
                            if (is_array) {
                                if (!Array.isArray(attributes[key]['options'])) {
                                    attributes[key]['options'] = Object.values(attributes[key]['options']);
                                }  
                            }
                        } else {
                            attributes[key]['enum'] = rowptions;
                            delete(attributes[key]['type']);
                        }
                    }
                }
                if (row.find('.source').val()) {
                    attributes[key]['source'] = row.find('.source').val();
                }
                if (row.find('.attribute').val()) {
                    attributes[key]['attribute'] = row.find('.attribute').val();
                }
                if (row.find('.selector').val()) {
                    attributes[key]['selector'] = row.find('.selector').val();
                }
                if (row.find('.multiple').val() && row.find('.multiple').val() == 'true') {
                    attributes[key]['multiple'] = true;
                }
                if (row.find('.position').val() && row.find('.position').val() != 'default') {
                    attributes[key]['position'] = row.find('.position').val();
                }
                if (row.find('.extra').val()) {
                    attributes[key] = {...attributes[key], ...JSON.parse(row.find('.extra').val())};
                }
            }
        });
        //console.log(attributes);
        let attr_json = JSON.stringify(attributes, null, 4);
        //console.log(attr_json);
        attr_attributes.val(attr_json);
        //_block_attributes.codemirror.refresh()
        //_block_attributes.destroy();
        
        if (!_block_attributes_lock) {
            //console.log('codemirror reinit');
            attr_attributes.next('.CodeMirror').remove();
            _block_attributes = wp.codeEditor.initialize(attr_attributes, editorSettings);

            _block_attributes.codemirror.on('change', function () {
                // destroy and rebuild 
                //console.log('codemirror change');
                if (_block_attributes_update) {
                    clearTimeout(_block_attributes_update);
                }
                _block_attributes_update = setTimeout(function(){
                    _block_attributes.codemirror.save();
                    //console.log('codemirror save');
                    update_block_attributes_editor(false);
                }, 500);
                
                if (_block_attributes_lock) {
                    clearTimeout(_block_attributes_lock);
                }
                _block_attributes_lock = setTimeout(function(){
                    //console.log('codemirror unlock');
                    _block_attributes_lock = false;
                }, 1000);
            });
        }
        //attr_attributes.trigger('change');
    } 
    
    if (attr_editor.length) {
        //console.log('init');
        attr_editor.data('row', attr_editor.find('.repeat_attrs').html());
        //console.log(attr_editor.data('row'));
        attr_editor.find('.repeat_attr').remove();

        /*
        _block_attributes.codemirror.on('change', function () {
            // destroy and rebuild 
            console.log('codemirror change');
            _block_attributes.codemirror.save();
            update_block_attributes_editor();
        });
        //console.log(_block_attributes.codemirror);
        attr_attributes.on('change', function () {
            console.log('textarea change');
            // destroy and rebuild 
            update_block_attributes_editor();
        });
        */
       
        attr_editor.on('click', '.attr_add', function () {
            //console.log('add');
            attr_editor.find('.repeat_attrs').append(attr_editor.data('row'));
            attr_editor.find('.repeat_attrs').find('.repeat_attr:last-child').find('.component, .source').trigger('change');
        });

        attr_editor.on('click', '.repeat_attr .button', function () {
            //console.log('update');
            setTimeout(function () {
                update_block_attributes();
            }, 100);
        });

        attr_editor.on('click', '.attr_toggle, .attr_name', function () {
            //console.log('toggle');
            jQuery(this).closest('.attr_ops').siblings('.attr_data').toggle();
        });

        attr_editor.on('click', '.attr_up', function () {
            //console.log('up');
            let row = jQuery(this).closest('.repeat_attr');
            if (row.eq()) {
                row.insertBefore(row.prev());
            }
        });

        attr_editor.on('click', '.attr_down', function () {
            //console.log('down');
            let row = jQuery(this).closest('.repeat_attr');
            if (!row.is(':last-child')) {
                row.insertAfter(row.next());
            }
        });

        attr_editor.on('click', '.attr_clone', function () {
            //console.log('clone');
            let row = jQuery(this).closest('.repeat_attr');
            //row.clone().appendTo(row.parent());
            let clone = row.clone();
            clone.insertAfter(row);
            clone.find('.attr_toggle').trigger('click');
        });

        attr_editor.on('click', '.attr_remove', function () {
            //console.log('remove');
            if (confirm(wp.i18n.__("Are you sure you want to remove this attribute?", "wizard-blocks"))) {
                let row = jQuery(this).closest('.repeat_attr');
                row.remove();
            }
        });


        attr_editor.on('change', '.repeat_attr input, .repeat_attr select, .repeat_attr textarea', function () {
            //console.log('update');
            let row = jQuery(this).closest('.repeat_attr');
            if (jQuery(this).hasClass('key') || jQuery(this).hasClass('label')) {
                let title = row.find('.label').val();
                update_block_label(row, title);
            }
            if (jQuery(this).hasClass('component')) {
                if (['InputControl'].includes(jQuery(this).val())) {
                    row.find('label[for="inputType"]').show();
                } else {
                    row.find('label[for="inputType"]').hide();
                }
                if (['SelectControl'].includes(jQuery(this).val())) {
                    row.find('label[for="multiple"]').show();
                } else {
                    row.find('label[for="multiple"]').hide();
                }
                if (['SelectControl', 'RadioControl', 'ButtonGroup', 'InnerBlocks', ''].includes(jQuery(this).val())) {
                    row.find('label[for="options"]').show();
                } else {
                    row.find('label[for="options"]').hide();
                }
            }

            if (jQuery(this).hasClass('source')) {
                if (![''].includes(jQuery(this).val())) {
                    row.find('label[for="selector"]').show();
                    if (['attribute'].includes(jQuery(this).val())) {
                        row.find('label[for="attribute"]').show();
                    }
                } else {
                    row.find('label[for="selector"]').hide();
                    if (!['attribute'].includes(jQuery(this).val())) {
                        row.find('label[for="attribute"]').hide();
                    }
                }
            }
            
            setTimeout(function () {
                update_block_attributes();
            }, 100);
        });

        update_block_attributes_editor()
    }
});