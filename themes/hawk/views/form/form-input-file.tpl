{import "form-input.tpl"}
<label for="{{$input->id}}" class="input-file-invitation" >
	{text key="main.input-file-invitation"}
	<span class="icon icon-check file-chosen-icon" ></span>
</label>
<script type="text/javascript">
    (function(){
        var Model = function(){
            this.value = ko.observable('');            
        };

        var model = new Model();
        
        ko.applyBindingsToNode($("[id='{{ $input->id }}']").get(0), {value : model.value, css : { 'filled' : model.value} });
    })();
</script>
