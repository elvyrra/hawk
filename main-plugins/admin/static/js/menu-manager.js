'use strict';

require(['app', 'emv', 'jquery'], (app, EMV, $) => {
    const menuManagerNode = document.getElementById('menu-manager');
    const allItems = JSON.parse(app.forms['set-menus-form'].inputs.data.val());

    /**
     * This class manages the menu items
     */
    class MenuItem extends EMV {
        /**
         * Constructor
         * @param   {Object} data The initial data
         */
        constructor(data) {
            super(data);
        }

        /**
         * get the item children
         * @param   {MenuModel} manager The menu manager
         * @returns {Array}         An array containing the item children
         */
        getChildren(manager) {
            return manager.items.filter((item) => {
                return item.active && item.parentId === this.id;
            });
        }
    }

    /**
     * This class manages the UI to manage the main menu
     */
    class MenuModel extends EMV {
        /**
         * Constructor
         */
        constructor() {
            super({
                items : allItems.map((item) => new MenuItem(item)),
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
            return this.items.filter((item) => {
                return item.active && item.parentId === parentId;
            });
        }

        /**
         * Activate an item
         * @param  {Objcet} item The item to activate
         */
        activateItem(item) {
            item.parentId = 0;
            item.order = Math.max.apply(this, this.getItemsByParent(0).map((item) => item.order)) + 1;
            item.active = 1;

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
                    $item.parent().children().each((index, elem) => {
                        const item = elem.$context;

                        item.order = index;
                    });

                    // Set the parent id to the item
                    moved.parentId = parentId;
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
            data.order = 0;

            menuManager.items.push(new MenuItem(data));
            $(this).reset();
        }
        else {
            app.dialog('close');
            for(let i in data) {
                if(item.hasOwnProperty(i)) {
                    item[i] = data[i];
                }
            }
        }

        menuManager.refresh();
    });
});
