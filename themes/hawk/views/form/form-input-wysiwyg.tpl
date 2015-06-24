{import "form-input-textarea.tpl"}

<script type="text/javascript">
	(function(){
		var editor = CKEDITOR.replace("{{ $input->id }}", {
			language : app.language,
			removeButtons : 'Save,Scayt,Rtl,Ltr,Language,Flash',
			entities : false,		
		});	
		editor.on('change', function(event){
			$("#{{ $input->id }}").val(event.editor.getData());
		})
	})();
</script>