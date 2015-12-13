<table class="list table table-hover">      
    {if(!$list->noHeader)}
        <thead>
            <!-- FIRST LINE, CONTAINING THE LABELS OF THE FIELDS AND THE SEARCH AND SORT OPTIONS -->
            <tr class='ui-state-default list-title-line' >
                {foreach($list->fields as $name => $field)}
                    {{ $field->displayHeader() }}                   
                {/foreach}
            </tr>
        </thead>
    {/if}
    
    <!-- THE CONTENT OF THE LIST RESULTS -->
    <tbody>
        {import file="{$list->resultTpl}"}
    </tbody>
</table>
