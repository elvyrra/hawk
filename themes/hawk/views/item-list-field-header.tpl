{if(! $field->hidden)}
    <th class="list-column-title">
        <span class='list-title-label list-title-label-{{ $field->list->id }}-{{ $field->name }}'>{{ $field->label }}</span>                          
        {if($field->sort)}
            <div class='list-sort-block' style='display:inline-block'>
                <!-- Sort ascending -->
                <span class='list-sort-column list-sort-asc {{ $field->sortValue == "ASC" ? "list-sort-active" : "" }}' data-field="{{ $field->name }}" value="{{ $field->sortValue == 'ASC' ? '' : 'ASC' }}">
                    <span class='fa fa-sort-alpha-asc' title='{text key="main.list-sort-asc"}'></span>
                </span>

                <!-- sort descending -->
                <span class='list-sort-column list-sort-desc {{ $field->sortValue == "DESC" ? "list-sort-active" : "" }}' data-field="{{ $field->name }}" value="{{ $field->sortValue == 'DESC' ? '' : 'DESC' }}">
                    <span class='fa fa-sort-alpha-desc' title='{text key="main.list-sort-desc"}'></span>
                </span>         
            </div>
        {/if}

        {if($field->search)}
            <div class='list-search-block'>     
                {if( $field->searchValue )}
                    <input type='text' class="list-search-input not-empty alert-info" data-field="{{ $field->name }}" value="{{ htmlspecialchars($field->searchValue, ENT_QUOTES) }}" />
                    <i class="fa fa-times-circle clean-search" data-field="{{ $field->name }}"></i>
                {else}
                    <input type='text' class="list-search-input empty" data-field="{{ $field->name }}" />
                {/if}
            </div>
        {/if}
    </th>
{/if}