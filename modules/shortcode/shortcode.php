<?php

namespace WizardBlocks\Modules\Shortcode;

use WizardBlocks\Core\Utils;
use WizardBlocks\Base\Module_Base;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Shortcode extends Module_Base {

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
        //var_dump($attr);
        $block_content = '';
        if ($attr['name']) {
            $block = \WP_Block_Type_Registry::get_instance()->get_registered($attr['name']);
            //var_dump($block);
            if ($block && $block->is_dynamic()) {
                $content = $attr['content'];
                $attributes = json_decode($attr['attributes'], true);
                $attributes = $attributes ? $attributes : [];
                ob_start();
                $render = $block->render_callback;
                echo $render($attributes, $content, $block);
                //$block->render($attributes, $content);
                $block_content = ob_get_clean();

                $this->enqueue_block_assets($block);
            }
        }
        return $block_content;
    }

    public function enqueue_block_assets($block) {
        // frontend assets
        if (!empty($block->style)) {
            $styles = is_array($block->style) ? $block->style : [$block->style];
            foreach ($styles as $style) {
                wp_enqueue_style($style);
            }
        }
        if (!empty($block->viewStyle)) {
            $styles = is_array($block->viewStyle) ? $block->viewStyle : [$block->viewStyle];
            foreach ($styles as $style) {
                wp_enqueue_style($style);
            }
        }
        if (!empty($block->script)) {
            $scripts = is_array($block->script) ? $block->script : [$block->script];
            foreach ($scripts as $script) {
                wp_enqueue_script($script);
            }
        }
        if (!empty($block->viewScript)) {
            $scripts = is_array($block->viewScript) ? $block->viewScript : [$block->viewScript];
            foreach ($scripts as $script) {
                wp_enqueue_script($script);
            }
        }
        if (!empty($block->viewScriptModule)) {
            $scripts = is_array($block->viewScriptModule) ? $block->viewScriptModule : [$block->viewScriptModule];
            foreach ($scripts as $script) {
                wp_enqueue_script($script);
            }
        }
    }
}
