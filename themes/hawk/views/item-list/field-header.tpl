{if(! $field->hidden)}
    <th class="list-column-title" data-field="{{ $field->name }}" e-with="fields['{{ $field->name }}']">
        <div class="list-label-sorts">
            <span class='list-title-label list-title-label-{{ $field->list->id }}-{{ $field->name }} pull-left' title="{{{ $field->label }}}">{{ $field->label }}</span>
            {if($field->sort)}
                <div class='list-sort-block pull-left'>
                    <!-- Sort ascending -->
                    <span class="list-sort-column list-sort-asc" e-class="{'list-sort-active' : sort == 'ASC'}" e-click="function(){ sort = sort === 'ASC' ? '' : 'ASC'; }">
                        {icon icon="sort-alpha-asc" title="{text key='main.list-sort-asc'}"}
                    </span>

                    <!-- sort descending -->
                    <span class="list-sort-column list-sort-desc" e-class="{'list-sort-active' : sort == 'DESC'}" e-click="function(){ sort = sort === 'DESC' ? '' : 'DESC'; }">
                        {icon icon="sort-alpha-desc" title="{text key='main.list-sort-desc'}"}
                    </span>
                </div>
            {/if}
        </div>

        <div class='list-search-block'>
            {if($field->search)}
                {{ $field->displaySearchInput() }}
            {/if}
        </div>
    </th>
{/if}