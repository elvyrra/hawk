<div class="input-inline input-group {{ $input->id }}">
	{import file="form-input.tpl"}
	<span class="input-group-addon"><i></i></span>
</div>
<script type="text/javascript">
    require(['jquery'], function($) {
        $('.{{$input->id}}').colorpicker()

        .on('hidePicker.colorpicker', function() {
            $('#{{ $input->id }}').trigger('change');
        });
    });
</script>