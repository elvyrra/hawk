<!-- Inactive items content -->
{assign name="backlogContent"}
    <ol class="inactive" ko-foreach="{data : inactiveItems, as : 'item'}">
        <li ko-attr="{'data-id' : item.id}">
            <span ko-text="item.label"></span>
            {icon icon="plus" class="pull-right text-success pointer" size="lg" ko-click=" $root.activateItem.bind($root)"}
            {icon icon="pencil" class="pull-right text-primary pointer" size="lg" ko-visible="item.plugin === 'custom'" ko-click="$root.editItem.bind($root)"}
            {icon icon="times" class="pull-right text-danger pointer" size="lg" ko-visible="item.plugin === 'custom'" ko-click="$root.removeItem.bind($root)"}
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
                        {icon icon="arrows" class="drag-handle"}
                        <span ko-text="item.label"></span>
                        {icon icon="ban" size="lg" class="pull-right text-danger deactivate-item pointer" ko-visible="!(item.children && item.children.length)" ko-click="$root.deactivateItem.bind($root)"}
                        {icon icon="pencil" size="lg" class="pull-right text-primary edit-item pointer" ko-visible="item.plugin === 'custom'" ko-click="$root.editItem.bind($root)"}
                    </div>
                    <ol ko-foreach="{data: children, as : 'subitem'}" ko-attr="{'data-parent': item.id}">
                        <li ko-attr="{ 'data-id': subitem.id, 'data-order': $index}">
                            <div class="sortable-item" ko-attr="{title : item.url}">
                                {icon icon="arrows" class="drag-handle"}
                                <span ko-text="subitem.label"></span>
                                {icon icon="ban" size="lg" class="pull-right text-danger deactivate-item pointer" ko-click="$root.deactivateItem.bind($root)"}
                                {icon icon="pencil" size="lg" class="pull-right text-primary edit-item pointer" ko-visible="subitem.plugin === 'custom'" ko-click="$root.editItem.bind($root)"}
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
        {panel type="info" id="sort-menu-inactive" title="{text key='admin.sort-menu-inactive-items-title'}" content="{$backlogContent}"}
        {panel type="success" title="{text key='admin.new-menu-form-title'}" content="{$newMenuItem}"}
    </div>

    <div class="col-sm-8" id="">
        {panel type="primary" title="{text key='admin.sort-menu-active-items-title'}" content="{$sortForm}"}
    </div>
</div>




