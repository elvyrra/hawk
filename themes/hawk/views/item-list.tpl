
<div class="list-wrapper" id='{{ $list->id }}' >
<!-- NAVIGATION BAR -->
	<div class="list-navigation">
		<div class="pull-left">
			{foreach($list->controls as $control)}
				{{ (new ViewPluginButton($control))->display() }}
			{/foreach}
		</div>
		{if($list->navigation !== false)}
		<div class="pull-right">
			<table>
				<tr>
					<td class='list-result-number'>{text key="main.list-results-number" number="$list->recordNumber"}</td>
					<td>
						<select class='list-max-lines'>
							{foreach(ItemList::$lineChoice as $v)}
								<option value='{{ $v }}' {{ $v == $list->lines ? "selected" : "" }} > {{ $v }}</option>
							{/foreach}					
						</select>
						<span class="line-by-page-label">{text key="main.list-line-per-page"}</span>
					</td>
					<td class='list-page-choice'>
						{if($list->page > 1)}
							<span class='list-previous-page fa fa-chevron-circle-left' title="{text key='main.list-previous-page'}" ></span>
						{/if}
						
						<input type='text' class='list-page-number' value="{{ $list->page }}"/> {text key="main.list-max-pages" max="{$pages}"}
						
						{if($pages > 1 && $list->page < $pages) }
							<span class="list-next-page fa fa-chevron-circle-right" title="{text key="main.list-next-page"}"></span>
						{/if}
					</td>
				</tr>
			</table>
		</div>
		{/if}
	</div>


	<input type='hidden' name='file' class="list-filename" value='{{ $list->file }}' />	
	
	<table class="list table table-hover table-stripped">
		{if(!$list->noHeader)}
			<!-- FIRST LINE, CONTAINING THE LABELS OF THE FIELDS AND THE SEARCH AND SORT OPTIONS -->
			<tr class='ui-state-default list-title-line'>				
				{foreach($list->fields as $name => $field)}
					{{ $field->displayHeader() }}					
				{/foreach}
			</tr>
		{/if}
		
		<!-- THE CONTENT OF THE LIST RESULTS -->
		{if($list->recordNumber)}
			{foreach($data as $id => $line)}
				<tr class="list-line list-line-{{ $list->id }} {{ $linesParameters[$id]['class'] }}" value="{{ $id }}" >					
					{foreach($line as $name => $cell)}
						{{ $cell }}						
					{/foreach}
				</tr>
			{/foreach}
		{else}
			<tr><td class="list-no-result" colspan="100%"><center class="text-error"> {{ $list->emptyMessage }} </center></td></tr>
		{/if}
	</table>
</div>
<script type="text/javascript">
	app.ready(function(){
		app.lists["{{ $list->id }}"] = new List({
			id : "{{ $list->id }}",
			action : "{{ $list->action }}",
			target : "{{ $list->target }}",
			lines : {{ $list->lines }},
			page : {{ $list->page }},
			sorts : {{ json_encode($list->sorts,JSON_HEX_QUOT | JSON_HEX_APOS | JSON_FORCE_OBJECT) }},
			searches : {{ json_encode($list->searches,JSON_HEX_QUOT | JSON_HEX_APOS| JSON_FORCE_OBJECT) }},
			selected : {{ $list->selected !== false ? "'$list->selected'" : "null" }},
			maxPages : {{ $pages }}
		});
	});
</script>