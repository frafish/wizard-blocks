<?php

namespace WizardBlocks\Modules\Styles\Traits;

trait Editor {

    function metabox_styles_controls() {

        $wb = \WizardBlocks\Modules\Block\Block::instance();
        if ($wb->is_block_edit() && !empty($_GET['post'])) {
            add_meta_box(
                    'block_styles_meta_box',
                    esc_html__('Styles', 'wizard-blocks'),
                    [$this, 'block_styles_meta_box_callback'],
                    'block'
            );
        }
    }

    // generate new dedicated metabox
    public function block_styles_meta_box_callback($post, $metabox) {

        $post = get_post();
        $wb = \WizardBlocks\Modules\Block\Block::instance();
        $block = $post ? $wb->get_json_data($post->post_name) : [];
        $block_textdomain = $wb->get_block_textdomain($block);
        ?>       
        <h3>Block Styles <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-styles/"><span class="dashicons dashicons-info-outline"></span></a></h3>
        <p>WordPress generates a classname that starts with "is-style-" and ends with the name key (ex: .is-style-dummy).</p>
        
        <span class="dashicons-before dashicons-plus button button-primary attr_add">Add new Block Style</span>
        <div id="style-editor">
            <label for="style-name"><b>Name</b> (type string)</label>
            <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-styles/#register_block_style"><span class="dashicons dashicons-info-outline"></span></a>
            <p class="hint"><i><?php esc_html_e('The identifier of the style used to compute a CSS class.', 'wizard-block'); ?></i></p>
            <p><input type="text" name="style[name]" id="style-name" placeholder="dummy"></p>

            <label for="style-title"><b>Title</b> (type string)</label>
            <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-styles/#register_block_style"><span class="dashicons dashicons-info-outline"></span></a>
            <p class="hint"><i><?php esc_html_e('A human-readable label for the style.', 'wizard-block'); ?></i></p>
            <p><input type="text" name="style[title]" id="style-title" placeholder="Dummy"></p>

            <label for="style-isDefault"><b>Is Default</b> (optional, type boolean)</label>
            <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-styles/#register_block_style"><span class="dashicons dashicons-info-outline"></span></a>
            <p class="hint"><i><?php esc_html_e('Defaults to false. Mark one of the block styles as the default one, should one be missing.', 'wizard-block'); ?></i></p>
            <p><input type="checkbox" name="style[isDefault]" id="style-isDefault"> <?php esc_html_e('This will be default style of this block', 'wizard-block'); ?></p>

            <label for="style-inlineStyle"><b>Inline Style</b> (optional, type string)</label>
            <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-styles/#register_block_style"><span class="dashicons dashicons-info-outline"></span></a>
            <p class="hint"><i><?php esc_html_e('Contains inline CSS code that registers the CSS class required for the style.', 'wizard-block'); ?></i></p>
            <p><textarea placeholder=".wp-block-quote.is-style-blue-quote { color: blue; }" name="style[inlineStyle]" id="style-inlineStyle"></textarea></p>

            <label for="style-styleHandle"><b>Style Handle</b> (optional, type string)</label>
            <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-styles/#register_block_style"><span class="dashicons dashicons-info-outline"></span></a>
            <p class="hint"><i><?php esc_html_e('Contains the handle to an already registered style that should be enqueued in places where block styles are needed. Example:', 'wizard-block'); ?></i></p>
            <p><code>wp_register_style( 'myguten-style', get_template_directory_uri() . '/custom-style.css' );</code></p>
            <p><input type="text" name="style[styleHandle]" id="style-styleHandle" placeholder="myguten-style"></p>

            <?php // ['border' => ['color' => '#f5bc42', 'style' => 'solid', 'width' => '4px', 'radius' => '15px']] ?>
            <label for="style-styleData"><b>Style Data</b> (optional, type array[])</label>
            <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-styles/#register_block_style"><span class="dashicons dashicons-info-outline"></span></a>
            <p class="hint"><i><?php esc_html_e('Contains a theme.json-like notation in an array of style properties.', 'wizard-block'); ?></i></p>
            <p><textarea placeholder='{"border": {"color": "#f5bc42", "style": "solid", "width": "4px", "radius": "15px"}}' name="style[styleData]" id="style-styleData"></textarea></p>
        </div>
        <?php
        if (!empty($block['styles'])) { 
            ?>
            <h3><?php esc_html_e('Current Block Styles', 'wizard-block'); ?></h3>
            <div id="_block_styles" class="repeat_wrapper">
            <?php
            foreach ($block['styles'] as $style_json) { 
                $style_file = wp_json_encode($style_json, JSON_PRETTY_PRINT);
                ?>
                <details class="repeat_attr">
                    <summary class="attr_ops d-flex">                 
                            <span class="attr_name dashicons-before dashicons-editor-expand"> [<?php echo $style_json['name']; ?>] <?php echo $style_json['title']; ?> <?php !empty($style_json['is_default']) ? esc_html_e(' - Default', 'wizard-block') : ''; ?></span>                        
                            <abbr title="<?php esc_html_e('Remove', 'wizard-block'); ?>" class="button button-danger attr_remove pull-right"><span class="dashicons dashicons-trash"></span></abbr>
                            <abbr title="<?php esc_html_e('Edit', 'wizard-block'); ?>" class="button button-danger attr_edit pull-right"><span class="dashicons dashicons-edit"></span></abbr>
                    </summary>
                    <label for="_block_styles_delete-<?php echo $style_json['name']; ?>"><input class="d-none style-delete" type="checkbox" id="_block_styles_delete-<?php echo $style_json['name']; ?>" name="_block_styles_delete[<?php echo $style_json['name']; ?>]"> <?php esc_html_e('Delete this style on save', 'wizard-blocks'); ?></label>
                    <textarea class="_block_styles" id="_block_styles_<?php echo esc_attr($style_json['name']); ?>" name="_block_styles[<?php echo esc_attr($style_json['name']); ?>]"><?php echo $style_file; ?></textarea>
              </details>
              <?php
            } ?>
            </div>
            <?php
        }
    }
}
