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

        if (!empty($_GET['action'])) {

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
                    foreach($blocks_dir as $adir) {
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
                        $block_basedir = $this->get_blocks_dir($block_slug).DIRECTORY_SEPARATOR;
                        // Add any other files by directory
                        $blocks = glob($block_dir . '*.*');
                        foreach ($blocks as $file) {
                            list($tmp, $local) = explode($block_basedir, $file, 2);
                            $zip->addFile($file, $local);
                        }

                        $zip->close();

                        $download_url = $dirs['baseurl'] . '/' . $filename;
                        $this->_notice( __('Block exported!', 'wizard-blocks') . ' <a href="' . $download_url . '"><span class="dashicons dashicons-download"></span></a>');
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
                                $alternate_dir = $blocks_dir[$alternate].DIRECTORY_SEPARATOR.$block_slug;
                                rename($block_dir, $alternate_dir);
                                $this->_notice( __('Block moved!', 'wizard-blocks') );
                            }
                        }
                    }
                    break;
            }
        }
        ?>

        <h1>ACTIONS</h1>

        <div class="card">
            <h2>IMPORT</h2>
            <p>Download some official Block examples: <a target="_blank" href="https://github.com/WordPress/block-development-examples?tab=readme-ov-file#block-development-examples"><span class="dashicons dashicons-download"></span></a></p>
            <form action="?post_type=block&page=ttsettings&action=import" method="POST" enctype="multipart/form-data">
                <input type="file" name="zip">
                <button class="btn button" type="submit"><?php _e('Import', 'wizard-blocks'); ?></button>
            </form>
        </div>

        <div class="card">
            <h2><?php _e('EXPORT', 'wizard-blocks'); ?></h2>
            <a class="btn button" href="?post_type=block&page=ttsettings&action=export"><?php _e('Export', 'wizard-blocks'); ?></a>
        </div>

        <div class="card" style="max-width: 98%">
            <h2><?php _e('Blocks', 'wizard-blocks'); ?></h2>
            <table class="wp-list-table widefat fixed striped table-view-list posts">
                <thead>
                    <tr>
                        <th scope="col" id="icon" class="manage-column column-icon" style="width: 30px;"><?php _e('Icon', 'wizard-blocks'); ?></th>
                        <th scope="col" id="title" class="manage-column column-title column-primary"><span><?php _e('Title', 'wizard-blocks'); ?></span></th>
                        <th scope="col" id="folder" class="manage-column column-folder"><?php _e('Folder', 'wizard-blocks'); ?></th>
                        <th scope="col" id="actions" class="manage-column column-actions"><?php _e('Actions', 'wizard-blocks'); ?></th>
                    </tr>
                </thead>

                <tbody id="the-list">
        <?php
        $blocks_dir = apply_filters('wizard/dirs', []);
        unset($blocks_dir['plugin']);
        
        // get_theme_update_available
        // wp_update_themes
        /* if ($update) {
        unset($blocks_dir['theme']);
        }
        */
        
        $blocks = $this->get_blocks();
        foreach ($blocks as $ablock) {
            $block_slug = basename($ablock);
            $block = $this->get_json_data($block_slug);
            $block_post = $this->get_block_post($block_slug);
            ?>
                        <tr id="post-<?php echo $block_post ? $block_post->ID : 'xxx'; ?>" class="iedit author-self type-block status-publish hentry">
                            <td class="icon column-icon" data-colname="Icon">
                                <?php echo empty($block['icon']) ? '' : '<span class="dashicons dashicons-' . $block['icon'] . '"></span> '; ?>
                            </td>
                            <td class="title column-title has-row-actions column-primary page-title" data-colname="<?php _e('Title', 'wizard-blocks'); ?>">
                                <strong>
                                    <?php if ($block_post) { ?><a class="row-title" href="<?php echo get_edit_post_link($block_post->ID); ?>" aria-label=""><?php } ?>
                                    <?php echo $this->get_block_title($block, $block_post); ?>
                                    <?php if ($block_post) { ?></a><?php } ?>
                                </strong>
                            </td>
                            <td class="folder column-folder" data-colname="<?php _e('Folder', 'wizard-blocks'); ?>">
                                <?php
                                $tmp = explode('wp-content', dirname($ablock), 2);
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
                                        <a class="btn button button-link-delete" href="?post_type=block&page=ttsettings&action=move&block=<?php echo $block_slug; ?>&dir=<?php echo $dkey; ?>"><?php _e('Move to ', 'wizard-blocks'); echo $dkey; ?></a>
                                <?php
                                    }
                                }
                                ?>
                            </td>	
                            <td class="actions column-actions" data-colname="Actions">
                                <a class="btn button" href="?post_type=block&page=ttsettings&action=download&block=<?php echo $block_slug; ?>"><?php _e('Download', 'wizard-blocks'); ?></a>
                                <?php /* <a class="btn button" href="?post_type=block&page=ttsettings&action=sync&block=<?php echo $block_slug?>"><?php _e('Sync', 'wizard-blocks'); ?></a> */ ?>
                                <?php if (!$block_post) { ?>
                                    <a class="btn button button-primary" href="?post_type=block&page=ttsettings&action=import&block=<?php echo $block_slug; ?>"><?php _e('Import', 'wizard-blocks'); ?></a>
                                <?php } ?>
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

}
