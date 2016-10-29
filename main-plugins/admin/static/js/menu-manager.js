'use strict';

require(['app', 'emv', 'jquery'], (app, EMV, $) => {
    const menuManagerNode = document.getElementById('menu-manager');

    /**
     * This class manages the UI to manage the main menu
     */
    class MenuModel extends EMV {
        /**
         * Constructor
         */
        constructor() {
            super({
                data : {
                    items : JSON.parse(app.forms['set-menus-form'].inputs.data.val())
                },
                computed : {
                    inactiveItems : function() {
                        return this.items.filter((item) => {
                            return !item.active;
                        });
                    },
                    activeItems : function() {
                        return this.items.filter((item) => {
                            return item.active;
                        });
                    },
                    sortedItems : function() {
                        var result = this.getItemsByParent(0);

                        result.forEach((item) => {
                            item.children = this.getItemsByParent(item.id);
                        });

                        return result;
                    }
                }
            });
        }

        /**
         * Get an item by it id
         * @param  {int} id The id of the item to get
         * @returns {Object} The found item
         */
        getItemById(id) {
            return this.items.find((item) => {
                return item.id === id;
            });
        }

        /**
         * Get items by their parent id
         * @param  {int} parentId The id of the parent item
         * @returns {Array}        The list of the parent children items
         */
        getItemsByParent(parentId) {
            const children = this.activeItems.filter((item) => {
                return item.parentId === parentId;
            });

            return children.sort(function(item1, item2) {
                return item1.order - item2.order;
            });
        }

        /**
         * Activate an item
         * @param  {Objcet} item The item to activate
         */
        activateItem(item) {
            item.active = 1;
            item.parentId = 0;
            item.order = this.getItemsByParent(0).length;

            this.refresh();
        }

        /**
         * Deactivate an item
         * @param  {Object} item  The item to deactivate
         */
        deactivateItem(item) {
            item.active = 0;
            this.refresh();
        }

        /**
         * Edit an item
         * @param  {Objcet} item The item to edit
         */
        editItem(item) {
            app.dialog(app.getUri('edit-menu', {itemId : item.id}));
        }

        /**
         * Remove an item from the menu
         * @param  {Object} item  The item to remove
         */
        removeItem(item) {
            $.get(app.getUri('delete-menu', {itemId : item.id}))

            .then(() => {
                this.items.splice(this.items.indexOf(item), 1);

                this.refresh();
            });
        }

        /**
         * Refresh the menu manager
         */
        refresh() {
            $('#sort-menu-wrapper .sortable').sortable({
                handle: '.drag-handle',
                placeholderClass : 'placeholder',

                isValidTarget: ($item, container) => {
                    const id = parseInt($item.attr('data-id'));
                    const moved = this.getItemById(id);

                    const parentId = parseInt(container.target.attr('data-parent'));

                    if((!moved.action || moved.children && moved.children.length) && parentId !== 0) {
                        return false;
                    }

                    return true;
                },

                onDrop: ($item, container, _super) => {
                    this.$clean(menuManagerNode);

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
                    $item.parent().children().each((index) => {
                        const item = this.getItemById($(this).attr('data-id'));

                        item.order = index;
                    });

                    // Set the parent id to the item
                    moved.parentId = parentId;

                    this.$apply(menuManagerNode);
                }
            });
        }
    }


    const menuManager = new MenuModel();

    window.menuManager = menuManager;

    menuManager.$apply(menuManagerNode);

    menuManager.refresh();


    // Manage when a new menu item is created
    app.forms['set-menus-form'].node.on('register-custom-item', function(event, data) {
        const item = menuManager.getItemById(data.id);

        if(!item) {
            menuManager.items.push(data);
            this.reset();
        }
        else {
            for(var prop in data) {
                if(data.hasOwnProperty(prop)) {
                    item[prop] = data[prop];
                }
            }
            app.dialog('close');
        }

        menuManager.refresh();
    });
});
