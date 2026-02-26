<?php

namespace WizardBlocks\Modules\Variations\Traits;

trait Save {

    function update_variations($block_json, $post, $update) {
        
        //$post_id = $post ? $post->ID : 0;
        
        /*
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;
        if (!current_user_can('edit_post', $post_id))
            return;
        if ($post->post_type != \WizardBlocks\Modules\Block\Block::get_cpt_name())
            return;
        */
        
        $wb = \WizardBlocks\Modules\Block\Block::instance();

        //if (!empty($_POST['_block_name']) && !empty($_POST['_block_textdomain'])) {
            $block_textdomain = $wb->get_block_textdomain($block_json); //sanitize_key(wp_unslash($_POST['_block_textdomain']));
            //$block_slug = $wb->get_block_slug($block_json); //sanitize_key(wp_unslash($_POST['_block_name']));
            
            // TODO: Store in FILE instead of main JSON?
            //$block_json = $wb->get_block_json($block_slug, $block_textdomain);
            //$basepath = $wb->get_blocks_dir($block_slug, $block_textdomain);
            //$var_dir = $basepath . DIRECTORY_SEPARATOR . self::VARIATIONS_FOLDER;
            /*
            if (!empty($_POST['variations']['delete'])) {
                foreach ($_POST['variations']['delete'] as $var_name => $val) {
                    $var_file = $var_dir . DIRECTORY_SEPARATOR . $var_name . '.json';
                    if (file_exists($var_file)) {
                        wp_delete_file($var_file);
                        $icon_file = $var_dir . DIRECTORY_SEPARATOR . $var_name . '.svg';
                        if (file_exists($icon_file)) {
                            wp_delete_file($icon_file);
                        }
                    }
                }
                if (count(glob($var_dir.DIRECTORY_SEPARATOR."*")) === 0) {
                    $wp->get_filesystem()->dir_delete($var_dir);
                }
            }
            */
            
            // add/edit variation
            $var_json = false;
            if (!empty($_POST['variation']['name'])) {
                $var_name = sanitize_title(wp_unslash($_POST['variation']['name']));

                /*
                // create variations folder
                if (!file_exists($var_dir)) {
                    mkdir($var_dir, 0777, true);
                }
                $var_file = $var_dir . DIRECTORY_SEPARATOR . $var_name . '.json';
                */
                
                $var_title = sanitize_text_field(wp_unslash($_POST['variation']['title']));
                $var_description = sanitize_textarea_field(wp_unslash($_POST['variation']['description']));

                $var_category = '';
                if (!empty($_POST['variation']['category'])) {
                    $var_category = sanitize_title(wp_unslash($_POST['variation']['category']));
                }
                
                $var_keywords = [];
                if (!empty($_POST['variation']['keywords'])) {
                    $var_keywords = array_filter(array_map('trim', explode(',', sanitize_text_field(wp_unslash($_POST['variation']['keywords'])))));
                }
                
                $var_innerblocks = [];
                if (!empty($_POST['variation']['innerBlocks'])) {
                    $var_innerblocks = wp_unslash($_POST['variation']['innerBlocks']);
                    $var_innerblocks = json_decode($var_innerblocks, true);
                    /* TODO: validate json */
                }
                
                $var_default = [];
                if (!empty($_POST['variation']['isDefault'])) {
                    $var_default = true;
                }
                
                $var_active = [];
                if (!empty($_POST['variation']['isActive'])) {
                    if (str_contains($_POST['variation']['isActive'], '=')) {
                        //TODO: ( blockAttributes, variationAttributes ) => blockAttributes.providerNameSlug === variationAttributes.providerNameSlug,
                        $var_active = $_POST['variation']['isActive'];
                    } else {
                        $var_active = array_filter(array_map('trim', explode(',', sanitize_text_field(wp_unslash($_POST['variation']['isActive'])))));
                    }
                }
                
                $var_icon = $wb->save_block_icon($block_textdomain . "/" . $var_name, $var_name, '', 'variation');//, self::VARIATIONS_FOLDER);
                
                $var_scope = [];
                if (!empty($_POST['variation']['scope']['block'])) {
                    $var_scope[] = 'block';
                }
                if (!empty($_POST['variation']['scope']['inserter'])) {
                    $var_scope[] = 'inserter';
                }
                if (!empty($_POST['variation']['scope']['transform'])) {
                    $var_scope[] = 'transform';
                }
                
                if (!empty($_POST['variation']['example'])) {
                    $var_example = $_POST['variation']['example'];
                    foreach($var_example as $akey => $aval) {
                        switch($block_json['attributes'][$akey]['type']) {
                            case 'integer':
                            case 'numeric':
                                $var_example[$akey] = floatval($aval);
                                break;
                            case 'boolean':
                                $var_example[$akey] = $aval === 'true' || $aval === 'on';
                                break;
                            case 'object':
                            case 'array':
                                $var_example[$akey] = json_decode($aval, true);
                                break;                               
                        }
                    }
                }
                
                if (!empty($_POST['variation']['attributes'])) {
                    $var_attributes = $_POST['variation']['attributes'];
                    foreach($var_attributes as $akey => $aval) {
                        switch($block_json['attributes'][$akey]['type']) {
                            case 'integer':
                            case 'numeric':
                                $var_attributes[$akey] = floatval($aval);
                                break;
                            case 'boolean':
                                $var_attributes[$akey] = $aval === 'true' || $aval === 'on';
                                break;
                            case 'object':
                            case 'array':
                                $var_attributes[$akey] = json_decode($aval, true);
                                break;                               
                        }
                    }
                }
                
                $var_json = [
                    'name' => $var_name,
                    'title' => $var_title,
                    'description' => $var_description,
                    'category' => $var_category,
                    'keywords' => $var_keywords,
                    'innerBlocks' => $var_innerblocks,
                    'isDefault' => $var_default,
                    'isActive' => $var_active,
                    'icon' => $var_icon,
                    'scope' => $var_scope,
                    'example' => $var_example,
                    'attributes' => $var_attributes
                ];
                
                // remove empty fields
                $var_json = array_filter($var_json);
                
                /*
                //var_dump($json);
                $var_json = apply_filters('wizard_blocks/before_save', $var_json, $post, $update);

                $code = wp_json_encode($var_json, JSON_PRETTY_PRINT);
                $code = str_replace('\/', '/', $code);
                $code = str_replace("\'", "'", $code);

                $result = $wb->get_filesystem()->put_contents($var_file, $code);
                //echo 'SAVE:'; var_dump($result); var_dump($path); var_dump($code); die();
                do_action('wizard_blocks/after_save', $var_json, $post, $update);
                 * 
                 */
            }
                
            //if (!empty($block_json['variations'])) {
                $block_json['variations'] = [];
            //}
            if (!empty($_POST['_block_variations'])) {
             foreach ($_POST['_block_variations'] as $var_name => $variation) {
                 if (empty($_POST['_block_variations_delete['.$var_name.']'])) {
                     if (empty($var_json['name']) || $var_name != $var_json['name']) {
                         $variation = sanitize_textarea_field(wp_unslash($variation));
                         $varj = json_decode($variation, true);
                         //var_dump($varj);
                         if ($varj) {
                             $block_json['variations'][] = $varj;
                         }
                     }
                 }
             }
            }
            if (!empty($var_json)) {
                $block_json['variations'][] = $var_json;
            }
            
        //}
            
        //var_dump($block_json); die();
        return $block_json;
    }
}
