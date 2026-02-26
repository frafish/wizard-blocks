<?php
namespace WizardBlocks\Modules\Variations;

use WizardBlocks\Base\Module_Base;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Variations extends Module_Base {

    use Traits\Editor;
    use Traits\Save;
    
    //const VARIATIONS_FOLDER = 'variations';
        
    /**
     * Variations constructor.
     *
     * @since 1.0.1
     */
    public function __construct() {
        parent::__construct();
 
        /* LOAD ALL BLOCKS VARIATIONS */
        //add_filter( 'get_block_type_variations', [$this, 'add_custom_variation'], 10, 2 );
        
        add_action('add_meta_boxes', [$this, 'metabox_variations_controls']);
        
        add_filter('wizard_blocks/before_save', [$this, 'update_variations'], 20, 3);
        
        if (is_admin()) {
            add_action('init', function () {
                $wb = \WizardBlocks\Modules\Block\Block::instance();
                if ($wb->is_block_edit()) {
                    $this->enqueue_style('block-variations', 'assets/css/wb-block-variations.css');
                    $this->enqueue_script('block-variations', 'assets/js/wb-block-variations.js', ['jquery']);
                }
            });
        }
    }
    
    
    //https://developer.wordpress.org/block-editor/reference-guides/block-api/block-variations/
    function add_custom_variation( $variations, $block_type ) {
        
        $wblocks = \WizardBlocks\Modules\Block\Block::instance();
        
        list($block_textdomain, $block_name) = explode('/', $block_type->name, 2);
        $block_dir = $wblocks->get_blocks_dir( $block_name, $block_textdomain );
        //var_dump($block_dir);
        if (!empty($block_dir)) {
            $block_variations_folder = $block_dir.DIRECTORY_SEPARATOR.self::VARIATIONS_FOLDER;
            if (is_dir($block_variations_folder)) {
                //var_dump($block_variations_folder);
                $block_variations = glob($block_variations_folder.DIRECTORY_SEPARATOR.'*.json');
                foreach ($block_variations as $variation) {
                    $variation_json = $wblocks->get_filesystem()->get_contents($variation);
                    $block_variation = json_decode($variation_json, true);
                    $variations[] = $block_variation;
                }
                //var_dump($variations); die();
            }
        }
        
        return $variations;
    }
}