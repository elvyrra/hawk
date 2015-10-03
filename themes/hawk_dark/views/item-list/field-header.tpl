{if(! $field->hidden)}
    <th class="list-column-title" data-bind="with: fields['{{ $field->name }}']">
        <span class='list-title-label list-title-label-{{ $field->list->id }}-{{ $field->name }}'>{{ $field->label }}</span>                          
        {if($field->sort)}
            <div class='list-sort-block' style='display:inline-block'>
                <!-- Sort ascending -->
                <span class="list-sort-column list-sort-asc" data-bind="css : {'list-sort-active' : sort() == 'ASC'}, click : function(data, event){ data.sort(data.sort() == 'ASC' ? '' : 'ASC'); }">
                    <span class='fa fa-sort-alpha-asc' title='{text key="main.list-sort-asc"}'></span>
                </span>

                <!-- sort descending -->
                <span class="list-sort-column list-sort-desc" data-bind="css : {'list-sort-active' : sort() == 'DESC'}, click : function(data, event){ data.sort(data.sort() == 'DESC' ? '' : 'DESC'); }">
                    <span class='fa fa-sort-alpha-desc' title='{text key="main.list-sort-desc"}'></span>
                </span>         
            </div>
        {/if}

        <div class='list-search-block'>     
            {if($field->search)}
                {{ $field->displaySearchInput() }}
            {/if}
        </div>
    </th>
{/if}