<!-- NAVIGATION BAR -->
{if($list->navigation !== false)}
	<table class="list-navigation {{ $list->NavigationClass }}" style="{{ $list->style }}">
		<tr>
			{foreach(array_reverse($list->buttons) as $button)}
				<td> {{ button($button) }} </td>
			{/foreach}
			<td class='list-result-number'>{{ $list->recordNumber." ".Lang::get('results-number', 'list', 'global', $list->recordNumber) }}</td>
			<td>
				<select class='list-max-lines' data-list="{{ $list->id }}">
					{foreach(array(10,20,30,50,100) as $v)}
						<option value='{{ $v }}' {{ $v == $list->linesNumber ? "selected" : "" }} > {{ $v }}</option>
					{/foreach}					
				</select>
				<span class="line-by-page-label">{{ Lang::get('line-per-page', 'list') }}</span>
			</td>
			<td class='list-page-choice'>
				{if($list->pageNumber > 1)}
					<span class='list-previous-page fa fa-chevron-circle-down fa-rotate-90' data-list='{{ $list->id }}' title="{{ Lang::get('previous-page', 'list') }}" ></span>
				{/if}
				{{ Lang::get('page-label', 'list') }} <input type='text' class='list-page-number' data-list="{{ $list->id }}" value="{{ $list->pageNumber }}"/> {{ Lang::get('max-pages', 'list') }}<span class='list-max-pages' data-list="{{ $list->id }}">{{ $pages }} </span>
				
				{if($pages > 1 && $list->pageNumber < $pages) }
					<span class="list-next-page fa fa-chevron-circle-down fa-rotate-270" data-list="{{ $list->id }}" title="{{ Lang::get('next-page', 'list') }}"></span>
				{/if}
			</td>
		</tr>
	</table>
{/if}

<div class="list-wrapper" id='{{ $list->id }}' >
	<input type='hidden' name='file' class="list-filename" data-list='{{ $list->id }}' value='{{ $list->file }}' />	
	{if($list->force)}
		<textarea class='list-forced-result'>{{ json_encode($list->force, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_NUMERIC_CHECK) }}</textarea>
	{/if}
	<table class="list">
		{if(!$list->noTitle)}
			<!-- FIRST LINE, CONTAINING THE LABELS OF THE FIELDS AND THE SEARCH AND SORT OPTIONS -->
			<tr class='ui-state-default list-title-line'>
				{if($list->checkbox && !$pdf)}
					<th>
						<input type='checkbox' class='list-checkbox-all' value="all" data-list="{{ $list->id }}" {{ $list->checkbox['default']=="all" ? "checked" : "" }} />
					</th>		
				{/if}
				{foreach($list->fields as $field)}
					{if(!$field['hidden'] && !($pdf & $field['pdf']=== false))}
						<th class="list-column-title">
							<span class='list-title-label list-title-label-{{ $this->id }}-{{str_replace(".","_",$field["name"]) }}'>{{ $field['label'] }}</span>
							{if(!$pdf)}
								{if($field['sort'] !== false)}
									<div class='list-sort-block' style='display:inline-block'>
										<span class='list-sort-column list-sort-asc {{ $list->sort[$field["name"]] == "1" ? "list-sort-active" : "" }}' data-list="{{ $list->id }}" data-field="{{ $field['name'] }}" value="{{ $list->sort[$field['name']] == '1' ? 0 : 1 }}">
											<span class='fa fa-sort-alpha-asc' title='A > Z'></span>
										</span>
										<span class='list-sort-column list-sort-desc {{ $list->sort[$field["name"]] == "-1" ? "list-sort-active" : "" }}' data-list="{{ $list->id }}" data-field="{{ $field['name'] }}" value="{{ $list->sort[$field['name']] == '-1' ? 0 : -1 }}">
											<span class='fa fa-sort-alpha-desc' title='Z > A'></span>
										</span>			
									</div>
								{/if}
								{if($field['search'] !== false)}
									<div class='list-search-block'>		
										<input type='text' class='list-search-input {{ empty($list->searches[$field["name"]]) ? "empty" : "not-empty alert-info" }}' data-list="{{ $list->id }}" data-field="{{ $field['name'] }}" value="{{ htmlspecialchars($list->searches[$field['name']], ENT_QUOTES) }}" />
									</div>
								{/if}
							{/if}
						</th>
					{/if}
				{/foreach}
			</tr>
		{/if}
		
		<!-- THE CONTENT OF THE LIST RESULTS -->
		{if($list->recordNumber)}
			{foreach($display as $id => $line)}
				<tr class='list-line list-line-{{ $list->id }} {{ $list->selected == $id ? "selected" : "" }}' value="{{ $id }}" >					
					{if($list->checkbox && !$pdf)}
						<td style='width:30px'>
							<input type='checkbox' class="list-checkbox list-checkbox-{{ $list->id }}" value="{{ $id }}" {{ $list->checkbox['default'] == "all" || in_array($id, $list->checkbox['default']) ? "checked" : "" }} />
						</td>
					{/if}
					{foreach($line as $name => $data)}
						<td class="{{ $data['class'] }}" title="{{ $data['title'] }}" style="{{ $data['style'] }}" onclick="{{ $data['onclick'] }}">
							{{ $data['display'] }}
						</td>
					{/foreach}
				</tr>
			{/foreach}
		{else}
			<tr><td class="list-no-result" colspan="{{ $list->displayedColumns }}"><center class="text-error"> {{ $list->emptyMessage }} </center></td></tr>
		{/if}
	</table>
</div>
<script type="text/javascript">				
	page.lists["{{ $list->id }}"] = new ItemList({
		id : "{{ $list->id }}",
		file : "{{ $list->file }}",
		lines : {{ $list->linesNumber }},
		page : {{ $list->pageNumber }},
		sorts : {{ json_encode($list->sort,JSON_HEX_QUOT | JSON_HEX_APOS | JSON_FORCE_OBJECT) }},
		searches : {{ json_encode($list->searches,JSON_HEX_QUOT | JSON_HEX_APOS| JSON_FORCE_OBJECT) }},
		selected : {{ $list->selected !== false ? "'$list->selected'" : "null" }}
	});		
</script>