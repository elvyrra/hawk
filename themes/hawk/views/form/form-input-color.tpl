<div class="input-inline input-group {{ $input->id }}">
	{import file="form-input.tpl"}
	<span class="input-group-addon"><i></i></span>
</div>
<script>$(".{{$input->id}}").colorpicker();</script>
