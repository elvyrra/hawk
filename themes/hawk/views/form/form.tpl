<form 	name="{{ $form->name }}" 
		class="{{ $form->class }} form {{ $form->upload ? 'upload-form' : ''}}" 
		id="{{ $form->id }}" 
		method="{{ $form->method }}" 
		action="{{ $form->action }}" 
		{{ $form->target ? "target='{$form->target}'" : "" }} novalidate 
		autocomplete="{{ $form->autocomplete ? "on" : "off" }}" 
		{{ $form->enctype ? "enctype='{$form->enctype}" : "" }} 
	>
	<input type='hidden' name='_FORM_ACTION_' value='valid'/>
	
	<div class='form-result-message'>
		{if($form->status == Form::STATUS_ERROR)}
			<p class="alert alert-error">{{ $form->returns['message'] }}</p>
		{/if}
	</div>	
	{{ $content }}	
</form>

<script type="text/javascript">
	app.ready(function(){
		var form = new Form("{{ $form->id }}", {{ json_encode($form->fields, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_NUMERIC_CHECK) }});
		
		
		{if(!empty($form->onsuccess))}
			form.onsuccess = function(data){
				{{ $form->onsuccess }}
			};			
		{/if}
	
		{if($form->status == Form::STATUS_ERROR)}		
			form.displayErrors({{ json_encode($form->errors,JSON_HEX_APOS | JSON_HEX_QUOT | JSON_NUMERIC_CHECK) }});
		{/if}
		app.forms["{{ $form->id }}"] = form;
	});
</script>


