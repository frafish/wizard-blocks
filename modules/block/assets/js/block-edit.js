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

});