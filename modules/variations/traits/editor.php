<?php

namespace WizardBlocks\Modules\Variations\Traits;

trait Editor {

    function metabox_variations_controls() {

        $wb = \WizardBlocks\Modules\Block\Block::instance();
        if ($wb->is_block_edit() && !empty($_GET['post'])) {
            add_meta_box(
                    'block_variations_meta_box',
                    esc_html__('Variations', 'wizard-blocks'),
                    [$this, 'block_variations_meta_box_callback'],
                    'block'
            );
        }
    }

    // generate new dedicated metabox
    public function block_variations_meta_box_callback($post, $metabox) {

        $post = get_post();
        $wb = \WizardBlocks\Modules\Block\Block::instance();
        $block = $post ? $wb->get_block_json($post->post_name) : [];
        $block_textdomain = $wb->get_block_textdomain($block);
        //$basepath = $wb->get_blocks_dir($post->post_name, $block_textdomain);

        //$path = $wb->get_blocks_dir($post->post_name);
        //$path_variations = $path . DIRECTORY_SEPARATOR . self::VARIATIONS_FOLDER . DIRECTORY_SEPARATOR;
        ?>       
        <h3><?php esc_html_e('Block Variation', 'wizard-block'); ?></h3>

        <span class="dashicons-before dashicons-plus button button-primary attr_add"><?php esc_html_e('Add new Block Variation', 'wizard-block'); ?></span>

        <div id="variation-editor">
            <label for="variation-name"><b>Name</b> (type string)</label>
            <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-variations/#defining-a-block-variation"><span class="dashicons dashicons-info-outline"></span></a>
            <p class="hint"><i><?php esc_html_e('A unique and machine-readable name.', 'wizard-block'); ?></i></p>
            <p><input type="text" name="variation[name]" id="variation-name" placeholder="<?php echo esc_attr($post->post_name); ?>"></p>

            <label for="variation-name"><b>Title</b> (optional, type string)</label>
            <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-variations/#defining-a-block-variation"><span class="dashicons dashicons-info-outline"></span></a>
            <p class="hint"><i><?php esc_html_e('A human-readable variation title.', 'wizard-block'); ?></i></p>
            <p><input type="text" name="variation[title]" id="variation-title" placeholder="<?php echo esc_attr($block['title']); ?>"></p>

            <label for="variation-description"><b>Description</b> (optional, type string)</label>
            <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-variations/#defining-a-block-variation"><span class="dashicons dashicons-info-outline"></span></a>
            <p class="hint"><i><?php esc_html_e('A human-readable variation description.', 'wizard-block'); ?></i></p>
            <p><textarea name="variation[description]" id="variation-description" placeholder="<?php echo !empty($block['description']) ? esc_attr($block['description']) : ''; ?>"></textarea></p>

            <label for="variation-category"><b>Category</b> (optional, type string)</label>
            <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-variations/#defining-a-block-variation"><span class="dashicons dashicons-info-outline"></span></a>
            <p class="hint"><i><?php esc_html_e('A category classification used in search interfaces to arrange block types by category.', 'wizard-block'); ?></i></p>
            <p><select name="variation[category]" id="variation-category">
            <?php
            $block_editor_context = new \WP_Block_Editor_Context(
                    array(
                'name' => 'core/customize-widgets',
                    )
            );
            $block_categories = get_block_categories($block_editor_context);
            foreach ($block_categories as $cat) {
                $selected = (!empty($block['category']) && $block['category'] == $cat['slug']) ? ' selected' : '';
                echo '<option value="' . esc_attr($cat['slug']) . '"' . esc_attr($selected) . '>' . esc_html($cat['title']) . '</option>';
            }
            ?>
            </select></p>

            <label for="variation-keywords"><b>Keywords</b> (optional, type string[])</label>
            <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-variations/#defining-a-block-variation"><span class="dashicons dashicons-info-outline"></span></a>
            <p class="hint"><i><?php esc_html_e('An array of terms (which can be translated) that help users discover the variation while searching.', 'wizard-block'); ?></i></p>
            <p><input type="text" name="variation[keywords]" id="variation-keywords" placeholder="red, blue, green"></p>



            <label for="variation-innerBlocks"><b>Inner Blocks</b> (optional, type Array[])</label>
            <a target="_blank" href="https://developer.wordpress.org/news/2023/08/an-introduction-to-block-variations/#creating-a-variation-with-inner-blocks"><span class="dashicons dashicons-info-outline"></span></a>
            <p class="hint"><i><?php esc_html_e('Initial configuration of nested blocks.', 'wizard-block'); ?></i></p>
            <p><textarea placeholder="[ [ 'core/heading', { level: 3, placeholder: 'Heading' } ], [ 'core/paragraph', { placeholder: 'Enter content here...' } ]" name="variation[innerBlocks]" id="variation-innerBlocks"></textarea></p>

            <label for="variation-isDefault"><b>Is Default</b> (optional, type boolean)</label>
            <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-variations/#using-isdefault"><span class="dashicons dashicons-info-outline"></span></a>
            <p class="hint"><i><?php esc_html_e('Defaults to false. Indicates whether the current variation is the default one.', 'wizard-block'); ?></i></p>
            <p><input type="checkbox" name="variation[isDefault]" id="variation-isDefault"> <?php esc_html_e('This will be default version of this block', 'wizard-block'); ?></p>


            <label for="variation-isActive"><b>Is Active</b> (optional, type <s>Function</s>|string[])</label>
            <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-variations/#using-isactive"><span class="dashicons dashicons-info-outline"></span></a>
            <p class="hint"><i><?php esc_html_e('A function or an array of block attributes that is used to determine if the variation is active when the block is selected. The function accepts blockAttributes and variationAttributes.', 'wizard-block'); ?></i></p>
            <p><textarea placeholder="textColor, backgroundColor" name="variation[isActive]" id="variation-isActive"></textarea></p>


            <label for="variation-icon"><b>Icon</b> (optional, type string | Object)</label>
            <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-variations/#defining-a-block-variation"><span class="dashicons dashicons-info-outline"></span></a>
            <p class="hint"><i><?php esc_html_e('An icon helping to visualize the variation. It can have the same shape as the block type.', 'wizard-block'); ?></i></p>
            <?php $wb->_block_icon_selector('', $block['name'],'variation_icon'); ?>

            <label for="variation-scope"><b>Scope</b> (optional, type WPBlockVariationScope[])</label>
            <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-variations/#using-isdefault"><span class="dashicons dashicons-info-outline"></span></a>
            <p class="hint"><i><?php esc_html_e('Defaults to block and inserter. The list of scopes where the variation is applicable. Available options include:', 'wizard-block'); ?></i></p>
            <p><input type="checkbox" name="variation[scope][block]" id="variation-scope-block"> <?php esc_html_e('Block: Used by blocks to filter specific block variations. Columns and Query blocks have such variations, which are passed to the experimental BlockVariationPicker component. This component handles displaying the variations and allows users to choose one of them.', 'wizard-block'); ?></p>
            <p><input type="checkbox" name="variation[scope][inserter]" id="variation-scope-inserter"> <?php esc_html_e('Inserter: Block variation is shown on the inserter.', 'wizard-block'); ?></p>
            <p><input type="checkbox" name="variation[scope][transform]" id="variation-scope-transform"> <?php esc_html_e('Transform: Block variation is shown in the component for variation transformations.', 'wizard-block'); ?></p>

            <?php 

            if (!empty($block['attributes'])) { ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th colspan="2"><label for="variation-example"><b><?php esc_html_e('Example', 'wizard-blocks'); ?></b> (optional, type Object)</label> <p class="hint">Provides structured data for the block preview. Set to undefined to disable the preview. See the Block Registration API for more details.</p></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($block['attributes'] as $akey => $attr) { 
                    $this->_e_attribute_row($attr, $akey, 'example');
                    } ?> 
                    </tbody>
                </table>

                <?php
            }

            if (!empty($block['attributes'])) { ?>
                <table class="widefat striped" style="margin-top: 15px">
                    <thead>
                        <tr>
                            <th colspan="2"><label for="variation-attributes"><b><?php esc_html_e('Attributes', 'wizard-blocks'); ?></b> (optional, type Object)</label> <p class="hint"><?php esc_html_e('Values that override block attributes.', 'wizard-block'); ?></p></th>
                        </tr>
                    </thead>
                    <tbody>
                <?php foreach ($block['attributes'] as $akey => $attr) { 
                    $this->_e_attribute_row($attr, $akey, 'attributes');
                } ?> 
                    </tbody>
                </table>

                <?php
            }
           ?>
        </div>
           
        <?php
        if (!empty($block['variations'])) { //is_dir($path_variations)) {
            //$variations = glob($path_variations.'*.json');
            //var_dump($variations);
            ?>
            <h3><?php esc_html_e('Current Block Variations', 'wizard-block'); ?></h3>
            <div id="_block_variations" class="repeat_wrapper">
            <?php
            foreach ($block['variations'] as $variation) { 
                $variation_file = $code = wp_json_encode($variation, JSON_PRETTY_PRINT); //$wb->get_filesystem()->get_contents($variation);
                $variation_json = $variation; //json_decode($variation_file, true);            
                ?>
                <details class="repeat_attr">
                    <summary class="attr_ops d-flex">                 
                            <span class="attr_name dashicons-before dashicons-editor-expand"> [<?php echo $variation_json['name']; ?>] <?php echo $variation_json['title']; ?> <?php !empty($variation_json['default']) ? esc_html_e(' - Default', 'wizard-block') : ''; ?></span>                        
                            <abbr title="<?php esc_html_e('Remove', 'wizard-block'); ?>" class="button button-danger attr_remove pull-right"><span class="dashicons dashicons-trash"></span></abbr>
                            <abbr title="<?php esc_html_e('Edit', 'wizard-block'); ?>" class="button button-danger attr_edit pull-right"><span class="dashicons dashicons-edit"></span></abbr>
                    </summary>
                    <label for="_block_variations_delete-<?php echo $variation_json['name']; ?>"><input class="d-none variation-delete" type="checkbox" id="_block_variations_delete-<?php echo $variation_json['name']; ?>" name="_block_variations_delete[<?php echo $variation_json['name']; ?>]"> <?php esc_html_e('Delete this variation on save', 'wizard-blocks'); ?></label>
                    <textarea class="_block_variations" id="_block_variations_<?php echo esc_attr($variation_json['name']); ?>" name="_block_variations[<?php echo esc_attr($variation_json['name']); ?>]"><?php echo $variation_file; ?></textarea>
              </details>
              <?php
            } ?>
            </div>
            <?php
        }
    }
    
    function _e_attribute_row($attr, $akey, $field) {
        ?>
        <tr>
            <td>
                <abbr title="<?php echo $akey; ?>"><?php echo empty($attr['label']) ? $akey : $attr['label']; ?></abbr>
                <?php if (!empty($attr['help'])) { ?>
                <p class="hint"><i><?php echo $attr['help']; ?></i></p>
                <?php } ?>
            </td>
            <?php
            switch ($attr['type']) {
                case 'boolean': ?>
                    <td><input type="checkbox" id="variation-<?php echo $field; ?>-<?php echo $akey; ?>" name="variation[<?php echo $field; ?>][<?php echo $akey; ?>]"></td>
                    <?php
                    break;
                case 'number':
                case 'integer': ?>
                    <td><input type="number" id="variation-<?php echo $field; ?>-<?php echo $akey; ?>" name="variation[<?php echo $field; ?>][<?php echo $akey; ?>]" placeholder="<?php echo esc_attr($akey); ?>" value=""></td>
                    <?php
                    break;
                case 'array':
                case 'object':
                    //$attr['default'] = wp_json_encode($attr['default'], JSON_PRETTY_PRINT); ?>
                    <td><textarea id="variation-<?php echo $field; ?>-<?php echo $akey; ?>" name="variation[<?php echo $field; ?>][<?php echo $akey; ?>]" placeholder="<?php echo esc_attr($akey); ?>"></textarea></td>
                    <?php
                    break;
                case 'string':
                default: ?>
                    <td><input type="text" id="variation-<?php echo $field; ?>-<?php echo $akey; ?>" name="variation[<?php echo $field; ?>][<?php echo $akey; ?>]" placeholder="<?php echo esc_attr($akey); ?>" value=""></td>
            <?php } ?>
        </tr>
        <?php
    }
}
