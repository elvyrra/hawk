<!-- Back log content -->
{assign name="backlogContent"}        
    <ol class="inactive" data-bind="foreach: {data : inactiveItems, as : 'item'}">
        <li data-bind="attr : {'data-id' : item.id}">
            <span data-bind="text: item.label"></span>
            <span class="pull-right text-success fa fa-plus fa-lg" data-bind="click: $root.activateItem.bind($root)"></span>
        </li>
    </ol>
{/assign}

<!-- The structure to sort the menu -->
{assign name="sortingContent"} 
    {{ $form->fields['data'] }}
    <div id="sort-menu-wrapper" data-bind="template: { nodes : [template] }"></div>

    <div id="sort-menu-template">
        <ol class="sortable active" data-bind="foreach: {data : sortedItems, as : 'item' }" data-parent="0">
            <li data-bind="attr : { 'data-id': item.id, 'data-order': $index}, css : {'no-action-item' : !item.action} ">
                <div class="sortable-item">
                    <span class="drag-handle fa fa-arrows"></span>
                    <span data-bind="text: item.label"></span>                
                    <span class="pull-right text-danger fa fa-trash fa-lg deactivate-item" data-bind="visible: !(item.children && item.children.length), click: $root.deactivateItem.bind($root)"></span>                
                </div>
                <ol data-bind="foreach: {data: children, as : 'subitem'}, attr : {'data-parent': item.id}">
                    <li data-bind="attr : { 'data-id': subitem.id, 'data-order': $index}">
                        <div class="sortable-item">
                            <span class="drag-handle fa fa-arrows"></span>
                            <span data-bind="text: subitem.label"></span>
                            <span class="pull-right text-danger fa fa-trash fa-lg deactivate-item" data-bind="click: $root.deactivateItem.bind($root)"></span>
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

