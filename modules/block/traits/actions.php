<?php
namespace WizardBlocks\Modules\Block\Traits;

Trait Actions {
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
            }
            
            
            do_action('wizard/blocks/action');
        }
    }
}