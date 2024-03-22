<?php

namespace WizardBlocks\Modules\Block\Traits;

trait Pages {

    public function admin_menu_action() {
        
        add_submenu_page(
                'edit.php?post_type=block',
                __('All Blocks'),
                __('All Blocks'),
                'manage_options',
                'wblocks',
                [$this, 'wizard_blocks'] //callback function
        );
        
        add_submenu_page(
                'edit.php?post_type=block',
                __('Tools'),
                __('Tools'),
                'manage_options',
                'wtools',
                [$this, 'wizard_tools'] //callback function
        );
        
    }

    public function _notice($message, $type = 'success') {
        echo '<div class="notice is-dismissible notice-' . $type . ' notice-alt"><p>' . $message . '</p></div>';
    }

    public function wizard_tools() {
        $this->execute_actions();
        ?>

        <div class="wrap">
            <h1><?php _e('Wizard Tools', 'wizard-blocks'); ?></h1>

            <div class="card-row" style="display: flex;">
                <div class="card" style="width: 100%;">
                    <h2><?php _e('IMPORT', 'wizard-blocks'); ?></h2>
                    <p><?php _e('Add your Custom Blocks importing the block zip.', 'wizard-blocks'); ?><br><?php _e('Try to download and import some official Block examples:', 'wizard-blocks'); ?> <a target="_blank" href="https://github.com/WordPress/block-development-examples?tab=readme-ov-file#block-development-examples"><span class="dashicons dashicons-download"></span></a></p>
                    <form action="?post_type=block&page=<?php echo $_GET['page']; ?>&action=import" method="POST" enctype="multipart/form-data">
                        <input type="file" name="zip">
                        <button class="btn button" type="submit"><?php _e('Import', 'wizard-blocks'); ?></button>
                    </form>
                </div>

                <div class="card" style="width: 100%;">
                    <h2><?php _e('EXPORT', 'wizard-blocks'); ?></h2>
                    <p><?php _e('Download all your Custom Blocks for a quick backup.', 'wizard-blocks'); ?><br><?php _e('You can then  install them as native blocks.', 'wizard-blocks'); ?> <a target="_blank" href="https://developer.wordpress.org/block-editor/getting-started/fundamentals/registration-of-a-block/"><span class="dashicons dashicons-info"></span></a></p>
                    <a class="btn button" href="?post_type=block&page=<?php echo $_GET['page']; ?>&action=export"><?php _e('Export', 'wizard-blocks'); ?></a>
                </div>
            </div>

            <?php
            $code = '/* Wizard Blocks */' . PHP_EOL;
            $wizard_blocks = $this->get_blocks();
            foreach ($wizard_blocks as $ablock) {
                $json = $ablock . DIRECTORY_SEPARATOR . 'block.json';
                $code .= 'register_block_type("' . $json . '");' . PHP_EOL;
            }
            ?>
            <hr>
            <h2><?php _e('Get code', 'wizard-blocks'); ?></h2>
            <p><?php _e('Copy these lines of PHP code into your Theme (or Child theme) at the end of the functions.php file. After that you could switch off this plugin.', 'wizard-blocks'); ?></p>
            <textarea style="width:100%;" rows="<?php echo count($wizard_blocks) + 2; ?>"><?php echo $code; ?></textarea>

        </div>
        <?php
    }

    public function wizard_blocks() {
        $this->execute_actions();
        
        $blocks_dir = apply_filters('wizard/dirs', []);
        $blocks = $this->get_registered_blocks();
        $blocks_count = [];
        foreach ($blocks as $name => $block) {
            $textdomain = $this->get_block_textdomain($block);
            $blocks_count[$textdomain] = empty($blocks_count[$textdomain]) ? 1 : ++$blocks_count[$textdomain];
        }
        $blocks_usage = $this->get_blocks_usage()
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php _e('All Registered Blocks', 'wizard-blocks'); ?></h1>           
            <a href="<?php echo admin_url('/post-new.php?post_type=block'); ?>" class="page-title-action dashicons-before dashicons-plus"><?php _e('Add new Block', 'wizard-blocks'); ?></a>
            <hr class="wp-header-end">
            <div class="displaying-num" style="margin-top: 12px; float: right;"><?php echo count($blocks); ?> <?php _e('blocks', 'wizard-blocks'); ?></div>

            <ul class="subsubsub blocks-filter">
                <li class="all"><a class="current" href="#"><?php _e('All'); ?> <span class="count">(<?php echo count($blocks); ?>)</span></a></li>
                <?php foreach ($blocks_count as $textdomain => $bc) { ?>
                    | <li class><a href="#<?php echo $textdomain; ?>"><?php _e(ucfirst($textdomain)); ?> <span class="count">(<?php echo $bc; ?>)</span></a></li>
                <?php } ?>
            </ul>
            <script>
                jQuery('.blocks-filter a').on('click', function () {
                    jQuery('.blocks-filter a.current').removeClass('current');
                    jQuery(this).addClass('current');
                    jQuery('.blocks .hentry').show();
                    let filter = jQuery(this).attr('href').replace('#', '');
                    if (filter) {
                        console.log(filter);
                        jQuery('.blocks .hentry').hide();
                        jQuery('.blocks .hentry.block-' + filter).show();
                    }
                    return false;
                });
            </script>
            <hr style="clear: both; padding-top: 10px;">
            <table class="wp-list-table widefat fixed striped table-view-list blocks">
                <thead>
                    <tr>
                        <th scope="col" id="icon" class="manage-column column-icon" style="width: 30px;"><?php _e('Icon'); ?></th>
                        <th scope="col" id="title" class="manage-column column-title column-primary"><span><?php _e('Title'); ?></span></th>
                        <th scope="col" id="description" class="manage-column column-description"><?php _e('Description'); ?></th>
                        <th scope="col" id="plugin" class="manage-column column-plugin"><?php _e('Plugin'); ?></th>
                        <th scope="col" id="usage" class="manage-column column-usage" style="width: 50px;"><?php _e('Usage'); ?></th>
                        <th scope="col" id="actions" class="manage-column column-actions"><?php _e('Actions'); ?></th>
                    </tr>
                </thead>

                <tbody id="the-list">
                    <?php
                    foreach ($blocks as $name => $block) {
                        $block_post = false;
                        $block_slug = $name;
                        if (!empty($block['folder'])) {
                            $block_slug = basename($block['folder']);
                            $block['post'] = $block_post = $this->get_block_post($block_slug);
                        }
                        $block['slug'] = $block_slug;
                        ?>
                        <tr id="post-<?php echo $block_post ? $block_post->ID : 'xxx'; ?>" class="iedit author-self type-block status-publish hentry block-<?php echo $block['textdomain']; ?><?php echo in_array($block['textdomain'], ['core', 'wizard', 'wizard-blocks']) ? '' : ' block-extra'; ?>">
                            <td class="icon column-icon" data-colname="Icon">
                                <?php
                                if (empty($block['icon'])) {
                                    $block['icon'] = 'block-default';
                                }
                                echo (substr($block['icon'], 0, 5) == '<svg ') ? $block['icon'] : '<span class="dashicons dashicons-' . $block['icon'] . '"></span> ';
                                ?>
                            </td>
                            <td class="title column-title has-row-actions column-primary page-title" data-colname="<?php _e('Title', 'wizard-blocks'); ?>">
                                <strong>
                                    <?php if ($block_post) { ?><a class="row-title" href="<?php echo get_edit_post_link($block_post->ID); ?>" aria-label=""><?php } ?>
                                        <abbr title="<?php echo $name; ?>"><?php echo $this->get_block_title($block, $block_post); ?></abbr>
                                        <?php if ($block_post) { ?></a><?php } ?>
                                </strong>
                            </td>
                            <td class="description column-description" data-colname="<?php _e('Description', 'wizard-blocks'); ?>">
                                <?php echo empty($block['description']) ? '' : $block['description']; ?>
                            </td>
                            <td class="folder column-folder" data-colname="<?php _e('Folder', 'wizard-blocks'); ?>">
                                <?php
                                if (!empty($block['file'])) {
                                    echo '<abbr title="'.$block['file'].'">';
                                    /*$tmp = explode('/plugins/', $block['file']);
                                    if (count($tmp) > 1) {
                                        list($plugin_slug, $more) = explode(DIRECTORY_SEPARATOR, $tmp[1]);
                                        echo $plugin_slug;
                                    }*/
                                }
                                echo $block['textdomain'];
                                if (!empty($block['file'])) {
                                    echo '</abbr>';
                                }
                                if (!empty($block['render_callback'])) {
                                    if (is_string($block['render_callback'])) {
                                        if (str_starts_with($block['render_callback'], 'render_block_core_')) {
                                            //echo _('core');
                                        }
                                    }
                                }
                                ?>
                            </td>	
                            <td class="usage column-usage" data-colname="<?php _e('Usage', 'wizard-blocks'); ?>">
                                <?php echo empty($blocks_usage[$block['name']]) ? '0' : $blocks_usage[$block['name']]; ?>
                            </td>
                            <td class="actions column-actions" data-colname="<?php _e('Actions', 'wizard-blocks'); ?>">
                                <?php if ($block['textdomain'] == 'core') { ?>
                                    <a class="btn button dashicons-before dashicons-welcome-view-site" href="https://wordpress.org/documentation/article/blocks-list/" target="_blank"><?php _e('Docs', 'wizard-blocks'); ?></a>
                                    <a class="btn button button-primary dashicons-before dashicons-migrate" href="?post_type=block&page=<?php echo $_GET['page']; ?>&action=clone&block=<?php echo $block_slug; ?>"><?php _e('Clone', 'wizard-blocks'); ?></a>
                                <?php } ?>

                                <?php if (!empty($block['folder'])) { ?>
                                    <a class="btn button dashicons-before dashicons-download" href="?post_type=block&page=<?php echo $_GET['page']; ?>&action=download&block=<?php echo $block_slug; ?>"><?php _e('Download', 'wizard-blocks'); ?></a>
                                <?php } ?>
                                <?php
                                if (empty($block['post'])) {
                                    if (!empty($block['folder'])) {
                                        ?>
                                        <a class="btn button button-primary dashicons-before dashicons-database-import" href="?post_type=block&page=<?php echo $_GET['page']; ?>&action=import&block=<?php echo $block_slug; ?>"><?php _e('Import', 'wizard-blocks'); ?></a>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <a class="btn button button-primary dashicons-before dashicons-edit" href=" <?php echo get_edit_post_link($block_post->ID); ?>"><?php _e('Edit', 'wizard-blocks'); ?></a>
                                <?php }
                                ?>
            <?php do_action('wizard/blocks/action/btn', $block); ?>
                            </td>		
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <style>
            .page-title-action.dashicons-before:before,
            .button.dashicons-before:before {
                margin-right: 4px;
                vertical-align: middle;
                margin-top: -4px;
            }
            .icon svg {
                max-width: 100%;
                height: auto;
                width: 20px;
            }
        </style>
        <?php
    }

    function get_registered_block($slug = '') {
        if ($slug) {
            $blocks = $this->get_registered_blocks();
            if (isset($blocks[$slug])) {
                return $blocks[$slug];
            }
        }
        return false;
    }

    function get_registered_blocks() {

        $blocks = [];

        $blocks_dir = apply_filters('wizard/blocks/dirs', []);
        unset($blocks_dir['plugin']);

        // get_theme_update_available
        // wp_update_themes
        /* if ($update) {
          unset($blocks_dir['theme']);
          }
         */

        $icons_block = $this->get_icons_block();
        $icons_core = $this->get_icons_core();
        //var_dump($icons_core);
        $icons_block = $icons_block + $icons_core['blocks'];

        $registered_blocks = \WP_Block_Type_Registry::get_instance()->get_all_registered();
        //echo '<pre>';var_dump($registered_blocks);die();
        foreach ($registered_blocks as $name => $block_obj) {
            $block_json = wp_json_encode($block_obj);
            $block = json_decode($block_json, true);
            $block['textdomain'] = $this->get_block_textdomain($block);
            $blocks[$name] = $block;
            list($textdomain, $slug) = explode('/', $name, 2);
            if (empty($block['icon'])) {
                if (isset($icons_core['library_' . $slug])) {
                    $blocks[$name]['icon'] = $icons_core['library_' . $slug];
                }
                $slug_underscore = str_replace('-', '_', $slug);
                if (isset($icons_core[$slug_underscore])) {
                    $blocks[$name]['icon'] = $icons_core[$slug_underscore];
                }
                if ($block['textdomain'] == 'woocommerce') {
                    $blocks[$name]['icon'] = $this->get_icons_woo($block);
                }
                if (!empty($icons_block[$name]) && !empty($icons_core[$icons_block[$name]])) {
                    $blocks[$name]['icon'] = $icons_core[$icons_block[$name]];
                }
                
            }
        }

        $wizard_blocks = $this->get_blocks();
        foreach ($wizard_blocks as $ablock) {
            $block_slug = basename($ablock);
            $block = $this->get_json_data($block_slug);
            $block['folder'] = $ablock;
            $blocks[$block['name']] = $block;
        }

        return $blocks;
    }
    
    function get_blocks_usage() {
        global $wpdb;
        $block_init = '<!-- wp:';
        $block_count = [];
        $sql = 'SELECT * FROM '.$wpdb->posts.' WHERE post_content LIKE "%'.$block_init.'%" AND post_status = "publish"';
        $posts = $wpdb->get_results($sql);
        foreach ($posts as $post) {
            $tmp = explode($block_init, $post->post_content);
            foreach ($tmp as $key => $block) {
                if ($key) {
                    list($block_name, $more) = explode(' ', $block, 2);
                    $block_count[$block_name] = empty($block_count[$block_name]) ? 1 : ++$block_count[$block_name];
                }
            }
        }
        return $block_count;
    }
}
