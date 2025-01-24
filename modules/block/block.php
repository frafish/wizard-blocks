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
        add_filter('wp_save_post_revision_post_has_changed', [$this, 'has_block_changed'], 10, 3);
        add_action( '_wp_put_post_revision', [$this, 'save_block_revision'], 10, 2);
        add_filter('wizard_blocks/before_save', [$this, 'generate_block_zip_for_revision'], 10, 3);
        add_filter( 'wp_get_revision_ui_diff', [$this, 'get_revision_ui_diff'], 10, 3);
        add_action( 'wp_restore_post_revision', [$this, 'restore_block_revision'], 10, 2 );
                
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
        
        
        //add_action('init', [$this, 'unregister_blocks_disabled'], 99);
        add_filter( 'allowed_block_types_all', [$this, 'allowed_block_types'], 10, 2 );
        
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
    
    public function enqueue_block_assets($block) {
        //var_dump($block);
        // frontend assets
        $styles = [];
        if (!empty($block->style)) { $styles = array_merge($styles, is_array($block->style) ? $block->style : [$block->style]); }
        if (!empty($block->style_handles)) { $styles = array_merge($styles, is_array($block->style_handles) ? $block->style_handles : [$block->style_handles]); }
        if (!empty($block->viewStyle)) { $styles = array_merge($styles, is_array($block->viewStyle) ? $block->viewStyle : [$block->viewStyle]); }
        if (!empty($block->view_style_handles)) { $styles = array_merge($styles, is_array($block->view_style_handles) ? $block->view_style_handles : [$block->view_style_handles]); }
        //var_dump($styles);
        foreach ($styles as $style) {
            wp_enqueue_style($style);
        }
        
        $scripts = [];
        if (!empty($block->script)) { $scripts = array_merge($scripts, is_array($block->script) ? $block->script : [$block->script]); }
        if (!empty($block->script_handles)) { $scripts = array_merge($scripts, is_array($block->script_handles) ? $block->script_handles : [$block->script_handles]); }
        if (!empty($block->viewScript)) { $scripts = array_merge($scripts, is_array($block->viewScript) ? $block->viewScript : [$block->viewScript]); }
        if (!empty($block->view_script_handles)) { $scripts = array_merge($scripts, is_array($block->view_script_handles) ? $block->view_script_handles : [$block->view_script_handles]); }
        if (!empty($block->viewScriptModule)) { $scripts = array_merge($scripts, is_array($block->viewScriptModule) ? $block->viewScriptModule : [$block->viewScriptModule]); }
        if (!empty($block->view_script_module_ids)) { $scripts = array_merge($scripts, is_array($block->view_script_module_ids) ? $block->view_script_module_ids : [$block->view_script_module_ids]); }
        //var_dump($scripts);
        foreach ($scripts as $script) {
            wp_enqueue_script($script);
        }

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
        if (!wp_verify_nonce(sanitize_key(wp_unslash($_POST['meta_fields_meta_box_nonce'])), 'meta_fields_save_meta_box_data'))
            return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;
        if (!current_user_can('edit_post', $post_id))
            return;
        
        $post_slug = get_post_field('post_name', $post_id);
        $block_slug = $post_slug;
        if (!empty($_POST['_block_name'])) {
            $block_slug = sanitize_key(wp_unslash($_POST['_block_name']));
        }
        
        $block_textdomain = $this->get_plugin_textdomain();
        if (!empty($_POST['_block_textdomain'])) {
            $block_textdomain = sanitize_key(wp_unslash($_POST['_block_textdomain']));
        }
        
        //var_dump($update); die();
        $json_old = [];
        if ($update) {
            $json_old = $this->get_block_json($post_slug);    
            //var_dump($json_old); die();
            
            $textdomain_old = $this->get_block_textdomain($json_old);
            if ($block_textdomain != $textdomain_old) {
                // delete old dir
                $old_dir = $this->get_ensure_blocks_dir($post_slug, $textdomain_old);
                $this->dir_delete($old_dir);
            }
            
            // if changed slug delete old dir
            if ($post_slug != $block_slug) {
                wp_update_post( ['ID' => $post_id, 'post_name' => $block_slug] );
                // delete old dir
                $old_dir = $this->get_ensure_blocks_dir($post_slug, $textdomain_old);
                $this->dir_delete($old_dir);
                $block_slug = get_post_field('post_name', $post_id); // prevent duplicates
            }
        }
        
        $basepath = $this->get_ensure_blocks_dir($block_slug, $block_textdomain);
        $post_excerpt = get_post_field('post_excerpt', $post_id);
        
        $attributes = [];
        if (!empty($_POST['_block_attributes'])) {
            $attributes = sanitize_textarea_field(wp_unslash($_POST['_block_attributes']));
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
        
        $apiVersion = end(self::$apiVersions);
        if (!empty($_POST['_block_apiVersion'])) {
            $apiVersion = intval($_POST['_block_apiVersion']);
            if (!in_array($apiVersion, self::$apiVersions)) {
                // something wrong...
            }
        }
        
        $version = ""; //1.0.1";
        if (!empty($_POST['_block_version'])) {
            $version = sanitize_text_field(wp_unslash($_POST['_block_version']));
        }

        $keywords = [];
        if (!empty($_POST['_block_keywords'])) {
            $keywords = array_filter(array_map('trim', explode(',', sanitize_text_field(wp_unslash($_POST['_block_keywords'])))));
        }

        $usesContext = [];
        if (!empty($_POST['_block_usesContext'])) {
            $usesContext = array_filter(array_map('trim', explode(',', sanitize_text_field(wp_unslash($_POST['_block_usesContext'])))));
        }

        $providesContext = [];
        if (!empty($_POST['_block_providesContext'])) {
            $providesContext = sanitize_textarea_field(wp_unslash($_POST['_block_providesContext']));
            if (substr($providesContext, 0, 1) != '{') {
                $providesContext = '{' . $providesContext . '}';
            }
            $providesContext = $this->unescape($providesContext);
            $providesContext = json_decode($providesContext);
        }
        
        $blockHooks = [];
        if (!empty($_POST['_block_blockHooks'])) {
            $blockHooks = sanitize_textarea_field(wp_unslash($_POST['_block_blockHooks']));
            if (substr($blockHooks, 0, 1) != '{') {
                $blockHooks = '{' . $blockHooks . '}';
            }
            $blockHooks = $this->unescape($blockHooks);
            $blockHooks = json_decode($blockHooks);
        }

        $parent = [];
        if (!empty($_POST['_block_parent'])) {
            $parent = array_filter(array_map('trim', explode(',', sanitize_text_field(wp_unslash($_POST['_block_parent'])))));
        }
        
        $allowedBlocks = [];
        if (!empty($_POST['_block_allowedBlocks'])) {
            $allowedBlocks = array_filter(array_map('trim',explode(',', sanitize_text_field(wp_unslash($_POST['_block_allowedBlocks'])))));
        }
        //var_dump($allowedBlocks);

        $ancestors = [];
        if (!empty($_POST['_block_ancestor'])) {
            $ancestors = array_filter(array_map('trim',explode(',', sanitize_text_field(wp_unslash($_POST['_block_ancestor'])))));
        }

        $supports = [];
        if (!empty($_POST['_block_supports'])) {
            //$keys = array_keys($_POST['_block_supports']);
            $_block_supports = array_map('sanitize_text_field', wp_unslash($_POST['_block_supports']));
            foreach ($_block_supports as $sup => $value) {
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
            $custom_json = $this->unescape(sanitize_textarea_field(wp_unslash($_POST['_block_supports_custom'], '"')));
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
            $icon = sanitize_key(wp_unslash($_POST['_block_icon']));
        } else {
            if (!empty($_POST['_block_icon_svg'])) {
                $icon = sanitize_textarea_field(wp_unslash($_POST['_block_icon_svg']));
                $icon = str_replace(PHP_EOL, "", $icon);
                $icon = str_replace('"', "'", $icon);
                $icon = str_replace("\'", "'", $icon);
                
            }
        }
        
        $category = '';
        if (!empty($_POST['_block_category'])) {
            $category = sanitize_title(wp_unslash($_POST['_block_category']));
        }
        
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
            "apiVersion" => $apiVersion,
            "name" => $block_textdomain . "/" . $block_slug,
            "title" => get_the_title($post_id),
            "category" => $category,
            "parent" => $parent,
            "allowedBlocks" => $allowedBlocks,
            "ancestor" => $ancestors,
            "icon" => $icon,
            "description" => $post_excerpt,
            "keywords" => $keywords,
            "version" => $version,
            "textdomain" => $block_textdomain,
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
        //var_dump($json);
        
        // SAVING ASSETS FILES
        $min = '.min.';
        foreach (self::$assets as $asset => $type) {
            $json[$asset] = [];
            $code = '';
            $path = $this->get_asset_file($json_old, $asset, $basepath);
            //var_dump($basepath); die();
            $file = basename($path);
            $file_name = basename($file, '.'.$type);
            $path_min = $this->get_ensure_blocks_dir($block_slug, $block_textdomain) . $file_name . $min . $type;
            
            if (!empty($_POST['_block_' . $asset.'_file'])) {
                switch ($asset) {
                    case 'render':
                        $code = $_POST['_block_' . $asset.'_file'];
                        break;
                    default: // get default file
                        $code = $_POST['_block_' . $asset.'_file'][$asset.'.'.$type];
                }
                $code = wp_unslash($code);
                if ($asset !== 'render') {
                    //$code = wp_kses_post($code);   
                }
                $code = $this->unescape($code);
                if ($asset == 'render') {
                    $abspath_check = "<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>";
                    if (strpos($code, $abspath_check) === false ) {
                        //  ## Allowing Direct File Access to plugin files  
                        $code = $abspath_check.PHP_EOL.$code;
                    }
                }
            }
            if ($asset == 'editorScript') {
                if (!$code || strpos($code, 'generated by '.$this->get_plugin_textdomain()) !== false) {
                    // autogenerated - so update it
                    $code = $this->_edit($json);
                }
            }
            $code = apply_filters('wizard_blocks/before_asset', $code, $asset);
            if ($code) {
                // save asset into block folder
                if ($this->get_filesystem()->put_contents($path, $code)) {
                //if (file_put_contents($path, $code)) {
                    $json[$asset] = [ "file:./" . $file ];
                }
                // generate minified version
                if (in_array($asset, ['editorScript', 'viewScript', 'viewScriptModule', 'script'])) {
                    $minifier = new \MatthiasMullie\Minify\JS($code);
                    // save minified file to disk
                    $minifier->minify($path_min);
                    $json[$asset] = [ "file:./" . $file_name . (SCRIPT_DEBUG ? '.' : $min) . $type ];
                    $path = $this->get_ensure_blocks_dir($block_slug, $block_textdomain) . $file_name . (SCRIPT_DEBUG ? '' : '.min') .'.asset.php';
                    $code = "<?php return array('dependencies'=>[], 'version'=>'".gmdate('U')."');";
                    if (!file_exists($path)) {
                        //file_put_contents($path, $code);
                        $this->get_filesystem()->put_contents($path, $code);
                    }
                }
            } else {
                // delete old assets files?!
                if (file_exists($path)) {
                    $this->get_filesystem()->delete($path);
                }
                if (file_exists($path_min)) {
                    $this->get_filesystem()->delete($path_min);
                }
            }
        }
        //var_dump($json); die();
        
        // SET ASSETS IN JSON
        foreach (self::$assets as $asset => $type) {
            if (!empty($_POST['_block_' . $asset])) {
                $_block_asset = sanitize_text_field(wp_unslash($_POST['_block_' . $asset]));
                $files = Utils::explode($_block_asset);
                foreach ($files as $file) {
                    $asset_file = 'file:./'.$asset.'.'.$type;
                    $asset_min = 'file:./'.$asset.$min.$type;
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
                    } else {
                        // update extra css/js lib
                        if ($file != $asset_file && $file != $asset_min) {
                            //var_dump($file); var_dump($asset); var_dump($asset_min);
                            $file_name = str_replace('file:./', '', $file);
                            if (!empty($_POST['_block_' . $asset.'_file'][$file_name])) {
                                $code = $_POST['_block_' . $asset.'_file'][$file_name];
                                $code = wp_unslash($code);
                                $path = $this->get_ensure_blocks_dir($block_slug, $block_textdomain) . $file_name;
                                //if (file_exists($path)) {
                                    //file_put_contents($path, $code);
                                    $this->get_filesystem()->put_contents($path, $code);
                                //}
                            }
                        }
                    }
                    // prevent duplicates
                    
                    if (empty($json[$asset]) || !in_array($file, $json[$asset])) {
                        $json[$asset][] = $file;
                    }
                }
                
                $key = array_search($asset_file, $json[$asset]);
                $key_min = array_search($asset_min, $json[$asset]);
                if ($key !== false && $key_min !== false) {
                    if (SCRIPT_DEBUG) {
                        // use plain version
                        unset($json[$asset][$key_min]);
                    } else {
                        // use minified version
                        unset($json[$asset][$key]);
                    }
                }
            }
            if (!empty($json_old[$asset])) {
                // clean removed assets
            }
        }
        //var_dump($json); die();
        
        // OPTIMIZATION: from array to string in case of single asset
        foreach (self::$assets as $asset => $type) {
            if (is_array($json[$asset]) && count($json[$asset]) == 1) {
                $json[$asset] = reset($json[$asset]);
            }
        }
        
        // remove empty fields
        $json = array_filter($json);
        
        // add extra fields
        if (!empty($_POST['_block_extra'])) {            
            $extra_json = $this->unescape(sanitize_textarea_field(wp_unslash($_POST['_block_extra'], '"')));
            $extra = json_decode($extra_json, true); 
            if ($extra == NULL) {
                update_post_meta($post_id, '_transient_block_extra', $extra_json);
            } else {
                $json = array_merge($json, $extra);
                delete_post_meta($post_id, '_transient_block_extra');
            }
            
        }
        
        //var_dump($json);
        $json = apply_filters('wizard_blocks/before_save', $json, $post, $update);
        
        $path = $basepath . 'block.json';
        $code = wp_json_encode($json, JSON_PRETTY_PRINT);
        $code = str_replace('\/', '/', $code);
        $code = str_replace("\'", "'", $code);
        
        $result = $this->get_filesystem()->put_contents($path, $code);
        //echo 'SAVE:'; var_dump($result); var_dump($path); var_dump($code); die();
        do_action('wizard_blocks/after_save', $json, $post, $update);
    }

    public function get_json_data($post_slug, $textdomain = '*') {
        return $this->get_block_json($post_slug, $textdomain);
    }
    public function get_block_json($post_slug, $textdomain = '*') {
        $path = $this->get_blocks_dir($post_slug, $textdomain) . DIRECTORY_SEPARATOR . 'block.json';
        if (file_exists($path)) {
            $content = file_get_contents($path);
            return json_decode($content, true);
        }
        return [];
    }
    
    public function get_block_attributes_condition($post_slug, $textdomain = '*') {
        $path = $this->get_blocks_dir($post_slug, $textdomain) . DIRECTORY_SEPARATOR . 'editorScript.js';
        if (file_exists($path)) {
            $content = file_get_contents($path);
            $tmp = explode('/* wb:attributes:condition ', $content, 2);
            if (count($tmp) > 1) {
                list($conditions, $more) = explode(' */', end($tmp), 2);
                return json_decode($conditions, true);
            }
        }
        return [];
    }
    
    public function get_asset_files($json, $asset, $basepath = '') {
        $type = self::$assets[$asset];
        $asset_file = $asset.'.'.$type;
        $asset_file_min = $asset.'.min.'.$type;
        $asset_files = [];
        if (!empty($json[$asset])) {
            //var_dump($json[$asset]); die();
            $asset_files = Utils::maybe_json_decode($json[$asset]);
            if (!is_array($asset_files)) {
                $asset_files = [$asset_files];
            }
            if ($type != 'php') {
                foreach ($asset_files as $key => $asset_file) {
                    //$unmin = str_replace('.min.js', '.js', $asset_file);
                    $unmin = str_replace('.min.'.$type, '.'.$type, $asset_file);
                    $unmin = str_replace('file:./', '', $unmin); // maybe local asset
                    $unmin_file = $basepath . $unmin;
                    $unmin_file = str_replace('/', DIRECTORY_SEPARATOR, $unmin_file);
                    if (file_exists($unmin_file)) {
                        $asset_file = $unmin;
                    }
                    $asset_file = str_replace('file:', '', $asset_file);
                    $asset_file = str_replace('/', DIRECTORY_SEPARATOR, $asset_file);
                    $asset_file = $basepath . $asset_file;
                    $asset_files[$key] = $asset_file;
                }
            }            
            $asset_files = array_unique($asset_files); //die();
        }
        return $asset_files;
    }
    
    
    public function get_asset_default_file($json, $asset, $basepath = '') {
        $type = self::$assets[$asset];
        $asset_file = $asset.'.'.$type;
        $asset_file_min = $asset.'.min.'.$type;
        if (!empty($json[$asset])) {
            //var_dump($json[$asset]); die();
            $asset_files = $json[$asset];
            if (is_array($asset_files)) {
                $key = array_search('file:./' . $asset_file, $asset_files);
                if ($key === false) {
                    $key = array_search('file:./' . $asset_file_min, $asset_files);
                }
                if ($key !== false) {
                    $asset_file = $json[$asset][$key];
                }
            }
            if ($type != 'php') {
                //$unmin = str_replace('.min.js', '.js', $asset_file);
                $unmin = str_replace('.min.'.$type, '.'.$type, $asset_file);
                $unmin = str_replace('file:./', '', $unmin); // maybe local asset
                $unmin_file = $basepath . $unmin;
                $unmin_file = str_replace('/', DIRECTORY_SEPARATOR, $unmin_file);
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
    
    
    public function get_asset_file($json, $asset, $basepath = '') {
        $type = self::$assets[$asset];
        $asset_file = $asset.'.'.$type;
        $asset_file_min = $asset.'.min.'.$type;
        if (!empty($json[$asset])) {
            //var_dump($json[$asset]); die();
            $asset_files = $json[$asset];
            if (is_array($asset_files)) {
                $key = array_search('file:./' . $asset_file, $asset_files);
                if ($key === false) {
                    $key = array_search('file:./' . $asset_file_min, $asset_files);
                }
                //var_dump($style_key);
                if ($key !== false) {
                    $asset_file = $json[$asset][$key];
                } else {
                    foreach ($json[$asset] as $tmp) {
                        /*if ($tmp == 'file:./'.$asset_file) {
                            $asset_file = $tmp;
                        }*/
                        if (substr($tmp, 0, 5) == 'file:') {
                            $asset_file = $tmp;
                            break; // maybe use the first one
                        }
                    }
                }
            } else {
                if (strpos($asset_files, 'file:./') !== false) {
                    $asset_file = $asset_files;
                }
            }
            if ($type != 'php') {
                //$unmin = str_replace('.min.js', '.js', $asset_file);
                $unmin = str_replace('.min.'.$type, '.'.$type, $asset_file);
                $unmin = str_replace('file:./', '', $unmin); // maybe local asset
                $unmin_file = $basepath . $unmin;
                $unmin_file = str_replace('/', DIRECTORY_SEPARATOR, $unmin_file);
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
        if (is_file($basepath)) {
            $asset_file = $basepath;
        } else {
            $asset_file = $this->get_asset_file($json, $asset, $basepath);
        }
        if (file_exists($asset_file)) {
            return file_get_contents($asset_file);
        }
        return '';
    }

    public function unescape($code = '', $quote = '') {
        $code = str_replace('\"', $quote ? $quote : '"', $code);
        $code = str_replace("\'", $quote ? $quote : "'", $code);
        $code = str_replace("\/", "/", $code);
        $code = str_replace("=&gt;", "=>", $code);
        return $code;
    }

    /**
     * Gets the path to uploaded file.
     *
     * @return string
     */
    private function get_blocks_dir($slug = '', $textdomain = '*') {
        $wp_upload_dir = wp_upload_dir();
        $path = $wp_upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'blocks';
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);

        if ($slug) {
            $path = false;
            $blocks_dirs = ['uploads' => $path];
            $blocks_dirs = apply_filters('wizard/blocks/dirs', $blocks_dirs);
            foreach ($blocks_dirs as $dir) {
                /*if (is_dir($dir . DIRECTORY_SEPARATOR . $slug)) {
                    $path = $dir;
                }*/
                $paths = glob($dir.DIRECTORY_SEPARATOR.$textdomain.DIRECTORY_SEPARATOR.$slug); // TODO: any textdomain?!
                //var_dump($paths);
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
        //var_dump($wp_filesystem);
        return $wp_filesystem;
    }
    
    // https://developer.wordpress.org/block-editor/how-to-guides/internationalization/
    public function add_translation() {
        // create languages folder
        // create main lang file
        //
    }
}

