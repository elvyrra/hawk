{assign name="formContent"}
    {{ $form->inputs['displayedFields'] }}
    {{ $form->inputs['allFields'] }}

    <template id="customize-list-item">
        ${label}
    </template>

    <div class="alert alert-info">{text key="main.customize-list-description"}</div>
    <div class="row">
        <div class="col-xs-6">
            <h4>{text key="main.customize-list-not-displayed-title"}</h4>
            <ol class="sortable not-displayed-fields">
                <li e-each="{$data : allFields, $filter : notDisplayedFilter, $sort : 'name'}" class="alert-info" e-template="'customize-list-item'"></li>
            </ol>
        </div>

        <div class="col-xs-6">
            <h4>{text key="main.customize-list-displayed-title"}</h4>
            <ol class="sortable displayed-fields">
                <li e-each="{$data : allFields, $filter : displayedFilter, $sort : 'order'}" class="alert-success" e-template="'customize-list-item'"></li>
            </ol>
        </div>
    </div>

    {{ $form->fieldsets['submits'] }}
{/assign}

{form id="customize-list" content="{$formContent}"}

