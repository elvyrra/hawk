<table class="list table table-hover">
    {if(!$list->noHeader)}
        <thead>
            <!-- FIRST LINE, CONTAINING THE LABELS OF THE FIELDS AND THE SEARCH AND SORT OPTIONS -->
            <tr class="list-title-line" >
                {if($list->selectableLines)}
                    <td>
                        <input type="checkbox" class="list-select-all-lines" e-value="selection.$all" id="{{ $list->id }}-list-select-all-lines"/>
                        <label for="{{ $list->id }}-list-select-all-lines" class="checkbox-icon"></label>
                    </td>
                {/if}
                {foreach($list->fields as $name => $field)}
                    {{ $field->displayHeader() }}
                {/foreach}
            </tr>
        </thead>
    {/if}

    <!-- THE CONTENT OF THE LIST RESULTS -->
    <tbody e-html="htmlResult">
        <tr>
            <td colspan="500" class="loading">{icon icon="spin" class="icon-spinner center" size="2x"}</td>
        </tr>
    </tbody>
</table>
