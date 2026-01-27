<?php

namespace WizardBlocks\Modules\Editor;

use WizardBlocks\Base\Module_Base;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Editor extends Module_Base {

    /**
     * SEO constructor.
     *
     * @since 1.0.1
     * @param array $args
     */
    public function __construct() {
        parent::__construct();
        
        //add_filter( 'block_editor_settings_all', [$this, 'block_editor_settings_all'] );
        
        add_action('init', function () {
            if ($this->is_gutenberg_editor()) {
                
                // Enqueue jQuery UI Resizable
                wp_enqueue_script('jquery-ui-core');
                wp_enqueue_script('jquery-ui-resizable');
                wp_enqueue_style('wp-jquery-ui-dialog');
                
                $this->enqueue_style('block-editor', 'assets/css/block-editor.css');
                $this->enqueue_script('block-editor', 'assets/js/block-editor.js', ['jquery']);
            }
        });

    }
    
    function is_gutenberg_editor() {
        if (is_admin()) {
            if (isset($_GET['action']) && $_GET['action'] == 'edit') {
                return true;
            }
        }
        return false;
    }
    
    function block_editor_settings_all( $settings ) {
        return $settings;
    }


}
