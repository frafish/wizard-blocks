<?php

namespace WizardBlocks\Modules\Block;

use WizardBlocks\Core\Utils;
use WizardBlocks\Base\Module_Base;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Block extends Module_Base {

    use Traits\Type;
    use Traits\Metabox;
    use Traits\Attributes;
    use Traits\Pages;
    use Traits\Actions;
    use Traits\Icons;
    
    public static $assets = [
            'script' => 'js',
            'viewScript' => 'js',
            'viewScriptModule' => 'js',
            'editorScript' => 'js',
            'style' => 'css',
            'viewStyle' => 'css',
            'editorStyle' => 'css',
            'render' => 'php'
        ];

    // https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types/Common_types
    public static $mimes = [
        'js' => 'text/javascript',
        'css' => 'text/css',
    ];
    
    /**
     * Twig constructor.
     *
     * @since 1.0.1
     * @param array $args
     */
    public function __construct() {
        parent::__construct();

        add_action('init', [$this, '_init_type']);
        add_action('add_meta_boxes', [$this, 'meta_fields_add_meta_box']);
        add_action('save_post', [$this, 'meta_fields_save_meta_box_data'], 10, 3);
        
        add_filter('block_type_metadata_settings', function($settings, $metadata) {
            if (isset($metadata['file']) && !isset($settings['file'])) {
                $settings['file'] = $metadata['file'];
            }
            return $settings;
        }, 10, 2);
        
        // fix native assets url bug on win server
        add_filter( 'plugins_url', function($url, $path, $plugin) {
            $tmp = explode('/wp-content/', $url);
            if (count($tmp) == 3) {
                $url = reset($tmp).'/wp-content/'.end($tmp);
            }
            return $url;
        }, 10, 3 );

        // clean folder after block delete
        add_action('after_delete_post', function ($postid, $post) {
            // For a specific post type block
            if ('block' === $post->post_type) {
                if ($post->post_name) {
                    //delete block folder
                    $block_slug = str_replace('__trashed', '', $post->post_name);
                    $block_dir = $this->get_blocks_dir($block_slug);
                    //var_dump($block_slug); var_dump($block_dir); die();
                    if (basename($block_dir) == $block_slug) {
                        $this->dir_delete($block_dir);
                    }
                }
            }
        }, 10, 2);

        add_action('admin_menu', [$this, 'admin_menu_action']);

        // autoload blocks folder
        add_filter('wizard/blocks/dirs', [$this, 'get_blocks_dirs']);

        // filter blocks
        add_filter('wizard/blocks', function ($blocks) {
            foreach ($blocks as $key => $block) {
                $slug = basename($block);
                $block_post = $this->get_block_post($slug);
                if ($block_post) {
                    //var_dump($block_post->post_name); var_dump($block_post->post_status);
                    if ($block_post->post_status != 'publish') {
                        unset($blocks[$key]);
                    }
                }
            }
            return $blocks;
        });
        
        if ($this->is_block_edit()) {
            add_filter( 'upload_mimes', [$this, 'add_mime_types'] );
        }
        add_filter( 'wp_check_filetype_and_ext', [$this, '_add_mime_types'], 10, 5);
        
        
        add_action('init', [$this, 'unregister_blocks_disabled'], 99);
        
    }
    
    public function unregister_blocks_disabled() {
        if (!isset($_GET['post_type']) || $_GET['post_type'] != 'block') {
            $blocks_disabled = get_option(self::$blocks_disabled_key);
            if (!empty($blocks_disabled)) {
                foreach ($blocks_disabled as $block_name) {
                    if (\WP_Block_Type_Registry::get_instance()->is_registered($block_name)) {
                        $registered_blocks = \WP_Block_Type_Registry::get_instance()->unregister($block_name);
                    }
                }
                add_filter( "get_user_metadata", function($value, $object_id, $meta_key, $single, $meta_type ) use ($blocks_disabled) {
                    if ($meta_key == 'wp_persisted_preferences') {
                        global $wpdb;
                        $value = $wpdb->get_var($wpdb->prepare('SELECT meta_value FROM %i WHERE user_id = %d AND meta_key = %s', $wpdb->usermeta, $object_id, $meta_key));
                        $value = maybe_unserialize($value);
                        $value['core']['hiddenBlockTypes'] = empty($value['core']['hiddenBlockTypes']) ? $blocks_disabled : array_merge($value['core']['hiddenBlockTypes'], $blocks_disabled);
                        //echo '<pre>';var_dump($blocks_disabled); var_dump($value); die();
                        return [$value];
                    }
                    return $value;
                }, 10, 5);
            }
        }
    }
    
    public function is_block_edit() {
        return ((!empty($_GET['action']) && $_GET['action'] == 'edit' && get_post_type() == 'block') || (!empty($_GET['post_type']) && $_GET['post_type'] == 'block'));
    }
    
    public function add_mime_types( $mimes ) {
        foreach (self::$mimes as $mkey => $mime) {
            $mimes[$mkey] = $mime;
        }
        //var_dump($_GET);var_dump($_POST);
        //var_dump($mimes); die();
        return $mimes;
    }
    
    function _add_mime_types($attr, $file, $filename, $mimes, $real_mime = null){
        $proper_filename = '';
        if (!empty($attr['ext'])) {
            $ext = $attr['ext'];
        } else {
            $tmp = explode(".", $filename);
            if (count($tmp) == 1){
                return $attr;
            }
            $ext  = array_pop($tmp);
            //$proper_filename = $filename; //implode('.', $tmp);
        }
        //var_dump($_GET);var_dump($_POST);var_dump($ext); var_dump($attr); var_dump($file); var_dump($filename); var_dump($mime); var_dump($real_mime); die();
        if (isset(self::$mimes[$ext])) {
            $type = self::$mimes[$ext];
            return compact('ext', 'type', 'proper_filename');
        }
        return $attr;
    }
    
    public function get_block_post($slug) {
        //var_dump($slug);
        $posts = get_posts(
                [
                    'name' => $slug,
                    'post_type' => 'block',
                    'posts_per_page' => 1,
                    'post_status' => 'any',
                ]
        );
        if (!empty($posts)) {
            return reset($posts);
        }
        return false;
    }
    
    public function get_blocks() {
        $blocks_dirs = ['self' => WIZARD_BLOCKS_PATH.'blocks'];
        $blocks_dirs = apply_filters('wizard/blocks/dirs', $blocks_dirs);
        $blocks = [];
        foreach ($blocks_dirs as $dir) {
            if (is_dir($dir)) {
                $blocks = array_merge($blocks, glob($dir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR));
            }
        }
        $blocks = apply_filters('wizard/blocks', $blocks);
        return $blocks;
    }
    
    public function get_block_title($block, $block_post = false) {
        if (!empty($block['title'])) return $block['title'];
        if (!empty($block['name'])) {
            list($textdomain, $title) = explode('/', $block['name'], 2);
            return ucfirst($title);
        }
        if (!empty($block_post)) return $block_post->post_title;
        return __('Unknown');
    }
    
    public function get_block_slug($block) {
        if (!empty($block['name'])) {
            list($textdomain, $title) = explode('/', $block['name'], 2);
            return $title;
        }
        return 'block';
    }
    
    public function get_block_textdomain($block) {
        if (!empty($block['textdomain'])) return $block['textdomain'];
        if (!empty($block['name'])) {
            list($textdomain, $title) = explode('/', $block['name'], 2);
            return strtolower($textdomain);
        }
        return $this->get_plugin_textdomain();
    }
    
    public function insert_block_post($block, $args = []) {
        $block_post = [
            'post_title' => empty($args['title']) ? $block : $args['title'],
            'post_name' => $block,
            'post_excerpt' => empty($args['description']) ? '' : $args['description'],
            'post_type' => 'block',
            'post_status' => 'publish'
        ];
        //var_dump($block_post); die();
        $block_post_id = wp_insert_post($block_post);
        return $block_post_id;
    }
    
    public function normalize_block($block = []) {
        $normalized = [];
        foreach ($block as $key => $val) {
            if (!empty($val)) {
                $tmp = explode('_', $key);
                $key = array_shift(($tmp)).implode('', array_map('ucfirst',$tmp));
                $normalized[$key] = $val;
            }
        }
        return $normalized;
    }

    function get_blocks_dirs($blocks_dirs) {

        // uploads
        $wp_upload_dir = wp_upload_dir();
        $blocks_dirs['uploads'] = str_replace('/', DIRECTORY_SEPARATOR, $wp_upload_dir["basedir"] . DIRECTORY_SEPARATOR . 'blocks');

        // current theme
        $blocks_dirs['theme'] = str_replace('/', DIRECTORY_SEPARATOR, get_template_directory() . DIRECTORY_SEPARATOR . 'blocks');

        // current theme
        $plugin = str_replace('/', DIRECTORY_SEPARATOR, plugin_dir_path(dirname(__FILE__, 2)) . 'blocks');
        if (!in_array($plugin, $blocks_dirs)) {
            $blocks_dirs['plugin'] = $plugin;
        }

        return $blocks_dirs;
    }

    // save metadata
    public function meta_fields_save_meta_box_data($post_id, $post, $update) {
        if (!isset($_POST['meta_fields_meta_box_nonce']))
            return;
        if (!wp_verify_nonce($_POST['meta_fields_meta_box_nonce'], 'meta_fields_save_meta_box_data'))
            return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;
        if (!current_user_can('edit_post', $post_id))
            return;

        // if changed slug delete old dir
        $post_slug = get_post_field('post_name', $post_id);
        if (!empty($_POST['_block_name'])) {
            $slug = sanitize_title($_POST['_block_name']);
            if ($post_slug != $slug) {
                wp_update_post( ['ID' => $post_id, 'post_name' => $slug] );
                // delete old dir
                $old_dir = $this->get_ensure_blocks_dir($post_slug);
                $this->dir_delete($old_dir);
                $post_slug = $slug;
            }
        }
        
        $basepath = $this->get_ensure_blocks_dir($post_slug);
        
        $post_excerpt = get_post_field('post_excerpt', $post_id);
        
        $json_old = $this->get_json_data($post_slug);
        
        $textdomain = $this->get_block_textdomain($json_old);
        
        $attributes = [];
        if (!empty($_POST['_block_attributes'])) {
            $attributes = trim($_POST['_block_attributes']);
            if (substr($attributes, 0, 1) != '{') {
                $attributes = '{' . $attributes;
            }
            if (substr($attributes, -1, 1) != '}') {
                $attributes = $attributes . '}';
            }
            $attributes_json = $this->unescape($attributes);
            //var_dump($attributes); die();
            $attributes = json_decode($attributes_json, true);
            //var_dump($attributes); die();
            if ($attributes == NULL) {
                update_post_meta($post_id, '_transient_block_attributes', $attributes_json);
            } else {
                delete_post_meta($post_id, '_transient_block_attributes');
            }
            //var_dump($attributes); die();
        }
        
        $version = ""; //1.0.1";
        if (!empty($_POST['_block_version'])) {
            $version = sanitize_text_field($_POST['_block_version']);
        }

        $keywords = [];
        if (!empty($_POST['_block_keywords'])) {
            $keywords = array_filter(array_map('trim', explode(',', $_POST['_block_keywords'])));
        }

        $usesContext = [];
        if (!empty($_POST['_block_usesContext'])) {
            $usesContext = array_filter(array_map('trim', explode(',', $_POST['_block_usesContext'])));
        }

        $providesContext = [];
        if (!empty($_POST['_block_providesContext'])) {
            $providesContext = trim($_POST['_block_providesContext']);
            if (substr($providesContext, 0, 1) != '{') {
                $providesContext = '{' . $providesContext . '}';
            }
            $providesContext = $this->unescape($providesContext);
            $providesContext = json_decode($providesContext);
        }
        
        $blockHooks = [];
        if (!empty($_POST['_block_blockHooks'])) {
            $blockHooks = trim($_POST['_block_blockHooks']);
            if (substr($blockHooks, 0, 1) != '{') {
                $blockHooks = '{' . $blockHooks . '}';
            }
            $blockHooks = $this->unescape($blockHooks);
            $blockHooks = json_decode($blockHooks);
        }

        $parent = [];
        if (!empty($_POST['_block_parent'])) {
            $parent = array_filter(array_map('trim', explode(',', $_POST['_block_parent'])));
        }
        
        $allowedBlocks = [];
        if (!empty($_POST['_block_allowedBlocks'])) {
            $allowedBlocks = array_filter(array_map('trim', explode(',', $_POST['_block_allowedBlocks'])));
        }

        $ancestors = [];
        if (!empty($_POST['_block_ancestor'])) {
            $ancestors = array_filter(array_map('trim', explode(',', $_POST['_block_ancestor'])));
        }

        $supports = [];
        if (!empty($_POST['_block_supports'])) {
            //$keys = array_keys($_POST['_block_supports']);
            foreach ($_POST['_block_supports'] as $sup => $value) {
                //var_dump($value); die();
                $value = $value == 'true' ? true : false;
                $default = self::$supports[$sup]; 
                if ($value != $default) {
                    $tmp = explode('.', $sup);
                    if (count($tmp) > 1) {
                        $supports[reset($tmp)][end($tmp)] = $value;
                    } else {
                        $supports[$sup] = $value;
                    }
                }
            }
        }
        if (!empty($_POST['_block_supports_custom'])) {            
            $custom_json = $this->unescape($_POST['_block_supports_custom'], '"');
            $custom = json_decode($custom_json, true); 
            if ($custom == NULL) {
                update_post_meta($post_id, '_transient_block_supports_custom', $custom_json);
            } else {
                $supports = array_merge($supports, $custom);
                delete_post_meta($post_id, '_transient_block_supports_custom');
            }
        }
        //var_dump($supports); die();
        
        $icon = '';
        if (!empty($_POST['_block_icon'])) {
            $icon = $_POST['_block_icon'];
        } else {
            if (!empty($_POST['_block_icon_svg'])) {
                $icon = trim($_POST['_block_icon_svg']);
                $icon = str_replace(PHP_EOL, "", $icon);
                $icon = str_replace('"', "'", $icon);
                $icon = str_replace("\'", "'", $icon);
                
            }
        }
        
        $category = $_POST['_block_category'];
        
        $example = false;
        $preview = get_post_meta($post_id, '_thumbnail_id', true);
        if ($preview) {
            $preview_src = wp_get_attachment_image_url($preview, 'medium');
            //var_dump($preview_src); die();
            if (empty($attributes['preview'])) {
                $attributes['preview'] = [
                    'type' => 'string'
                ];
            }
            if (empty($example)) {
                $example = [];
                $example['attributes']['preview'] = $preview_src;
            }
        }
        
        // https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/
        $json = [
            "\$schema" => "https://schemas.wp.org/trunk/block.json",
            "apiVersion" => 3,
            "name" => $textdomain . "/" . $post_slug,
            "title" => get_the_title($post_id),
            "category" => $category,
            "parent" => $parent,
            "allowedBlocks" => $allowedBlocks,
            "ancestor" => $ancestors,
            "icon" => $icon,
            "description" => $post_excerpt,
            "keywords" => $keywords,
            "version" => $version,
            "textdomain" => $textdomain,
            "attributes" => $attributes,
            "blockHooks" => $blockHooks,
            "providesContext" => $providesContext,
            "usesContext" => $usesContext,
            "supports" => $supports,
            "example" => $example,
            /* "selectors": {
              "root": ".wp-block-my-plugin-notice"
              }, */
        ];

        
        foreach (self::$assets as $asset => $type) {
            $json[$asset] = [];
            $code = '';
            $path = $this->get_asset_file($json_old, $asset, $basepath);
            $file = basename($path);
            $file_name = basename($file, '.'.$type);
            if (!empty($_POST['_block_' . $asset.'_file'])) {
                $code = trim($_POST['_block_' . $asset.'_file']);
                $code = $this->unescape($code);
                if ($asset == 'render') {
                    $abspath_check = "<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>";
                    if (strpos($code, $abspath_check) === false ) {
                        //  ## Allowing Direct File Access to plugin files  
                        $code = $abspath_check.PHP_EOL.$code;
                    }
                }
                if ($this->get_filesystem()->put_contents($path, $code)) {
                    $json[$asset] = [ "file:./" . $file ];
                }
            }
            if (in_array($asset, ['editorScript', 'viewScript'])) {
                if ($asset == 'editorScript') {
                    if (!$code || strpos($code, 'generated by '.$this->get_plugin_textdomain()) !== false) {
                        // autogenerated - so update it
                        $code = $this->_edit($json);
                        $this->get_filesystem()->put_contents($path, $code);
                    }
                }
                if ($code) {
                    $min = '.min.';
                    $path_min = $this->get_ensure_blocks_dir($post_slug) . $file_name . $min . $type;
                    $minifier = new \MatthiasMullie\Minify\JS($code);
                    // save minified file to disk
                    $minifier->minify($path_min);
                    $json[$asset] = [ "file:./" . $file_name . (SCRIPT_DEBUG ? '.' : $min) . $type ];
                    $path = $this->get_ensure_blocks_dir($post_slug) . $file_name . (SCRIPT_DEBUG ? '' : '.min') .'.asset.php';
                    $code = "<?php return array('dependencies'=>[], 'version'=>'".gmdate('U')."');";
                    if (!file_exists($path)) {
                        $this->get_filesystem()->put_contents($path, $code);
                    }
                }
            }
        }
        
        foreach (self::$assets as $asset => $type) {
            if (!empty($_POST['_block_' . $asset])) {
                $files = Utils::explode($_POST['_block_' . $asset]);
                foreach ($files as $file) {
                    if (substr($file, 0, 5) != 'file:') {
                        // if local file copy into block folder
                        if (filter_var($file, FILTER_VALIDATE_URL)) {
                            $file = \WizardBlocks\Core\Helper::url_to_path($file);
                            if (file_exists($file)) {
                                $block_file = $basepath.basename($file);
                                if (copy($file, $block_file)) {
                                    $file = 'file:./'.basename($block_file);
                                }
                            }
                        }
                    }
                    // prevent duplicates
                    if (empty($json[$asset]) || !in_array($file, $json[$asset])) {
                        $json[$asset][] = $file;
                    }
                }
            }
            if (!empty($json_old[$asset])) {
                // clean removed assets
            }
        }
        
        // from array to string in case of single asset
        foreach (self::$assets as $asset => $type) {
            if (is_array($json[$asset]) && count($json[$asset]) == 1) {
                $json[$asset] = reset($json[$asset]);
            }
        }
        
        $json = array_filter($json);
        
        if (!empty($_POST['_block_extra'])) {            
            $extra_json = $this->unescape($_POST['_block_extra'], '"');
            $extra = json_decode($extra_json, true); 
            
            if ($extra == NULL) {
                update_post_meta($post_id, '_transient_block_extra', $extra_json);
            } else {
                $json = array_merge($json, $extra);
                delete_post_meta($post_id, '_transient_block_extra');
            }
            
        }

        $path = $basepath . 'block.json';
        $code = wp_json_encode($json, JSON_PRETTY_PRINT);
        $code = str_replace('\/', '/', $code);
        $code = str_replace("\'", "'", $code);
        $this->get_filesystem()->put_contents($path, $code);
    }

    public function get_json_data($post_slug) {
        $path = $this->get_ensure_blocks_dir($post_slug) . 'block.json';
        if (file_exists($path)) {
            $content = file_get_contents($path);
            return json_decode($content, true);
        }
        return [];
    }
    
    public function get_asset_file($json, $asset, $basepath = '') {
        $type = self::$assets[$asset];
        $asset_file = $asset.'.'.$type;
        if (!empty($json[$asset])) {
            //var_dump($json[$asset]); die();
            $asset_files = $json[$asset];
            if (is_array($asset_files)) {
                $key = array_search('file:./' . $asset_file, $asset_files);
                //var_dump($style_key);
                if ($key !== false) {
                    $asset_file = $json[$asset][$key];
                } else {
                    foreach ($json[$asset] as $tmp) {
                        if ($tmp == 'file:./'.$asset_file) {
                            $asset_file = $tmp;
                        }
                        if (substr($tmp, 0, 5) == 'file:') {
                            $asset_file = $tmp;
                        }
                    }
                }
            } else {
                if (strpos('file:./', $asset_files) !== false) {
                    $asset_file = $asset_files;
                }
            }
            if ($type != 'php') {
                $unmin = str_replace('.min.js', '.js', $asset_file);
                $unmin_file = $basepath . $unmin;
                if (file_exists($unmin_file)) {
                    $asset_file = $unmin;
                }
            }
            $asset_file = str_replace('file:', '', $asset_file);
            $asset_file = str_replace('/', DIRECTORY_SEPARATOR, $asset_file);
            //var_dump($asset_file); die();
        }
        $asset_file = $basepath . $asset_file;
        return $asset_file;
    }
    
    public function get_asset_file_contents($json, $asset, $basepath) {
        $asset_file = $this->get_asset_file($json, $asset, $basepath);
        if (file_exists($asset_file)) {
            return file_get_contents($asset_file);
        }
        return '';
    }

    public function unescape($code = '', $quote = '') {
        $code = str_replace('\"', $quote ? $quote : '"', $code);
        $code = str_replace("\'", $quote ? $quote : "'", $code);
        $code = str_replace("\/", "/", $code);
        return $code;
    }

    /**
     * Gets the path to uploaded file.
     *
     * @return string
     */
    private function get_blocks_dir($slug = '') {
        $wp_upload_dir = wp_upload_dir();
        $path = $wp_upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'blocks';
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);

        $blocks_dirs = ['uploads' => $path];
        $blocks_dirs = apply_filters('wizard/blocks/dirs', $blocks_dirs);
        foreach ($blocks_dirs as $dir) {
            if (is_dir($dir . DIRECTORY_SEPARATOR . $slug)) {
                $path = $dir;
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
    private function get_ensure_blocks_dir($slug = '') {
        $path = $this->get_blocks_dir($slug);
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

        wp_mkdir_p($path . DIRECTORY_SEPARATOR . $slug);
        return $path . DIRECTORY_SEPARATOR . $slug . DIRECTORY_SEPARATOR;
    }

    public function dir_delete($dir) {
        if (is_dir($dir)) {
            return $this->get_filesystem->delete($dir, true);
        }
        return false;
    }

    public function dir_copy($src, $dst) {
        // open the source directory 
        $dir = opendir($src);
        // Make the destination directory if not exist 
        @wp_mkdir_p($dst);
        // Loop through the files in source directory 
        foreach (scandir($src) as $file) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if (is_dir($src . DIRECTORY_SEPARATOR . $file)) {
                    // Recursively calling custom copy function 
                    // for sub directory  
                    $this->dir_copy($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file);
                } else {
                    copy($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file);
                }
            }
        }
        closedir($dir);
    }
    
    public function get_filesystem() {
        global $wp_filesystem;

        if (!$wp_filesystem) {
            require_once( ABSPATH . DIRECTORY_SEPARATOR . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'file.php' ); // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude.FileIncludeFound
            $context = apply_filters('request_filesystem_credentials_context', false);
            $creds = request_filesystem_credentials(site_url(), '', false, $context, null);
            \WP_Filesystem($creds, $context);
        }

        return $wp_filesystem;
    }
}
