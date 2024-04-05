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
    
    public function _edit($args = [], $wrapper = false) {
        $key = $args['name'];
        ob_start();
if ($wrapper) { ?><script id="<?php echo $key; ?>"><?php } ?>
/* generated by wizard-blocks - remove this comment to customize */
<?php
if (!empty($args['attributes'])) {
    foreach ($args['attributes'] as $id => $attr) {
        if (!empty($attr['api']) && is_array($attr['api']) && !empty($attr['api']['path'])) {
            // https://developer.wordpress.org/rest-api/reference/
            if (empty($attr['options']) || is_string($attr['options'])) {
                $api = $attr['api'];
                $var_name = empty($api['name']) ? 'wp_api' : $api['name'];
                if (empty($api['value'])) { $api['value'] = 'index'; }
                $api['label'] = empty($api['label']) ? $api['value'] : $api['label'];
                ?>
var <?php echo $var_name; ?> = <?php echo $var_name; ?> || [];
<?php echo $var_name; ?>['<?php echo $key; ?>'] = <?php echo $var_name; ?>['<?php echo $key; ?>'] || [];
<?php echo $var_name; ?>['<?php echo $key; ?>']['<?php echo $id; ?>'] = [];
wp.apiFetch( { path: '<?php echo $api['path']; ?>' } ).then( ( data ) => {
    if (data && typeof data == 'object') {
        if (data instanceof Array) {
            data.forEach((item, index) => {
                <?php echo $var_name; ?>['<?php echo $key; ?>']['<?php echo $id; ?>'].push({ value: <?php echo $api['value']; ?>, label: <?php echo $api['label']; ?> });
            } );
        } else {
            for (const [index, item] of Object.entries(data)) {
                <?php echo $var_name; ?>['<?php echo $key; ?>']['<?php echo $id; ?>'].push({ value: <?php echo $api['value']; ?>, label: <?php echo $api['label']; ?> });
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
wp.blocks.registerBlockType("<?php echo $key; ?>", {
    <?php
    if (!empty($args['icon']) && substr($args['icon'], 0, 5) == '<svg ') {
    ?>
    icon: { 
        src: <?php echo $this->parse_svg($args['icon']); ?>
    },
    <?php } ?>
    edit(props) {
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
                                        title: wp.i18n.__("Settings", "proto-block")
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
                                        title: wp.i18n.__("Style", "proto-block")
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
                            block: "<?php echo $key; ?>",
                            <?php if (!empty($args['supports'])) { ?>skipBlockSupportAttributes: true, 
                            <?php } ?>attributes: props.attributes,
                        }),
                    )
            );
    },
    save() {
        return null;
    },
});
<?php
if ($wrapper) { ?></script><?php }
        return ob_get_clean();
   }
   
   public function _component($id, $attr = [], $args = []) {
       
       $label = empty($attr['label']) ? ucfirst($id) : $attr['label'];
       
       if (!empty($attr['type']) && $attr['type'] == "object") {
           return;
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
       
       ?>
       wp.element.createElement(
            <?php
            if ($component == 'ButtonGroup') { ?>
                //wp.element.createElement(wp.components.PanelRow, {}, 
                    /*wp.element.createElement(wp.components.BaseControl, { 
                        label: wp.i18n.__("<?php echo $label ?>", "<?php echo $args['textdomain']; ?>"),
                        <?php if (!empty($attr['help'])) { ?>
                            help: wp.i18n.__("<?php echo $attr['help']; ?>","<?php echo $args['textdomain']; ?>"),
                        <?php } ?>
                    },*/
                        wp.element.createElement(wp.components.ButtonGroup, {
                            'aria-label': wp.i18n.__("<?php echo $label; ?>","<?php echo $args['textdomain']; ?>")
                        },
                        <?php 
                        if (!empty($attr['values'])) {
                             //[10, 15, 20, 25, 30, 33, 35, 40, 50, 60, 66, 70, 75, 80, 90, 100]; 
                            foreach ($attr['values'] as $value) { ?>
                                wp.element.createElement(wp.components.Button, {
                                    value: <?php echo $value; ?>,
                                    isPrimary: (props.attributes.<?php echo $id; ?> === <?php echo $value; ?>),
                                    isSecondary: (props.attributes.<?php echo $id; ?> !== <?php echo $value; ?>),
                                    onClick: function (val) {
                                        props.setAttributes({<?php echo $id; ?>: val});
                                    },
                                    title: wp.i18n.__("<?php echo $value; ?>", "<?php echo $args['textdomain']; ?>")
                                }),
                            <?php }
                        }
                        if ($id == 'align') { ?>
                            wp.element.createElement(wp.components.Button, {
                                icon: 'editor-alignleft',
                                value: 'left',
                                isPrimary: (props.attributes.<?php echo $id; ?> === 'left'),
                                isSecondary: true,
                                onClick: function (val) {
                                    props.setAttributes({<?php echo $id; ?>: val});
                                },
                                title: wp.i18n.__('Left', "<?php echo $args['textdomain']; ?>")
                            }),
                            wp.element.createElement(wp.components.Button, {
                                icon: 'editor-aligncenter',
                                value: 'center',
                                isPrimary: (props.attributes.<?php echo $id; ?> === 'center'),
                                isSecondary: true,
                                onClick: function (val) {
                                    props.setAttributes({<?php echo $id; ?>: val});
                                },
                                title: wp.i18n.__('Center', "<?php echo $args['textdomain']; ?>")
                            }),
                            wp.element.createElement(wp.components.Button, {
                                icon: 'editor-alignright',
                                value: 'right',
                                isPrimary: (props.attributes.<?php echo $id; ?> === 'right'),
                                isSecondary: true,
                                onClick: function (val) {
                                    props.setAttributes({<?php echo $id; ?>: val});
                                },
                                title: wp.i18n.__('Right', "<?php echo $args['textdomain']; ?>")
                            })
                            <?php } ?>
                            )                               
                        )
                //)
            //),
            <?php } else { ?>
            <?php echo in_array($component, ['MediaUpload', 'RichText', 'PanelColorSettings']) ? 'wp.blockEditor.' : 'wp.components.'; ?><?php echo $component; ?>,
                {
                    label: wp.i18n.__("<?php echo $label ?>", "<?php echo $args['textdomain']; ?>"),
                    <?php switch($component) {
                        case 'ColorPicker': 
                            $color = '';
                            if (!empty($attr['default'])) { 
                                $color = $attr['default'];
                                ?>
                                defaultValue: "<?php echo $color ?>",
                                <?php
                            }
                            if (!empty($attr['color'])) { $color = $attr['color']; } ?>
                            color: props.attributes.<?php echo $id; ?><?php if (!empty($color)) { echo ' || "'.$color.'"'; } ?>,
                        <?php
                            break;
                        case 'DatePicker':
                        case 'DateTimePicker':
                        case 'TimePicker': 
                            $date = 'new Date()';
                            if (!empty($attr['default'])) { 
                                $date = '"'.$attr['default'].'"';
                            }
                            ?>
                            currentDate: props.attributes.<?php echo $id; ?> || <?php echo $date; ?>,
                        <?php 
                            break;
                        case 'CheckboxControl':
                        case 'ToggleControl': ?>
                            checked: props.attributes.<?php echo $id; ?>,
                        <?php 
                            break;
                        case 'RadioControl': 
                            if (!empty($attr['selected'])) { $attr['default'] = $attr['selected']; }
                            ?>
                            selected: props.attributes.<?php echo $id; ?><?php if (!empty($attr['default'])) { echo ' || '; echo (empty($attr['type']) || $attr['type'] == 'string') ? '"'.$attr['default'].'"' : $attr['default']; } ?>,
                        <?php 
                            break;
                        case 'SelectControl': 
                            $default = '';
                            if (!empty($attr['default'])) {
                                if (!empty($attr['multiple'])) { 
                                    if (is_array($attr['default'])) {
                                        $values = $attr['default'];
                                    } else {
                                        $values = array_filter(array_map('trim', explode(',', $attr['default'])));
                                    }
                                    $default = '[';
                                    foreach ($values as $key => $value) {
                                        $def = (empty($attr['type']) || $attr['type'] == 'string') ? '"'.$attr['default'].'"' : $attr['default'];
                                        if ($key) $default .= ',';
                                        $default .= $def;
                                    }
                                    $default .= ']';
                                } else {
                                    $default = (empty($attr['type']) || $attr['type'] == 'string') ? '"'.$attr['default'].'"' : $attr['default'];
                                }
                            }
                            ?>
                            value: props.attributes.<?php echo $id; ?><?php if (!empty($attr['default'])) { echo ' || '; echo $default; } ?>,
                        <?php 
                            break;
                        case 'RichText':
                        case 'TextControl':
                        default: ?>
                            value: props.attributes.<?php echo $id; ?><?php if (!empty($attr['default'])) { echo ' || '; echo (empty($attr['type']) || $attr['type'] == 'string') ? '"'.$attr['default'].'"' : $attr['default']; } ?>,
                        <?php
                    } ?>
                    onChange: function (val) {
                        <?php
                        if (!empty($attr['sanitize'])) { 
                            switch($attr['sanitize']) {
                                case 'title': ?>
                                    val = val.replace(/[^a-zA-Z0-9_-]/g,"");
                                    <?php break;
                            }
                        }
                        if ($attr['type'] == 'number') { 
                        ?>
                        val = parseInt(val);
                        <?php } ?>
                        //console.log(val);
                        props.setAttributes({<?php echo $id; ?>: val});
                    },
                    <?php if (!empty($attr['options'])) { ?>
                        options: <?php 
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
                                    ?>
                                    {value: <?php echo $value; ?>, label: "<?php echo $label; ?>"},
                                    <?php } 
                                }
                                echo ']';
                            } else {    
                                echo $attr['options'];
                            }
                            ?>,
                    <?php }
                    if (!empty($attr['placeholder'])) { ?>
                        placeholder: "<?php echo $attr['placeholder']; ?>",
                    <?php }
                    if (isset($attr['multiple'])) { ?>
                        multiple: <?php echo $attr['multiple'] ? 'true' : 'false'; ?>,
                    <?php }
                    if (!empty($attr['help'])) { ?>
                        help: wp.i18n.__("<?php echo $attr['help']; ?>","<?php echo $args['textdomain']; ?>"),
                    <?php }
                    if (!empty($attr['tag'])) { ?>
                        tag: "<?php echo $attr['tag']; ?>",
                    <?php }
                    if (!empty($attr['className'])) { ?>
                        className: "<?php echo $attr['className']; ?>",
                    <?php }
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
                        type: "<?php echo $attr['inputType']; ?>",
                    <?php } ?>
                }
            ),
       <?php
        }
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
