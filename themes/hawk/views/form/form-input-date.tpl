{import file="form-input.tpl"}

<script type="text/javascript">
    require(['app'], function() {
        $('#{{ $input->id }}').datepicker({{ $input->picker }});
    });
</script>