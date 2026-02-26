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
    use Traits\Icons;
    use Traits\Save;
    use Traits\Registry;
    
    public static $instance = null;
    
    // block JSON properties
    public static $fields = [
        "\$schema",
        "apiVersion",
        "name",
        "title",
        "category",
        "keywords",
        "parent",
        "ancestor",
        "allowedBlocks",
        "icon",
        "description",
        "version",
        "textdomain",
        "attributes",
        "viewScript",
        "editorScript",
        "editorScriptModule",
        "editorStyle",
        "script",
        "style",
        "viewStyle",
        "render",
        "provides",
        "usesContext",
        "supports",
        "providesContext"
    ];
    //https://developer.wordpress.org/block-editor/reference-guides/block-api/block-api-versions/
    public static $apiVersions = [
        1,
        2,
        3
    ];
    // https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#category
    public static $categories = [
        'text',
        'media',
        'design',
        'widgets',
        'theme',
        'embed'
    ];
    // https://developer.wordpress.org/block-editor/reference-guides/block-api/block-supports/
    public static $supports = [
        'allowedBlocks' => false,
        'anchor' => false,
        'autoRegister' => false,
        'align' => false,
        'alignWide' => true,
        'ariaLabel' => false,
        'background.backgroundImage' => false, // Enable background image control.
        'background.backgroundSize' => false, // Enable background image + size control.
        //'background.backgroundPosition' => false,
        'className' => true,
        'color.background' => true,
        'color.button' => false,
        'color.enableContrastChecker' => true,
        'color.gradients' => false,
        'color.heading' => false,
        'color.link' => false,
        'color.text' => true,
        'customClassName' => true,
        'dimensions.aspectRatio' => true,
        'dimensions.minHeight' => false,
        'filter.duotone' => false,
        'html' => true,
        'inserter' => true,
        'interactivity.clientNavigation' => false,
        'interactivity.interactive' => false,
        'layout.allowSwitching' => false,
        'layout.allowEditing' => true,
        'layout.allowInheriting' => true,
        'layout.allowSizingOnChildren' => false,
        'layout.allowVerticalAlignment' => true,
        'layout.allowJustification' => true,
        'layout.allowOrientation' => true,
        'layout.allowCustomContentAndWideSize' => true,
        'lock' => true,
        'multiple' => true,
        'position.sticky' => false,
        'renaming' => true,
        'reusable' => true,
        'shadow' => false,
        'spacing.margin' => false,
        'spacing.padding' => false,
        'spacing.blockGap' => false,
        'typography.fontSize' => false,
        'typography.lineHeight' => false,
        'typography.textAlign' => false,
        'splitting' => false
    ];
    //https://developer.wordpress.org/block-editor/reference-guides/block-api/block-attributes/#type-validation
    public static $attributes_type = [
        '' => 'Auto',
        'string' => 'String',
        'boolean' => 'Boolean',
        'number' => 'Number',
        'integer' => 'Integer',
        'array' => 'Array',
        'null' => 'Null',
        'object' => 'Object',
    ];
    //https://make.wordpress.org/core/2023/03/07/introduction-of-block-inspector-tabs/
    //https://developer.wordpress.org/news/2023/06/02/using-block-inspector-sidebar-groups/
    public static $attributes_position = [
        "default" => 'Settings Sidebar - Panel Content',
        //"settings" => 'Settings Sidebar',
        //"color" => 'Settings Sidebar - Panel Content',
        //"typography" => 'Settings Sidebar - Panel Typography',
        //"dimensions" => 'Settings Sidebar - Panel Dimensions',
        //"border" => 'Settings Sidebar - Panel Border',
        "advanced" => 'Settings Sidebar - Panel Advanced',
        //"position" => 'Settings Sidebar - Panel Position',
        "style" => 'Settings Sidebar - Panel Style',
        //"list" => 'Settings Sidebar - Children List',
        "toolbar" => 'Block Toolbar',
        //"menu" => 'Block Toolbar - Dropdown Menu',
        "block" => 'Block Content canvas',
    ];
    //https://developer.wordpress.org/block-editor/reference-guides/components/
    //https://wp-gb.com/
    //https://wordpress.github.io/gutenberg/
    public static $attributes_component = [
        'AlignmentMatrixControl' => 'Alignment Matrix',
        'AnglePickerControl' => 'Angle',
        //'BaseControl' => 'Base',
        //'BorderBoxControl' => 'Border Box',
        //'BorderControl' => 'Border',
        'BoxControl' => 'Box Sizing',
        'ButtonGroup' => 'Buttons',
        'CheckboxControl' => 'Checkbox',
        'ComboboxControl' => 'Combobox',
        'ColorPicker' => 'Color',
        'DatePicker' => 'Date',
        'DateTimePicker' => 'DateTime',
        //'Divider' => 'Divider', // same of HorizontalRule
        //'Dropdown' => 'Dropdown',
        //'DuotonePicker' => 'Duotone',
        'ExternalLink' => 'External Link',
        'FocalPointPicker' => 'Focal Point',
        'FontSizePicker' => '​Font Size',
        //'FormToggle' => 'Toggle',
        //'GradientPicker' => 'Gradient',
        'InnerBlocks' => 'Inner Blocks',
        //'InputControl' => 'Email', //experimental
        //'Heading' => 'Heading',
        'HorizontalRule' => '​Horizontal Rule',
        //'InputControl' => 'InputControl', //experimental
        'MediaUpload' => 'Media',
        //'NumberControl' => 'Number', //experimental
        'RadioControl' => 'Radio',
        //'RadioGroup' => 'RadioGroup', //experimental
        //'RichText' => 'RichText', //replaced by InnerBlocks, TODO
        'SelectControl' => 'Select',
        //'InputControl' => 'Tel', //experimental
        'TextareaControl' => 'TextArea',
        'TextControl' => 'Text',
        'TimePicker' => 'Time',
        'ToggleControl' => 'Toggle',
            //'InputControl' => 'URL', //experimental

            /*
             * TODO - wp.components
              AlignmentMatrixControl:
              AnglePickerControl:
              Animate:
              Autocomplete:
              BaseControl:
              BlockQuotation:
              BorderBoxControl:
              BorderControl:
              BoxControl:
              Button:
              ButtonGroup:
              Card:
              CardBody:
              CardDivider:
              CardFooter:
              CardHeader:
              CardMedia:
              CheckboxControl:
              Circle:
              ClipboardButton:
              ColorIndicator:
              ColorPalette:
              ColorPicker:
              ComboboxControl:
              Composite:
              CustomGradientPicker:
              CustomSelectControl:
              Dashicon:
              DatePicker:
              DateTimePicker:
              Disabled:
              Draggable:
              DropZone:
              DropZoneProvider:
              Dropdown:
              DropdownMenu:
              DuotonePicker:
              DuotoneSwatch:
              ExternalLink:
              Fill:
              Flex:
              FlexBlock:
              FlexItem:
              FocalPointPicker:
              FocusReturnProvider:
              FocusableIframe:
              ​FontSizePicker:
              ​FormFileUpload:
              FormToggle:
              FormTokenField:
              G:
              GradientPicker:
              Guide:
              GuidePage:
              ​HorizontalRule:
              Icon:
              ​IconButton:
              ​IsolatedEventContainer:
              ​KeyboardShortcuts:
              ​Line:
              ​MenuGroup:
              ​MenuItem:
              ​MenuItemsChoice:
              ​Modal:
              ​NavigableMenu:
              ​Navigator:
              ​Notice:
              ​NoticeList:
              ​Panel:
              ​PanelBody:
              ​PanelHeader:
              ​PanelRow:
              ​Path:
              ​Placeholder:
              ​Polygon:
              ​Popover:
              ​ProgressBar:
              ​QueryControls:
              ​RadioControl:
              ​RangeControl:
              ​Rect:
              ​ResizableBox:
              ​ResponsiveWrapper:
              ​SVG:
              ​SandBox:
              ​ScrollLock:
              ​SearchControl:
              ​SelectControl:
              ​Slot:
              ​SlotFillProvider:
              Snackbar:
              ​SnackbarList:
              ​Spinner:
              ​TabPanel:
              ​TabbableContainer:
              ​TextControl:
              ​TextHighlight:
              ​TextareaControl:
              ​TimePicker:
              ​Tip:
              ​ToggleControl:
              ​Toolbar:
              ​ToolbarButton:
              ​ToolbarDropdownMenu:
              ​ToolbarGroup:
              ​ToolbarItem:
              ​Tooltip:
              ​TreeSelect:
              ​VisuallyHidden:
             */
    ];
    //https://www.w3schools.com/html/html_form_input_types.asp
    public static $attributes_input_type = [
        'text',
        //'button',
        'checkbox',
        'color',
        'date',
        //'datetime-local',
        'email',
        //'file',
        //'hidden',
        //'image',
        'month',
        'number',
        'password',
        //'radio',
        'range',
        //'reset',
        //'search',
        //'submit',
        'tel',
        'time',
        'url',
        'week',
    ];
    //https://developer.wordpress.org/block-editor/reference-guides/block-api/block-attributes/#value-source
    public static $attributes_source = [
        'attribute' => 'Attribute',
        'text' => 'Text',
        'html' => 'HTML',
        'query' => 'Query',
        'meta' => 'Meta (deprecated)'
    ];
    
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
    
    public static $assets_alias = [
            'style.css' => 'style-index.css',
            'editorStyle.css' => 'index.css',
            'editorScript.js' => 'index.js',
            'viewScript.js' => 'view.js'
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
    
    public function get_block_class($block) {
        if (!empty($block['name'])) {
            $block_name = $block['name'];
        }
        if (is_string($block)) {
            $block_name = $block;
        }
        $class = str_replace('/','-',$block_name);
        return 'wp-block-'.$class;
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