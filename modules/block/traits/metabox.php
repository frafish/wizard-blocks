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
        "allowedBlocks",
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
        'background.backgroundImage' => false, // Enable background image control.
        'background.backgroundSize' => false, // Enable background image + size control.
        //'background.backgroundPosition' => false,
        'className' => true,
        'color.background' => true,
        'color.button' => false,
        'color.enableContrastChecker' => true,
        'color.gradients' => false,
        'color.heading' => false,
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
        'shadow' => false,
        'splitting' => false,
        'typography.fontSize' => false,
        'typography.lineHeight' => false,
        'typography.textAlign' => false
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
        '' => 'Eval',
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
        //"menu" => 'Block Toolbar - Dropdown Menu',
        "block" => 'Block Content canvas',
    ];
    //https://developer.wordpress.org/block-editor/reference-guides/components/
    //https://wp-gb.com/
    public static $attributes_component = [
        'AlignmentMatrixControl' => 'Alignment Matrix',
        'AnglePickerControl' => 'Angle',
        //'BaseControl' => 'Base',
        //'BorderBoxControl' => 'Border Box',
        //'BorderControl' => 'Border',
        //'BoxControl' => 'Box',
        'ButtonGroup' => 'Buttons',
        'CheckboxControl' => 'Checkbox',
        'ComboboxControl' => 'Combobox',
        'ColorPicker' => 'Color',
        'DatePicker' => 'Date',
        'DateTimePicker' => 'DateTime',
        //'Divider' => 'Divider',
        //'Dropdown' => 'Dropdown',
        'DuotonePicker' => 'Duotone',
        'ExternalLink' => 'External Link',
        //'FocalPointPicker' => 'Focal Point',
        'FontSizePicker' => '​Font Size',
        //'GradientPicker' => 'Gradient',
        'InnerBlocks' => 'Inner Blocks',
        //'InputControl' => 'Email', //experimental
        //'Heading' => 'Heading',
        'HorizontalRule' => '​Horizontal Rule',
        //'InputControl' => 'InputControl', //experimental
        'MediaUpload' => 'Media',
        //'NumberControl' => 'Number', //experimental
        'RadioControl' => 'Radio',
        //'RadioGroup' => 'RadioGroup', //experimental
        //'RichText' => 'RichText', //replaced by InnerBlocks, TODO
        'SelectControl' => 'Select',
        //'InputControl' => 'Tel', //experimental
        'TextareaControl' => 'TextArea',
        'TextControl' => 'Text',
        'TimePicker' => 'Time',
        'ToggleControl' => 'Toggle',
        //'InputControl' => 'URL', //experimental

            /*
             * TODO - wp.components
            AlignmentMatrixControl: 
            AnglePickerControl: 
            Animate: 
            Autocomplete:
            BaseControl: 
            BlockQuotation:
            BorderBoxControl:
            BorderControl: 
            BoxControl: 
            Button: 
            ButtonGroup:
            Card: 
            CardBody:
            CardDivider:
            CardFooter: 
            CardHeader: 
            CardMedia: 
            CheckboxControl:
            Circle: 
            ClipboardButton:
            ColorIndicator: 
            ColorPalette: 
            ColorPicker:
            ComboboxControl:
            Composite: 
            CustomGradientPicker: 
            CustomSelectControl: 
            Dashicon: 
            DatePicker:
            DateTimePicker:
            Disabled: 
            Draggable:
            DropZone: 
            DropZoneProvider:
            Dropdown: 
            DropdownMenu:
            DuotonePicker:
            DuotoneSwatch:
            ExternalLink: 
            Fill: 
            Flex: 
            FlexBlock:
            FlexItem: 
            FocalPointPicker:
            FocusReturnProvider: 
            FocusableIframe: 
            ​FontSizePicker: 
            ​FormFileUpload: 
            FormToggle: 
            FormTokenField:
            G: 
            GradientPicker:
            Guide: 
            GuidePage: 
            ​HorizontalRule: 
            Icon: 
            ​IconButton: 
            ​IsolatedEventContainer: 
            ​KeyboardShortcuts: 
            ​Line: 
            ​MenuGroup: 
            ​MenuItem: 
            ​MenuItemsChoice: 
            ​Modal: 
            ​NavigableMenu: 
            ​Navigator: 
            ​Notice: 
            ​NoticeList: 
            ​Panel: 
            ​PanelBody: 
            ​PanelHeader: 
            ​PanelRow: 
            ​Path: 
            ​Placeholder: 
            ​Polygon: 
            ​Popover: 
            ​ProgressBar: 
            ​QueryControls: 
            ​RadioControl: 
            ​RangeControl: 
            ​Rect: 
            ​ResizableBox: 
            ​ResponsiveWrapper: 
            ​SVG: 
            ​SandBox: 
            ​ScrollLock: 
            ​SearchControl: 
            ​SelectControl: 
            ​Slot: 
            ​SlotFillProvider: 
            Snackbar: 
            ​SnackbarList: 
            ​Spinner: 
            ​TabPanel: 
            ​TabbableContainer: 
            ​TextControl: 
            ​TextHighlight: 
            ​TextareaControl: 
            ​TimePicker: 
            ​Tip: 
            ​ToggleControl: 
            ​Toolbar: 
            ​ToolbarButton: 
            ​ToolbarDropdownMenu: 
            ​ToolbarGroup: 
            ​ToolbarItem: 
            ​Tooltip: 
            ​TreeSelect: 
            ​VisuallyHidden: 
             */
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

            add_action('post_submitbox_start', function ($post) {
                if ($post && $post->post_name) {
                    $json = $this->get_json_data($post->post_name);
                    //var_dump($json);
                    echo '<div id="export-action" style="float: left; margin-right: 5px;"><a class="button button-secondary button-large" target="_blank" href="' . esc_url($this->get_action_url('action=download&block='.$this->get_block_textdomain($json).'/' . $post->post_name)) . '">' . esc_html__('Export', 'wizard-blocks') . '</a></div>';
                    
                    //$revisione = $this->get_block_revision();
                    /*$revisions_url = wp_get_post_revisions_url($post->ID);
                    if ($revisions_url) {
                        echo '<div id="revision-action" style="float: left; margin-right: 5px;"><a class="button button-secondary button-large" href="' . esc_url($revisions_url) . '">' . esc_html__('Revisions', 'wizard-blocks') . '</a></div>';
                    }*/
                    
                    echo '<br style="clear:both;">';
                }
            });
        }
    }

    public function meta_fields_build_render_callback($post, $metabox) {
        wp_nonce_field('meta_fields_save_meta_box_data', 'meta_fields_meta_box_nonce');

        $json = $post ? $this->get_json_data($post->post_name) : [];
        //var_dump($post->post_name);
        //var_dump($json);

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

            $render_file = $this->get_blocks_dir($post->post_name) . DIRECTORY_SEPARATOR . $file;
            //var_dump($render_file);
            if (file_exists($render_file)) {
                $render = file_get_contents($render_file);
            }
        }
        //$render_safe = $render;
        ?>
        <div class="inside">
            <h3><label for="_block_render_file"><?php esc_attr_e('Render', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#render"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p class="hint"><i><?php esc_attr_e('PHP file to use when rendering the block type on the server to show on the front end.', 'wizard-blocks'); ?></i></p>
            <p><textarea id="_block_render_file" name="_block_render_file" placeholder="Hello world!" class="wp-editor-area"><?php echo esc_textarea($render); ?></textarea></p>	           
            <div class="notice inline notice-primary notice-alt" style="display: block; padding: 20px;">
                <span class="dashicons dashicons-info"></span> <?php esc_attr_e('The following variables are exposed to the file:', 'wizard-blocks'); ?>
                <ul>
                    <li><b>$attributes</b> (array): <?php esc_attr_e('The array of attributes for this block.', 'wizard-blocks'); ?></li>
                    <li><b>$content</b> (string): <?php esc_attr_e('The rendered block default output. ie. <code>&lt;InnerBlocks.Content /&gt;</code>.', 'wizard-blocks'); ?></li>
                    <li><b>$block</b> (<a href="https://developer.wordpress.org/reference/classes/wp_block/" target="_blank">WP_Block</a>): <?php esc_attr_e('The instance of the WP_Block class that represents the block being rendered.', 'wizard-blocks'); ?></li>
                </ul>
            <?php
            $example = "&lt;p &lt;?php echo <a href='https://developer.wordpress.org/reference/functions/get_block_wrapper_attributes/' target='_blank'>get_block_wrapper_attributes</a>(); ?&gt&gt;<br> &lt;?php<br> echo <b>\$attributes</b>['acme']; <br> echo <b>\$content</b>; <br> echo <b>\$block</b>->blockName;<br>?&gt;<br>&lt;/p&gt;";
            //echo '<p ' . get_block_wrapper_attributes() . '><?php (empty($attributes['acme'])) ? '' : $content) . '</p>' 
            ?>
            <details>
                <summary class="cursor-pointer"><u><?php esc_attr_e('Render PHP code example', 'wizard-blocks'); ?>:</u></summary>
                <div>
                    <q style="padding: 10px; display: block; background-color: #dedede;"><i><?php echo $example; ?></i></q>
                    <span class="dashicons dashicons-welcome-learn-more"></span> <a href="https://github.com/WordPress/block-development-examples" target="_blank"><?php esc_attr_e('Find out more examples', 'wizard-blocks'); ?> &gt;&gt;</a>
                </div>
            </details>    
                
            </div>
            
        </div>
        <?php
    }
    
    public function assets_merge($assets, $default) {
        
        // add default
        if (!in_array($default, $assets)) {
            array_unshift($assets, $default);
        }
        
        // remove minified version
        $pieces = explode('.', $default);
        $min = array_pop($pieces);
        $min = implode('.', $pieces) . '.min.' . $min;
        //var_dump($min);
        if ($key = array_search($min, $assets)) {
            unset($assets[$key]);
        }
        //var_dump($assets);
        return $assets;
    }
    
    public function asset_form($json, $asset_file, $basepath, $post) {
        $default = $this->get_asset_default_file($json, $asset_file, $basepath); 
        $assets = $this->get_asset_files($json, $asset_file, $basepath);
        //var_dump($assets);
        if (count($assets) > 1 || (count($assets) == 1 && reset($assets) != $default)) { ?> 
        <nav class="nav-tab-wrapper wb-nav-tab-wrapper">
            <?php
            $assets = $this->assets_merge($assets, $default);
            foreach($assets as $key => $asset) { ?>
                <a href="#wb-<?php echo sanitize_title($asset); ?>" class="nav-tab wb-nav-tab<?php echo $key ? '' : ' nav-tab-active'; ?>"><?php echo basename($asset); ?></a>
            <?php }?>
        </nav>
        <?php } ?>

        <div class="wb-files">
            <?php
            $assets = $this->assets_merge($assets, $default);
            foreach ($assets as $key => $asset) { ?>
                <p class="wb-file<?php echo $key ? ' wb-hide' : ''; ?>" id="wb-<?php echo sanitize_title($asset); ?>">
                    <textarea class="wp-editor-area wb-asset-<?php echo sanitize_title(basename($asset)); ?> wb-codemirror-<?php echo self::$assets[$asset_file]; ?>" id="<?php echo ($asset == $default) ? '_block_'.$asset_file.'_file' : sanitize_title($asset); ?>" name="_block_<?php echo $asset_file; ?>_file[<?php esc_attr_e(basename($asset)); ?>]"><?php echo esc_textarea($this->get_asset_file_contents($json, $asset_file, $asset)); ?></textarea>
                </p>              
            <?php 
            } ?>
        </div>
        <?php
        // Get WordPress' media upload URL
        $upload_link = esc_url(get_upload_iframe_src('image', $post->ID));
        ?>
        <p class="d-flex assets">
            <input type="text" id="_block_<?php echo $asset_file; ?>" name="_block_<?php echo $asset_file; ?>" value="<?php esc_attr_e(empty($json[$asset_file]) ? '' : Utils::implode($json[$asset_file])); ?>" placeholder="file:./<?php echo $asset_file; ?>.<?php echo self::$assets[$asset_file]; ?>">
            <a title="<?php esc_attr_e('Upload new asset', 'wizard-blocks') ?>" class="dashicons-before dashicons-plus button button-primary upload-assets" href="<?php echo esc_url($upload_link); ?>" target="_blank"></a>
        </p>
        <?php
    }

    // build meta box
    public function meta_fields_build_css_callback($post, $metabox) {
        $plugin_name = $this->get_plugin_slug();
        $basepath = '';
        $json = [];
        if ($post) {
            $json = $this->get_json_data($post->post_name);
            $basepath = $this->get_blocks_dir($post->post_name) . DIRECTORY_SEPARATOR;
        }

        
        ?>
        <div class="inside">

            <h3><label for="_block_style"><?php esc_attr_e('Style', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#style"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p class="hint"><i><?php esc_attr_e('Block type frontend and editor styles definition. They will be enqueued both in the editor and when viewing the content on the front of the site.', 'wizard-blocks'); ?></i></p>
            <?php 
            $asset = 'style';
            $this->asset_form($json, $asset, $basepath, $post);
            ?>
            
            <hr>

            <h3><label for="_block_viewStyle"><?php esc_attr_e('View Style', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-style"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p class="hint"><i><?php esc_attr_e('Block type frontend styles definition. They will be enqueued only when viewing the content on the front of the site.', 'wizard-blocks'); ?></i></p>
            <?php 
            $asset = 'viewStyle';
            $this->asset_form($json, $asset, $basepath, $post);
            ?>
            
            <hr>

            <h3><label for="_block_editorStyle"><?php esc_attr_e('Editor Style', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#editor-style"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p class="hint"><i><?php esc_attr_e('Block type editor styles definition. They will only be enqueued in the context of the editor.', 'wizard-blocks'); ?></i></p>
            <?php 
            $asset = 'editorStyle';
            $this->asset_form($json, $asset, $basepath, $post);
            ?>

        </div>
        <?php
    }

    public function meta_fields_build_js_callback($post, $metabox) {
        $plugin_name = $this->get_plugin_slug();
        $basepath = '';
        $json = [];
        if ($post) {
            $json = $this->get_json_data($post->post_name);
            $basepath = $this->get_blocks_dir($post->post_name) . DIRECTORY_SEPARATOR;

            //$is_editor_script_generated = strpos($this->get_asset_file_contents($json, 'editorScript', $basepath), 'generated by ' . $plugin_name);
            //var_dump($is_editor_script_generated);
        }

        // Get WordPress' media upload URL
        $upload_link = esc_url(get_upload_iframe_src('image', $post->ID));
        ?>
        <div class="inside">

            <h3><label for="_block_editorScript"><?php esc_attr_e('Editor Script', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#editor-script"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p class="hint"><i><?php esc_attr_e('Block type editor scripts definition. They will only be enqueued in the context of the editor.', 'wizard-blocks'); ?></i></p>
            <?php
            // ' style="background-color: white; cursor: not-allowed;" rows="15" readonly'
            $asset = 'editorScript';
            $this->asset_form($json, $asset, $basepath, $post);
            ?>

            <hr>

            <h3><label for="_block_script"><?php esc_attr_e('Script', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#script"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p class="hint"><i><?php esc_attr_e('Block type frontend and editor scripts definition. They will be enqueued both in the editor and when viewing the content on the front of the site.', 'wizard-blocks'); ?></i></p>
            <?php 
            $asset = 'script';
            $this->asset_form($json, $asset, $basepath, $post);
            ?>

            <hr>

            <h3><label for="_block_viewScript"><?php esc_attr_e('View Script', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-script"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p class="hint"><i><?php esc_attr_e('Block type frontend scripts definition. They will be enqueued only when viewing the content on the front of the site.', 'wizard-blocks'); ?></i></p>
            <?php 
            $asset = 'viewScript';
            $this->asset_form($json, $asset, $basepath, $post);
            ?>

            <hr>

            <h3><label for="_block_viewScriptModule"><?php esc_attr_e('View Script Module', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-script-module"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p class="hint"><i><?php esc_attr_e('Block type frontend script module definition. They will be enqueued only when viewing the content on the front of the site.', 'wizard-blocks'); ?></i></p>
            <?php 
            $asset = 'viewScriptModule';
            $this->asset_form($json, $asset, $basepath, $post);
            ?>

        </div>
        <?php
    }

    public function meta_fields_build_attributes_callback($post, $metabox) {
        //wp_nonce_field('meta_fields_save_meta_box_side_data', 'meta_fields_meta_box_nonce');

        $json = $post ? $this->get_block_json($post->post_name) : [];

        //$style = get_post_meta($post->ID, '_meta_fields_book_title', true);
        $attributes = '';
        if (empty($json['attributes'])) {
            $attributes = get_post_meta($post->ID, '_transient_block_attributes', true);
            if ($attributes) {
                //warn
                $this->_notice(__('Attributes are not saved! Please <a href="#attributes">fix them</a> and resave block.', 'wizard-blocks'), 'danger error');
                $this->_notice(esc_html__('Please verify that Attributes is a valid JSON data!', 'wizard-blocks'), 'danger error inline');
            }
        }
        ?>
        <div class="inside">

            <h3 id="attributes"><label for="_block_attributes"><?php esc_attr_e('Attributes', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-attributes/"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><textarea id="_block_attributes" name="_block_attributes" class="wp-editor-area"><?php echo esc_textarea(empty($json['attributes']) ? $attributes : wp_json_encode($json['attributes'], JSON_PRETTY_PRINT)); ?></textarea></p>	

            <?php $attributes_condition = $post ? $this->get_block_attributes_condition($post->post_name) : []; ?>
            <div class="d-none">
                <h4 id="attributes_condition"><label for="_block_attributes_condition"><?php esc_attr_e('Attributes Condition', 'wizard-blocks'); ?></label></h4>
                <p><textarea id="_block_attributes_condition" name="_block_attributes_condition"><?php echo esc_textarea(empty($attributes_condition) ? '' : wp_json_encode($attributes_condition, JSON_PRETTY_PRINT)); ?></textarea></p>	
            </div>
            
            <div id="_block_attributes_editor">
                <div class="repeat_attrs">
                    <div class="repeat_attr">
                        <div class="attr_ops d-flex">
                            <span class="attr_name dashicons-before dashicons-editor-expand"> <?php esc_attr_e('Add an attribute KEY', 'wizard-blocks'); ?></span>
                            <abbr title="<?php esc_attr_e('Remove', 'wizard-blocks'); ?>" class="button button-danger attr_remove pull-right"><span class="dashicons dashicons-trash"></span></abbr>
                            <abbr title="<?php esc_attr_e('Up', 'wizard-blocks'); ?>" class="button attr_up pull-right"><span class="dashicons dashicons-arrow-up-alt"></span></abbr>
                            <abbr title="<?php esc_attr_e('Down', 'wizard-blocks'); ?>" class="button attr_down pull-right"><span class="dashicons dashicons-arrow-down-alt"></span></abbr>
                            <abbr title="<?php esc_attr_e('Clone', 'wizard-blocks'); ?>" class="button attr_clone pull-right"><span class="dashicons dashicons-admin-page"></span></abbr>
                        </div>
                        <div class="attr_data">
                            <label for="key"><?php esc_attr_e('Key', 'wizard-blocks'); ?>*: <input type="text" class="key"></label>
                            <label for="label"><?php esc_attr_e('Label', 'wizard-blocks'); ?> <a class="dashicons-before dashicons-info-outline" href="https://wordpress.github.io/gutenberg/?path=/docs/components-textcontrol--docs" target="_blank"></a>: <input type="text" class="label"></label>
                            <label for="type"><?php esc_attr_e('Type', 'wizard-blocks'); ?> <a class="dashicons-before dashicons-info-outline" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-attributes/#type-validation" target="_blank"></a>: 
                                <select class="type">
                                    <?php foreach (self::$attributes_type as $type => $label) { ?>
                                        <option value="<?php echo esc_attr($type); ?>"><?php echo esc_html($label); ?></option>
                                    <?php } ?>
                                </select></label>
                            <label for="component"><?php esc_attr_e('Component', 'wizard-blocks'); ?> <a class="dashicons-before dashicons-info-outline" href="https://developer.wordpress.org/block-editor/reference-guides/components/" target="_blank"></a>: 
                                <select class="component">
                                    <option value=""><?php esc_attr_e('Auto', 'wizard-blocks'); ?></option>
                                    <?php foreach (self::$attributes_component as $type => $label) { ?>
                                        <option value="<?php echo esc_attr($type); ?>"><?php echo esc_html($label); ?></option>
                                    <?php } ?>
                                </select></label>
                            <label for="inputType"><?php esc_attr_e('Input Type', 'wizard-blocks'); ?>: 
                                <select class="inputType">
                                    <?php foreach (self::$attributes_input_type as $type => $label) { ?>
                                        <option value="<?php echo esc_attr($label); ?>"><?php echo esc_html(ucfirst($label)); ?></option>
                                    <?php } ?>
                                </select></label>
                            <label for="position"><?php esc_attr_e('Position', 'wizard-blocks'); ?> <a class="dashicons-before dashicons-info-outline" href="https://developer.wordpress.org/block-editor/getting-started/fundamentals/block-in-the-editor/#block-controls-block-toolbar-and-settings-sidebar" target="_blank"></a>: <select class="position">
                                    <?php foreach (self::$attributes_position as $postion => $label) { ?>
                                        <option value="<?php echo esc_attr($postion); ?>"><?php echo esc_html($label); ?></option>
                                    <?php } ?>
                                </select></label>
                            <label for="multiple"><?php esc_attr_e('Multiple', 'wizard-blocks'); ?>: <select class="multiple">
                                    <option value="false"><?php esc_attr_e('False', 'wizard-blocks'); ?></option>
                                    <option value="true"><?php esc_attr_e('True', 'wizard-blocks'); ?></option>                                
                                </select></label>
                            <label for="options"><?php esc_attr_e('Options', 'wizard-blocks'); ?>: <textarea class="options" placeholder="FF00FF|Magenta"></textarea></label>
                            <label for="default"><?php esc_attr_e('Default value', 'wizard-blocks'); ?>: <textarea class="default" rows="1"></textarea></label>
                            <label for="source"><?php esc_attr_e('Source', 'wizard-blocks'); ?>: <select class="source">
                                    <option value=""><?php esc_attr_e('No value', 'wizard-blocks'); ?></option>
                                    <?php foreach (self::$attributes_source as $type => $label) { ?>
                                        <option value="<?php echo esc_attr($type); ?>"><?php echo esc_html($label); ?></option>
                                    <?php } ?>
                                </select></label>
                            <label for="selector"><?php esc_attr_e('Selector', 'wizard-blocks'); ?>: <input type="text" class="selector"></label>
                            <label for="attribute"><?php esc_attr_e('Attribute', 'wizard-blocks'); ?>: <input type="text" class="attribute"></label>
                            <label for="help"><?php esc_attr_e('Help', 'wizard-blocks'); ?> <a class="dashicons-before dashicons-info-outline" href="https://wordpress.github.io/gutenberg/?path=/docs/components-textcontrol--docs" target="_blank"></a>: <input type="text" class="help"></label>
                            <label for="className"><?php esc_attr_e('Wrapper Class', 'wizard-blocks'); ?>: <input type="text" class="className" placeholder="pt-5 my-spacial-control"></label>
                            <label for="extra"><?php esc_attr_e('Extra', 'wizard-blocks'); ?>: <textarea class="extra" placeholder='{ "var": "value" }'></textarea></label>
                            <label for="condition"><?php esc_attr_e('Condition', 'wizard-blocks'); ?>: <textarea class="condition" placeholder="attributes.fieldKey == true && attributes['field-key'] == 'blue'"></textarea></label>
                        </div>
                    </div>
                </div>
                <span class="dashicons-before dashicons-plus button button-primary attr_add">Add</span>
            </div>
        </div>
        <?php
    }

    public function meta_fields_build_meta_box_side_callback($post, $metabox) {
        //wp_nonce_field('meta_fields_save_meta_box_side_data', 'meta_fields_meta_box_nonce');

        $json = $post ? $this->get_block_json($post->post_name) : [];
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
            $css = file_get_contents($dashicons);  //wp_remote_get($dashicons); //
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

            <h3><label for="_block_apiVersion"><?php esc_attr_e('apiVersion', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-api-versions/"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><select type="text" id="_block_apiVersion" name="_block_apiVersion"><?php
        if (empty($json['apiVersion']))
            $json['apiVersion'] = 3;
        foreach (self::$apiVersions as $apiVersion) {
            $selected_safe = (!empty($json['apiVersion']) && $json['apiVersion'] == $apiVersion) ? ' selected' : '';
            echo '<option value="' . esc_attr($apiVersion) . '"' . esc_attr($selected_safe) . '>' . esc_html($apiVersion) . '</option>';
        }
        ?></select></p>	           

            <?php if (!empty($post->post_name)) { ?>
                <h3><label for="_block_name"><?php esc_attr_e('Name', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#name"><span class="dashicons dashicons-info-outline"></span></a></h3>
                <p><input style="width: 45%;" type="text" id="_block_textdomain" name="_block_textdomain" value="<?php echo esc_attr($this->get_block_textdomain($json)); ?>" />
                    <?php //echo esc_html($this->get_block_textdomain($json)); ?>
                    /
                    <input style="width: 45%;" type="text" id="_block_name" name="_block_name" value="<?php echo esc_attr($post->post_name); ?>" /></p>
            <?php } ?>
            
            <h3><label for="_block_version"><?php esc_attr_e('Version', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#version"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><input type="text" id="_block_version" name="_block_version" placeholder="1.0.1" value="<?php
            if (!empty($json['version'])) {
                echo esc_attr($json['version']);
            }
            ?>" /></p>	
            
            <p><label for="revision"><input type="checkbox" id="revision" name="revision"> <?php esc_attr_e('Create new revision', 'wizard-blocks'); ?></label></p>	

            <h3><label for="_block_icon"><?php esc_attr_e('Icon', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#icon"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><select id="_block_icon" name="_block_icon"><option value=""><?php esc_attr_e('Custom', 'wizard-blocks'); ?></option><?php
                if (empty($json['icon']))
                    $json['icon'] = '';
                $is_dash = false;
                foreach ($icons as $icon) {
                    $selected_safe = '';
                    if (isset($json['icon']) && $json['icon'] == $icon) {
                        $selected_safe = ' selected';
                        $is_dash = true;
                    }
                    echo '<option value="' . esc_attr($icon) . '"' . esc_attr($selected_safe) . '>' . esc_html($icon) . '</option>';
                }
                ?></select>
                <span id="icon_svg">
                    <textarea id="_block_icon_svg" name="_block_icon_svg" placeholder="<svg ...>...</svg>"><?php if (!empty($json['icon']) && !$is_dash) echo esc_textarea($json['icon']); ?></textarea>
                    <?php
                    if (!empty($json['icon'])) {
                        // PHPCS - The SVG file content is being read from a strict file path structure.
                        $json_icon_safe = $json['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
                        ?> 
                        <b><?php esc_attr_e('Current', 'wizard-blocks'); ?>:</b><br>
                        <?php echo $is_dash ? '<span class="dashicons dashicons-' . esc_attr($json['icon']) . '"></span>' : $json_icon_safe; ?>
                    <?php } ?> 
                </span>
            </p>	
                    <?php
                    $this->enqueue_style('select2', 'assets/lib/select2/select2.min.css');
                    $this->enqueue_script('select2', 'assets/lib/select2/select2.min.js', array('jquery'));
                    $block_editor_context = new \WP_Block_Editor_Context(
                            array(
                        'name' => 'core/customize-widgets',
                            )
                    );
                    $block_categories = get_block_categories($block_editor_context);
                    //var_dump($block_categories);
                    ?>
            <h3><label for="_block_category"><?php esc_attr_e('Category', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#category"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><select type="text" id="_block_category" name="_block_category"><?php
            foreach ($block_categories as $cat) {
                $selected = (!empty($json['category']) && $json['category'] == $cat['slug']) ? ' selected' : '';
                echo '<option value="' . esc_attr($cat['slug']) . '"' . esc_attr($selected) . '>' . esc_html($cat['title']) . '</option>';
            }
            ?></select></p>

            <h3><label for="_block_keywords"><?php esc_attr_e('Keywords', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#keywords"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><input type="text" id="_block_keywords" name="_block_keywords" placeholder="alert, message" value="<?php
                    if (!empty($json['keywords'])) {
                        echo esc_attr(is_array($json['keywords']) ? implode(', ', $json['keywords']) : $json['keywords']);
                    }
                    ?>" /></p>	           

            <h3><label for="_block_parent"><?php esc_attr_e('Parent', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#parent"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><input type="text" id="_block_parent" name="_block_parent" placeholder="core/group"  value="<?php
                      if (!empty($json['parent'])) {
                          echo esc_attr(is_array($json['parent']) ? implode(', ', $json['parent']) : $json['parent']);
                      }
                      ?>" /></p>	           

            <h3><label for="_block_ancestor"><?php esc_attr_e('Ancestor', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#ancestor"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><input type="text" id="_block_ancestor" name="_block_ancestor" placeholder="my-block/product"  value="<?php
                      if (!empty($json['ancestor'])) {
                          echo esc_attr(is_array($json['ancestor']) ? implode(', ', $json['ancestor']) : $json['ancestor']);
                      }
                      ?>" /></p>	

            <h3><label for="_block_allowedBlocks"><?php esc_attr_e('Allowed Blocks', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#allowed-blocks"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><input type="text" id="_block_allowedBlocks" name="_block_allowedBlocks" placeholder="my-block/product, my-block/title"  value="<?php
                      if (!empty($json['allowedBlocks'])) {
                          echo esc_attr(is_array($json['allowedBlocks']) ? implode(', ', $json['allowedBlocks']) : $json['allowedBlocks']);
                      }
                      ?>" /></p>

            <h3><label for="_block_blockHooks"><?php esc_attr_e('Hooks', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#block-hooks"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><textarea id="_block_blockHooks" name="_block_blockHooks" placeholder='{ "my-plugin/banner": "after" }'><?php
                      if (!empty($json['blockHooks'])) {
                          echo wp_json_encode($json['blockHooks'], JSON_PRETTY_PRINT);
                      }
                      ?></textarea></p>

            <h3><label for="_block_providesContext"><?php esc_attr_e('providesContext', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#provides-context"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><textarea id="_block_providesContext" name="_block_providesContext" placeholder='{ "my-plugin/recordId": "recordId" }'><?php
                    if (!empty($json['providesContext'])) {
                        echo wp_json_encode($json['providesContext'], JSON_PRETTY_PRINT);
                    }
                    ?></textarea></p>	

            <h3><label for="_block_usesContext"><?php esc_attr_e('usesContext', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#uses-context"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><input type="text" id="_block_usesContext" name="_block_usesContext" placeholder="postId, postType" value="<?php
                    if (!empty($json['usesContext'])) {
                        echo esc_attr(is_array($json['usesContext']) ? implode(', ', $json['usesContext']) : $json['usesContext']);
                    }
                    ?>" /></p>	           

            <h3><label for="_block_supports"><?php esc_attr_e('Supports', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-supports/"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <div style="height: 180px; overflow: auto; border: 1px solid #eee; padding: 0 10px;">
                      <?php
                      $custom = [];
                      $supports_array = [];
                      foreach (self::$supports as $sup => $default) {
                          $sup = esc_attr($sup);
                          ?>
                    <p>
                        <label for="_block_supports_<?php echo esc_attr($sup); ?>"><b><?php echo esc_html($sup); ?></b></label><br>
                        <!-- <input type="checkbox" id="_block_supports_<?php echo esc_attr($sup); ?>" name="_block_supports[<?php echo esc_attr($sup); ?>]"<?php
                    if (!empty($json['supports']) && in_array($sup, $json['supports'])) {
                        echo ' checked';
                    }
                    ?>> <b><?php echo esc_attr($sup); ?></b></label> -->
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
                                $tmp = explode('.', $sup, 2);
                                if (count($tmp) > 1) {
                                    $supports_array[reset($tmp)] = reset($tmp);
                                    if (isset($json['supports'][reset($tmp)][end($tmp)])) {
                                        if (is_bool($json['supports'][reset($tmp)][end($tmp)])) {
                                            $value = $json['supports'][reset($tmp)][end($tmp)];
                                        } else {
                                            $custom[reset($tmp)][end($tmp)] = $value;
                                        }
                                    } else {
                                        // like interactive which can be bool or obj
                                        if (isset($json['supports'][reset($tmp)])) {
                                            if (is_bool($json['supports'][reset($tmp)])) {
                                                $value = $json['supports'][reset($tmp)];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        ?>
                        <input type="radio" id="_block_supports_<?php echo esc_attr($sup); ?>_true" name="_block_supports[<?php echo esc_attr($sup); ?>]" value="true"<?php
                        if ($value) {
                            echo ' checked';
                        }
                        ?>> <label for="_block_supports_<?php echo esc_attr($sup); ?>_true"><?php esc_html_e('True', 'wizard-blocks'); ?></label>
                        <input type="radio" id="_block_supports_<?php echo esc_attr($sup); ?>_false" name="_block_supports[<?php echo esc_attr($sup); ?>]" value="false"<?php
                        if (!$value == 'false') {
                            echo ' checked';
                        }
                        ?>> <label for="_block_supports_<?php echo esc_attr($sup); ?>_false"><?php esc_html_e('False', 'wizard-blocks'); ?></label>
                    </p>
                    <?php } ?>	
            </div>
                           <?php
                           if (!empty($json['supports'])) {
                               //var_dump($supports_array);
                               foreach ($json['supports'] as $sup => $support) {
                                   if (!isset($custom[$sup]) && !isset(self::$supports[$sup]) && !in_array($sup, $supports_array)) {
                                       $custom[$sup] = $support;
                                   } else {
                                       if (is_array($support)) {
                                           foreach ($support as $sub => $suppo) {
                                               //var_dump($sub); var_dump($sup);
                                               if (!isset($custom[$sup][$sub]) && !isset(self::$supports[$sup . '.' . $sub])) {
                                                   if (!is_array($custom[$sup])) {
                                                       $custom[$sup] = [];
                                                   }
                                                   $custom[$sup][$sub] = $suppo;
                                               }
                                           }
                                       }
                                   }
                               }
                           }

                           $custom_transient = get_post_meta($post->ID, '_transient_block_supports_custom', true);
                           if ($custom_transient) {
                               //warn
                               $this->_notice(__('Custom Supports are not saved! Please <a href="#supports_custom">fix it</a> and resave block.', 'wizard-blocks'), 'danger error');
                               $this->_notice(esc_html__('Please verify that Custom Supports is a valid JSON data!', 'wizard-blocks'), 'danger error inline');
                           }
                           $custom = empty($custom) ? $custom_transient : wp_json_encode($custom, JSON_PRETTY_PRINT);
                           ?>
            <label id="supports_custom" for="_block_supports_custom"><b><?php esc_attr_e('Supports custom values', 'wizard-blocks'); ?></b></label>
            <textarea rows="10" id="_block_supports_custom" name="_block_supports_custom" style="width: 100%;" placeholder='{ "spacing": { "margin": [ "top", "bottom" ] } }'><?php echo esc_textarea($custom); ?></textarea>

            <?php
            $extra = $json;
            foreach (self::$fields as $field) {
                $tmp = explode('.', $field, 2);
                if (count($tmp) == 2) {
                    if (isset($extra[reset($tmp)][end($tmp)])) {
                        unset($extra[reset($tmp)][end($tmp)]);
                    }
                } else {
                    if (isset($extra[$field])) {
                        unset($extra[$field]);
                    }
                }
            }
            $extra_transien = get_post_meta($post->ID, '_transient_block_extra', true);
            if ($extra_transien) {
                //warn
                $this->_notice(__('Extra are not saved! Please <a href="#extra">fix it</a> and resave block.', 'wizard-blocks'), 'danger error');
                $this->_notice(esc_html__('Please verify that Custom Supports is a valid JSON data!', 'wizard-blocks'), 'danger error inline');
            }
            $extra = empty($extra) ? $extra_transien : wp_json_encode($extra, JSON_PRETTY_PRINT);
            ?>
            <h3><label id="extra" for="_block_extra"><b><?php esc_attr_e('Extra', 'wizard-blocks'); ?></b></label></h3>
            <textarea rows="10" id="_block_extra" name="_block_extra" style="width: 100%;"><?php echo esc_textarea($extra); ?></textarea>
        </div>
            <?php
        }
    }
    
