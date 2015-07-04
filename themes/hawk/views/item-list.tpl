
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
							<span class='list-previous-page fa fa-chevron-circle-left' title="{text key="main.list-previous-page"}" ></span>
						{/if}
						
						<input type='text' class='list-page-number' value="{{ $list->page }}"/> {text key="main.list-max-pages" max="$pages"}
						
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
					{if(! $field->hidden)}
						<th class="list-column-title">
							<span class='list-title-label list-title-label-{{ $this->id }}-{{ $name }}'>{{ $field->label }}</span>							
							{if($field->sort)}
								<div class='list-sort-block' style='display:inline-block'>
									<!-- Sort ascending -->
									<span class='list-sort-column list-sort-asc {{ !empty($list->sorts[$name]) && $list->sorts[$name] == "ASC" ? "list-sort-active" : "" }}' data-field="{{ $name }}" value="{{ !empty($list->sorts[$name]) && $list->sorts[$name] == 'ASC' ? '' : 'ASC' }}">
										<span class='fa fa-sort-alpha-asc' title='{text key="main.list-sort-asc"}'></span>
									</span>

									<!-- sort descending -->
									<span class='list-sort-column list-sort-desc {{ !empty($list->sorts[$name]) && $list->sorts[$name] == "DESC" ? "list-sort-active" : "" }}' data-field="{{ $name }}" value="{{ !empty($list->sorts[$name]) && $list->sorts[$name] == 'DESC' ? '' : 'DESC' }}">
										<span class='fa fa-sort-alpha-desc' title='{text key="main.list-sort-desc"}'></span>
									</span>			
								</div>
							{/if}

							{if($field->search)}
								<div class='list-search-block'>		
									{if(!empty($list->searches[$name]))}
										<input type='text' class="list-search-input not-empty alert-info" data-field="{{ $name }}" value="{{ htmlspecialchars($list->searches[$name], ENT_QUOTES) }}" />
										<i class="fa fa-times-circle clean-search" data-field="{{ $name }}"></i>
									{else}
										<input type='text' class="list-search-input empty" data-field="{{ $name }}" />
									{/if}
								</div>
							{/if}							
						</th>
					{/if}
				{/foreach}
			</tr>
		{/if}
		
		<!-- THE CONTENT OF THE LIST RESULTS -->
		{if($list->recordNumber)}
			{foreach($data as $id => $line)}
				<tr class="list-line list-line-{{ $list->id }} {{ $linesParameters[$id]['class'] }}" value="{{ $id }}" >					
					{foreach($line as $name => $cell)}
						<td {if(!empty($cell['class']))} class="{{ $cell['class'] }}" {/if}
							{if(!empty($cell['title']))} title="{{ $cell['title'] }}" {/if}
							{if(!empty($cell['style']))} style="{{ $cell['style'] }}" {/if}
							{if(!empty($cell['onclick']))} onclick="{{ $cell['onclick'] }}" {/if}
							{if(!empty($cell['href']))} href="{{$cell['href']}}" {/if}
							{if(!empty($cell['target']))} target="{{$cell['target']}}" {/if} >
							{{ $cell['display'] }}
						</td>
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