{import file="form-input-textarea.tpl"}

<script type="text/javascript">
    (function(){
        ko.applyBindingsToNode(document.getElementById("{{ $input->id }}"), { wysiwyg : 1});
    })();
</script>