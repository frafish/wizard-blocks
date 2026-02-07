<?php

namespace WizardBlocks\Modules\Block\Traits;
use WizardBlocks\Core\Utils;

trait Registry {

function get_registered_block($name = '') {
        if ($name) {
            //return \WP_Block_Type_Registry::get_instance()->get_registered($slug);
            $blocks = $this->get_registered_blocks();
            if (isset($blocks[$name])) {
                return $blocks[$name];
            }
        }
        return false;
    }

    function get_registered_blocks() {
        
        $blocks = [];

        $blocks_dir = apply_filters('wizard/blocks/dirs', []);
        unset($blocks_dir['plugin']);

        // get_theme_update_available
        // wp_update_themes
        /* if ($update) {
          unset($blocks_dir['theme']);
          }
         */

        $icons_block = $this->get_icons_block();
        $icons_core = $this->get_icons_core();
        //var_dump($icons_core);
        $icons_block = $icons_block + $icons_core['blocks'];

        $registered_blocks = \WP_Block_Type_Registry::get_instance()->get_all_registered();
        //echo '<pre>';var_dump($registered_blocks);die();
        foreach ($registered_blocks as $name => $block_obj) {
            $block_json = wp_json_encode($block_obj);
            $block = json_decode($block_json, true);
            $block['textdomain'] = $this->get_block_textdomain($block);
            $blocks[$name] = $block;
            list($textdomain, $slug) = explode('/', $name, 2);
            if (empty($block['icon'])) {
                
                if (!empty($icons_block[$name])) {
                    $icon_slug = str_replace('library_', '', $icons_block[$name]);
                    if (!empty($icons_block[$icon_slug])) {
                        $blocks[$name]['icon'] = $icons_block[$icon_slug];
                    }
                }                
                if ($textdomain == 'core' && isset($icons_block[$slug])) {
                    //var_dump($slug);
                    $blocks[$name]['icon'] = $icons_block[$slug];
                }
                if (isset($icons_core['library_' . $slug])) {
                    $blocks[$name]['icon'] = $icons_core['library_' . $slug];
                }
                $slug_underscore = str_replace('-', '_', $slug);
                if (isset($icons_core[$slug_underscore])) {
                    $blocks[$name]['icon'] = $icons_core[$slug_underscore];
                }
                if ($block['textdomain'] == 'woocommerce') {
                    $blocks[$name]['icon'] = $this->get_icons_woo($block);
                }

                if (!empty($icons_block[$name]) && !empty($icons_core[$icons_block[$name]])) {
                    $blocks[$name]['icon'] = $icons_core[$icons_block[$name]];
                }
            }
        }

        $wizard_blocks = $this->get_blocks();
        foreach ($wizard_blocks as $ablock) {
            $block_slug = basename($ablock);
            $block = $this->get_json_data($block_slug);
            $block['folder'] = $ablock;
            if (!empty($block['name'])) {
                $blocks[$block['name']] = $block;
                //var_dump($ablock);
                $blocks[$block['name']]['file'] = $ablock . DIRECTORY_SEPARATOR . 'block.json';
            } else {
                // TODO: no name?!
                //var_dump($block);
            }
        }

        return $blocks;
    }

    public function get_blocks_usage($block = '') {
        global $wpdb;
        $block_init = '<!-- wp:';
        if ($block) {
            $block_init .= $block;
        }
        $block_count = [];
        $posts = $wpdb->get_results($wpdb->prepare('SELECT * FROM %i WHERE post_content LIKE %s AND post_status = "publish"', $wpdb->posts, '%<!-- wp:%'));
        foreach ($posts as $post) {
            $tmp = explode($block_init, $post->post_content);
            foreach ($tmp as $key => $block) {
                if ($key) {
                    list($block_name, $more) = explode(' ', $block, 2);
                    $block_count[$block_name]['count'] = empty($block_count[$block_name]) ? 1 : ++$block_count[$block_name]['count'];
                    if (empty($block_count[$block_name]['posts']) || !in_array($post->ID, $block_count[$block_name]['posts'])) {
                        $block_count[$block_name]['posts'][] = $post->ID;
                    }
                }
            }
        }
        if (!empty($block_count) && $block) {
            return reset($block_count);
        }
        return $block_count;
    }
}