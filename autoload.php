<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
add_action('init', function () {
    $blocks_dirs = apply_filters('wizard/blocks/dirs', []); //'self' => __DIR__
    $blocks = [];
    foreach ($blocks_dirs as $dir) {
        if (is_dir($dir)) {
            //$blocks = array_merge($blocks, glob($dir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR));
            $blocks = array_merge($blocks, glob($dir . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR));
        }
    }
    $blocks = apply_filters('wizard/blocks', $blocks);
    if (!empty($blocks)) {
        foreach ($blocks as $block) {
            $block_json = $block . DIRECTORY_SEPARATOR . 'block.json';
            if (file_exists($block_json)) {
                $metadata = wp_json_file_decode( $block_json, array( 'associative' => true ) );
                if (!empty($metadata['name']) && !\WP_Block_Type_Registry::get_instance()->is_registered($metadata['name'])) {
                    $args = [];
                    // Fallback: if render.php exists in block directory but not declared in block.json
                    if (empty($metadata['render']) && file_exists($block . DIRECTORY_SEPARATOR . 'render.php')) {
                        $args['render_callback'] = function ($attributes, $content, $block_instance) use ($block) {
                            ob_start();
                            $render_file = $block . DIRECTORY_SEPARATOR . 'render.php';
                            $return = include $render_file;
                            $output = ob_get_clean();
                            return $output ?: (is_string($return) ? $return : '');
                        };
                    }
                    /**
                    * Registers the block using the metadata loaded from the `block.json` file.
                    * Behind the scenes, it registers also all assets so they can be enqueued
                    * through the block editor in the corresponding context.
                    *
                    * @see https://developer.wordpress.org/reference/functions/register_block_type/
                    */
                    register_block_type($block_json, $args);
                }
            }
        }
    }
});
