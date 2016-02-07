{if(! $field->hidden)}
    <th class="list-column-title" ko-with="fields['{{ $field->name }}']">
        <div class="list-label-sorts">
            {if($field->sort)}
                <div class='list-sort-block pull-right'>
                    <!-- Sort ascending -->
                    <span class="list-sort-column list-sort-asc" ko-class="{'list-sort-active' : sort() == 'ASC'}" ko-click="function(data, event){ data.sort(data.sort() == 'ASC' ? '' : 'ASC'); }">
                        <span class='icon icon-sort-alpha-asc' title='{text key="main.list-sort-asc"}'></span>
                    </span>

                    <!-- sort descending -->
                    <span class="list-sort-column list-sort-desc" ko-class="{'list-sort-active' : sort() == 'DESC'}" ko-click="function(data, event){ data.sort(data.sort() == 'DESC' ? '' : 'DESC'); }">
                        <span class='icon icon-sort-alpha-desc' title='{text key="main.list-sort-desc"}'></span>
                    </span>
                </div>
            {/if}
            <span class='list-title-label list-title-label-{{ $field->list->id }}-{{ $field->name }} pull-left' title="{{ htmlentities($field->label, ENT_QUOTES) }}">{{ $field->label }}</span>
        </div>

        <div class='list-search-block'>
            {if($field->search)}
                {{ $field->displaySearchInput() }}
            {/if}
        </div>
    </th>
{/if}