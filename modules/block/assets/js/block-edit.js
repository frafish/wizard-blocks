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

    var editorSettings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};

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
    var _block_viewScript = wp.codeEditor.initialize(jQuery('#_block_viewScript'), editorSettings);

    var editorSettings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};
    editorSettings.codemirror = _.extend(
            {},
            editorSettings.codemirror,
            {
                indentUnit: 2,
                tabSize: 2,
                mode: 'application/ld+json'
            }
    );
    var _block_render = wp.codeEditor.initialize(jQuery('#_block_attributes'), editorSettings);

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
    function update_block_attributes() {
        let attributes = [];
        attr_editor.find('.repeat_attr').each(function(index, row){
            
        });
        console.log(attributes);
    }
    if (attr_editor.length) {
        console.log('init');
        attr_editor.data('row', attr_editor.find('.repeat_attrs').html());
        console.log(attr_editor.data('row'));
        attr_editor.find('.repeat_attr').remove();
        
        jQuery('#_block_attributes').on('change', function(){
           // destroy and rebuild 
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
        
        
        attr_editor.on('change', '.repeat_attr input, .repeat_attr select', function(){
            console.log('update');
            setTimeout(function() {
                update_block_attributes();
            }, 100);
        });
        
        var attributes = JSON.parse(jQuery('#_block_attributes').val());
        console.log(attributes);
        var index = 0;
        jQuery.each(attributes, function(key, element){
            console.log(index);
            console.log(key);
            console.log(element);
            attr_editor.find('.attr_add').trigger('click');
            let row = attr_editor.find('.repeat_attr').eq(index);
            console.log(row);
            let title = element.title ? element.title : key;
            row.find('.attr_name').text(row.find('.attr_name').text().replace('attr_key', key).replace('attr_title', title));
            row.find('.attr_key').val(key);
            row.find('.attr_type').val(element.type);
            if (element.default) {
                row.find('.attr_default').val(element.default);
            }
            index++;
        });
    }
});