<div class="list-navigation">
    <div class="pull-left list-controls">
        {foreach($list->controls as $control)}
            {button _attrs="{$control}" }            
        {/foreach}
    </div>
    
    {if($list->navigation !== false)}
        <div class="pull-right list-pagination">
            <table>
                <tr>
                    <td class='list-result-number' ko-text="recordNumberLabel"></td>
                    <td>
                        <select class="list-max-lines" ko-value="lines">
                            {foreach(ItemList::$lineChoice as $v)}
                                <option value='{{ $v }}'> {{ $v }}</option>
                            {/foreach}                  
                        </select>
                        <span class="line-by-page-label">{text key="main.list-line-per-page"}</span>
                    </td>
                    <td class='list-page-choice'>
                        <span class='list-previous-page icon icon-chevron-circle-left' ko-click="function(data){ data.page(data.page() - 1); }" ko-visible="page() > 1" title="{text key='main.list-previous-page'}" ></span>

                        
                        <input type='text' class='list-page-number' ko-value="page" /> / <span ko-text="maxPages" ></span>
                        
                        <span class="list-next-page icon icon-chevron-circle-right" ko-click="function(data){ data.page(data.page() + 1); }" ko-visible="maxPages() > 1 && page() < maxPages()" title="{text key="main.list-next-page"}"></span>
                    </td>
                </tr>
            </table>
        </div>
    {/if}
</div>