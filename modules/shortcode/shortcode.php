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
                <a class="btn button dashicons-before dashicons-shortcode" href="<?php echo esc_url($wblocks->get_action_url('action=shortcode&block=' . $block['name'])); ?>" title="<?php esc_attr_e('Shortcode', 'wizard-blocks'); ?>"></a>
            <?php
            }
        }, 10, 2);
        add_action('wizard/blocks/action', [$this, 'exec_actions']);
    }
    
    public function get_default_attributes($block) {
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
            if (isset($attributes['preview'])) {
                unset($attributes['preview']);
            }
            $attributes = wp_json_encode($attributes);
        }
        return $attributes;
    }
    
    public function generate_shortcode($block_name, $wblocks) {
        $block = $wblocks->get_registered_block($block_name);
        //var_dump($block); die();
        $attributes = $this->get_default_attributes($block);
        $shortcode = '[block name="' . $block_name . '"' . ($attributes ? ' attributes=\'' . esc_attr($attributes) . '\'' : '') . ']';
        if (!empty($block['allowed_blocks'])) {
            foreach ($block['allowed_blocks'] as $sublock_name) {
                $sublock = $wblocks->get_registered_block($sublock_name);
                $shortcode .= $this->generate_shortcode($sublock, $wblocks);
            }
            $shortcode .= '[/block]';
        }
        return $shortcode;
    }
    
    public function exec_actions($wblocks) {
        if (!empty($_GET['action'])) {
            switch ($_GET['action']) {
                case 'shortcode':
                    if (!empty($_GET['block'])) {
                        $block_name = sanitize_text_field(wp_unslash($_GET['block']));
                        //$block = \WP_Block_Type_Registry::get_instance()->get_registered($block_name);
                        $shortcode = $this->generate_shortcode($block_name, $wblocks);

                        $message = __('Here the Block Shortcode:', 'wizard-blocks') . '<br>';
                        $message .= '<b class="block-shortcode dashicons-before dashicons-clipboard" onClick="navigator.clipboard.writeText(this.innerText);">'.$shortcode.'</b>';
                        $wblocks->_notice($message);
                    }
                    break;
            }
        }
    }

    public function render_block($atts, $content = '') {
        $attr = shortcode_atts(array(
            'name' => 'core/paragraph', // required
            'attributes' => '',
        ), $atts, 'block');
        $block_content = '';
        if ($attr['name']) {
            $block = \WP_Block_Type_Registry::get_instance()->get_registered($attr['name']);
            if ($block && $block->is_dynamic()) {
                $content = do_shortcode($content);
                $attributes = json_decode($attr['attributes'], true);
                $attributes = $attributes ? $attributes : [];
                
                $modules = \WizardBlocks\Plugin::instance()->modules_manager;
                $blocks = $modules->get_modules('block');
                $block_content = $blocks->render($attributes, $content, $block);
            }
        }
        return $block_content;
    }
    
}
