<?php

namespace WizardBlocks\Modules\Block\Traits;

if ( ! defined( 'ABSPATH' ) ) exit; 

Trait Icons {
    
    public function _block_icon_selector($block_icon, $block_name = '', $input_name = '_block_icon') {
        $input_name_attr = str_replace('[','_', $input_name);
        $input_name_attr = str_replace(']','', $input_name_attr);
        ?>
        <div class="block-icon-selector">
            <select id="<?php echo esc_attr($input_name_attr); ?>" name="<?php echo esc_attr($input_name); ?>" class="block-icon-select">
                <option value="">-- <?php esc_attr_e('CUSTOM', 'wizard-blocks'); ?> --</option><?php
            if (empty($block_icon)) {
                $block_icon = '';
            }
            $is_dash = false;
            $icons = $this->get_dashicons();
            foreach ($icons as $icon) {
                $selected_safe = '';
                if (isset($block_icon) && $block_icon == $icon) {
                    $selected_safe = ' selected';
                    $is_dash = true;
                }
                echo '<option value="' . esc_attr($icon) . '"' . esc_attr($selected_safe) . '>' . esc_html($icon) . '</option>';
            }
            ?></select>
            <p class="d-flex<?php if ($is_dash) { ?> d-none<?php } ?> assets block-icon-src-wrapper" id="<?php echo esc_attr($input_name_attr); ?>-src-wrapper">
                <textarea id="<?php echo esc_attr($input_name_attr); ?>_src" name="<?php echo esc_attr($input_name_attr); ?>_src" placeholder="<svg ...>...</svg>"><?php if (!empty($block_icon) && !$is_dash) echo esc_textarea($block_icon); ?></textarea>
                <a title="<?php esc_attr_e('Upload new Icon', 'wizard-blocks'); ?>" class="dashicons-before dashicons-plus button button-primary upload-icon" href="<?php echo esc_url( admin_url( 'media-upload.php?post_id='.get_the_ID().'&amp;type=image&amp;TB_iframe=1')); ?>" target="_blank"></a>
            </p>
            <p id="<?php echo esc_attr($input_name_attr); ?>_current">
                <?php
                if (!empty($block_icon)) {
                    ?> 
                    <b><?php esc_attr_e('Current', 'wizard-blocks'); ?>:</b><br>
                    <?php $this->the_block_thumbnail($block_name, $block_icon); ?>
                <?php } ?> 
            </p>
            <?php
            // TODO: add ColorPicker for background and foreground
            //https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/#icon-optional
            ?>
        </div>
    <?php }

    // Add the new column
    function add_block_columns($columns) {
        $new_columns = array();
        foreach ($columns as $key => $title) {
            $new_columns[$key] = $title;
            if ($key == 'title') {
                $new_columns['featured_image'] = __('Icon', 'wizard-blocks');
            }
        }
        return $new_columns;
    }

    function populate_block_columns($column, $post_id) {
        if ($column == 'featured_image') {
            if (has_post_thumbnail($post_id)) {
                the_post_thumbnail('thumbnail', ['style' => 'width:40px;height:40px;']); // You can specify a different image size.
            } else {
                $post = get_post($post_id);
                $this->the_block_thumbnail($post->post_name);
            }
        }
    }

    function save_block_icon($block_name, $icon_name = 'icon', $folder = '') {
        //https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/#icon-optional
        $icon = '';
        $input = '_block';
        if ($folder) {
            if ($folder == 'variations') {
                $input = 'variation';
            }
        }
        if (!empty($_POST[$input.'_icon'])) {
            $icon = sanitize_key(wp_unslash($_POST[$input.'_icon']));
        } else {
            if (!empty($_POST[$input.'_icon_src'])) {
                $icon_name = $icon_name.'.svg'; //basename($icon_url);
                list($block_textdomain, $block_slug) = explode('/', $block_name);
                $basepath = $this->get_ensure_blocks_dir($block_slug, $block_textdomain);
                $medias_dir = $basepath.DIRECTORY_SEPARATOR.$folder;  // . DIRECTORY_SEPARATOR . \WizardBlocks\Modules\Media\Media::FOLDER . DIRECTORY_SEPARATOR;
                //var_dump($_POST['_block_icon_src']); die();
                if (str_starts_with($_POST[$input.'_icon_src'], 'http')) {
                    // svg image url
                    $icon_url = sanitize_url($_POST[$input.'_icon_src']);
                    if (!str_starts_with($icon_url, site_url())) {
                        if (str_ends_with(strtolower($icon_url), '.svg')) {
                            //var_dump($icon_url); var_dump(site_url()); die();
                            // Use wp_remote_get to fetch the data
                            $data = wp_remote_get($icon_url);
                            // Save the body part to a variable
                            $svg = $data['body'];
                            $icon_path = $basepath.$icon_name;
                            //var_dump($icon_path); die();
                            $this->get_filesystem()->put_contents($icon_path, $svg);
                            $icon = 'file:./' . $icon_name;
                        }
                    } else {
                        $icon_path = \WizardBlocks\Core\Helper::url_to_path($icon_url);
                        if (!is_dir($medias_dir)) {
                            wp_mkdir_p($medias_dir);
                        }
                        //$icon_name = basename($icon_url);
                        if ($this->get_filesystem()->copy($icon_path, $medias_dir . $icon_name, true)) {
                            $icon = 'file:./' . $icon_name;
                        }
                    }
                } else {
                    // other...like <svg code
                    //$_block_icon_src = sanitize_textarea_field(wp_unslash($_POST['_block_icon_src']));
                    $_block_icon_src = wp_unslash($_POST[$input.'_icon_src']);
                    $icon = $_block_icon_src;
                    $icon = str_replace(PHP_EOL, "", $icon);
                    $icon = str_replace('"', "'", $icon);
                    $icon = str_replace("\'", "'", $icon);
                    //var_dump($icon); die();
                    if (str_starts_with($icon, '<svg ')) {
                        if (!is_dir($medias_dir)) {
                            wp_mkdir_p($medias_dir);
                        }
                        //var_dump($medias_dir . $icon_name); die();
                        if ($this->get_filesystem()->put_contents($medias_dir . $icon_name, $_block_icon_src)) {
                            $icon = 'file:./' . $icon_name;
                        }
                    }
                }
            }
        }
        return $icon;
    }

    function the_block_thumbnail($block_name, $icon = '', $attr = []) {
        if (empty($attr['width'])) {
            $attr['width'] = 40;
            $attr['height'] = 40;
            $attr['font-size'] = '40px';
        }
        if (empty($attr['font-size'])) {
            $attr['font-size'] = $attr['width'].'px';
        }
        
        $width = $attr['width'];
        $height = 'auto';
        if (!empty($attr['height'])) {
            $height = $attr['height'];
            unset($attr['height']);
        }
        $attr['max-width'] = $attr['width'].'px';
        unset($attr['width']);
        
        $class = 'block-icon';
        if (!empty($attr['class'])) {
            $class .= ' '.$attr['class'];
            unset($attr['class']);
        }
        
        $block_json = $this->get_block_json($block_name);
        if (!$icon) {
            if (!empty($block_json['icon'])) {
                $icon = $block_json['icon'];
            }
        }
        
        if ($icon) {
            $dashicons = $this->get_dashicons();
            if (in_array($icon, $dashicons)) {
                ?> 
                <span class="<?php echo esc_attr($class); ?> dashicons dashicons-<?php echo esc_attr($icon); ?>" style="<?php echo esc_attr($this->get_icon_style($attr)); ?>"></span> 
                <?php
            } else {
                //var_dump($icon);
                if (str_starts_with($icon, 'file:./')) {
                    list($block_textdomain, $block_slug) = explode('/', $block_json['name']);
                    $basepath = $this->get_ensure_blocks_dir($block_slug, $block_textdomain);
                    $icon_path = str_replace('file:./', $basepath, $icon);
                    $icon_path = str_replace('/', DIRECTORY_SEPARATOR, $icon_path);
                    $icon_url = \WizardBlocks\Core\Helper::path_to_url($icon_path);
                    ?>
                    <img class="<?php echo esc_attr($class); ?>" width="<?php echo esc_attr($width); ?>" src="<?php echo esc_url($icon_url); ?>" style="<?php echo esc_attr($this->get_icon_style($attr)); ?>">
                    <?php
                } else { 
                    if (filter_var($icon, FILTER_VALIDATE_URL)) { ?>
                    <img class="<?php echo esc_attr($class); ?>" width="<?php echo esc_attr($width); ?>" src="<?php echo esc_url($icon); ?>" style="<?php echo esc_attr($this->get_icon_style($attr)); ?>">
                <?php } else {
                    // PHPCS - The SVG file content is being read from a strict file path structure.
                    $block_icon_safe = $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
                    ?>
                    <span class="<?php echo esc_attr($class); ?>" style="<?php echo esc_attr($this->get_icon_style($attr)); ?>"><?php echo $block_icon_safe; ?></span>
                <?php }
                }
            }
        }
    }
    
    function get_icon_style($attr = []) {
        $style = '';
        foreach ($attr as $key => $value) {
            $style .= $key.':'.$value.';';
        }
        return $style;
    }

    function get_dashicons() {
        $icons = [];
        //Get an instance of WP_Scripts or create new;
        $wp_styles = wp_styles();
        //Get the script by registered handler name
        $style = $wp_styles->registered['dashicons'];
        $dashicons = ABSPATH . $style->src;
        $dashicons = str_replace('//', DIRECTORY_SEPARATOR, $dashicons);
        $dashicons = str_replace('/', DIRECTORY_SEPARATOR, $dashicons);
        if (file_exists($dashicons)) {
            $css = $this->get_filesystem()->get_contents($dashicons);  //wp_remote_get($dashicons); //
            $tmp = explode('.dashicons-', $css);
            foreach ($tmp as $key => $piece) {
                if ($key) {
                    list($icon, $more) = explode(':', $piece, 2);
                    $icons[$icon] = $icon;
                }
            }
        }
        unset($icons['before']);
        return $icons;
    }

    function get_icons_block() {
        return [
            //'core/block' => 'library_symbol',
            'core/pattern' => 'library_symbol',
            'core/navigation-submenu' => 'remove_submenu',
            'core/page-list-item' => 'library_page',
            'core/page-list' => 'library_pages',
            'core/archives' => 'library_archive',
            'core/avatar' => 'comment_author_avatar',
            'core/categories' => 'post_categories',
            'core/post-author' => 'post_author',
            'core/post-author-biography' => 'post_author',
            'core/post-author-name' => 'post_author',
            'core/comments' => 'post_comments',
            'core/comments-title' => 'library_title',
            'core/comment-author-name' => 'comment_author_name',
            'core/comment-content' => 'comment_content',
            'core/comment-date' => 'post_date',
            //'core/comment-count' => 'post_comments_count',
            'core/comment-template' => 'library_layout',
            'core/comment-date' => 'post_date',
            'core/comments-pagination' => 'query_pagination',
            'core/comments-pagination-next' => 'query_pagination_next',
            'core/comments-pagination-numbers' => 'query_pagination_numbers',
            'core/comments-pagination-previous' => 'query_pagination_previous',
            //'core/comment-edit-link' => 'comment_edit_link',
            'core/footnotes' => 'format_list_numbered',
            //'core/footnotes' => 'format_list_bullets',
            'core/home-link' => 'library_home',
            'core/latest-comments' => 'library_comment',
            'core/latest-posts' => 'post_list',
            'core/loginout' => 'library_login',
            'core/navigation-link' => 'custom_link',
            'core/spacer' => 'resize_corner_n_e',
            'core/media-text' => 'media_and_text',
            'core/freeform' => 'library_classic',
            'core/template-part' => 'library_layout',
            //'core/embed' => 'embedContentIcon',
            'core/tag-cloud' => 'library_tag',
            'core/social-links' => 'library_share',
            'core/site-title' => 'map_marker',
            'core/site-tagline' => 'site_tagline_icon',
            'core/read-more' => 'library_link',
            'core/query-title' => 'library_title',
            'core/query' => 'library_loop',
            'core/query-no-results' => 'library_loop',
            'core/post-title' => 'library_title',
            'core/post-template' => 'library_layout',
            'woocommerce/all-reviews' => 'post_comments',
            'woocommerce/reviews-by-product' => 'comment_content',
            'woocommerce/reviews-by-category' => 'comment_content',
                // TODO: complete core/woo association
        ];
    }

    function get_icons_core() {
        $icons_core = [];
        $icons_block = [];
        $block_library_js = $this->get_filesystem()->get_contents(get_home_path() . DIRECTORY_SEPARATOR . 'wp-includes' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'dist' . DIRECTORY_SEPARATOR . 'block-library.js');
        //var_dump($block_library_js);
        $tmp = explode('external_wp_primitives_namespaceObject.SVG,', $block_library_js);
        foreach ($tmp as $key => $value) {
            if ($key) {
                $tmp2 = explode('// ', $value, 2);

                $tmp3 = explode('/* harmony default export */', reset($tmp2), 2);

                $tmp5 = explode(' = (', end($tmp3), 2);
                if (count($tmp5) == 2) {
                    list($tmp9, $more) = $tmp5;
                    $tmpname = explode(' ', $tmp9);
                    $name = end($tmpname);
                    list($name2, $more2) = explode(');', $more, 2);
                    //echo $name.'-'.$name2.'<br>';

                    $tmp8 = explode('/**', end($tmp2), 2);
                    //var_dump(reset($tmp8)); //die();
                    $tmp6 = explode('/edit.js', reset($tmp8));
                    if (count($tmp6) == 2) {
                        $tmp7 = explode('/build-module/', reset($tmp6), 2);
                        if (count($tmp7) == 2) {
                            list($more3, $block_name) = $tmp7;
                            $icons_block['core/' . $block_name] = $name;
                        }
                    }
                }
                $tmp8 = explode('));', reset($tmp2), 2);
                if (count($tmp8) < 2) {
                    //var_dump(reset($tmp2)); die();
                    $tmp8 = explode(');', reset($tmp2), 2);
                    //echo count($tmp8);
                }
                if (count($tmp8) > 1) {
                    list($jsons, $tmp4) = $tmp8;
                    $jsons = str_replace('width:', '"width":', $jsons);
                    $jsons = str_replace('height:', '"height":', $jsons);
                    $jsons = str_replace('xmlns:', '"xmlns":', $jsons);
                    $jsons = str_replace('viewBox:', '"viewBox":', $jsons);
                    $jsons = str_replace('fillRule:', '"fill-rule":', $jsons);
                    $jsons = str_replace('clipRule:', '"clip-rule":', $jsons);
                    $jsons = str_replace('d:', '"d":', $jsons);
                    $jsons = str_replace('cx:', '"cx":', $jsons);
                    $jsons = str_replace('cy:', '"cy":', $jsons);
                    $jsons = str_replace('r:', '"r":', $jsons);
                    $svg_objs = explode(', (0,external_wp_element_namespaceObject.createElement)(external_wp_primitives_namespaceObject.', $jsons);
                    if (count($svg_objs) == 1) {
                        //6.6 beta
                        $svg_objs = explode(', (0,external_React_namespaceObject.createElement)(external_wp_primitives_namespaceObject.', $jsons);
                    }
                    if (count($svg_objs) == 1) {
                        //6.6
                        $svg_objs = explode('children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.', $jsons);
                        //var_dump($jsons); die();
                        //echo count($svg_objs);
                    }
                    if (count($svg_objs) > 1) {
                        $svg_wrap_json = array_shift($svg_objs);
                        $svg_wrap = json_decode($svg_wrap_json, true);
                        if ($svg_wrap) {
                            $svg = '<svg';
                            if (empty($svg_wrap['width']))
                                $svg_wrap['width'] = 24;
                            if (empty($svg_wrap['height']))
                                $svg_wrap['height'] = 24;
                            foreach ($svg_wrap as $key => $value) {
                                $svg .= ' ' . $key . '="' . $value . '"';
                            }
                            $svg .= ' aria-hidden="true" focusable="false">';
                            foreach ($svg_objs as $svg_obj) {
                                list($type, $svg_inner_json) = explode(',', $svg_obj, 2);
                                $svg_inner_json = str_replace(')', '', $svg_inner_json);
                                $svg_inner = json_decode($svg_inner_json, true);
                                if ($svg_inner) {
                                    $svg .= '<' . strtolower($type);
                                    foreach ($svg_inner as $key => $value) {
                                        $svg .= ' ' . $key . '="' . $value . '"';
                                    }
                                    $svg .= '></' . strtolower($type) . '>';
                                }
                            }
                            $svg .= '</svg>';
                            $icons_core[$name] = $svg;
                        }
                    }
                }
            }
        }

        $icons_core['blocks'] = $icons_block;
        //var_dump($icons_core);
        //die();
        // ICONS: \wp-includes\js\dist\block-library.js
        //<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M6 5V18.5911L12 13.8473L18 18.5911V5H6Z"></path></svg>
        $icons_core = apply_filters('wizard-blocks/icons', $icons_core, $this);
        return $icons_core;
    }

    function get_icons_woo($block = []) {
        $svg = '';
        if (defined('WC_PLUGIN_FILE')) {
            $slug = $this->get_block_slug($block);
            switch ($slug) {
                case 'mini-cart': false;
            }
            $woo_js = dirname(WC_PLUGIN_FILE) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'client' . DIRECTORY_SEPARATOR . 'blocks' . DIRECTORY_SEPARATOR . $slug . '.js';
            //var_dump($woo_js);
            if (file_exists($woo_js)) {
                $block_js = $this->get_filesystem()->get_contents($woo_js);
                $tmp = explode('.SVG,', $block_js, 2);
                if (count($tmp) == 2) {

                    $tmp = explode('));var', end($tmp), 2);
                    if (count($tmp) == 2) {
                        list($jsons, $more) = $tmp;

                        $jsons = $this->fix_js_json($jsons);
                        $tmp = explode(',(', $jsons);
                        $svg_objs = [];
                        $types = [];
                        foreach ($tmp as $key => $piece) {
                            if ($key) {
                                $tmp3 = explode('",', $piece, 2);
                                if (count($tmp3) == 2) {
                                    list($more, $json) = $tmp3;
                                    $tmp2 = explode('"', $more);
                                    $types[] = end($tmp2);
                                    $svg_objs[] = str_replace(')', '', $json);
                                }
                            } else {
                                $svg_objs[] = str_replace(')', '', $piece);
                            }
                        }
                        //var_dump($svg_objs); die();
                        if (count($svg_objs) > 1) {
                            $svg_wrap_json = array_shift($svg_objs);
                            $svg_wrap = json_decode($svg_wrap_json, true);
                            if ($svg_wrap) {
                                $svg = '<svg';
                                if (empty($svg_wrap['width']))
                                    $svg_wrap['width'] = 24;
                                if (empty($svg_wrap['height']))
                                    $svg_wrap['height'] = 24;
                                foreach ($svg_wrap as $key => $value) {
                                    $svg .= ' ' . $key . '="' . $value . '"';
                                }
                                $svg .= ' aria-hidden="true" focusable="false">';
                                foreach ($svg_objs as $key => $svg_inner_json) {
                                    $type = $types[$key];
                                    $svg_inner_json = str_replace(')', '', $svg_inner_json);
                                    $svg_inner = json_decode($svg_inner_json, true);
                                    if ($svg_inner) {
                                        $svg .= '<' . strtolower($type);
                                        foreach ($svg_inner as $key => $value) {
                                            $svg .= ' ' . $key . '="' . $value . '"';
                                        }
                                        $svg .= '></' . strtolower($type) . '>';
                                    } else {
                                        //var_dump($svg_inner_json);
                                        return '';
                                    }
                                }
                                $svg .= '</svg>';
                            }
                        }
                    }
                }
            }
        }
        return $svg;
    }

    function fix_js_json($jsons = '') {
        $jsons = str_replace('width:', '"width":', $jsons);
        $jsons = str_replace('height:', '"height":', $jsons);
        $jsons = str_replace('xmlns:', '"xmlns":', $jsons);
        $jsons = str_replace('viewBox:', '"viewBox":', $jsons);
        $jsons = str_replace('fill:', '"fill":', $jsons);
        $jsons = str_replace('fillRule:', '"fill-rule":', $jsons);
        $jsons = str_replace('clipRule:', '"clip-rule":', $jsons);
        $jsons = str_replace('d:', '"d":', $jsons);
        $jsons = str_replace('cx:', '"cx":', $jsons);
        $jsons = str_replace('cy:', '"cy":', $jsons);
        $jsons = str_replace('r:', '"r":', $jsons);
        $jsons = str_replace('stroke:', '"stroke":', $jsons);
        $jsons = str_replace('strokeWidth:', '"stroke-width":', $jsons);
        return $jsons;
    }

    function fix_jsson_js($jsons = '') {
        $jsons = str_replace('"width":', 'width:', $jsons);
        $jsons = str_replace('"height":', 'height:', $jsons);
        $jsons = str_replace('"xmlns":', 'xmlns:', $jsons);
        $jsons = str_replace('"view-box":', 'viewBox:', $jsons);
        $jsons = str_replace('"viewBox":', 'viewBox:', $jsons);
        $jsons = str_replace('"fill":', 'fill:', $jsons);
        $jsons = str_replace('"fill-rule":', 'fillRule:', $jsons);
        $jsons = str_replace('"fillRule":', 'fillRule:', $jsons);
        $jsons = str_replace('"clip-rule":', 'clipRule:', $jsons);
        $jsons = str_replace('"clipRule":', 'clipRule:', $jsons);
        $jsons = str_replace('"d":', 'd:', $jsons);
        $jsons = str_replace('"cx":', 'cx:', $jsons);
        $jsons = str_replace('"cy":', 'cy:', $jsons);
        $jsons = str_replace('"r":', 'r:', $jsons);
        $jsons = str_replace('"stroke":', 'stroke:', $jsons);
        $jsons = str_replace('"stroke-width":', 'strokeWidth:', $jsons);
        $jsons = str_replace('"stroke-linejoin":', 'strokeLinejoin:', $jsons);
        $jsons = str_replace('"stroke-linecap":', 'strokeLinecap:', $jsons);
        $jsons = str_replace('"strokeWidth":', 'strokeWidth:', $jsons);
        $jsons = str_replace('"strokeLinejoin":', 'strokeLinejoin:', $jsons);
        $jsons = str_replace('"strokeLinecap":', 'strokeLinecap:', $jsons);
        return $jsons;
    }

    public function parse_svg($svg) {

        // remove xml line
        if (str_starts_with($svg, '<?xml ')) {
            $tmp = explode('?>', $svg, 2);
            $svg = end($tmp);
        }


        //include_once(WIZARD_BLOCKS_PATH.'modules/block/assets/lib/svg-parser.php');
        $svg_js = $this->generateVanillaJSSVG($svg);
        //$svg_js = 'wp.element.createElement('.$svg_js.'),';
        $svg_js = str_replace('&quot;', '"', $svg_js);
        $parsed = $this->fix_jsson_js($svg_js);
        //var_dump($parsed); die();

        return $parsed;

        /*
        $parsed = "";
        $tags = explode('<', $svg);
        $close = 0;
        foreach ($tags as $key => $tagg) {
            if ($key) {
                list($tag, $more) = explode('>', $tagg, 2);
                $tag_attr = explode(' ', $tag, 2);
                $tag_name = array_shift($tag_attr);
                $tag_attr = reset($tag_attr);
                $tag_attr = str_replace("'", '"', $tag_attr);
                $tag_attr = explode('" ', $tag_attr);
                if (substr($tag_name, 0, 1) == '/') {
                    // close
                    $close--;
                    $parsed .= '),';
                } else {
                    // open
                    $primitive = ($tag_name == 'svg') ? 'SVG' : ucfirst($tag_name);
                    if (!empty($parsed))
                        $parsed .= ',';
                    $parsed .= 'wp.element.createElement(wp.primitives.' . $primitive . ','; //{';
                    $close++;
                    $tag_attrs = [];
                    foreach ($tag_attr as $attr) {
                        list($attr_name, $attr_value) = explode('=', $attr, 2);
                        $tmp_name = explode('-', $attr_name);
                        if ($attr_value != 'http://www.w3.org/2000/svg') {
                            //$attr_name = array_shift($tmp_name).implode('', array_map('ucfirst', $tmp_name));
                            $attr_value = str_replace('"', '', $attr_value);
                            $attr_value = str_replace("'", '', $attr_value);
                            $attr_value = str_replace("\\", '', $attr_value);
                            $attr_value = str_replace("/", '', $attr_value);
                        }
                        $tag_attrs[$attr_name] = $attr_value;
                    }

                    $parsed .= wp_json_encode($tag_attrs);
                    if (substr(end($tag_attr), -1, 1) == '/') {
                        $close--;
                        $parsed .= '),';
                    }
                }
            }
        }
        //for ($i=0; $i<=$close;$i++) {
        //    $parsed .= '),';
        //}
        $parsed = str_replace('),)', '))', $parsed);
        $parsed = str_replace(',,', ',', $parsed);
        $parsed = $this->fix_jsson_js($parsed);
        //var_dump($parsed); die();

        return $parsed;
        */
    }

    function generateVanillaJSSVG($svgString) {
        // Load the SVG string into a DOMDocument object
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadXML($svgString);
        libxml_clear_errors();

        if (!$dom) {
            return "Error: Invalid SVG string.";
        }

        $root = $dom->documentElement;
        $output = '';
        // Process the root element
        $svgJsCode = $this->processNode($root);
        $output .= $svgJsCode;

        return $output;
    }

    // This function recursively processes the SVG nodes
    function processNode($node) {
        $jsCode = '';
        $tagName = $node->tagName;
        $primitivesMap = [
            'svg' => 'wp.primitives.SVG',
            'path' => 'wp.primitives.Path',
            'circle' => 'wp.primitives.Circle',
            'rect' => 'wp.primitives.Rect',
            'polygon' => 'wp.primitives.Polygon',
            'line' => 'wp.primitives.Line',
            'polyline' => 'wp.primitives.Polyline',
            // Add more as needed
            /*
            BlockQuotation
            Defs
            G
            HorizontalRule
            LinearGradient 
            RadialGradient 
            Stop
            View
            */
        ];

        // Map SVG tags to wp.primitives components, or use createElement for others
        $component = isset($primitivesMap[$tagName]) ? "wp.element.createElement(".$primitivesMap[$tagName]. "," : "wp.element.createElement('" . $tagName . "',";

        $attributes = [];
        foreach ($node->attributes as $attr) {
            // Convert kebab-case attributes to camelCase for React props
            $propName = preg_replace_callback('/-([a-z])/', function ($matches) {
                return strtoupper($matches[1]);
            }, $attr->name);
            $attributes[$propName] = $attr->value;
        }

        // Generate the props object string
        $propsString = json_encode($attributes);

        // Build the current element's code
        $jsCode .= $component . ' ';
        $jsCode .= $propsString . ', ';

        // Recursively process child nodes
        $children = [];
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $children[] = $this->processNode($child);
            }
        }

        // Add children to the code
        if (!empty($children)) {
            $jsCode .= "\n" . implode(",\n", $children);
        }

        $jsCode .= '),';

        $jsCode = str_replace(',,', ',', $jsCode); // TODO: fix
        
        return $jsCode;
    }
}
