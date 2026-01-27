<?php
namespace WizardBlocks\Modules\Block\Traits;

Trait Attributes {
    
    public function is_editor() {
        if ( is_admin() && function_exists( 'get_current_screen' ) ) {
                $screen = get_current_screen();
                if ( $screen && method_exists( $screen, 'is_block_editor' ) ) {
                        return $screen->is_block_editor();
                }
        }
        return isset($_GET['context']) && $_GET['context'] == "edit";
    }
    
    public function get_attributes($attributes, $panel = 'base', $group = 'settings', $position = 'default') {
        $attributes_panel = [];
        $panel = empty($group) ? 'base' : $panel;
        $group = empty($group) ? 'settings' : $group;
        $position = empty($position) ? 'default' : $position;
        //var_dump($panel);var_dump($group);var_dump($position);
        if (!empty($attributes)) {
            foreach ($attributes as $key => $attr) {
                if ((empty($attr['position']) && $position == 'default') 
                        || (!empty($attr['position']) && $attr['position'] == $position)) {
                    if ((empty($attr['group']) && $group == 'settings') 
                            || (!empty($attr['group']) && $attr['group'] == $group)) {
                        if ((empty($attr['panel']) && $panel == 'base') 
                                || (!empty($attr['panel']) && $attr['panel'] == $panel)) {
                            $attributes_panel[$key] = $attr;
                        }
                    }
                }
            }
        }
        return $attributes_panel;
    }
    
    
    public function get_default_attributes($block) {
        $attributes = [];
        if ($block && !empty($block['attributes'])) {
            $attributes_keys = array_keys($block['attributes']);
            foreach ($attributes_keys as $attr) {
                if (isset($block['example']['attributes'][$attr])) {
                    $attributes[$attr] = $block['example']['attributes'][$attr];
                } else if (isset($block['attributes'][$attr]['default'])) {
                    $attributes[$attr] = $block['attributes'][$attr]['default'];
                } else {
                    switch ($block['attributes'][$attr]['type']) {
                        case 'boolean':
                            $attributes[$attr] = false;
                            break;
                        case 'string':
                        default:
                            $attributes[$attr] = '';
                            break;
                    }
                }
            }
            if (isset($attributes['preview'])) {
                unset($attributes['preview']);
            }
            //$attributes = wp_json_encode($attributes);
        }
        return $attributes;
    }
    
    public function has_inner_blocks($args) {
        if (!empty($args['attributes'])) {
            foreach ($args['attributes'] as $key => $attr) {
                if (!empty($attr['component']) && $attr['component'] == 'InnerBlocks') {
                    return true;
                }
            }
        }
        return false;
    }
    
    public function set_inner_blocks_props($args) {
        if (!empty($args['attributes'])) {
            foreach ($args['attributes'] as $key => $attr) {
                if (!empty($attr['component']) && $attr['component'] == 'InnerBlocks') {
                    
                    //defaultBlock
                    if (empty($attr['defaultBlock']) && !empty($attr['options'])) {
                        $attr['defaultBlock'] = wp_unslash($attr['options']);
                    }
                    if (!empty($attr['defaultBlock']) && is_string($attr['defaultBlock'])) {
                        $tmp = array_filter(explode(PHP_EOL, $attr['defaultBlock']));
                        $attr['defaultBlock'] = [];
                        foreach ($tmp as $ablock) {
                            $bname = $ablock;
                            $bargs = [];
                            $btmp = explode(':', $ablock, 2);
                            if (count($btmp) == 2) {
                                $bname = reset($btmp);
                                $bargs = json_decode(end($btmp), true);
                            }
                            $attr['defaultBlock'][$bname] = $bargs;
                        }

                    }
                    //$defaultBlock_safe = empty($attr['defaultBlock']) ? '' : json_encode($attr['defaultBlock']);
                    $defaultBlock_safe = '';
                    if (!empty($attr['defaultBlock'])) {
                        $defaultBlock_safe = '[';
                        foreach ($attr['defaultBlock'] as $block_name => $block_value) {
                            if (!is_numeric($block_name)) {
                                $block_value_str = json_encode($block_value);
                                $block_value_str = str_replace('[]', '{}', $block_value_str);
                                $defaultBlock_safe .= "['".$block_name."', ".json_encode($block_value_str)."],";
                            }
                        }
                        $defaultBlock_safe .= ']';
                    }

                    //template
                    if (empty($attr['template']) && !empty($attr['default'])) {
                        $attr['template'] = wp_unslash($attr['default']);
                    }
                    if (!empty($attr['template']) && is_string($attr['template'])) {
                        $tmp = array_filter(explode(PHP_EOL, $attr['template']));
                        $attr['template'] = [];
                        foreach ($tmp as $ablock) {
                            $bname = $ablock;
                            $bargs = [];
                            $btmp = explode(':', $ablock, 2);
                            if (count($btmp) == 2) {
                                $bname = reset($btmp);
                                $bargs = end($btmp);
                                $bargs = $this->unescape($bargs);
                                
                                $bargs = str_replace("'", '"', $bargs);
                                //var_dump($bargs); die();
                                $bargs = str_replace('{', '{"', $bargs);
                                $bargs = str_replace(':', '":', $bargs);
                                $bargs = json_decode($bargs, true);
                                //var_dump($bargs); die();
                            }
                            $attr['template'][$bname] = $bargs;
                        }

                    }
                    //var_dump($attr['template']); die();
                    $template_safe = '';
                    if (!empty($attr['template'])) {
                        $template_safe = '[';
                        //foreach ($attr['template'] as $block_template) {
                            foreach ($attr['template'] as $block_name => $block_value) {
                                $block_value_str = json_encode($block_value);
                                if (!$block_value_str) {
                                    $block_value_str = '{}';
                                } else {
                                    $block_value_str = str_replace('[]', '{}', $block_value_str);
                                }
                                $template_safe .= "['".$block_name."', ".$block_value_str."],";
                            }
                        //}
                        $template_safe .= ']';
                        $template_safe = $this->unescape($template_safe);
                        if ($template_safe == '[]') $template_safe = false;
                    }
                    //var_dump($template_safe); die();

                    $allowedBlocks_safe = empty($attr['allowedBlocks']) ? '' : $attr['allowedBlocks'];
                    $allowedBlocks_safe = empty($allowedBlocks_safe) && !empty($args['allowedBlocks']) ? $args['allowedBlocks'] : $allowedBlocks_safe;
                    $allowedBlocks_safe = !empty($allowedBlocks_safe) && is_array($allowedBlocks_safe) ? '["'.implode('","', $allowedBlocks_safe).'"]' : '';

                    $renderAppender_safe = false; // TODO: a function that render a button
                    $orientation_safe = empty($attr['orientation']) ? '' : esc_js($attr['orientation']); //$in_toolbar ? 'horizontal' : 'vertical';
                    ?>
                    const innerBlocksProps = wp.blockEditor.useInnerBlocksProps(blockProps, {});
                    
                    const innerBlocksPropsCustom = { 
                    <?php
                        if ($defaultBlock_safe) { ?>defaultBlock: <?php echo $defaultBlock_safe; ?>, directInsert: true,<?php }
                        if ($template_safe) { ?>templateLock: false, template: <?php echo $template_safe; ?>,<?php }
                        if ($allowedBlocks_safe) { ?>allowedBlocks: <?php echo $allowedBlocks_safe; ?>,<?php }
                        if ($orientation_safe) { ?>orientation: '<?php echo esc_js($orientation_safe); ?>',<?php }
                        if ($renderAppender_safe) { ?>renderAppender: <?php echo $renderAppender_safe; ?>,<?php }
                        ?>
                    };
                    
                    const blockInnerBlocksProps = {
                        ...innerBlocksProps,
                        ...innerBlocksPropsCustom
                    };
                <?php
                }
            }
        }
    }
    
    public function _edit($args = [], $wrapper = false) {
        $key = esc_attr($args['name']);
        $textdomain = $this->get_block_textdomain($args);
        if (!empty($args['attributes'])) {
            foreach ($args['attributes'] as $id => $attr) {
                if (!empty($attr['location']) && empty($attr['position'])) { 
                    //genesis location
                    switch($attr['location']) {
                        case 'style': $args['attributes'][$id]['position'] = $attr['position'] = 'style'; break;
                        case 'advanced': $args['attributes'][$id]['position'] = $attr['position'] = 'advanced'; break;
                        case 'editor': 
                        default: // default
                    }
                }
                if (!empty($attr['position'])) {
                    switch ($attr['position']) {
                        case 'style':
                            $args['attributes'][$id]['position'] = 'default';
                            $args['attributes'][$id]['group'] = 'styles';
                            $args['attributes'][$id]['panel'] = 'style';
                            break;
                        case 'advanced':
                            $args['attributes'][$id]['position'] = 'default';
                            //$args['attributes'][$id]['group'] = 'advanced';
                            $args['attributes'][$id]['panel'] = 'advanced';
                            break;
                        case 'toolbar':
                            $args['attributes'][$id]['group'] = 'block';
                            $args['attributes'][$id]['panel'] = 'base';
                            break;
                    }
                }
            }
        }
        
        ob_start();
if ($wrapper) { ?><script id="<?php echo esc_attr($key); ?>"><?php } ?>
/* generated by wizard-blocks - remove this comment to customize */
<?php
if (!empty($args['attributes'])) {
    foreach ($args['attributes'] as $id => $attr) {
        $id = esc_attr($id);
        if (!empty($attr['api']) && is_array($attr['api']) && !empty($attr['api']['path'])) {
            // https://developer.wordpress.org/rest-api/reference/
            if (empty($attr['options']) || is_string($attr['options'])) {
                $api = $attr['api'];
                $var_name = empty($api['name']) ? 'wp_api' : esc_attr($api['name']);
                if (empty($api['value'])) { $api['value'] = 'index'; }
                $api['label'] = empty($api['label']) ? $api['value'] : $api['label'];
                ?>
var <?php echo esc_attr($var_name); ?> = <?php echo esc_attr($var_name); ?> || [];
<?php echo esc_attr($var_name); ?>['<?php echo esc_attr($key); ?>'] = <?php echo esc_attr($var_name); ?>['<?php echo esc_attr($key); ?>'] || [];
<?php echo esc_attr($var_name); ?>['<?php echo esc_attr($key); ?>']['<?php echo esc_attr($id); ?>'] = [];
wp.apiFetch( { path: '<?php echo esc_url($api['path']); ?>' } ).then( ( data ) => {
    if (data && typeof data == 'object') {
        if (data instanceof Array) {
            data.forEach((item, index) => {
                <?php echo esc_attr($var_name); ?>['<?php echo esc_attr($key); ?>']['<?php echo esc_attr($id); ?>'].push({ value: <?php echo esc_html($api['value']); ?>, label: <?php echo esc_html($api['label']); ?> });
            } );
        } else {
            for (const [index, item] of Object.entries(data)) {
                <?php echo esc_attr($var_name); ?>['<?php echo esc_attr($key); ?>']['<?php echo esc_attr($id); ?>'].push({ value: <?php echo esc_html($api['value']); ?>, label: <?php echo esc_html($api['label']); ?> });
            }
        }
    }
} );
    <?php
        $args['attributes'][$id]['options'] = $var_name."['".$key."']['".$id."']";
            }
        }
    }
}
//window.addEventListener("load", (event) => {
//var_dump($args); die();
?>
window.document.addEventListener("DOMContentLoaded", function(e) {
wp.blocks.registerBlockType("<?php echo esc_attr($key); ?>", {
    <?php
    if (!empty($args['icon'])) {
        $icon_svg = false;
        if (substr($args['icon'], 0, 7) == 'file:./') {
            $tmp = explode('/', $args['name']);
            $block_slug = end($tmp);
            $block_path = $this->get_ensure_blocks_dir($block_slug, $args['textdomain']);
            $tmp = explode('/', $args['icon']);
            $icon_name = substr($args['icon'], 6); // file:.
            $icon_name = str_replace('/', DIRECTORY_SEPARATOR, $icon_name);
            $icon_path = $block_path . $icon_name;
            $icon_svg = $this->get_filesystem()->get_contents($icon_path);
            //var_dump($icon_svg); die();
        }
        if (substr($args['icon'], 0, 5) == '<svg ') {
            $icon_svg = $args['icon'];
        }
        if ($icon_svg) {
            $icon_safe = $this->parse_svg($icon_svg);
    ?>
    icon: { 
        src: <?php echo $icon_safe; //echo esc_js($icon_safe); ?>
    },
    <?php }
    } ?>
    edit(props) {
        <?php if (!empty($args['example']['attributes']['preview'])) { 
        $image_url = $args['example']['attributes']['preview'];
        // TODO: need to convert file:../ to current block folder via js
        $script_id = sanitize_title($args['name']).'-editor-script-js';    
        //document.getElementById("?php echo $script_id; ?").src
        ?>
        if ( props.attributes.preview ) {
            return wp.element.createElement('img', {
                width: "100%",
                height: "auto",
                src: '<?php echo esc_url($image_url); ?>'
            });	
	}
        <?php } ?>
        
        const blockProps = wp.blockEditor.useBlockProps();
        <?php
        $this->set_inner_blocks_props($args);
        ?>
                
        return wp.element.createElement(
                'div',
                blockProps,
                <?php 
                if (!empty($args['attributes'])) {
                    
                    foreach ($args['attributes'] as $id => $attr) {
                        if (!empty($attr['panel']) && $attr['panel'] == 'inline') {
                            $this->_component($id, $attr, $args);
                        }
                    }
                    
                    $settings = $this->get_attributes($args['attributes']);
                    if (!empty($settings)) {
                    ?>
                    wp.element.createElement(
                            wp.blockEditor.InspectorControls,
                            {},
                            wp.element.createElement(
                                    wp.components.PanelBody,
                                    {
                                        title: wp.i18n.__("Settings", "<?php echo esc_attr($textdomain); ?>")
                                    },
                                    <?php 
                                    foreach ($settings as $id => $attr) {
                                        $this->_component($id, $attr, $args);
                                    }
                                    ?>

                            ),
                    ),
                    <?php 
                    }

                    $styles = $this->get_attributes($args['attributes'], 'style', 'styles');
                    if (!empty($styles)) { ?>
                    wp.element.createElement(
                            wp.blockEditor.InspectorControls,
                            { group: "styles" },
                            wp.element.createElement(
                                    wp.components.PanelBody,
                                    {
                                        title: wp.i18n.__("Style", "<?php echo esc_attr($textdomain); ?>")
                                    },
                                    <?php 
                                    foreach ($styles as $id => $attr) {
                                        $this->_component($id, $attr, $args);
                                    }
                                    ?>

                            ),
                    ),
                    <?php } 

                    $advanced = $this->get_attributes($args['attributes'], 'advanced');
                    if (!empty($advanced)) { ?>
                        wp.element.createElement(
                            wp.blockEditor.InspectorAdvancedControls,
                            {},
                            <?php 
                            foreach ($advanced as $id => $attr) {
                                $this->_component($id, $attr, $args);
                            }
                            ?>
                        ),
                    <?php }
                    
                    $toolbar = $this->get_attributes($args['attributes'], 'base', 'block', 'toolbar');
                    if (!empty($toolbar)) { ?>
                        wp.element.createElement(
                            wp.blockEditor.BlockControls,
                            { group: "block" },
                            <?php 
                            foreach ($toolbar as $id => $attr) {
                                $this->_component($id, $attr, $args);
                            }
                            ?>
                        ),
                    <?php }
                    
                    $inline = $this->get_attributes($args['attributes'], null, null, 'block');
                    if (!empty($inline)) {
                        foreach ($inline as $id => $attr) {
                            $this->_component($id, $attr, $args);
                        }
                    }
                }?>
                wp.element.createElement(
                    wp.components.Disabled, {},
                        wp.element.createElement(wp.serverSideRender, {
                            block: "<?php echo esc_attr($key); ?>",
                            <?php if (!empty($args['supports'])) { ?>skipBlockSupportAttributes: true, 
                            <?php } ?>attributes: props.attributes,
                        }),
                    )
            );
    },
    save() { <?php 
        // https://developer.wordpress.org/block-editor/how-to-guides/block-tutorial/nested-blocks-inner-blocks/
        if ($this->has_inner_blocks($args)) { ?>
        const blockPropsSave = wp.blockEditor.useBlockProps.save();
        //const innerBlocksPropsSave = wp.blockEditor.useInnerBlocksProps.save();
        const innerBlocksPropsSave = wp.blockEditor.useInnerBlocksProps.save(blockPropsSave);
        return innerBlocksPropsSave.children; //wp.blockEditor.InnerBlocks.Content;
        <?php
        /*return wp.element.createElement(
            'div',
            innerBlocksPropsSave,
            wp.element.createElement(wp.blockEditor.InnerBlocks.Content, null)
        );*/
        } else { ?>return null;<?php } ?> },
});
});
<?php
$conditions = $this->get_attributes_condition($args);
if (!empty($conditions)) {
    echo '/* wb:attributes:condition '.$conditions.' */';
    $conditions = json_decode($conditions, true);
}

if ($wrapper) { ?></script><?php }
        return ob_get_clean();
   }
   
   public function get_attributes_condition($args = [], $json = false) {
       $conditions = isset($_POST['_block_attributes_condition']) ? sanitize_textarea_field(wp_unslash($_POST['_block_attributes_condition'])) : wp_json_encode($this->get_block_attributes_condition($args['name'], $this->get_block_textdomain($args)));
       $conditions = $this->unescape($conditions);
       if ($json) $conditions = json_decode($conditions, true);
       return $conditions;
   }
   
   public function _component($id, $attr = [], $args = []) {
       
       $label = empty($attr['label']) ? ucfirst($id) : esc_html($attr['label']);
       $textdomain = empty($attr['textdomain']) ? $this->get_block_textdomain($args) : esc_attr($attr['textdomain']);
       
       $in_toolbar = !empty($attr['position']) && $attr['position'] == 'toolbar';
       
       if (!empty($attr['type']) && $attr['type'] == "object") {
           //return;
           // TODO: find a way to structure value on the onchange event
       }
       
       if (empty($attr['type'])) {
           $attr['type'] = 'string';
           if (!empty($attr['component'])) {
               switch($attr['component']) {
                   case 'CheckboxControl':
                       $attr['type'] = 'boolean';
               }
           }
       }
       
       if (empty($attr['component'])) {
           if (!empty($attr['control'])) { 
                //genesis control
                switch($attr['control']) {
                    //case 'url': $attr['component'] = 'LinkControl'; break;
                    case 'textarea': $attr['component'] = 'TextareaControl'; break;
                    case 'text':
                    default: //TextControl
                }
           }
       }
       
       // https://developer.wordpress.org/block-editor/reference-guides/block-api/block-attributes/#type-validation
       $component = 'TextControl';
       if (empty($attr['component'])) {
           if (!empty($attr['type'])) {
            switch ($attr['type']) {
                case 'boolean':
                    $component = 'ToggleControl';
                    break;
                case 'string':
                default:
                    $component = 'TextControl';
            }
           }
           if (!empty($attr['enum'])) {
               $attr['options'] = $attr['enum'];
           }
           if (!empty($attr['options'])) { 
               $component = 'SelectControl'; 
           }
       } else {
           $component = $attr['component'];
       }
       
       if ($component == 'ColorPicker') {
            if (!isset($attr['enableAlpha'])) {
                $attr['enableAlpha'] = true;
            }
        }
       
       if ($in_toolbar) {
           if ($component == 'ButtonGroup') {
               $component = 'ToolbarGroup';
           }
           if ($component == 'RadioControl') {
               $component = 'ToolbarDropdownMenu';
               if (!empty($attr['selected'])) { $attr['default'] = $attr['selected']; }
           }
           if ($component == 'MediaUpload') {
               //$component = 'HStack';
           }
       }
       
       // if has condition
       // example: (props.attribute.key == true) ? wp.element.createElement(...) : null
       $conditions = $this->get_attributes_condition($args, true);
       //var_dump($conditions); die();
       if (!empty($conditions[$id])) {
           $condition = $conditions[$id];
           $condition = str_replace('attributes.', 'props.attributes.', $condition);
           $condition = str_replace('attributes["', 'props.attributes["', $condition);
           echo '('.$condition.') ? (';
       }
      ?>
    wp.element.createElement(<?php if ($in_toolbar) { ?>wp.components.Toolbar<?php } else {?>"div"<?php } ?>,{ className: "block-editor-wrapper block-editor-wrapper__<?php echo esc_attr($id); ?> components-<?php echo strtolower($component); ?><?php if (!empty($attr['className'])) { echo ' '.esc_attr($attr['className']); } ?>", <?php if (!$in_toolbar) { ?>style: {marginTop: "10px"}<?php } ?>},
        <?php 
        // TITLE LABEL
        if (!$in_toolbar && !in_array($component, ['InnerBlocks', 'AnglePickerControl', 'CheckboxControl', 'ComboboxControl', 'ExternalLink', 'HorizontalRule', 'RadioControl', 'TextControl', 'TextareaControl', 'SelectControl', 'ToggleControl', 'FocalPointPicker']) && $label) { ?>
            wp.element.createElement("label",{className:"components-input-control__label", htmlFor: "inspector-control-<?php echo esc_attr($id); ?>", style: {display: "block"}}, wp.i18n.__("<?php echo esc_attr($label); ?>", "<?php echo esc_attr($textdomain); ?>")),
        <?php } ?>
       <?php if ($component == 'MediaUpload')  { ?>wp.element.createElement(wp.blockEditor.MediaUploadCheck, null, <?php } ?>
       wp.element.createElement(
            <?php 
            if ($component == 'InnerBlocks') {
                ?>
                wp.blockEditor.InnerBlocks, 
                blockInnerBlocksProps 
            <?php } else {
                echo in_array($component, ['MediaUpload', 'RichText', 'PanelColorSettings']) ? 'wp.blockEditor.' : 'wp.components.'; ?><?php echo esc_attr($component); ?>,
                    {
                    <?php if ($component == 'TextControl') {
                      switch ($attr['type']) {
                        case 'number':
                        case 'integer': ?>
                            type: 'number',
                            <?php if ($attr['type'] == 'integer') { ?>
                                step: 1,
                            <?php }
                      } 
                    } ?>
                        'aria-label': wp.i18n.__("<?php echo esc_attr($label); ?>","<?php echo esc_attr($textdomain); ?>"),
                        label: wp.i18n.__("<?php echo esc_attr($label); ?>", "<?php echo esc_attr($textdomain); ?>"),
                        id: "inspector-control-<?php echo esc_attr($id); ?>",
                        <?php if ($component == 'ButtonGroup') { ?>
                        style: {
                            display: "flex",
                            width: "100%"
                        },
                        <?php
                        }
                        // default
                        switch($component) {
                            case 'ToolbarGroup':  break;
                            case 'ButtonGroup':  break;
                            case 'ExternalLink':  break;
                            case 'ColorPicker': 
                                $color = '';
                                if (!empty($attr['default'])) { 
                                    $color = esc_html($attr['default']);
                                    ?>
                                    defaultValue: "<?php echo esc_attr($color); ?>",
                                    <?php
                                }
                                if (!empty($attr['color'])) { $color = esc_attr($attr['color']); } ?>
                                color: props.attributes.<?php echo esc_attr($id); ?><?php if (!empty($color)) { echo ' || "'.esc_attr($color).'"'; } ?>,
                            <?php
                                break;
                            case 'DatePicker':
                            case 'DateTimePicker':
                            case 'TimePicker': 
                                $date = 'new Date()';
                                if (!empty($attr['default'])) { 
                                    $date = '"'.esc_attr($attr['default']).'"';
                                }
                                ?>
                                currentDate: props.attributes.<?php echo esc_attr($id); ?> || <?php echo $date; ?>,
                            <?php 
                                break;
                            case 'CheckboxControl':
                            case 'ToggleControl': ?>
                                checked: (typeof props.attributes.<?php echo esc_attr($id); ?> == "boolean") ? props.attributes.<?php echo esc_attr($id); ?> : <?php if (!empty($attr['checked']) && $attr['checked'] == 'true') { ?>props.setAttributes({"<?php echo esc_attr($id); ?>": true}) || true<?php } else { ?>false<?php } ?>,
                            <?php 
                                break;
                            case 'RadioControl': 
                                if (!empty($attr['selected'])) { $attr['default'] = $attr['selected']; }
                                //selected: String(props.attributes.inherit !== undefined ? props.attributes.inherit : true),
                                ?>
                                selected: String(props.attributes.<?php echo esc_attr($id); ?><?php if (!empty($attr['default'])) { echo ' || '; echo (empty($attr['type']) || $attr['type'] == 'string') ? '"'.esc_attr($attr['default']).'"' : esc_attr($attr['default']); } ?>),
                            <?php 
                                break;
                            case 'SelectControl': 
                                $default = '';
                                if (!empty($attr['default'])) {
                                    if (!empty($attr['multiple'])) { 
                                        if (is_array($attr['default'])) {
                                            $values = $attr['default'];
                                        } else {
                                            $values = array_map('esc_js', array_filter(array_map('trim', explode(',', $attr['default']))));
                                        }
                                        $default = '[';
                                        foreach ($values as $key => $value) {
                                            $def = (empty($attr['type']) || in_array($attr['type'], ['string','array','object'])) ? '"'.esc_js($attr['default']).'"' : esc_js($attr['default']);
                                            if ($key) $default .= ',';
                                            $default .= $def;
                                        }
                                        $default .= ']';
                                    } else {
                                        $default = (empty($attr['type']) || $attr['type'] == 'string') ? '"'.esc_js($attr['default']).'"' : esc_js($attr['default']);
                                        $default = ($attr['type'] == 'boolean') ? '"'.esc_js($attr['default'] === true || $attr['default'] === 'true' ? 'true' : 'false').'"' : $default;
                                    }
                                }
                                $default_safe = $default;
                                ?>
                                value: props.attributes["<?php echo esc_attr($id); ?>"]<?php if (!empty($attr['default'])) { echo ' || '; echo $default_safe; } ?>,
                            <?php 
                                break;
                            case 'FontSizePicker': 
                                $fontSizes = [];
                                if (!empty($attr['options'])) {
                                    foreach ($attr['options'] as $size => $opt) {
                                        $fontSizes[] = [
                                            "name" => $opt,
                                            "slug" => sanitize_title($opt),
                                            "size" => intval($size)
                                        ];
                                    } 
                                    unset($attr['options']);
                                }
                                ?>
                                withSlider: true,
                                fontSizes: <?php echo wp_json_encode($fontSizes); ?>,<?php //[{"name":"Small","slug":"small","size":12},{"name":"Big","slug":"big","size":26}] ?>
                                value: props.attributes["<?php echo esc_attr($id); ?>"]<?php if (!empty($attr['default'])) { echo ' || ' . esc_attr($attr['default']); } ?>,
                            <?php
                                break;
                            case 'BorderBoxControl':
                            case 'BorderControl':
                                $colors = [];
                                if (!empty($attr['options'])) {
                                    foreach ($attr['options'] as $color => $opt) {
                                        $colors[] = [
                                            "name" => $opt,
                                            "color" => intval($color)
                                        ];
                                    } 
                                    unset($attr['options']);
                                }
                                if (!empty($colors)) { ?>
                                    colors: <?php echo wp_json_encode($colors); ?>,
                                <?php }
                            case 'BoxControl':
                                $default_safe = '{}';
                                if (!empty($attr['default'])) {
                                    $tmp = $attr['default'];
                                    if (is_string($attr['default'])) {
                                       $tmp = explode(',', $attr['default']);
                                       $tmp = array_filter($tmp);
                                    }
                                    $default = [];
                                    if (is_array($tmp) && count($tmp)) {
                                        if (count($tmp) == 1) {
                                            $default['left'] = $default['top'] = $default['right'] = $default['bottom'] = trim(reset($tmp));
                                        } else {
                                            if (!empty($tmp[0])) { $default['left'] = trim($tmp[0]); }
                                            if (!empty($tmp[1])) { $default['top'] = trim($tmp[1]); }
                                            if (!empty($tmp[2])) { $default['right'] = trim($tmp[2]); }
                                            if (!empty($tmp[3])) { $default['bottom'] = trim($tmp[3]); }
                                        }
                                        $default_safe = $default = json_encode($default);
                                    }
                                }
                                ?>
                                values: props.attributes.<?php echo esc_attr($id); ?> || <?php if (!empty($default_safe)) { echo $default_safe; } else { echo '{}'; } ?>,
                            <?php
                                break;
                            case 'DuotonePicker':
                                $colorPalette = [];
                                if (!empty($attr['options'])) {
                                    foreach ($attr['options'] as $color => $opt) {
                                        $colorPalette[] = [
                                            "name" => $opt,
                                            "slug" => sanitize_title($opt),
                                            "color" => intval($color)
                                        ];
                                    } 
                                    unset($attr['options']);
                                }
                                ?>
                                colorPalette: <?php echo wp_json_encode($colorPalette); ?>,
                                duotonePalette: <?php echo wp_json_encode($attr['default']); ?>,
                                values: props.attributes.<?php echo esc_attr($id); ?>,
                            <?php
                                unset($attr['default']);
                                break;
                            case 'RichText':
                            case 'TextControl':
                            default: 
                                if (isset($attr['default'])) {
                                    $default = (empty($attr['type']) || $attr['type'] == 'string') ? '"'.esc_js($attr['default']).'"' : esc_js($attr['default']);
                                    $default = ($attr['type'] == 'boolean') ? '"'.esc_js($attr['default'] === true || $attr['default'] === 'true' ? 'true' : 'false').'"' : $default; 
                                }
                                if ($attr['type'] == 'array') {
                                    $default = '[]';
                                    if (isset($attr['default'])) {
                                        $default = '['.esc_js(implode(',', $attr['default'])).']';
                                    }
                                }
                                ?>
                                value: props.attributes["<?php echo esc_attr($id); ?>"]<?php if (isset($attr['default'])) { echo ' || '.$default; } ?>,
                            <?php
                        } 
                        switch ($component) {
                            case 'MediaUpload': 
                                ?>
                                allowedTypes: ['image'],
                                <?php
                                if (!empty($attr['multiple'])) { ?>
                                gallery: true,
                                frame: 'post',
                                onSelect: function (mediaArray) {
                                    const newIds = mediaArray.map(media => media.id);
                                    props.setAttributes({ "<?php echo esc_attr($id); ?>": newIds }); 
                                },
                                render: function ({ open }) {
                                    const mediaIds = props.attributes.<?php echo esc_attr($id); ?> || [];
                                    const mediaCount = mediaIds.length;
                                    const previewImages = mediaIds.map((mediaId, index) => {
                                        const elementId = 'media-image-' + mediaId;
                                        if (mediaId) {
                                            wp.apiFetch({
                                                path: '/wp/v2/media/' + mediaId
                                            }).then((media) => {
                                                let newSrc = media.source_url;
                                                if (media.media_details && media.media_details.sizes && media.media_details.sizes.thumbnail && media.media_details.sizes.thumbnail.source_url) {
                                                    newSrc = media.media_details.sizes.thumbnail.source_url;
                                                }
                                                let mediaElement = document.getElementById(elementId);
                                                if (mediaElement && mediaElement.src !== newSrc) {
                                                    mediaElement.src = newSrc;
                                                }
                                            }).catch(() => { 
                                                 // error
                                            });
                                        }
                                        return wp.element.createElement("img", {
                                            key: mediaId,
                                            id: elementId, // ID DOM unic
                                            style: {
                                                display: 'block',
                                                width: '24%', 
                                                height: 'auto', 
                                                objectFit: 'cover',
                                                marginRight: '1%',
                                                marginBottom: '2px',
                                                border: '1px solid #aaa'
                                            },
                                            alt: wp.i18n.__("Media preview", "<?php echo esc_attr($textdomain); ?>"),
                                            title: `ID: ${mediaId}`, 
                                        });
                                    });
                                    const controls = [];
                                    controls.push(
                                        wp.element.createElement(wp.components.IconButton, {
                                            icon: mediaCount > 0 ? 'edit' : 'upload',
                                            label: mediaCount > 0 ? wp.i18n.__("Edit Gallery", "<?php echo esc_attr($textdomain); ?>") : wp.i18n.__("Aggiungi Immagini", "wb"),
                                            onClick: open,
                                            isPrimary: true,
                                        })
                                    );

                                    if (mediaCount > 0) {
                                        controls.push(
                                            wp.element.createElement(wp.components.IconButton, {
                                                icon: 'trash', 
                                                label: wp.i18n.__("Remove Gallery", "<?php echo esc_attr($textdomain); ?>"),
                                                onClick: () => {
                                                    props.setAttributes({ "<?php echo esc_attr($id); ?>": undefined }); 
                                                },
                                                isDestructive: true,
                                            })
                                        );
                                    }

                                    return wp.element.createElement("div", {
                                        style: {
                                            backgroundColor: "#e7e7e7",
                                            padding: "5px",
                                            border: "1px solid #ccc",
                                            position: "relative"
                                        }
                                    },
                                        wp.element.createElement("div", {
                                            style: {
                                                display: "flex",
                                                flexWrap: "wrap"
                                            }
                                        },
                                            mediaCount > 0 
                                                ? previewImages 
                                                : wp.element.createElement("p", { style: { margin: 0, padding: '15px 0', width: '100%', textAlign: 'center' } }, wp.i18n.__("No selected Medias", "<?php echo esc_attr($textdomain); ?>"))
                                        ),

                                        wp.element.createElement("div", {
                                            style: {
                                                position: "absolute",
                                                top: "5px",
                                                right: "5px",
                                                display: "flex",
                                                gap: "5px",
                                            }
                                        },
                                            ...controls
                                        )
                                    );
                                },
                                <?php } else {
                                ?>
                                onSelect: function (media) {
                                    props.setAttributes({ "<?php echo esc_attr($id); ?>": media.id });
                                    if (media.sizes && media.sizes.thumbnail && media.sizes.thumbnail.url) {
                                        let mediaElement = document.getElementById('media-<?php echo esc_attr($id); ?>');
                                        if (mediaElement) { mediaElement.src = media.sizes.thumbnail.url; }
                                    }
                                },
                                render: function ({ open }) {
                                    const mediaId = props.attributes.<?php echo esc_attr($id); ?>;
                                    let src = '/wp-includes/images/media/default.svg';
                                    if (mediaId) {
                                        wp.apiFetch({
                                            path: '/wp/v2/media/' + mediaId
                                        }).then((media) => {
                                            let newSrc = media.source_url;
                                            if (media.media_details && media.media_details.sizes && media.media_details.sizes.thumbnail && media.media_details.sizes.thumbnail.source_url) {
                                                newSrc = media.media_details.sizes.thumbnail.source_url;
                                            }
                                            let mediaElement = document.getElementById('media-<?php echo esc_attr($id); ?>');
                                            if (mediaElement && mediaElement.src !== newSrc) {
                                                mediaElement.src = newSrc;
                                            }
                                        }).catch(() => { /* Handle error */ });
                                    }

                                    return wp.element.createElement("div", {
                                        style: {
                                            backgroundColor: "#e7e7e7",
                                            display: "flex",
                                            flexDirection: "column",
                                            alignItems: "center",
                                            gap: "10px",
                                            border: "1px solid #ccc",
                                            position: "relative"
                                        }
                                    },
                                    
                                        wp.element.createElement("img", {
                                            id: "media-<?php echo esc_attr($id); ?>",
                                            src: src,
                                            style: {
                                                width: "100%",
                                                height: "auto",
                                                maxHeight: "150px",
                                                objectFit: "cover",
                                                aspectRatio: "2/1",
                                                objectPosition: "50% 50%"
                                            },
                                            alt: wp.i18n.__("<?php echo esc_attr($label); ?> preview", "<?php echo esc_attr($textdomain); ?>")
                                        }),

                                        wp.element.createElement("div", {
                                            className: "components-image-controls",
                                            style: {
                                                position: "absolute",
                                                top: "5px",
                                                right: "5px",
                                                display: "flex",
                                                gap: "5px"
                                            }
                                        },
                                            wp.element.createElement(wp.components.IconButton, {
                                                icon: mediaId ? 'edit' : 'upload',
                                                label: mediaId ? wp.i18n.__("Change Media", "<?php echo esc_attr($textdomain); ?>") : wp.i18n.__("Select Media", "<?php echo esc_attr($textdomain); ?>"),
                                                onClick: open,
                                                isPrimary: true,
                                            }),
                                            mediaId && wp.element.createElement(wp.components.IconButton, {
                                                icon: 'trash', 
                                                label: wp.i18n.__("Remove Media", "<?php echo esc_attr($textdomain); ?>"),
                                                onClick: () => {
                                                    props.setAttributes({ "<?php echo esc_attr($id); ?>": undefined }); 
                                                    let mediaElement = document.getElementById('media-<?php echo esc_attr($id); ?>');
                                                    if (mediaElement) { mediaElement.src = '/wp-includes/images/media/default.svg'; }
                                                },
                                                isDestructive: true,
                                            })  
                                        )
                                    )
                                },
                        <?php
                                }
                            break;
                            case 'Heading': ?>
                            text: wp.i18n.__('<?php echo esc_attr($label); ?>', "<?php echo esc_attr($textdomain); ?>"),
                        <?php break;
                        case 'ExternalLink': ?>
                            href: <?php echo "'". esc_attr($attr['default']) ."'";  ?>,
                        <?php break;
                        case 'ToolbarDropdownMenu':  
                            if (!empty($attr['icon'])) { ?>
                            icon: '<?php echo esc_attr($attr['icon']); ?>',    
                            <?php }
                            break;
                        case 'ToolbarGroup':  break;
                        case 'ButtonGroup':  break;
                        case 'BorderControl': ?>
                            onChange: function (val) {
                                const { width, style, color } = val;
                                const isEffectivelyEmpty = (width === undefined || width === '') && 
                                                           (style === undefined || style === '') && 
                                                           (color === undefined || color === '');
                                if (isEffectivelyEmpty) {
                                    val = undefined;
                                } 
                                props.setAttributes({"<?php echo esc_attr($id); ?>": val});
                            },
                            <?php
                            break;
                        default: ?>
                        onChange: function (val) {
                            if (val === '') {
                               <?php if (isset($attr['default'])) {
                                    switch ($attr['type']) {
                                        case 'boolean':
                                            $default = '"'.esc_js($attr['default'] === true || $attr['default'] === 'true' ? 'true' : 'false').'"';
                                            break;
                                        case 'numeric':
                                        case 'integer':
                                            $default = esc_js($attr['default']);
                                            break;
                                        case 'array':
                                        case 'object':
                                            // this var already contain the previous generated default JS printable value
                                            if (!isset($default)) {
                                                $default = json_encode($attr['default']);
                                            }
                                            break;
                                        case 'string':
                                        default:
                                            $default = '"'.esc_js($attr['default']).'"';
                                    }
                                    ?>
                                    val = <?php echo $default; ?>;
                                    <?php    
                                } else { ?>
                                     val = undefined; // Or null
                                <?php } ?>
                            } else {
                            <?php
                            //console.log(val);
                            //console.log(typeof val);
                            switch ($attr['type']) { 
                                case 'number': ?>
                                    val = parseFloat(val);
                                    <?php
                                    break;
                                case 'integer': 
                                    ?>
                                    val = parseInt(val);
                                    <?php
                                    break;
                                case 'boolean': ?>
                                    val = typeof val == 'string' ? val === 'true' : val;
                                    <?php
                                    break;
                                case 'object': ?>
                                    const isEmpty = Object.values(val).every(v => v === undefined || v === '');
                                    if (isEmpty) {
                                        val = undefined;
                                    }
                                    <?php
                                    break;
                                case 'string':
                                default:
                                    if (!empty($attr['sanitize'])) { 
                                        switch($attr['sanitize']) {
                                            case 'title': ?>
                                                val = val.replace(/[^a-zA-Z0-9_-]/g,"");
                                                <?php break;
                                        }
                                    }
                                
                            }
                            /*document.querySelector('[id=^"<?php echo esc_attr($id); ?>"][value=<?php echo (empty($attr['type']) || $attr['type'] == 'string') ? '"' : ''; ?>'+val+'<?php echo (empty($attr['type']) || $attr['type'] == 'string') ? '"' : ''; ?>]').checked = true;*/
                            /*console.log("<?php echo esc_attr($id); ?>");*/
                            //console.log(val);
                            //console.log(typeof val);
                            ?>
                            }
                            props.setAttributes({"<?php echo esc_attr($id); ?>": val});
                        },
                        <?php
                        }
                        if (!empty($attr['options']) && !in_array($component, ['ButtonGroup', 'ToolbarGroup'])) {
                            if (in_array($component, ['ToolbarDropdownMenu'])) { ?>
                            controls: [
                                    <?php foreach ($attr['options'] as $value => $label) { 
                                        if ($attr['type'] == 'string') {
                                            $value = '"'.esc_attr($value).'"';
                                            $tmp = explode('|', $label, 2);
                                            if (count($tmp) > 1) {
                                                $label = reset($tmp);
                                                $value = '"'.esc_attr(end($tmp)).'"';
                                            }
                                        }
                                        $value_safe = $value;
                                        ?>
                                        {title: "<?php echo esc_attr($label); ?>",
                                        icon: (<?php echo $value_safe; ?> == props.attributes["<?php echo $id; ?>"] ? "saved" : ""),
                                        onClick: function () {
                                            <?php /*
                                            console.log("<?php echo esc_attr($id); ?>");
                                            console.log(<?php echo $value_safe; ?>);
                                            console.log(this);
                                            //this.className.add("is-active"); */ ?>
                                            props.setAttributes({"<?php echo esc_attr($id); ?>": <?php echo $value_safe; ?>});                                    
                                        }, },
                                        <?php
                                    }
                                ?>],
                            <?php } else { ?>
                            options: <?php 
                                //var_dump($attr['options']);
                                if (is_array($attr['options'])) {
                                    echo '[';
                                    if (is_array(reset($attr['options']))) {
                                        echo wp_json_encode($attr['options']);
                                    } else {
                                        
                                        $has_label = false;
                                        foreach ($attr['options'] as $value => $label) { 
                                            if (count(explode('|', $label)) > 1) {
                                                $has_label = true;
                                            }
                                        }
                                        
                                        foreach ($attr['options'] as $value => $label) { 
                                            if (!$has_label) {
                                                $value = $label;
                                            }
                                            switch ($attr['type']) {
                                                case 'string':
                                                case 'array':
                                                case 'object': 
                                                    $value = '"'.$value.'"';
                                                    break;
                                            }
                                            $tmp = explode('|', $label, 2);
                                            if (count($tmp) > 1) {
                                                $label = reset($tmp);
                                                $value = '"'.end($tmp).'"';
                                            }
                                            switch($attr['type']) {   
                                                case 'boolean':
                                                    //var_dump($value); die();
                                                    $value = is_bool($value) ? ($value ? 'true' : 'false') : $value;
                                                    $value = '"'.$value.'"';
                                                        break;
                                            }
                                            $value_safe = $value;
                                            ?>
                                            {value: <?php echo $value_safe; ?>, label: "<?php echo esc_attr($label); ?>"},
                                        <?php } 
                                    }
                                    echo ']';
                                } else {    
                                    $attr_options_safe = $attr['options'];
                                    echo $attr_options_safe;
                                }
                                ?>,
                        <?php }
                        }
                        if (!empty($attr['placeholder'])) { ?>
                            placeholder: "<?php echo esc_attr($attr['placeholder']); ?>",
                        <?php }
                        if (isset($attr['multiple'])) { ?>
                            multiple: <?php echo $attr['multiple'] ? 'true' : 'false'; ?>,
                        <?php }
                        if (!empty($attr['help']) && !$in_toolbar) { ?>
                            help: wp.i18n.__("<?php echo esc_attr($attr['help']); ?>","<?php echo esc_attr($textdomain); ?>"),
                        <?php }
                        if (!empty($attr['tag'])) { ?>
                            tag: "<?php echo esc_attr($attr['tag']); ?>",
                        <?php }
                        /*if (!empty($attr['className'])) { ?>
                            className: "<?php echo esc_attr($attr['className']); ?>",
                        <?php } */
                        if (!empty($attr['enableAlpha'])) { ?>
                            enableAlpha: true,
                        <?php }
                        if (!empty($attr['rows'])) { ?>
                            rows: <?php echo intval($attr['rows']); ?>,
                        <?php }
                        if (!empty($attr['indeterminate'])) { ?>
                            indeterminate: true,
                        <?php }
                        if (!empty($attr['inputType'])) { ?>
                            type: "<?php echo esc_attr($attr['inputType']); ?>",
                        <?php } ?>
                    },
                    <?php if (in_array($component, ['ButtonGroup', 'ToolbarGroup'])) {
                        if (empty($attr['options']) && $id == 'align') {
                            $attr['options'] = ['left', 'center', 'right', 'justify'];
                        }
                        if (!empty($attr['options'])) {
                            //var_dump($attr); die();
                            foreach ($attr['options'] as $key => $label) {
                                $value = $key;
                                $tmp = explode('|', $label, 2);
                                if (count($tmp) > 1) {
                                    $value = reset($tmp);
                                    $label = end($tmp);
                                }
                                $value_escaped = '"'.esc_attr($value).'"';
                                $label_escaped = '"'.esc_attr($label).'"';
                                if (!is_numeric($key)) {
                                    $value_escaped = '"'.esc_attr($key).'"'; // value|Label
                                    $label_escaped = '"'.esc_attr($label).'"'; // value|Label
                                }
                                if (in_array($attr['type'], ['boolean'])) {
                                    $value = is_bool($value) ? ($value ? 'true' : 'false') : $value;
                                    $value_escaped = esc_attr( $value );
                                }
                                if (in_array($attr['type'], ['number', 'integer'])) {
                                    $value_escaped = esc_attr(floatval($label));
                                }
                                ?>
                                wp.element.createElement(wp.components.<?php echo $in_toolbar ? 'Toolbar' : ''; ?>Button, {
                                    style: {
                                        width: "100%"
                                    },
                                    value: <?php echo $value_escaped; ?>,
                                    variant: (props.attributes.<?php echo esc_attr($id); ?> === <?php echo $value_escaped; ?>) ? 'primary' : 'secondary',
                                    onClick: function (event) {
                                        jQuery(event.target).addClass('is-primary').removeClass('is-secondary');
                                        jQuery(event.target).siblings('.is-primary').removeClass('is-primary');
                                        let val = event.target.value;
                                        <?php 
                                        switch ($attr['type']) { 
                                            case 'number':
                                            case 'integer': 
                                                ?>
                                                val = parseFloat(val);
                                                <?php
                                                break;
                                            case 'boolean': ?>
                                            val = typeof val == 'string' ? val === 'true' : val;
                                            <?php
                                            break;
                                        }
                                        ?>
                                        props.setAttributes({"<?php echo esc_attr($id); ?>": val});                                    
                                    },
                                    text: wp.i18n.__(<?php echo $label_escaped; ?>, "<?php echo esc_attr($textdomain); ?>")
                                }),
                            <?php }
                        }       
                    } 
            }
            ?>
            <?php if ($component == 'MediaUpload')  { ?>), <?php } ?>
            <?php if ($component == 'ExternalLink') { ?>
            wp.i18n.__('<?php echo esc_attr($label); ?>', "<?php echo esc_attr($textdomain); ?>"),
            <?php } ?>
            ),
            <?php if ($component == 'ExternalLink' && !empty($attr['help'])) { ?>
            wp.element.createElement("p", {className:'components-base-control__help'}, wp.i18n.__("<?php echo esc_attr($attr['help']); ?>", "<?php echo esc_attr($textdomain); ?>"), ),
            <?php } ?>
            
        )<?php if (!empty($conditions[$id])) { echo ') : null'; } ?>,
    <?php
   }
   
    
}
