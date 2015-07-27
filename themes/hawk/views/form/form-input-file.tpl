{import "form-input.tpl"}
<label for="{{$input->id}}" class="input-file-invitation" data-bind="css : value() ? 'btn-success' : 'btn-default'">
	{text key="main.input-file-invitation"}
	<span class="fa fa-check file-chosen-icon" data-bind="visible: value"></span>
</label>
<script type="text/javascript">
	ko.applyBindings({
		value : ko.observable()
	}, $("[id='{{ $input->id }}']").parent().get(0));
</script>
