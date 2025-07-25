<?php

namespace WizardBlocks\Modules\Block\Traits;
use WizardBlocks\Core\Utils;

trait Save {

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
        if ($post->post_type != self::get_cpt_name())
            return;
        
        $post_slug = get_post_field('post_name', $post_id);
        $block_slug = $post_slug;
        if (!empty($_POST['_block_name'])) {
            $block_slug = sanitize_key(wp_unslash($_POST['_block_name']));
        }
        
        $block_textdomain = $this->get_plugin_textdomain();
        if ($user = wp_get_current_user()) {
            $block_textdomain = $user->user_nicename;
        }
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
        if (!empty($_POST['_block_attributes']) && $_POST['_block_attributes'] != '{}') {
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
        } else {
            delete_post_meta($post_id, '_transient_block_attributes');
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
        if (!empty($_POST['_block_supports_custom']) && $_POST['_block_supports_custom'] != '{}') {            
            $custom_json = $this->unescape(sanitize_textarea_field(wp_unslash($_POST['_block_supports_custom'], '"')));
            $custom = json_decode($custom_json, true); 
            if ($custom == NULL) {
                update_post_meta($post_id, '_transient_block_supports_custom', $custom_json);
            } else {
                $supports = array_merge($supports, $custom);
                delete_post_meta($post_id, '_transient_block_supports_custom');
            }
        } else {
            delete_post_meta($post_id, '_transient_block_supports_custom');
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
            $image_src = wp_get_attachment_image_url($preview, 'full');
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
            //copy image inside block folder  
            $image_name = basename($image_src);
            if (copy($image_src,$basepath.$image_name)) {
                $example['attributes']['preview'] = "file:./".$image_name;
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
                // remove the asset from the json
                $json[$asset] = [];
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
                        } else {
                            // default asset file, already insert in json
                            $file = false;
                        }
                    }
                    if ($file) {
                        // prevent duplicates
                        if (empty($json[$asset]) || !in_array($file, $json[$asset])) {
                            $json[$asset][] = $file;
                        }
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
        if (!empty($_POST['_block_extra']) && $_POST['_block_extra'] != '{}') {            
            $extra_json = $this->unescape(sanitize_textarea_field(wp_unslash($_POST['_block_extra'], '"')));
            $extra = json_decode($extra_json, true); 
            if ($extra == NULL) {
                update_post_meta($post_id, '_transient_block_extra', $extra_json);
            } else {
                $json = array_merge($json, $extra);
                delete_post_meta($post_id, '_transient_block_extra');
            }
        } else {
            delete_post_meta($post_id, '_transient_block_extra');
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

}