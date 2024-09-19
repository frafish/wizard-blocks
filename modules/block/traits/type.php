<?php
namespace WizardBlocks\Modules\Block\Traits;

trait Type {
    
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
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array('slug' => self::$cpt_name),
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'author', 'excerpt', 'thumbnail'), // 'editor', 'page-attributes'
            'menu_icon' => 'dashicons-block-default',
        );

        register_post_type(self::$cpt_name, $args);
        
        add_filter( 'manage_posts_columns', function($posts_columns, $post_type ) {
            if ($post_type == self::$cpt_name) {
                return $this->array_insert_after($posts_columns, 'title', ['description' => __('Description', 'wizard-blocks')]);
            }
            return $posts_columns;
        }, 10, 2);
        add_action('manage_posts_custom_column', function($column_name, $post_ID) {
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
   function array_insert_after( array $array, $key, array $new ) {
           $keys = array_keys( $array );
           $index = array_search( $key, $keys );
           $pos = false === $index ? count( $array ) : $index + 1;

           return array_merge( array_slice( $array, 0, $pos ), $new, array_slice( $array, $pos ) );
   }
}
