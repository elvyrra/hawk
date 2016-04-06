{import file="form-input.tpl"}
<label for="{{$input->id}}" class="input-file-invitation" >
    <span class="input-file-invitation-text">{text key="main.input-file-invitation"}</span>
    {icon icon="check" class="file-chosen-icon"}
</label>
<script type="text/javascript">
    (function(){
        require(['app'], function(){
            var Model = function(){
                this.value = ko.observable('');
            };

            var model = new Model();

            ko.applyBindingsToNode($("[id='{{ $input->id }}']").get(0), {value : model.value, css : { 'filled' : model.value} });
        });
    })();
</script>
