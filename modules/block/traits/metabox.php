<?php

namespace WizardBlocks\Modules\Block\Traits;

use WizardBlocks\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) exit; 

trait Metabox {

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
                'example_meta_box',
                esc_html__('Example', 'wizard-blocks'),
                [$this, 'meta_fields_build_example_callback'],
                'block',
                'side',
                'default'
        );
        
        add_meta_box(
                'meta_fields_side_meta_box',
                esc_html__('Info', 'wizard-blocks'),
                [$this, 'meta_fields_build_meta_box_side_callback'],
                'block',
                'side',
                'default'
        );

        add_meta_box(
                'extra_side_meta_box',
                esc_html__('Extra', 'wizard-blocks'),
                [$this, 'meta_fields_build_extra_side_callback'],
                'block',
                'side',
                'default'
        );
        
        remove_meta_box( 'pageparentdiv', 'block', 'side' ); 

        if ($this->is_block_edit()) {
            $this->enqueue_style('block-edit', 'assets/css/block-edit.css');
            $this->enqueue_style('block-ai', 'assets/css/ai.css');
            $this->enqueue_script('block-edit', 'assets/js/block-edit.js');
            $php = wp_enqueue_code_editor(array('type' => 'text/html'));
            $css = wp_enqueue_code_editor(array('type' => 'text/css'));
            $js = wp_enqueue_code_editor(array('type' => 'application/javascript'));
            $json = wp_enqueue_code_editor(array('type' => 'application/json'));
            wp_enqueue_script('wp-theme-plugin-editor');
            wp_enqueue_style('wp-codemirror');
            wp_enqueue_media();

            // Enqueue jQuery UI Resizable
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-resizable');
            wp_enqueue_style('wp-jquery-ui-dialog');

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
                $render = $this->get_filesystem()->get_contents($render_file);
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
                $example = "&lt;div &lt;?php echo <a href='https://developer.wordpress.org/reference/functions/get_block_wrapper_attributes/' target='_blank'>get_block_wrapper_attributes</a>(); ?&gt&gt;<br> &lt;?php<br> echo <b>\$attributes</b>['acme']; <br> echo <b>\$content</b>; <br> echo <b>\$block</b>->blockName;<br>?&gt;<br>&lt;/div&gt;";
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
            
            <?php do_action('wizard/block/edit/render', $json, $post, $this); ?>
            
        </div>
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
        
        $this->enqueue_script('js-beautify', 'assets/lib/js-beautify/beautify-js.min.js', array(), '1.15.1', true);
        $this->enqueue_script('css-beautify', 'assets/lib/js-beautify/beautify-css.min.js', array(), '1.15.1', true);    

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
                Utils::_notice(__('Attributes are not saved! Please <a href="#attributes">fix them</a> and resave block.', 'wizard-blocks'), 'danger error');
                Utils::_notice(esc_html__('Please verify that Attributes is a valid JSON data!', 'wizard-blocks'), 'danger error inline');
            }
        }
        //var_dump($json['attributes']);
        ?>
        <div class="inside">

            <h3 id="attributes"><label for="_block_attributes"><?php esc_attr_e('Attributes', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-attributes/"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><textarea id="_block_attributes" name="_block_attributes" class="wp-editor-area"><?php echo esc_textarea(empty($json['attributes']) ? $attributes : wp_json_encode($json['attributes'], JSON_PRETTY_PRINT)); ?></textarea></p>	

        <?php $attributes_condition = $post ? $this->get_block_attributes_condition($post->post_name) : []; ?>
            <div class="d-none">
                <h4 id="attributes_condition"><label for="_block_attributes_condition"><?php esc_attr_e('Attributes Condition', 'wizard-blocks'); ?></label></h4>
                <p><textarea id="_block_attributes_condition" name="_block_attributes_condition"><?php echo esc_textarea(empty($attributes_condition) ? '' : wp_json_encode($attributes_condition, JSON_PRETTY_PRINT)); ?></textarea></p>	
            </div>

            <div id="_block_attributes_editor" class="repeat_wrapper">
                <div class="repeat_attrs">
                    <details class="repeat_attr">
                        <summary class="attr_ops d-flex">
                            <span class="attr_name dashicons-before dashicons-editor-expand"> <?php esc_attr_e('Add an attribute KEY', 'wizard-blocks'); ?></span>
                            <abbr title="<?php esc_attr_e('Remove', 'wizard-blocks'); ?>" class="button button-danger attr_remove pull-right"><span class="dashicons dashicons-trash"></span></abbr>
                            <abbr title="<?php esc_attr_e('Up', 'wizard-blocks'); ?>" class="button attr_up pull-right"><span class="dashicons dashicons-arrow-up-alt"></span></abbr>
                            <abbr title="<?php esc_attr_e('Down', 'wizard-blocks'); ?>" class="button attr_down pull-right"><span class="dashicons dashicons-arrow-down-alt"></span></abbr>
                            <abbr title="<?php esc_attr_e('Clone', 'wizard-blocks'); ?>" class="button attr_clone pull-right"><span class="dashicons dashicons-admin-page"></span></abbr>
                        </summary>
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
                    </details>
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

        ?>
        <div class="inside">

            <h3><label for="_block_apiVersion"><?php esc_attr_e('apiVersion', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-api-versions/"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><select type="text" id="_block_apiVersion" name="_block_apiVersion"><?php
            if (empty($json['apiVersion'])) {
                $json['apiVersion'] = 3;
            }
            foreach (self::$apiVersions as $apiVersion) {
                $selected_safe = (!empty($json['apiVersion']) && $json['apiVersion'] == $apiVersion) ? ' selected' : '';
                echo '<option value="' . esc_attr($apiVersion) . '"' . esc_attr($selected_safe) . '>' . esc_html($apiVersion) . '</option>';
            }
            ?></select></p>	           
        <?php
        
            $textdomain = $post_name = $placeholder = '';
            if (!empty($post->post_name)) { 
                $post_name = $post->post_name; 
                $textdomain = $this->get_block_textdomain($json);
            }
            if ($user = wp_get_current_user()) {
                $placeholder = $user->user_nicename;
            }
            
            $block_usage_count = 0;
            if (!empty($json['name'])) {
                $block_usage = $this->get_blocks_usage($json['name']);
                $block_usage_count = empty($block_usage['count']) ? 0 : intval($block_usage['count']);
                //var_dump($block_usage);
            }
            ?>
            <h3><label for="_block_name"><?php esc_attr_e('Name', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#name"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><input<?php echo $block_usage_count ? ' readonly' : ''; ?> style="width: 45%;" type="text" id="_block_textdomain" name="_block_textdomain" value="<?php echo esc_attr($textdomain); ?>" placeholder="<?php echo esc_attr($placeholder); ?>" />
            <?php //echo esc_html($this->get_block_textdomain($json));  ?>
                /
            <input<?php echo $block_usage_count ? ' readonly' : ''; ?>  style="width: 45%;" type="text" id="_block_name" name="_block_name" value="<?php echo esc_attr($post_name); ?>" placeholder="<?php echo esc_attr($post_name); ?>" /></p>
            
            <?php if ($block_usage_count) { ?>
            <label class="text-warning"><input type="checkbox" onChange="jQuery('#_block_textdomain, #_block_name').prop('readonly', false); jQuery(this).parent().fadeOut();"><?php esc_html_e('WARNING: I know that if I change Textdomain or Name the block will not appear anymore on pages where I\'ve previously used it.', 'wizard-blocks'); ?></label>            
            <?php } ?>
            
            <?php if (!empty($json['name'])) { ?>
            <h3><label for="_block_usage"><?php esc_attr_e('Usage', 'wizard-blocks'); ?></label></h3>
            <p><?php 
            /* translators: 1: Block used times. */
            printf( esc_attr__('Used %s times in this site', 'wizard-blocks'), esc_attr($block_usage_count)); ?></p>           
            <?php if ($block_usage_count) { ?>
            <div class="block-usage-posts-list"><ul>
            <?php
            foreach ($block_usage['posts'] as $post_id) { ?>
                <li><a href="<?php echo esc_url(get_edit_post_link($post_id)); ?>" target="_blank">[<?php echo esc_html($post_id); ?>] <?php echo esc_html(get_the_title($post_id)); ?></a></li>
            <?php } ?>
            </ul>
            </div>
            <?php } ?>
            <?php } ?>
            
            <h3><label for="_block_version"><?php esc_attr_e('Version', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#version"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p><input type="text" id="_block_version" name="_block_version" placeholder="1.0.1" value="<?php
                if (!empty($json['version'])) {
                    echo esc_attr($json['version']);
                }
                ?>" /></p>	

            <p><label for="revision"><input type="checkbox" id="revision" name="revision"> <?php esc_attr_e('Create new revision', 'wizard-blocks'); ?></label></p>	

            <h3><label for="_block_icon"><?php esc_attr_e('Icon', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#icon"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <?php 
            $block_icon = empty($json['icon']) ? '' : $json['icon'];
            $block_name = empty($json['name']) ? '' : $json['name'];
            $this->_block_icon_selector($block_icon, $block_name); ?>	
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
            
            <?php do_action('wizard-block/category', $block_categories, $this); ?>

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

        </div>
        <?php
    }

    public function meta_fields_build_extra_side_callback($post, $metabox) {
        //wp_nonce_field('meta_fields_save_meta_box_side_data', 'meta_fields_meta_box_nonce');

        $json = $post ? $this->get_block_json($post->post_name) : [];
        //$style = get_post_meta($post->ID, '_meta_fields_book_title', true);
        ?>
        <div class="inside">

            <h3><label for="_block_blockHooks"><?php esc_attr_e('Hooks', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#block-hooks"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p class="block-blockHooks"><textarea id="_block_blockHooks" name="_block_blockHooks" placeholder='{ "my-plugin/banner": "after" }'><?php
                    if (!empty($json['blockHooks'])) {
                        echo wp_unslash(wp_json_encode($json['blockHooks'], JSON_PRETTY_PRINT));
                    }
                    ?></textarea></p>

            <h3><label for="_block_providesContext"><?php esc_attr_e('providesContext', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#provides-context"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p class="block-providesContext"><textarea id="_block_providesContext" name="_block_providesContext" placeholder='{ "my-plugin/recordId": "recordId" }'><?php
                    if (!empty($json['providesContext'])) {
                        echo wp_unslash(wp_json_encode($json['providesContext'], JSON_PRETTY_PRINT));
                    }
                    ?></textarea></p>	

            <h3><label for="_block_usesContext"><?php esc_attr_e('usesContext', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#context"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <p class="block-usesContext"><input type="text" id="_block_usesContext" name="_block_usesContext" placeholder="postId, postType" value="<?php
                if (!empty($json['usesContext'])) {
                    echo esc_attr(is_array($json['usesContext']) ? implode(', ', $json['usesContext']) : $json['usesContext']);
                }
                ?>" /></p>	           

            <h3><label for="_block_supports"><?php esc_attr_e('Supports', 'wizard-blocks'); ?></label> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-supports/"><span class="dashicons dashicons-info-outline"></span></a></h3>
            <div class="block-supports" style="height: 280px; overflow: auto; border: 1px solid #eee; padding: 0 10px;">
                <?php
                $custom = [];
                $supports_array = [];
                foreach (self::$supports as $sup => $default) {
                    $sup = esc_attr($sup);

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
                        
                    $changed = '';
                    if ($value != $default) {
                        $changed = ' text-accent';
                    }
                    ?>
                    <p class="support support-<?php echo esc_attr($sup); ?><?php echo esc_attr($changed); ?>">
                        <label for="_block_supports_<?php echo esc_attr($sup); ?>"><b><?php echo esc_html($sup); ?></b></label><br>
                        <!-- <input type="checkbox" id="_block_supports_<?php echo esc_attr($sup); ?>" name="_block_supports[<?php echo esc_attr($sup); ?>]"<?php
                        if (!empty($json['supports']) && in_array($sup, $json['supports'])) {
                            echo ' checked';
                        }
                        ?>> <b><?php echo esc_attr($sup); ?></b></label> -->
                        
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
                                    if (empty($custom[$sup]) || !is_array($custom[$sup])) {
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
                Utils::_notice(__('Custom Supports are not saved! Please <a href="#supports_custom">fix it</a> and resave block.', 'wizard-blocks'), 'danger error');
                Utils::_notice(esc_html__('Please verify that Custom Supports is a valid JSON data!', 'wizard-blocks'), 'danger error inline');
            }
            $custom = empty($custom) ? $custom_transient : wp_json_encode($custom, JSON_PRETTY_PRINT);
            ?>
            <label id="supports_custom" for="_block_supports_custom"><b><?php esc_attr_e('Supports custom values', 'wizard-blocks'); ?></b></label>
            <p class="block-supports-custom"><textarea rows="10" id="_block_supports_custom" name="_block_supports_custom" style="width: 100%;" placeholder='{ "spacing": { "margin": [ "top", "bottom" ] } }'><?php echo esc_textarea($custom); ?></textarea></p>

            <?php
            if (isset($json['example'])) {
                if (isset($json['example']['attributes'])) {
                    unset($json['example']['attributes']);
                }
                if (empty($json['example'])) {
                    unset($json['example']);
                }
            }
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
            $extra_transient = get_post_meta($post->ID, '_transient_block_extra', true);
            if ($extra_transient) {
                //warn
                Utils::_notice(__('Extra are not saved! Please <a href="#extra">fix it</a> and resave block.', 'wizard-blocks'), 'danger error');
                Utils::_notice(esc_html__('Please verify that Custom Supports is a valid JSON data!', 'wizard-blocks'), 'danger error inline');
            }
            $extra = empty($extra) ? $extra_transient : wp_json_encode($extra, JSON_PRETTY_PRINT);
            ?>
            <h3><label id="extra" for="_block_extra"><b><?php esc_attr_e('Extra', 'wizard-blocks'); ?></b></label></h3>
            <textarea rows="10" id="_block_extra" name="_block_extra" style="width: 100%;"><?php echo esc_textarea($extra); ?></textarea>
        </div>
        <?php
    }
    
    public function meta_fields_build_example_callback($post, $metabox) {
        //var_dump($post);
        $block = $post ? $this->get_block_json($post->post_name) : [];
        $upload_link = '';
        $example_preview_url = false;
        if ($post) {
            $upload_link = esc_url(get_upload_iframe_src('image', $post->ID));
            $block_textdomain = $this->get_block_textdomain($block);
            $basepath = $this->get_blocks_dir($post->post_name, $block_textdomain);
            $example_preview = empty($block['example']['attributes']['preview']) ? '' : $block['example']['attributes']['preview'];
            if ($example_preview) {
                $example_preview_path = $this->get_asset_file($block, $example_preview, $basepath);
                //var_dump($example_preview_path);
                if (file_exists($example_preview_path)) {
                    $example_preview_url = \WizardBlocks\Core\Helper::path_to_url($example_preview_path);
                }
            }
        }
        ?>
        <div class="inside">
            
                <h3><label id="_block_preview_label" for="_block_preview"><b><?php esc_attr_e('Preview Image', 'wizard-blocks'); ?></b></label></h3>
            
                <p class="hide-if-no-js">
                    <a href="<?php echo esc_url($upload_link); ?>" id="set-block-example-thumbnail" aria-describedby="set-block-thumbnail-desc" class="thickbox d-block">
                        <span class="set-block-example-thumbnail-label"><?php esc_attr_e('Set cover image', 'wizard-blocks'); ?></span>
                        <?php if ($example_preview_url) { ?>
                            <img class="block-example-thumbnail" src="<?php echo esc_url($example_preview_url); ?>">
                        <?php } ?>
                    </a>
                </p>
                <p class="hide-if-no-js howto" id="set-block-example-thumbnail-desc"><?php esc_attr_e('Click the image to edit or update', 'wizard-blocks'); ?></p>
                <p class="hide-if-no-js"><a href="#" id="remove-block-example-thumbnail" class="text-danger"><?php esc_attr_e('Remove cover image', 'wizard-blocks'); ?></a></p>
                <input placeholder="file:./preview.png" type="text" id="_block_preview" name="_block_preview" value="<?php echo $example_preview; ?>">
                
                
                <?php if ($post && !empty($block['attributes']) && (count($block['attributes']) > 1 || !isset($block['attributes']['preview']))) { ?>
                <div class="_block_example_values">
                <hr>
                
                <h3><label id="_block_example_label" for="_block_example"><b><?php esc_attr_e('Example Values', 'wizard-blocks'); ?></b></label></h3>
                
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th colspan="2">
                                <h2><?php esc_attr_e('Optional example values', 'wizard-blocks'); ?>
                                    <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#example"><span class="dashicons dashicons-info-outline"></span></a>
                                </h2>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($block['attributes'] as $akey => $attr) { 
                        if (isset($block['example']['attributes'][$akey])) {
                            $attr['default'] = $block['example']['attributes'][$akey];
                        }
                        $this->_e_attribute_row($attr, $akey, '_block_example', $block);
                    } ?> 
                    </tbody>
                </table>
                </div>
                <?php } ?>
                    
        </div>
        <?php
        
    }
    
    function _e_attribute_row($attr, $akey, $field, $block) {
        $id = $field.'-'.$akey;
        ?>
        <tr>
            <td>
                <abbr title="<?php echo $akey; ?>">
                    <label for="<?php echo esc_attr($id); ?>">
                    <?php echo empty($attr['label']) ? $akey : $attr['label']; ?>
                    </label>
                </abbr>
                <?php if (!empty($attr['help'])) { ?>
                <p class="hint"><i><?php echo $attr['help']; ?></i></p>
                <?php } ?>
            </td>
            <?php
            switch ($attr['type']) {
                case 'boolean': ?>
                    <td><input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $field; ?>[<?php echo $akey; ?>]"<?php echo esc_attr(!empty($attr['default']) ? ' checked' : ''); ?>></td>
                    <?php
                    break;
                case 'number':
                case 'integer': ?>
                    <td><input type="number" id="<?php echo $id ?>" name="<?php echo $field; ?>[<?php echo $akey; ?>]" placeholder="<?php echo esc_attr(isset($attr['default']) ? $attr['default'] : $akey); ?>" value="<?php echo esc_attr(isset($attr['default']) ? $attr['default'] : ''); ?>"></td>
                    <?php
                    break;
                case 'array':
                case 'object':
                    $attr['default'] = wp_json_encode($attr['default'], JSON_PRETTY_PRINT); 
                case 'string':
                    //if ($akey == 'preview') break;
                default: ?>
                    <td><textarea id="<?php echo $id; ?>" name="<?php echo $field; ?>[<?php echo $akey; ?>]" placeholder="<?php echo esc_attr(isset($attr['default']) ? $attr['default'] : $akey); ?>"><?php echo empty($attr['default']) ? '' : $attr['default']; ?></textarea></td>
              
                <?php  
                /*default: ?>
                    <td><input type="text" id="<?php echo $id; ?>" name="<?php echo $field; ?>[<?php echo $akey; ?>]" placeholder="<?php echo esc_attr(isset($field['default']) ? $field['default'] : $akey); ?>" value=""></td>
            <?php */ } ?>
        </tr>
        <?php
    }
}
