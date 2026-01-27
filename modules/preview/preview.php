<?php

namespace WizardBlocks\Modules\Preview;

use WizardBlocks\Core\Utils;
use WizardBlocks\Base\Module_Base;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Preview extends Module_Base {

    public function __construct() {
        
        if (isset($_GET['post'])) {
            add_action('init', function () {
                $wb = \WizardBlocks\Modules\Block\Block::instance();
                if ($wb->is_block_edit()) {
                    $this->enqueue_style('block-preview', 'assets/css/block-preview.css');
                    $this->enqueue_script('block-preview', 'assets/js/block-preview.js');
                }
            });
        }
        
        $wb = \WizardBlocks\Modules\Block\Block::instance();
        
        //only on update
        if ($wb->is_block_edit()) {

            if (isset($_GET['post'])) {
                add_action('add_meta_boxes', [$this, 'add_preview_meta_box']);


                add_action('post_submitbox_start', function ($post) {
                //add_action('wizard/block/edit/render', function ($block_json, $post, $wb) {
                    ?>
                    <p>
                        <a href="#block-preview" class="dashicons-before dashicons-welcome-view-site button d-block"> <?php esc_html_e('Check Block Preview', 'wizard-blocks'); ?>*</a> <small>* <?php esc_html_e('For an optimal Preview please add Default values to Block Attributes and Save your Block.', 'wizard-blocks'); ?></small>
                        <?php /*<br><label for="block-instant-preview"><input id="block-instant-preview" type="checkbox"> <?php esc_html_e('Enable Instant Block Preview update', 'wizard-blocks'); ?></label> */ ?>
                    </p>
                    <hr>
                    <?php
                //}, 10, 3);
                });
            }

        }

        if (!is_admin()) {
            $cpt_name = $wb::get_cpt_name();

            $this->fix_api_access($cpt_name);

            add_action("registered_post_type_" . $cpt_name, [$this, 'registered_post_type_block'], 10, 2);

            add_filter('the_content', [$this, 'the_content']);
            //global $wp_query;
            //var_dump($wp_query);
        }
    }

    function fix_api_access($cpt_name) {
        //https://developer.wordpress.org/rest-api/reference/rendered-blocks/
        if (isset($_GET['post_id']) && isset($_GET['context'])) {
            // fix direct access to render-block api
            include_once(ABSPATH . 'wp-includes' . DIRECTORY_SEPARATOR . 'pluggable.php');
            $user = wp_get_current_user();
            $user_id = get_current_user_id();
            //var_dump($user_id);
            if ($user_id) {
                $post_id = intval($_GET['post_id']);
                $post = get_post($post_id);
                if ($post && get_class($post) == 'WP_Post' && $post->post_type == $cpt_name) {
                    //var_dump($user_id); var_dump($post->post_author);
                    if ($post && current_user_can('edit_post', $post->ID)) {
                        add_filter('user_has_cap', function ($allcaps, $caps, $args, $user) {
                            //var_dump($caps);
                            foreach ($caps as $cap) {
                                if (empty($allcaps[$cap])) {
                                    $allcaps[$cap] = true;
                                }
                            }
                            return $allcaps;
                        }, 10, 4);
                    }
                }
            }
            //var_dump($_GET); die();
        }
    }

    function the_content($content) {
        $wb = \WizardBlocks\Modules\Block\Block::instance();
        $post = get_queried_object();
        //var_dump($post); die();
        if ($post && get_class($post) == 'WP_Post' && $post->post_type == $wb::get_cpt_name()) {
            $block_json = $wb->get_block_json($post->post_name);
            //var_dump($block_json);
            $block = \WP_Block_Type_Registry::get_instance()->get_registered($block_json['name']);
            if ($block && $block->is_dynamic()) {
                //$content = do_shortcode($content);
                $attributes = $wb->get_default_attributes($block_json);
                //var_dump($attributes);
                $block_content = $wb->render($attributes, $content, $block);

                //if (isset($_GET['context']) && $_GET['context'] == 'preview') {
                if (isset($_SERVER['HTTP_SEC_FETCH_DEST']) && $_SERVER['HTTP_SEC_FETCH_DEST'] == 'iframe') {
                    $block_content .= '<style>header,footer,#wpadminbar,.wp-block-post-title{display:none !important;}html,body,main{margin:0 !important;padding:0 !important;}</style>';
                }

                $content = $block_content;
            }
        }

        return $content;
    }

    function registered_post_type_block($post_type, $post_type_object) {
        /*
          // too early to get_queried_object ...
          $post = get_queried_object();
          if ($post && get_class($post) == 'WP_Post' && $post->post_type == $wb::get_cpt_name()) {
          if ( $post && current_user_can( 'edit_post', $post->ID ) ) {
          $post_type_object->public = true;
          }
          }
         */
        //var_dump(current_user_can( 'edit_blocks'));
        if (!current_user_can('edit_blocks')) {
            // prevent guests see the block preview
            $post_type_object->publicly_queryable = false;
        }
    }

    function add_preview_meta_box() {
        add_meta_box(
                'block_preview_box',
                esc_html__('Preview', 'wizard-blocks'),
                [$this, 'block_preview_box_callback'],
                'block',
        );
    }

    public function block_preview_box_callback($post, $metabox) {

        $wb = \WizardBlocks\Modules\Block\Block::instance();
        $json = $post ? $wb->get_json_data($post->post_name) : [];
        $block_textdomain = $wb->get_block_textdomain($json);
        $block_slug = $post->post_name;
        //$basepath = $wb->get_blocks_dir($block_slug, $block_textdomain);
        //$src = '/wp-json/wp/v2/block-renderer/' . $block_textdomain . '/' . $block_slug . '?context=edit&attributes[color]=red&attributes[asd]=Testo&post_id=2&_locale=user';
        /* <a href="<?php echo $src; ?>" target="_blank"><?php echo $src; ?></a> */
        ?>
        <iframe id="block-preview" width="100%" height="600" src="<?php echo esc_url(get_permalink($post).'?context=preview'); ?>"></iframe>
        <?php
    }
}
