<?php

namespace WizardBlocks\Modules\Block\Traits;

trait Type {

    public $revision;
    public static $cpt_name = 'block';

    /**
     * Register a custom post type called "book".
     *
     * @see get_post_type_labels() for label keys.
     */
    public function _init_type() {

        $labels = array(
            'name' => _x('Blocks', 'Post type general name', 'wizard-blocks'),
            'singular_name' => _x('Block', 'Post type singular name', 'wizard-blocks'),
            'menu_name' => _x('Blocks', 'Admin Menu text', 'wizard-blocks'),
            'name_admin_bar' => _x('Block', 'Add New on Toolbar', 'wizard-blocks'),
            'add_new' => __('Add New', 'wizard-blocks'),
            'add_new_item' => __('Add New Block', 'wizard-blocks'),
            'new_item' => __('New Block', 'wizard-blocks'),
            'edit_item' => __('Edit Block', 'wizard-blocks'),
            'view_item' => __('View Block', 'wizard-blocks'),
            'all_items' => __('My Blocks', 'wizard-blocks'),
            'search_items' => __('Search Blocks', 'wizard-blocks'),
            'parent_item_colon' => __('Parent Blocks:', 'wizard-blocks'),
            'not_found' => __('No Blocks found.', 'wizard-blocks'),
            'not_found_in_trash' => __('No Blocks found in Trash.', 'wizard-blocks'),
            'featured_image' => _x('Blocks Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'wizard-blocks'),
            'set_featured_image' => _x('Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'wizard-blocks'),
            'remove_featured_image' => _x('Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'wizard-blocks'),
            'use_featured_image' => _x('Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'wizard-blocks'),
            'archives' => _x('Block archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'wizard-blocks'),
            'insert_into_item' => _x('Insert into Block', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'wizard-blocks'),
            'uploaded_to_this_item' => _x('Uploaded to this Block', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'wizard-blocks'),
            'filter_items_list' => _x('Filter Blocks list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'wizard-blocks'),
            'items_list_navigation' => _x('Blocks list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'wizard-blocks'),
            'items_list' => _x('Blocks list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'wizard-blocks'),
        );

        $args = array(
            //'label' => _x('Blocks', 'wizard-blocks'),
            'labels' => $labels,
            'public' => true, //false
            //'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array('slug' => self::$cpt_name),
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'author', 'excerpt', 'thumbnail', 'revisions'), // 'editor', 'page-attributes'
            'menu_icon' => 'dashicons-block-default',
            'show_in_rest' => true,
            //'rest_base' => self::$cpt_name.'s',
        );

        register_post_type(self::$cpt_name, $args);

        add_filter('manage_posts_columns', function ($posts_columns, $post_type) {
            if ($post_type == self::$cpt_name) {
                return $this->array_insert_after($posts_columns, 'title', ['description' => __('Description', 'wizard-blocks')]);
            }
            return $posts_columns;
        }, 10, 2);
        add_action('manage_posts_custom_column', function ($column_name, $post_ID) {
            if ($column_name == 'description') {
                if ($post_content = get_the_excerpt($post_ID)) {
                    echo esc_html(substr($post_content, 0, 50));
                }
            }
        }, 10, 2);
    }

    /**
     * Insert a value or key/value pair after a specific key in an array.  If key doesn't exist, value is appended
     * to the end of the array.
     *
     * @param array $array
     * @param string $key
     * @param array $new
     *
     * @return array
     */
    function array_insert_after(array $array, $key, array $new) {
        $keys = array_keys($array);
        $index = array_search($key, $keys);
        $pos = false === $index ? count($array) : $index + 1;

        return array_merge(array_slice($array, 0, $pos), $new, array_slice($array, $pos));
    }

    /**
     * Filters whether a post has changed.
     *
     * By default a revision is saved only if one of the revisioned fields has changed.
     * This filter allows for additional checks to determine if there were changes.
     *
     * @since 4.1.0
     *
     * @param bool    $post_has_changed Whether the post has changed.
     * @param WP_Post $latest_revision  The latest revision post object.
     * @param WP_Post $post             The post object.
     */
    public function has_block_changed($post_has_changed, $latest_revision, $post) {
        // natively check only title and excerpt
        //echo 'REVISION:'; var_dump($post_has_changed); die();
        if ($post->post_type == self::$cpt_name) {
            $post_has_changed = false; 
            $json = $this->get_block_json($post->post_name);
            //var_dump(($_POST['revision'])); die();
            if (!empty($json)) {
                if (!empty($json['version']) && !empty($_POST['_block_version']) 
                        && $_POST['_block_version'] != $json['version']) {
                    $post_has_changed = true; 
                }
                if (!empty($_POST['revision'])) {
                    $post_has_changed = true; 
                }
            }
        }
        return $post_has_changed;
    }
    
    /**
    * Fires once a revision has been saved.
    *
    * @since 2.6.0
    * @since 6.4.0 The post_id parameter was added.
    *
    * @param int $revision_id Post revision ID.
    * @param int $post_id     Post ID.
    */
    public function save_block_revision($revision_id, $post_id) {
        $post = get_post($post_id);
        if ($post->post_type == self::$cpt_name) {
            $json = $this->get_block_json($post->post_name);
            $zip = $this->get_block_revision($json['name'], $this->revision);
            if ($zip) {
                //var_dump($revision_id); die();
                //update_post_meta($revision_id, 'zip', $this->revision);
            }
            // create new zip with new block folder
            //$this->generate_block_zip($json['name'], 'revision');
        }
    }
    
    
    public function generate_block_zip_for_revision($json, $post, $update) {
        if ($this->has_block_changed(false, $post, $post)) {
            //var_dump($json); die();
            // create new zip with previous block folder
            
            /*
            * Compare the proposed update with the last stored revision verifying that
            * they are different, unless a plugin tells us to always save regardless.
            * If no previous revisions, save one.
            */
           $revisions = wp_get_post_revisions( $post_id );
           if ( $revisions ) {
                // Grab the latest revision, but not an autosave.
                foreach ( $revisions as $revision ) {
                        if ( str_contains( $revision->post_name, "{$revision->post_parent}-revision" ) ) {
                                $latest_revision = $revision;
                                break;
                        }
                }           

                //var_dump($post); die();
                $this->revision = strtotime($latest_revision->post_modified); //date('U');
                $filename = $this->get_block_zip_filename($json['name'], true).'_'.$this->revision.'.zip';
                $this->generate_block_zip($json['name'], 'revision', $filename);
            }
        }
        return $json;
    }
    
    public function get_block_revision($block, $revision = '') {
        $basename = $this->get_block_zip_filename($block, true);
        $filename = $this->get_blocks_dir().DIRECTORY_SEPARATOR.'revision'.DIRECTORY_SEPARATOR.$basename.($revision ? '_'.$revision : '_*').'.zip';
        //var_dump($filename);
        $zip = glob($filename);
        if (!empty($zip)) {
            return end($zip);
        }
        return false;
    }
    
    /**
	 * Filters the fields displayed in the post revision diff UI.
	 *
	 * @since 4.1.0
	 *
	 * @param array[] $return       Array of revision UI fields. Each item is an array of id, name, and diff.
	 * @param WP_Post $compare_from The revision post to compare from.
	 * @param WP_Post $compare_to   The revision post to compare to.
	 */
	public function get_revision_ui_diff( $return, $compare_from, $compare_to ) {
            //var_dump($compare_from); var_dump($zip_from); var_dump($zip_to); die(); 
            $parent = get_post($compare_from->post_parent);
            $json_last = $this->get_block_json($parent->post_name);
            
            $json_from = $this->get_block_revision_json($compare_from);
            $json_to = $this->get_block_revision_json($compare_to);
            //var_dump($compare_to);
            
            /*
            $return[] = [
                'id' => 'zip',
                'name' => 'Zip',
                'diff' => '<table class="diff"><colgroup><col class="content diffsplit left"><col class="content diffsplit middle"><col class="content diffsplit right"></colgroup><tbody><tr><td>'.$zip_from.'</td><td></td><td>'.$zip_to.'</td></tr></tbody></table>',
            ];
            */
            
            $fields = ['version', 'render', 'attributes'];
            foreach ($json_last as $key => $field) {
                if (is_string($field)) {
                    $empty = '<td class="diff-deletedline"><span aria-hidden="true" class="dashicons dashicons-minus"></span><span class="screen-reader-text">Eliminato: </span></td>';
                    //if (!empty($json[$key])) {
                        $prev = '<td>'.(empty($json_from[$key]) ? $empty : $json_from[$key]).'</td>';
                        $next = '<td>'.(empty($json_to[$key]) ? $empty : $json_to[$key]).'</td>';
                    //}
                    $return[] = [
                        'id' => $key,
                        'name' => $key,
                        'diff' => '<table class="diff"><colgroup><col class="content diffsplit left"><col class="content diffsplit middle"><col class="content diffsplit right"></colgroup><tbody><tr>'.$prev.'<td></td>'.$next.'</tr></tbody></table>',
                    ];
                }
            }
            //TODO
            // add Render
            // add Attributes
            //var_dump($return); die();
            return $return;
        }
        
        public function get_block_revision_json($revision) {
            $json = false;
            $zip = $this->get_revision_zip($revision);
            if ($zip) {
                // Make sure our zipping class exists
                if (!class_exists('ZipArchive')) {
                    die('Cannot find class ZipArchive');
                }
                $file = 'zip://' . $zip. '#block.json';
                //var_dump($file);
                $json = file_get_contents($file);
                if ($json) {
                    $json = json_decode($json, true);
                }
            } else {
                if ($revision->post_parent) {
                    $parent = get_post($revision->post_parent);
                    $json = $this->get_block_json($parent->post_name);
                } else {
                    $json = $this->get_block_json($revision->post_name);
                }
            }   
            //var_dump($json);
            return $json;
        }
        
        public function get_revision_zip($revision) {
            $parent = $revision->post_parent ? get_post($revision->post_parent) : $revision;
            $json = $this->get_block_json($parent->post_name);
            $zip = $this->get_block_revision($json['name'], strtotime($revision->post_date));
            //var_dump($zip);
            if (file_exists($zip)) {
                return $zip;
            }
            return false;
        }
        
        /**
	 * Fires after a post revision has been restored.
	 *
	 * @since 2.6.0
	 *
	 * @param int $post_id     Post ID.
	 * @param int $revision_id Post revision ID.
	 */
        public function restore_block_revision($post_id, $revision_id) {            
            $revision = get_post($revision_id);
            // backup current block as new zip
            $zip = $this->get_revision_zip($revision);
            //var_dump($zip); die();
            if ($zip) {
                // restore revizion zip on block folder
                $this->extract_block_zip($zip);
            }
        }
}
