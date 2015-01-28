<table class='form-table'>
	{foreach($fieldsets as $blockname => $fieldset)}
		{if($column++ % $form->columns == 0)}
		<tr class='form-table-line'>
		{/if}
			<td class='form-table-cell' >
				{{ $fieldset }}								
			</td>
		{if($column % $form->columns == 0 || $column == count($form->fields))}
		</tr>
		{/if}
	{/foreach}
</table>