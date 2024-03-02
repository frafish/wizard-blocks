<?php
namespace WizardBlocks\Modules\Block\Traits;

trait Type {

    /**
     * Register a custom post type called "book".
     *
     * @see get_post_type_labels() for label keys.
     */
    public function _init_type() {

        $labels = array(
            'name' => _x('Blocks', 'Post type general name', 'textdomain'),
            'singular_name' => _x('Block', 'Post type singular name', 'textdomain'),
            'menu_name' => _x('Blocks', 'Admin Menu text', 'textdomain'),
            'name_admin_bar' => _x('Block', 'Add New on Toolbar', 'textdomain'),
            'add_new' => __('Add New', 'textdomain'),
            'add_new_item' => __('Add New Block', 'textdomain'),
            'new_item' => __('New Block', 'textdomain'),
            'edit_item' => __('Edit Block', 'textdomain'),
            'view_item' => __('View Block', 'textdomain'),
            'all_items' => __('All Blocks', 'textdomain'),
            'search_items' => __('Search Blocks', 'textdomain'),
            'parent_item_colon' => __('Parent Blocks:', 'textdomain'),
            'not_found' => __('No Blocks found.', 'textdomain'),
            'not_found_in_trash' => __('No Blocks found in Trash.', 'textdomain'),
            'featured_image' => _x('Blocks Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'textdomain'),
            'set_featured_image' => _x('Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'textdomain'),
            'remove_featured_image' => _x('Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'textdomain'),
            'use_featured_image' => _x('Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'textdomain'),
            'archives' => _x('Block archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'textdomain'),
            'insert_into_item' => _x('Insert into Block', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'textdomain'),
            'uploaded_to_this_item' => _x('Uploaded to this Block', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'textdomain'),
            'filter_items_list' => _x('Filter Blocks list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'textdomain'),
            'items_list_navigation' => _x('Blocks list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'textdomain'),
            'items_list' => _x('Blocks list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'textdomain'),
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'block'),
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'author', 'excerpt'), // 'editor', 'page-attributes'
            'menu_icon' => 'dashicons-block-default',
        );

        register_post_type('block', $args);
    }
}
