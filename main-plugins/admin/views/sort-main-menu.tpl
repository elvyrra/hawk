<!-- Inactive items content -->
{assign name="backlogContent"}        
    <ol class="inactive" ko-foreach="{data : inactiveItems, as : 'item'}">
        <li ko-attr="{'data-id' : item.id}">
            <span ko-text="item.label"></span>
            <span class="pull-right text-success icon icon-plus icon-lg pointer" ko-click="$root.activateItem.bind($root)"></span>
            <span class="pull-right text-primary icon icon-pencil icon-lg pointer" ko-visible="item.plugin === 'custom'" ko-click="$root.editItem.bind($root)"></span>
            <span class="pull-right text-danger icon icon-times icon-lg pointer" ko-visible="item.plugin === 'custom'" ko-click="$root.removeItem.bind($root)"></span>
        </li>
    </ol>
{/assign}

<!-- New MenuItem widget -->
{assign name="newMenuItem"}
    {widget class="\Hawk\Plugins\Admin\NewMenuWidget"}
{/assign}

<!-- The structure to sort the menu -->
{assign name="sortingContent"}
    {{ $form->inputs['valid'] }}
    <div id="sort-menu-active">    
        {{ $form->inputs['data'] }}
        <div id="sort-menu-wrapper" ko-template="{ nodes : [template] }"></div>

        <div id="sort-menu-template">
            <ol class="sortable active" ko-foreach="{data : sortedItems, as : 'item' }" data-parent="0">
                <li ko-attr="{ 'data-id': item.id, 'data-order': $index}" ko-class="{'no-action-item' : !item.action}">
                    <div class="sortable-item" ko-attr="{title : item.url}">
                        <span class="drag-handle icon icon-arrows"></span>
                        <span ko-text="item.label"></span>      
                        <span class="pull-right text-danger icon icon-ban icon-lg deactivate-item pointer" ko-visible="!(item.children && item.children.length)" ko-click="$root.deactivateItem.bind($root)"></span>                
                        <span class="pull-right text-primary icon icon-pencil icon-lg pointer edit-item" ko-visible="item.plugin === 'custom'" ko-click="$root.editItem.bind($root)"></span>          
                    </div>
                    <ol ko-foreach="{data: children, as : 'subitem'}" ko-attr="{'data-parent': item.id}">
                        <li ko-attr="{ 'data-id': subitem.id, 'data-order': $index}">
                            <div class="sortable-item" ko-attr="{title : item.url}">
                                <span class="drag-handle icon icon-arrows"></span>
                                <span ko-text="subitem.label"></span>
                                <span class="pull-right text-danger icon icon-ban icon-lg deactivate-item pointer" ko-click="$root.deactivateItem.bind($root)"></span>
                                <span class="pull-right text-primary icon icon-pencil edit-item icon-lg pointer" ko-visible="subitem.plugin === 'custom'" ko-click="$root.editItem.bind($root)"></span>
                            </div>
                        </li>
                    </ol>
                </li>
            </ol>
        </div>
    </div>
{/assign}

{assign name="sortForm"}
    {form id="set-menus-form" content="{$sortingContent}"}
{/assign}

<div class="row">  
    <div class="col-sm-4">
        {panel type="info" id="sort-menu-inactive" title="{Lang::get('admin.sort-menu-inactive-items-title')}" content="{$backlogContent}"}
        {panel type="success" title="{Lang::get('admin.new-menu-form-title')}" content="{$newMenuItem}"}
    </div>

    <div class="col-sm-8" id="">
        {panel type="primary" title="{Lang::get('admin.sort-menu-active-items-title')}" content="{$sortForm}"}
    </div>
</div>




