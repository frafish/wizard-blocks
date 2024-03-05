<?php

namespace WizardBlocks\Modules\Block\Traits;

trait Pages {

    public function admin_menu_action() {
        add_submenu_page(
                'edit.php?post_type=block',
                __('Tools'),
                __('Tools'),
                'manage_options',
                'ttools',
                [$this, 'twig_tools'] //callback function
        );

        add_submenu_page(
                'edit.php?post_type=block',
                __('Cloud', 'menu-test'),
                __('Cloud', 'menu-test'),
                'manage_options',
                'ttcloud',
                [$this, 'twig_cloud'] //callback function
        );
    }

    public function _notice($message, $type = 'success') {
        echo '<div class="notice is-dismissible notice-' . $type . ' notice-alt"><p>' . $message . '</p></div>';
    }

    public function twig_cloud() {
        ?>
        <h1>CLOUD</h1>
        <script>
            jQuery.ajax({
                type: "GET",
                url: "https://expandit.site/wp-json/wp/v2/users",
                cache: false,
                crossDomain: true,
                dataType: 'json',
                xhrFields: {
                    withCredentials: true
                },
                success: function (data) {
                    console.log(data);
                }
            });
        </script>
        <?php
    }

    public function twig_tools() {

        $this->execute_actions();

        ?>

        <h1><?php _e('ACTIONS', 'wizard-blocks'); ?></h1>

        <div class="card-row" style="display: flex;">
        <div class="card" style="width: 100%;">
            <h2><?php _e('IMPORT', 'wizard-blocks'); ?></h2>
            <p><?php _e('Add your Custom Blocks importing the block zip.', 'wizard-blocks'); ?><br><?php _e('Try to download and import some official Block examples:', 'wizard-blocks'); ?> <a target="_blank" href="https://github.com/WordPress/block-development-examples?tab=readme-ov-file#block-development-examples"><span class="dashicons dashicons-download"></span></a></p>
            <form action="?post_type=block&page=ttsettings&action=import" method="POST" enctype="multipart/form-data">
                <input type="file" name="zip">
                <button class="btn button" type="submit"><?php _e('Import', 'wizard-blocks'); ?></button>
            </form>
        </div>

        <div class="card" style="width: 100%;">
            <h2><?php _e('EXPORT', 'wizard-blocks'); ?></h2>
            <p><?php _e('Download all your Custom Blocks for a quick backup.', 'wizard-blocks'); ?><br><?php _e('You can then  install them as native blocks.', 'wizard-blocks'); ?> <a target="_blank" href="https://developer.wordpress.org/block-editor/getting-started/fundamentals/registration-of-a-block/"><span class="dashicons dashicons-info"></span></a></p>
            <a class="btn button" href="?post_type=block&page=ttsettings&action=export"><?php _e('Export', 'wizard-blocks'); ?></a>
        </div>
        </div>
            
        <?php
        $blocks_dir = apply_filters('wizard/dirs', []);
        $blocks = $this->get_registered_blocks();
        $blocks_count = [];
        foreach ($blocks as $name => $block) {
            $textdomain = $this->get_block_textdomain($block);
            $blocks_count[$textdomain] = empty($blocks_count[$textdomain]) ? 1 : ++$blocks_count[$textdomain];
        }
        ?>
        <div class="card" style="max-width: 98%">
            <h2><?php _e('Blocks', 'wizard-blocks'); ?></h2>
            <ul class="subsubsub blocks-filter">
                <li class="all"><a class="current" href="#"><?php _e('All', 'wizard-blocks'); ?> <span class="count">(<?php echo count($blocks); ?>)</span></a></li>
                <?php foreach ($blocks_count as $textdomain => $bc) { ?>
                    | <li class><a href="#<?php echo $textdomain; ?>"><?php _e(ucfirst($textdomain)); ?> <span class="count">(<?php echo $bc; ?>)</span></a></li>
                <?php } ?>
            </ul>
            <script>
            jQuery('.blocks-filter a').on('click', function() {
                jQuery('.blocks .hentry').show();
                let filter = jQuery(this).attr('href').replace('#', '');
                if (filter) {
                    console.log(filter);
                    jQuery('.blocks .hentry').hide();
                    jQuery('.blocks .hentry.block-'+filter).show();
                }
                return false;
            });  
            </script>
            
            <table class="wp-list-table widefat fixed striped table-view-list blocks">
                <thead>
                    <tr>
                        <th scope="col" id="icon" class="manage-column column-icon" style="width: 30px;"><?php _e('Icon', 'wizard-blocks'); ?></th>
                        <th scope="col" id="title" class="manage-column column-title column-primary"><span><?php _e('Title', 'wizard-blocks'); ?></span></th>
                        <th scope="col" id="folder" class="manage-column column-description"><?php _e('Description', 'wizard-blocks'); ?></th>
                        <th scope="col" id="folder" class="manage-column column-folder"><?php _e('Folder', 'wizard-blocks'); ?></th>
                        <th scope="col" id="actions" class="manage-column column-actions"><?php _e('Actions', 'wizard-blocks'); ?></th>
                    </tr>
                </thead>

                <tbody id="the-list">
        <?php
        foreach ($blocks as $name => $block) {
            $block_post = false;
            $block_slug = $name;
            if (!empty($block['folder'])) {
                $block_slug = basename($block['folder']);
                $block_post = $this->get_block_post($block_slug);
            }
            $textdomain = $this->get_block_textdomain($block);
            ?>
                        <tr id="post-<?php echo $block_post ? $block_post->ID : 'xxx'; ?>" class="iedit author-self type-block status-publish hentry block-<?php echo $textdomain; ?><?php echo in_array($textdomain, ['core','wizard','wizard-blocks']) ? '' : ' block-extra'; ?>">
                            <td class="icon column-icon" data-colname="Icon">
            <?php if (!empty($block['icon'])) {
                echo (substr($block['icon'], 0, 5) == '<svg ') ? $block['icon'] : '<span class="dashicons dashicons-' . $block['icon'] . '"></span> ';
            } ?>
                            </td>
                            <td class="title column-title has-row-actions column-primary page-title" data-colname="<?php _e('Title', 'wizard-blocks'); ?>">
                                <strong>
                                    <?php if ($block_post) { ?><a class="row-title" href="<?php echo get_edit_post_link($block_post->ID); ?>" aria-label=""><?php } ?>
                                        <abbr title="<?php echo $name; ?>"><?php echo $this->get_block_title($block, $block_post); ?></abbr>
                                        <?php if ($block_post) { ?></a><?php } ?>
                                </strong>
                            </td>
                            <td class="description column-description" data-colname="Description">
                                <?php echo empty($block['description']) ? '' : $block['description']; ?>
                            </td>
                            <td class="folder column-folder" data-colname="<?php _e('Folder', 'wizard-blocks'); ?>">
                                <?php
                                echo $textdomain;
                                
                                if (!empty($block['render_callback'])) {
                                    if (is_string($block['render_callback'])) {
                                        if (str_starts_with($block['render_callback'], 'render_block_core_')) {
                                            //echo _('core');
                                        }
                                    }
                                }

                                if (!empty($block['folder'])) {
                                    $tmp = explode('wp-content', dirname($block['folder']), 2);
                                    $block_dir = end($tmp);
                                    $icon = 'upload';
                                    if (strpos($block_dir, 'themes') !== false) {
                                        $icon = 'admin-appearance';
                                    }
                                    if (strpos($block_dir, 'plugins') !== false) {
                                        $icon = 'admin-plugins';
                                    }
                                    ?>
                                    <span class="dashicons dashicons-<?php echo $icon; ?>"></span>
                                    <?php
                                    echo $block_dir;

                                    foreach ($blocks_dir as $dkey => $adir) {
                                        if (strpos($adir, $block_dir) == false) {
                                            ?>
                                            <a class="btn button button-link-delete" href="?post_type=block&page=ttsettings&action=move&block=<?php echo $block_slug; ?>&dir=<?php echo $dkey; ?>"><?php _e('Move to ', 'wizard-blocks');
                        echo $dkey;
                                            ?></a>
                                            <?php
                                        }
                                    }
                                }
                                ?>
                            </td>	
                            <td class="actions column-actions" data-colname="Actions">
                                <a class="btn button" href="?post_type=block&page=ttsettings&action=variations&block=<?php echo $block_slug; ?>"><?php _e('Add Variation', 'wizard-blocks'); ?></a>
                                <?php if (!empty($block['folder'])) { ?>
                                    <a class="btn button" href="?post_type=block&page=ttsettings&action=download&block=<?php echo $block_slug; ?>"><?php _e('Download', 'wizard-blocks'); ?></a>
                                <?php } ?>
                                <?php /* <a class="btn button" href="?post_type=block&page=ttsettings&action=sync&block=<?php echo $block_slug?>"><?php _e('Sync', 'wizard-blocks'); ?></a> */ ?>
                                <?php if (!$block_post) { ?>
                                    <a class="btn button button-primary" href="?post_type=block&page=ttsettings&action=import&block=<?php echo $block_slug; ?>"><?php _e('Import', 'wizard-blocks'); ?></a>
            <?php } ?>
                                <?php do_action('wizard/blocks/action/btn', $block); ?>
                            </td>		
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    function execute_actions() {
        if (!empty($_GET['action'])) {

            $blocks_dir = apply_filters('wizard/dirs', []);
            $dirs = wp_upload_dir();
            $basedir = str_replace('/', DIRECTORY_SEPARATOR, $dirs['basedir']) . DIRECTORY_SEPARATOR;

            switch ($_GET['action']) {
                case 'import':
                    if (!empty($_FILES["zip"]["tmp_name"])) {
                        //var_dump($_FILES); die();
                        $target_file = $basedir . basename($_FILES["zip"]["name"]);
                        $tmpdir = $basedir . 'tmp';
                        if (move_uploaded_file($_FILES["zip"]["tmp_name"], $target_file)) {
                            $zip = new \ZipArchive;
                            if ($zip->open($target_file) === TRUE) {
                                $zip->extractTo($tmpdir);
                                $zip->close();

                                $jsons = glob($tmpdir . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*.json');
                                foreach ($jsons as $json) {
                                    //var_dump($json);
                                    $jfolder = dirname($json);
                                    //var_dump($jfolder);
                                    $block = basename($jfolder);
                                    //var_dump($block);
                                    if ($block == 'src') {
                                        continue;
                                    }
                                    $json_code = file_get_contents($json);
                                    $args = json_decode($json_code, true);
                                    //if (!empty($args['$schema'])) {
                                    if (!empty($args['name'])) {
                                        //var_dump($args); die();
                                        // is a valid block
                                        list($domain, $block) = explode('/', $args['name'], 2);
                                        $dest = $this->get_ensure_blocks_dir($block);
                                        //var_dump($jfolder); var_dump($dest); die();
                                        $this->dir_copy($jfolder, $dest);
                                        $block_post = $this->get_block_post($block);
                                        if (!$block_post) {
                                            $block_post_id = $this->insert_block_post($block, $args);
                                        }
                                    }
                                    //}
                                }
                                $this->_notice(__('Blocks imported!', 'wizard-blocks'));
                            }
                            // clean tmp
                            $this->dir_delete($tmpdir);
                            unlink($target_file);
                        }
                    }
                    if (!empty($_GET['block'])) {
                        $block = $_GET['block'];
                        $block_post = $this->get_block_post($block);
                        if (!$block_post) {
                            $args = $this->get_json_data($block);
                            $block_post_id = $this->insert_block_post($block, $args);
                        }
                        $this->_notice(__('Block imported!', 'wizard-blocks'));
                    }

                    break;
                case 'export':

                    // Make sure our zipping class exists
                    if (!class_exists('ZipArchive')) {
                        die('Cannot find class ZipArchive');
                    }

                    $zip = new \ZipArchive();

                    // Set the system path for our zip file
                    $filename = 'blocks_' . date('Y-m-d') . '.zip';
                    $filepath = $basedir . $filename;

                    // Remove any existing file with that filename
                    if (file_exists($filepath))
                        unlink($filepath);

                    // Create and open the zip file
                    if (!$zip->open($filepath, \ZipArchive::CREATE)) {
                        die('Failed to create zip at ' . $filepath);
                    }

                    $blocks_dir = apply_filters('wizard/dirs', []);
                    foreach ($blocks_dir as $adir) {
                        // Add any other files by directory
                        $block_files = $adir . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*.*';
                        $blocks = glob($block_files);
                        //var_dump($block_files); die();
                        foreach ($blocks as $file) {
                            list($tmp, $local) = explode($adir . DIRECTORY_SEPARATOR, $file, 2);
                            //var_dump($local);
                            $zip->addFile($file, $local);
                        }
                    }

                    $zip->close();

                    $download_url = $dirs['baseurl'] . '/' . $filename;
                    $this->_notice(__('Blocks exported!', 'wizard-blocks') . ' <a href="' . $download_url . '"><span class="dashicons dashicons-download"></span></a>');
                    ?>
                    <script>
                        // Simulate an HTTP redirect:
                        setTimeout(() => {
                            let download = "<?php echo $download_url; ?>";
                            window.location.replace(download);
                        }, 1000);
                    </script>
                    <?php
                    break;

                case 'download':

                    if (!empty($_GET['block'])) {
                        // Make sure our zipping class exists
                        if (!class_exists('ZipArchive')) {
                            die('Cannot find class ZipArchive');
                        }

                        $zip = new \ZipArchive();

                        $block_slug = $_GET['block'];
                        $block_json = $this->get_json_data($block_slug);
                        // Set the system path for our zip file
                        $filename = 'block_' . $block_slug . '_' . $block_json['version'] . '.zip';
                        $filepath = $basedir . $filename;

                        // Remove any existing file with that filename
                        if (file_exists($filepath))
                            unlink($filepath);

                        // Create and open the zip file
                        if (!$zip->open($filepath, \ZipArchive::CREATE)) {
                            die('Failed to create zip at ' . $filepath);
                        }

                        $block_dir = $this->get_ensure_blocks_dir($block_slug);
                        $block_basedir = $this->get_blocks_dir($block_slug) . DIRECTORY_SEPARATOR;
                        // Add any other files by directory
                        $blocks = glob($block_dir . '*.*');
                        foreach ($blocks as $file) {
                            list($tmp, $local) = explode($block_basedir, $file, 2);
                            $zip->addFile($file, $local);
                        }

                        $zip->close();

                        $download_url = $dirs['baseurl'] . '/' . $filename;
                        $this->_notice(__('Block exported!', 'wizard-blocks') . ' <a href="' . $download_url . '"><span class="dashicons dashicons-download"></span></a>');
                        ?>
                        <script>
                            // Simulate an HTTP redirect:
                            setTimeout(() => {
                                let download = "<?php echo $download_url; ?>";
                                window.location.replace(download);
                            }, 1000);
                        </script>
                        <?php
                    }
                    break;

                case 'move':

                    if (!empty($_GET['block'])) {
                        $block_slug = $_GET['block'];
                        $block_dir = $this->get_ensure_blocks_dir($block_slug);
                        $blocks_dir = apply_filters('wizard/dirs', []);
                        //if (strpos('uploads', $block_dir) !== false)
                        if (!empty($_GET['dir'])) {
                            $alternate = $_GET['dir'];
                            if (!empty($blocks_dir[$alternate])) {
                                $alternate_dir = $blocks_dir[$alternate] . DIRECTORY_SEPARATOR . $block_slug;
                                rename($block_dir, $alternate_dir);
                                $this->_notice(__('Block moved!', 'wizard-blocks'));
                            }
                        }
                    }
                    break;
            }
            do_action('wizard/blocks/action', $this);
        }
    }
    
    function get_icons_core() {
        $icons_core = [];
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
                        if (empty($svg_wrap['width'])) $svg_wrap['width'] = 24;
                        if (empty($svg_wrap['height'])) $svg_wrap['height'] = 24;
                        foreach ($svg_wrap as $key => $value) {
                            $svg .= ' '.$key.'="'.$value.'"';
                        }
                        $svg .= ' aria-hidden="true" focusable="false">';
                        foreach ($svg_objs as $svg_obj) {
                            list($type, $svg_inner_json) = explode(',', $svg_obj, 2);
                            $svg_inner_json = str_replace(')', '', $svg_inner_json);
                            $svg_inner = json_decode($svg_inner_json, true);
                            if ($svg_inner) {
                                $svg .= '<'. strtolower($type);
                                foreach ($svg_inner as $key => $value) {
                                    $svg .= ' '.$key.'="'.$value.'"';
                                }
                                $svg .= '></'. strtolower($type) .'>';
                            }
                        }
                        $svg .= '</svg>';
                        $icons_core[$name] = $svg;
                    }
                }
            }
        }
        //var_dump($icons_core);
        //die();
        // ICONS: \wp-includes\js\dist\block-library.js
        //<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M6 5V18.5911L12 13.8473L18 18.5911V5H6Z"></path></svg>
        return $icons_core;
    }
    
    function get_registered_blocks() {
        
        $blocks = [];
        
        $blocks_dir = apply_filters('wizard/dirs', []);
        unset($blocks_dir['plugin']);

        // get_theme_update_available
        // wp_update_themes
        /* if ($update) {
          unset($blocks_dir['theme']);
          }
         */

        $icons_block = [
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
            
        ];
        $icons_core = $this->get_icons_core();
        //var_dump($icons_core);

        

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
