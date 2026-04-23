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

        // Ajax export for non-admin users with edit_posts capability
        add_action('wp_ajax_wizard_blocks_download_block', [$this, 'ajax_download_block']);

        add_filter('admin_footer_text', [$this, 'admin_footer_text'], 1, 99);
    }

    public function get_action_url($args = '') {
        $nonce = wp_create_nonce('wizard-blocks-nonce');
        $page = isset($_GET['page']) ? sanitize_title(wp_unslash($_GET['page'])) : 'wblocks';
        return esc_url(admin_url("edit.php?post_type=block&page=" . $page . "&nonce=" . $nonce . ($args ? "&" . $args : '')));
    }

    /**
     * Add rating text to footer on settings page
     */
    function admin_footer_text($default) {

        if (isset($_GET['post_type']) && $_GET['post_type'] == 'block') {
            $strong_open = '<strong>';
            $strong_close = '</strong>';
            $link_open = '<a href="https://wordpress.org/support/view/plugin-reviews/wizard-blocks?filter=5#postform" target="_blank" class="svgs-rating-link">';
            $link_close = '</a>';

            // translators: %1$s: Opening strong tag, %2$s: Closing strong tag, %3$s: Opening anchor tag for rating link, %4$s: Closing anchor tag
            $text = esc_html__('If you like %1$sWizard Blocks%2$s please leave a %3$s★★★★★%4$s rating. Thanks ❤!', 'svg-support');

            return wp_kses(
                    sprintf(
                            $text,
                            $strong_open,
                            $strong_close,
                            $link_open,
                            $link_close
                    ),
                    array(
                        'strong' => array(),
                        'a' => array(
                            'href' => array(),
                            'target' => array(),
                            'class' => array()
                        )
                    )
            );
        }

        return $default;
    }
}
