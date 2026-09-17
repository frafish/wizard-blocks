jQuery(document).ready(function ($) {

    /**
     * Add edit link button next to block title in BlockCard only if managed by WizardBlocks
     */
    function updateWbEditBlockButton() {
        if (typeof wp === 'undefined' || !wp.data || !wp.data.select('core/block-editor')) {
            return;
        }

        var blockEditor = wp.data.select('core/block-editor');
        var selectedBlock = blockEditor.getSelectedBlock();

        var editUrls = (window.WizardBlocksEditorData && window.WizardBlocksEditorData.editUrls) ? window.WizardBlocksEditorData.editUrls : {};
        var cardTitles = document.querySelectorAll('.block-editor-block-card__title');

        if (!cardTitles.length) {
            return;
        }

        // Check if selected block is managed by WizardBlocks
        var editUrl = null;
        if (selectedBlock && selectedBlock.name) {
            var blockName = selectedBlock.name;
            editUrl = editUrls[blockName] || (blockName.indexOf('/') !== -1 ? editUrls[blockName.split('/')[1]] : null);
        }

        // If no WB block is selected, remove any existing button
        if (!editUrl) {
            document.querySelectorAll('.wb-edit-block-btn').forEach(function (el) {
                el.remove();
            });
            return;
        }

        var tooltip = (window.WizardBlocksEditorData && window.WizardBlocksEditorData.i18n && window.WizardBlocksEditorData.i18n.editBlock)
            ? window.WizardBlocksEditorData.i18n.editBlock
            : 'Edit Block in WizardBlocks';

        cardTitles.forEach(function (cardTitle) {
            var btn = cardTitle.querySelector('.wb-edit-block-btn');
            if (!btn) {
                btn = document.createElement('a');
                btn.className = 'wb-edit-block-btn components-button is-compact has-icon';
                btn.href = editUrl;
                btn.target = '_blank';
                btn.rel = 'noopener noreferrer';
                btn.title = tooltip;
                btn.setAttribute('aria-label', tooltip);
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false">' +
                    '<path d="m19 7-3-3m-9 9-2 5 5-2 11-11a2.828 2.828 0 0 0-4-4L7 13Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
                    '</svg>';
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                });

                var cardName = cardTitle.querySelector('.block-editor-block-card__name');
                if (cardName && cardName.nextSibling) {
                    cardTitle.insertBefore(btn, cardName.nextSibling);
                } else {
                    cardTitle.appendChild(btn);
                }
            } else if (btn.getAttribute('href') !== editUrl) {
                btn.setAttribute('href', editUrl);
            }
        });
    }

    // Immediate and polled updater when selection changes
    var selectionPollTimer = null;
    function triggerSelectionUpdate() {
        // Run immediately
        updateWbEditBlockButton();

        // Also poll rapidly for 600ms while Gutenberg mounts the sidebar
        if (selectionPollTimer) {
            clearInterval(selectionPollTimer);
        }
        var attempts = 0;
        selectionPollTimer = setInterval(function () {
            attempts++;
            updateWbEditBlockButton();
            var cardTitle = document.querySelector('.block-editor-block-card__title');
            if ((cardTitle && cardTitle.querySelector('.wb-edit-block-btn')) || attempts >= 12) {
                clearInterval(selectionPollTimer);
                selectionPollTimer = null;
            }
        }, 50);
    }

    // Subscribe to Gutenberg block selection changes
    if (typeof wp !== 'undefined' && wp.data && wp.data.subscribe) {
        var prevSelectedClientId = null;
        wp.data.subscribe(function () {
            if (!wp.data.select('core/block-editor')) return;
            var currentClientId = wp.data.select('core/block-editor').getSelectedBlockClientId();
            if (currentClientId !== prevSelectedClientId) {
                prevSelectedClientId = currentClientId;
                triggerSelectionUpdate();
            }
        });
    }

    // Observe DOM mutations to immediately catch when sidebar or card title is added
    if (typeof MutationObserver !== 'undefined') {
        var observer = new MutationObserver(function () {
            var cardTitle = document.querySelector('.block-editor-block-card__title');
            if (cardTitle && !cardTitle.querySelector('.wb-edit-block-btn')) {
                updateWbEditBlockButton();
            }
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }

    // Also listen to tab and settings button clicks
    $(document).on('click', '.components-tab-button, .edit-post-header__settings, button[aria-label="Settings"]', function () {
        triggerSelectionUpdate();
    });

    setInterval(function(){
        // Update edit button periodically as fallback
        updateWbEditBlockButton();

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