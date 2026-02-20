<?php

namespace WizardBlocks\Modules\Admin;

use WizardBlocks\Base\Module_Base;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Admin extends Module_Base {
    
    use Traits\Blocks;
    use Traits\Patterns;
    use Traits\Tools;
    use Traits\Actions;
    
    static public $blocks_disabled_key = 'blocks_disabled';

    /**
     * Admin constructor.
     *
     * @since 1.0.1
     * @param array $args
     */
    public function __construct() {
        parent::__construct();
        
        add_action('admin_menu', [$this, 'admin_menu_blocks']);
        add_action('admin_menu', [$this, 'admin_menu_patterns']);
        add_action('admin_menu', [$this, 'admin_menu_tools']);

    }

    public function get_action_url($args = '') {
        $nonce = wp_create_nonce('wizard-blocks-nonce');
        $page = isset($_GET['page']) ? sanitize_title(wp_unslash($_GET['page'])) : 'wblocks';
        return esc_url(admin_url("edit.php?post_type=block&page=" . $page . "&nonce=" . $nonce . ($args ? "&" . $args : '')));
    }

}
