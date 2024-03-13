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
            'name' => _x('Wizard Blocks', 'Post type general name', 'wizard-blocks'),
            'singular_name' => _x('Block', 'Post type singular name', 'wizard-blocks'),
            'menu_name' => _x('Wizard Blocks', 'Admin Menu text', 'wizard-blocks'),
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
