<?php

namespace WizardBlocks\Modules\Styles\Traits;
if ( ! defined( 'ABSPATH' ) ) exit;
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
        <h3><?php esc_html_e('Block Styles', 'wizard-blocks'); ?> <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-styles/"><span class="dashicons dashicons-info-outline"></span></a></h3>
        <p><?php esc_html_e('WordPress generates a classname that starts with "is-style-" and ends with the name key (ex: .is-style-dummy).', 'wizard-blocks'); ?></p>
        
        <span class="dashicons-before dashicons-plus button button-primary attr_add"><?php esc_html_e('Add new Block Style', 'wizard-blocks'); ?></span>
        <div id="style-editor">
            <label for="style-name"><b><?php esc_html_e('Name', 'wizard-blocks'); ?></b> (<?php esc_html_e('type string', 'wizard-blocks'); ?>)</label>
            <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-styles/#register_block_style"><span class="dashicons dashicons-info-outline"></span></a>
            <p class="hint"><i><?php esc_html_e('The identifier of the style used to compute a CSS class.', 'wizard-blocks'); ?></i></p>
            <p><input type="text" name="style[name]" id="style-name" placeholder="<?php echo esc_attr__( 'dummy', 'wizard-blocks' ); ?>"></p>

            <label for="style-title"><b><?php esc_html_e('Title', 'wizard-blocks'); ?></b> (<?php esc_html_e('type string', 'wizard-blocks'); ?>)</label>
            <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-styles/#register_block_style"><span class="dashicons dashicons-info-outline"></span></a>
            <p class="hint"><i><?php esc_html_e('A human-readable label for the style.', 'wizard-blocks'); ?></i></p>
            <p><input type="text" name="style[title]" id="style-title" placeholder="<?php echo esc_attr__( 'Dummy', 'wizard-blocks' ); ?>"></p>

            <label for="style-isDefault"><b><?php esc_html_e('Is Default', 'wizard-blocks'); ?></b> (<?php esc_html_e('optional, type boolean', 'wizard-blocks'); ?>)</label>
            <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-styles/#register_block_style"><span class="dashicons dashicons-info-outline"></span></a>
            <p class="hint"><i><?php esc_html_e('Defaults to false. Mark one of the block styles as the default one, should one be missing.', 'wizard-blocks'); ?></i></p>
            <p><input type="checkbox" name="style[isDefault]" id="style-isDefault"> <?php esc_html_e('This will be default style of this block', 'wizard-blocks'); ?></p>

            <label for="style-inlineStyle"><b><?php esc_html_e('Inline Style', 'wizard-blocks'); ?></b> (<?php esc_html_e('optional, type string', 'wizard-blocks'); ?>)</label>
            <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-styles/#register_block_style"><span class="dashicons dashicons-info-outline"></span></a>
            <p class="hint"><i><?php esc_html_e('Contains inline CSS code that registers the CSS class required for the style.', 'wizard-blocks'); ?></i></p>
            <p><textarea placeholder="<?php echo esc_attr__( '.wp-block-quote.is-style-blue-quote { color: blue; }', 'wizard-blocks' ); ?>" name="style[inlineStyle]" id="style-inlineStyle"></textarea></p>

            <label for="style-styleHandle"><b><?php esc_html_e('Style Handle', 'wizard-blocks'); ?></b> (<?php esc_html_e('optional, type string', 'wizard-blocks'); ?>)</label>
            <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-styles/#register_block_style"><span class="dashicons dashicons-info-outline"></span></a>
            <p class="hint"><i><?php esc_html_e('Contains the handle to an already registered style that should be enqueued in places where block styles are needed. Example:', 'wizard-blocks'); ?></i></p>
            <p><code>wp_register_style( 'myguten-style', get_template_directory_uri() . '/custom-style.css' );</code></p>
            <p><input type="text" name="style[styleHandle]" id="style-styleHandle" placeholder="<?php echo esc_attr__( 'myguten-style', 'wizard-blocks' ); ?>"></p>

            <?php // ['border' => ['color' => '#f5bc42', 'style' => 'solid', 'width' => '4px', 'radius' => '15px']] ?>
            <label for="style-styleData"><b><?php esc_html_e('Style Data', 'wizard-blocks'); ?></b> (<?php esc_html_e('optional, type array[]', 'wizard-blocks'); ?>)</label>
            <a target="_blank" href="https://developer.wordpress.org/block-editor/reference-guides/block-api/block-styles/#register_block_style"><span class="dashicons dashicons-info-outline"></span></a>
            <p class="hint"><i><?php esc_html_e('Contains a theme.json-like notation in an array of style properties.', 'wizard-blocks'); ?></i></p>
            <p><textarea placeholder='<?php echo esc_attr__( '{"border": {"color": "#f5bc42", "style": "solid", "width": "4px", "radius": "15px"}}', 'wizard-blocks' ); ?>' name="style[styleData]" id="style-styleData"></textarea></p>
        </div>
        <?php
        if (!empty($block['styles'])) { 
            ?>
            <h3><?php esc_html_e('Current Block Styles', 'wizard-blocks'); ?></h3>
            <div id="_block_styles" class="repeat_wrapper">
            <?php
            foreach ($block['styles'] as $style_json) { 
                $style_file = wp_json_encode($style_json, JSON_PRETTY_PRINT);
                ?>
                <details class="repeat_attr">
                    <summary class="attr_ops d-flex">                 
                            <span class="attr_name dashicons-before dashicons-editor-expand"> [<?php echo $style_json['name']; ?>] <?php echo $style_json['title']; ?><?php if ( ! empty( $style_json['is_default'] ) ) { echo ' - ' . esc_html__( 'Default', 'wizard-blocks' ); } ?></span>                        
                            <abbr title="<?php esc_html_e('Remove', 'wizard-blocks'); ?>" class="button button-danger attr_remove pull-right"><span class="dashicons dashicons-trash"></span></abbr>
                            <abbr title="<?php esc_html_e('Edit', 'wizard-blocks'); ?>" class="button button-danger attr_edit pull-right"><span class="dashicons dashicons-edit"></span></abbr>
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
