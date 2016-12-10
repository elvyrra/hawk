{import file="form-input.tpl"}
<label for="{{$input->id}}" class="input-file-invitation" e-class="{filled : value}">
    <span class="input-file-invitation-text">{text key="main.input-file-invitation"}</span>
    {icon icon="check" class="file-chosen-icon"}
</label>
<script type="text/javascript">
    require(['emv', 'jquery'], function(EMV, $) {
        const model = new EMV({
            value : ''
        });

        model.$apply($('[id="{{ $input->id }}"]').get(0));
    });
</script>
