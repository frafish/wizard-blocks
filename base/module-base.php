<?php

namespace WizardBlocks\Base;

use WizardBlocks\Core\Utils;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

abstract class Module_Base {

    /**
     * Module class reflection.
     *
     * Holds the information about a class.
     *
     * @since 1.7.0
     * @access private
     *
     * @var \ReflectionClass
     */
    private $reflection;

    /**
     * Module components.
     *
     * Holds the module components.
     *
     * @since 1.7.0
     * @access private
     *
     * @var array
     */
    private $components = [];

    /**
     * Module instance.
     *
     * Holds the module instance.
     *
     * @since 1.7.0
     * @access protected
     *
     * @var Module
     */
    protected static $_instances = [];

    public function __construct() {
        $this->init_extensions();
        add_action('elementor/dynamic_tags/register', [$this, 'init_tags']);
    }

    /**
     * Instance.
     *
     * Ensures only one instance of the module class is loaded or can be loaded.
     *
     * @since 1.7.0
     * @access public
     * @static
     *
     * @return Module An instance of the class.
     */
    public static function instance() {
        $class_name = static::class_name();

        if (empty(static::$_instances[$class_name])) {
            static::$_instances[$class_name] = new static();
        }

        return static::$_instances[$class_name];
    }

    /**
     * Class name.
     *
     * Retrieve the name of the class.
     *
     * @since 1.7.0
     * @access public
     * @static
     */
    public static function class_name() {
        return get_called_class();
    }

    /**
     * @since 2.0.0
     * @access public
     */
    public function get_reflection() {
        if (null === $this->reflection) {
            $this->reflection = new \ReflectionClass($this);
        }

        return $this->reflection;
    }

    /**
     * Get Name
     *
     * Get the name of the module
     *
     * @since  1.0.1
     * @return string
     */
    public function get_name() {
        $assets_name = $this->get_reflection()->getNamespaceName();
        $tmp = explode('\\', $assets_name);
        $module = end($tmp);
        $module = Utils::camel_to_slug($module);
        return $module;
    }

    /**
     * Get Name
     *
     * Get the name of the module
     *
     * @since  1.0.1
     * @return string
     */
    public function get_label() {
        $assets_name = $this->get_reflection()->getNamespaceName();
        $tmp = explode('\\', $assets_name);
        $module = end($tmp);
        $module = Utils::camel_to_slug($module, ' ');
        return ucfirst($module);
    }

    public function get_plugin_textdomain() {
        $assets_name = $this->get_reflection()->getNamespaceName();
        $tmp = explode('\\', $assets_name);
        $plugin = reset($tmp);
        $plugin = Utils::camel_to_slug($plugin, '-');
        return $plugin;
    }

    public function get_plugin_path() {
        $wp_plugin_dir = Utils::get_wp_plugin_dir();
        return $wp_plugin_dir . DIRECTORY_SEPARATOR . $this->get_plugin_textdomain() . DIRECTORY_SEPARATOR;
    }

    public function has_elements($folder = 'widgets') {
        $module = $this->get_name();
        $class_name = $this->get_reflection()->getNamespaceName();
        $plugin_path = Utils::get_plugin_path($class_name);
        $path = $plugin_path . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR;
        if (is_dir($path)) {
            $files = glob($path . '*.php');
            return !empty($files);
        }
        return false;
    }

    public function get_elements($folder = 'widgets', $enabled = true) {
        $elements = array();
        $module = $this->get_name();
        $class_name = $this->get_reflection()->getNamespaceName();
        $plugin_path = Utils::get_plugin_path($class_name);
        $path = $plugin_path . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR;
        //if ($folder == 'triggers' && $module == 'display') { return $elements; }
        if (is_dir($path)) {

            $files = glob($path . '*.php');
            //$files = array_filter(glob(DIRECTORY_SEPARATOR."*"), 'is_file');

            foreach ($files as $ele) {
                $file = basename($ele);
                $name = pathinfo($file, PATHINFO_FILENAME);
                $elements[] = Utils::slug_to_camel($name, '_');
            }
        }
        return $elements;
    }

    public function init_extensions() {        
        foreach ($this->get_elements('extensions') as $ext) {
            $class_name = $this->get_reflection()->getNamespaceName() . '\Extensions\\' . $ext;
                $ext_obj = new $class_name();
            
        }
    }
    
    public function init_tags($dynamic_tags) {
        /** @var \Elementor\Core\DynamicTags\Manager $module */
        $module = \Elementor\Plugin::$instance->dynamic_tags;  
        foreach ($this->get_elements('tags') as $tag) {
            $class_name = $this->get_reflection()->getNamespaceName() . '\Tags\\' . $tag;
                $tag_obj = new $class_name();
                $module->register($tag_obj);
            
        }
    }

}
