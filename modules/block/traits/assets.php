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
                    //$asset_file = str_replace('/./', DIRECTORY_SEPARATOR, $asset_file);
                    $asset_file = str_replace('/', DIRECTORY_SEPARATOR, $asset_file);
                    //var_dump($asset_file);
                    $asset_file = $basepath . $asset_file;
                    $asset_file = str_replace(DIRECTORY_SEPARATOR.'.'.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $asset_file);
                    //var_dump($asset_file);
                    if (file_exists($asset_file)) {
                        //var_dump($asset_file);
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
        //var_dump($asset_files);
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
                    $file_name = str_replace('file:./', '', $asset_files);
                    if ($ass = array_search($file_name, self::$assets_alias)) {
                        // force native name?
                        $asset_file = 'file:./'.$ass;
                        //var_dump($asset_file); die();
                    } else {
                        $asset_file = $asset_files;
                    }
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
    
    public function assets_merge($assets, $default) {

        // add default
        if (!in_array($default, $assets)) {
            array_unshift($assets, $default);
        }

        // remove minified version
        $pieces = explode('.', $default);
        $min = array_pop($pieces);
        $min = implode('.', $pieces) . '.min.' . $min;
        //var_dump($min);
        if ($key = array_search($min, $assets)) {
            unset($assets[$key]);
        }
        //var_dump($assets);
        return $assets;
    }

    public function asset_form($json, $asset_file, $basepath, $post) {
        $default = $this->get_asset_default_file($json, $asset_file, $basepath);
        $assets = $this->get_asset_files($json, $asset_file, $basepath);
        //var_dump($assets); var_dump($default); var_dump($json[$asset_file]);
        if (count($assets) > 1 || (count($assets) == 1 && reset($assets) != $default)) {
            ?> 
            <nav class="nav-tab-wrapper wb-nav-tab-wrapper">
                <?php
                $assets = $this->assets_merge($assets, $default);
                foreach ($assets as $key => $asset) {
                    ?>
                <a href="#wb-<?php echo esc_attr(sanitize_title($asset)); ?>" class="nav-tab wb-nav-tab<?php echo esc_attr($key ? '' : ' nav-tab-active'); ?>"><?php echo esc_attr(basename($asset)); ?></a>
            <?php } ?>
            </nav>
        <?php } ?>

        <div class="wb-files">
            <?php
            $assets = $this->assets_merge($assets, $default);
            //var_dump($assets);
            foreach ($assets as $key => $asset) {
                //var_dump($asset);
                $tmp = explode(DIRECTORY_SEPARATOR, $asset);
                $asset_name = end($tmp);
                ?>
                <p class="wb-file<?php echo esc_attr( $key ? ' wb-hide' : ''); ?> <?php echo esc_attr(sanitize_title($asset_name)); ?>" id="wb-<?php echo esc_attr(sanitize_title($asset)); ?>">
                    <textarea class="wp-editor-area wb-asset-<?php echo esc_attr(sanitize_title(basename($asset))); ?> wb-codemirror-<?php echo esc_attr(self::$assets[$asset_file]); ?>" id="<?php echo esc_attr(($asset == $default) ? '_block_' . $asset_file . '_file' : sanitize_title($asset)); ?>" name="_block_<?php echo esc_attr($asset_file); ?>_file[<?php echo esc_attr(basename($asset)); ?>]"><?php echo esc_textarea($this->get_asset_file_contents($json, $asset_file, $asset)); ?></textarea>
                </p>              
            <?php }
        ?>
        </div>
        <?php
        // Get WordPress' media upload URL
        $upload_link = esc_url(get_upload_iframe_src('image', $post->ID));
        $block_assets = [];
        foreach ($assets as $asset) {
            $block_assets[] = $this->get_asset_local($asset);
        }
        $txt = empty($json[$asset_file]) ? '' : Utils::implode($json[$asset_file]);
        $txt = empty($block_assets) ? '' : Utils::implode($block_assets);
        $block_assets
        ?>
        <p class="d-flex assets">
            <input type="text" id="_block_<?php echo esc_attr($asset_file); ?>" name="_block_<?php echo esc_attr($asset_file); ?>" value="<?php echo esc_attr($txt); ?>" placeholder="file:./<?php echo esc_attr($asset_file); ?>.<?php echo esc_attr(self::$assets[$asset_file]); ?>">
            <a title="<?php esc_attr_e('Upload new asset', 'wizard-blocks') ?>" class="dashicons-before dashicons-plus button button-primary upload-assets" href="<?php echo esc_url($upload_link); ?>" target="_blank"></a>
        </p>
        <?php
    }
    
    function get_asset_local($asset) {
        return 'file:./'.basename($asset);
    }
    
}