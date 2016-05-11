{if(! $field->hidden)}
    <th class="list-column-title" data-field="{{ $field->name }}" ko-with="fields['{{ $field->name }}']">
        <div class="list-label-sorts">
            {if($field->sort)}
                <div class='list-sort-block'>
                    <!-- Sort ascending -->
                    <span class="list-sort-column list-sort-asc" ko-class="{'list-sort-active' : sort() == 'ASC'}" ko-click="function(data, event){ data.sort(data.sort() == 'ASC' ? '' : 'ASC'); }">
                        {icon icon="sort-alpha-asc" title="{text key='main.list-sort-asc'}"}
                    </span>

                    <!-- sort descending -->
                    <span class="list-sort-column list-sort-desc" ko-class="{'list-sort-active' : sort() == 'DESC'}" ko-click="function(data, event){ data.sort(data.sort() == 'DESC' ? '' : 'DESC'); }">
                        {icon icon="sort-alpha-desc" title="{text key='main.list-sort-desc'}"}
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