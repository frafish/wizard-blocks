<?php
/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
add_action('init', function () {
    $blocks_dirs = ['self' => __DIR__];
    $blocks_dirs = apply_filters('wizard/blocks/dirs', $blocks_dirs);
    $blocks = [];
    foreach ($blocks_dirs as $dir) {
        if (is_dir($dir)) {
            $blocks = array_merge($blocks, glob($dir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR));
        }
    }
    $blocks = apply_filters('wizard/blocks', $blocks);
    //var_dump($blocks); die();
    if (!empty($blocks)) {
        //var_dump($blocks); die();
        foreach ($blocks as $block) {
            $block_json = $block . DIRECTORY_SEPARATOR . 'block.json';
            if (file_exists($block_json)) {
                $json = file_get_contents($block_json);
                $args = json_decode($json, true);
                if (empty($args['file'])) {
                    $args['file'] = $block_json;
                }
                $render_file = 'render.php';
                if (!empty($args['render'])) {
                    $render_file = str_replace('file:', '', $args['render']);
                    $render_file = str_replace('/', DIRECTORY_SEPARATOR, $render_file);
                }
                $render = $block . DIRECTORY_SEPARATOR . $render_file;
                if (file_exists($render) && empty($args['render'])) {
                    $args['render_callback'] = function ($attributes, $content, $block) use ($block_json, $args, $render) {
                        // frontend assets
                        if ($style = register_block_style_handle($args, 'style')) {
                            wp_enqueue_style($style);
                        }
                        if ($script = register_block_script_handle($args, 'script')) {
                            wp_enqueue_script($script);
                        }
                        if ($viewScript = register_block_script_handle($args, 'viewScript')) {
                            wp_enqueue_script($viewScript);
                        }
                        ob_start();
                        include($render);
                        $code = ob_get_clean();
                        $data = [
                            'attributes' => $attributes,
                            'block' => $block,
                        ];
                        $code = apply_filters('twig/that', $code, $data);
                        return $code;
                    };
                    register_block_type($args['name'], $args);

                    if (empty($args['editorScript'])) {
                        //add_action('enqueue_block_editor_assets', function () use ($block, $args) {
                        add_action('admin_print_footer_scripts', function () use ($args) {
                            $key = sanitize_key(str_replace('/', '-', $args['name'])) . '-editor-script-js';
                            ?>
                            <script id="<?php echo $key; ?>">
                                wp.blocks.registerBlockType("<?php echo $args['name']; ?>", {
                                    edit(props) {
                                        return wp.element.createElement(
                                                wp.element.Fragment,
                                                {},
                                                wp.element.createElement(wp.serverSideRender, {
                                                    block: "<?php echo $args['name']; ?>",
                                                    attributes: props.attributes,
                                                })
                                                );
                                    },
                                    save() {
                                        return null;
                                    },
                                });
                            </script>
                            <?php
                        }, 99);
                        //});
                    }
                } else {
                    register_block_type($block_json);
                }
            }
        }
    }
});
