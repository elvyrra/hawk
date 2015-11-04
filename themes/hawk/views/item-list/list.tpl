<div class="list-wrapper" id='{{ $list->id }}'>
<!-- NAVIGATION BAR -->
	<div class="list-navigation">
		<div class="pull-left">
			{foreach($list->controls as $control)}
				{{ (new \Hawk\View\Plugins\Button($control))->display() }}
			{/foreach}
		</div>
		{if($list->navigation !== false)}
		<div class="pull-right">
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
			{import "result.tpl"}
		</tbody>
	</table>
</div>
