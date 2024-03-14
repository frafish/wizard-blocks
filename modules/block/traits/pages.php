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
                        <th scope="col" id="folder" class="manage-column column-description"><?php _e('Description'); ?></th>
                        <th scope="col" id="folder" class="manage-column column-folder"><?php _e('Folder'); ?></th>
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
                        $block['textdomain'] = $this->get_block_textdomain($block);
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
                                echo $block['textdomain'];

                                if (!empty($block['render_callback'])) {
                                    if (is_string($block['render_callback'])) {
                                        if (str_starts_with($block['render_callback'], 'render_block_core_')) {
                                            //echo _('core');
                                        }
                                    }
                                }
                                ?>
                            </td>	
                            <td class="actions column-actions" data-colname="<?php _e('Actions', 'wizard-blocks'); ?>">
                                <?php if ($block['textdomain'] == 'core') { ?>
                                    <a class="btn button dashicons-before dashicons-welcome-view-site" href="https://wordpress.org/documentation/article/blocks-list/" target="_blank"><?php _e('Docs', 'wizard-blocks'); ?></a>
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
        </style>
        <?php
    }

    function get_icons_core() {
        $icons_core = [];
        $icons_block = [];
        $block_library_js = file_get_contents(get_home_path() . DIRECTORY_SEPARATOR . 'wp-includes' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'dist' . DIRECTORY_SEPARATOR . 'block-library.js');
        //var_dump($block_library_js);
        $tmp = explode('external_wp_primitives_namespaceObject.SVG,', $block_library_js);
        foreach ($tmp as $key => $value) {
            if ($key) {
                $tmp2 = explode('// ', $value, 2);

                $tmp3 = explode('/* harmony default export */ var ', reset($tmp2), 2);

                $tmp5 = explode(' = (', end($tmp3), 2);
                if (count($tmp5) == 2) {
                    list ($name, $more) = $tmp5;
                    list($name2, $more2) = explode(');', $more, 2);
                    //echo $name.'-'.$name2.'<br>';

                    $tmp8 = explode('/**', end($tmp2), 2);
                    //var_dump(reset($tmp8)); //die();
                    $tmp6 = explode('/edit.js', reset($tmp8));
                    if (count($tmp6) == 2) {
                        $tmp7 = explode('/build-module/', reset($tmp6), 2);
                        if (count($tmp7) == 2) {
                            list($more3, $block_name) = $tmp7;
                            $icons_block['core/' . $block_name] = $name;
                        }
                    }
                }
                list($jsons, $tmp4) = explode('));', reset($tmp2), 2);
                $jsons = str_replace('width:', '"width":', $jsons);
                $jsons = str_replace('height:', '"height":', $jsons);
                $jsons = str_replace('xmlns:', '"xmlns":', $jsons);
                $jsons = str_replace('viewBox:', '"viewBox":', $jsons);
                $jsons = str_replace('fillRule:', '"fill-rule":', $jsons);
                $jsons = str_replace('clipRule:', '"clip-rule":', $jsons);
                $jsons = str_replace('d:', '"d":', $jsons);
                $jsons = str_replace('cx:', '"cx":', $jsons);
                $jsons = str_replace('cy:', '"cy":', $jsons);
                $jsons = str_replace('r:', '"r":', $jsons);
                $svg_objs = explode(', (0,external_wp_element_namespaceObject.createElement)(external_wp_primitives_namespaceObject.', $jsons);
                if (count($svg_objs) > 1) {
                    $svg_wrap_json = array_shift($svg_objs);
                    $svg_wrap = json_decode($svg_wrap_json, true);
                    if ($svg_wrap) {
                        $svg = '<svg';
                        if (empty($svg_wrap['width']))
                            $svg_wrap['width'] = 24;
                        if (empty($svg_wrap['height']))
                            $svg_wrap['height'] = 24;
                        foreach ($svg_wrap as $key => $value) {
                            $svg .= ' ' . $key . '="' . $value . '"';
                        }
                        $svg .= ' aria-hidden="true" focusable="false">';
                        foreach ($svg_objs as $svg_obj) {
                            list($type, $svg_inner_json) = explode(',', $svg_obj, 2);
                            $svg_inner_json = str_replace(')', '', $svg_inner_json);
                            $svg_inner = json_decode($svg_inner_json, true);
                            if ($svg_inner) {
                                $svg .= '<' . strtolower($type);
                                foreach ($svg_inner as $key => $value) {
                                    $svg .= ' ' . $key . '="' . $value . '"';
                                }
                                $svg .= '></' . strtolower($type) . '>';
                            }
                        }
                        $svg .= '</svg>';
                        $icons_core[$name] = $svg;
                    }
                }
            }
        }
        //var_dump($icons_block);
        $icons_core['blocks'] = $icons_block;
        //die();
        // ICONS: \wp-includes\js\dist\block-library.js
        //<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M6 5V18.5911L12 13.8473L18 18.5911V5H6Z"></path></svg>
        return $icons_core;
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

        $icons_block = [
            //'core/block' => 'library_symbol',
            'core/pattern' => 'library_symbol',
            'core/navigation-submenu' => 'remove_submenu',
            'core/page-list-item' => 'library_page',
            'core/page-list' => 'library_pages',
            'core/archives' => 'library_archive',
            'core/avatar' => 'comment_author_avatar',
            'core/categories' => 'post_categories',
            'core/post-author' => 'post_author',
            'core/post-author-biography' => 'post_author',
            'core/post-author-name' => 'post_author',
            'core/comments' => 'post_comments',
            'core/comments-title' => 'library_title',
            'core/comment-author-name' => 'comment_author_name',
            'core/comment-content' => 'comment_content',
            'core/comment-date' => 'post_date',
            //'core/comment-count' => 'post_comments_count',
            'core/comment-template' => 'library_layout',
            'core/comment-date' => 'post_date',
            'core/comments-pagination' => 'query_pagination',
            'core/comments-pagination-next' => 'query_pagination_next',
            'core/comments-pagination-numbers' => 'query_pagination_numbers',
            'core/comments-pagination-previous' => 'query_pagination_previous',
            //'core/comment-edit-link' => 'comment_edit_link',
            'core/footnotes' => 'format_list_numbered',
            //'core/footnotes' => 'format_list_bullets',
            'core/home-link' => 'library_home',
            'core/latest-comments' => 'library_comment',
            'core/latest-posts' => 'post_list',
            'core/loginout' => 'library_login',
            'core/navigation-link' => 'custom_link',
            'core/spacer' => 'resize_corner_n_e',
            'core/media-text' => 'media_and_text',
            'core/freeform' => 'library_classic',
            'core/template-part' => 'library_layout',
            //'core/embed' => 'embedContentIcon',
            'core/tag-cloud' => 'library_tag',
            'core/social-link' => 'library_share',
            'core/site-title' => 'map_marker',
            'core/site-tagline' => 'site_tagline_icon',
            'core/read-more' => 'library_link',
            'core/query-title' => 'library_title',
            'core/query' => 'library_loop',
            'core/query-no-results' => 'library_loop',
            'core/post-title' => 'library_title',
            'core/post-template' => 'library_layout',
        ];
        $icons_core = $this->get_icons_core();
        //var_dump($icons_core);
        $icons_block = $icons_block + $icons_core['blocks'];

        $registered_blocks = \WP_Block_Type_Registry::get_instance()->get_all_registered();
        //echo '<pre>';var_dump($registered_blocks);die();
        foreach ($registered_blocks as $name => $block_obj) {
            $block_json = wp_json_encode($block_obj);
            $block = json_decode($block_json, true);
            $blocks[$name] = $block;
            list($textdomain, $slug) = explode('/', $name, 2);
            if (empty($block['icon'])) {
                if (isset($icons_core['library_' . $slug])) {
                    $blocks[$name]['icon'] = $icons_core['library_' . $slug];
                }
                $slug_underscore = str_replace('-', '_', $slug);
                //var_dump($slug_underscore);
                if (isset($icons_core[$slug_underscore])) {
                    $blocks[$name]['icon'] = $icons_core[$slug_underscore];
                }
                //var_dump($name);
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
}
