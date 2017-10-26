{import file="../../../hawk/views/form/form-input.tpl"}
<label for="{{$input->id}}" class="input-file-invitation">
    <span class="input-file-invitation-text">{text key="main.input-file-invitation"}</span>
    {icon icon="check" class="file-chosen-icon"}
</label>
<script type="text/javascript">
    require(['emv', 'jquery'], function(EMV, $) {
        $('[id="{{ $input->id }}"]').change(function(event) {
            var files = event.target.files || event.originalEvent.target.files;

            if(files && files.length) {
                $(this).next('label').addClass('filled');
            }
            else {
                $(this).next('label').removeClass('filled');
            }
        });
    });
</script>