<?php
namespace WizardBlocks\Modules\Styles;

use WizardBlocks\Base\Module_Base;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Styles extends Module_Base {

    use Traits\Editor;
    use Traits\Save;

    /**
     * Styles constructor.
     *
     * @since 1.0.1
     */
    public function __construct() {
        parent::__construct();
 
        add_action('add_meta_boxes', [$this, 'metabox_styles_controls']);
        
        add_filter('wizard_blocks/before_save', [$this, 'update_styles'], 20, 3);
        
        if (is_admin()) {
            add_action('init', function () {
                $wb = \WizardBlocks\Modules\Block\Block::instance();
                if ($wb->is_block_edit()) {
                    $this->enqueue_style('block-styles', 'assets/css/wb-block-styles.css');
                    $this->enqueue_script('block-styles', 'assets/js/wb-block-styles.js', ['jquery']);
                }
            });
        }
    }
    
}