'use strict';

require(['app', 'emv', 'jquery'], function(app, EMV, $) {
    const menuManagerNode = document.getElementById('menu-manager');
    const allItems = JSON.parse(app.forms['set-menus-form'].inputs.data.val());

    /**
     * This class manages the UI to manage the main menu
     * @returns {EMV} An EMV instance managing the menu
     */
    const menuManager = new EMV({
        items : allItems,
        activeFilter : function(item) {
            return item.active;
        },
        inactiveFilter : function(item) {
            return !item.active;
        },
        parentFilter : function(parentId) {
            return function(item) {
                return item.active && item.parenId === parentId;
            };
        }
    });

    /**
     * Get an item by it id
     * @param  {int} id The id of the item to get
     * @returns {Object} The found item
     */
    menuManager.getItemById = function(id) {
        return this.items.find(function(item) {
            return item.id === id;
        });
    }.bind(menuManager);

    /**
     * Get items by their parent id
     * @param  {int} parentId The id of the parent item
     * @returns {Array}        The list of the parent children items
     */
    menuManager.getItemsByParent = function(parentId) {
        return this.items.filter(function(item) {
            return item.active && item.parentId === parentId;
        });
    }.bind(menuManager);

    /**
     * get the item children
     * @param   {Object} menuItem The menu item to get the children
     * @returns {Array}         An array containing the item children
     */
    menuManager.getChildren = function(menuItem) {
        return this.items.filter(function(item) {
            return item.active && item.parentId === menuItem.id;
        });
    }.bind(menuManager);

    /**
     * Activate an item
     * @param  {Objcet} item The item to activate
     */
    menuManager.activateItem = function(item) {
        item.parentId = 0;
        item.order = Math.max.apply(this, this.getItemsByParent(0).map(function(item) {
            return item.order;
        })) + 1;
        item.active = 1;
    }.bind(menuManager);

    /**
     * Deactivate an item
     * @param  {Object} item  The item to deactivate
     */
    menuManager.deactivateItem = function(item) {
        item.active = 0;
    };

    /**
     * Edit an item
     * @param  {Objcet} item The item to edit
     */
    menuManager.editItem = function(item) {
        app.dialog(app.getUri('edit-menu', {itemId : item.id}));
    };

    /**
     * Remove an item from the menu
     * @param  {Object} item  The item to remove
     */
    menuManager.removeItem = function(item) {
        $.get(app.getUri('delete-menu', {
            itemId : item.id
        }))

        .then(function() {
            this.items.splice(this.items.indexOf(item), 1);

            this.refresh();
        }.bind(this));
    }.bind(menuManager);

    /**
     * Refresh the menu manager
     */
    menuManager.refresh = function() {
        $('#sort-menu-wrapper .sortable').sortable({
            handle: '.drag-handle',
            placeholderClass : 'placeholder',

            isValidTarget: function($item, container) {
                const id = parseInt($item.attr('data-id'));
                const moved = this.getItemById(id);

                const parentId = parseInt(container.target.attr('data-parent'));

                if((!moved.action || moved.children && moved.children.length) && parentId !== 0) {
                    return false;
                }

                return true;
            }.bind(this),

            onDrop: function($item, container, _super) {
                _super($item, container);

                // Get the moved item
                const id = parseInt($item.attr('data-id'));
                const moved = this.getItemById(id);

                // Get the parent item
                const parentId = parseInt(container.target.attr('data-parent'));

                // calculate the new order of the item
                const index = $item.parent().children().index($item);

                moved.order = index;

                // Increment items that are ordered after this one
                $item.parent().children().each(function(index, elem) {
                    const item = elem.$context;

                    item.order = index;
                });

                // Set the parent id to the item
                moved.parentId = parentId;

                this.refresh();
            }.bind(this)
        });
    }.bind(menuManager);


    menuManager.$apply(menuManagerNode);

    menuManager.refresh();
});
