<?php

namespace WizardBlocks\Modules\Styles\Traits;
if ( ! defined( 'ABSPATH' ) ) exit;
trait Save {

    function update_styles($block_json, $post, $update) {


        $wb = \WizardBlocks\Modules\Block\Block::instance();

        $nonce = isset($_POST['meta_fields_meta_box_nonce']) ? sanitize_text_field(wp_unslash($_POST['meta_fields_meta_box_nonce'])) : '';
        if (empty($nonce) || !wp_verify_nonce($nonce, 'meta_fields_save_meta_box_data')) {
            return $block_json;
        }

        // add/edit style
        $style_json = false;
        if (!empty($_POST['style']['name']) || !empty($_POST['style']['title'])) {

            $var_name = sanitize_title(wp_unslash($_POST['style']['name']));
            $var_title = sanitize_text_field(wp_unslash($_POST['style']['title']));
            $var_default = false;
            if (!empty($_POST['style']['isDefault'])) {
                $var_default = true;
            }

            $var_inlineStyle = isset($_POST['style']['inlineStyle']) ? sanitize_textarea_field(wp_unslash($_POST['style']['inlineStyle'])) : false;
            $var_styleHandle = isset($_POST['style']['styleHandle']) ? sanitize_text_field(wp_unslash($_POST['style']['styleHandle'])) : false;
            $var_styleData = isset($_POST['style']['styleData']) ? json_decode(sanitize_textarea_field(wp_unslash($_POST['style']['styleData'])), true) : [];

            $style_json = [
                'name' => $var_name,
                'title' => $var_title,
                'isDefault' => $var_default,
                'inlineStyle' => $var_inlineStyle,
                'styleHandle' => $var_styleHandle,
                'styleData' => $var_styleData
            ];

            // remove empty fields
            $style_json = array_filter($style_json);
        }

        $block_json['styles'] = [];
        if (!empty($_POST['_block_styles'])) {
            foreach (wp_unslash($_POST['_block_styles']) as $style) {
                $style = sanitize_textarea_field($style);
                if ($style = json_decode($style, true)) {
                    $style_name = sanitize_key($style['name'] ?? '');
                    if ($style_name && empty($_POST['_block_styles_delete-' . $style_name])) {
                        if (empty($style_json['name']) || $style['name'] != $style_json['name']) {
                            $block_json['styles'][] = $style;
                        }
                    }
                }
            }
        }
        if (!empty($style_json)) {
            $block_json['styles'][] = $style_json;
        }


        //var_dump($block_json); die();
        return $block_json;
    }
}
