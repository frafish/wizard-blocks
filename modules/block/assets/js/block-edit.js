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
    var _block_render = wp.codeEditor.initialize(jQuery('#_block_render'), editorSettings);

    editorSettings.codemirror = _.extend(
            {},
            editorSettings.codemirror,
            {
                indentUnit: 2,
                tabSize: 2,
                mode: 'css'
            }
    );
    var _block_style = wp.codeEditor.initialize(jQuery('#_block_style'), editorSettings);
    var _block_editorStyle = wp.codeEditor.initialize(jQuery('#_block_editorStyle'), editorSettings);
    var _block_viewStyle = wp.codeEditor.initialize(jQuery('#_block_viewStyle'), editorSettings);

    editorSettings.codemirror = _.extend(
            {},
            editorSettings.codemirror,
            {
                indentUnit: 2,
                tabSize: 2,
                mode: 'javascript',
            }
    );
    var _block_script = wp.codeEditor.initialize(jQuery('#_block_script'), editorSettings);
    var _block_editorScript = wp.codeEditor.initialize(jQuery('#_block_editorScript'), editorSettings);
    var _block_viewScriptModule = wp.codeEditor.initialize(jQuery('#_block_viewScriptModule'), editorSettings);
    var _block_viewScript = wp.codeEditor.initialize(jQuery('#_block_viewScript'), editorSettings);

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
    console.log(_block_attributes);
    console.log(wp.codeEditor);
    console.log(wp.CodeMirror);

    var editorSettings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};
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
    var _block_extra = wp.codeEditor.initialize(jQuery('#_block_extra'), editorSettings);


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

    jQuery('.tab-head').on('click', function () {
        jQuery('.tab-head').removeClass('tab-active');
        jQuery(this).addClass('tab-active');
        jQuery('.tab-body').toggle();
        return false;
    });
    setTimeout(function () {
        jQuery('.tab-js').hide();
    }, 1000);

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
    
    
    
    var attr_editor = jQuery('#_block_attributes_editor');
    var attr_attributes = jQuery('#_block_attributes');
    
    function update_block_attributes_editor() {
        attr_editor.find('.repeat_attrs').html(''); // reset
        var attributes = JSON.parse(attr_attributes.val());
        //console.log(attributes);
        var index = 0;
        jQuery.each(attributes, function(key, element){
            //console.log(index);
            //console.log(key);
            //console.log(element);
            attr_editor.find('.attr_add').trigger('click');
            let row = attr_editor.find('.repeat_attr').eq(index);
            console.log(row);
            let title = element.title ? element.title : key;
            row.find('.attr_name').text(row.find('.attr_name').text().replace('attr_key', key).replace('attr_title', title));
            row.find('.key').val(key);
            if (element.type) {
                row.find('.type').val(element.type);
                delete(element.type);
            }
            if (element.control) {
                row.find('.control').val(element.control);
                delete(element.control);
            }
            if (element.label) {
                row.find('.label').val(element.label);
                delete(element.label);
            }
            if (element.hasOwnProperty('default')) {
                row.find('.default').val(element.default);
                delete(element.default);
            }
            if (element.hasOwnProperty('selected')) {
                row.find('.default').val(element.selected);
                delete(element.selected);
            }
            if (element.enum) {
                row.find('.options').val(element.enum.join("\r\n"));
                delete(element.enum);
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
            if (Object.keys(element).length) {
                row.find('.extra').val(JSON.stringify(element));
            }
            index++;
        });
    }
    
    function update_block_attributes() {
        let attributes = {};
        attr_editor.find('.repeat_attr').each(function(index, row){
            row = jQuery(row);
            let key = row.find('.key').val();
            //console.log(key);
            if (key) {
                attributes[key] = {};
            }
            if (row.find('.type').val()) {
                attributes[key]['type'] = row.find('.type').val();
            }
            if (row.find('.control').val()) {
                attributes[key]['control'] = row.find('.control').val();
            }
            if (row.find('.label').val()) {
                attributes[key]['label'] = row.find('.label').val();
            }
            if (row.find('.default').val()) {
                attributes[key]['default'] = row.find('.default').val();
            }
            if (row.find('.options').val()) {
                let evals = row.find('.options').val().split('\n');
                if (row.find('.options').val().includes('|')) {
                    attributes[key]['options'] = {};
                    jQuery(evals).each(function(id, label){
                        let tmp = label.split('|');
                        let value = label;
                        if (tmp.length > 1) {
                            value = tmp.pop();
                            label = tmp.pop();
                        }
                        attributes[key]['options'][value] = label;
                    });
                } else {
                    attributes[key]['eval'] = evals;
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
            if (row.find('.extra').val()) {
                attributes[key] =  { ...attributes[key], ...JSON.parse(row.find('.extra').val()) };
            }
        });
        //console.log(attributes);
        let attr_json = JSON.stringify(attributes, null, 4);
        //console.log(attr_json);
        attr_attributes.val(attr_json);
        //_block_attributes.codemirror.refresh()
        //_block_attributes.destroy();
        attr_attributes.next('.CodeMirror').remove();
        _block_attributes = wp.codeEditor.initialize(attr_attributes, editorSettings);
        //attr_attributes.trigger('change');
    }
    if (attr_editor.length) {
        console.log('init');
        attr_editor.data('row', attr_editor.find('.repeat_attrs').html());
        //console.log(attr_editor.data('row'));
        attr_editor.find('.repeat_attr').remove();
        
        
        _block_attributes.codemirror.on('change', function(){
            // destroy and rebuild 
            console.log('codemirror change');
            _block_attributes.codemirror.save();
            update_block_attributes_editor();
        });
        attr_attributes.on('change', function(){
            console.log('textarea change');
            // destroy and rebuild 
            update_block_attributes_editor();
        });
        
        attr_editor.on('click', '.attr_add', function(){
            console.log('add');
            attr_editor.find('.repeat_attrs').append(attr_editor.data('row'));
        });
        
        attr_editor.on('click', '.repeat_attr .button', function(){
            console.log('update');
            setTimeout(function() {
                update_block_attributes();
            }, 100);
        });
        
        attr_editor.on('click', '.attr_toggle, .attr_name', function(){
            console.log('toggle');
            jQuery(this).closest('.attr_ops').siblings('.attr_data').toggle();
        });
        
        attr_editor.on('click', '.attr_up', function(){
            console.log('up');
            let row = jQuery(this).closest('.repeat_attr');
            if (row.eq()) {
                    row.insertBefore(row.prev());
            }
        });
        
        attr_editor.on('click', '.attr_down', function(){
            console.log('down');
            let row = jQuery(this).closest('.repeat_attr');
            if (!row.is(':last-child')) {
                row.insertAfter(row.next());
            }
        });
        
        attr_editor.on('click', '.attr_clone', function(){
            console.log('clone');
            let row = jQuery(this).closest('.repeat_attr');
            //row.clone().appendTo(row.parent());
            let clone = row.clone();
            clone.insertAfter(row);
            clone.find('.attr_toggle').trigger('click');
        });
        
        attr_editor.on('click', '.attr_remove', function(){
            console.log('remove');
            let row = jQuery(this).closest('.repeat_attr');
            row.remove();
        });
        
        
        attr_editor.on('change', '.repeat_attr input, .repeat_attr select, .repeat_attr textarea', function(){
            console.log('update');
            setTimeout(function() {
                update_block_attributes();
            }, 100);
        });
        
        update_block_attributes_editor()
    }
});