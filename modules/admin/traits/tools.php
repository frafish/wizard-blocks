<?php

namespace WizardBlocks\Modules\Admin\Traits;

if ( ! defined( 'ABSPATH' ) ) exit; 

trait Tools {

    public function admin_menu_tools() {

        add_submenu_page(
                'edit.php?post_type=block',
                __('Tools', 'wizard-blocks'),
                __('Tools', 'wizard-blocks'),
                'manage_options',
                'wtools',
                [$this, 'wizard_tools'] //callback function
        );
        
        add_action('post_submitbox_start', function ($post) {
            if ($post && $post->post_name) {
                if ($post->post_type == 'block') {
                    $wb = \WizardBlocks\Modules\Block\Block::instance();
                    $json = $wb->get_json_data($post->post_name);
                    //var_dump($json);
                    ?>
                    <div id="export-action">
                        <a class="button button-secondary button-large dashicons-before dashicons-database-export d-block" target="_blank" href="<?php echo esc_url($this->get_action_url('action=download&block=' . $wb->get_block_textdomain($json) . '/' . $post->post_name)); ?>">
                            <?php esc_html_e('Export', 'wizard-blocks'); ?>
                        </a>
                    </div>
                    <?php
                    //$revisione = $this->get_block_revision();
                    /* $revisions_url = wp_get_post_revisions_url($post->ID);
                      if ($revisions_url) {
                      echo '<div id="revision-action" style="float: left; margin-right: 5px;"><a class="button button-secondary button-large" href="' . esc_url($revisions_url) . '">' . esc_html__('Revisions', 'wizard-blocks') . '</a></div>';
                      } */
                    ?>
                    <hr style="clear:both;">
                    <?php
                }
            } else { 
                if (isset($_GET['post_type']) && $_GET['post_type'] == 'block') { ?>
                <div id="ai-action">
                    <button class="button-ai">
                       <a class="button button-ai-content button-rounded" href="https://telex.automattic.ai" target="_blank"><?php esc_html_e('Create with TelexAI', 'wizard-blocks'); ?></a>
                    </button>
                </div>
            <?php }
            }
        });
        
    }

    public function wizard_tools() {
        $wb = \WizardBlocks\Modules\Block\Block::instance();
        $this->enqueue_style('block-ai', '../block/assets/css/ai.css');
        $this->execute_actions();
        ?>

        <div class="wrap">
            <h1><?php esc_html_e('Wizard Tools', 'wizard-blocks'); ?></h1>

            <div class="card-row" style="display: flex;">
                <div class="card upload-block" style="width: 100%;">
                    <h2><?php esc_html_e('IMPORT', 'wizard-blocks'); ?></h2>
                    <p><?php esc_html_e('Add your Custom Blocks importing the block zip.', 'wizard-blocks'); ?><br><?php esc_html_e('Try to download and import some official Block examples:', 'wizard-blocks'); ?> <a target="_blank" href="https://github.com/WordPress/block-development-examples?tab=readme-ov-file#block-development-examples"><span class="dashicons dashicons-download"></span></a>
                    <br><?php esc_html_e('or', 'wizard-blocks'); ?> &nbsp; <button class="button-ai">
                       <a class="button button-ai-content button-rounded" href="https://telex.automattic.ai" target="_blank"><?php esc_html_e('Create with TelexAI', 'wizard-blocks'); ?></a>
                    </button></p>
                    <form class="wp-upload-form" action="<?php echo esc_url($this->get_action_url("action=import")); ?>" method="POST" enctype="multipart/form-data">
                        <input type="file" name="zip">
                        <button class="btn button" type="submit"><?php esc_html_e('Import', 'wizard-blocks'); ?></button>
                    </form>
                </div>

                <div class="card export-blocks" style="width: 100%;">
                    <h2><?php esc_html_e('EXPORT', 'wizard-blocks'); ?></h2>
                    <p><?php esc_html_e('Download all your Custom Blocks for a quick backup.', 'wizard-blocks'); ?><br><?php esc_html_e('You can then install them as native blocks.', 'wizard-blocks'); ?> <a target="_blank" href="https://developer.wordpress.org/block-editor/getting-started/fundamentals/registration-of-a-block/"><span class="dashicons dashicons-info"></span></a></p>
                    <a class="btn button" href="<?php echo esc_url($this->get_action_url("action=export")); ?>"><?php esc_html_e('Export', 'wizard-blocks'); ?></a>
                </div>
            </div>

            <?php
            $code = '/* Wizard Blocks */' . PHP_EOL;
            $wizard_blocks = $wb->get_blocks();
            foreach ($wizard_blocks as $ablock) {
                $json = $ablock . DIRECTORY_SEPARATOR . 'block.json';
                $code_block = 'register_block_type("' . $json . '");' . PHP_EOL;
                $code_block = apply_filters('wizard/blocks/code/block', $code_block, $json, $code);
                $code .= $code_block;
            }
            $code = apply_filters('wizard/blocks/code', $code);
            ?>
            <hr>
            <h2><?php esc_html_e('Get code', 'wizard-blocks'); ?></h2>
            <p><?php esc_html_e('Copy these lines of PHP code into your Theme (or Child theme) at the end of the functions.php file. After that you could switch off this plugin.', 'wizard-blocks'); ?></p>
            <textarea style="width:100%;" rows="<?php echo esc_attr(substr_count($code, PHP_EOL) + 2); ?>" data-blocks="<?php echo count($wizard_blocks); ?>"><?php echo esc_html($code); ?></textarea>
            <?php do_action('wizard/blocks/tools', $this); ?>
        </div>
        <?php
        wp_enqueue_style('wizard-blocks-all', WIZARD_BLOCKS_URL.'modules/block/assets/css/import.css', [], '1.2.0');
    }

}
