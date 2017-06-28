{import file="form-input.tpl"}

<script type="text/javascript">
    require(['jquery'], function($) {
        setTimeout(function() {
            $('#{{ $input->id }}').datepicker({{ $input->picker }});
        });
    });
</script>