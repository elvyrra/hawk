<div class="list-navigation">
    <div class="pull-left list-controls">
        {foreach($list->controls as $control)}
            {if(!empty($control))}
                {button _attrs="{$control}" }
            {/if}
        {/foreach}
    </div>

    {if($list->navigation !== false)}
        <div class="pull-right list-pagination">
            <table>
                <tr>
                    <td class='list-result-number'>${recordNumberLabel}</td>
                    <td>
                        <select class="list-max-lines" e-value="lines">
                            {foreach(ItemList::$lineChoice as $v)}
                                <option value='{{ $v }}'> {{ $v }}</option>
                            {/foreach}
                        </select>
                        <span class="line-by-page-label">{text key="main.list-line-per-page"}</span>
                    </td>
                    <td class='list-page-choice'>
                        {icon icon="chevron-circle-left" class="list-previous-page" e-click="$this.page--" e-show="page > 1" title="{text key='main.list-previous-page'}"}


                        <input type='text' class='list-page-number' e-value="page" /> / ${maxPages}

                        {icon icon="chevron-circle-right" class="list-next-page" e-click="$this.page++" e-show="maxPages > 1 && page < maxPages" title="{text key='main.list-next-page'}"}
                    </td>
                </tr>
            </table>
        </div>
    {/if}
</div>