<!-- Back log content -->
{assign name="backlogContent"}        
    <ol class="inactive" ko-foreach="{data : inactiveItems, as : 'item'}">
        <li ko-attr="{'data-id' : item.id}">
            <span ko-text="item.label"></span>
            <span class="pull-right text-success icon icon-plus icon-lg pointer" ko-click="$root.activateItem.bind($root)"></span>
        </li>
    </ol>
{/assign}

<!-- The structure to sort the menu -->
{assign name="sortingContent"} 
    {{ $form->fields['data'] }}
    <div id="sort-menu-wrapper" ko-template="{ nodes : [template] }"></div>

    <div id="sort-menu-template">
        <ol class="sortable active" ko-foreach="{data : sortedItems, as : 'item' }" data-parent="0">
            <li ko-attr="{ 'data-id': item.id, 'data-order': $index}" ko-class="{'no-action-item' : !item.action}">
                <div class="sortable-item" ko-attr="{title : item.url}">
                    <span class="drag-handle icon icon-arrows"></span>
                    <span ko-text="item.label"></span>                
                    <span class="pull-right text-danger icon icon-trash icon-lg deactivate-item pointer" ko-visible="!(item.children && item.children.length)" ko-click="$root.deactivateItem.bind($root)"></span>                
                </div>
                <ol ko-foreach="{data: children, as : 'subitem'}" ko-attr="{'data-parent': item.id}">
                    <li ko-attr="{ 'data-id': subitem.id, 'data-order': $index}">
                        <div class="sortable-item" ko-attr="{title : item.url}">
                            <span class="drag-handle icon icon-arrows"></span>
                            <span ko-text="subitem.label"></span>
                            <span class="pull-right text-danger icon icon-trash icon-lg deactivate-item pointer" ko-click="$root.deactivateItem.bind($root)"></span>
                        </div>
                    </li>
                </ol>
            </li>
        </ol>
    </div>
{/assign}


{assign name="formContent"}
    {{ $form->fields['valid'] }}
    
    <div class="row">  
        <div class="col-sm-4" id="sort-menu-inactive">
            {panel type="info" title="{Lang::get('admin.sort-menu-inactive-items-title')}" content="{$backlogContent}"}
        </div>

        <div class="col-sm-8" id="sort-menu-active">
            {panel type="primary" title="{Lang::get('admin.sort-menu-active-items-title')}" content="{$sortingContent}"}
        </div>
    </ol>

{/assign}

{form id="{$form->id}" content="{$formContent}"}

