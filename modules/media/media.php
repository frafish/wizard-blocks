<?php

namespace WizardBlocks\Modules\Media;

use WizardBlocks\Core\Utils;
use WizardBlocks\Base\Module_Base;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Media extends Module_Base {

    const FOLDER = 'media';

    public function __construct() {

        add_action('add_meta_boxes', [$this, 'add_media_meta_box']);

        add_action('init', function () {
            $wb = \WizardBlocks\Modules\Block\Block::instance();
            if ($wb->is_block_edit()) {
                $this->enqueue_style('block-media', 'assets/css/block-media.css');
                $this->enqueue_script('block-media', 'assets/js/block-media.js', ['query']);
            }
        });

        add_action('save_post', [$this, 'save_medias'], 10, 3);

        // Allow SVG
        add_filter('wp_check_filetype_and_ext', function ($data, $file, $filename, $mimes) {
            $filetype = wp_check_filetype($filename, $mimes);
            return [
                'ext' => $filetype['ext'],
                'type' => $filetype['type'],
                'proper_filename' => $data['proper_filename']
            ];
        }, 10, 4);
        add_filter('upload_mimes', function ($mimes) {
            $mimes['svg'] = 'image/svg+xml';
            return $mimes;
        });
        add_action('admin_head', function () {
            echo '<style type="text/css">
                .attachment-266x266, .thumbnail img {
                     width: 100% !important;
                     height: auto !important;
                }
                </style>';
        });
    }

    function add_media_meta_box() {
        add_meta_box(
                'block_media_box',
                esc_html__('Media', 'wizard-blocks'),
                [$this, 'block_media_box_callback'],
                'block',
                'side',
                'default'
        );
    }

    public function get_media($basepath, $folder = self::FOLDER) {
        $medias_dir = $basepath . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR;
        $_block_media = glob($medias_dir . '*');
        foreach ($_block_media as $mid => $media) {
            $_block_media[$mid] = trim(basename($media));
        }
        return $_block_media;
    }

    public function block_media_box_callback($post, $metabox) {

        $wb = \WizardBlocks\Modules\Block\Block::instance();
        $json = $post ? $wb->get_json_data($post->post_name) : [];
        $block_textdomain = $wb->get_block_textdomain($json);

        $basepath = $wb->get_blocks_dir($post->post_name, $block_textdomain);
        $medias_dir = $basepath . DIRECTORY_SEPARATOR . self::FOLDER . DIRECTORY_SEPARATOR;
        $medias_url = \WizardBlocks\Core\Helper::path_to_url($medias_dir);
        $_block_media = $this->get_media($basepath);
        ?>

        <h3><label for="_block_media"><?php esc_attr_e('Media', 'wizard-blocks'); ?></label></h3>
        <p class="hint"><i><?php esc_attr_e('Block Medias, stored into Block /media folder, which you can link into Content with relative path.', 'wizard-blocks'); ?></i></p>

        <?php
        // Get WordPress' media upload URL
        $upload_link = esc_url(get_upload_iframe_src('image', $post->ID));
        ?>
        <p class="d-flex assets">
            <textarea type="text" id="_block_media" name="_block_media" rows="<?php echo count($_block_media); ?>" placeholder="file:./icon.png"><?php echo esc_textarea(Utils::implode($_block_media, PHP_EOL)); ?></textarea>
            <a title="<?php esc_attr_e('Upload new Media', 'wizard-blocks') ?>" class="dashicons-before dashicons-plus button button-primary upload-medias" href="<?php echo esc_url($upload_link); ?>" target="_blank"></a>
        </p>    
        <div class="block-medias">
        <?php
        foreach ($_block_media as $media) {
            ?>
                <figure class="media-preview">
                    <span class="media-delete dashicons dashicons-trash"></span>
                    <a href="<?php echo esc_url($medias_url . $media); ?>" target="_blank">
                        <img class="media" data-type="<?php echo esc_attr(mime_content_type($medias_dir . $media)); ?>" data-size="<?php echo esc_attr(filesize($medias_dir . $media)); ?>" data-date="<?php echo esc_attr(filemtime($medias_dir . $media)); ?>" src="<?php echo esc_url($medias_url . $media); ?>">
                    </a>    
                </figure>
            <?php
        }
        ?>
        </div>
            <?php /*
              <details class="hidden">
              <summary class="cursor-pointer"><u><?php esc_attr_e('Code to insert Media in Content', 'wizard-blocks'); ?>:</u></summary>
              <ol>
              <?php foreach ($_block_media as $media) { ?>
              <li><i>
              &lt;img src="&lt;?php echo esc_url(plugins_url(__DIR__)); ?&gt;/<?php echo esc_attr(self::FOLDER); ?>/<?php echo esc_attr(urlencode($media)); ?>"&gt;<br>
              </i></li>
              <?php } ?>
              </ol>
              </details>
             */ ?>

        <div tabindex="0" id="block-media-modal" class="media-modal wp-core-ui" role="dialog" aria-labelledby="media-frame-title" style="display:none;">

            <div class="media-modal-content" role="document"><div class="edit-attachment-frame mode-select hide-router">
                    <div class="edit-media-header">
                        <button class="left dashicons"><span class="screen-reader-text"><?php esc_attr_e('View previous media item', 'wizard-blocks'); ?></span></button>
                        <button class="right dashicons"><span class="screen-reader-text"><?php esc_attr_e('View next media item', 'wizard-blocks'); ?></span></button>
                        <button type="button" class="media-modal-close"><span class="media-modal-icon"><span class="screen-reader-text"><?php esc_attr_e('Close dialog', 'wizard-blocks'); ?></span></span></button>
                    </div>
                    <div class="media-frame-title"><h1>&nbsp;<?php esc_attr_e('Media details', 'wizard-blocks'); ?></h1></div>
                    <div class="media-frame-content"><div class="attachment-details save-ready">
                            <div class="attachment-media-view landscape">
                                <h2 class="screen-reader-text"> <?php esc_attr_e('Media Preview', 'wizard-blocks'); ?></h2>
                                <div class="thumbnail thumbnail-image">
                                    <img class="details-image" src="/wp-content/uploads/woocommerce-placeholder.png" draggable="false" alt="">
                                    <div class="attachment-actions">
                                        <button type="button" data-url="<\?php echo esc_url(plugins_url(__DIR__)); ?>/<?php echo esc_attr(self::FOLDER); ?>/" class="button edit-attachment"><?php esc_attr_e('Copy PHP code for Media relative URL', 'wizard-blocks'); ?></button>
                                        <span class="success hidden" aria-hidden="true"><?php esc_attr_e('Copied!', 'wizard-blocks'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="attachment-info">
                                <div class="details">
                                    <h2 class="screen-reader-text"><?php esc_attr_e('Details', 'wizard-blocks'); ?></h2>
                                    <div class="uploaded"><strong><?php esc_attr_e('Uploaded on', 'wizard-blocks'); ?>:</strong> <span><?php gmdate('Y-m-d'); ?></span></div>
                                    <div class="filename"><strong><?php esc_attr_e('File name', 'wizard-blocks'); ?>:</strong> <span>icon.png</span></div>
                                    <div class="file-type"><strong><?php esc_attr_e('File type', 'wizard-blocks'); ?>:</strong> <span>image/png</span></div>
                                    <div class="file-size"><strong><?php esc_attr_e('File size', 'wizard-blocks'); ?>:</strong> <span>117</span> KB</div>
                                    <div class="dimensions"><strong><?php esc_attr_e('Dimensions', 'wizard-blocks'); ?>:</strong> <span>800 by 800</span> pixels</div>
                                </div>
                                <div class="settings">
                                    <span class="setting" data-setting="url">
                                        <label for="attachment-details-two-column-copy-link" class="name"><?php esc_attr_e('File URL', 'wizard-blocks'); ?>:</label>
                                        <input type="text" class="attachment-details-copy-link" id="attachment-details-two-column-copy-link" value="/wp-content/uploads/woocommerce-placeholder.png" readonly="">
                                        <span class="copy-to-clipboard-container">
                                            <button type="button" class="button button-small copy-attachment-url" data-clipboard-target="#attachment-details-two-column-copy-link"><?php esc_attr_e('Copy URL to clipboard', 'wizard-blocks'); ?></button>
                                            <span class="success hidden" aria-hidden="true"><?php esc_attr_e('Copied!', 'wizard-blocks'); ?></span>
                                        </span>
                                    </span>
                                </div>
                                <div class="actions">
                                    <a class="view-attachment" href=""><?php esc_attr_e('View media file', 'wizard-blocks'); ?></a>
                                    <span class="links-separator">|</span>
                                    <a class="download-attachment" href="" download=""><?php esc_attr_e('Download file', 'wizard-blocks'); ?></a>
                                    <span class="links-separator">|</span>
                                    <button type="button" class="button-link delete-attachment"><?php esc_attr_e('Delete permanently', 'wizard-blocks'); ?></button>
                                </div>
                            </div>
                        </div></div>
                </div></div>
        </div>

        <?php
        wp_enqueue_media();
    }

    public function save_medias($post_id, $post, $update) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;
        if (!current_user_can('edit_post', $post_id))
            return;
        if ($post->post_type != \WizardBlocks\Modules\Block\Block::get_cpt_name())
            return;

        $wb = \WizardBlocks\Modules\Block\Block::instance();
        $json = $post ? $wb->get_json_data($post->post_name) : [];
        $block_textdomain = $wb->get_block_textdomain($json);

        $basepath = $wb->get_blocks_dir($post->post_name, $block_textdomain);
        $medias_dir = $basepath . DIRECTORY_SEPARATOR . self::FOLDER . DIRECTORY_SEPARATOR;

        if (!empty($_POST['_block_media'])) {
            $_block_media_txt = sanitize_textarea_field(wp_unslash($_POST['_block_media']));
            $_block_media = explode(PHP_EOL, $_block_media_txt);
            $_block_media = array_map('trim', $_block_media);
            //var_dump($_block_media); die();
            // delete unlisted media
            $_old_media = $this->get_media($basepath);
            //var_dump($_old_media); die();
            //$_delete_media = array_diff($_old_media, $_block_media);
            //var_dump($_delete_media); die();
            /* foreach($_delete_media as $del_media) {
              unlink($medias_dir.$del_media);
              } */
            foreach ($_old_media as $media) {
                if (!in_array($media, $_block_media)) {
                    //$wb->get-filesystem()->delete
                    wp_delete_file($medias_dir . $media);
                }
            }

            //var_dump($_block_media); die();
            foreach ($_block_media as $mid => $amedia) {
                // copy media starting with http
                if (str_starts_with($amedia, 'http')) {
                    $media_url = $amedia;
                    $basename = trim(basename($media_url));
                    if (!is_dir($medias_dir)) {
                        wp_mkdir_p($medias_dir);
                    }
                    if (!str_starts_with($media_url, site_url())) {                        
                        // Use wp_remote_get to fetch the data
                        $data = wp_remote_get($media_url);
                        // Save the body part to a variable
                        $media = $data['body'];
                        $media_path = $medias_dir.$basename;
                        //var_dump($media_path); die();
                        $wb->get_filesystem()->put_contents($media_path, $media);
                    } else {
                        $media_path = \WizardBlocks\Core\Helper::url_to_path($media_url);
                        //var_dump($media_path);
                        //var_dump($medias_dir . $basename);
                        $wb->get_filesystem()->copy($media_path, $medias_dir . $basename, true);
                    }
                    //$_block_media[$mid] = $basename;
                }
            }
            //die();
        }
    }
}
