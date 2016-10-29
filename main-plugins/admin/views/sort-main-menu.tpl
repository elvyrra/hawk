<!-- Inactive items content -->
{assign name="backlogContent"}
    <ol class="inactive">
        <li e-each="inactiveItems" data-id="${id}">
            <span>${label}</span>
            {icon icon="plus" class="pull-right text-success pointer" size="lg" e-click=" $root.activateItem.bind($root)"}
            {icon icon="pencil" class="pull-right text-primary pointer" size="lg" e-show="plugin === 'custom'" e-click="$root.editItem.bind($root)"}
            {icon icon="times" class="pull-right text-danger pointer" size="lg" e-show="plugin === 'custom'" e-click="$root.removeItem.bind($root)"}
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
        <div id="sort-menu-wrapper">
            <ol class="sortable active" data-parent="0">
                <li e-each="sortedItems" data-id="${id}" data-order="${$index}" e-class="{'no-action-item' : !action}">
                    <div class="sortable-item" title="${url}">
                        {icon icon="arrows" class="drag-handle"}
                        <span>${label}</span>
                        {icon icon="ban" size="lg" class="pull-right text-danger deactivate-item pointer" e-show="!(children && children.length)" e-click="$root.deactivateItem.bind($root)"}
                        {icon icon="pencil" size="lg" class="pull-right text-primary edit-item pointer" e-show="plugin === 'custom'" e-click="$root.editItem.bind($root)"}
                    </div>
                    <ol data-parent="${id}">
                        <li e-each="children" data-id="${id}" data-order="${$index}">
                            <div class="sortable-item" title="${url}">
                                {icon icon="arrows" class="drag-handle"}
                                <span>${ label }</span>
                                {icon icon="ban" size="lg" class="pull-right text-danger deactivate-item pointer" e-click="$root.deactivateItem.bind($root)"}
                                {icon icon="pencil" size="lg" class="pull-right text-primary edit-item pointer" e-show="plugin === 'custom'" e-click="$root.editItem.bind($root)"}
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

<div class="row" id="menu-manager">
    <div class="col-sm-4">
        {panel type="info" id="sort-menu-inactive" title="{text key='admin.sort-menu-inactive-items-title'}" content="{$backlogContent}"}
        {panel type="success" title="{text key='admin.new-menu-form-title'}" content="{$newMenuItem}"}
    </div>

    <div class="col-sm-8" id="">
        {panel type="primary" title="{text key='admin.sort-menu-active-items-title'}" content="{$sortForm}"}
    </div>
</div>




