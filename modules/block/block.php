<?php

namespace WizardBlocks\Modules\Block;

use WizardBlocks\Core\Utils;
use WizardBlocks\Base\Module_Base;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Block extends Module_Base {

    use Traits\Type;
    use Traits\Assets;
    use Traits\Filesystem;
    use Traits\Metabox;
    use Traits\Attributes;
    use Traits\Pages;
    use Traits\Actions;
    use Traits\Icons;
    use Traits\Save;
    
    public static $instance = null;
    
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
     * Block constructor.
     *
     * @since 1.0.1
     * @param array $args
     */
    public function __construct() {
        parent::__construct();

        add_action('init', [$this, '_init_type']);
        
        if (is_admin()) {
            if ($this->is_block_archive()) {
                add_action('admin_enqueue_scripts', function() {
                    wp_enqueue_style('wizard-blocks-archive', WIZARD_BLOCKS_URL.'modules/block/assets/css/block-archive.css', [], '1.2.0');
                    wp_print_scripts();
                });
                add_action( 'pre_get_posts', [$this,'block_admin_order'] );
            }
            /*if ($this->is_block_edit()) {
                add_action('admin_enqueue_scripts', function() {
                    //wp_print_scripts();
                });
            }*/
        }
        
        /* REVISION */
        add_filter('wp_save_post_revision_post_has_changed', [$this, 'has_block_changed'], 10, 3);
        add_action('_wp_put_post_revision', [$this, 'save_block_revision'], 10, 2);
        add_filter('wizard_blocks/before_save', [$this, 'generate_block_zip_for_revision'], 10, 3);
        add_filter('wp_get_revision_ui_diff', [$this, 'get_revision_ui_diff'], 10, 3);
        add_action('wp_restore_post_revision', [$this, 'restore_block_revision'], 10, 2 );
                
        add_action('add_meta_boxes', [$this, 'meta_fields_add_meta_box']);
        add_action('save_post', [$this, 'meta_fields_save_meta_box_data'], 10, 3);
        
        if (is_admin() && isset($_GET['post_type']) && $_GET['post_type'] == self::$cpt_name) {
            add_filter( 'gettext', [$this, 'gettext'], 10, 3);
        }
        
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
        
        // allow upload js and css
        if ($this->is_block_edit()) {
            add_filter( 'upload_mimes', [$this, 'add_mime_types'] );
        }
        add_filter( 'wp_check_filetype_and_ext', [$this, '_add_mime_types'], 10, 5);
        
        
        //add_action('init', [$this, 'unregister_blocks_disabled'], 99);
        add_filter( 'allowed_block_types_all', [$this, 'allowed_block_types'], 10, 2 );
        
        // add specific columns on admin archive tables
        add_filter('manage_block_posts_columns', [$this, 'add_block_columns']);
        add_action('manage_block_posts_custom_column', [$this, 'populate_block_columns'], 10, 2);
        
    }
    
    /**
     * Instance.
     *
     * Ensures only one instance of the block class is loaded or can be loaded.
     *
     * @since 1.0.0
     * @access public
     * @static
     *
     * @return Plugin An instance of the class.
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();

            /**
             * on loaded.
             *
             * Fires when was fully loaded and instantiated.
             *
             * @since 1.0.1
             */
            do_action('wizard-blocks/loaded/blocks', self::$instance);
        }

        return self::$instance;
    }
    
    public function render($attributes, $content, $block) {
        
        global $post;
        $global_post = $post;
        
        // set Context
        if (empty($block->context)) {
            $block->context = [];
            $block->context['postId'] = get_the_ID();
        }
        if (empty($block->blockName)) {
            $block->blockName = $block->name;
            \WP_Block_Supports::$block_to_render = json_decode(wp_json_encode($block), true);
        }
        
        //echo $block->render($attributes, $content);
        //var_dump($block->render_callback);
        
        ob_start();
        if (is_object($block->render_callback)) {
            $render = $block->render_callback;
            echo $render($attributes, $content, $block);
        }
        if (is_string($block->render_callback)) {
            if (is_callable($block->render_callback)) {
                //var_dump($block->render_callback);
                echo call_user_func($block->render_callback, $attributes, $content, $block);
            }
        }
        //$reflection = new \ReflectionFunction($closure);
        //$arguments  = $reflection->getParameters();
        //var_dump(get_class($block));
        //var_dump($arguments);
       
        // ////wp-includes/class-wp-block.php:433
        //echo $block->render();
        
        //////wp-includes/class-wp-block-type.php:466
        //echo $block->render($attributes, $content); // FIX: native is bugged, should pass $this as 3rd parameter
        $block_content = ob_get_clean();
        
        $post = $global_post;

        $this->enqueue_block_assets($block);
        return $block_content;
    }
    
    public function allowed_block_types($allowed_block_types, $block_editor_context) {
        if (!isset($_GET['post_type']) || $_GET['post_type'] != 'block') {
            $blocks_disabled = get_option(self::$blocks_disabled_key);
            //var_dump($blocks_disabled); die();
            if (!empty($blocks_disabled)) {
                $registered_blocks = \WP_Block_Type_Registry::get_instance()->get_all_registered();
                if (is_bool($allowed_block_types)) $allowed_block_types = [];
                foreach ($registered_blocks as $name => $block_obj) {
                    if (!in_array($name, $blocks_disabled)) {
                        $allowed_block_types[] = $name;
                    }
                }
            }
        }
        //var_dump($allowed_block_types); die();
        return $allowed_block_types;
    }
    
    public function unregister_blocks_disabled() {
        if ($this->is_block_edit()) {
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
        return ((!empty($_GET['action']) && $_GET['action'] == 'edit' && !empty($_GET['post']) && get_post_type(intval($_GET['post'])) == self::get_cpt_name()) || (!empty($_GET['post_type']) && $_GET['post_type'] == self::get_cpt_name()));
    }
    
    public function is_block_archive() {
        return (empty($_GET['action']) && empty($_GET['page']) && (!empty($_GET['post_type']) && $_GET['post_type'] == self::get_cpt_name()));
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
        $tmp = explode('/', $slug, 2);
        $slug = end($tmp); // maybe is passed name
        //var_dump($slug);
        $posts = get_posts(
                [
                    'name' => $slug,
                    'post_type' => self::get_cpt_name(),
                    'posts_per_page' => 1,
                    'post_status' => 'any',
                ]
        );
        if (!empty($posts)) {
            return reset($posts);
        }
        return false;
    }
    
    public static function get_wb_blocks() {
        return self::instance()->get_blocks();
    }
    
    public function get_blocks() {
        $blocks_dirs = ['self' => WIZARD_BLOCKS_PATH.'blocks'];
        $blocks_dirs = apply_filters('wizard/blocks/dirs', $blocks_dirs);
        $blocks = [];
        foreach ($blocks_dirs as $dir) {
            if (is_dir($dir)) {
                $blocks = array_merge($blocks, glob($dir . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR));
                //var_dump($blocks);
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
        return __('Unknown', 'wizard-blocks');
    }
    
    public function get_block_slug($block) {
        if (!empty($block['name'])) {
            list($textdomain, $title) = explode('/', $block['name'], 2);
            return $title;
        }
        if (is_string($block)) {
            $tmp = explode('/', $block, 2);
            return end($tmp);
        }
        return 'block';
    }
    
    public function get_block_textdomain($block) {
        if (!empty($block['name'])) {
            $tmp = explode('/', $block['name'], 2);
            if (count($tmp) > 1) {
                list($textdomain, $title) = $tmp;
                return strtolower($textdomain);
            }
        }
        if (!empty($block['textdomain'])) return $block['textdomain'];
        if ($user = wp_get_current_user()) {
            return $user->user_nicename;
        }
        return $this->get_plugin_textdomain();
    }
    
    public function insert_block_post($block, $args = []) {
        $block_post = $this->get_block_post($block);
        if ($block_post) {
            $block_post_id = $block_post->ID;
        } else {
            $block_post = [
                'post_title' => empty($args['title']) ? $block : $args['title'],
                'post_name' => $block,
                'post_excerpt' => empty($args['description']) ? '' : $args['description'],
                'post_type' => 'block',
                'post_status' => 'publish'
            ];
            //var_dump($block_post); die();
            $block_post_id = wp_insert_post($block_post);
        }
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

    public function get_blocks_dirs($blocks_dirs) {

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

    public function get_json_data($post_slug, $textdomain = '*') {
        return $this->get_block_json($post_slug, $textdomain);
    }
    public function get_block_json($post_slug, $textdomain = '*') {
        $tmp = explode('/', $post_slug, 2);
        if (count($tmp) == 2) {
            $post_slug = end($tmp);
            if ($textdomain == '*') {
                $textdomain = reset($tmp);
            }
        }
        $path = $this->get_blocks_dir($post_slug, $textdomain) . DIRECTORY_SEPARATOR . 'block.json';
        if (file_exists($path)) {
            $content = $this->get_filesystem()->get_contents($path);
            return json_decode($content, true);
        }
        return [];
    }
    
    public function get_block_attributes_condition($post_slug, $textdomain = '*') {
        $path = $this->get_blocks_dir($post_slug, $textdomain) . DIRECTORY_SEPARATOR . 'editorScript.js';
        if (file_exists($path)) {
            $content = $this->get_filesystem()->get_contents($path);
            $tmp = explode('/* wb:attributes:condition ', $content, 2);
            if (count($tmp) > 1) {
                list($conditions, $more) = explode(' */', end($tmp), 2);
                return json_decode($conditions, true);
            }
        }
        return [];
    }
    
    public function unescape($code = '', $quote = '') {
        //$code = wp_unslash($code);
        $code = str_replace('\"', $quote ? $quote : '"', $code);
        $code = str_replace("\'", $quote ? $quote : "'", $code);
        $code = str_replace("\/", "/", $code);
        $code = str_replace("=&gt;", "=>", $code);
        $code = str_replace("=&gt;", "=>", $code);
        return $code;
    }

}