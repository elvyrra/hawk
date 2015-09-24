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
					<td class='list-result-number'>{text key="main.list-results-number" number="$list->recordNumber"}</td>
					<td>
						<select class="list-max-lines" data-bind="value: lines">
							{foreach(ItemList::$lineChoice as $v)}
								<option value='{{ $v }}'> {{ $v }}</option>
							{/foreach}					
						</select>
						<span class="line-by-page-label">{text key="main.list-line-per-page"}</span>
					</td>
					<td class='list-page-choice'>
						<span class='list-previous-page fa fa-chevron-circle-left' data-bind="click: function(data){ data.page(data.page() - 1); }, visible: page() > 1" title="{text key='main.list-previous-page'}" ></span>

						
						<input type='text' class='list-page-number' data-bind="value: page"/> / <span data-bind="text: maxPages"></span>
						
						<span class="list-next-page fa fa-chevron-circle-right" data-bind="click: function(data){ data.page(data.page() + 1); }, visible: maxPages() > 1 && page() < maxPages()" title="{text key="main.list-next-page"}"></span>
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
<script type="text/javascript">
	require(['app'], function(){
		app.ready(function(){
			app.lists["{{ $list->id }}"] = new List({
				id : "{{ $list->id }}",
				action : "{{ $list->action }}",
				target : "{{ $list->target }}",
				fields : {{ json_encode(array_keys($list->fields)) }}			
			});
			
			app.lists["{{ $list->id }}"].selected = {{ $list->selected !== false ? "'$list->selected'" : "null" }};
			app.lists["{{ $list->id }}"].maxPages({{ $pages }});	
			

			ko.applyBindings(app.lists["{{ $list->id }}"], document.getElementById("{{ $list->id }}"));				
		});
	});
</script>