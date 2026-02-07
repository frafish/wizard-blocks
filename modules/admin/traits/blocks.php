<?php

namespace WizardBlocks\Modules\Admin\Traits;

trait Blocks {

    public function admin_menu_blocks() {

        add_submenu_page(
                'edit.php?post_type=block',
                __('All Blocks', 'wizard-blocks'),
                __('All Blocks', 'wizard-blocks'),
                'manage_options',
                'wblocks',
                [$this, 'wizard_blocks'] //callback function
        );
        
        if (is_admin() && isset($_GET['post_type']) && $_GET['post_type'] == \WizardBlocks\Modules\Block\Block::get_cpt_name()) {
            add_filter( 'gettext', [$this, 'gettext'], 10, 3);
        }
        
    }
    

    public function wizard_blocks() {
        $this->execute_actions();
        
        $wb = \WizardBlocks\Modules\Block\Block::instance();

        $blocks_dir = apply_filters('wizard/dirs', []);
        $blocks = $wb->get_registered_blocks();
        $blocks_count = [];
        foreach ($blocks as $name => $block) {
            $textdomain = $wb->get_block_textdomain($block);
            $blocks_count[$textdomain] = empty($blocks_count[$textdomain]) ? 1 : ++$blocks_count[$textdomain];
            $block_slug = $wb->get_block_slug($name);
            if ($block_post = $wb->get_block_post($block_slug)) {
                $blocks[$name]['post'] = $block_post;
                if ($block_dir = $wb->get_blocks_dir($block_slug)) {
                    $blocks[$name]['file'] = $block_dir . DIRECTORY_SEPARATOR . 'block.json';
                }
            }
            if (!empty($blocks[$name]['file']) && file_exists($blocks[$name]['file'])) {
                $json = $wb->get_filesystem()->get_contents($blocks[$name]['file']);
                $block_json = json_decode($json, true);
                foreach ($block_json as $key => $value) {
                    if (!isset($blocks[$name][$key])) {
                        $blocks[$name][$key] = $value;
                    }
                }
            }
        }
        $blocks_usage = $wb->get_blocks_usage();
        
        $blocks_disabled = get_option(\WizardBlocks\Modules\Admin\Admin::$blocks_disabled_key);
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
                    | <li class><a href="#<?php esc_attr_e($textdomain); ?>"><?php echo esc_html(ucfirst(str_replace('-', ' ', $textdomain))); ?> <span class="count">(<?php echo esc_html($bc); ?>)</span></a></li>
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
                            <th scope="col" id="icon" class="manage-column column-icon" style="width: 50px;"><?php esc_html_e('Icon', 'wizard-blocks'); ?></th>
                            <th scope="col" id="title" class="manage-column column-title column-primary sortable sorted asc"><span><?php esc_html_e('Title', 'wizard-blocks'); ?></span></th>
                            <th scope="col" id="status" class="manage-column column-status" style="width: 80px;"><?php esc_html_e('Status', 'wizard-blocks'); ?></th>
                            <th scope="col" id="description" class="manage-column column-description"><?php esc_html_e('Description', 'wizard-blocks'); ?></th>
                            <th scope="col" id="api" class="manage-column column-category" style="width: 50px;"><?php esc_html_e('Api', 'wizard-blocks'); ?></th>
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
                                $block['post'] = $block_post = $wb->get_block_post($block_slug);
                            }
                            $block['slug'] = $block_slug;
                            ?>
                            <tr id="post-<?php esc_attr_e($block_post ? $block_post->ID : 'xxx'); ?>" class="iedit author-self type-block status-publish hentry block-<?php esc_attr_e($block['textdomain']); ?><?php echo in_array($block['textdomain'], ['core', 'wizard', 'wizard-blocks']) ? '' : ' block-extra'; ?>">
                                <td class="icon column-icon" data-colname="<?php esc_attr_e('Icon', 'wizard-blocks'); ?>">
                                    <?php
                                    if (empty($block['icon'])) {
                                        $block['icon'] = 'block-default';
                                    }
                                    $wb->the_block_thumbnail($block_name, $block['icon'], ['width' => '30']);
                                    ?>
                                </td>
                                <td class="title column-title has-row-actions column-primary page-title" data-colname="<?php esc_attr_e('Title', 'wizard-blocks'); ?>">
                                    <strong>
                                        <?php if ($block_post) { ?><a class="row-title" href="<?php echo esc_url(get_edit_post_link($block_post->ID)); ?>" aria-label=""><?php } ?>
                                            <abbr title="<?php esc_attr_e($block_name); ?>"><?php echo esc_html($wb->get_block_title($block, $block_post)); ?></abbr>
                                            <?php if ($block_post) { ?></a><?php } ?>
                                        <br><small class="block-title" onClick="navigator.clipboard.writeText(this.innerText);"><?php esc_attr_e($block_name); ?> <span class="dashicons dashicons-clipboard"></span></small>
                                    </strong>
                                </td>
                                <td class="status column-status" data-colname="<?php esc_attr_e('Status', 'wizard-blocks'); ?>">
                                    <label class="switch">
                                        <input type="checkbox" name="blocks_disabled[<?php esc_attr_e($block['name']); ?>]"<?php echo (!empty($blocks_disabled) && in_array($block['name'], $blocks_disabled)) ? ' checked' : ''; ?>>
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
                                    <?php echo esc_html(empty($blocks_usage[$block['name']]['count']) ? '0' : $blocks_usage[$block['name']]['count']); ?>
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
                                        if (!empty($block['folder'])) { ?>
                                            <a class="btn button button-primary dashicons-before dashicons-database-import" href="<?php echo esc_url($this->get_action_url("action=import&block=" . $block_name)); ?>" title="<?php esc_attr_e('Import', 'wizard-blocks'); ?>"></a>
                                        <?php }
                                    } else {
                                        if ($block_post) { ?>
                                        <a class="btn button button-primary dashicons-before dashicons-edit" href="<?php echo esc_url(get_edit_post_link($block_post->ID)); ?>" title="<?php esc_attr_e('Edit', 'wizard-blocks'); ?>"></a>
                                        <?php }
                                    }
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
        wp_enqueue_style('wizard-blocks-all', WIZARD_BLOCKS_URL.'modules'. DIRECTORY_SEPARATOR.'block'. DIRECTORY_SEPARATOR.'assets'. DIRECTORY_SEPARATOR.'css'. DIRECTORY_SEPARATOR.'all-blocks.css', [], '1.0.1');
        wp_enqueue_script('wizard-blocks-all', WIZARD_BLOCKS_URL.'modules'. DIRECTORY_SEPARATOR.'block'. DIRECTORY_SEPARATOR.'assets'. DIRECTORY_SEPARATOR.'js'. DIRECTORY_SEPARATOR.'all-blocks.js', [], '1.0.1', true);
    }

    function gettext($translation, $text, $domain ) {
        if ($text == 'No Blocks found.') {
            ob_start();
            ?>
            <div style="text-align: center;">
                <span class="dashicons dashicons-warning"></span>
                <br>
                <h2><?php echo $translation; ?></h2>
                <br>
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type='.self::$cpt_name)); ?>" class="button dashicons-before dashicons-plus-alt components-button has-text has-icon"><?php esc_html_e('Add your first Block', 'wizard-blocks'); ?></a>
                or 
                <a href="<?php echo esc_url(admin_url('edit.php?post_type='.self::$cpt_name.'&page=wtools')); ?>" class="button dashicons-before dashicons-database-import"><?php esc_html_e('Import Blocks', 'wizard-blocks'); ?></a>
            </div>
            <?php
            $translation = ob_get_clean();
        }
        return $translation;
    }
}
