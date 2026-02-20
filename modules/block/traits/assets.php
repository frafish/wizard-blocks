<?php

namespace WizardBlocks\Modules\Block\Traits;

use WizardBlocks\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) exit; 

Trait Assets {
    
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
                    
                    if (file_exists($asset_file)) {
                        // asset file in block folder
                        $asset_files[$key] = $asset_file;
                    } else {
                        // remove external libs
                        unset($asset_files[$key]);
                    }
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
        if (!empty(self::$assets[$asset])) {
            $type = self::$assets[$asset];
            $asset_file = $asset.'.'.$type;
            $asset_file_min = $asset.'.min.'.$type;
        } else {
            $asset_file = $asset;
        }
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
                        /*
                        // maybe use the first one ??
                        if (substr($tmp, 0, 5) == 'file:') {
                            $asset_file = $tmp;
                            break; 
                        }
                        */
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
        }
        
        $asset_file = str_replace('file:', '', $asset_file);
        $asset_file = str_replace('/', DIRECTORY_SEPARATOR, $asset_file);
        //var_dump($asset_file); die();
        if ($basepath && !str_ends_with($basepath, DIRECTORY_SEPARATOR)) {
            $basepath .= DIRECTORY_SEPARATOR;
        }
        $asset_file = $basepath . $asset_file;
        $asset_file = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $asset_file);
        $asset_file = str_replace(DIRECTORY_SEPARATOR.'.'.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $asset_file);
        return $asset_file;
    }
    
}