<?php

namespace WizardBlocks\Modules\Block\Traits;

if ( ! defined( 'ABSPATH' ) ) exit; 

Trait Filesystem {
    
    /**
     * Gets the path to uploaded file.
     *
     * @return string
     */
    public function get_blocks_dir($slug = '', $textdomain = '*') {
        $wp_upload_dir = wp_upload_dir();
        $path = $wp_upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'blocks';
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        $blocks_dirs = ['uploads' => $path];
        $blocks_dirs = apply_filters('wizard/blocks/dirs', $blocks_dirs);
            
        if ($slug) {
            $path = false; // not exists yet
            foreach ($blocks_dirs as $dir) {
                /*if (is_dir($dir . DIRECTORY_SEPARATOR . $slug)) {
                    $path = $dir;
                }*/
                $find = $dir.DIRECTORY_SEPARATOR.$textdomain.DIRECTORY_SEPARATOR.$slug;
                $paths = glob($find); // TODO: any textdomain?!
                if (!empty($paths)) {
                    $path = reset($paths);
                }
            }
        }

        /**
         * Blocks upload file path.
         *
         * @since 1.0.0
         *
         * @param string $path Path to uploaded files.
         */
        $path = apply_filters('wizard/blocks/path', $path);

        return $path;
    }

    /**
     * This function returns the uploads folder after making sure
     * it is created and has protection files
     * @return string
     */
    private function get_ensure_blocks_dir($slug = '', $textdomain = '*') {
        //var_dump($slug); var_dump($textdomain);
        $path = $this->get_blocks_dir($slug, $textdomain);
        if ($slug) {
            // generate block folder textdomain/slug
            $textdomain = $textdomain == '*' ? $this->get_plugin_textdomain() : $textdomain;
            $path = $this->get_blocks_dir() . DIRECTORY_SEPARATOR . $textdomain . DIRECTORY_SEPARATOR . $slug . DIRECTORY_SEPARATOR;
            wp_mkdir_p($path);
        } else {
            // init blocks folder
            if (!file_exists($path . DIRECTORY_SEPARATOR . 'index.php')) {
                wp_mkdir_p($path);
                $files = [
                    [
                        'file' => 'index.php',
                        'content' => [
                            '<?php',
                            '// Silence is golden.',
                        ],
                    ],
                    [
                        'file' => '.htaccess',
                        'content' => [
                            'Options -Indexes',
                            '<ifModule mod_headers.c>',
                            '	<Files *.*>',
                            '       Header set Content-Disposition attachment',
                            '	</Files>',
                            '</IfModule>',
                        ],
                    ],
                ];
                foreach ($files as $file) {
                    if (!file_exists(trailingslashit($path) . $file['file'])) {
                        $content = implode(PHP_EOL, $file['content']);
                        @ $this->get_filesystem()->put_contents(trailingslashit($path) . $file['file'], $content);
                    }
                }
            }
        }
        
        return $path;
    }

    public function dir_delete($dir) {
        if ($dir && is_dir($dir)) {
            return $this->get_filesystem()->delete($dir, true);
        }
        return false;
    }

    public function copy_dir($src, $dst, $skip = []) {
        // open the source directory 
        if (substr($src,-1) == DIRECTORY_SEPARATOR) $src = substr($src, 0, -1);
        if (substr($dst,-1) == DIRECTORY_SEPARATOR) $dst = substr($dst, 0, -1);
        // Make the destination directory if not exist 
        @wp_mkdir_p($dst);
        // Loop through the files in source directory 
        /*
        //$dir = opendir($src);
        foreach (scandir($src) as $file) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if (is_dir($src . DIRECTORY_SEPARATOR . $file)) {
                    // Recursively calling custom copy function 
                    // for sub directory  
                    $this->copy_dir($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file, $skip);
                } else {
                    copy($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file);
                }
            }
        }
        closedir($dir);
        */
        $this->get_filesystem(); //maybe init $wp_filesystem
        copy_dir($src, $dst, $skip);
    }
    
    public function get_filesystem() {
        global $wp_filesystem;

        if (!$wp_filesystem) {
            require_once( ABSPATH . DIRECTORY_SEPARATOR . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'file.php' ); // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude.FileIncludeFound
            $creds = request_filesystem_credentials(site_url());
            \WP_Filesystem($creds);
        }
        //var_dump($wp_filesystem);
        return $wp_filesystem;
    }
    
    public function get_asset_file_contents($json, $asset, $basepath) {
        if (is_file($basepath)) {
            $asset_file = $basepath;
        } else {
            $asset_file = $this->get_asset_file($json, $asset, $basepath);
        }
        if (file_exists($asset_file)) {
            return $this->get_filesystem()->get_contents($asset_file);
        }
        return '';
    }
}
