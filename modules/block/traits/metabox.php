<?php

namespace WizardBlocks\Modules\Block\Traits;

use WizardBlocks\Core\Utils;

trait Metabox {

    // block JSON properties
    public static $fields = [
        "\$schema",
        "apiVersion",
        "name",
        "title",
        "category",
        "keywords",
        "parent",
        "ancestor",
        "icon",
        "description",
        "version",
        "textdomain",
        "attributes",
        "viewScript",
        "editorScript",
        "editorScriptModule",
        "editorStyle",
        "script",
        "style",
        "viewStyle",
        "render",
        "provides",
        "usesContext",
        "supports",
        "providesContext"
    ];
    //https://developer.wordpress.org/block-editor/reference-guides/block-api/block-api-versions/
    public static $apiVersions = [
        1,
        2,
        3
    ];
    // https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#category
    public static $categories = [
        'text',
        'media',
        'design',
        'widgets',
        'theme',
        'embed'
    ];
    // https://developer.wordpress.org/block-editor/reference-guides/block-api/block-supports/
    public static $supports = [
        'anchor' => false,
        'align' => false,
        'alignWide' => true,
        'ariaLabel' => false,
        'className' => true,
        'color.background' => true,
        'color.gradients' => false,
        'color.link' => false,
        'color.text' => true,
        'customClassName' => false,
        'dimensions.aspectRatio' => true,
        'dimensions.minHeight' => false,
        'filter.duotone' => false,
        'html' => true,
        'inserter' => true,
        'interactivity.clientNavigation' => false,
        'interactivity.interactive' => false,
        'layout.allowSwitching' => false,
        'layout.allowEditing' => true,
        'layout.allowInheriting' => true,
        'layout.allowSizingOnChildren' => false,
        'layout.allowVerticalAlignment' => true,
        'layout.allowJustification' => true,
        'layout.allowOrientation' => true,
        'layout.allowCustomContentAndWideSize' => true,
        'multiple' => true,
        'reusable' => true,
        'lock' => true,
        'position.sticky' => false,
        'spacing.margin' => false,
        'spacing.padding' => false,
        'spacing.blockGap' => false,
        'typography.fontSize' => false,
        'typography.lineHeight' => false
    ];
    //https://developer.wordpress.org/block-editor/reference-guides/block-api/block-attributes/#type-validation
    public static $attributes_type = [
        'string' => 'String',
        'boolean' => 'Boolean',
        'number' => 'Number',
        'integer' => 'Integer',
        'array' => 'Array',
        'null' => 'Null',
        'object' => 'Object',
    ];
    
    //https://make.wordpress.org/core/2023/03/07/introduction-of-block-inspector-tabs/
    //https://developer.wordpress.org/news/2023/06/02/using-block-inspector-sidebar-groups/
    public static $attributes_position = [
        "default" => 'Settings Sidebar - Panel Content',
        //"settings" => 'Settings Sidebar',
        //"color" => 'Settings Sidebar - Panel Content',
        //"typography" => 'Settings Sidebar - Panel Typography',
        //"dimensions" => 'Settings Sidebar - Panel Dimensions',
        //"border" => 'Settings Sidebar - Panel Border',
        "advanced" => 'Settings Sidebar - Panel Advanced',
        //"position" => 'Settings Sidebar - Panel Position',
        "style" => 'Settings Sidebar - Panel Style',
        //"list" => 'Settings Sidebar - Children List',
        "toolbar" => 'Block Toolbar',
        "block" => 'Block Content canvas'
    ];
    
    
    //https://developer.wordpress.org/block-editor/reference-guides/components/
    //https://wp-gb.com/
    public static $attributes_component = [
        'CheckboxControl' => 'Checkbox',
        'ColorPicker' => 'Color',
        'DatePicker' => 'Date',
        'DateTimePicker' => 'DateTime',
        //'InputControl' => 'Email',
        'Heading' => 'Heading',
        'InputControl' => 'InputControl',
        'MediaUpload' => 'Media',
        'NumberControl' => 'Number',
        'RadioControl' => 'Radio',
        'RadioGroup' => 'RadioGroup',
        'RichText' => 'RichText',
        'SelectControl' => 'Select',
        'Separator' => 'Separator',
        //'InputControl' => 'Tel',
        'TextareaControl' => 'TextArea',
        'TextControl' => 'Text',
        'TimePicker' => 'Time',
        'ToggleControl' => 'Toggle',
            //'InputControl' => 'URL',
    ];
    //https://www.w3schools.com/html/html_form_input_types.asp
    public static $attributes_input_type = [
        'text',
        //'button',
        'checkbox',
        'color',
        'date',
        //'datetime-local',
        'email',
        //'file',
        //'hidden',
        //'image',
        'month',
        'number',
        'password',
        //'radio',
        'range',
        //'reset',
        //'search',
        //'submit',
        'tel',
        'time',
        'url',
        'week',
    ];
    //https://developer.wordpress.org/block-editor/reference-guides/block-api/block-attributes/#value-source
    public static $attributes_source = [
        'attribute' => 'Attribute',
        'text' => 'Text',
        'html' => 'HTML',
        'query' => 'Query',
        'meta' => 'Meta (deprecated)'
    ];

// register meta box
    public function meta_fields_add_meta_box() {
        add_meta_box(
                'render_meta_box',
                esc_html__('Content', 'wizard-blocks'),
                [$this, 'meta_fields_build_render_callback'],
                'block',
                //'side',
                //'default'
        );

        add_meta_box(
                'css_meta_box',
                esc_html__('CSS Assets', 'wizard-blocks'),
                [$this, 'meta_fields_build_css_callback'],
                'block'
        );
        add_meta_box(
                'js_meta_box',
                esc_html__('JS Assets', 'wizard-blocks'),
                [$this, 'meta_fields_build_js_callback'],
                'block'
        );
        add_meta_box(
                'attributes_meta_box',
                esc_html__('Attributes', 'wizard-blocks'),
                [$this, 'meta_fields_build_attributes_callback'],
                'block'
        );
        add_meta_box(
                'meta_fields_side_meta_box',
                esc_html__('Info', 'wizard-blocks'),
                [$this, 'meta_fields_build_meta_box_side_callback'],
                'block',
                'side',
                'default'
        );

        if ($this->is_block_edit()) {
            $this->enqueue_style('block-edit', 'assets/css/block-edit.css');
            $this->enqueue_script('block-edit', 'assets/js/block-edit.js');
            $php = wp_enqueue_code_editor(array('type' => 'text/html'));
            $css = wp_enqueue_code_editor(array('type' => 'text/css'));
            $js = wp_enqueue_code_editor(array('type' => 'application/javascript'));
            $json = wp_enqueue_code_editor(array('type' => 'application/json'));
            wp_enqueue_script('wp-theme-plugin-editor');
            wp_enqueue_style('wp-codemirror');
            wp_enqueue_media();
        }
    }

    public function meta_fields_build_render_callback($post, $metabox) {
        wp_nonce_field('meta_fields_save_meta_box_data', 'meta_fields_meta_box_nonce');

        $json = $post ? $this->get_json_data($post->post_name) : [];

        $render = '';
        if ($post) {
            $file = 'render.php';
            if (!empty($json['render'])) {
                $file = str_replace('file:', '', $json['render']);
                $file = str_replace('/\\', DIRECTORY_SEPARATOR, $file);
                $file = str_replace('/', DIRECTORY_SEPARATOR, $file);
                $file = str_replace(' ', '', $file);
                $file = str_replace(DIRECTORY_SEPARATOR . '.' . DIRECTORY_SEPARATOR, '', $file); // \.\
            }

            $render_file = $this->get_ensure_blocks_dir($post->post_name) . $file;
            //var_dump($render_file);
            if (file_exists($render_file)) {
                $render = file_get_contents($render_file);
            }
        }
        ?>
        <div class="inside">
            <h3><label for="_block_render_file"><?php _e('Render', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#render"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p class="hint"><i><?php _e('PHP file to use when rendering the block type on the server to show on the front end.', 'wizard-blocks'); ?></i></p>
            <p><textarea id="_block_render_file" name="_block_render_file" placeholder="Hello world!"><?php echo $render; ?></textarea></p>	           
            <div class="notice inline notice-primary notice-alt" style="display: block; padding: 20px;">
                <span class="dashicons dashicons-info"></span> <?php _e('The following variables are exposed to the file:', 'wizard-blocks'); ?>
                <ul>
                    <li><b>$attributes</b> (array): <?php _e('The array of attributes for this block.', 'wizard-blocks'); ?></li>
                    <li><b>$content</b> (string): <?php _e('The rendered block default output. ie. <code>&lt;InnerBlocks.Content /&gt;</code>.', 'wizard-blocks'); ?></li>
                    <li><b>$block</b> (WP_Block): <?php _e('The instance of the WP_Block class that represents the block being rendered.', 'wizard-blocks'); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }

    // build meta box
    public function meta_fields_build_css_callback($post, $metabox) {
        $plugin_name = $this->get_plugin_slug();
        $basepath = '';
        if ($post) {

            $json = $post ? $this->get_json_data($post->post_name) : [];
            $basepath = $this->get_ensure_blocks_dir($post->post_name);
        }
        
        // Get WordPress' media upload URL
        $upload_link = esc_url(get_upload_iframe_src('image', $post->ID));
        ?>
        <div class="inside">
            
            <h3><label for="_block_style"><?php _e('Style', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#style"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p class="hint"><i><?php _e('Block type frontend and editor styles definition. They will be enqueued both in the editor and when viewing the content on the front of the site.', 'wizard-blocks'); ?></i></p>
            <p><textarea id="_block_style_file" name="_block_style_file"><?php echo $this->get_asset_file_contents('style', $basepath); ?></textarea></p>	
            <p class="d-flex assets">
                <input type="text" id="_block_style" name="_block_style" value="<?php echo empty($json['style']) ? '' : Utils::implode($json['style']); ?>" placeholder="file:./style.css">
                <a title="<?php _e('Upload new asset') ?>" class="dashicons-before dashicons-plus button button-primary upload-assets" href="<?php echo $upload_link ?>" target="_blank"></a>
            </p>
            <hr>

            <h3><label for="_block_viewStyle"><?php _e('View Style', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-style"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p class="hint"><i><?php _e('Block type frontend styles definition. They will be enqueued only when viewing the content on the front of the site.', 'wizard-blocks'); ?></i></p>
            <p><textarea id="_block_viewStyle_file" name="_block_viewStyle_file"><?php echo $this->get_asset_file_contents('viewStyle', $basepath); ?></textarea></p>
            <p class="d-flex assets">
                <input type="text" id="_block_viewStyle" name="_block_viewStyle" value="<?php echo empty($json['viewStyle']) ? '' : Utils::implode($json['viewStyle']); ?>" placeholder="file:./viewStyle.css">
                <a title="<?php _e('Upload new asset') ?>" class="dashicons-before dashicons-plus button button-primary upload-assets" href="<?php echo $upload_link ?>" target="_blank"></a>
            </p>
            
            <hr>

            <h3><label for="_block_editorStyle"><?php _e('Editor Style', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#editor-style"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p class="hint"><i><?php _e('Block type editor styles definition. They will only be enqueued in the context of the editor.', 'wizard-blocks'); ?></i></p>
            <p><textarea id="_block_editorStyle_file" name="_block_editorStyle_file"><?php echo $this->get_asset_file_contents('editorStyle', $basepath); ?></textarea></p>	
            <p class="d-flex assets">
                <input type="text" id="_block_editorStyle" name="_block_editorStyle" value="<?php echo empty($json['editorStyle']) ? '' : Utils::implode($json['editorStyle']); ?>" placeholder="file:./editorStyle.css">
                <a title="<?php _e('Upload new asset') ?>" class="dashicons-before dashicons-plus button button-primary upload-assets" href="<?php echo $upload_link ?>" target="_blank"></a>
            </p>
            
        </div>
        <?php
    }

    public function meta_fields_build_js_callback($post, $metabox) {
        $plugin_name = $this->get_plugin_slug();
        $basepath = '';
        if ($post) {

            $json = $post ? $this->get_json_data($post->post_name) : [];
            $basepath = $this->get_ensure_blocks_dir($post->post_name);

            //$is_editor_script_generated = strpos($this->get_asset_file_contents('editorScript', $basepath), 'generated by ' . $plugin_name);
            //var_dump($is_editor_script_generated);
        }
        
        // Get WordPress' media upload URL
        $upload_link = esc_url(get_upload_iframe_src('image', $post->ID));
        ?>
        <div class="inside">

            <h3><label for="_block_editorScript"><?php _e('Editor Script', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#editor-script"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p class="hint"><i><?php _e('Block type editor scripts definition. They will only be enqueued in the context of the editor.', 'wizard-blocks'); ?></i></p>
            <p><textarea<?php echo (false) ? ' style="background-color: white; cursor: not-allowed;" rows="15" readonly' : ''; ?> id="_block_editorScript_file" name="_block_editorScript_file"><?php echo $this->get_asset_file_contents('editorScript', $basepath); ?></textarea></p>
            <p class="d-flex assets">
                <input type="text" id="_block_editorScript" name="_block_editorScript" value="<?php echo empty($json['editorScript']) ? '' : Utils::implode($json['editorScript']); ?>" placeholder="file:./editorScript.js">
                <a title="<?php _e('Upload new asset') ?>" class="dashicons-before dashicons-plus button button-primary upload-assets" href="<?php echo $upload_link ?>" target="_blank"></a>
            </p>
            
            <hr>

            <h3><label for="_block_script"><?php _e('Script', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#script"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p class="hint"><i><?php _e('Block type frontend and editor scripts definition. They will be enqueued both in the editor and when viewing the content on the front of the site.', 'wizard-blocks'); ?></i></p>
            <p><textarea id="_block_script_file" name="_block_script_file"><?php echo $this->get_asset_file_contents('script', $basepath); ?></textarea></p>
            <p class="d-flex assets">
                <input type="text" id="_block_script" name="_block_script" value="<?php echo empty($json['script']) ? '' : Utils::implode($json['script']); ?>" placeholder="file:./script.js">
                <a title="<?php _e('Upload new asset') ?>" class="dashicons-before dashicons-plus button button-primary upload-assets" href="<?php echo $upload_link ?>" target="_blank"></a>
            </p>
            
            <hr>

            <h3><label for="_block_viewScript"><?php _e('View Script', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-script"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p class="hint"><i><?php _e('Block type frontend scripts definition. They will be enqueued only when viewing the content on the front of the site.', 'wizard-blocks'); ?></i></p>
            <p><textarea id="_block_viewScript_file" name="_block_viewScript_file"><?php echo $this->get_asset_file_contents('viewScript', $basepath); ?></textarea></p>
            <p class="d-flex assets">
                <input type="text" id="_block_viewScript" name="_block_viewScript" value="<?php echo empty($json['viewScript']) ? '' : Utils::implode($json['viewScript']); ?>" placeholder="file:./viewScript.js">
                <a title="<?php _e('Upload new asset') ?>" class="dashicons-before dashicons-plus button button-primary upload-assets" href="<?php echo $upload_link ?>" target="_blank"></a>
            </p>
            
            <hr>

            <h3><label for="_block_viewScriptModule"><?php _e('View Script Module', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-script-module"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p class="hint"><i><?php _e('Block type frontend script module definition. They will be enqueued only when viewing the content on the front of the site.', 'wizard-blocks'); ?></i></p>
            <p><textarea id="_block_viewScriptModule_file" name="_block_viewScriptModule_file"><?php echo $this->get_asset_file_contents('viewScriptModule', $basepath); ?></textarea></p>
            <p class="d-flex assets">
                <input type="text" id="_block_viewScriptModule" name="_block_viewScriptModule" value="<?php echo empty($json['viewScriptModule']) ? '' : Utils::implode($json['viewScriptModule']); ?>" placeholder="file:./viewScriptModule.js">
                <a title="<?php _e('Upload new asset') ?>" class="dashicons-before dashicons-plus button button-primary upload-assets" href="<?php echo $upload_link ?>" target="_blank"></a>
            </p>
            
        </div>
        <?php
    }

    public function meta_fields_build_attributes_callback($post, $metabox) {
        //wp_nonce_field('meta_fields_save_meta_box_side_data', 'meta_fields_meta_box_nonce');

        $json = $post ? $this->get_json_data($post->post_name) : [];

        //$style = get_post_meta($post->ID, '_meta_fields_book_title', true);
        $attributes = '';
        if (empty($json['attributes'])) {
            $attributes = get_post_meta($post->ID, '_transient_block_attributes', true);
            if ($attributes) {
                //warn
                $this->_notice(__('Attributes are not saved! Please <a href="#attributes">fix them</a> and resave block.'), 'danger error');
                $this->_notice(esc_html__('Please verify that Attributes is a valid JSON data!'), 'danger error inline');
            }
        }
        ?>
        <div class="inside">

            <h3 id="attributes"><label for="_block_attributes"><?php _e('Attributes', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-attributes/"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><textarea id="_block_attributes" name="_block_attributes"><?php echo empty($json['attributes']) ? $attributes : wp_json_encode($json['attributes'], JSON_PRETTY_PRINT); ?></textarea></p>	

            <div id="_block_attributes_editor">
                <div class="repeat_attrs">
                    <div class="repeat_attr">
                        <div class="attr_ops">
                            <span class="attr_name dashicons-before dashicons-editor-expand"> <?php _e('Add an attribute KEY', 'wizard-blocks'); ?></span>
                            <abbr title="<?php _e('Remove', 'wizard-blocks'); ?>" class="button button-danger attr_remove pull-right"><span class="dashicons dashicons-trash"></span></abbr>
                            <abbr title="<?php _e('Up', 'wizard-blocks'); ?>" class="button attr_up pull-right"><span class="dashicons dashicons-arrow-up-alt"></span></abbr>
                            <abbr title="<?php _e('Down', 'wizard-blocks'); ?>" class="button attr_down pull-right"><span class="dashicons dashicons-arrow-down-alt"></span></abbr>
                            <abbr title="<?php _e('Clone', 'wizard-blocks'); ?>" class="button attr_clone pull-right"><span class="dashicons dashicons-admin-page"></span></abbr>
                        </div>
                        <div class="attr_data">
                            <label for="key"><?php _e('Key', 'wizard-blocks'); ?>*: <input type="text" class="key"></label>
                            <label for="label"><?php _e('Label', 'wizard-blocks'); ?>: <input type="text" class="label"></label>
                            <label for="type"><?php _e('Type', 'wizard-blocks'); ?> <a class="dashicons-before dashicons-info-outline" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-attributes/#type-validation" target="_blank"></a>: 
                                <select class="type">
                                    <?php foreach (self::$attributes_type as $type => $label) { ?>
                                        <option value="<?php echo $type ?>"><?php echo $label ?></option>
                                    <?php } ?>
                                </select></label>
                            <label for="component"><?php _e('Component', 'wizard-blocks'); ?> <a class="dashicons-before dashicons-info-outline" href="https://developer.wordpress.org/block-editor/reference-guides/components/" target="_blank"></a>: 
                                <select class="component">
                                    <option value=""><?php _e('Auto', 'wizard-blocks'); ?></option>
                                    <?php foreach (self::$attributes_component as $type => $label) { ?>
                                        <option value="<?php echo $type ?>"><?php echo $label ?></option>
                                    <?php } ?>
                                </select></label>
                            <label for="inputType"><?php _e('Input Type', 'wizard-blocks'); ?>: 
                                <select class="inputType">
                                <?php foreach (self::$attributes_input_type as $type => $label) { ?>
                                    <option value="<?php echo $label ?>"><?php echo ucfirst($label); ?></option>
                                <?php } ?>
                                </select></label>
                            <label for="position"><?php _e('Position', 'wizard-blocks'); ?> <a class="dashicons-before dashicons-info-outline" href="https://developer.wordpress.org/block-editor/getting-started/fundamentals/block-in-the-editor/#block-controls-block-toolbar-and-settings-sidebar" target="_blank"></a>: <select class="position">
                                    <?php foreach (self::$attributes_position as $postion => $label) { ?>
                                        <option value="<?php echo $postion ?>"><?php echo $label ?></option>
                                    <?php } ?>
                                </select></label>
                            <label for="multiple"><?php _e('Multiple', 'wizard-blocks'); ?>: <select class="multiple">
                                    <option value="false"><?php _e('False', 'wizard-blocks'); ?></option>
                                    <option value="true"><?php _e('True', 'wizard-blocks'); ?></option>                                
                                </select></label>
                            <label for="options"><?php _e('Options', 'wizard-blocks'); ?>: <textarea class="options" placeholder="FF00FF|Magenta"></textarea></label>
                            <label for="default"><?php _e('Default', 'wizard-blocks'); ?>: <input type="text" class="default"></label>
                            <label for="source"><?php _e('Source', 'wizard-blocks'); ?>: <select class="source">
                                    <option value=""><?php _e('No value', 'wizard-blocks'); ?></option>
                                    <?php foreach (self::$attributes_source as $type => $label) { ?>
                                        <option value="<?php echo $type ?>"><?php echo $label ?></option>
                                    <?php } ?>
                                </select></label>
                            <label for="selector"><?php _e('Selector', 'wizard-blocks'); ?>: <input type="text" class="selector"></label>
                            <label for="attribute"><?php _e('Attribute', 'wizard-blocks'); ?>: <input type="text" class="attribute"></label>
                            <label for="label"><?php _e('Help', 'wizard-blocks'); ?>: <input type="text" class="help"></label>
                            <label for="extra"><?php _e('Extra', 'wizard-blocks'); ?>: <textarea class="extra" placeholder='{ "var": "value" }'></textarea></label>
                        </div>
                    </div>
                </div>
                <span class="button button-primary attr_add">Add</span>
            </div>
        </div>
        <?php
    }

    public function meta_fields_build_meta_box_side_callback($post, $metabox) {
        //wp_nonce_field('meta_fields_save_meta_box_side_data', 'meta_fields_meta_box_nonce');

        $json = $post ? $this->get_json_data($post->post_name) : [];

        //$style = get_post_meta($post->ID, '_meta_fields_book_title', true);

        $icons = [];
        //Get an instance of WP_Scripts or create new;
        $wp_styles = wp_styles();
        //Get the script by registered handler name
        $style = $wp_styles->registered['dashicons'];
        $dashicons = ABSPATH . $style->src;
        $dashicons = str_replace('//', DIRECTORY_SEPARATOR, $dashicons);
        $dashicons = str_replace('/', DIRECTORY_SEPARATOR, $dashicons);
        if (file_exists($dashicons)) {
            $css = file_get_contents($dashicons);
            $tmp = explode('.dashicons-', $css);
            foreach ($tmp as $key => $piece) {
                if ($key) {
                    list($icon, $more) = explode(':', $piece, 2);
                    $icons[$icon] = $icon;
                }
            }
        }
        unset($icons['before']);
        ?>
        <div class="inside">

            <h3><label for="_block_apiVersion"><?php _e('apiVersion', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-api-versions/"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><select type="text" id="_block_apiVersion" name="_block_apiVersion"><?php
                    if (empty($json['apiVersion']))
                        $json['apiVersion'] = 3;
                    foreach (self::$apiVersions as $apiVersion) {
                        $selected = (!empty($json['apiVersion']) && $json['apiVersion'] == $apiVersion) ? ' selected' : '';
                        echo '<option value="' . $apiVersion . '"' . $selected . '>' . $apiVersion . '</option>';
                    }
                    ?></select></p>	           

        <?php if (!empty($post->post_name)) { ?>
                <h3><label for="_block_name"><?php _e('Name', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#name"><span class="dashicons dashicons-info-outline"></span></a></h3>
                <p><?php echo $this->get_block_textdomain($json); ?>/<input style="width: 60%;" type="text" id="_block_name" name="_block_name" value="<?php echo $post->post_name; ?>" /></p>
                <?php } ?>

            <h3><label for="_block_version"><?php _e('Version', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#version"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><input type="text" id="_block_version" name="_block_version" placeholder="1.0.1" value="<?php
              if (!empty($json['version'])) {
                  echo $json['version'];
              }
                ?>" /></p>	           

            <h3><label for="_block_icon"><?php _e('Icon', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#icon"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><select id="_block_icon" name="_block_icon"><option value=""><?php _e('Custom', 'wizard-blocks'); ?></option><?php
                    if (empty($json['icon']))
                        $json['icon'] = 'smiley';
                    $is_dash = false;
                    foreach ($icons as $icon) {
                        $selected = '';
                        if (!empty($json['icon']) && $json['icon'] == $icon) {
                            $selected = ' selected';
                            $is_dash = true;
                        }
                        echo '<option value="' . $icon . '"' . $selected . '>' . $icon . '</option>';
                    }
                    ?></select>
                <span id="icon_svg">
                    <textarea id="_block_icon_svg" name="_block_icon_svg" placeholder="<svg ...>...</svg>"><?php if (!empty($json['icon']) && !$is_dash) echo $json['icon']; ?></textarea>
            <?php if (!empty($json['icon'])) { ?> 
                        <b><?php _e('Current', 'wizard-blocks'); ?>:</b><br>
                <?php echo $is_dash ? '<span class="dashicons dashicons-' . $json['icon'] . '"></span>' : $json['icon']; ?>
            <?php } ?> 
                </span>
            </p>	
            <?php
            $this->enqueue_style('select2', 'assets/lib/select2/select2.min.css');
            $this->enqueue_script('select2', 'assets/lib/select2/select2.min.js', array('jquery'));
            $block_categories = get_default_block_categories();
            $block_categories = apply_filters('block_categories_all', $block_categories);
            //var_dump($block_categories);
            ?>
            <h3><label for="_block_category"><?php _e('Category', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#category"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><select type="text" id="_block_category" name="_block_category"><?php
            foreach ($block_categories as $cat) {
                $selected = (!empty($json['category']) && $json['category'] == $cat['slug']) ? ' selected' : '';
                echo '<option value="' . $cat['slug'] . '"' . $selected . '>' . $cat['title'] . '</option>';
            }
            ?></select></p>

            <h3><label for="_block_keywords"><?php _e('Keywords', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#keywords"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><input type="text" id="_block_keywords" name="_block_keywords" placeholder="alert, message" value="<?php
              if (!empty($json['keywords'])) {
                  echo is_array($json['keywords']) ? implode(', ', $json['keywords']) : $json['keywords'];
              }
            ?>" /></p>	           

            <h3><label for="_block_parent"><?php _e('Parent', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#parent"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><input type="text" id="_block_parent" name="_block_parent" placeholder="core/group"  value="<?php
              if (!empty($json['parent'])) {
                  echo is_array($json['parent']) ? implode(', ', $json['parent']) : $json['parent'];
              }
            ?>" /></p>	           

            <h3><label for="_block_ancestor"><?php _e('Ancestor', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#ancestor"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><input type="text" id="_block_ancestor" name="_block_ancestor" placeholder="my-block/product"  value="<?php
              if (!empty($json['ancestor'])) {
                  echo is_array($json['ancestor']) ? implode(', ', $json['ancestor']) : $json['ancestor'];
              }
            ?>" /></p>	

            <h3><label for="_block_allowedBlocks"><?php _e('Allowed Blocks', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#allowed-blocks"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><input type="text" id="_block_allowedBlocks" name="_block_allowedBlocks" placeholder="my-block/product, my-block/title"  value="<?php
              if (!empty($json['allowedBlocks'])) {
                  echo is_array($json['allowedBlocks']) ? implode(', ', $json['allowedBlocks']) : $json['allowedBlocks'];
              }
            ?>" /></p>

            <h3><label for="_block_blockHooks"><?php _e('Hooks', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#block-hooks"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><textarea id="_block_blockHooks" name="_block_blockHooks" placeholder='{ "my-plugin/banner": "after" }'><?php
            if (!empty($json['blockHooks'])) {
                echo wp_json_encode($json['blockHooks'], JSON_PRETTY_PRINT);
            }
            ?></textarea></p>

            <h3><label for="_block_providesContext"><?php _e('providesContext', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#provides-context"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><textarea id="_block_providesContext" name="_block_providesContext" placeholder='{ "my-plugin/recordId": "recordId" }'><?php
            if (!empty($json['providesContext'])) {
                echo wp_json_encode($json['providesContext'], JSON_PRETTY_PRINT);
            }
            ?></textarea></p>	

            <h3><label for="_block_usesContext"><?php _e('usesContext', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#uses-context"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><input type="text" id="_block_usesContext" name="_block_usesContext" placeholder="postId, postType" value="<?php
              if (!empty($json['usesContext'])) {
                  echo is_array($json['usesContext']) ? implode(', ', $json['usesContext']) : $json['usesContext'];
              }
            ?>" /></p>	           

            <h3><label for="_block_supports"><?php _e('Supports', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-supports/"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <div style="height: 180px; overflow: auto; border: 1px solid #eee; padding: 0 10px;">
        <?php
        $custom = [];
        foreach (self::$supports as $sup => $default) {
            ?>
                    <p>
                        <label for="_block_supports_<?php echo $sup; ?>"><b><?php echo $sup; ?></b></label><br>
                        <!-- <input type="checkbox" id="_block_supports_<?php echo $sup; ?>" name="_block_supports[<?php echo $sup; ?>]"<?php
                        if (!empty($json['supports']) && in_array($sup, $json['supports'])) {
                            echo ' checked';
                        }
                        ?>> <b><?php echo $sup; ?></b></label> -->
                        <?php
                        $value = $default;
                        if (!empty($json['supports'])) {
                            if (isset($json['supports'][$sup])) {
                                if (is_bool($json['supports'][$sup])) {
                                    $value = $json['supports'][$sup];
                                } else {
                                    $custom[$sup] = $value;
                                }
                            } else {
                                $tmp = explode('.', $sup);
                                if (count($tmp) > 2) {
                                    if (isset($json['supports'][reset($tmp)][end($tmp)])) {
                                        if (is_bool($json['supports'][reset($tmp)][end($tmp)])) {
                                            $value = $json['supports'][reset($tmp)][end($tmp)];
                                        } else {
                                            $custom[reset($tmp)][end($tmp)] = $value;
                                        }
                                    }
                                }
                            }
                        }
                        ?>
                        <input type="radio" id="_block_supports_<?php echo $sup; ?>_true" name="_block_supports[<?php echo $sup; ?>]" value="true"<?php
                        if ($value) {
                            echo ' checked';
                        }
                        ?>> <label for="_block_supports_<?php echo $sup; ?>_true"><?php echo 'True'; ?></label>
                        <input type="radio" id="_block_supports_<?php echo $sup; ?>_false" name="_block_supports[<?php echo $sup; ?>]" value="false"<?php
                    if (!$value == 'false') {
                        echo ' checked';
                    }
                    ?>> <label for="_block_supports_<?php echo $sup; ?>_false"><?php echo 'False'; ?></label>
                    </p>
            <?php } ?>	
            </div>
            <?php
            if (!empty($json['supports'])) {
                foreach ($json['supports'] as $sup => $support) {
                    if (!isset($custom[$sup]) && !isset(self::$supports[$sup])) {
                        $custom[$sup] = $support;
                    } else {
                        if (is_array($support)) {
                            foreach ($support as $sub => $suppo) {
                                if (!isset($custom[$sup][$sub]) && !isset(self::$supports[$sup . '.' . $sub])) {
                                    $custom[$sup][$sub] = $suppo;
                                }
                            }
                        }
                    }
                }
            }

            $custom_transien = get_post_meta($post->ID, '_transient_block_supports_custom', true);
            if ($custom_transien) {
                //warn
                $this->_notice(__('Custom Supports are not saved! Please <a href="#supports_custom">fix it</a> and resave block.'), 'danger error');
                $this->_notice(esc_html__('Please verify that Custom Supports is a valid JSON data!'), 'danger error inline');
            }
            $custom = empty($custom) ? $custom_transien : wp_json_encode($custom, JSON_PRETTY_PRINT);
            ?>
            <label id="supports_custom" for="_block_supports_custom"><b><?php _e('Supports custom values', 'wizard-blocks'); ?></b></label>
            <textarea rows="10" id="_block_supports_custom" name="_block_supports_custom" style="width: 100%;" placeholder='{ "spacing": { "margin": [ "top", "bottom" ] } }'><?php echo $custom; ?></textarea>

            <?php
            $extra = $json;
            foreach (self::$fields as $field) {
                if (isset($extra[$field])) {
                    unset($extra[$field]);
                }
            }
            $extra_transien = get_post_meta($post->ID, '_transient_block_extra', true);
            if ($extra_transien) {
                //warn
                $this->_notice(__('Extra are not saved! Please <a href="#extra">fix it</a> and resave block.'), 'danger error');
                $this->_notice(esc_html__('Please verify that Custom Supports is a valid JSON data!'), 'danger error inline');
            }
            $extra = empty($extra) ? $extra_transien : wp_json_encode($extra, JSON_PRETTY_PRINT);
            ?>
            <h3><label id="extra" for="_block_extra"><b><?php _e('Extra', 'wizard-blocks'); ?></b></label></h3>
            <textarea rows="10" id="_block_extra" name="_block_extra" style="width: 100%;"><?php echo $extra; ?></textarea>
        </div>
        <?php
    }
}
