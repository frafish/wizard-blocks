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
        
        //add_action('init', [$this, 'unregister_blocks_disabled'], 99);
        add_filter( 'allowed_block_types_all', [$this, 'allowed_block_types'], 10, 2 );

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
    
    public function allowed_block_types($allowed_block_types, $block_editor_context) {
        if (!isset($_GET['post_type']) || $_GET['post_type'] != 'block') {
            $blocks_disabled = get_option(\WizardBlocks\Modules\Admin\Admin::$blocks_disabled_key);
            //var_dump($blocks_disabled); die();
            if (!empty($blocks_disabled)) {
                $registered_blocks = \WP_Block_Type_Registry::get_instance()->get_all_registered();
                if (is_bool($allowed_block_types)) $allowed_block_types = [];
                foreach ($registered_blocks as $name => $block_obj) {
                    if (!in_array($name, $blocks_disabled)) {
                        $allowed_block_types[] = $name;
                    }
                }
            }
        }
        //var_dump($allowed_block_types); die();
        return $allowed_block_types;
    }
    
    public function unregister_blocks_disabled() {
        if ($this->is_block_edit()) {
            $blocks_disabled = get_option(\WizardBlocks\Modules\Admin\Admin::$blocks_disabled_key);
            if (!empty($blocks_disabled)) {
                foreach ($blocks_disabled as $block_name) {
                    if (\WP_Block_Type_Registry::get_instance()->is_registered($block_name)) {
                        $registered_blocks = \WP_Block_Type_Registry::get_instance()->unregister($block_name);
                    }
                }
                add_filter( "get_user_metadata", function($value, $object_id, $meta_key, $single, $meta_type ) use ($blocks_disabled) {
                    if ($meta_key == 'wp_persisted_preferences') {
                        global $wpdb;
                        $value = $wpdb->get_var($wpdb->prepare('SELECT meta_value FROM %i WHERE user_id = %d AND meta_key = %s', $wpdb->usermeta, $object_id, $meta_key));
                        $value = maybe_unserialize($value);
                        $value['core']['hiddenBlockTypes'] = empty($value['core']['hiddenBlockTypes']) ? $blocks_disabled : array_merge($value['core']['hiddenBlockTypes'], $blocks_disabled);
                        //echo '<pre>';var_dump($blocks_disabled); var_dump($value); die();
                        return [$value];
                    }
                    return $value;
                }, 10, 5);
            }
        }
    }


}
