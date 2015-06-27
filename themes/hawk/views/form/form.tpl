<form 	class="{{ $form->class }} form {{ $form->ajax ? 'ajax-form' : '' }} {{ $form->upload ? 'upload-form' : ''}}" 
		id="{{ $form->id }}" 
		method="{{ $form->method }}" 
		action="{{ $form->action }}" 
		{{ $form->target ? "target='{$form->target}'" : "" }} novalidate 
		autocomplete="{{ $form->autocomplete ? "on" : "off" }}" 
		{{ $form->enctype ? "enctype='{$form->enctype}" : "" }} 
	>
	
	<input type='hidden' name='_FORM_ACTION_' value='valid'/>
	{if($form->title)}
		<h2 class='form-title' >{{ $form->title }}</h2>
	{/if}
	
	<div class='form-result-message'>
		{if($form->status == Form::STATUS_ERROR)}
			<p class="alert alert-error">{{ $form->returns['message'] }}</p>
		{/if}
	</div>	
	
	{{ $content }}	
</form>

<script type="text/javascript">
	app.ready(function(){
		app.forms["{{ $form->id }}"] = new Form("{{ $form->id }}", {{ json_encode($form->fields, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_NUMERIC_CHECK) }});
		
		{if(!empty($form->onsuccess))}
			$("#{{$form->id}}").on('success', function(event, data){		
				{{ $form->onsuccess }}
			});	
		{/if}
	
		{if($form->status == Form::STATUS_ERROR)}		
			app.forms["{{ $form->id }}"].displayErrors({{ json_encode($form->errors,JSON_HEX_APOS | JSON_HEX_QUOT | JSON_NUMERIC_CHECK) }});
		{/if}
	});
</script>


