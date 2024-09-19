<?php

namespace WizardBlocks\Modules\Block\Traits;

trait Pages {

    public function admin_menu_action() {

        add_submenu_page(
                'edit.php?post_type=block',
                __('All Blocks', 'wizard-blocks'),
                __('All Blocks', 'wizard-blocks'),
                'manage_options',
                'wblocks',
                [$this, 'wizard_blocks'] //callback function
        );

        add_submenu_page(
                'edit.php?post_type=block',
                __('Tools', 'wizard-blocks'),
                __('Tools', 'wizard-blocks'),
                'manage_options',
                'wtools',
                [$this, 'wizard_tools'] //callback function
        );
    }

    public function _notice($message, $type = 'success') {
        echo '<div class="notice is-dismissible notice-' . esc_attr($type) . ' notice-alt"><p>' . wp_kses($message, 'post') . '</p></div>';
    }

    public function wizard_tools() {
        $this->execute_actions();
        ?>

        <div class="wrap">
            <h1><?php esc_html_e('Wizard Tools', 'wizard-blocks'); ?></h1>

            <div class="card-row" style="display: flex;">
                <div class="card" style="width: 100%;">
                    <h2><?php esc_html_e('IMPORT', 'wizard-blocks'); ?></h2>
                    <p><?php esc_html_e('Add your Custom Blocks importing the block zip.', 'wizard-blocks'); ?><br><?php esc_html_e('Try to download and import some official Block examples:', 'wizard-blocks'); ?> <a target="_blank" href="https://github.com/WordPress/block-development-examples?tab=readme-ov-file#block-development-examples"><span class="dashicons dashicons-download"></span></a></p>
                    <form action="<?php echo esc_url($this->get_action_url("action=import")); ?>" method="POST" enctype="multipart/form-data">
                        <input type="file" name="zip">
                        <button class="btn button" type="submit"><?php esc_html_e('Import', 'wizard-blocks'); ?></button>
                    </form>
                </div>

                <div class="card" style="width: 100%;">
                    <h2><?php esc_html_e('EXPORT', 'wizard-blocks'); ?></h2>
                    <p><?php esc_html_e('Download all your Custom Blocks for a quick backup.', 'wizard-blocks'); ?><br><?php esc_html_e('You can then install them as native blocks.', 'wizard-blocks'); ?> <a target="_blank" href="https://developer.wordpress.org/block-editor/getting-started/fundamentals/registration-of-a-block/"><span class="dashicons dashicons-info"></span></a></p>
                    <a class="btn button" href="<?php echo esc_url($this->get_action_url("action=export")); ?>"><?php esc_html_e('Export', 'wizard-blocks'); ?></a>
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
            <h2><?php esc_html_e('Get code', 'wizard-blocks'); ?></h2>
            <p><?php esc_html_e('Copy these lines of PHP code into your Theme (or Child theme) at the end of the functions.php file. After that you could switch off this plugin.', 'wizard-blocks'); ?></p>
            <textarea style="width:100%;" rows="<?php echo count($wizard_blocks) + 2; ?>"><?php echo esc_html($code); ?></textarea>

        </div>
        <?php
    }

    public function get_action_url($args = '') {
        $nonce = wp_create_nonce('wizard-blocks-nonce');
        $page = isset($_GET['page']) ? sanitize_title(wp_unslash($_GET['page'])) : 'wblocks';
        return esc_url(admin_url("edit.php?post_type=block&page=" . $page . "&nonce=" . $nonce . ($args ? "&" . $args : '')));
    }

    public function wizard_blocks() {
        $this->execute_actions();

        $blocks_dir = apply_filters('wizard/dirs', []);
        $blocks = $this->get_registered_blocks();
        $blocks_count = [];
        foreach ($blocks as $name => $block) {
            $textdomain = $this->get_block_textdomain($block);
            $blocks_count[$textdomain] = empty($blocks_count[$textdomain]) ? 1 : ++$blocks_count[$textdomain];
            $block_slug = $this->get_block_slug($name);
            if ($block_post = $this->get_block_post($block_slug)) {
                $blocks[$name]['post'] = $block_post;
                if ($block_dir = $this->get_blocks_dir($block_slug)) {
                    $blocks[$name]['file'] = $block_dir . DIRECTORY_SEPARATOR . $block_slug . DIRECTORY_SEPARATOR . 'block.json';
                }
            }
            if (!empty($blocks[$name]['file']) && file_exists($blocks[$name]['file'])) {
                $json = file_get_contents($blocks[$name]['file']);
                $block_json = json_decode($json, true);
                foreach ($block_json as $key => $value) {
                    if (!isset($blocks[$name][$key])) {
                        $blocks[$name][$key] = $value;
                    }
                }
            }
        }
        $blocks_usage = $this->get_blocks_usage();

        $blocks_disabled = get_option(self::$blocks_disabled_key);
        //var_dump($blocks_disabled);



        $block_editor_context = new \WP_Block_Editor_Context(
                array(
            'name' => 'core/customize-widgets',
                )
        );
        $block_categories = get_block_categories($block_editor_context);
        $tmp = [];
        foreach ($block_categories as $cat) {
            $tmp[$cat['slug']] = $cat['title'];
        }
        $block_categories = $tmp;
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e('All Registered Blocks', 'wizard-blocks'); ?></h1>           
            <a href="<?php echo esc_url(admin_url('/post-new.php?post_type=block')); ?>" class="page-title-action dashicons-before dashicons-plus"><?php esc_html_e('Add new Block', 'wizard-blocks'); ?></a>
            <hr class="wp-header-end">
            <div class="displaying-num" style="margin-top: 12px; float: right;"><?php echo count($blocks); ?> <?php esc_html_e('blocks', 'wizard-blocks'); ?></div>

            <ul class="subsubsub blocks-filter">
                <li class="all"><a class="current" href="#"><?php esc_html_e('All', 'wizard-blocks'); ?> <span class="count">(<?php echo count($blocks); ?>)</span></a></li>
                <?php foreach ($blocks_count as $textdomain => $bc) { ?>
                    | <li class><a href="#<?php echo esc_attr($textdomain); ?>"><?php echo esc_html(ucfirst($textdomain)); ?> <span class="count">(<?php echo esc_html($bc); ?>)</span></a></li>
                <?php } ?>
            </ul>
            <hr style="clear: both; padding-top: 10px;">
            <form action="<?php echo esc_url($this->get_action_url()); ?>" method="POST">
                <input type="hidden" name="action" value="disable">
                <div class="card" style="max-width: none; width: 100%; display: flex; justify-content: space-between;">
                    <input id="blocks-search" placeholder="<?php esc_html_e('Search Block', 'wizard-blocks'); ?>" type="search">
                    <span>
                        <a class="button button-danger" href="<?php echo esc_url($this->get_action_url("action=reset")); ?>"><?php esc_html_e('Reset', 'wizard-blocks'); ?></a> 
                        <input class="button button-primary" type="submit" value="<?php esc_html_e('Save', 'wizard-blocks'); ?>">
                    </span>
                </div>

                <table class="wp-list-table widefat fixed striped table-view-list blocks">
                    <thead>
                        <tr>
                            <th scope="col" id="icon" class="manage-column column-icon" style="width: 30px;"><?php esc_html_e('Icon', 'wizard-blocks'); ?></th>
                            <th scope="col" id="title" class="manage-column column-title column-primary sortable sorted asc"><span><?php esc_html_e('Title', 'wizard-blocks'); ?></span></th>
                            <th scope="col" id="status" class="manage-column column-status" style="width: 40px;"><?php esc_html_e('Status', 'wizard-blocks'); ?></th>
                            <th scope="col" id="description" class="manage-column column-description"><?php esc_html_e('Description', 'wizard-blocks'); ?></th>
                            <th scope="col" id="api" class="manage-column column-category" style="width: 30px;"><?php esc_html_e('Api', 'wizard-blocks'); ?></th>
                            <th scope="col" id="category" class="manage-column column-category"><?php esc_html_e('Category', 'wizard-blocks'); ?></th>
                            <th scope="col" id="usage" class="manage-column column-usage sortable" style="width: 50px;"><?php esc_html_e('Usage', 'wizard-blocks'); ?></th>
                            <th scope="col" id="plugin" class="manage-column column-plugin"><?php esc_html_e('Plugin', 'wizard-blocks'); ?></th>
                            <th scope="col" id="actions" class="manage-column column-actions"><?php esc_html_e('Actions', 'wizard-blocks'); ?></th>
                        </tr>
                    </thead>

                    <tbody id="the-list">
                        <?php
                        foreach ($blocks as $block_name => $block) {
                            $block_post = false;
                            $block_slug = $block_name;
                            if (!empty($block['folder'])) {
                                $block_slug = basename($block['folder']);
                                $block['post'] = $block_post = $this->get_block_post($block_slug);
                            }
                            $block['slug'] = $block_slug;
                            ?>
                            <tr id="post-<?php echo esc_attr($block_post ? $block_post->ID : 'xxx'); ?>" class="iedit author-self type-block status-publish hentry block-<?php echo esc_attr($block['textdomain']); ?><?php echo in_array($block['textdomain'], ['core', 'wizard', 'wizard-blocks']) ? '' : ' block-extra'; ?>">
                                <td class="icon column-icon" data-colname="<?php esc_attr_e('Icon', 'wizard-blocks'); ?>">
                                    <?php
                                    if (empty($block['icon'])) {
                                        $block['icon'] = 'block-default';
                                    }
                                    // PHPCS - The SVG file content is being read from a strict file path structure.
                                    $block_icon_safe = $block['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
                                    echo (substr($block['icon'], 0, 5) == '<svg ') ? $block_icon_safe : '<span class="dashicons dashicons-' . esc_attr($block['icon']) . '"></span> ';
                                    ?>
                                </td>
                                <td class="title column-title has-row-actions column-primary page-title" data-colname="<?php esc_attr_e('Title', 'wizard-blocks'); ?>">
                                    <strong>
                                        <?php if ($block_post) { ?><a class="row-title" href="<?php echo esc_url(get_edit_post_link($block_post->ID)); ?>" aria-label=""><?php } ?>
                                            <abbr title="<?php echo esc_attr($block_name); ?>"><?php echo esc_html($this->get_block_title($block, $block_post)); ?></abbr>
                                            <?php if ($block_post) { ?></a><?php } ?>
                                        <br><small class="block-title" onClick="navigator.clipboard.writeText(this.innerText);"><?php echo esc_attr($block_name); ?> <span class="dashicons dashicons-clipboard"></span></small>
                                    </strong>
                                </td>
                                <td class="status column-status" data-colname="<?php esc_attr_e('Status', 'wizard-blocks'); ?>">
                                    <label class="switch">
                                        <input type="checkbox" name="blocks_disabled[<?php echo esc_attr($block['name']); ?>]"<?php echo (!empty($blocks_disabled) && in_array($block['name'], $blocks_disabled)) ? ' checked' : ''; ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td class="description column-description" data-colname="<?php esc_attr_e('Description', 'wizard-blocks'); ?>">
                                    <?php echo esc_html(empty($block['description']) ? '' : $block['description']); ?>
                                </td>
                                <td class="api column-api" data-colname="<?php esc_attr_e('Api Version', 'wizard-blocks'); ?>">
                                    <?php echo esc_html(empty($block['apiVersion']) ? '' : $block['apiVersion']); ?>
                                </td>
                                <td class="category column-category" data-colname="<?php esc_html_e('Category', 'wizard-blocks'); ?>">
                                    <?php
                                    if (!empty($block['category'])) {
                                        echo '<abbr title="' . esc_attr($block['category']) . '">';
                                        echo esc_html(empty($block_categories[$block['category']]) ? ucfirst($block['category']) : $block_categories[$block['category']]);
                                        echo '</abbr>';
                                    }
                                    ?>
                                </td>
                                <td class="usage column-usage" data-colname="<?php esc_attr_e('Usage', 'wizard-blocks'); ?>">
                                    <?php echo esc_html(empty($blocks_usage[$block['name']]) ? '0' : $blocks_usage[$block['name']]); ?>
                                </td>
                                <td class="folder column-folder" data-colname="<?php esc_attr_e('Folder', 'wizard-blocks'); ?>">
                                    <?php
                                    if (!empty($block['file'])) {
                                        //$block['file'] = str_replace('c:/', 'c://', $block['file']);
                                        $link = \WizardBlocks\Core\Helper::path_to_url($block['file']);
                                        $tmp = explode('/wp-includes/', $link, 2);
                                        if (count($tmp) > 1) { $link = '/wp-includes/'.end($tmp); } // core
                                        $tmp = explode('/wp-content/', $link, 2);
                                        if (count($tmp) > 1) { $link = '/wp-content/'.end($tmp); } // other
                                        echo '<a target="_blank" title="' . esc_attr($block['file']) . '" href="' . esc_attr($link) . '">';
                                        //var_dump($block['file']);
                                        /* $tmp = explode('/plugins/', $block['file']);
                                          if (count($tmp) > 1) {
                                          list($plugin_slug, $more) = explode(DIRECTORY_SEPARATOR, $tmp[1]);
                                          echo $plugin_slug;
                                          } */
                                    }
                                    echo esc_html($block['textdomain']);
                                    if (!empty($block['file'])) {
                                        echo '</a>';
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

                                <td class="actions column-actions" data-colname="<?php esc_attr_e('Actions', 'wizard-blocks'); ?>">
                                    <?php if ($block['textdomain'] == 'core') { ?>
                                        <a class="btn button dashicons-before dashicons-welcome-view-site" href="https://wordpress.org/documentation/article/blocks-list/" target="_blank" title="<?php esc_attr_e('Docs', 'wizard-blocks'); ?>"></a>
                                    <?php } ?>
                                    <a class="d-none hidden btn button button-secondary dashicons-before dashicons-dismiss" href="<?php echo esc_url($this->get_action_url("action=disable&block=" . $block_name)); ?>" title="<?php esc_attr_e('Disable', 'wizard-blocks'); ?>"></a>
                                    <?php if (!empty($block['folder'])) { ?>
                                        <a class="btn button dashicons-before dashicons-download" href="<?php echo esc_url($this->get_action_url("action=download&block=" . $block_name)); ?>" title="<?php esc_attr_e('Download', 'wizard-blocks'); ?>"></a>
                                    <?php } ?>
                                    <?php
                                    if (empty($block['post'])) {
                                        if (!empty($block['folder'])) {
                                            ?>
                                            <a class="btn button button-primary dashicons-before dashicons-database-import" href="<?php echo esc_url($this->get_action_url("action=import&block=" . $block_name)); ?>" title="<?php esc_attr_e('Import', 'wizard-blocks'); ?>"></a>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <a class="btn button button-primary dashicons-before dashicons-edit" href="<?php echo esc_url(get_edit_post_link($block_post->ID)); ?>" title="<?php esc_attr_e('Edit', 'wizard-blocks'); ?>"></a>
                                    <?php }
                                    ?>
                                    <?php do_action('wizard/blocks/action/btn', $block, $this); ?>
                                </td>		
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <th scope="col" id="icon" class="manage-column column-icon"><?php esc_attr_e('Icon', 'wizard-blocks'); ?></th>
                            <th scope="col" id="title" class="manage-column column-title column-primary"><span><?php esc_attr_e('Title', 'wizard-blocks'); ?></span></th>
                            <th scope="col" id="status" class="manage-column column-status"><?php esc_attr_e('Status', 'wizard-blocks'); ?></th>
                            <th scope="col" id="description" class="manage-column column-description"><?php esc_attr_e('Description', 'wizard-blocks'); ?></th>
                            <th scope="col" id="api" class="manage-column column-category"><?php esc_attr_e('Api', 'wizard-blocks'); ?></th>
                            <th scope="col" id="category" class="manage-column column-category"><?php esc_attr_e('Category', 'wizard-blocks'); ?></th>
                            <th scope="col" id="usage" class="manage-column column-usage sortable"><?php esc_attr_e('Usage', 'wizard-blocks'); ?></th>
                            <th scope="col" id="plugin" class="manage-column column-plugin"><?php esc_attr_e('Plugin', 'wizard-blocks'); ?></th>
                            <th scope="col" id="actions" class="manage-column column-actions"><?php esc_attr_e('Actions', 'wizard-blocks'); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </form>
        </div>
        <?php
        wp_enqueue_style('wizard-blocks-all', WIZARD_BLOCKS_URL.'modules/block/assets/css/all-blocks.css', [], '1.0.1');
        wp_enqueue_script('wizard-blocks-all', WIZARD_BLOCKS_URL.'modules/block/assets/js/all-blocks.js', [], '1.0.1', true);
    }

    function get_registered_block($name = '') {
        if ($name) {
            //return \WP_Block_Type_Registry::get_instance()->get_registered($slug);
            $blocks = $this->get_registered_blocks();
            if (isset($blocks[$name])) {
                return $blocks[$name];
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
            if (!empty($block['name'])) {
                $blocks[$block['name']] = $block;
                $blocks[$block['name']]['file'] = $ablock . DIRECTORY_SEPARATOR . 'block.json';
            } else {
                // TODO: no name?!
                //var_dump($block);
            }
        }

        return $blocks;
    }

    private function get_blocks_usage() {
        global $wpdb;
        $block_init = '<!-- wp:';
        $block_count = [];
        $posts = $wpdb->get_results($wpdb->prepare('SELECT * FROM %i WHERE post_content LIKE %s AND post_status = "publish"', $wpdb->posts, '%<!-- wp:%'));
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
