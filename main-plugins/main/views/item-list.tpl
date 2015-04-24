
<div class="list-wrapper" id='{{ $list->id }}' >
<!-- NAVIGATION BAR -->
{if($list->navigation !== false)}
	<div class="list-navigation {{ $list->NavigationClass }}" style="{{ $list->style }}">
		<div class="pull-left">
			{foreach($list->controls as $control)}
				{{ (new ViewPluginButton($control))->display() }}
			{/foreach}
		</div>
		<div class="pull-right">
			<table>
				<tr>
					<td class='list-result-number'>{text key="main.list-results-number" number="$list->recordNumber"}</td>
					<td>
						<select class='list-max-lines'>
							{foreach(array(10,20,30,50,100) as $v)}
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
	</div>
{/if}


	<input type='hidden' name='file' class="list-filename" value='{{ $list->file }}' />	
	{if($list->force)}
		<textarea class='list-forced-result'>{{ json_encode($list->force, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_NUMERIC_CHECK) }}</textarea>
	{/if}
	<table class="list table table-hover table-stripped">
		{if(!$list->noTitle)}
			<!-- FIRST LINE, CONTAINING THE LABELS OF THE FIELDS AND THE SEARCH AND SORT OPTIONS -->
			<tr class='ui-state-default list-title-line'>
				{if($list->checkbox && !$pdf)}
					<th>
						<input type='checkbox' class='list-checkbox-all' value="all" {{ $list->checkbox['default']=="all" ? "checked" : "" }} />
					</th>		
				{/if}
				{foreach($list->fields as $name => $field)}
					{if(!$field['hidden'] && !($pdf & $field['pdf']=== false))}
						<th class="list-column-title">
							<span class='list-title-label list-title-label-{{ $this->id }}-{{ $name }}'>{{ $field['label'] }}</span>							
							{if($field['sort'] !== false)}
								<div class='list-sort-block' style='display:inline-block'>
									<span class='list-sort-column list-sort-asc {{ $list->sorts[$name] == "1" ? "list-sort-active" : "" }}' data-field="{{ $name }}" value="{{ $list->sorts[$name] == '1' ? 0 : 1 }}">
										<span class='fa fa-sort-alpha-asc' title='{text key="main.list-sort-asc"}'></span>
									</span>
									<span class='list-sort-column list-sort-desc {{ $list->sorts[$name] == "-1" ? "list-sort-active" : "" }}' data-field="{{ $name }}" value="{{ $list->sorts[$name] == '-1' ? 0 : -1 }}">
										<span class='fa fa-sort-alpha-desc' title='{text key="main.list-sort-desc"}'></span>
									</span>			
								</div>
							{/if}

							{if($field['search'] !== false)}
								<div class='list-search-block'>		
									<input type='text' class='list-search-input {{ empty($list->searches[$name]) ? "empty" : "not-empty alert-info" }}' data-field="{{ $name }}" value="{{ htmlspecialchars($list->searches[$name], ENT_QUOTES) }}" />
									{if(!empty($list->searches[$name]))}
										<i class="fa fa-times-circle clean-search" data-field="{{ $name }}"></i>
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
			{foreach($display as $id => $line)}
				<tr class="list-line list-line-{{ $list->id }} {{ $linesParameters[$id]['class'] }}" value="{{ $id }}" >					
					{if($list->checkbox)}
						<td style='width:30px'>
							<input type='checkbox' class="list-checkbox list-checkbox-{{ $list->id }}" value="{{ $id }}" {{ $this->checked && ($list->checked == "all" || in_array($id, $list->checked)) ? "checked" : "" }} />
						</td>
					{/if}
					{foreach($line as $name => $data)}
						<td {if($data['class'])} class="{{ $data['class'] }}" {/if}
							{if($data['title'])} title="{{ $data['title'] }}" {/if}
							{if($data['style'])} style="{{ $data['style'] }}" {/if}
							{if($data['onclick'])} onclick="{{ $data['onclick'] }}" {/if}
							{if($data['href'])} href="{{$data['href']}}" {/if}
							{if($data['target'])} target="{{$data['target']}}" {/if} >
							{{ $data['display'] }}
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
	mint.lists["{{ $list->id }}"] = new List({
		id : "{{ $list->id }}",
		action : "{{ $list->action }}",
		lines : {{ $list->lines }},
		page : {{ $list->page }},
		sorts : {{ json_encode($list->sorts,JSON_HEX_QUOT | JSON_HEX_APOS | JSON_FORCE_OBJECT) }},
		searches : {{ json_encode($list->searches,JSON_HEX_QUOT | JSON_HEX_APOS| JSON_FORCE_OBJECT) }},
		selected : {{ $list->selected !== false ? "'$list->selected'" : "null" }},
		maxPages : {{ $pages }}
	});
/* 	mint.lists["{{ $list->id }}"].initControls(); */
</script>