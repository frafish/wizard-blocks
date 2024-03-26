<?php

namespace WizardBlocks\Modules\Block\Traits;

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
    
    //https://developer.wordpress.org/block-editor/reference-guides/components/
    public static $attributes_control = [
        'TextControl' => 'Text',
        'RadioControl' => 'Radio',
        'SelectControl' => 'Select',
        'CheckboxControl' => 'Checkbox',
        'ToggleControl' => 'Toggle',
        'RichText' => 'RichText',
        'MediaUpload' => 'Media',
        'Heading' => 'Heading',
        'Separator' => 'Separator',
    ];

// register meta box
    public function meta_fields_add_meta_box() {
        add_meta_box(
                'render_meta_box',
                esc_html__('Block Content', 'wizard-blocks'),
                [$this, 'meta_fields_build_render_callback'],
                'block',
                //'side',
                //'default'
        );

        add_meta_box(
                'meta_fields_meta_box',
                esc_html__('Block Assets', 'wizard-blocks'),
                [$this, 'meta_fields_build_meta_box_callback'],
                'block',
                //'side',
                //'default'
        );
        add_meta_box(
                'attributes_meta_box',
                esc_html__('Block Attributes', 'wizard-blocks'),
                [$this, 'meta_fields_build_attributes_callback'],
                'block'
        );
        add_meta_box(
                'meta_fields_side_meta_box',
                esc_html__('Block Info', 'wizard-blocks'),
                [$this, 'meta_fields_build_meta_box_side_callback'],
                'block',
                'side',
                'default'
        );

        if ((!empty($_GET['action']) && $_GET['action'] == 'edit' && get_post_type() == 'block') || (!empty($_GET['post_type']) && $_GET['post_type'] == 'block')) {
            $this->enqueue_style('block-edit', 'assets/css/block-edit.css');
            $this->enqueue_script('block-edit', 'assets/js/block-edit.js');
            $php = wp_enqueue_code_editor(array('type' => 'text/html'));
            $css = wp_enqueue_code_editor(array('type' => 'text/css'));
            $js = wp_enqueue_code_editor(array('type' => 'application/javascript'));
            $json = wp_enqueue_code_editor(array('type' => 'application/json'));
            wp_enqueue_script('wp-theme-plugin-editor');
            wp_enqueue_style('wp-codemirror');
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
                $file = str_replace(DIRECTORY_SEPARATOR.'.'.DIRECTORY_SEPARATOR, '', $file); // \.\
            }
            
            $render_file = $this->get_ensure_blocks_dir($post->post_name) . $file;
            if (file_exists($render_file)) {
                $render = file_get_contents($render_file);
            }
        }
        ?>
        <div class="inside">
            <h3><label fot="_block_render"><?php _e('Render', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#render"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><textarea id="_block_render" name="_block_render" placeholder="Hello world!"><?php echo $render; ?></textarea></p>	           
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
    public function meta_fields_build_meta_box_callback($post, $metabox) {
        //wp_nonce_field('meta_fields_save_meta_box_data', 'meta_fields_meta_box_nonce');
        //$style = get_post_meta($post->ID, '_meta_fields_book_title', true);
        $plugin_name = $this->get_plugin_slug();
        $style = $editorStyle = $viewStyle = $script = $editorScript = $viewScriptModule = $viewScript = '';
        if ($post) {

            $json = $post ? $this->get_json_data($post->post_name) : [];
            $basepath = $this->get_ensure_blocks_dir($post->post_name);

            $asset_file = 'style.css';
            if (!empty($json['style'])) {
                $asset_file = str_replace('file:', '', $json['style']);
                $asset_file = str_replace('/', DIRECTORY_SEPARATOR, $asset_file);
            }
            $asset_file = $basepath . $asset_file;
            if (file_exists($asset_file)) {
                $style = file_get_contents($asset_file);
            }

            $asset_file = 'editorStyle.css';
            if (!empty($json['editorStyle'])) {
                $asset_file = str_replace('file:', '', $json['editorStyle']);
                $asset_file = str_replace('/', DIRECTORY_SEPARATOR, $asset_file);
            }
            $asset_file = $basepath . $asset_file;
            if (file_exists($asset_file)) {
                $editorStyle = file_get_contents($asset_file);
            }
            
            $asset_file = 'viewStyle.css';
            if (!empty($json['viewStyle'])) {
                $asset_file = str_replace('file:', '', $json['viewStyle']);
                $asset_file = str_replace('/', DIRECTORY_SEPARATOR, $asset_file);
            }
            $asset_file = $basepath . $asset_file;
            if (file_exists($asset_file)) {
                $viewStyle = file_get_contents($asset_file);
            }
            
            $asset_file = 'script.js';
            if (!empty($json['script'])) {
                $asset_file = str_replace('file:', '', $json['script']);
                $asset_file = str_replace('/', DIRECTORY_SEPARATOR, $asset_file);
            }
            $asset_file = $basepath . $asset_file;
            if (file_exists($asset_file)) {
                $script = file_get_contents($asset_file);
            }

            $is_editor_script_generated = false;
            $asset_file = 'editorScript.js';
            if (!empty($json['editorScript'])) {
                $asset_file = str_replace('file:', '', $json['editorScript']);
                $asset_file = str_replace('/', DIRECTORY_SEPARATOR, $asset_file);
                $unmin = str_replace('.min.js', '.js', $asset_file);
                $unmin_file = $basepath . $unmin;
                if (file_exists($unmin_file)) {
                    $asset_file = $unmin;
                }
            }
            $asset_file = $basepath . $asset_file;
            if (file_exists($asset_file)) {
                $editorScript = file_get_contents($asset_file);
                if (strpos($editorScript, 'generated by ' . $plugin_name) !== false) {
                    $is_editor_script_generated = true;
                }
            }
            //var_dump($is_editor_script_generated);

            $asset_file = 'viewScriptModule.js';
            if (!empty($json['viewScriptModule'])) {
                $asset_file = str_replace('file:', '', $json['viewScriptModule']);
                $asset_file = str_replace('/', DIRECTORY_SEPARATOR, $asset_file);
            }
            $asset_file = $basepath . $asset_file;
            if (file_exists($asset_file)) {
                $viewScriptModule = file_get_contents($asset_file);
            }
            
            $asset_file = 'viewScript.js';
            if (!empty($json['viewScript'])) {
                $asset_file = str_replace('file:', '', $json['viewScript']);
                $asset_file = str_replace('/', DIRECTORY_SEPARATOR, $asset_file);
            }
            $asset_file = $basepath . $asset_file;
            if (file_exists($asset_file)) {
                $viewScript = file_get_contents($asset_file);
            }
        }
        ?>
        <div class="inside">

            <a class="tab-head tab-active" href="#css"><?php _e('CSS', 'wizard-blocks'); ?></a> <a class="tab-head" href="js"><?php _e('SCRIPT', 'wizard-blocks'); ?></a>

            <div class="tab-body tab-css">
                <h3><label for="_block_style"><?php _e('Style', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#style"><span class="dashicons dashicons-info-outline"></span></a></h3>
                <p><textarea id="_block_style" name="_block_style"><?php echo $style; ?></textarea></p>	

                <h3><label for="_block_viewStyle"><?php _e('View Style', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-style"><span class="dashicons dashicons-info-outline"></span></a></h3>
                <p><textarea id="_block_viewStyle" name="_block_viewStyle"><?php echo $viewStyle; ?></textarea></p>
                
                <h3><label for="_block_editorStyle"><?php _e('Editor Style', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#editor-style"><span class="dashicons dashicons-info-outline"></span></a></h3>
                <p><textarea id="_block_editorStyle" name="_block_editorStyle"><?php echo $editorStyle; ?></textarea></p>	
            </div>
            <div class="tab-body tab-js">

                <h3><label for="_block_editorScript"><?php _e('Editor Script', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#editor-script"><span class="dashicons dashicons-info-outline"></span></a></h3>
                <p><textarea<?php echo (false) ? ' style="background-color: white; cursor: not-allowed;" rows="15" readonly' : ''; ?> id="_block_editorScript" name="_block_editorScript"><?php echo $editorScript; ?></textarea></p>
                
                <h3><label for="_block_script"><?php _e('Script', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#script"><span class="dashicons dashicons-info-outline"></span></a></h3>
                <p><textarea id="_block_script" name="_block_script"><?php echo $script; ?></textarea></p>

                <h3><label for="_block_viewScript"><?php _e('View Script', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-script"><span class="dashicons dashicons-info-outline"></span></a></h3>
                <p><textarea id="_block_viewScript" name="_block_viewScript"><?php echo $viewScript; ?></textarea></p>

                <h3><label for="_block_viewScriptModule"><?php _e('View Script Module', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-script-module"><span class="dashicons dashicons-info-outline"></span></a></h3>
                <p><textarea id="_block_viewScriptModule" name="_block_viewScriptModule"><?php echo $viewScriptModule; ?></textarea></p>

            </div>

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
                            <span class="attr_toggle"><span class="dashicons dashicons-editor-expand"></span></span>
                            <span class="attr_name">[attr_key] attr_title</span>
                            <abbr title="<?php _e('Remove', 'wizard-blocks'); ?>" class="button button-danger attr_remove pull-right"><span class="dashicons dashicons-trash"></span></abbr>
                            <abbr title="<?php _e('Up', 'wizard-blocks'); ?>" class="button attr_up pull-right"><span class="dashicons dashicons-arrow-up-alt"></span></abbr>
                            <abbr title="<?php _e('Down', 'wizard-blocks'); ?>" class="button attr_down pull-right"><span class="dashicons dashicons-arrow-down-alt"></span></abbr>
                            <abbr title="<?php _e('Clone', 'wizard-blocks'); ?>" class="button attr_clone pull-right"><span class="dashicons dashicons-admin-page"></span></abbr>
                        </div>
                        <div class="attr_data">
                            <label for="key">Key: <input type="text" class="key"></label>
                            <label for="label">Label: <input type="text" class="label"></label>
                            <label for="type">Type: <select class="type">
                                <?php foreach (self::$attributes_type as $type => $label) { ?>
                                   <option value="<?php echo $type ?>"><?php echo $label ?></option>
                                <?php } ?>
                            </select></label>
                            <label for="control">Control: <select class="control">
                                <option value=""><?php _e('Auto', 'wizard-blocks'); ?></option>
                                <?php foreach (self::$attributes_control as $type => $label) { ?>
                                    <option value="<?php echo $type ?>"><?php echo $label ?></option>
                                <?php } ?>
                            </select></label>
                            <label for="position">Position: <select class="position">
                                <option value="content"><?php _e('Content', 'wizard-blocks'); ?></option>
                                <option value="style"><?php _e('Style', 'wizard-blocks'); ?></option>
                                <option value="inspector"><?php _e('Inspector', 'wizard-blocks'); ?></option>
                                <option value="block"><?php _e('Block Content', 'wizard-blocks'); ?></option>
                            </select></label>
                            <label for="options">Options: <textarea class="options"></textarea></label>
                            <label for="default">Default: <input type="text" class="default"></label>
                            <label for="source">Source: <input type="text" class="source"></label>
                            <label for="selector">Selector: <input type="text" class="selector"></label>
                            <label for="attribute">Attribute: <input type="text" class="attribute"></label>
                            <label for="attr_extra">Extra: <textarea class="attr_extra"></textarea></label>
                        </div>
                    </div>
                </div>
                <span class="button button-primary attr_add">Add</span>
            </div>
        </div>
        <?php
        /*
          Currently available block fields

          Inner Blocks Field
          File Field
          Text Field
          Image Field
          URL Field
          Toggle Field
          Textarea Field
          Select Field
          Range Field
          Radio Field
          Number Field
          Multi-select Field
          Email Field
          Color Field
          Checkbox Field
         */
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
                            <?php echo $is_dash ? '<span class="dashicons dashicons-'.$json['icon'].'"></span>' : $json['icon']; ?>
                        <?php } ?> 
                    </span>
            </p>	
            <?php
            $this->enqueue_style('select2', 'assets/lib/select2/select2.min.css');
            $this->enqueue_script('select2', 'assets/lib/select2/select2.min.js', array('jquery'));
            ?>
            <h3><label for="_block_category"><?php _e('Category', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#category"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><select type="text" id="_block_category" name="_block_category"><?php
                    foreach (self::$categories as $cat) {
                        $selected = (!empty($json['category']) && $json['category'] == $cat) ? ' selected' : '';
                        echo '<option value="' . $cat . '"' . $selected . '>' . $cat . '</option>';
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
            
            <h3><label for="_block_providesContext"><?php _e('providesContext', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#provides-context"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><textarea id="_block_providesContext" name="_block_providesContext" placeholder='"my-plugin/recordId": "recordId"'><?php
                    if (!empty($json['providesContext'])) {
                        echo $providesContext = wp_json_encode($json['providesContext'], JSON_PRETTY_PRINT);
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
