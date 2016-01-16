<div class="input-inline input-group {{ $input->id }}">
	{import file="form-input.tpl"}
	<span class="input-group-addon"><i></i></span>
</div>
<script type="text/javascript">
    $(".{{$input->id}}").colorpicker()

    .on('hidePicker.colorpicker', function(event){
        $("#{{ $input->id }}").trigger('change');      
    });
</script>
