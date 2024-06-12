<?php

namespace WizardBlocks\Modules\Block\Traits;

Trait Icons {

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
            'core/social-link' => 'library_share',
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
        $block_library_js = file_get_contents(get_home_path() . DIRECTORY_SEPARATOR . 'wp-includes' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'dist' . DIRECTORY_SEPARATOR . 'block-library.js');
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
        return $icons_core;
    }

    function get_icons_woo($block = []) {
        $svg = '';
        if (defined('WC_PLUGIN_FILE')) {
            $slug = $this->get_block_slug($block);
            $woo_js = dirname(WC_PLUGIN_FILE) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'client' . DIRECTORY_SEPARATOR . 'blocks' . DIRECTORY_SEPARATOR . $slug . '.js';
            //var_dump($woo_js);
            if (file_exists($woo_js)) {
                $block_js = file_get_contents($woo_js);
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
}
