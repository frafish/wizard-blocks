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

    public function __construct() {}

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
    
    public function get_plugin_slug() {
        return basename(plugin_dir_path(dirname(__FILE__, 1)));
    }
    
    public function register_style($handle, $path, $deps = [], $version = '', $media = 'all') {
        $assets_name = $this->get_reflection()->getNamespaceName();
        $tmp = explode('\\', $assets_name);
        $module = implode('/', $tmp);
        $module = Utils::camel_to_slug($module);
		$url = WP_PLUGIN_URL . '/' . $module . '/' . $path;
		$url = str_replace('/-', '/', $url);
        wp_register_style($handle, $url, $deps, $version, $media);
    }
    
    public function register_script($handle, $path, $deps = [], $version = '', $footer = true, $attrs = []) {
        $assets_name = $this->get_reflection()->getNamespaceName();
        $tmp = explode('\\', $assets_name);
        $module = implode('/', $tmp);
        $module = Utils::camel_to_slug($module);
		$url = WP_PLUGIN_URL . '/' . $module . '/' . $path;
		$url = str_replace('/-', '/', $url);
        wp_register_script($handle, $url, $deps, $version, $footer);
        if (!empty($attrs)) {
            self::$script_attrs[$handle] = $attrs;
        }
    }
    
    public function enqueue_style($handle, $path, $deps = [], $version = '', $media = 'all') {
        $this->register_style($handle, $path, $deps, $version, $media);
        wp_enqueue_style($handle);
    }
    public function enqueue_script($handle, $path, $deps = [], $version = '', $footer = true, $attrs = []) {
        $this->register_script($handle, $path, $deps, $version, $footer, $attrs);
        wp_enqueue_script($handle);
    }
            

}
