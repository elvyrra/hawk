<form 	name="{{ $form->name }}"
		class="{{ $form->class }} form {{ $form->upload ? 'upload-form' : ''}}"
		id="{{ $form->id }}"
		method="{{ $form->method }}"
		action="{{ $form->action }}"
		novalidate
		autocomplete="{{ $form->autocomplete ? "on" : "off" }}"
		{foreach($form->attributes as $key => $value)}
			{if($value !== null)} {{ $key }}="{{ htmlentities($value, ENT_COMPAT) }}" {/if}
		{/foreach}
	>
	<input type='hidden' name='_submittedForm' value='submitted'/>

	<div class='form-result-message'>
		{if($form->status == Form::STATUS_ERROR)}
			<p class="alert alert-danger">{{ $form->returns['message'] }}</p>
		{/if}
	</div>
	{{ $content }}
</form>



