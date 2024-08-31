<?php

namespace WizardBlocks\Modules\Elementor;

use WizardBlocks\Core\Utils;
use WizardBlocks\Base\Module_Base;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Elementor extends Module_Base {

    public function __construct() {
        add_action('elementor/experiments/default-features-registered', [$this, 'elementor_features']);        
        $module_active = get_option("elementor_experiment-gutenberg");
        if (in_array($module_active, ['active'])) {
            add_action('elementor/widgets/register', [$this, 'register_gutenberg_blocks']);
            //add_action( 'elementor/init', 'add_gutenberg_blocks' );
            add_action('elementor/elements/categories_registered', [$this, 'add_elementor_widget_categories']);
        }
    }   
    
    public function elementor_features($features) {
        $features->add_feature([
            'name' => 'gutenberg',
            'title' => '<i class="dashicons dashicons-block-default"></i> ' . esc_html__('Gutenberg Block Widgets', 'wizard-blocks'),
            'description' => esc_html__('Use all rendered Gutenberg Block inside Elementor Editor as Widgets', 'wizard-blocks'),
            'release_status' => $features::RELEASE_STATUS_STABLE,
            'default' => false,
        ]);
    }
    
    function add_elementor_widget_categories($elements_manager) {
        $elements_manager->add_category(
                'gutenberg',
                [
                    'title' => esc_html__('Gutenberg', 'wizard-blocks'),
                    'icon' => 'eicon-wordpress',
                ]
        );
    }

    public function register_gutenberg_blocks($widgets_manager) {
        $registered_blocks = \WP_Block_Type_Registry::get_instance()->get_all_registered();
        //echo '<pre>';var_dump($registered_blocks);die();
        foreach ($registered_blocks as $name => $block) {
            if ($block->is_dynamic()) {
                $widgets_manager->register(new Base\GutenbergWidget([], ['block' => $block]));
            }
        }
    }
}
