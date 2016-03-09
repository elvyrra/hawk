<form 	name="{{ $form->name }}"
		class="{{ $form->class }} form {{ $form->upload ? 'upload-form' : ''}}"
		id="{{ $form->id }}"
		method="{{ $form->method }}"
		action="{{ $form->action }}"
		novalidate
		autocomplete="{{ $form->autocomplete ? "on" : "off" }}"
	>
	<input type='hidden' name='_submittedForm' value='submitted'/>

	<div class='form-result-message'>
		{if($form->status == Form::STATUS_ERROR)}
			<p class="alert alert-danger">{{ $form->returns['message'] }}</p>
		{/if}
	</div>
	{{ $content }}
</form>



