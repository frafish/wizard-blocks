<?php

namespace WizardBlocks\Modules\Shortcode;

use WizardBlocks\Core\Utils;
use WizardBlocks\Base\Module_Base;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Shortcode extends Module_Base {

    public function __construct() {
        add_shortcode( 'block', [$this, 'render_block'] );
    }   
    
   function render_block( $atts ) {
	$attr = shortcode_atts( array(
		'name' => 'core/paragraph', // required
		'attributes' => '',
                'content' => '',
	), $atts, 'block' );
        $block_content = '';
        if ($attr['name']) {
            $block = \WP_Block_Type_Registry::get_instance()->get_registered($attr['name']);
            if ($block && $block->is_dynamic()) {
                $attributes = json_decode($attr['attributes']);
                $attributes = $attributes ? $attributes : [];
                ob_start();
                $render = $block->render_callback;
                echo $render($attributes, $content, $block);
                $block_content = ob_get_clean();
            }
        }
	return $block_content;
    }
}
