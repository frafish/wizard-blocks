jQuery(document).ready(function ($) {

    setInterval(function(){
        //console.log('resize');
        jQuery( ".interface-complementary-area__fill:not(.ui-resizable)" ).resizable({
            handles: 'w' 
        });
        /*jQuery( ".interface-interface-skeleton__secondary-sidebar:not(.ui-resizable)" ).resizable({
            handles: 'e' 
        });*/
        
        if (!jQuery('.block-editor-list-view-actions').length) {
            
            jQuery('.block-editor-tabbed-sidebar__tablist-and-close-button').append('<div class="block-editor-list-view-actions"></div>');
            
            jQuery('.block-editor-list-view-actions').append('<button type="button" class="components-button block-editor-tabbed-sidebar__expand-all-button is-compact has-icon" aria-label="Expand"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><g><polyline fill="none" points="3 17.3 3 21 6.7 21" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/><line fill="none" stroke-width="2" x1="10" x2="3.8" y1="14" y2="20.2"/><line fill="none" stroke-width="2" x1="14" x2="20.2" y1="10" y2="3.8"/><polyline fill="none" points="21 6.7 21 3 17.3 3" stroke-width="2"/></g></svg></button>');
            jQuery('.block-editor-list-view-actions').append('<button type="button" class="components-button block-editor-tabbed-sidebar__reduce-all-button is-compact has-icon" aria-label="Reduce"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M14 10L21 3M14 10H20M14 10V4M3 21L10 14M10 14V20M10 14H4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>');
            
            jQuery('.block-editor-tabbed-sidebar__close-button').appendTo('.block-editor-list-view-actions');
            
            jQuery('.block-editor-tabbed-sidebar__expand-all-button').on('click', function(){
                 jQuery('.block-editor-list-view-block-contents[aria-expanded="false"] > .block-editor-list-view__expander').trigger('click');
            });
            jQuery('.block-editor-tabbed-sidebar__reduce-all-button').on('click', function(){
                 jQuery('.block-editor-list-view-block-contents[aria-expanded="true"] > .block-editor-list-view__expander').trigger('click');
            });
        }
    }, 1000);
    
});