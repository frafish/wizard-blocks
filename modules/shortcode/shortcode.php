<?php

namespace WizardBlocks\Modules\Shortcode;

use WizardBlocks\Core\Utils;
use WizardBlocks\Base\Module_Base;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Shortcode extends Module_Base {

    //TODO: add Contextual Menu item "Copy Shortcode" in Block Toolbar
    
    public function __construct() {
        add_shortcode('block', [$this, 'render_block']);

        add_action('wizard/blocks/action/btn', function ($block, $wblocks) {
            //var_dump($block); die();
            if (!empty($block['render']) || !empty($block['render_callback'])) {
                ?>
                <a class="btn button dashicons-before dashicons-shortcode" href="<?php echo $wblocks->get_action_url('action=shortcode&block=' . $block['name']); ?>" title="<?php esc_attr_e('Shortcode', 'wizard-blocks'); ?>"></a>
            <?php
            }
        }, 10, 2);
        add_action('wizard/blocks/action', function ($wblocks) {
            switch ($_GET['action']) {
                case 'shortcode':
                    if (!empty($_GET['block'])) {
                        $block_name = $_GET['block'];

                        //$block = \WP_Block_Type_Registry::get_instance()->get_registered($block_name);
                        $block = $wblocks->get_registered_block($block_name);
                        $attributes = [];
                        if ($block && !empty($block['attributes'])) {
                            $attributes_keys = array_keys($block['attributes']);
                            foreach ($attributes_keys as $attr) {
                                if (!empty($block['example']['attributes'][$attr])) {
                                    $attributes[$attr] = $block['example']['attributes'][$attr];
                                } else {
                                    switch ($block['attributes'][$attr]['type']) {
                                        case 'string':
                                            $attributes[$attr] = '';
                                            break;
                                        case 'boolean':
                                            $attributes[$attr] = false;
                                            break;
                                    }
                                }
                            }
                            $attributes = wp_json_encode($attributes);
                        }
                        $shortcode = '[block name="' . $block_name . '"' . ($attributes ? ' attributes=\'' . esc_attr($attributes) . '\'' : '') . ']';

                        $message = __('Here the Block Shortcode:', 'wizard-blocks') . '<br>';
                        $message .= $shortcode;
                        $wblocks->_notice($message);
                    }
                    break;
            }
        });
    }

    public function render_block($atts) {
        $attr = shortcode_atts(array(
            'name' => 'core/paragraph', // required
            'attributes' => '',
            'content' => '',
        ), $atts, 'block');
        $block_content = '';
        if ($attr['name']) {
            $block = \WP_Block_Type_Registry::get_instance()->get_registered($attr['name']);
            if ($block && $block->is_dynamic()) {
                $content = $attr['content'];
                $attributes = json_decode($attr['attributes'], true);
                $attributes = $attributes ? $attributes : [];
                ob_start();
                $render = $block->render_callback;
                echo $render($attributes, $content, $block);
                //$block->render($attributes, $content); // FIX: native is bugged, should pass $this as 3rd parameter
                $block_content = ob_get_clean();

                $this->enqueue_block_assets($block);
            }
        }
        return $block_content;
    }

    public function enqueue_block_assets($block) {
        //var_dump($block);
        // frontend assets
        $styles = [];
        if (!empty($block->style)) { $styles = array_merge($styles, is_array($block->style) ? $block->style : [$block->style]); }
        if (!empty($block->style_handles)) { $styles = array_merge($styles, is_array($block->style_handles) ? $block->style_handles : [$block->style_handles]); }
        if (!empty($block->viewStyle)) { $styles = array_merge($styles, is_array($block->viewStyle) ? $block->viewStyle : [$block->viewStyle]); }
        if (!empty($block->view_style_handles)) { $styles = array_merge($styles, is_array($block->view_style_handles) ? $block->view_style_handles : [$block->view_style_handles]); }
        //var_dump($styles);
        foreach ($styles as $style) {
            wp_enqueue_style($style);
        }
        
        $scripts = [];
        if (!empty($block->script)) { $scripts = array_merge($scripts, is_array($block->script) ? $block->script : [$block->script]); }
        if (!empty($block->script_handles)) { $scripts = array_merge($scripts, is_array($block->script_handles) ? $block->script_handles : [$block->script_handles]); }
        if (!empty($block->viewScript)) { $scripts = array_merge($scripts, is_array($block->viewScript) ? $block->viewScript : [$block->viewScript]); }
        if (!empty($block->view_script_handles)) { $scripts = array_merge($scripts, is_array($block->view_script_handles) ? $block->view_script_handles : [$block->view_script_handles]); }
        if (!empty($block->viewScriptModule)) { $scripts = array_merge($scripts, is_array($block->viewScriptModule) ? $block->viewScriptModule : [$block->viewScriptModule]); }
        if (!empty($block->view_script_module_ids)) { $scripts = array_merge($scripts, is_array($block->view_script_module_ids) ? $block->view_script_module_ids : [$block->view_script_module_ids]); }
        //var_dump($scripts);
        foreach ($scripts as $script) {
            wp_enqueue_script($script);
        }

    }
}
