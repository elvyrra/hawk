<form class="{{ $form->class }} form {{ $form->ajax ? 'ajax-form' : '' }}" id="{{ $form->id }}" method="{{ $form->method }}" action="{{ $form->action }}" {{ $form->target ? "target='{$form->target}'" : "" }} novalidate autocomplete="{{ $form->autocomplete ? "on" : "off" }}" {{ $form->enctype ? "enctype='{$form->enctype}" : "" }} >
	<input type='hidden' name='_FORM_ACTION_' value='valid'/>
	<h2 class='form-title' >{{ $form->title }}</h2>
	<div class='form-result-message'>
		{if($form->status == Form::STATUS_ERROR)}
			<p class="alert alert-error">{{ $form->returns['message'] }}</p>
		{/if}
	</div>	
	{{ $content }}	
</form>
{if($form->onsuccess)}
	<script type="text/javascript">
		$("#{{$form->id}}").on('form_success', function(event, data){		
			{{ $form->onsuccess }}
		});
	</script>
{/if}

{if($form->status == Form::STATUS_ERROR)}
	'coucou'
	<script type="text/javascript">
		debugger;
		$("#{{$form->id}}").displayErrors({{ json_encode($form->errors,JSON_HEX_APOS | JSON_HEX_QUOT | JSON_NUMERIC_CHECK) }});
	</script>
{/if}

