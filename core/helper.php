<?php

namespace WizardBlocks\Core;

/**
 * Description of Helper
 *
 * @author fra
 */
class Helper {

    static public function get_plugin_path($file) {
        $wp_plugin_dir = self::get_wp_plugin_dir();
        // from __FILE__
        $tmp = explode($wp_plugin_dir . DIRECTORY_SEPARATOR, $file, 2);        
        if (count($tmp) == 2) {
            @list($plugin_name, $other) = explode(DIRECTORY_SEPARATOR, end($tmp));
            return $wp_plugin_dir . DIRECTORY_SEPARATOR . $plugin_name . DIRECTORY_SEPARATOR;
        }

        // from DOMAIN
        $tmp = explode('\\', $file);
        $tmp = array_filter($tmp);
        if (count($tmp) > 1) {
            $base = reset($tmp);
            $folder = self::camel_to_slug($base);
            return $wp_plugin_dir . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR;
        }

        return false;
    }

    public static function class_to_path($class) {
        $wp_plugin_dir = self::get_wp_plugin_dir();
        $filename = str_replace('\\', DIRECTORY_SEPARATOR, $class);
        $filename = self::camel_to_slug($filename);
        $filename = str_replace(DIRECTORY_SEPARATOR . '-', DIRECTORY_SEPARATOR, $filename);
        $filename = str_replace('_', '', $filename) . '.php';
        $filename = $wp_plugin_dir . DIRECTORY_SEPARATOR . $filename;
        $filename = str_replace('//', '/', $filename);
        $filename = str_replace('/', DIRECTORY_SEPARATOR, $filename);
        return $filename;
    }

    public static function path_to_class($path) {
        $wp_plugin_dir = self::get_wp_plugin_dir();
        $path = str_replace($wp_plugin_dir, '', $path);
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        $tmp = explode(DIRECTORY_SEPARATOR, $path);
        $tmp = array_filter($tmp);
        $filename = array_pop($tmp);
        foreach ($tmp as $tkey => $atmp) {
            $tmp[$tkey] = self::slug_to_camel($atmp);
        }
        $filename = str_replace('.php', '', $filename);
        $class = self::slug_to_camel(implode('\\', $tmp)) . '\\' . self::slug_to_camel($filename, '_');
        return $class;
    }

    public static function camel_to_slug($title, $separator = '-') {
        $label = preg_replace('/(?<=[a-z])[A-Z]|[A-Z](?=[a-z])/', ' $0', $title);
        $label = strtolower($label);
        $label = str_replace(DIRECTORY_SEPARATOR . ' ', DIRECTORY_SEPARATOR, $label);
        $label = trim($label);
        $label = str_replace('_ ', ' ', $label); // class name
        return str_replace(' ', $separator, $label);
    }

    public static function slug_to_camel($title, $separator = '') {
        $title = str_replace('-', ' ', $title);
        $title = ucwords($title);
        return str_replace(' ', $separator, $title);
    }
    
    public static function get_wp_plugin_dir() {
        $wp_plugin_dir = str_replace('/', DIRECTORY_SEPARATOR, WP_PLUGIN_DIR);
        $wp_plugin_dir = str_replace('//', '/', $wp_plugin_dir);
        if (!is_dir($wp_plugin_dir)) {
            $wp_plugin_dir = str_replace(DIRECTORY_SEPARATOR.'opt'.DIRECTORY_SEPARATOR.'bitnami', DIRECTORY_SEPARATOR.'bitnami', $wp_plugin_dir);
        }
        return $wp_plugin_dir;
    }
    
    static public function url_to_path($url) {
        include_once(ABSPATH.'wp-admin'.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'file.php');
        $rel = wp_make_link_relative($url);
        $rel = str_replace('/', DIRECTORY_SEPARATOR, $rel);
        $home_url = get_home_url();
        $tmp = explode('/', $home_url);
        if (count($tmp) > 3) {
            $tmp = array_slice($tmp, 3);
            if (!empty($tmp)) {
                $tmp = DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $tmp);
                if (substr($rel, 0, strlen($tmp)) == $tmp) {
                    $rel = substr($rel, strlen($tmp));
                }
            }
        }
        $tmp = substr(get_home_path(), 0, -1) . $rel;
        $tmp = str_replace('/', DIRECTORY_SEPARATOR, $tmp);
        return $tmp;
    }

    public static function path_to_url($path) {
        $wp_upload_dir = wp_upload_dir();
        $url = str_replace($wp_upload_dir["basedir"], $wp_upload_dir["baseurl"], $path);
        $ABSPATH = str_replace('/', DIRECTORY_SEPARATOR, ABSPATH);
        $url = str_replace($ABSPATH, get_home_url(null, '/'), $url);
        $url = str_replace('\\', '/', $url);
        return $url;
    }

}
