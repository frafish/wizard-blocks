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
        
        add_action('wizard/blocks/action/btn', function($block, $wblocks){
            ?>
            <a class="btn button dashicons-before dashicons-shortcode" href="<?php echo $wblocks->get_action_url('action=shortcode&block='.$block['name']); ?>" title="<?php esc_attr_e('Shortcode', 'wizard-blocks'); ?>"></a>
        <?php }, 10, 2);
        add_action('wizard/blocks/action', function($wblocks){
            switch ($_GET['action']) {
                case 'shortcode':
                    if (!empty($_GET['block'])) {
                        $block_name = $_GET['block'];
                        
                        $shortcode = '[block name="'.$block_name.'"]';
                        
                        $message = __('Here the Block Shortcode:', 'wizard-blocks').'<br>';
                        $message .= $shortcode;
                        $wblocks->_notice($message);
                    }
                    break;
            }
        });
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
