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
    
    public function _edit($args = [], $wrapper = false) {
        $key = esc_attr($args['name']);
        $textdomain = $this->get_block_textdomain($args);
        if (!empty($args['attributes'])) {
            foreach ($args['attributes'] as $id => $attr) {
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
                            $args['attributes'][$id]['group'] = 'other';
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
?>
window.addEventListener("load", (event) => {
wp.blocks.registerBlockType("<?php echo esc_attr($key); ?>", {
    <?php
    if (!empty($args['icon']) && substr($args['icon'], 0, 5) == '<svg ') {
    ?>
    icon: { 
        src: <?php $icon_safe = $this->parse_svg($args['icon']); echo esc_js($icon_safe); ?>
    },
    <?php } ?>
    edit(props) {
        <?php if (!empty($args['example']['attributes']['preview'])) { ?>
        if ( props.attributes.preview ) {
            return wp.element.createElement('img', {
                width: "100%",
                height: "auto",
                src: '<?php echo esc_url($args['example']['attributes']['preview']); ?>'
            });	
	}
        <?php } ?>
        return wp.element.createElement(
                'div',
                wp.blockEditor.useBlockProps(),
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
                    
                    $toolbar = $this->get_attributes($args['attributes'], 'base', 'other', 'toolbar');
                    if (!empty($toolbar)) { ?>
                        wp.element.createElement(
                            wp.blockEditor.BlockControls,
                            { group: "other" },
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
        const innerBlocksProps = wp.blockEditor.useInnerBlocksProps.save();
        return innerBlocksProps.children; //wp.blockEditor.InnerBlocks.Content;
        <?php } else { ?>return null;<?php } ?> },
});
});
<?php
if ($wrapper) { ?></script><?php }
        return ob_get_clean();
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
       
       if ($in_toolbar) {
           if ($component == 'ButtonGroup') {
               $component = 'ToolbarGroup';
           }
           if ($component == 'RadioControl') {
               $component = 'ToolbarDropdownMenu';
           }
           if ($component == 'MediaUpload') {
               //$component = 'HStack';
           }
           
           
       }
       
      ?>
    wp.element.createElement(<?php if ($in_toolbar) { ?>wp.components.Toolbar<?php } else {?>"div"<?php } ?>,{<?php if (!empty($attr['className'])) { ?>className: "<?php echo esc_attr($attr['className']); ?>", <?php }  if (!$in_toolbar) { ?>style: {marginTop: "10px"}<?php } ?>},
        <?php 
        if (!$in_toolbar && !in_array($component, ['AnglePickerControl', 'CheckboxControl', 'RadioControl', 'TextControl', 'TextareaControl', 'SelectControl', 'ToggleControl']) && $label) { ?>
            wp.element.createElement("label",{className:"components-input-control__label", htmlFor: "inspector-control-<?php echo esc_attr($id); ?>", style: {display: "block"}}, wp.i18n.__("<?php echo esc_attr($label); ?>", "<?php echo esc_attr($textdomain); ?>")),
        <?php } ?>
       wp.element.createElement(
            <?php 
            if ($component == 'InnerBlocks') {
                $template_safe = empty($attr['template']) ? '' : $attr['template'];
                $allowedBlocks_safe = empty($attr['allowedBlocks']) ? [] : array_map('esc_js', $attr['allowedBlocks']);
                $allowedBlocks_safe = empty($allowedBlocks_safe) && !empty($args['allowedBlocks']) ? $args['allowedBlocks'] : $allowedBlocks_safe;
                $allowedBlocks_safe = !empty($allowedBlocks_safe) && is_array($allowedBlocks_safe) ? '["'.implode('","', $allowedBlocks_safe).'"]' : '';
                $renderAppender_safe = false; // TODO: a function that render a button
                $orientation_safe = empty($attr['orientation']) ? '' : $attr['orientation']; //$in_toolbar ? 'horizontal' : 'vertical';
                ?>
                wp.blockEditor.InnerBlocks, wp.blockEditor.useInnerBlocksProps(wp.blockEditor.useBlockProps(), {
                    <?php 
                    if ($template_safe) { ?>template: <?php echo $template_safe; ?>,<?php }
                    if ($allowedBlocks_safe) { ?>allowedBlocks: <?php echo $allowedBlocks_safe; ?>,<?php }
                    if ($orientation_safe) { ?>orientation: '<?php echo esc_js($orientation_safe); ?>',<?php }
                    if ($renderAppender_safe) { ?>renderAppender: <?php echo $renderAppender_safe; ?>,<?php }
                    ?>
                }),
            <?php } else {
                echo in_array($component, ['MediaUpload', 'RichText', 'PanelColorSettings']) ? 'wp.blockEditor.' : 'wp.components.'; ?><?php echo esc_attr($component); ?>,
                    {
                        'aria-label': wp.i18n.__("<?php echo esc_attr($label); ?>","<?php echo esc_attr($textdomain); ?>"),
                        label: wp.i18n.__("<?php echo esc_attr($label); ?>", "<?php echo esc_attr($textdomain); ?>"),
                        id: "inspector-control-<?php echo esc_attr($id); ?>",
                        <?php
                        // default
                        switch($component) {
                            case 'ToolbarGroup':  break;
                            case 'ButtonGroup':  break;
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
                                    $date = '"'.esc_html($attr['default']).'"';
                                }
                                ?>
                                currentDate: props.attributes.<?php echo esc_attr($id); ?> || <?php echo esc_attr($date); ?>,
                            <?php 
                                break;
                            case 'CheckboxControl':
                            case 'ToggleControl': ?>
                                checked: props.attributes.<?php echo esc_attr($id); ?>,
                            <?php 
                                break;
                            case 'RadioControl': 
                                if (!empty($attr['selected'])) { $attr['default'] = $attr['selected']; }
                                ?>
                                selected: props.attributes.<?php echo esc_attr($id); ?><?php if (!empty($attr['default'])) { echo ' || '; echo (empty($attr['type']) || $attr['type'] == 'string') ? '"'.esc_attr($attr['default']).'"' : esc_attr($attr['default']); } ?>,
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
                                            $def = (empty($attr['type']) || $attr['type'] == 'string') ? '"'.esc_js($attr['default']).'"' : esc_js($attr['default']);
                                            if ($key) $default .= ',';
                                            $default .= $def;
                                        }
                                        $default .= ']';
                                    } else {
                                        $default = (empty($attr['type']) || $attr['type'] == 'string') ? '"'.esc_js($attr['default']).'"' : esc_js($attr['default']);
                                    }
                                }
                                $default_safe = $default;
                                ?>
                                value: props.attributes.<?php echo esc_attr($id); ?><?php if (!empty($attr['default'])) { echo ' || '; echo $default_safe; } ?>,
                            <?php 
                                break;
                            case 'RichText':
                            case 'TextControl':
                            default: ?>
                                value: props.attributes.<?php echo esc_attr($id); ?><?php if (!empty($attr['default'])) { echo ' || '; echo (empty($attr['type']) || $attr['type'] == 'string') ? '"'.esc_attr($attr['default']).'"' : esc_attr($attr['default']); } ?>,
                            <?php
                        } 
                        switch ($component) {
                            case 'MediaUpload': ?>
                        onSelect: function (media) {
                            document.getElementById('media-<?php echo esc_attr($id); ?>').src = media.sizes.thumbnail.url;
                            props.setAttributes({<?php echo esc_attr($id); ?>: media.id});
                        },
                        render: function ( open ) {
                            let src = '/wp-includes/images/media/default.svg';
                            if (props.attributes.<?php echo esc_attr($id); ?>) {
                                wp.apiFetch( { path: '/wp/v2/media/'+props.attributes.<?php echo esc_attr($id); ?> } ).then( ( media ) => {
                                    src = media.media_details.sizes.thumbnail.source_url;
                                    document.getElementById('media-<?php echo esc_attr($id); ?>').src = src;
                                } );
                            }
                            return wp.element.createElement("div", {},
                                wp.element.createElement(wp.components.Button,
                                { 
                                    text: wp.i18n.__("<?php echo esc_attr($label); ?>", "<?php echo esc_attr($textdomain); ?>"),
                                    //title: wp.i18n.__("Open Media Library", "<?php echo esc_attr($textdomain); ?>"),
                                    onClick: open.open,
                                    variant: "secondary",
                                    style: {width: "100%"},
                                }), 
                                wp.element.createElement("img",
                                    {
                                        id: "media-<?php echo esc_attr($id); ?>",
                                        src: src,
                                    }
                                ),
                            );
                        },
                        <?php break;
                            case 'Heading': ?>
                        text: wp.i18n.__('<?php echo esc_attr($label); ?>', "<?php echo esc_attr($textdomain); ?>"),
                        <?php break;
                        case 'ToolbarDropdownMenu':  break;
                        case 'ToolbarGroup':  break;
                        case 'ButtonGroup':  break;
                            default: ?>
                        onChange: function (val) {
                            <?php
                            if (!empty($attr['sanitize'])) { 
                                switch($attr['sanitize']) {
                                    case 'title': ?>
                                        val = val.replace(/[^a-zA-Z0-9_-]/g,"");
                                        <?php break;
                                }
                            }
                            if ($attr['type'] == 'number' || $attr['type'] == 'integer') { 
                            ?>
                            val = parseInt(val);
                            <?php } ?>
                            props.setAttributes({<?php echo esc_attr($id); ?>: val});
                        },
                        <?php
                        }
                        if (!empty($attr['options']) && !in_array($component, ['ButtonGroup', 'ToolbarGroup'])) {
                            if (in_array($component, ['ToolbarDropdownMenu'])) { ?>
                            controls: [
                                    <?php foreach ($attr['options'] as $value => $label) { 
                                        if ($attr['type'] == 'string') {
                                            $value = '"'.$label.'"';
                                            $tmp = explode('|', $label, 2);
                                            if (count($tmp) > 1) {
                                                $label = reset($tmp);
                                                $value = '"'.end($tmp).'"';
                                            }
                                        }
                                        $value_safe = $value;
                                        ?>
                                        {title: "<?php echo esc_attr($label); ?>",
                                        onClick: function (event) {
                                            props.setAttributes({<?php echo esc_attr($id); ?>: <?php echo esc_js($value_safe); ?>});                                    
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
                                        foreach ($attr['options'] as $value => $label) { 
                                            if ($attr['type'] == 'string') {
                                                $value = '"'.$label.'"';
                                                $tmp = explode('|', $label, 2);
                                                if (count($tmp) > 1) {
                                                    $label = reset($tmp);
                                                    $value = '"'.end($tmp).'"';
                                                }
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
                            foreach ($attr['options'] as $value) { 
                                $value_escaped = in_array($attr['type'], ['number', 'integer', 'boolean']) ? esc_attr($value) : '"'.esc_attr($value).'"'; ?>
                                wp.element.createElement(wp.components.<?php echo $in_toolbar ? 'Toolbar' : ''; ?>Button, {
                                    value: <?php echo esc_js($value_escaped); ?>,
                                    variant: (props.attributes.<?php echo esc_attr($id); ?> === <?php echo esc_js($value_escaped); ?>) ? 'primary' : 'secondary',
                                    onClick: function (event) {
                                        jQuery(event.target).addClass('is-primary').removeClass('is-secondary');
                                        jQuery(event.target).siblings('.is-primary').removeClass('is-primary');
                                        props.setAttributes({<?php echo esc_attr($id); ?>: event.target.value});                                    
                                    },
                                    text: wp.i18n.__(<?php echo esc_js($value_escaped); ?>, "<?php echo esc_attr($textdomain); ?>")
                                }),
                            <?php }
                        }       
                    } 
            }
            ?>
            ),
        ),
    <?php
   }
   
    public function parse_svg($svg) {
        $parsed = "";
        $tags = explode('<', $svg);
        $close = 0;
        foreach ($tags as $key => $tagg) {
            if ($key) {
                list($tag, $more) = explode('>', $tagg, 2);
                $tag_attr = explode(' ', $tag, 2);
                $tag_name = array_shift($tag_attr);
                $tag_attr = reset($tag_attr);
                $tag_attr = str_replace("'", '"', $tag_attr);
                $tag_attr = explode('" ', $tag_attr);
                if (substr($tag_name, 0 , 1) == '/') {
                    // close
                    $close--;
                    $parsed .= '),';
                } else {
                    // open
                    $primitive = ($tag_name == 'svg') ? 'SVG' : ucfirst($tag_name);
                    if (!empty($parsed)) $parsed .= ',';    
                    $parsed .= 'wp.element.createElement(wp.primitives.'.$primitive.','; //{';
                    $close ++;
                    $tag_attrs = [];
                    foreach ($tag_attr as $attr) {
                        list($attr_name, $attr_value) = explode('=', $attr, 2);
                        $tmp_name = explode('-', $attr_name);
                        //$attr_name = array_shift($tmp_name).implode('', array_map('ucfirst', $tmp_name));
                        $attr_value = str_replace('"', '', $attr_value);
                        $attr_value = str_replace("'", '', $attr_value);
                        $attr_value = str_replace("\\", '', $attr_value);
                        $attr_value = str_replace("/", '', $attr_value);
                        $tag_attrs[$attr_name ] = $attr_value;
                    }

                    $parsed .= wp_json_encode($tag_attrs);
                    if (substr(end($tag_attr), -1, 1) == '/') {
                        $close--;
                        $parsed .= '),';
                    }
                }
            }
        }
        /*for ($i=0; $i<=$close;$i++) {
            $parsed .= '),';
        }*/
        $parsed = str_replace('),)', '))', $parsed);
        $parsed = str_replace(',,', ',', $parsed);
        $parsed = $this->fix_jsson_js($parsed);
        //var_dump($parsed); die();

        return $parsed;
    }
}
