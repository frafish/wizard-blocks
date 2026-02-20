<?php

namespace WizardBlocks\Modules\Admin\Traits;

if ( ! defined( 'ABSPATH' ) ) exit; 

trait Patterns {

    public function admin_menu_patterns() {
        add_submenu_page(
                'edit.php?post_type=block',
                __('Patterns', 'wizard-blocks'),
                __('Patterns', 'wizard-blocks'),
                'manage_options',
                'wc_block',
                [$this, 'wizard_patterns']
        );
    }

    public function wizard_patterns() {
        $current_theme = wp_get_theme()->get_stylesheet();
        
        $patterns_usage = apply_filters('wizard/blocks/patterns_usage_data', $this->get_patterns_usage());
        //var_dump($patterns_usage);
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e('All Patterns', 'wizard-blocks'); ?></h1>
            <a class="page-title-action" href="<?php echo esc_url( admin_url( 'admin.php?page=site-editor-v2&p=%2Fpatterns%2Flist%2Fall' ) ); ?>">
                <?php esc_html_e( 'Add Pattern', 'wizard-blocks' ); ?>
            </a>
            <a style="margin-top: 10px;" class="button button-primary dashicons-before dashicons-external" href="https://wordpress.org/patterns/" target="_blank"><?php esc_html_e('Find more', 'wizard-blocks'); ?></a>

            <hr class="wp-header-end">

            <h2><?php esc_html_e('Database Patterns', 'wizard-blocks'); ?></h2>
            <table class="wp-list-table widefat fixed striped table-view-list blocks">
                <thead>
                    <tr>
                        <th scope="col" class="manage-column column-title column-primary"><?php esc_html_e('Title', 'wizard-blocks'); ?></th>
                        <th scope="col" class="manage-column" style="width: 60px;"><?php esc_html_e('ID', 'wizard-blocks'); ?></th>
                        <th scope="col" class="manage-column"><?php esc_html_e('Description', 'wizard-blocks'); ?></th>
                        <th scope="col" class="manage-column"><?php esc_html_e('Category', 'wizard-blocks'); ?></th>
                        <th scope="col" class="manage-column" style="width: 100px;"><?php esc_html_e('Status', 'wizard-blocks'); ?></th>
                        <th scope="col" class="manage-column" style="width: 100px;"><?php esc_html_e('Synced', 'wizard-blocks'); ?></th>
                        <th scope="col" class="manage-column" style="width: 60px;"><?php esc_html_e('Usage', 'wizard-blocks'); ?></th>
                        <th scope="col" class="manage-column column-actions" style="width: 80px;"><?php esc_html_e('Actions', 'wizard-blocks'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $db_patterns = get_posts([
                        'post_type' => 'wp_block',
                        'posts_per_page' => -1,
                        'post_status' => ['publish', 'draft', 'pending', 'private'],
                    ]);

                    if ($db_patterns) {
                        foreach ($db_patterns as $post) {
                            $sync_status = get_post_meta($post->ID, 'wp_pattern_sync_status', true);
                            $slug = apply_filters('wizard/blocks/pattern/slug', esc_attr($post->post_name), $post->ID);
                            ?>
                            <tr>
                                <td class="title column-title has-row-actions column-primary">
                                    <strong><a class="row-title" href="<?php echo esc_url(get_edit_post_link($post->ID)); ?>"><?php echo esc_html($post->post_title); ?></a></strong>
                                    <p class="description"><code><?php echo $slug; ?></code></p>
                                </td>
                                <td><?php echo esc_attr($post->ID); ?></td>
                                <td><small><?php echo esc_html($post->post_excerpt ?: ''); ?></small></td>
                                <td>
                                    <?php
                                    $terms = get_the_terms($post->ID, 'wp_pattern_category');
                                    echo ($terms && !is_wp_error($terms)) ? esc_html(implode(', ', wp_list_pluck($terms, 'name'))) : '--';
                                    ?>
                                </td>
                                <td><?php echo esc_html(ucfirst($post->post_status)); ?></td>
                                <td>
                                    <?php echo ($sync_status === 'unsynced') ? '🔓 ' . esc_html__('Unsynced', 'wizard-blocks') : '🔄 ' . esc_html__('Synced', 'wizard-blocks'); ?>
                                </td>
                                <td>
                                <?php
                                if (empty($patterns_usage[$post->ID])) { ?>0<?php } else {
                                    if (!empty($patterns_usage[$post->ID]['posts'])) {
                                        echo '<abbr title="'.esc_attr(implode(', ', $patterns_usage[$post->ID]['posts'])).'">';
                                    }
                                    echo esc_attr($patterns_usage[$post->ID]['count']);
                                    if (!empty($patterns_usage[$post->ID]['posts'])) {
                                        echo '</abbr>';
                                    }
                                }
                                ?>
                                </td>
                                <td class="column-actions">
                                    <a class="button button-small dashicons-before dashicons-edit" href="<?php echo esc_url(get_edit_post_link($post->ID)); ?>" title="<?php esc_attr_e('Edit', 'wizard-blocks'); ?>"></a>
                                    <?php do_action('wizard/blocks/pattern/database/actions', $post); ?>
                                </td>
                            </tr>
                        <?php }
                    } else {
                        ?>
                        <tr><td colspan="8"><?php esc_html_e('No database patterns found.', 'wizard-blocks'); ?></td></tr>
                    <?php } ?>
                </tbody>
            </table>

            <br><hr><br>

            <h2><?php esc_html_e('System Patterns (Theme / Plugin)', 'wizard-blocks'); ?> <a target="_blank" href="https://developer.wordpress.org/block-editor/how-to-guides/curating-the-editor-experience/patterns/">🛈</a>:</h2>
            <table class="wp-list-table widefat fixed striped table-view-list blocks">
                <thead>
                    <tr>
                        <th scope="col" class="manage-column column-title column-primary"><?php esc_html_e('Title / Slug', 'wizard-blocks'); ?></th>
                        <th scope="col" class="manage-column"><?php esc_html_e('Description', 'wizard-blocks'); ?></th>
                        <th scope="col" class="manage-column"><?php esc_html_e('Categories', 'wizard-blocks'); ?></th>
                        <th scope="col" class="manage-column" style="width: 100px;"><?php esc_html_e('Source', 'wizard-blocks'); ?></th>
                        <th scope="col" class="manage-column" style="width: 60px;"><?php esc_html_e('Usage', 'wizard-blocks'); ?></th>
                        <th scope="col" class="manage-column column-actions" style="width: 80px;"><?php esc_html_e('Actions', 'wizard-blocks'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $registry = \WP_Block_Patterns_Registry::get_instance();
                    $sys_patterns = $registry->get_all_registered();

                    if (!empty($sys_patterns)) {
                        foreach ($sys_patterns as $p) {
                            $slug = apply_filters('wizard/blocks/pattern/slug', esc_attr($p['name']));
                            $is_theme_pattern = (strpos($slug, $current_theme . '/') === 0 || strpos($slug, 'core/') === false);
                            $file_slug = strpos($slug, '/') !== false ? substr($p['name'], strpos($p['name'], '/') + 1) : $slug;
                            ?>
                            <tr>
                                <td class="title column-title column-primary">
                                    <strong><?php echo esc_html($p['title']); ?></strong>
                                    <p class="description"><code><?php echo $slug; ?></code></p>
                                </td>
                                <td><small><?php echo esc_html($p['description'] ?? '--'); ?></small></td>
                                <td><?php echo!empty($p['categories']) ? esc_html(implode(', ', $p['categories'])) : '--'; ?></td>
                                <td>
                                    <?php echo $is_theme_pattern ? esc_html__('Theme', 'wizard-blocks') : esc_html__('Core / Plugin', 'wizard-blocks'); ?>
                                </td>
                                <td><?php echo esc_attr(empty($patterns_usage[$p['name']]) ? '0' : $patterns_usage[$p['name']]); ?></td>
                                <td class="column-actions">
                                    <?php
                                    if ($is_theme_pattern) {
                                        $edit_url = admin_url(sprintf(
                                                'theme-editor.php?file=patterns/%s.php&theme=%s',
                                                urlencode($file_slug),
                                                urlencode($current_theme)
                                        ));
                                        ?>
                                        <a class="button button-small dashicons-before dashicons-edit" href="<?php echo esc_url($edit_url); ?>" title="<?php esc_attr_e('Edit File', 'wizard-blocks'); ?>"></a>
                                    <?php } else { ?>
                                        <span class="dashicons dashicons-lock" title="<?php esc_attr_e('Read Only', 'wizard-blocks'); ?>"></span>
                                    <?php } ?>
                                    <?php do_action('wizard/blocks/pattern/system/actions', $p); ?>
                                </td>
                            </tr>
                    <?php }
                    } else { ?>
                        <tr><td colspan="5"><?php esc_html_e('No system patterns found.', 'wizard-blocks'); ?></td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function get_patterns_usage($block_id = '') {
        global $wpdb;

        // <!-- wp:block {"ref":123} /-->
        $block_init = '<!-- wp:block {"ref":';
        if ($block_id) {
            $block_init .= $block_id;
        }
        $block_count = [];
        $posts = $wpdb->get_results($wpdb->prepare('SELECT * FROM %i WHERE post_content LIKE %s AND post_status = "publish"', $wpdb->posts, '%' . $block_init . '%'));
        foreach ($posts as $post) {
            $tmp = explode($block_init, $post->post_content);
            foreach ($tmp as $key => $block) {
                if ($key) {
                    list($block_name, $more) = explode('}', $block, 2);
                    $block_count[$block_name]['count'] = empty($block_count[$block_name]) ? 1 : ++$block_count[$block_name]['count'];
                    if (empty($block_count[$block_name]['posts']) || !in_array($post->ID, $block_count[$block_name]['posts'])) {
                        $block_count[$block_name]['posts'][] = $post->ID;
                    }
                }
            }
        }
        if (!empty($block_count) && $block_id) {
            return reset($block_count);
        }
        return $block_count;
    }
}
