{import file="form-input.tpl"}

<script type="text/javascript">
    require(['jquery'], function($) {
        setTimeout(function() {
            $('#{{ $input->id }}').datepicker({{ json_encode($input->picker) }})
            {if($input->interval)}
                .on('changeDate', function(event) {
                    var dates = event.dates;
                    dates.sort(function(date1, date2) {
                        return date1 > date2 ? 1 : -1;
                    });

                    $('#{{ $input->id }}').datepicker('update', dates[0], dates[1]);
                });
            {/if}
        });
    });
</script>