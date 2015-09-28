{import "form-input-textarea.tpl"}

<script type="text/javascript">
	(function(){
		var id = "{{ $input->id }}";
		var editor = CKEDITOR.replace(id, {
			language : app.language,
			removeButtons : 'Save,Scayt,Rtl,Ltr,Language,Flash',
			entities : false,		
			on : {				
				change : function(event){ 
					document.getElementById(id).value = event.editor.getData(); 
				}
			}
		});			
	})();
</script>