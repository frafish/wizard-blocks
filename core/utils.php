<?php

namespace WizardBlocks\Core;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Utils.
 *
 * @since 1.0.1
 */
class Utils {

    /**
     * Split a string by a string
     * <p>Returns an array of strings, each of which is a substring of <code>string</code> formed by splitting it on boundaries formed by the string <code>delimiter</code>.</p>
     * @param string $delimiter <p>The boundary string.</p>
     * @param string $string <p>The input string.</p>
     * @param int $limit <p>If <code>limit</code> is set and positive, the returned array will contain a maximum of <code>limit</code> elements with the last element containing the rest of <code>string</code>.</p> <p>If the <code>limit</code> parameter is negative, all components except the last -<code>limit</code> are returned.</p> <p>If the <code>limit</code> parameter is zero, then this is treated as 1.</p>
     * @param string $format <p>Perform a function an chunk, use functions like trim, intval, absint.</p>
     * @return array <p>Returns an <code>array</code> of <code>string</code>s created by splitting the <code>string</code> parameter on boundaries formed by the <code>delimiter</code>.</p><p>If <code>delimiter</code> is an empty <code>string</code> (""), <b>explode()</b> will return <b><code>FALSE</code></b>. If <code>delimiter</code> contains a value that is not contained in <code>string</code> and a negative <code>limit</code> is used, then an empty <code>array</code> will be returned, otherwise an <code>array</code> containing <code>string</code> will be returned.</p>
     */
    public static function explode($string = '', $delimiter = ',', $limit = PHP_INT_MAX, $format = null) {
        //$string = '45.68174362, 5.91081238'; $delimeter = ','; $limit = PHP_INT_MAX; $format = 'trim';
        /*if ($limit == PHP_INT_MAX) {
            $limit = -1;
        }*/
        if (is_null($string)) {
            $string = [];
        }
        if (is_numeric($string)) {
            $string = array($string);
        }
        if (is_string($string)) {
            $tmp = array();
            if ($delimiter == PHP_EOL) {
                $string = preg_split( "/\\r\\n|\\r|\\n/", $string, $limit );
            } else {
                $string = explode($delimiter, $string, $limit);
            }
            
            $string = array_map('trim', $string);
            foreach ($string as $value) {
                if ($value != '') {
                    $tmp[] = $value;
                }
            }
            $string = $tmp;
        }
        if (!empty($string) && is_array($string) && $format) {
            $string = array_map($format, $string);
        }
        //var_dump($string); die();
        return $string;
    }
    
    /**
     * Join array elements with a string
     * <p>Join array elements with a <code>glue</code> string.</p><p><b>Note</b>:</p><p><b>implode()</b> can, for historical reasons, accept its parameters in either order. For consistency with <code>explode()</code>, however, it may be less confusing to use the documented order of arguments.</p>
     * @param string $glue <p>Defaults to an empty string.</p>
     * @param array $pieces <p>The array of strings to implode.</p>
     * @param bool $listed <p>Return array as a list, maybe use it with empty glue.</p>
     * @return string <p>Returns a string containing a string representation of all the array elements in the same order, with the glue string between each element.</p>
     */
    public static function implode($pieces = array(), $glue = ', ', $listed = false) {
        $string = '';
        if (is_string($pieces)) {
            $string = $pieces;
        }
        if (!empty($pieces) && is_array($pieces)) {
            if ($listed) {
                $string .= (is_string($listed)) ? '<' . $listed . '>' : '<ul>';
            }
            $i = 0;
            foreach ($pieces as $av) {
                if ($listed) {
                    $string .= '<li>';
                }
                if (is_object($av)) {
                    $av = self::to_string($av);
                }
                if (is_array($av)) {
                    $string .= self::implode($av, $glue, $listed);
                } else {
                    if ($i) {
                        $string .= $glue;
                    }
                    $string .= $av;
                }
                if ($listed) {
                    $string .= '</li>';
                }
                $i++;
            }
            if ($listed) {
                $string .= (is_string($listed)) ? '</' . $listed . '>' : '</ul>';
            }
        }
        return $string;
    }

    /**
     * Maybe JSON Decode — Decodes a JSON string if valid
     *
     * @param  string $json
     * @param  bool   $associative
     * @param  int    $depth
     * @param  bitmask$flags
     *
     * @return array
     */
    public static function maybe_json_decode($json, $associative = null, $depth = null, $flags = null) {
        return self::json_validate($json) ? json_decode($json, $associative) : $json;
    }
    
    /**
     * Test if given object is a JSON string or not.
     *
     * @param  mixed $json
     *
     * @return bool
     */
    public static function json_validate($json) {
        return is_string($json) && is_array(json_decode($json, true)) && json_last_error() === JSON_ERROR_NONE;
    }

    public static function strip_tag($tag, $content = '') {
        $content = preg_replace('/<' . $tag . '[^>]*>/i', '', $content);
        $content = preg_replace('/<\/' . $tag . '>/i', '', $content);
        return $content;
    }

    static public function get_plugin_path($file) {
        return Helper::get_plugin_path($file);
    }

    public static function camel_to_slug($title, $separator = '-') {
        return Helper::camel_to_slug($title, $separator);
    }

    public static function slug_to_camel($title, $separator = '') {
        return Helper::slug_to_camel($title, $separator);
    }
    
    public static function get_wp_plugin_dir() {        
        return \WizardBlocks\Core\Helper::get_wp_plugin_dir();
    }

}
